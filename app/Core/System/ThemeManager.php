<?php

namespace App\Core\System;

use App\Models\Setting;

class ThemeManager
{
    private static array $themes = [];
    private static ?string $activeSlug = null;
    private static bool $discovered = false;

    private static function basePath(): string
    {
        return dirname(__DIR__, 3);
    }

    private static function themesDir(): string
    {
        return self::basePath() . '/resources/themes';
    }

    private static function viewsDir(): string
    {
        return self::basePath() . '/resources/views';
    }

    private static function publicThemesDir(): string
    {
        return self::basePath() . '/public/themes';
    }

    public static function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9_-]+$/', $slug) === 1;
    }

    public static function clearCache(): void
    {
        self::$themes = [];
        self::$activeSlug = null;
        self::$discovered = false;
    }

    public static function availableThemes(): array
    {
        self::discover();
        return self::$themes;
    }

    public static function hasTheme(string $slug): bool
    {
        self::discover();
        return isset(self::$themes[$slug]);
    }

    public static function activeSlug(): string
    {
        if (self::$activeSlug !== null) {
            return self::$activeSlug;
        }

        $saved = Setting::getValue('active_theme', 'default');

        if (self::isValidSlug($saved) && self::hasTheme($saved)) {
            self::$activeSlug = $saved;
        } else {
            self::$activeSlug = 'default';
        }

        return self::$activeSlug;
    }

    public static function resolveView(string $name): string
    {
        $path = str_replace('.', '/', $name) . '.php';

        $active = self::activeSlug();

        $themeView = self::themesDir() . '/' . $active . '/views/' . $path;
        if (file_exists($themeView)) {
            return $themeView;
        }

        $fallback = self::viewsDir() . '/' . $path;
        if (file_exists($fallback)) {
            return $fallback;
        }

        throw new \Exception("View $name not found");
    }

    public static function themeAssetUrl(string $path, ?string $slug = null): string
    {
        $slug = $slug ?? self::activeSlug();
        $baseUrl = rtrim(\App\Core\Support\Config::get('app.url', 'http://localhost:8080/'), '/');
        return $baseUrl . '/themes/' . $slug . '/' . ltrim($path, '/');
    }

    public static function activeCssAssets(): array
    {
        return self::themeAssets('css');
    }

    public static function activeJsAssets(): array
    {
        return self::themeAssets('js');
    }

    private static function themeAssets(string $type): array
    {
        $slug = self::activeSlug();
        $meta = self::loadMetadata($slug);

        if ($meta === null || !isset($meta['assets'][$type])) {
            return [];
        }

        return array_map(function ($asset) use ($slug) {
            return self::themeAssetUrl($asset, $slug);
        }, $meta['assets'][$type]);
    }

    private static function loadMetadata(string $slug): ?array
    {
        $path = self::themesDir() . '/' . $slug . '/theme.json';
        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (!is_array($data) || empty($data['slug'])) {
            return null;
        }

        return $data;
    }

    private static function discover(): void
    {
        if (self::$discovered) {
            return;
        }

        self::$discovered = true;
        self::$themes = [];

        $dir = self::themesDir();
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!self::isValidSlug($item)) {
                continue;
            }

            $metaPath = $dir . '/' . $item . '/theme.json';
            if (!file_exists($metaPath)) {
                continue;
            }

            $content = file_get_contents($metaPath);
            $data = json_decode($content, true);

            if (!is_array($data) || ($data['slug'] ?? '') !== $item) {
                continue;
            }

            self::$themes[$item] = $data;
        }
    }
}
