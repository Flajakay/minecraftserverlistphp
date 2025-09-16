<?php

namespace App\Models;

use App\Core\Database;


class Favorite
{
    public static function add($userId, $serverId)
    {
        if (self::exists($userId, $serverId)) {
            return false;
        }
        
        Database::insert('favorites', [
            'user_id' => $userId,
            'server_id' => $serverId
        ]);
        
        Database::query('UPDATE servers SET favorites = favorites + 1 WHERE id = ?', [$serverId]);
        
        return true;
    }

    public static function remove($userId, $serverId)
    {
        if (!self::exists($userId, $serverId)) {
            return false;
        }
        
        Database::delete('favorites', 'user_id = ? AND server_id = ?', [$userId, $serverId]);
        Database::query('UPDATE servers SET favorites = favorites - 1 WHERE id = ?', [$serverId]);
        
        return true;
    }

    public static function toggle($userId, $serverId)
    {
        if (self::exists($userId, $serverId)) {
            return self::remove($userId, $serverId) ? 'removed' : false;
        } else {
            return self::add($userId, $serverId) ? 'added' : false;
        }
    }

    public static function exists($userId, $serverId)
    {
        $favorite = Database::fetch(
            'SELECT * FROM favorites WHERE user_id = ? AND server_id = ?',
            [$userId, $serverId]
        );
        
        return $favorite !== false;
    }

    public static function getUserFavorites($userId)
    {
        return Database::fetchAll(
            'SELECT s.*, c.name as category_name 
             FROM servers s 
             LEFT JOIN categories c ON s.category_id = c.id 
             INNER JOIN favorites f ON s.id = f.server_id 
             WHERE f.user_id = ? AND s.active = 1 AND s.private = 0
             ORDER BY f.id DESC',
            [$userId]
        );
    }

    public static function getForUserByServerIds($userId, $serverIds)
    {
        if (empty($serverIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($serverIds), '?'));
        $params = array_merge([$userId], $serverIds);
        
        $sql = "SELECT server_id FROM favorites WHERE user_id = ? AND server_id IN ({$placeholders})";
        
        $results = Database::fetchAll($sql, $params);
        
        return array_map(fn($row) => (int)$row->server_id, $results);
    }
}
