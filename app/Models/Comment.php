<?php

namespace App\Models;

use App\Core\System\Database;


class Comment
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['type'])) {
            $data['type'] = 0;
        }
        
        return Database::insert('comments', $data);
    }

    public static function getServerComments($serverId, $limit = 100, $offset = 0)
    {
        $sql = 'SELECT c.*, u.username, u.name 
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.server_id = ? 
                ORDER BY c.created_at DESC
                LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        
        return Database::fetchAll($sql, [$serverId]);
    }

    public static function delete($id)
    {
        return Database::delete('comments', 'id = ?', [$id]);
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM comments WHERE id = ?', [$id]);
    }

    public static function getByType($serverId, $type, $limit = 100, $offset = 0)
    {
        $sql = 'SELECT c.*, u.username, u.name 
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.server_id = ? AND c.type = ? 
                ORDER BY c.created_at DESC
                LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        
        return Database::fetchAll($sql, [$serverId, $type]);
    }

    public static function countByType($serverId, $type)
    {
        $result = Database::fetch(
            'SELECT COUNT(*) as count FROM comments WHERE server_id = ? AND type = ?',
            [$serverId, $type]
        );
        return $result ? $result->count : 0;
    }

    public static function countServerComments($serverId)
    {
        $result = Database::fetch(
            'SELECT COUNT(*) as count FROM comments WHERE server_id = ?',
            [$serverId]
        );
        return $result ? $result->count : 0;
    }
}
