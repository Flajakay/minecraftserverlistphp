<?php

namespace App\Core\Security;

/**
 * CSRF verification helper.
 *
 * The router calls `check()` for state-changing HTTP methods.
 */
class Csrf
{
    private static array $whitelist = [
        '/vote',
        '/banner'
    ];

    public static function verify($token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function isWhitelisted($uri): bool
    {
        // Exact match or prefix match (e.g., '/vote/...' ) are treated as whitelisted.
        foreach (self::$whitelist as $whitelistedUri) {
            if ($uri === $whitelistedUri || strpos($uri, $whitelistedUri) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function check($method, $uri): bool
    {
        // Only enforce CSRF on state-changing methods.
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        if (self::isWhitelisted($uri)) {
            return true;
        }

        $token = $_POST['csrf_token'] ?? '';
        return self::verify($token);
    }
}
