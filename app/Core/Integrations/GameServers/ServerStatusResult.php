<?php

namespace App\Core\Integrations\GameServers;

class ServerStatusResult
{
    public function __construct(
        public readonly bool $online,
        public readonly int $players = 0,
        public readonly int $maxPlayers = 0,
        public readonly string $version = '',
        public readonly ?string $name = null,
        public readonly ?string $map = null,
        public readonly ?string $game = null,
        public readonly array $metadata = [],
    ) {}

    public static function offline(): self
    {
        return new self(
            online: false,
            players: 0,
            maxPlayers: 0,
            version: 'offline',
        );
    }

    public function toArray(): array
    {
        return [
            'online' => $this->online,
            'players' => $this->players,
            'max_players' => $this->maxPlayers,
            'version' => $this->version,
            'name' => $this->name,
            'map' => $this->map,
            'game' => $this->game,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            online: (bool)($data['online'] ?? false),
            players: (int)($data['players'] ?? 0),
            maxPlayers: (int)($data['max_players'] ?? 0),
            version: (string)($data['version'] ?? ''),
            name: $data['name'] ?? null,
            map: $data['map'] ?? null,
            game: $data['game'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }
}
