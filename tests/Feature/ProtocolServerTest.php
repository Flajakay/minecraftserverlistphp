<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;

class ProtocolServerTest extends TestCase
{
    private $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUserId = User::create([
            'username' => 'protocoluser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'protocol@example.com',
            'name' => 'Protocol User',
            'type' => 0,
            'active' => 1
        ]);
    }

    public function testCanCreateMinecraftJavaServer(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'mc.example.com',
            'port' => 25565,
            'name' => 'Minecraft Server',
            'game_type' => 'minecraft',
            'protocol' => 'minecraft_java',
            'query_port' => 25565,
        ]);

        $server = Server::find($serverId);
        $this->assertNotNull($server);
        $this->assertEquals('minecraft', $server->game_type);
        $this->assertEquals('minecraft_java', $server->protocol);
        $this->assertEquals(25565, $server->query_port);
        $this->assertEquals('minecraft_java', $server->protocol);
    }

    public function testCanCreateMinecraftBedrockServer(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'bedrock.example.com',
            'port' => 19132,
            'name' => 'Bedrock Server',
            'game_type' => 'minecraft',
            'protocol' => 'minecraft_bedrock',
            'query_port' => 19132,
            'game_name' => 'Minecraft Bedrock',
        ]);

        $server = Server::find($serverId);
        $this->assertNotNull($server);
        $this->assertEquals('minecraft', $server->game_type);
        $this->assertEquals('minecraft_bedrock', $server->protocol);
        $this->assertEquals(19132, $server->port);
        $this->assertEquals(19132, $server->query_port);
        $this->assertEquals('Minecraft Bedrock', $server->game_name);
    }

    public function testCanCreateSteamA2SServer(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'steam.example.com',
            'port' => 27015,
            'name' => 'Steam Server',
            'game_type' => 'steam',
            'protocol' => 'steam_a2s',
            'query_port' => 27015,
            'game_app_id' => 730,
            'game_name' => 'CS2',
        ]);

        $server = Server::find($serverId);
        $this->assertNotNull($server);
        $this->assertEquals('steam', $server->game_type);
        $this->assertEquals('steam_a2s', $server->protocol);
        $this->assertEquals(27015, $server->query_port);
        $this->assertEquals(730, $server->game_app_id);
        $this->assertEquals('CS2', $server->game_name);
    }

    public function testUpdateProtocolStatusStoresProtocolFields(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'protocol-status.example.com',
            'port' => 27015,
            'name' => 'Protocol Status Test',
            'protocol' => 'steam_a2s',
        ]);

        Server::updateProtocolStatus($serverId, [
            'map_name' => 'de_dust2',
            'game_name' => 'Counter-Strike 2',
            'password_protected' => true,
            'protocol_metadata' => ['app_id' => 730, 'secure' => true],
        ]);

        $server = Server::find($serverId);
        $this->assertEquals('de_dust2', $server->map_name);
        $this->assertEquals('Counter-Strike 2', $server->game_name);
        $this->assertEquals(1, $server->password_protected);
        $this->assertNotNull($server->protocol_metadata);
    }

    public function testDefaultProtocolIsMinecraftJava(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'default-protocol.example.com',
            'port' => 25565,
            'name' => 'Default Protocol Server',
        ]);

        $server = Server::find($serverId);
        $this->assertEquals('minecraft', $server->game_type);
        $this->assertEquals('minecraft_java', $server->protocol);
        $this->assertEquals(25565, $server->query_port);
    }

    public function testFindByAddressWithProtocol(): void
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'multi-protocol.example.com',
            'port' => 25565,
            'name' => 'Multi-Protocol Server',
            'protocol' => 'minecraft_java',
        ]);

        $found = Server::findByAddress('multi-protocol.example.com', 25565, 'minecraft_java');
        $this->assertNotNull($found);
        $this->assertEquals($serverId, $found->id);

        $notFound = Server::findByAddress('multi-protocol.example.com', 25565, 'steam_a2s');
        $this->assertFalse($notFound);
    }

    public function testExistsRespectsProtocol(): void
    {
        Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'exists-protocol.example.com',
            'port' => 25565,
            'name' => 'Exists Test Server',
            'protocol' => 'minecraft_java',
        ]);

        $this->assertTrue(Server::exists('exists-protocol.example.com', 25565, 'minecraft_java'));
        $this->assertFalse(Server::exists('exists-protocol.example.com', 25565, 'steam_a2s'));
    }
}
