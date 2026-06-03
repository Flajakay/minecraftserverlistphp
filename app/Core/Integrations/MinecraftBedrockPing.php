<?php

namespace App\Core\Integrations;

class MinecraftBedrockPing
{
    private const RAKNET_MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
    private const PACKET_ID_UNCONNECTED_PING = "\x01";
    private const PACKET_ID_UNCONNECTED_PONG = "\x1c";

    private string $address;
    private int $port;
    private int $timeout;

    public function __construct(string $address, int $port, int $timeout = 2)
    {
        $this->address = $this->resolveSafeHostIp($address);
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function query(): array|false
    {
        $socket = @stream_socket_client("udp://{$this->address}:{$this->port}", $errno, $errstr, $this->timeout);

        if (!$socket) {
            return false;
        }

        stream_set_timeout($socket, $this->timeout);

        $pingPacket = $this->buildPingPacket();
        @fwrite($socket, $pingPacket);

        $response = @fread($socket, 4096);

        fclose($socket);

        if ($response === false || $response === '') {
            return false;
        }

        return $this->parseResponse($response);
    }

    public function buildPingPacket(): string
    {
        return self::PACKET_ID_UNCONNECTED_PING
            . $this->packLong(0)
            . self::RAKNET_MAGIC
            . $this->packLong($this->generateClientGuid());
    }

    public function parseResponse(string $response): array|false
    {
        $offset = 0;

        $packetId = $response[$offset] ?? '';
        $offset++;

        if ($packetId !== self::PACKET_ID_UNCONNECTED_PONG) {
            return false;
        }

        $offset += 8;

        $offset += 8;

        $magic = substr($response, $offset, 16);
        $offset += 16;

        if ($magic !== self::RAKNET_MAGIC) {
            return false;
        }

        if (strlen($response) < $offset + 2) {
            return false;
        }

        $length = unpack('n', substr($response, $offset, 2))[1];
        $offset += 2;

        if ($length === 0 || strlen($response) < $offset + $length) {
            return false;
        }

        $serverIdString = substr($response, $offset, $length);

        return $this->parseServerIdString($serverIdString);
    }

    public function parseServerIdString(string $serverIdString): array|false
    {
        $parts = explode(';', $serverIdString);

        if (empty($parts) || $parts[0] !== 'MCPE') {
            return false;
        }

        $edition = $parts[0] ?? 'MCPE';
        $motd = $parts[1] ?? '';
        $protocolVersion = isset($parts[2]) ? (int)$parts[2] : 0;
        $version = $parts[3] ?? '';
        $players = isset($parts[4]) ? (int)$parts[4] : 0;
        $maxPlayers = isset($parts[5]) ? (int)$parts[5] : 0;
        $serverGuid = $parts[6] ?? '';
        $motdExtra = $parts[7] ?? '';
        $gameMode = $parts[8] ?? '';
        $gameModeNumeric = isset($parts[9]) ? (int)$parts[9] : 0;
        $ipv4Port = isset($parts[10]) ? (int)$parts[10] : $this->port;
        $ipv6Port = isset($parts[11]) ? (int)$parts[11] : 19133;

        return [
            'online' => true,
            'players' => $players,
            'max_players' => $maxPlayers,
            'version' => $version,
            'protocol_version' => $protocolVersion,
            'motd' => $motd,
            'motd_extra' => $motdExtra,
            'server_guid' => $serverGuid,
            'game_mode' => $gameMode,
            'game_mode_numeric' => $gameModeNumeric,
            'ipv4_port' => $ipv4Port,
            'ipv6_port' => $ipv6Port,
            'edition' => $edition,
        ];
    }

    private function resolveSafeHostIp(string $address): string
    {
        $ip = @gethostbyname($address);

        if ($ip === $address && !filter_var($address, FILTER_VALIDATE_IP)) {
            return $address;
        }

        return $ip;
    }

    private function packLong(int $value): string
    {
        return pack('P', $value);
    }

    private function generateClientGuid(): int
    {
        if (function_exists('random_int')) {
            return random_int(0, PHP_INT_MAX);
        }

        return crc32(uniqid('', true));
    }
}
