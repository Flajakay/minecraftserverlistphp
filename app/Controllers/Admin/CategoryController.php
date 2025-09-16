<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\AuditLog;

class CategoryController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');

        $limit = 20;
        $categories = Category::getAllPaginated($page, $limit, $search);
        $totalCategories = Category::countAllAdmin($search);
        $totalPages = ceil($totalCategories / $limit);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->create();
        }

        $parentCategories = Category::getAll();

        view('admin.categories-index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'totalCategories' => $totalCategories,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }

    public function create()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'url' => $this->generateSlug($_POST['url'] ?? $_POST['name']),
            'description' => sanitize($_POST['description'] ?? ''),
            'title' => sanitize($_POST['title'] ?? ''),
            'parent_id' => (int)($_POST['parent_id'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['url'])) {
            flash('error', lang('marked_fields_empty'));
            redirect('/admin/categories');
        }

        if (Category::findByUrl($data['url'])) {
            flash('error', 'URL already exists');
            redirect('/admin/categories');
        }

        if (Category::create($data)) {
            $currentUser = auth();
            AuditLog::log('create', 'categories', 0, $currentUser->id, 'Created category: ' . $data['name']);
            flash('success', lang('category_created'));
        } else {
            flash('error', 'Failed to create category');
        }

        redirect('/admin/categories');
    }

    public function edit($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $category = Category::find($id);
        if (!$category) {
            flash('error', 'Category not found');
            redirect('/admin/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }

        $parentCategories = Category::getAll();

        view('admin.categories-edit', [
            'category' => $category,
            'parentCategories' => $parentCategories
        ]);
    }

    public function update($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $category = Category::find($id);
        if (!$category) {
            flash('error', 'Category not found');
            redirect('/admin/categories');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'url' => $this->generateSlug($_POST['url'] ?? $_POST['name']),
            'description' => sanitize($_POST['description'] ?? ''),
            'title' => sanitize($_POST['title'] ?? ''),
            'parent_id' => (int)($_POST['parent_id'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['url'])) {
            flash('error', lang('marked_fields_empty'));
            redirect('/admin/categories');
        }

        $existingCategory = Category::findByUrl($data['url']);
        if ($existingCategory && $existingCategory->id != $id) {
            flash('error', 'URL already exists');
            redirect('/admin/categories');
        }

        if (Category::update($id, $data)) {
            $currentUser = auth();
            AuditLog::log('update', 'categories', $id, $currentUser->id, 'Updated category: ' . $category->name);
            flash('success', lang('category_updated'));
        } else {
            flash('error', 'Failed to update category');
        }

        redirect('/admin/categories');
    }

    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $category = Category::find($id);
        if (!$category) {
            flash('error', 'Category not found');
            redirect('/admin/categories');
        }

        if ($id == 1) {
            flash('error', 'Cannot delete default category');
            redirect('/admin/categories');
        }

        if (Category::delete($id)) {
            $currentUser = auth();
            AuditLog::log('delete', 'categories', $id, $currentUser->id, 'Deleted category: ' . $category->name);
            flash('success', lang('category_deleted'));
        } else {
            flash('error', 'Failed to delete category');
        }

        redirect('/admin/categories');
    }

    private function generateSlug($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
}
