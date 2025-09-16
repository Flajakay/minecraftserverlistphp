<?php

namespace App\Models;

use App\Core\Database;



class Server
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['votes'] = 0;
        $data['favorites'] = 0;
        $data['highlight'] = 0;
        $data['private'] = 1;
        $data['active'] = 1;
        $data['status'] = 1;
        
        return Database::insert('servers', $data);
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM servers WHERE id = ?', [$id]);
    }

    public static function findByAddress($address, $port)
    {
        return Database::fetch(
            'SELECT s.*, u.username as owner_username 
             FROM servers s 
             LEFT JOIN users u ON s.user_id = u.id 
             WHERE s.address = ? AND s.port = ?', 
            [$address, $port]
        );
    }

    public static function getAll($filters = [])
    {
        $sql = 'SELECT s.*, c.name as category_name, u.username as owner_username 
                FROM servers s 
                LEFT JOIN categories c ON s.category_id = c.id 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE s.active = 1 AND s.private = 0';
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND s.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['status'])) {
            $sql .= ' AND s.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['country'])) {
            $sql .= ' AND s.country = ?';
            $params[] = $filters['country'];
        }
        
        if (!empty($filters['version'])) {
            $sql .= ' AND s.version = ?';
            $params[] = $filters['version'];
        }
        
        if (isset($filters['highlight'])) {
            $sql .= ' AND s.highlight = ?';
            $params[] = $filters['highlight'];
        }
        
        $orderBy = $filters['order_by'] ?? 'created_at';
        $validOrders = ['votes', 'players', 'favorites', 'created_at'];
        
        if (!in_array($orderBy, $validOrders)) {
            $orderBy = 'created_at';
        }
        
        $sql .= ' ORDER BY s.highlight DESC, s.' . $orderBy . ' DESC';
        
        if (isset($filters['limit'])) {
            $sql .= ' LIMIT ' . (int)$filters['limit'];
        }
        
        if (isset($filters['offset'])) {
            $sql .= ' OFFSET ' . (int)$filters['offset'];
        }
        
        return Database::fetchAll($sql, $params);
    }

    public static function getUserServers($userId)
    {
        return Database::fetchAll(
            'SELECT s.*, c.name as category_name 
             FROM servers s 
             LEFT JOIN categories c ON s.category_id = c.id 
             WHERE s.user_id = ? 
             ORDER BY s.created_at DESC', 
            [$userId]
        );
    }

    public static function updateStatus($id, $status, $players = 0, $maxPlayers = 0, $version = '')
    {
        return Database::update('servers', [
            'status' => $status,
            'players' => $players,
            'max_players' => $maxPlayers,
            'version' => $version,
            'last_check' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
    }

    public static function addVote($serverId)
    {
        return Database::query('UPDATE servers SET votes = votes + 1 WHERE id = ?', [$serverId]);
    }

    public static function addFavorite($serverId)
    {
        return Database::query('UPDATE servers SET favorites = favorites + 1 WHERE id = ?', [$serverId]);
    }

    public static function removeFavorite($serverId)
    {
        return Database::query('UPDATE servers SET favorites = favorites - 1 WHERE id = ?', [$serverId]);
    }

    public static function updateHighlight($serverId, $highlight)
    {
        return Database::update('servers', ['highlight' => $highlight], 'id = ?', [$serverId]);
    }

    public static function setPrivate($serverId, $private)
    {
        return Database::update('servers', ['private' => $private], 'id = ?', [$serverId]);
    }

    public static function setActive($serverId, $active)
    {
        return Database::update('servers', ['active' => $active], 'id = ?', [$serverId]);
    }

    public static function delete($id)
    {
        Database::delete('votes', 'server_id = ?', [$id]);
        Database::delete('favorites', 'server_id = ?', [$id]);
        Database::delete('comments', 'server_id = ?', [$id]);
        Database::delete('reports', 'reported_id = ? AND type = 2', [$id]);
        return Database::delete('servers', 'id = ?', [$id]);
    }

    public static function exists($address, $port)
    {
        $server = self::findByAddress($address, $port);
        return $server !== false;
    }

    public static function count($filters = [])
    {
        $sql = 'SELECT COUNT(*) as count FROM servers WHERE active = 1 AND private = 0';
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '', $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT s.*, c.name as category_name, u.username as owner_username 
                FROM servers s 
                LEFT JOIN categories c ON s.category_id = c.id 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (s.name LIKE ? OR s.address LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $sql .= ' AND s.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= ' AND s.status = ?';
            $params[] = $filters['status'];
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $sql .= ' AND s.active = ?';
            $params[] = $filters['active'];
        }

        if (isset($filters['private']) && $filters['private'] !== '') {
            $sql .= ' AND s.private = ?';
            $params[] = $filters['private'];
        }

        $sql .= ' ORDER BY s.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '', $filters = [])
    {
        $sql = 'SELECT COUNT(*) as count FROM servers s 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (s.name LIKE ? OR s.address LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $sql .= ' AND s.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= ' AND s.status = ?';
            $params[] = $filters['status'];
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $sql .= ' AND s.active = ?';
            $params[] = $filters['active'];
        }

        if (isset($filters['private']) && $filters['private'] !== '') {
            $sql .= ' AND s.private = ?';
            $params[] = $filters['private'];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function update($id, $data)
    {
        return Database::update('servers', $data, 'id = ?', [$id]);
    }

    public static function resetAllVotes()
    {
        return Database::query('UPDATE servers SET votes = 0');
    }
}
