<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Core\Features\Users;
/**
 * Admin users controller.
 *
 * Allows admins to search, edit, activate/deactivate, and delete user accounts.
 */
class UserController
{
    /**
     * List users with filters.
     */
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');
        $filters = [
            'type' => $_GET['type'] ?? '',
            'active' => $_GET['active'] ?? ''
        ];

        $limit = 20;
        $users = User::getAllPaginated($page, $limit, $search, $filters);
        $totalUsers = User::countAll($search, $filters);
        $totalPages = ceil($totalUsers / $limit);

        view('admin.users-index', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'filters' => $filters
        ]);
    }

    /**
     * Render the edit form for a user.
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $user = User::find($id);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }

        view('admin.users-edit', ['user' => $user]);
    }

    /**
     * Persist edits to a user.
     *
     * Safety: admins cannot modify their own status/role via this endpoint.
     */
    public function update($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $user = User::find($id);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'name' => $_POST['name'] ?? '',
            'about' => $_POST['about'] ?? '',
            'website' => $_POST['website'] ?? '',
            'location' => $_POST['location'] ?? '',
            'type' => $_POST['type'] ?? 0,
            'active' => $_POST['active'] ?? null,
            'private' => $_POST['private'] ?? null
        ];

        $currentUser = auth();
        $result = Users::updateAdmin($id, $currentUser->id, $data);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/users');
    }

    /**
     * Delete a user.
     *
     * Safety: admins cannot delete themselves.
     */
    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $currentUser = auth();
        $result = Users::performAdminAction($id, $currentUser->id, 'delete');

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/users');
    }

    /**
     * Perform a bulk action on a user (activate/deactivate/delete).
     */
    public function action($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $action = $_POST['action'] ?? '';
        $user = User::find($id);
        
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        $currentUser = auth();
        $result = Users::performAdminAction($id, $currentUser->id, $action);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/users');
    }
}
