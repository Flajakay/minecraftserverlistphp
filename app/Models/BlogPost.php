<?php

namespace App\Models;

use App\Core\System\Database;

class BlogPost
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return Database::insert('blog_posts', $data);
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM blog_posts WHERE id = ?', [$id]);
    }

    public static function getServerPosts($serverId, $limit = null, $offset = 0)
    {
        $sql = 'SELECT bp.*, u.username, u.name 
                FROM blog_posts bp 
                LEFT JOIN users u ON bp.user_id = u.id 
                WHERE bp.server_id = ? 
                ORDER BY bp.created_at DESC';
        
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$offset . ', ' . (int)$limit;
        }
        
        return Database::fetchAll($sql, [$serverId]);
    }

    public static function countServerPosts($serverId)
    {
        $result = Database::fetch(
            'SELECT COUNT(*) as count FROM blog_posts WHERE server_id = ?',
            [$serverId]
        );
        return $result ? $result->count : 0;
    }

    public static function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::update('blog_posts', $data, 'id = ?', [$id]);
    }

    public static function delete($id)
    {
        return Database::delete('blog_posts', 'id = ?', [$id]);
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '', $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT bp.*, u.username, u.name, s.name as server_name, s.address, s.port 
                FROM blog_posts bp 
                LEFT JOIN users u ON bp.user_id = u.id 
                LEFT JOIN servers s ON bp.server_id = s.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (bp.title LIKE ? OR bp.content LIKE ? OR u.username LIKE ? OR s.name LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['server_id']) && $filters['server_id'] !== '') {
            $sql .= ' AND bp.server_id = ?';
            $params[] = $filters['server_id'];
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $sql .= ' AND bp.user_id = ?';
            $params[] = $filters['user_id'];
        }

        $sql .= ' ORDER BY bp.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '', $filters = [])
    {
        $sql = 'SELECT COUNT(*) as count FROM blog_posts bp 
                LEFT JOIN users u ON bp.user_id = u.id 
                LEFT JOIN servers s ON bp.server_id = s.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (bp.title LIKE ? OR bp.content LIKE ? OR u.username LIKE ? OR s.name LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['server_id']) && $filters['server_id'] !== '') {
            $sql .= ' AND bp.server_id = ?';
            $params[] = $filters['server_id'];
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $sql .= ' AND bp.user_id = ?';
            $params[] = $filters['user_id'];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }
}
