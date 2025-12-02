<?php

namespace App\Core;

class MinecraftPing
{
    private $socket;
    private $address;
    private $port;
    private $timeout;

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

        // Use the shared static method to build the handshake packet
        // We extracted this so the AsyncBatchPinger can also build packets
        // without duplicating this binary protocol logic.
        $handshake = self::buildHandshakePacket($this->address, $this->port);

        fwrite($this->socket, $handshake);
        // Send status request packet
        fwrite($this->socket, "\x01\x00");

        // Read response packet length and validate it
        // We pass $this->socket to the static method because in the async version,
        // we will be passing a different socket resource.
        $length = self::readVarInt($this->socket);
        if ($length < 10) {
            return false;
        }

        // Skip packet ID byte, then read JSON data length
        self::readVarInt($this->socket);
        $length = self::readVarInt($this->socket);

        $data = "";
        while (strlen($data) < $length) {
            // Loop until all JSON response bytes are read (accounts for partial reads)
            $data .= fread($this->socket, $length - strlen($data));
        }

        $this->close();

        // Parse JSON response; return false if empty or malformed
        $response = json_decode($data, true);
        return $response ?: false;
    }

    private function connect()
    {
        // Suppress fsockopen warnings with @ operator; errors handled via return value
        $this->socket = @fsockopen($this->address, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            return false;
        }

        // Set socket timeout to prevent indefinite hangs on slow/unresponsive servers
        stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    private function close()
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Reads a VarInt from the socket.
     */
    public static function readVarInt($socket)
    {
        // Minecraft uses variable-length integers: each byte contains 7 data bits + 1 continuation bit
        $i = 0;  // Accumulated value
        $j = 0;  // Bit position counter

        while (true) {
            $k = @fgetc($socket);
            if ($k === false) {
                return 0;
            }

            $k = ord($k);
            // Shift 7-bit segment into position and accumulate
            $i |= ($k & 0x7F) << $j++ * 7;

            // Sanity check: if we've read more than 5 bytes, the data is invalid
            if ($j > 5) {
                return 0;
            }

            // Check continuation bit (MSB); if not set, we're done reading
            if (($k & 0x80) != 128) {
                break;
            }
        }

        return $i;
    }

    /**
     * Builds the initial Handshake + Status Request packet.
     */
    public static function buildHandshakePacket($address, $port)
    {
        $data = "\x00";  // Packet ID for handshake
        $data .= "\x04";  // Protocol version 4
        $data .= pack('c', strlen($address)) . $address;  // Server address with length prefix
        $data .= pack('n', $port);  // Port number (big-endian)
        $data .= "\x01";  // Next state = status
        $data = pack('c', strlen($data)) . $data;  // Prepend packet length

        return $data;
    }

    public static function checkServer($address, $port)
    {
        $ping = new self($address, $port);
        $result = $ping->query();

        // Provide consistent response format whether server is online or offline
        if (!$result) {
            return [
                'online' => false,
                'players' => 0,
                'max_players' => 0,
                'version' => 'offline'
            ];
        }

        // Extract and safely default nested values to prevent missing key errors
        return [
            'online' => true,
            'players' => $result['players']['online'] ?? 0,
            'max_players' => $result['players']['max'] ?? 0,
            'version' => $result['version']['name'] ?? 'unknown'
        ];
    }
}
