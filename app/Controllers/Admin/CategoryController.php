<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Core\Features\Categories;
/**
 * Admin categories controller.
 *
 * Allows admins to manage category taxonomy used for server classification.
 */
class CategoryController
{
    /**
     * List categories and render the create form.
     *
     * Note: POST requests to this route create a category (handled by `create()`).
     */
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

    /**
     * Create a new category.
     */
    public function create(): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'url' => $_POST['url'] ?? $_POST['name'],
            'description' => sanitize($_POST['description'] ?? ''),
            'title' => sanitize($_POST['title'] ?? ''),
            'parent_id' => (int)($_POST['parent_id'] ?? 0)
        ];

        $currentUser = auth();
        $result = Categories::create($data, $currentUser->id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/categories');
    }

    /**
     * Render the edit form for a category.
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $category = Category::find($id);
        if (!$category) {
            flash('error', lang('category_not_found'));
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

    /**
     * Persist changes to a category.
     */
    public function update($id): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $category = Category::find($id);
        if (!$category) {
            flash('error', lang('category_not_found'));
            redirect('/admin/categories');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'url' => $_POST['url'] ?? $_POST['name'],
            'description' => sanitize($_POST['description'] ?? ''),
            'title' => sanitize($_POST['title'] ?? ''),
            'parent_id' => (int)($_POST['parent_id'] ?? 0)
        ];

        $currentUser = auth();
        $result = Categories::update($id, $data, $currentUser->id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/categories');
    }

    /**
     * Delete a category.
     *
     * The default/root category cannot be deleted.
     */
    public function delete($id): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $currentUser = auth();
        $result = Categories::delete($id, $currentUser->id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/categories');
    }
}
