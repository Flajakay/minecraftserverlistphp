<?php

namespace App\Models;

use App\Core\System\Database;

class ServerCategory
{
    public static function getServerCategories($serverId)
    {
        return Database::fetchAll(
            'SELECT sc.*, c.name as category_name, c.url as category_url 
             FROM server_categories sc 
             JOIN categories c ON sc.category_id = c.id 
             WHERE sc.server_id = ? 
             ORDER BY sc.is_primary DESC, c.name ASC', 
            [$serverId]
        );
    }

    public static function getCategoryServers($categoryIds, $includeSubcategories = false)
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        $categoryIds = is_array($categoryIds) ? $categoryIds : [$categoryIds];
        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        
        $sql = 'SELECT DISTINCT s.* 
                FROM servers s 
                JOIN server_categories sc ON s.id = sc.server_id 
                WHERE sc.category_id IN (' . $placeholders . ') 
                AND s.active = 1 AND s.private = 0';
        
        if ($includeSubcategories) {
            $allCategoryIds = $categoryIds;
            foreach ($categoryIds as $categoryId) {
                $subcategories = Category::getSubcategories($categoryId);
                foreach ($subcategories as $sub) {
                    $allCategoryIds[] = $sub->id;
                }
            }
            $allCategoryIds = array_unique($allCategoryIds);
            $placeholders = str_repeat('?,', count($allCategoryIds) - 1) . '?';
            $sql = 'SELECT DISTINCT s.* 
                    FROM servers s 
                    JOIN server_categories sc ON s.id = sc.server_id 
                    WHERE sc.category_id IN (' . $placeholders . ') 
                    AND s.active = 1 AND s.private = 0';
            $categoryIds = $allCategoryIds;
        }
        
        return Database::fetchAll($sql, $categoryIds);
    }

    public static function setServerCategories($serverId, $categoryIds, $primaryCategoryId = null): bool
    {
        Database::pdo()->beginTransaction();
        try {
            Database::delete('server_categories', 'server_id = ?', [$serverId]);

            if (empty($categoryIds)) {
                Database::pdo()->commit();
                return false;
            }

            foreach ($categoryIds as $categoryId) {
                $isPrimary = ($primaryCategoryId && $categoryId == $primaryCategoryId) ? 1 : 0;
                Database::insert('server_categories', [
                    'server_id' => $serverId,
                    'category_id' => $categoryId,
                    'is_primary' => $isPrimary
                ]);
            }

            Database::pdo()->commit();
            return true;
        } catch (\Exception $e) {
            Database::pdo()->rollBack();
            error_log('Failed to set server categories: ' . $e->getMessage());
            return false;
        }
    }

    public static function getServerCategoriesForServers(array $serverIds): array
    {
        if (empty($serverIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($serverIds) - 1) . '?';
        $rows = Database::fetchAll(
            'SELECT sc.*, c.name as category_name, c.url as category_url
             FROM server_categories sc
             JOIN categories c ON sc.category_id = c.id
             WHERE sc.server_id IN (' . $placeholders . ')
             ORDER BY sc.is_primary DESC, c.name ASC',
            $serverIds
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->server_id][] = $row;
        }
        return $grouped;
    }

    public static function getPrimaryCategory($serverId)
    {
        return Database::fetch(
            'SELECT sc.*, c.name as category_name, c.url as category_url 
             FROM server_categories sc 
             JOIN categories c ON sc.category_id = c.id 
             WHERE sc.server_id = ? AND sc.is_primary = 1', 
            [$serverId]
        );
    }

    public static function exists($serverId, $categoryId): bool
    {
        $result = Database::fetch(
            'SELECT id FROM server_categories WHERE server_id = ? AND category_id = ?',
            [$serverId, $categoryId]
        );
        return $result !== false;
    }

    public static function removeServerCategory($serverId, $categoryId)
    {
        return Database::delete('server_categories', 'server_id = ? AND category_id = ?', [$serverId, $categoryId]);
    }
}
