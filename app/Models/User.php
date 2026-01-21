<?php

namespace App\Models;

use App\Core\System\Database;

class User
{


    public static function find($id)
    {
        return Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByUsername($username)
    {
        return Database::fetch('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public static function findByEmail($email)
    {
        return Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function activate($email, $code)
    {
        return Database::update('users', 
            ['active' => 1, 'email_activation_code' => ''], 
            'email = ? AND email_activation_code = ?', 
            [$email, $code]
        );
    }

    public static function updatePassword($id, $password, $username = null)
    {
        $hashedPassword = Auth::hashPassword($password);
        return Database::update('users', ['password' => $hashedPassword], 'id = ?', [$id]);
    }

    public static function getServers($userId)
    {
        return Database::fetchAll('SELECT COUNT(*) as count FROM servers WHERE user_id = ?', [$userId]);
    }

    public static function isActive($username)
    {
        $user = self::findByUsername($username);
        return $user && $user->active == 1;
    }



    public static function getAllPaginated($page = 1, $limit = 20, $search = '', $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (username LIKE ? OR email LIKE ? OR name LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $sql .= ' AND type = ?';
            $params[] = $filters['type'];
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $sql .= ' AND active = ?';
            $params[] = $filters['active'];
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAll($search = '', $filters = [])
    {
        $sql = 'SELECT COUNT(*) as count FROM users WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (username LIKE ? OR email LIKE ? OR name LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $sql .= ' AND type = ?';
            $params[] = $filters['type'];
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $sql .= ' AND active = ?';
            $params[] = $filters['active'];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function update($id, $data)
    {
        return Database::update('users', $data, 'id = ?', [$id]);
    }

    public static function delete($id)
    {
        Database::query('UPDATE servers SET user_id = 1 WHERE user_id = ?', [$id]);
        Database::delete('comments', 'user_id = ?', [$id]);
        Database::delete('reports', 'user_id = ?', [$id]);
        return Database::delete('users', 'id = ?', [$id]);
    }

    public static function getAll($options = [])
    {
        $limit = $options['limit'] ?? null;
        $sql = 'SELECT id, username, name FROM users ORDER BY username ASC';
        
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
            return Database::fetchAll($sql);
        }
        
        return Database::fetchAll($sql);
    }
}
