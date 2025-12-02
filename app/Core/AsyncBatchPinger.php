<?php

namespace App\Core;

use App\Core\MinecraftPing;

class AsyncBatchPinger
{
    public function pingBatch(array $servers, int $timeout = 2, int $concurrency = 10)
    {
        $results = [];
        $sockets = []; // Active sockets: resource_id => socket
        $map = [];     // Map: resource_id => server_id
        $queue = $servers; // Queue of servers waiting to be pinged
        $startTimes = []; // Track when each socket started (for timeout)
        $written = []; // Track which sockets have sent the handshake

        // Loop until we have processed every server in the queue AND closed all open sockets.
        while (!empty($queue) || !empty($sockets)) {

            // 1. Fill the pool up to the concurrency limit
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
                    // Set non-blocking mode so we can manage multiple connections
                    stream_set_blocking($socket, false);

                    $id = (int) $socket;
                    $sockets[$id] = $socket;
                    $map[$id] = $server;
                    $startTimes[$id] = microtime(true);
                } else {
                    // Immediate failure (DNS resolution failed, etc.)
                    $results[$server->id] = $this->getOfflineResult();
                }
            }

            // 2. Wait for activity on any socket
            $read = $sockets;
            // Only check for writability if we haven't written the handshake yet
            $write = array_diff_key($sockets, $written);
            $except = null;

            // stream_select pauses the script until at least one socket is ready.
            // Timeout is 0.1s
            if (stream_select($read, $write, $except, 0, 100000) > 0) {

                // Handle Writable Sockets (Connected!)
                foreach ($write as $socket) {
                    $id = (int) $socket;
                    if (isset($map[$id])) {
                        // Send the handshake packet
                        $packet = MinecraftPing::buildHandshakePacket($map[$id]->address, $map[$id]->port);
                        fwrite($socket, $packet);
                        fwrite($socket, "\x01\x00"); // Status request

                        // Mark as written so we stop checking for writability
                        $written[$id] = true;
                    }
                }

                // Handle Readable Sockets (Data received!)
                foreach ($read as $socket) {
                    $id = (int) $socket;
                    if (isset($map[$id])) {
                        $server = $map[$id];

                        // Switch to BLOCKING mode for reading.
                        // Since stream_select told us there is data, we can safely block 
                        // to read the full response. This prevents 'readVarInt' from returning 
                        // 0 just because the data hasn't fully arrived in the buffer yet.
                        stream_set_blocking($socket, true);

                        // Read the response using our helper
                        $length = MinecraftPing::readVarInt($socket);

                        if ($length > 0) {
                            MinecraftPing::readVarInt($socket); // Skip Packet ID
                            $jsonLength = MinecraftPing::readVarInt($socket);

                            $data = "";
                            // Since we are blocking, fread should get the data or timeout
                            // We read in chunks to be safe
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

                        // Done with this socket
                        fclose($socket);
                        unset($sockets[$id]);
                        unset($map[$id]);
                        unset($startTimes[$id]);
                        unset($written[$id]);
                    }
                }
            }

            // 3. Check for Timeouts
            // stream_select doesn't handle connection timeouts automatically for us.
            // We must manually check if any socket has been open too long without finishing.
            $now = microtime(true);
            foreach ($sockets as $id => $socket) {
                if ($now - $startTimes[$id] >= $timeout) {
                    // Timeout!
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

    private function getOfflineResult()
    {
        return [
            'online' => false,
            'players' => 0,
            'max_players' => 0,
            'version' => 'offline'
        ];
    }

    private function formatResult($result)
    {
        return [
            'online' => true,
            'players' => $result['players']['online'] ?? 0,
            'max_players' => $result['players']['max'] ?? 0,
            'version' => $result['version']['name'] ?? 'unknown'
        ];
    }
}
