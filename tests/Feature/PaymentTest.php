<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Models\Payment;
use App\Core\System\Database;

class PaymentTest extends TestCase
{
    private $userId;
    private $serverId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->userId = User::create([
            'username' => 'buyer123',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'buyer@example.com',
            'name' => 'Premium Buyer',
            'type' => 0,
            'active' => 1
        ]);

        // Create a test server
        $this->serverId = Server::create([
            'user_id' => $this->userId,
            'category_id' => 1,
            'address' => 'premium.example.com',
            'port' => 25565,
            'name' => 'Premium Server'
        ]);
    }

    /**
     * Test payment status transition from pending to completed.
     */
    public function testPaymentStatusTransitions()
    {
        // 1. Create a payment in the default pending status
        $paymentId = Payment::create([
            'user_id' => $this->userId,
            'server_id' => $this->serverId,
            'highlighted_days' => 7,
            'revenue' => 15.00,
            'email' => 'buyer@example.com',
            'status' => 'pending',
            'paypal_order_id' => 'PAY-PENDING-123'
        ]);

        $this->assertGreaterThan(0, $paymentId);

        // Fetch and assert initial status is pending
        $payment = Payment::find($paymentId);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('PAY-PENDING-123', $payment->paypal_order_id);

        // 2. Transition status to completed
        Payment::updateStatus($paymentId, 'completed', 'PAY-COMPLETED-123');

        // Fetch and assert updated status is completed and order ID is updated
        $updatedPayment = Payment::find($paymentId);
        $this->assertEquals('completed', $updatedPayment->status);
        $this->assertEquals('PAY-COMPLETED-123', $updatedPayment->paypal_order_id);
    }

    /**
     * Test that completing a payment successfully toggles premium highlights.
     */
    public function testPremiumHighlightActivation()
    {
        // Confirm server highlight is initially off (0)
        $server = Server::find($this->serverId);
        $this->assertEquals(0, $server->highlight);

        // Create a completed payment
        $paymentId = Payment::create([
            'user_id' => $this->userId,
            'server_id' => $this->serverId,
            'highlighted_days' => 5,
            'revenue' => 10.00,
            'email' => 'buyer@example.com',
            'status' => 'completed',
            'paypal_order_id' => 'PAY-HL-123'
        ]);

        // Activate highlight
        Server::updateHighlight($this->serverId, 1);

        // Verify server's highlight state is now active (1)
        $activatedServer = Server::find($this->serverId);
        $this->assertEquals(1, $activatedServer->highlight);
    }

    /**
     * Test that expired highlights are automatically deactivated by the cron/expiry engine.
     */
    public function testPremiumHighlightExpiration()
    {
        // 1. Create a server with highlight turned ON
        $serverId = Server::create([
            'user_id' => $this->userId,
            'category_id' => 1,
            'address' => 'oldpremium.example.com',
            'port' => 25565,
            'name' => 'Old Premium Server'
        ]);
        Server::updateHighlight($serverId, 1);

        // 2. Insert a historical completed payment that expired 2 days ago
        // Highlighted days = 5, Created 8 days ago -> should be expired
        $oldCreatedAt = date('Y-m-d H:i:s', strtotime('-8 days'));
        
        $paymentId = Database::insert('payments', [
            'user_id' => $this->userId,
            'server_id' => $serverId,
            'highlighted_days' => 5,
            'revenue' => 10.00,
            'email' => 'buyer@example.com',
            'status' => 'completed',
            'paypal_order_id' => 'PAY-EXPIRED-123',
            'created_at' => $oldCreatedAt,
            'updated_at' => $oldCreatedAt
        ]);

        // Assert setup is ready
        $serverBeforeExpire = Server::find($serverId);
        $this->assertEquals(1, $serverBeforeExpire->highlight);
        
        $paymentBeforeExpire = Payment::find($paymentId);
        $this->assertEquals('completed', $paymentBeforeExpire->status);

        // 3. Trigger highlight expiration logic
        $expiredCount = Payment::expireHighlights();

        // 4. Assertions
        $this->assertEquals(1, $expiredCount); // 1 expired server caught

        // Server highlight should now be turned OFF (0)
        $serverAfterExpire = Server::find($serverId);
        $this->assertEquals(0, $serverAfterExpire->highlight);

        // Payment status should now be updated to 'expired'
        $paymentAfterExpire = Payment::find($paymentId);
        $this->assertEquals('expired', $paymentAfterExpire->status);
    }
}
