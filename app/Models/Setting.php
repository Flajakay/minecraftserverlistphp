<?php

namespace App\Models;

use App\Core\System\Database;


class Setting
{
    private static $settings = null;

    public static function get()
    {
        if (self::$settings === null) {
            self::$settings = Database::fetch('SELECT * FROM settings WHERE id = 1');
        }
        
        return self::$settings;
    }

    public static function update($data)
    {
        self::clearCache();
        return Database::update('settings', $data, 'id = 1');
    }

    public static function clearCache(): void
    {
        self::$settings = null;
    }

    public static function getValue($key, $default = null)
    {
        $settings = self::get();
        return $settings->$key ?? $default;
    }
}

