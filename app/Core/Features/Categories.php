<?php

namespace App\Core\Features;

use App\Models\Category;
use App\Models\AuditLog;

class Categories
{
    public static function create($data, $creatorId)
    {
        if (empty($data['name']) || empty($data['url'])) {
            return [
                'success' => false,
                'error' => lang('marked_fields_empty')
            ];
        }

        $slug = self::generateSlug($data['url']);
        
        if (Category::findByUrl($slug)) {
            return [
                'success' => false,
                'error' => lang('url_already_exists')
            ];
        }

        $categoryData = [
            'name' => $data['name'],
            'url' => $slug,
            'description' => $data['description'] ?? '',
            'title' => $data['title'] ?? '',
            'parent_id' => (int)($data['parent_id'] ?? 0)
        ];

        if (Category::create($categoryData)) {
            AuditLog::log('create', 'categories', 0, $creatorId, 'Created category: ' . $categoryData['name']);
            
            return [
                'success' => true,
                'message' => lang('category_created')
            ];
        }

        return [
            'success' => false,
            'error' => lang('category_create_failed')
        ];
    }

    public static function update($id, $data, $updaterId)
    {
        $category = Category::find($id);
        if (!$category) {
            return [
                'success' => false,
                'error' => lang('category_not_found')
            ];
        }

        if (empty($data['name']) || empty($data['url'])) {
            return [
                'success' => false,
                'error' => lang('marked_fields_empty')
            ];
        }

        $slug = self::generateSlug($data['url']);
        
        $existingCategory = Category::findByUrl($slug);
        if ($existingCategory && $existingCategory->id != $id) {
            return [
                'success' => false,
                'error' => lang('url_already_exists')
            ];
        }

        $categoryData = [
            'name' => $data['name'],
            'url' => $slug,
            'description' => $data['description'] ?? '',
            'title' => $data['title'] ?? '',
            'parent_id' => (int)($data['parent_id'] ?? 0)
        ];

        if (Category::update($id, $categoryData)) {
            AuditLog::log('update', 'categories', $id, $updaterId, 'Updated category: ' . $category->name);
            
            return [
                'success' => true,
                'message' => lang('category_updated')
            ];
        }

        return [
            'success' => false,
            'error' => lang('category_update_failed')
        ];
    }

    public static function delete($id, $deleterId)
    {
        $category = Category::find($id);
        if (!$category) {
            return [
                'success' => false,
                'error' => lang('category_not_found')
            ];
        }

        if ($id == 1) {
            return [
                'success' => false,
                'error' => lang('cannot_delete_default_category')
            ];
        }

        if (Category::delete($id)) {
            AuditLog::log('delete', 'categories', $id, $deleterId, 'Deleted category: ' . $category->name);
            
            return [
                'success' => true,
                'message' => lang('category_deleted')
            ];
        }

        return [
            'success' => false,
            'error' => lang('category_delete_failed')
        ];
    }

    private static function generateSlug($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
}
