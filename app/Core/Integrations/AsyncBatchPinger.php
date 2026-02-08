<?php

namespace App\Core\Integrations;



/**
 * Concurrent Minecraft status pings using non-blocking sockets.
 *
 * Uses `stream_select()` to multiplex connect/write/read across many servers.
 * The implementation returns a normalized "offline" result on any failure/timeout.
 */
class AsyncBatchPinger
{
    /**
     * Ping many servers concurrently.
     *
     * @param array $servers Objects with at least: id, address, port
     */
    public function pingBatch(array $servers, int $timeout = 2, int $concurrency = 10): array
    {
        $results = [];
        $sockets = [];
        $map = [];
        $queue = $servers;
        $startTimes = [];
        $written = [];

        // Loop until the queue is empty and all sockets have been closed.
        while (!empty($queue) || !empty($sockets)) {

            // Fill the pool up to the concurrency limit.
            while (count($sockets) < $concurrency && !empty($queue)) {
                $server = array_shift($queue);

                $socket = @stream_socket_client(
                    "tcp://{$server->address}:{$server->port}",
                    $errno,
                    $errstr,
                    $timeout,
                    STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
                );

                if ($socket) {
                    // Non-blocking so `stream_select()` can manage multiple connections.
                    stream_set_blocking($socket, false);

                    $id = (int) $socket;
                    $sockets[$id] = $socket;
                    $map[$id] = $server;
                    $startTimes[$id] = microtime(true);
                } else {
                    // Immediate failure (e.g., DNS failure).
                    $results[$server->id] = $this->getOfflineResult();
                }
            }

            // Wait for activity on any socket.
            $read = $sockets;
            // Only check for writability until the handshake has been written.
            $write = array_diff_key($sockets, $written);
            $except = null;

            // Short select timeout keeps the loop responsive and allows manual timeout checks.
            if (stream_select($read, $write, $except, 0, 100000) > 0) {

                // Writable sockets are connected and ready for the handshake.
                foreach ($write as $socket) {
                    $id = (int) $socket;
                    if (isset($map[$id])) {
                        // Send handshake + status request.
                        $packet = MinecraftPing::buildHandshakePacket($map[$id]->address, $map[$id]->port);
                        fwrite($socket, $packet);
                        fwrite($socket, "\x01\x00"); // Status request

                        $written[$id] = true;
                    }
                }

                // Readable sockets have response data available.
                foreach ($read as $socket) {
                    $id = (int) $socket;
                    if (isset($map[$id])) {
                        $server = $map[$id];

                        // Once readable, switch to blocking mode to read the full VarInt+JSON
                        // payload without `readVarInt()` returning 0 due to partial buffering.
                        stream_set_blocking($socket, true);

                        $length = MinecraftPing::readVarInt($socket);

                        if ($length > 0) {
                            MinecraftPing::readVarInt($socket); // Skip Packet ID
                            $jsonLength = MinecraftPing::readVarInt($socket);

                            $data = "";
                            // Read in chunks to handle partial reads.
                            while (strlen($data) < $jsonLength) {
                                $chunk = fread($socket, $jsonLength - strlen($data));
                                if ($chunk === false || strlen($chunk) === 0) {
                                    break;
                                }
                                $data .= $chunk;
                            }

                            $response = json_decode($data, true);
                            $results[$server->id] = $response ? $this->formatResult($response) : $this->getOfflineResult();
                        } else {
                            $results[$server->id] = $this->getOfflineResult();
                        }

                        fclose($socket);
                        unset($sockets[$id]);
                        unset($map[$id]);
                        unset($startTimes[$id]);
                        unset($written[$id]);
                    }
                }
            }

            // `stream_select()` doesn't enforce per-socket connect/read timeouts; track elapsed
            // time ourselves and treat long-running sockets as offline.
            $now = microtime(true);
            foreach ($sockets as $id => $socket) {
                if ($now - $startTimes[$id] >= $timeout) {
                    $server = $map[$id];
                    $results[$server->id] = $this->getOfflineResult();

                    fclose($socket);
                    unset($sockets[$id]);
                    unset($map[$id]);
                    unset($startTimes[$id]);
                    unset($written[$id]);
                }
            }
        }

        return $results;
    }

    private function getOfflineResult(): array
    {
        return [
            'online' => false,
            'players' => 0,
            'max_players' => 0,
            'version' => 'offline'
        ];
    }

    private function formatResult($result): array
    {
        return [
            'online' => true,
            'players' => $result['players']['online'] ?? 0,
            'max_players' => $result['players']['max'] ?? 0,
            'version' => $result['version']['name'] ?? 'unknown'
        ];
    }
}
