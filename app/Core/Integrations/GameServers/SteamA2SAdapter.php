<?php

namespace App\Core\Integrations\GameServers;

use xPaw\SourceQuery\SourceQuery;
use xPaw\SourceQuery\Exception\AuthenticationException;
use xPaw\SourceQuery\Exception\InvalidPacketException;
use xPaw\SourceQuery\Exception\SocketException;

class SteamA2SAdapter implements ServerProtocolAdapter
{
    public function supports(string $protocol): bool
    {
        return $protocol === 'steam_a2s';
    }

    public function query(string $address, int $port, int $queryPort, int $timeout = 2): ServerStatusResult
    {
        $sq = new SourceQuery();

        try {
            $sq->Connect($address, $queryPort, $timeout, SourceQuery::SOURCE);
            $info = $sq->GetInfo();

            $sq->Disconnect();

            if (empty($info)) {
                return ServerStatusResult::offline();
            }

            $players = (int)($info['Players'] ?? 0);
            $maxPlayers = (int)($info['MaxPlayers'] ?? 0);
            $version = (string)($info['Version'] ?? '');
            $name = (string)($info['HostName'] ?? '');
            $map = (string)($info['Map'] ?? '');

            $game = null;
            $appId = null;

            if (!empty($info['GameDir'])) {
                $game = (string)$info['GameDir'];
            }

            if (!empty($info['AppID'])) {
                $appId = (int)$info['AppID'];
            } elseif (!empty($info['GameID'])) {
                $appId = (int)$info['GameID'];
            }

            $passwordProtected = !empty($info['Visibility']) && $info['Visibility'] === 1;

            return new ServerStatusResult(
                online: true,
                players: $players,
                maxPlayers: $maxPlayers,
                version: $version,
                name: $name,
                map: $map,
                game: $game,
                metadata: [
                    'app_id' => $appId,
                    'game_dir' => $info['GameDir'] ?? null,
                    'game_description' => $info['GameDescription'] ?? null,
                    'password_protected' => $passwordProtected,
                    'secure' => !empty($info['Secure']),
                    'bots' => (int)($info['Bots'] ?? 0),
                    'operating_system' => $info['OS'] ?? null,
                    'server_type' => $info['ServerType'] ?? null,
                    'environment' => $info['Environment'] ?? null,
                ],
            );
        } catch (AuthenticationException | InvalidPacketException | SocketException $e) {
            return ServerStatusResult::offline();
        }
    }
}
