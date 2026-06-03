<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Integrations\GameServers\ServerProtocolRegistry;
use App\Core\Integrations\GameServers\MinecraftJavaAdapter;
use App\Core\Integrations\GameServers\SteamA2SAdapter;
use App\Core\Integrations\GameServers\MinecraftBedrockAdapter;

class ProtocolRegistryTest extends TestCase
{
    private ServerProtocolRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ServerProtocolRegistry();
    }

    public function testRegistryResolvesMinecraftJavaAdapter(): void
    {
        $adapter = $this->registry->getAdapter('minecraft_java');
        $this->assertNotNull($adapter);
        $this->assertInstanceOf(MinecraftJavaAdapter::class, $adapter);
    }

    public function testRegistryResolvesSteamA2SAdapter(): void
    {
        $adapter = $this->registry->getAdapter('steam_a2s');
        $this->assertNotNull($adapter);
        $this->assertInstanceOf(SteamA2SAdapter::class, $adapter);
    }

    public function testRegistryResolvesMinecraftBedrockAdapter(): void
    {
        $adapter = $this->registry->getAdapter('minecraft_bedrock');
        $this->assertNotNull($adapter);
        $this->assertInstanceOf(MinecraftBedrockAdapter::class, $adapter);
    }

    public function testRegistryReturnsNullForUnknownProtocol(): void
    {
        $adapter = $this->registry->getAdapter('unknown_protocol');
        $this->assertNull($adapter);
    }

    public function testMinecraftJavaAdapterSupportsCorrectProtocol(): void
    {
        $adapter = new MinecraftJavaAdapter();
        $this->assertTrue($adapter->supports('minecraft_java'));
        $this->assertFalse($adapter->supports('steam_a2s'));
    }

    public function testSteamA2SAdapterSupportsCorrectProtocol(): void
    {
        $adapter = new SteamA2SAdapter();
        $this->assertTrue($adapter->supports('steam_a2s'));
        $this->assertFalse($adapter->supports('minecraft_java'));
    }

    public function testMinecraftBedrockAdapterSupportsCorrectProtocol(): void
    {
        $adapter = new MinecraftBedrockAdapter();
        $this->assertTrue($adapter->supports('minecraft_bedrock'));
        $this->assertFalse($adapter->supports('minecraft_java'));
        $this->assertFalse($adapter->supports('steam_a2s'));
    }

    public function testGetAvailableProtocols(): void
    {
        $protocols = $this->registry->getAvailableProtocols();
        $this->assertArrayHasKey('minecraft_java', $protocols);
        $this->assertArrayHasKey('steam_a2s', $protocols);
        $this->assertArrayHasKey('minecraft_bedrock', $protocols);
        $this->assertCount(3, $protocols);
    }

    public function testGetProtocolLabel(): void
    {
        $this->assertEquals('Minecraft Java', $this->registry->getProtocolLabel('minecraft_java'));
        $this->assertEquals('Minecraft Bedrock', $this->registry->getProtocolLabel('minecraft_bedrock'));
        $this->assertEquals('Steam', $this->registry->getProtocolLabel('steam_a2s'));
        $this->assertEquals('unknown_protocol', $this->registry->getProtocolLabel('unknown_protocol'));
    }
}
