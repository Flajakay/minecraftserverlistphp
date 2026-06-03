<?php

namespace App\Core\Security;
use App\Core\System\Database;
use App\Models\LoginAttempt;

/**
 * Login attempt tracking and lockouts.
 *
 * Tracks failed attempts by a configurable identifier (e.g., username or IP) and sets a
 * temporary lockout timestamp when thresholds are exceeded.
 */
class LoginSecurity
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_DURATION_MINUTES = 15;
    const ATTEMPT_WINDOW_MINUTES = 10;
    const USE_EXPONENTIAL_BACKOFF = false;

    public static function isLockedOut($identifier, $type): bool
    {
        $attempt = LoginAttempt::findLockedOut($identifier, $type);

        if (!$attempt) {
            return false;
        }

        // Lockout is active if the timestamp is still in the future.
        if ($attempt->lockout_until > date('Y-m-d H:i:s')) {
            return true;
        }

        LoginAttempt::clearExpiredLockout($identifier, $type);
        return false;
    }

    public static function recordFailedAttempt($identifier, $type, $ip = null): void
    {
        // Use RateLimit's IP detection if not provided explicitly
        if ($ip === null) {
            $ip = self::getClientIp();
        }

        $existing = LoginAttempt::find($identifier, $type);

        if ($existing) {
            $newAttempts = $existing->attempts + 1;
            $shouldLock = $newAttempts >= self::MAX_ATTEMPTS;

            $lockoutUntil = null;
            if ($shouldLock) {
                $lockoutUntil = date('Y-m-d H:i:s', strtotime('+' . self::LOCKOUT_DURATION_MINUTES . ' minutes'));
            }

            LoginAttempt::update($existing->id, [
                'attempts' => $newAttempts,
                'last_attempt' => date('Y-m-d H:i:s'),
                'lockout_until' => $lockoutUntil,
                'ip_address' => $ip
            ]);
        } else {
            LoginAttempt::create([
                'identifier' => $identifier,
                'identifier_type' => $type,
                'attempts' => 1,
                'first_attempt' => date('Y-m-d H:i:s'),
                'last_attempt' => date('Y-m-d H:i:s'),
                'ip_address' => $ip
            ]);
        }

    }

    public static function clearFailedAttempts($identifier, $type): void
    {
        LoginAttempt::delete($identifier, $type);
    }

    public static function getRemainingAttempts($identifier, $type)
    {
        $attempt = LoginAttempt::find($identifier, $type);

        return $attempt ? max(0, self::MAX_ATTEMPTS - $attempt->attempts) : self::MAX_ATTEMPTS;
    }

    public static function getLockoutTimeRemaining($identifier, $type)
    {
        $attempt = LoginAttempt::find($identifier, $type);

        if (!$attempt || !$attempt->lockout_until) {
            return 0;
        }

        $remaining = strtotime($attempt->lockout_until) - time();
        return max(0, $remaining);
    }

    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function cleanupOldAttempts(): void
    {
        LoginAttempt::cleanup(self::ATTEMPT_WINDOW_MINUTES);
    }
}
