<?php

namespace App\Models;

use App\Core\Database;

class LoginAttempt
{
    public static function find($identifier, $type)
    {
        return Database::fetch(
            'SELECT * FROM login_attempts WHERE identifier = ? AND identifier_type = ?',
            [$identifier, $type]
        );
    }

    public static function create($data)
    {
        return Database::insert('login_attempts', $data);
    }

    public static function update($id, $data)
    {
        return Database::update('login_attempts', $data, 'id = ?', [$id]);
    }

    public static function delete($identifier, $type)
    {
        return Database::delete('login_attempts', 'identifier = ? AND identifier_type = ?', [$identifier, $type]);
    }

    public static function cleanup()
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        Database::query('DELETE FROM login_attempts WHERE last_attempt < ? AND lockout_until IS NULL', [$cutoff]);
    }
}
