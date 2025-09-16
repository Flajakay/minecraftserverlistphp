<?php

namespace App\Controllers\Admin;

use App\Models\Server;
use App\Models\Category;
use App\Models\AuditLog;

class ServerController
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
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'active' => $_GET['active'] ?? '',
            'private' => $_GET['private'] ?? ''
        ];

        $limit = 20;
        $servers = Server::getAllPaginated($page, $limit, $search, $filters);
        $totalServers = Server::countAllAdmin($search, $filters);
        $totalPages = ceil($totalServers / $limit);
        $categories = Category::getAll();

        view('admin.servers-index', [
            'servers' => $servers,
            'categories' => $categories,
            'totalServers' => $totalServers,
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

        $server = Server::find($id);
        if (!$server) {
            flash('error', 'Server not found');
            redirect('/admin/servers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }

        $categories = Category::getAllForSelect();
        $countries = getCountries();

        view('admin.servers-edit', [
            'server' => $server,
            'categories' => $categories,
            'countries' => $countries
        ]);
    }

    public function update($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $server = Server::find($id);
        if (!$server) {
            flash('error', 'Server not found');
            redirect('/admin/servers');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'port' => (int)($_POST['port'] ?? 25565),
            'category_id' => (int)($_POST['category_id'] ?? 1),
            'description' => sanitize($_POST['description'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'country' => sanitize($_POST['country'] ?? 'US'),
            'youtube_id' => sanitize($_POST['youtube_id'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
            'private' => isset($_POST['private']) ? 1 : 0,
            'highlight' => isset($_POST['highlight']) ? 1 : 0
        ];

        if (Server::update($id, $data)) {
            $currentUser = auth();
            AuditLog::log('update', 'servers', $id, $currentUser->id, 'Updated server: ' . $server->name);
            flash('success', lang('server_updated'));
        } else {
            flash('error', 'Failed to update server');
        }

        redirect('/admin/servers');
    }

    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $server = Server::find($id);
        if (!$server) {
            flash('error', 'Server not found');
            redirect('/admin/servers');
        }

        if (Server::delete($id)) {
            $currentUser = auth();
            AuditLog::log('delete', 'servers', $id, $currentUser->id, 'Deleted server: ' . $server->name);
            flash('success', lang('server_deleted'));
        } else {
            flash('error', 'Failed to delete server');
        }

        redirect('/admin/servers');
    }

    public function action($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $action = $_POST['action'] ?? '';
        $server = Server::find($id);
        
        if (!$server) {
            flash('error', 'Server not found');
            redirect('/admin/servers');
        }

        $currentUser = auth();

        switch ($action) {
            case 'activate':
                Server::update($id, ['active' => 1]);
                AuditLog::log('activate', 'servers', $id, $currentUser->id, 'Activated server: ' . $server->name);
                flash('success', 'Server activated');
                break;
            case 'deactivate':
                Server::update($id, ['active' => 0]);
                AuditLog::log('deactivate', 'servers', $id, $currentUser->id, 'Deactivated server: ' . $server->name);
                flash('success', 'Server deactivated');
                break;
            case 'make_private':
                Server::update($id, ['private' => 1]);
                AuditLog::log('make_private', 'servers', $id, $currentUser->id, 'Made server private: ' . $server->name);
                flash('success', 'Server made private');
                break;
            case 'make_public':
                Server::update($id, ['private' => 0]);
                AuditLog::log('make_public', 'servers', $id, $currentUser->id, 'Made server public: ' . $server->name);
                flash('success', 'Server made public');
                break;
            case 'add_highlight':
                Server::update($id, ['highlight' => 1]);
                AuditLog::log('add_highlight', 'servers', $id, $currentUser->id, 'Added highlight to server: ' . $server->name);
                flash('success', 'Server highlighted');
                break;
            case 'remove_highlight':
                Server::update($id, ['highlight' => 0]);
                AuditLog::log('remove_highlight', 'servers', $id, $currentUser->id, 'Removed highlight from server: ' . $server->name);
                flash('success', 'Server highlight removed');
                break;
            case 'delete':
                if (Server::delete($id)) {
                    AuditLog::log('delete', 'servers', $id, $currentUser->id, 'Deleted server: ' . $server->name);
                    flash('success', lang('server_deleted'));
                }
                break;
            default:
                flash('error', 'Invalid action');
        }

        redirect('/admin/servers');
    }
}
