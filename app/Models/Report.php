<?php

namespace App\Models;

use App\Core\Database;

class Report
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return Database::insert('reports', $data);
    }

    public static function getAll()
    {
        return Database::fetchAll(
            'SELECT r.*, u.username as reporter_name 
             FROM reports r 
             LEFT JOIN users u ON r.user_id = u.id 
             ORDER BY r.created_at DESC'
        );
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM reports WHERE id = ?', [$id]);
    }

    public static function delete($id)
    {
        return Database::delete('reports', 'id = ?', [$id]);
    }

    public static function exists($userId, $type, $reportedId)
    {
        $report = Database::fetch(
            'SELECT * FROM reports WHERE user_id = ? AND type = ? AND reported_id = ?',
            [$userId, $type, $reportedId]
        );
        
        return $report !== false;
    }

    public static function getReported($type, $reportedId)
    {
        return Database::fetchAll(
            'SELECT r.*, u.username as reporter_name 
             FROM reports r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.type = ? AND r.reported_id = ? 
             ORDER BY r.created_at DESC',
            [$type, $reportedId]
        );
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '', $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT r.*, u.username as reporter_name,
                       CASE 
                           WHEN r.type = 1 THEN (SELECT username FROM users WHERE id = r.reported_id)
                           WHEN r.type = 2 THEN (SELECT name FROM servers WHERE id = r.reported_id)
                       END as reported_name
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (r.message LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $sql .= ' AND r.type = ?';
            $params[] = $filters['type'];
        }

        $sql .= ' ORDER BY r.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '', $filters = [])
    {
        $sql = 'SELECT COUNT(*) as count FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (r.message LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $sql .= ' AND r.type = ?';
            $params[] = $filters['type'];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function getWithDetails($id)
    {
        return Database::fetch(
            'SELECT r.*, u.username as reporter_name,
                    CASE 
                        WHEN r.type = 1 THEN (SELECT username FROM users WHERE id = r.reported_id)
                        WHEN r.type = 2 THEN (SELECT name FROM servers WHERE id = r.reported_id)
                    END as reported_name
             FROM reports r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.id = ?',
            [$id]
        );
    }
}
