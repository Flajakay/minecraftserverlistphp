<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Integrations\GameServers\MinecraftJavaAdapter;
use App\Core\Integrations\GameServers\ServerStatusResult;

class MinecraftJavaAdapterTest extends TestCase
{
    private MinecraftJavaAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new MinecraftJavaAdapter();
    }

    public function testSupportsMinecraftJava(): void
    {
        $this->assertTrue($this->adapter->supports('minecraft_java'));
        $this->assertFalse($this->adapter->supports('steam_a2s'));
    }

    public function testServerStatusResultOfflineFactory(): void
    {
        $result = ServerStatusResult::offline();
        $this->assertFalse($result->online);
        $this->assertEquals(0, $result->players);
        $this->assertEquals(0, $result->maxPlayers);
        $this->assertEquals('offline', $result->version);
    }

    public function testServerStatusResultToArray(): void
    {
        $result = new ServerStatusResult(
            online: true,
            players: 10,
            maxPlayers: 100,
            version: '1.20.4',
            name: 'Test Server',
            map: 'world',
            game: 'minecraft',
            metadata: ['description' => 'A test server']
        );

        $array = $result->toArray();
        $this->assertTrue($array['online']);
        $this->assertEquals(10, $array['players']);
        $this->assertEquals(100, $array['max_players']);
        $this->assertEquals('1.20.4', $array['version']);
        $this->assertEquals('Test Server', $array['name']);
        $this->assertEquals('world', $array['map']);
        $this->assertEquals('minecraft', $array['game']);
        $this->assertEquals(['description' => 'A test server'], $array['metadata']);
    }

    public function testServerStatusResultFromArray(): void
    {
        $data = [
            'online' => true,
            'players' => 25,
            'max_players' => 50,
            'version' => '1.21',
            'name' => 'Awesome Server',
            'map' => 'overworld',
            'game' => 'minecraft',
            'metadata' => ['favicon' => 'data:image/png;base64,...']
        ];

        $result = ServerStatusResult::fromArray($data);
        $this->assertTrue($result->online);
        $this->assertEquals(25, $result->players);
        $this->assertEquals(50, $result->maxPlayers);
        $this->assertEquals('1.21', $result->version);
        $this->assertEquals('Awesome Server', $result->name);
    }
}
