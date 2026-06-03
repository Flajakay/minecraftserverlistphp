<?php

namespace App\Core\Integrations\GameServers;

class ServerProtocolRegistry
{
    private array $adapters = [];

    public function __construct()
    {
        $this->register(new MinecraftJavaAdapter());
        $this->register(new SteamA2SAdapter());
        $this->register(new MinecraftBedrockAdapter());
    }

    public function register(ServerProtocolAdapter $adapter): void
    {
        $this->adapters[] = $adapter;
    }

    public function getAdapter(string $protocol): ?ServerProtocolAdapter
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($protocol)) {
                return $adapter;
            }
        }
        return null;
    }

    public function getProtocols(): array
    {
        $protocols = [];
        foreach ($this->adapters as $adapter) {
            $ref = new \ReflectionClass($adapter);
            $interfaces = $ref->getInterfaceNames();
            foreach ($this->adapters as $a) {
                $protocols[] = $a->supports('') ? '' : '';
            }
        }
        $result = [];
        foreach ($this->adapters as $adapter) {
            $ref = new \ReflectionClass($adapter);
            $shortName = $ref->getShortName();
            $result[] = $shortName;
        }
        return $result;
    }

    public function getAvailableProtocols(): array
    {
        $protocols = [];
        $minecraft = new MinecraftJavaAdapter();
        $steam = new SteamA2SAdapter();
        $bedrock = new MinecraftBedrockAdapter();

        $protocols['minecraft_java'] = 'Minecraft Java';
        $protocols['steam_a2s'] = 'Steam';
        $protocols['minecraft_bedrock'] = 'Minecraft Bedrock';

        return $protocols;
    }

    public function getProtocolLabel(string $protocol): string
    {
        $labels = $this->getAvailableProtocols();
        return $labels[$protocol] ?? $protocol;
    }
}
