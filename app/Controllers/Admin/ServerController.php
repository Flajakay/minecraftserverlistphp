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
        
        foreach ($servers as $server) {
            $server->categories = Server::getCategories($server->id);
            //$server->primary_category = Server::getPrimaryCategory($server->id);
        }
        
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
            flash('error', lang('server_not_found_admin'));
            redirect('/admin/servers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }

        $categories = Category::getAllForSelect();
        $countries = getCountries();
        $serverCategories = Server::getCategories($id);

        view('admin.servers-edit', [
            'server' => $server,
            'categories' => $categories,
            'countries' => $countries,
            'server_categories' => $serverCategories
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
            flash('error', lang('server_not_found_admin'));
            redirect('/admin/servers');
        }

        $categoryIds = $_POST['category_ids'] ?? [];
        
        if (empty($categoryIds) || !is_array($categoryIds)) {
            flash('error', lang('category_required'));
            redirect("/admin/servers/edit/{$id}");
        }
        
        $categoryIds = array_filter(array_map('intval', $categoryIds));
        if (empty($categoryIds)) {
            flash('error', lang('invalid_categories'));
            redirect("/admin/servers/edit/{$id}");
        }

        if (empty($_POST['country'] ?? '')) {
            flash('error', 'Country is required');
            redirect("/admin/servers/edit/{$id}");
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'port' => (int)($_POST['port'] ?? 25565),
            'category_id' => $categoryIds[0],
            'description' => sanitize($_POST['description'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'country' => sanitize($_POST['country'] ?? ''),
            'youtube_id' => sanitize($_POST['youtube_id'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
            'private' => isset($_POST['private']) ? 1 : 0,
            'highlight' => isset($_POST['highlight']) ? 1 : 0
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], 'banners');
            if ($image) {
                $data['image'] = $image;
            }
        }

        $customData = [];
        if (!empty($_POST['votifier_public_key'])) {
            $customData['votifier_public_key'] = $_POST['votifier_public_key'];
            $customData['votifier_ip'] = $_POST['votifier_ip'] ?? $server->address;
            $customData['votifier_port'] = (int)($_POST['votifier_port'] ?? 8192);
        }
        if (!empty($customData)) {
            $data['custom_data'] = json_encode($customData);
        }

        if (Server::update($id, $data)) {
            Server::setCategories($id, $categoryIds, $categoryIds[0]);
            $currentUser = auth();
            AuditLog::log('update', 'servers', $id, $currentUser->id, 'Updated server: ' . $server->name);
            flash('success', lang('server_updated'));
        } else {
            flash('error', lang('server_update_failed'));
        }

        redirect("/admin/servers/edit/{$id}");
    }

    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $server = Server::find($id);
        if (!$server) {
            flash('error', lang('server_not_found_admin'));
            redirect('/admin/servers');
        }

        if (Server::delete($id)) {
            $currentUser = auth();
            AuditLog::log('delete', 'servers', $id, $currentUser->id, 'Deleted server: ' . $server->name);
            flash('success', lang('server_deleted'));
        } else {
            flash('error', lang('server_delete_failed'));
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
            flash('error', lang('server_not_found_admin'));
            redirect('/admin/servers');
        }

        $currentUser = auth();

        switch ($action) {
            case 'activate':
                Server::update($id, ['active' => 1]);
                AuditLog::log('activate', 'servers', $id, $currentUser->id, 'Activated server: ' . $server->name);
                flash('success', lang('server_activated'));
                break;
            case 'deactivate':
                Server::update($id, ['active' => 0]);
                AuditLog::log('deactivate', 'servers', $id, $currentUser->id, 'Deactivated server: ' . $server->name);
                flash('success', lang('server_deactivated'));
                break;
            case 'make_private':
                Server::update($id, ['private' => 1]);
                AuditLog::log('make_private', 'servers', $id, $currentUser->id, 'Made server private: ' . $server->name);
                flash('success', lang('server_made_private'));
                break;
            case 'make_public':
                Server::update($id, ['private' => 0]);
                AuditLog::log('make_public', 'servers', $id, $currentUser->id, 'Made server public: ' . $server->name);
                flash('success', lang('server_made_public'));
                break;
            case 'add_highlight':
                Server::update($id, ['highlight' => 1]);
                AuditLog::log('add_highlight', 'servers', $id, $currentUser->id, 'Added highlight to server: ' . $server->name);
                flash('success', lang('server_highlighted'));
                break;
            case 'remove_highlight':
                Server::update($id, ['highlight' => 0]);
                AuditLog::log('remove_highlight', 'servers', $id, $currentUser->id, 'Removed highlight from server: ' . $server->name);
                flash('success', lang('server_highlight_removed'));
                break;
            case 'delete':
                if (Server::delete($id)) {
                    AuditLog::log('delete', 'servers', $id, $currentUser->id, 'Deleted server: ' . $server->name);
                    flash('success', lang('server_deleted'));
                    redirect('/admin/servers');
                    return;
                }
                break;
            default:
                flash('error', 'Invalid action');
        }

        redirect("/admin/servers/edit/{$id}");
    }
}
