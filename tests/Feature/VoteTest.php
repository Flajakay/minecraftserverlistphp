<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Models\Vote;
use App\Core\Features\Votes;
use App\Core\System\Database;

class VoteTest extends TestCase
{
    private $testUserId;
    private $serverId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->testUserId = User::create([
            'username' => 'votetester',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'vote@example.com',
            'name' => 'Vote Tester',
            'type' => 0,
            'active' => 1
        ]);

        // Create a test server
        $this->serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'vote.example.com',
            'port' => 25565,
            'name' => 'Vote Test Server'
        ]);

        // Make the server public so we can work with it normally
        Server::setPrivate($this->serverId, 0);
    }

    /**
     * Test a successful first-time vote casting.
     */
    public function testCanCastFirstVoteSuccessfully()
    {
        $ip = '203.0.113.100';

        // 1. Assert we are allowed to vote initially
        $this->assertTrue(Vote::canVote($this->serverId, $ip));

        // 2. Cast the vote
        $result = Votes::castVote($this->serverId, $ip, 'tester_player');
        $this->assertTrue($result['success']);
        $this->assertEquals('Vote recorded successfully', $result['message']);

        // 3. Verify points table incremented
        $votesCount = Database::fetch(
            'SELECT COUNT(*) as count FROM points WHERE server_id = ? AND ip = ? AND type = 1',
            [$this->serverId, $ip]
        );
        $this->assertEquals(1, $votesCount->count);

        // 4. Verify server votes count incremented in servers table
        $server = Server::find($this->serverId);
        $this->assertEquals(1, $server->votes);
    }

    /**
     * Test that casting a second vote from the same IP within 24 hours fails due to cooldown.
     */
    public function testCannotVoteDoubleWithinTwentyFourHours()
    {
        $ip = '203.0.113.101';

        // Cast first vote
        $result = Votes::castVote($this->serverId, $ip, 'player_one');
        $this->assertTrue($result['success']);

        // Assert we cannot vote again immediately
        $this->assertFalse(Vote::canVote($this->serverId, $ip));

        // Attempt second vote from same IP
        $secondResult = Votes::castVote($this->serverId, $ip, 'player_one');
        $this->assertFalse($secondResult['success']);
        $this->assertEquals('You can only vote once per day', $secondResult['message']);

        // Verify points log only has 1 record
        $pointsLogs = Database::fetchAll(
            'SELECT * FROM points WHERE server_id = ? AND ip = ? AND type = 1',
            [$this->serverId, $ip]
        );
        $this->assertCount(1, $pointsLogs);

        // Verify server votes column remains 1
        $server = Server::find($this->serverId);
        $this->assertEquals(1, $server->votes);
    }

    /**
     * Test that cooldown expires after 24 hours.
     */
    public function testCanVoteAgainAfterCooldownExpires()
    {
        $ip = '203.0.113.102';

        // Manually insert an old vote (25 hours ago) into points table
        // to simulate an expired cooldown
        $oldTimestamp = time() - (25 * 3600); // 25 hours ago
        Database::insert('points', [
            'type' => 1,
            'server_id' => $this->serverId,
            'ip' => $ip,
            'timestamp' => $oldTimestamp
        ]);

        // Manually increment the server's votes counter accordingly
        Server::addVote($this->serverId);

        // 1. Assert that the cooldown is expired and we can vote again
        $this->assertTrue(Vote::canVote($this->serverId, $ip));

        // 2. Cast new vote
        $result = Votes::castVote($this->serverId, $ip, 'player_two');
        $this->assertTrue($result['success']);

        // 3. Verify there are now 2 vote entries in the database
        $pointsLogs = Database::fetchAll(
            'SELECT * FROM points WHERE server_id = ? AND ip = ? AND type = 1',
            [$this->serverId, $ip]
        );
        $this->assertCount(2, $pointsLogs);

        // 4. Verify server votes count is now 2
        $server = Server::find($this->serverId);
        $this->assertEquals(2, $server->votes);
    }

    /**
     * Test tracking server hit (type = 0) details and rate limits.
     */
    public function testServerHitsRateLimits()
    {
        $ip = '203.0.113.103';

        // First hit should record successfully
        $this->assertNotFalse(Vote::recordHit($this->serverId, $ip));

        // Second hit from the same IP within 24 hours should be ignored
        $this->assertFalse(Vote::recordHit($this->serverId, $ip));

        // Hit from a different IP should succeed
        $otherIp = '203.0.113.104';
        $this->assertNotFalse(Vote::recordHit($this->serverId, $otherIp));

        // Verify points logs for hits exist
        $hitsLogs = Database::fetchAll(
            'SELECT * FROM points WHERE server_id = ? AND type = 0',
            [$this->serverId]
        );
        $this->assertCount(2, $hitsLogs);
    }
}
