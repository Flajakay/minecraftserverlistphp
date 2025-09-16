<?php

namespace App\Models;

use App\Core\Database;


class Setting
{
    public static function get()
    {
        static $settings = null;
        
        if ($settings === null) {
            $settings = Database::fetch('SELECT * FROM settings WHERE id = 1');
        }
        
        return $settings;
    }

    public static function update($data)
    {
        return Database::update('settings', $data, 'id = 1');
    }

    public static function getValue($key, $default = null)
    {
        $settings = self::get();
        return $settings->$key ?? $default;
    }
}
