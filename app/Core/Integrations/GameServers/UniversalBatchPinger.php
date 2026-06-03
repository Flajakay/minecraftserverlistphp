<?php

namespace App\Core\Integrations\GameServers;

class UniversalBatchPinger
{
    private ServerProtocolRegistry $registry;

    public function __construct(?ServerProtocolRegistry $registry = null)
    {
        $this->registry = $registry ?? new ServerProtocolRegistry();
    }

    public function pingBatch(array $servers, int $timeout = 2, int $concurrency = 10): array
    {
        $results = [];

        $minecraftServers = [];
        $steamServers = [];

        foreach ($servers as $server) {
            $protocol = $server->protocol ?? 'minecraft_java';

            if ($protocol === 'minecraft_java') {
                $minecraftServers[] = $server;
            } elseif ($protocol === 'steam_a2s') {
                $steamServers[] = $server;
            }
        }

        if (!empty($minecraftServers)) {
            $mcResults = $this->pingMinecraftBatch($minecraftServers, $timeout, $concurrency);
            foreach ($mcResults as $id => $result) {
                $results[$id] = $result;
            }
        }

        foreach ($steamServers as $server) {
            $results[$server->id] = $this->pingSingleSteam($server, $timeout);
        }

        return $results;
    }

    private function pingMinecraftBatch(array $servers, int $timeout, int $concurrency): array
    {
        $pinger = new \App\Core\Integrations\AsyncBatchPinger();
        $rawResults = $pinger->pingBatch($servers, $timeout, $concurrency);
        $results = [];

        foreach ($rawResults as $id => $raw) {
            $result = ServerStatusResult::fromArray($raw);
            $results[$id] = $result;
        }

        return $results;
    }

    private function pingSingleSteam($server, int $timeout): ServerStatusResult
    {
        $adapter = $this->registry->getAdapter('steam_a2s');
        if (!$adapter) {
            return ServerStatusResult::offline();
        }

        $queryPort = $server->query_port ?? $server->port;
        return $adapter->query($server->address, (int)$server->port, (int)$queryPort, $timeout);
    }
}
