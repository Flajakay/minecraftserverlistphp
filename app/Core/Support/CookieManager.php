<?php

namespace App\Core\Support;

/**
 * Cookie helpers.
 *
 * Centralizes cookie option defaults (domain, secure, httponly, samesite).
 */
class CookieManager
{

    public static function set(string $name, string $value, int $days = 30): bool
    {
        if (headers_sent()) {
            return false;
        }

        $config = require __DIR__ . '/../../../config/app.php';
        $domain = parse_url($config['url'], PHP_URL_HOST);
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        $options = [
            'expires' => time() + ($days * 24 * 60 * 60),
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        return setcookie($name, $value, $options);
    }

    public static function get(string $name, string $default = null): ?string
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = filter_var($_COOKIE[$name], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $value ?: $default;
    }

    public static function delete(string $name): bool
    {
        if (headers_sent()) {
            return false;
        }

        $config = require __DIR__ . '/../../../config/app.php';
        $domain = parse_url($config['url'], PHP_URL_HOST);
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        return setcookie($name, '', $options);
    }

    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    public static function setSecure(string $name, string $value, int $days = 30): bool
    {
        // NOTE: This is encoding (base64), not encryption. Do not store secrets here.
        $encryptedValue = base64_encode($value);
        return self::set($name, $encryptedValue, $days);
    }

    public static function getSecure(string $name, string $default = null): ?string
    {
        // NOTE: This is decoding (base64), not decryption.
        $value = self::get($name, $default);
        if ($value === $default) {
            return $default;
        }
        return base64_decode($value);
    }
}

?>