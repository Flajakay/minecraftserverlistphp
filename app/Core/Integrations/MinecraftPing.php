<?php

namespace App\Core\Integrations;

/**
 * Minecraft server status ping (handshake + status request).
 *
 * Returns the decoded JSON status response, or false on any network/protocol failure.
 */
class MinecraftPing
{
    private $socket;
    private $address;
    private int $port;
    private int $timeout;

    public function __construct($address, $port = 25565, $timeout = 2)
    {
        $this->address = $address;
        $this->port = (int) $port;
        $this->timeout = (int) $timeout;
    }

    public function query()
    {
        if (!$this->connect()) {
            return false;
        }

        // Shared packet builder allows the async implementation to reuse protocol logic.
        $handshake = self::buildHandshakePacket($this->address, $this->port);

        fwrite($this->socket, $handshake);
        // Status request packet.
        fwrite($this->socket, "\x01\x00");

        // Read response packet length.
        $length = self::readVarInt($this->socket);
        if ($length < 10) {
            return false;
        }

        // Skip packet ID, then read JSON payload length.
        self::readVarInt($this->socket);
        $length = self::readVarInt($this->socket);

        $data = "";
        while (strlen($data) < $length) {
            $data .= fread($this->socket, $length - strlen($data));
        }

        $this->close();

        // Parse JSON response; return false if empty or malformed
        $response = json_decode($data, true);
        return $response ?: false;
    }

    private function connect(): bool
    {
        // Suppress warnings; the caller treats any failure as "offline".
        $this->socket = @fsockopen($this->address, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            return false;
        }

        // Prevent indefinite hangs on slow/unresponsive servers.
        stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    private function close(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Read a VarInt from a socket.
     *
     * Minecraft uses VarInt encoding (7 bits per byte + continuation bit). Returns 0 on
     * read/protocol failure.
     */
    public static function readVarInt($socket): int
    {
        $i = 0;  // Accumulated value
        $j = 0;  // Bit position counter

        while (true) {
            $k = @fgetc($socket);
            if ($k === false) {
                return 0;
            }

            $k = ord($k);
            $i |= ($k & 0x7F) << $j++ * 7;

            // VarInt is at most 5 bytes for 32-bit values.
            if ($j > 5) {
                return 0;
            }

            if (($k & 0x80) != 128) {
                break;
            }
        }

        return $i;
    }

    /**
     * Build the handshake packet for a status request.
     */
    public static function buildHandshakePacket($address, $port): string
    {
        $data = "\x00";  // Packet ID for handshake
        $data .= "\x04";  // Protocol version 4
        $data .= pack('c', strlen($address)) . $address;  // Server address with length prefix
        $data .= pack('n', $port);  // Port number (big-endian)
        $data .= "\x01";  // Next state = status
        // Prepend packet length

        return pack('c', strlen($data)) . $data;
    }

    public static function checkServer($address, $port): array
    {
        $ping = new self($address, $port);
        $result = $ping->query();

        // Normalize response for callers.
        if (!$result) {
            return [
                'online' => false,
                'players' => 0,
                'max_players' => 0,
                'version' => 'offline'
            ];
        }

        return [
            'online' => true,
            'players' => $result['players']['online'] ?? 0,
            'max_players' => $result['players']['max'] ?? 0,
            'version' => $result['version']['name'] ?? 'unknown'
        ];
    }
}
