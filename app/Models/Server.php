<?php

namespace App\Models;

use App\Core\Database;



class Server
{
    public static function getEffectiveOwnerUserId($server)
    {
        if (!$server) {
            return null;
        }

        if (!empty($server->verified_owner_user_id)) {
            return (int) $server->verified_owner_user_id;
        }

        return !empty($server->user_id) ? (int) $server->user_id : null;
    }

    public static function isEffectiveOwner($server, $userId)
    {
        $effectiveOwnerId = self::getEffectiveOwnerUserId($server);
        return $effectiveOwnerId && (int) $effectiveOwnerId === (int) $userId;
    }

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
             LEFT JOIN users u ON COALESCE(s.verified_owner_user_id, s.user_id) = u.id 
             WHERE s.address = ? AND s.port = ?',
            [$address, $port]
        );
    }

    public static function getAll($filters = [])
    {
        $sql = 'SELECT s.*, u.username as owner_username 
                FROM servers s 
                LEFT JOIN users u ON COALESCE(s.verified_owner_user_id, s.user_id) = u.id 
                WHERE s.active = 1 AND s.private = 0';

        $params = [];

        if (!empty($filters['categories'])) {
            $categoryIds = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];

            if (!empty($filters['include_subcategories'])) {
                $allCategoryIds = $categoryIds;
                foreach ($categoryIds as $categoryId) {
                    $subcategories = \App\Models\Category::getSubcategories($categoryId);
                    foreach ($subcategories as $sub) {
                        $allCategoryIds[] = $sub->id;
                    }
                }
                $categoryIds = array_unique($allCategoryIds);
            }

            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql .= " AND s.id IN (
                SELECT DISTINCT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id IN ($placeholders)
            )";
            $params = array_merge($params, $categoryIds);
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND s.id IN (
                SELECT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id = ?
            )";
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

        $orderBy = $filters['order_by'] ?? 'votes';

        if ($orderBy === 'newest') {
            $orderBy = 'created_at';
        }

        $validOrders = ['votes', 'players', 'favorites', 'created_at'];

        if (!in_array($orderBy, $validOrders)) {
            $orderBy = 'votes';
        }

        $sql .= ' ORDER BY s.highlight DESC, s.' . $orderBy . ' DESC';

        if (isset($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        if (isset($filters['offset'])) {
            $sql .= ' OFFSET ' . (int) $filters['offset'];
        }

        return Database::fetchAll($sql, $params);
    }

    public static function getUserServers($userId)
    {
        return Database::fetchAll(
            'SELECT s.* 
             FROM servers s 
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
        $sql = 'SELECT COUNT(DISTINCT s.id) as count FROM servers s WHERE s.active = 1 AND s.private = 0';
        $params = [];

        if (!empty($filters['categories'])) {
            $categoryIds = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];

            if (!empty($filters['include_subcategories'])) {
                $allCategoryIds = $categoryIds;
                foreach ($categoryIds as $categoryId) {
                    $subcategories = \App\Models\Category::getSubcategories($categoryId);
                    foreach ($subcategories as $sub) {
                        $allCategoryIds[] = $sub->id;
                    }
                }
                $categoryIds = array_unique($allCategoryIds);
            }

            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql .= " AND s.id IN (
                SELECT DISTINCT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id IN ($placeholders)
            )";
            $params = array_merge($params, $categoryIds);
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND s.id IN (
                SELECT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id = ?
            )";
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

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '', $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT DISTINCT s.*, u.username as owner_username 
                FROM servers s 
                LEFT JOIN users u ON COALESCE(s.verified_owner_user_id, s.user_id) = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (s.name LIKE ? OR s.address LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($filters['categories'])) {
            $categoryIds = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];
            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql .= " AND s.id IN (
                SELECT DISTINCT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id IN ($placeholders)
            )";
            $params = array_merge($params, $categoryIds);
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $sql .= " AND s.id IN (
                SELECT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id = ?
            )";
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

        $sql .= ' ORDER BY s.created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '', $filters = [])
    {
        $sql = 'SELECT COUNT(DISTINCT s.id) as count FROM servers s 
                LEFT JOIN users u ON COALESCE(s.verified_owner_user_id, s.user_id) = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (s.name LIKE ? OR s.address LIKE ? OR u.username LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($filters['categories'])) {
            $categoryIds = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];
            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql .= " AND s.id IN (
                SELECT DISTINCT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id IN ($placeholders)
            )";
            $params = array_merge($params, $categoryIds);
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $sql .= " AND s.id IN (
                SELECT sc.server_id 
                FROM server_categories sc 
                WHERE sc.category_id = ?
            )";
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

    public static function startClaim($serverId, $userId, $token, $expiresAt)
    {
        return Database::update('servers', [
            'verification_status' => 1,
            'verification_requested_by_user_id' => $userId,
            'verification_token' => $token,
            'verification_token_expires_at' => $expiresAt,
            'verified_owner_user_id' => null,
            'verified_at' => null
        ], 'id = ?', [$serverId]);
    }

    public static function cancelClaim($serverId)
    {
        return Database::update('servers', [
            'verification_status' => 0,
            'verification_requested_by_user_id' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
            'last_verification_attempt_at' => null
        ], 'id = ?', [$serverId]);
    }

    public static function markVerificationAttempt($serverId)
    {
        return Database::update('servers', [
            'last_verification_attempt_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$serverId]);
    }

    public static function canAttemptVerification($serverId, $cooldownSeconds = 30)
    {
        $row = Database::fetch('SELECT last_verification_attempt_at FROM servers WHERE id = ?', [$serverId]);
        if (!$row || empty($row->last_verification_attempt_at)) {
            return true;
        }

        $last = strtotime($row->last_verification_attempt_at);
        if (!$last) {
            return true;
        }

        return (time() - $last) >= (int) $cooldownSeconds;
    }

    public static function markVerified($serverId, $userId)
    {
        return Database::update('servers', [
            'verification_status' => 2,
            'verified_owner_user_id' => $userId,
            'verified_at' => date('Y-m-d H:i:s'),
            'verification_requested_by_user_id' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null
        ], 'id = ?', [$serverId]);
    }

    public static function resetAllVotes()
    {
        return Database::query('UPDATE servers SET votes = 0');
    }

    public static function getCountriesWithServerCount()
    {
        return Database::fetchAll(
            'SELECT country, COUNT(*) as server_count 
             FROM servers 
             WHERE active = 1 AND private = 0 AND country IS NOT NULL AND country != ""
             GROUP BY country 
             HAVING server_count > 0
             ORDER BY server_count DESC, country ASC'
        );
    }

    public static function getCategories($serverId)
    {
        return \App\Models\ServerCategory::getServerCategories($serverId);
    }

    public static function setCategories($serverId, $categoryIds, $primaryCategoryId = null)
    {
        return \App\Models\ServerCategory::setServerCategories($serverId, $categoryIds, $primaryCategoryId);
    }

    public static function getPrimaryCategory($serverId)
    {
        return \App\Models\ServerCategory::getPrimaryCategory($serverId);
    }
}
