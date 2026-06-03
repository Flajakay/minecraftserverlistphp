<?php

namespace App\Models;

use App\Core\System\Database;

class Game
{
    public static function getAll()
    {
        return Database::fetchAll('SELECT * FROM games ORDER BY name ASC');
    }

    public static function getEnabled()
    {
        return Database::fetchAll('SELECT * FROM games WHERE enabled = 1 ORDER BY name ASC');
    }

    public static function getByProtocol(string $protocol)
    {
        return Database::fetchAll('SELECT * FROM games WHERE protocol = ? AND enabled = 1 ORDER BY name ASC', [$protocol]);
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM games WHERE id = ?', [$id]);
    }

    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return Database::insert('games', $data);
    }

    public static function update($id, $data)
    {
        return Database::update('games', $data, 'id = ?', [$id]);
    }

    public static function delete($id)
    {
        Database::query('UPDATE servers SET game_id = NULL WHERE game_id = ?', [$id]);
        return Database::delete('games', 'id = ?', [$id]);
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '')
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT g.*, COUNT(s.id) as server_count
                FROM games g
                LEFT JOIN servers s ON g.id = s.game_id
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (g.name LIKE ?)';
            $params[] = "%{$search}%";
        }

        $sql .= ' GROUP BY g.id ORDER BY g.name ASC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '')
    {
        $sql = 'SELECT COUNT(*) as count FROM games WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (name LIKE ?)';
            $params[] = "%{$search}%";
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }
}
