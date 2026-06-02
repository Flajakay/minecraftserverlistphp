<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use PDOException;

class ServerTest extends TestCase
{
    private $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for server ownership tests
        $this->testUserId = User::create([
            'username' => 'serverowner',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'owner@example.com',
            'name' => 'Server Owner',
            'type' => 0,
            'active' => 1
        ]);
    }

    /**
     * Test creating a server with defaults.
     */
    public function testCanCreateServerWithDefaultValues()
    {
        $serverData = [
            'user_id' => $this->testUserId,
            'category_id' => 1, // Survival category (seeded in schema.sql)
            'address' => 'play.example.com',
            'port' => 25565,
            'name' => 'Survival Server',
            'description' => 'A great survival server'
        ];

        $serverId = Server::create($serverData);
        $this->assertGreaterThan(0, $serverId);

        $server = Server::find($serverId);
        $this->assertNotNull($server);
        $this->assertEquals('play.example.com', $server->address);
        $this->assertEquals(25565, $server->port);
        $this->assertEquals('Survival Server', $server->name);
        
        // Assert defaults are correctly set by Server::create
        $this->assertEquals(1, $server->status);
        $this->assertEquals(0, $server->votes);
        $this->assertEquals(0, $server->highlight);
        $this->assertEquals(1, $server->private);
        $this->assertEquals(1, $server->active);
    }

    /**
     * Test finding a server by address and port (including joined owner's username).
     */
    public function testCanFindServerByAddressAndPort()
    {
        $serverData = [
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'pvp.example.com',
            'port' => 25566,
            'name' => 'PvP Server'
        ];

        $serverId = Server::create($serverData);

        // Fetch using address/port
        $server = Server::findByAddress('pvp.example.com', 25566);
        $this->assertNotNull($server);
        $this->assertEquals($serverId, $server->id);
        $this->assertEquals('serverowner', $server->owner_username);
    }

    /**
     * Test database unique index prevents duplicate address and port.
     */
    public function testCannotCreateDuplicateServerHostAndPort()
    {
        $serverData1 = [
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'dup.example.com',
            'port' => 25565,
            'name' => 'Server One'
        ];

        $serverData2 = [
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'dup.example.com',
            'port' => 25565,
            'name' => 'Server Two'
        ];

        Server::create($serverData1);

        // This second insertion must trigger a unique constraint violation exception
        $this->expectException(PDOException::class);
        Server::create($serverData2);
    }

    /**
     * Test owner helper methods.
     */
    public function testOwnerHelpers()
    {
        $serverData = [
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'helpers.example.com',
            'port' => 25565,
            'name' => 'Helpers Test'
        ];

        $serverId = Server::create($serverData);
        $server = Server::find($serverId);

        $this->assertEquals($this->testUserId, Server::getEffectiveOwnerUserId($server));
        $this->assertTrue(Server::isEffectiveOwner($server, $this->testUserId));
        $this->assertFalse(Server::isEffectiveOwner($server, 999999));
    }
}
