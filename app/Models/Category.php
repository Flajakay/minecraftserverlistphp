<?php

namespace App\Models;

use App\Core\System\Database;

class Category
{
    public static function getAll()
    {
        return Database::fetchAll('SELECT * FROM categories WHERE parent_id = 0 ORDER BY name ASC');
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findByUrl($url)
    {
        return Database::fetch('SELECT * FROM categories WHERE url = ?', [$url]);
    }

    public static function getSubcategories($parentId)
    {
        return Database::fetchAll('SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC', [$parentId]);
    }

    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return Database::insert('categories', $data);
    }

    public static function update($id, $data)
    {
        return Database::update('categories', $data, 'id = ?', [$id]);
    }

    public static function delete($id)
    {
        Database::query('UPDATE servers SET category_id = 1 WHERE category_id = ?', [$id]);
        Database::delete('categories', 'parent_id = ?', [$id]);
        return Database::delete('categories', 'id = ?', [$id]);
    }

    public static function getWithServerCount()
    {
        return Database::fetchAll(
            'SELECT c.*, COUNT(DISTINCT sc.server_id) as server_count 
             FROM categories c 
             LEFT JOIN server_categories sc ON c.id = sc.category_id
             LEFT JOIN servers s ON sc.server_id = s.id AND s.active = 1 AND s.private = 0
             WHERE c.parent_id = 0
             GROUP BY c.id 
             ORDER BY c.name ASC'
        );
    }

    public static function getAllPaginated($page = 1, $limit = 20, $search = '')
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT c.*, p.name as parent_name, COUNT(s.id) as server_count 
                FROM categories c 
                LEFT JOIN categories p ON c.parent_id = p.id 
                LEFT JOIN servers s ON c.id = s.category_id 
                WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (c.name LIKE ? OR c.url LIKE ? OR c.description LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $sql .= ' GROUP BY c.id ORDER BY c.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAllAdmin($search = '')
    {
        $sql = 'SELECT COUNT(*) as count FROM categories WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (name LIKE ? OR url LIKE ? OR description LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function getAllForSelect()
    {
        return Database::fetchAll('SELECT id, name, parent_id FROM categories ORDER BY parent_id, name ASC');
    }

    public static function getAllWithHierarchy()
    {
        return Database::fetchAll('SELECT * FROM categories ORDER BY parent_id, name ASC');
    }

    public static function getDescendants($categoryId): array
    {
        $descendants = [];
        $subcategories = self::getSubcategories($categoryId);
        
        foreach ($subcategories as $subcategory) {
            $descendants[] = $subcategory;
            $subDescendants = self::getDescendants($subcategory->id);
            $descendants = array_merge($descendants, $subDescendants);
        }
        
        return $descendants;
    }

    public static function getServerCountByCategory($categoryId, $includeSubcategories = false)
    {
        $categoryIds = [$categoryId];
        
        if ($includeSubcategories) {
            $descendants = self::getDescendants($categoryId);
            foreach ($descendants as $desc) {
                $categoryIds[] = $desc->id;
            }
        }
        
        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        $result = Database::fetch(
            "SELECT COUNT(DISTINCT sc.server_id) as count 
             FROM server_categories sc 
             JOIN servers s ON sc.server_id = s.id 
             WHERE sc.category_id IN ($placeholders) 
             AND s.active = 1 AND s.private = 0",
            $categoryIds
        );
        
        return $result ? $result->count : 0;
    }
}
