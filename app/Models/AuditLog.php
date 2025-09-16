<?php

namespace App\Models;

use App\Core\Database;

class AuditLog
{
    public static function log($action, $tableName, $recordId, $userId, $details = null)
    {
        return Database::insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function getAll($page = 1, $limit = 50, $search = '')
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT a.*, u.username 
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id';
        $params = [];

        if (!empty($search)) {
            $sql .= ' WHERE (a.action LIKE ? OR a.table_name LIKE ? OR u.username LIKE ? OR a.details LIKE ?)';
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }

        $sql .= ' ORDER BY a.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function count($search = '')
    {
        $sql = 'SELECT COUNT(*) as count FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id';
        $params = [];

        if (!empty($search)) {
            $sql .= ' WHERE (a.action LIKE ? OR a.table_name LIKE ? OR u.username LIKE ? OR a.details LIKE ?)';
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }
}
