<?php

namespace App\Core;

use App\Core\CookieManager;

/**
 * Lightweight translation loader.
 *
 * Loads a single language file from `resources/languages/*.php` into memory and provides
 * dot-notation lookups via `Language::get()`.
 */
class Language
{
    private static $currentLanguage = 'english';
    private static $defaultLanguage = 'english';
    private static $availableLanguages = [];
    private static $translations = [];
    private static $languagesPath;
    private static $initialized = false;

    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$languagesPath = dirname(__DIR__, 2) . '/resources/languages/';
        self::loadAvailableLanguages();
        self::detectAndSetLanguage();
        self::loadCurrentLanguageTranslations();
        self::$initialized = true;
    }

    public static function get(string $key, string $default = null): string
    {
        $keys = explode('.', $key);
        $value = self::$translations;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default ?? $key;
            }
        }

        return is_string($value) ? $value : ($default ?? $key);
    }

    public static function getCurrentLanguage(): string
    {
        return self::$currentLanguage;
    }

    public static function getAvailableLanguages(): array
    {
        return self::$availableLanguages;
    }

    public static function getTranslations(): array
    {
        return self::$translations;
    }

    private static function loadAvailableLanguages(): void
    {
        if (!is_dir(self::$languagesPath)) {
            self::$availableLanguages = ['english'];
            return;
        }

        $files = glob(self::$languagesPath . "*.php");
        self::$availableLanguages = [];

        foreach ($files as $file) {
            if (is_readable($file)) {
                self::$availableLanguages[] = basename($file, '.php');
            }
        }

        if (empty(self::$availableLanguages)) {
            self::$availableLanguages = ['english'];
        }
    }

    private static function detectAndSetLanguage(): void
    {
        $language = self::$defaultLanguage;

        if (isset($_GET['language'])) {
            // Explicit language switch (persisted via cookie).
            $requested = self::sanitizeLanguage($_GET['language']);
            if ($requested && in_array($requested, self::$availableLanguages)) {
                $language = $requested;
                CookieManager::set('language', $language);
            }
        }

        else {
            // Fallback to previously selected language.
            $cookieLanguage = CookieManager::get('language');
            if ($cookieLanguage && in_array($cookieLanguage, self::$availableLanguages)) {
                $language = $cookieLanguage;
            }
        }

        self::$currentLanguage = $language;
    }

    private static function loadCurrentLanguageTranslations(): void
    {
        $file = self::$languagesPath . self::$currentLanguage . '.php';

        if (file_exists($file) && is_readable($file)) {
            $language = [];
            include $file;
            if (is_array($language)) {
                self::$translations = $language;
            }
        }
    }

    private static function sanitizeLanguage(string $input): ?string
    {
        $sanitized = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return preg_match('/^[a-zA-Z0-9_-]+$/', $sanitized) ? $sanitized : null;
    }
}

?>