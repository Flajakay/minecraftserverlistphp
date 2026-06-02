<?php

namespace App\Core\Support;

class Env
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = null;
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        } else {
            $value = getenv($key);
        }

        if ($value === false || $value === null) {
            return $default;
        }

        return self::normalizeValue($value);
    }

    public static function writeValues(string $path, array $values): void
    {
        $content = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $linePattern = '/^' . preg_quote((string) $key, '/') . '=.*$/m';
            $newLine = $key . '=' . self::formatValue($value);

            if (preg_match($linePattern, $content)) {
                $content = preg_replace($linePattern, $newLine, $content);
            } elseif (trim($content) === '') {
                $content = $newLine . "\n";
            } else {
                $content = rtrim($content) . "\n" . $newLine . "\n";
            }
        }

        file_put_contents($path, rtrim($content) . "\n");
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (preg_match('/\A"(.*)"\z/', $value, $matches)) {
            return stripcslashes($matches[1]);
        }

        return $value;
    }

    private static function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        $value = str_replace(
            ["\\", '"', "\n", "\r"],
            ["\\\\", '\"', '\n', '\r'],
            (string) $value
        );

        return '"' . $value . '"';
    }
}
