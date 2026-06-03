<?php

namespace App\Models;

use App\Core\System\Database;

class LoginAttempt
{
    public static function find($identifier, $type)
    {
        return Database::fetch(
            'SELECT * FROM login_attempts WHERE identifier = ? AND identifier_type = ?',
            [$identifier, $type]
        );
    }

    public static function findLockedOut($identifier, $type)
    {
        return Database::fetch(
            'SELECT * FROM login_attempts WHERE identifier = ? AND identifier_type = ? AND lockout_until IS NOT NULL',
            [$identifier, $type]
        );
    }

    public static function findById($id)
    {
        return Database::fetch('SELECT * FROM login_attempts WHERE id = ?', [$id]);
    }

    public static function create($data)
    {
        return Database::insert('login_attempts', $data);
    }

    public static function update($id, $data)
    {
        return Database::update('login_attempts', $data, 'id = ?', [$id]);
    }

    public static function clearExpiredLockout($identifier, $type): void
    {
        Database::update('login_attempts',
            ['lockout_until' => null],
            'identifier = ? AND identifier_type = ? AND lockout_until IS NOT NULL AND lockout_until <= ?',
            [$identifier, $type, date('Y-m-d H:i:s')]
        );
    }

    public static function delete($identifier, $type)
    {
        return Database::delete('login_attempts', 'identifier = ? AND identifier_type = ?', [$identifier, $type]);
    }

    public static function cleanup($minutes = null): void
    {
        $window = $minutes ?? 10;
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $window . ' minutes'));
        Database::query('DELETE FROM login_attempts WHERE last_attempt < ? AND lockout_until IS NULL', [$cutoff]);
    }
}
