<?php

namespace App\Core\Integrations\GameServers;

use App\Core\Integrations\MinecraftPing;

class MinecraftJavaAdapter implements ServerProtocolAdapter
{
    public function supports(string $protocol): bool
    {
        return $protocol === 'minecraft_java';
    }

    public function query(string $address, int $port, int $queryPort, int $timeout = 2): ServerStatusResult
    {
        $raw = (new MinecraftPing($address, $port, $timeout))->query();

        if (!$raw) {
            return ServerStatusResult::offline();
        }

        $players = $raw['players'] ?? [];
        $version = $raw['version'] ?? [];

        return new ServerStatusResult(
            online: true,
            players: (int)($players['online'] ?? 0),
            maxPlayers: (int)($players['max'] ?? 0),
            version: (string)($version['name'] ?? 'unknown'),
            name: null,
            map: null,
            game: 'minecraft',
            metadata: ['description' => $raw['description'] ?? null, 'favicon' => $raw['favicon'] ?? null],
        );
    }
}
