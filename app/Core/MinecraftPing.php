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
        $this->port = (int)$port;
        $this->timeout = (int)$timeout;
    }

    public function query()
    {
        if (!$this->connect()) {
            return false;
        }

        $data = "\x00";
        $data .= "\x04";
        $data .= pack('c', strlen($this->address)) . $this->address;
        $data .= pack('n', $this->port);
        $data .= "\x01";
        $data = pack('c', strlen($data)) . $data;

        fwrite($this->socket, $data);
        fwrite($this->socket, "\x01\x00");

        $length = $this->readVarInt();
        if ($length < 10) {
            return false;
        }

        $this->readVarInt();
        $length = $this->readVarInt();

        $data = "";
        while (strlen($data) < $length) {
            $data .= fread($this->socket, $length - strlen($data));
        }

        $this->close();

        $response = json_decode($data, true);
        return $response ?: false;
    }

    private function connect()
    {
        $this->socket = @fsockopen($this->address, $this->port, $errno, $errstr, $this->timeout);
        
        if (!$this->socket) {
            return false;
        }

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

    private function readVarInt()
    {
        $i = 0;
        $j = 0;

        while (true) {
            $k = @fgetc($this->socket);
            if ($k === false) {
                return 0;
            }

            $k = ord($k);
            $i |= ($k & 0x7F) << $j++ * 7;

            if ($j > 5) {
                return 0;
            }

            if (($k & 0x80) != 128) {
                break;
            }
        }

        return $i;
    }

    public static function checkServer($address, $port)
    {
        $ping = new self($address, $port);
        $result = $ping->query();
        
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
