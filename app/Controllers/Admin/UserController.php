<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\AuditLog;

class UserController
{
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

        $currentUser = auth();
        if ($user->id == $currentUser->id) {
            flash('error', lang('status_yourself'));
            redirect('/admin/users');
        }

        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'name' => sanitize($_POST['name'] ?? ''),
            'about' => sanitize($_POST['about'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'location' => sanitize($_POST['location'] ?? ''),
            'type' => (int)($_POST['type'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
            'private' => isset($_POST['private']) ? 1 : 0
        ];

        if (User::update($id, $data)) {
            AuditLog::log('update', 'users', $id, $currentUser->id, 'Updated user: ' . $user->username);
            flash('success', lang('user_updated'));
        } else {
            flash('error', 'Failed to update user');
        }

        redirect('/admin/users');
    }

    public function delete($id)
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

        $currentUser = auth();
        if ($user->id == $currentUser->id) {
            flash('error', lang('delete_yourself'));
            redirect('/admin/users');
        }

        if (User::delete($id)) {
            AuditLog::log('delete', 'users', $id, $currentUser->id, 'Deleted user: ' . $user->username);
            flash('success', lang('user_deleted'));
        } else {
            flash('error', 'Failed to delete user');
        }

        redirect('/admin/users');
    }

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
        if ($user->id == $currentUser->id) {
            flash('error', lang('status_yourself'));
            redirect('/admin/users');
        }

        switch ($action) {
            case 'activate':
                User::update($id, ['active' => 1]);
                AuditLog::log('activate', 'users', $id, $currentUser->id, 'Activated user: ' . $user->username);
                flash('success', lang('user_activated'));
                break;
            case 'deactivate':
                User::update($id, ['active' => 0]);
                AuditLog::log('deactivate', 'users', $id, $currentUser->id, 'Deactivated user: ' . $user->username);
                flash('success', lang('user_deactivated'));
                break;
            case 'delete':
                if (User::delete($id)) {
                    AuditLog::log('delete', 'users', $id, $currentUser->id, 'Deleted user: ' . $user->username);
                    flash('success', lang('user_deleted'));
                }
                break;
            default:
                flash('error', 'Invalid action');
        }

        redirect('/admin/users');
    }
}
