<?php

namespace App\Core\Integrations\GameServers;

use App\Core\Integrations\MinecraftBedrockPing;

class MinecraftBedrockAdapter implements ServerProtocolAdapter
{
    public function supports(string $protocol): bool
    {
        return $protocol === 'minecraft_bedrock';
    }

    public function query(string $address, int $port, int $queryPort, int $timeout = 2): ServerStatusResult
    {
        $raw = (new MinecraftBedrockPing($address, $port, $timeout))->query();

        if (!$raw) {
            return ServerStatusResult::offline();
        }

        return new ServerStatusResult(
            online: true,
            players: $raw['players'],
            maxPlayers: $raw['max_players'],
            version: $raw['version'],
            name: $raw['motd'],
            map: $raw['motd_extra'] ?: null,
            game: 'Minecraft Bedrock',
            metadata: [
                'protocol_version' => $raw['protocol_version'],
                'edition' => $raw['edition'],
                'server_guid' => $raw['server_guid'],
                'game_mode' => $raw['game_mode'],
                'game_mode_numeric' => $raw['game_mode_numeric'],
                'motd' => $raw['motd'],
                'motd_extra' => $raw['motd_extra'],
                'ipv4_port' => $raw['ipv4_port'],
                'ipv6_port' => $raw['ipv6_port'],
            ],
        );
    }
}
