<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Core\Security\RateLimit;
use App\Core\Security\FileStorage;
use App\Core\System\Database;

class RateLimitTest extends TestCase
{
    private $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we reset the singleton before each test to load fresh config
        $this->resetRateLimitSingleton();
        
        // Save the baseline configuration loaded from config/ratelimit.php
        $this->originalConfig = require dirname(__DIR__, 2) . '/config/ratelimit.php';
    }

    protected function tearDown(): void
    {
        // Reset singleton to original state
        $this->resetRateLimitSingleton();

        // Clean up temporary rate limit files
        $storageDir = dirname(__DIR__, 2) . '/storage/framework/rate_limits';
        if (is_dir($storageDir)) {
            $files = glob($storageDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        parent::tearDown();
    }

    /**
     * Resets the RateLimit singleton instance using reflection.
     */
    private function resetRateLimitSingleton()
    {
        $reflector = new \ReflectionClass(RateLimit::class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    /**
     * Configures the rate limiter instance in-memory for the test run.
     */
    private function setTestConfig(array $newConfig)
    {
        $merged = array_replace_recursive($this->originalConfig, $newConfig);
        RateLimit::getInstance()->setConfig($merged);
    }

    /**
     * Test that rate limiting can be disabled globally.
     */
    public function testRateLimitingCanBeDisabledGlobally()
    {
        // 1. Disable rate limiting globally in config
        $this->setTestConfig([
            'enabled' => false,
            'rules' => [
                'auth' => [
                    'limit' => 1, // Only 1 allowed normally
                    'window' => 60
                ]
            ]
        ]);

        $limiter = RateLimit::getInstance();

        // 2. Perform multiple request checks (should always pass since disabled)
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));
    }

    /**
     * Test that the Golden Balance rule for authentication limits works.
     * Allowed: 5 requests. 6th is blocked.
     */
    public function testAuthenticationRateLimitEnforcement()
    {
        // 1. Ensure rate limiting is enabled with our golden balance rule
        $this->setTestConfig([
            'enabled' => true,
            'rules' => [
                'auth' => [
                    'routes' => ['/login'],
                    'limit' => 5,
                    'window' => 10, // 10 second window for test
                    'key_type' => 'ip'
                ]
            ]
        ]);

        // Mock remote address so keys are consistent
        $_SERVER['REMOTE_ADDR'] = '203.0.113.220';

        $limiter = RateLimit::getInstance();

        // 2. Make 5 requests (should succeed)
        for ($i = 1; $i <= 5; $i++) {
            $this->assertTrue(
                $limiter->checkRequest('/login', 'POST'),
                "Request {$i} should be allowed under the limit of 5"
            );
        }

        // 3. Make 6th request (should fail!)
        $this->assertFalse(
            $limiter->checkRequest('/login', 'POST'),
            "6th request should be blocked by the rate limiter"
        );

        unset($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Test rate limit bypasses for whitelist IPs.
     */
    public function testIpBypassWhitelist()
    {
        $this->setTestConfig([
            'enabled' => true,
            'rules' => [
                'auth' => [
                    'routes' => ['/login'],
                    'limit' => 1,
                    'window' => 60
                ]
            ],
            'bypass' => [
                'ips' => ['127.0.0.1', '192.0.2.99']
            ]
        ]);

        // 1. Check blocked for non-bypass IP
        $_SERVER['REMOTE_ADDR'] = '203.0.113.221';
        $limiter = RateLimit::getInstance();
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));
        $this->assertFalse($limiter->checkRequest('/login', 'POST'));

        // 2. Reset singleton for fresh state and set bypass IP
        $this->resetRateLimitSingleton();
        $this->setTestConfig([
            'enabled' => true,
            'rules' => [
                'auth' => [
                    'routes' => ['/login'],
                    'limit' => 1,
                    'window' => 60
                ]
            ],
            'bypass' => [
                'ips' => ['127.0.0.1', '192.0.2.99']
            ]
        ]);
        
        $_SERVER['REMOTE_ADDR'] = '192.0.2.99'; // Whitelisted
        $limiter = RateLimit::getInstance();

        // Should never be blocked
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));
        $this->assertTrue($limiter->checkRequest('/login', 'POST'));

        unset($_SERVER['REMOTE_ADDR']);
    }
}
