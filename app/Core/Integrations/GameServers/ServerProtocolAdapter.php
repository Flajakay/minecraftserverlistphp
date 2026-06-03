<?php

namespace App\Core\Integrations\GameServers;

interface ServerProtocolAdapter
{
    public function supports(string $protocol): bool;
    public function query(string $address, int $port, int $queryPort, int $timeout = 2): ServerStatusResult;
}
