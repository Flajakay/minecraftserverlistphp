<?php

namespace App\Core;

class LoginSecurity
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_DURATION_MINUTES = 15;
    const ATTEMPT_WINDOW_MINUTES = 10;
    const USE_EXPONENTIAL_BACKOFF = false;

    public static function isLockedOut($identifier, $type)
    {
        $attempt = Database::fetch(
            'SELECT lockout_until FROM login_attempts WHERE identifier = ? AND identifier_type = ? AND lockout_until IS NOT NULL',
            [$identifier, $type]
        );

        if (!$attempt) {
            return false;
        }

        if ($attempt->lockout_until > date('Y-m-d H:i:s')) {
            return true;
        }

        self::clearExpiredLockout($identifier, $type);
        return false;
    }

    public static function recordFailedAttempt($identifier, $type, $ip)
    {
        $existing = Database::fetch(
            'SELECT id, attempts, first_attempt FROM login_attempts WHERE identifier = ? AND identifier_type = ?',
            [$identifier, $type]
        );

        if ($existing) {
            $newAttempts = $existing->attempts + 1;
            $shouldLock = $newAttempts >= self::MAX_ATTEMPTS;

            $lockoutUntil = null;
            if ($shouldLock) {
                $lockoutUntil = date('Y-m-d H:i:s', strtotime('+' . self::LOCKOUT_DURATION_MINUTES . ' minutes'));
            }

            Database::update('login_attempts',
                [
                    'attempts' => $newAttempts,
                    'last_attempt' => date('Y-m-d H:i:s'),
                    'lockout_until' => $lockoutUntil
                ],
                'id = ?',
                [$existing->id]
            );
        } else {
            Database::insert('login_attempts', [
                'identifier' => $identifier,
                'identifier_type' => $type,
                'attempts' => 1,
                'first_attempt' => date('Y-m-d H:i:s'),
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        }

    }

    public static function clearFailedAttempts($identifier, $type)
    {
        Database::delete('login_attempts', 'identifier = ? AND identifier_type = ?', [$identifier, $type]);
    }

    public static function getRemainingAttempts($identifier, $type)
    {
        $attempt = Database::fetch(
            'SELECT attempts FROM login_attempts WHERE identifier = ? AND identifier_type = ?',
            [$identifier, $type]
        );

        return $attempt ? max(0, self::MAX_ATTEMPTS - $attempt->attempts) : self::MAX_ATTEMPTS;
    }

    public static function getLockoutTimeRemaining($identifier, $type)
    {
        $attempt = Database::fetch(
            'SELECT lockout_until FROM login_attempts WHERE identifier = ? AND identifier_type = ?',
            [$identifier, $type]
        );

        if (!$attempt || !$attempt->lockout_until) {
            return 0;
        }

        $remaining = strtotime($attempt->lockout_until) - time();
        return max(0, $remaining);
    }

    private static function clearExpiredLockout($identifier, $type)
    {
        Database::update('login_attempts',
            ['lockout_until' => null],
            'identifier = ? AND identifier_type = ? AND lockout_until <= ?',
            [$identifier, $type, date('Y-m-d H:i:s')]
        );
    }

    public static function cleanupOldAttempts()
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::ATTEMPT_WINDOW_MINUTES . ' minutes'));
        Database::query('DELETE FROM login_attempts WHERE last_attempt < ? AND lockout_until IS NULL', [$cutoff]);
    }
}
