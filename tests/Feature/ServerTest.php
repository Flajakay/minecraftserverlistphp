<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\ServerCategory;
use App\Core\System\SiteSettings;
use App\Core\System\Database;

class ServerTest extends TestCase
{
    private $testUserId;
    private $adminUserId;
    private $claimUserId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard test users
        $this->testUserId = User::create([
            'username' => 'regularuser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'regular@example.com',
            'name' => 'Regular User',
            'type' => 0,
            'active' => 1
        ]);

        $this->claimUserId = User::create([
            'username' => 'claimuser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'claim@example.com',
            'name' => 'Claim User',
            'type' => 0,
            'active' => 1
        ]);

        $this->adminUserId = User::create([
            'username' => 'localadmin',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'localadmin@example.com',
            'name' => 'Local Admin User',
            'type' => 2, // Admin type
            'active' => 1
        ]);
    }

    /**
     * Test updating server online status and metadata.
     */
    public function testCanUpdateServerStatusAndMetadata()
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'status.example.com',
            'port' => 25565,
            'name' => 'Status Server'
        ]);

        // Update server stats
        $updated = Server::updateStatus($serverId, 1, 12, 100, '1.20.4');
        $this->assertTrue($updated->rowCount() > 0);

        // Re-fetch and assert updated status
        $server = Server::find($serverId);
        $this->assertEquals(1, $server->status);
        $this->assertEquals(12, $server->players);
        $this->assertEquals(100, $server->max_players);
        $this->assertEquals('1.20.4', $server->version);
        $this->assertNotNull($server->last_check);
    }

    /**
     * Test toggling highlight, private, and active flags on the server.
     */
    public function testCanToggleServerStates()
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'toggles.example.com',
            'port' => 25565,
            'name' => 'Toggle Server'
        ]);

        // Assert starting state (defaults inside Server::create)
        $server = Server::find($serverId);
        $this->assertEquals(1, $server->private);
        $this->assertEquals(1, $server->active);
        $this->assertEquals(0, $server->highlight);

        // Toggle states
        Server::setPrivate($serverId, 0);
        Server::setActive($serverId, 0);
        Server::updateHighlight($serverId, 1);

        // Re-fetch and check
        $server = Server::find($serverId);
        $this->assertEquals(0, $server->private);
        $this->assertEquals(0, $server->active);
        $this->assertEquals(1, $server->highlight);
    }

    /**
     * Test server category bindings and database ON DELETE CASCADE releases.
     */
    public function testCategoryBindingAndDeletions()
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'cat.example.com',
            'port' => 25565,
            'name' => 'Cat Test Server'
        ]);

        // Bind category 1 (Survival) and 2 (Creative) with 1 as primary
        $bindSuccess = Server::setCategories($serverId, [1, 2], 1);
        $this->assertTrue($bindSuccess);

        // Verify categories exist
        $categories = Server::getCategories($serverId);
        $this->assertCount(2, $categories);

        $primary = Server::getPrimaryCategory($serverId);
        $this->assertNotNull($primary);
        $this->assertEquals(1, $primary->category_id);
        $this->assertEquals(1, $primary->is_primary);

        // Delete server and check database ON DELETE CASCADE releases bindings
        Server::delete($serverId);

        $serverCategoriesCount = Database::fetch('SELECT COUNT(*) as count FROM server_categories WHERE server_id = ?', [$serverId]);
        $this->assertEquals(0, $serverCategoriesCount->count);
    }

    /**
     * Test the full server ownership claim and verification lifecycle.
     */
    public function testServerClaimLifecycle()
    {
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'claim.example.com',
            'port' => 25565,
            'name' => 'Claim Test Server'
        ]);

        $server = Server::find($serverId);
        $this->assertEquals(0, $server->verification_status);

        // 1. Start claim
        $token = 'claim_token_123';
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        Server::startClaim($serverId, $this->claimUserId, $token, $expiresAt);

        $server = Server::find($serverId);
        $this->assertEquals(1, $server->verification_status);
        $this->assertEquals($this->claimUserId, $server->verification_requested_by_user_id);
        $this->assertEquals($token, $server->verification_token);

        // 2. Cancel the claim while it is still pending
        Server::cancelClaim($serverId);
        $server = Server::find($serverId);
        $this->assertEquals(0, $server->verification_status);
        $this->assertNull($server->verification_requested_by_user_id);
        $this->assertNull($server->verification_token);

        // 3. Restart claim to proceed with verification checks
        Server::startClaim($serverId, $this->claimUserId, $token, $expiresAt);

        // 4. Verification attempt and cooldown checks
        $this->assertTrue(Server::canAttemptVerification($serverId));

        // Mark attempt
        Server::markVerificationAttempt($serverId);
        
        // Cooldown of 30 seconds should prevent immediate second attempt
        $this->assertFalse(Server::canAttemptVerification($serverId, 30));

        // 5. Mark server verified
        Server::markVerified($serverId, $this->claimUserId);
        $server = Server::find($serverId);
        $this->assertEquals(2, $server->verification_status);
        $this->assertEquals($this->claimUserId, $server->verified_owner_user_id);
        $this->assertNull($server->verification_token);
    }

    /**
     * Test resetting votes on all servers and verifying audit logging integrations.
     */
    public function testVoteResetsAndAuditLogging()
    {
        // Set fake REMOTE_ADDR for Audit Log details
        $_SERVER['REMOTE_ADDR'] = '192.0.2.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit-Agent';

        // Create a server
        $serverId = Server::create([
            'user_id' => $this->testUserId,
            'category_id' => 1,
            'address' => 'vote.example.com',
            'port' => 25565,
            'name' => 'Vote Reset Test Server'
        ]);

        // Add votes
        Server::addVote($serverId);
        Server::addVote($serverId);
        
        $server = Server::find($serverId);
        $this->assertEquals(2, $server->votes);

        // Reset votes using SiteSettings
        $result = SiteSettings::resetVotes($this->adminUserId);
        $this->assertTrue($result['success']);

        // Check votes are reset to 0
        $serverAfterReset = Server::find($serverId);
        $this->assertEquals(0, $serverAfterReset->votes);

        // Check audit log entry exists
        $logs = AuditLog::getAll(1, 5);
        $this->assertNotEmpty($logs);
        
        $resetLog = null;
        foreach ($logs as $log) {
            if ($log->action === 'reset_votes') {
                $resetLog = $log;
                break;
            }
        }

        $this->assertNotNull($resetLog);
        $this->assertEquals('servers', $resetLog->table_name);
        $this->assertEquals($this->adminUserId, $resetLog->user_id);
        $this->assertEquals('192.0.2.1', $resetLog->ip_address);
        $this->assertEquals('PHPUnit-Agent', $resetLog->user_agent);

        // Clean up mock globals
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}
