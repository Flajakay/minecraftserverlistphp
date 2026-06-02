<?php

namespace App\Core\Support;

class Config
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = self::parseKey($key);
        $config = self::load($file);

        if ($path === '') {
            return $config;
        }

        $value = $config;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function load(string $file): array
    {
        if (!isset(self::$cache[$file])) {
            $path = dirname(__DIR__, 3) . '/config/' . $file . '.php';
            self::$cache[$file] = file_exists($path) ? require $path : [];
        }

        return self::$cache[$file];
    }

    public static function clear(): void
    {
        self::$cache = [];
    }

    private static function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);
        return [$parts[0], $parts[1] ?? ''];
    }
}
