<?php

namespace App\Controllers\Admin;

use App\Models\Server;
use App\Models\ServerCategory;
use App\Models\Category;
use App\Core\Features\Servers;
/**
 * Admin servers controller.
 *
 * Provides moderation and management tools for server listings (search/filter/edit/actions).
 */
class ServerController
{
    /**
     * List servers with admin-only filters.
     */
    public function index(): void
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
        
        $serverIds = array_map(fn($s) => $s->id, $servers);
        $categoriesGrouped = !empty($serverIds) ? ServerCategory::getServerCategoriesForServers($serverIds) : [];
        foreach ($servers as $server) {
            $server->categories = $categoriesGrouped[$server->id] ?? [];
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

    /**
     * Render the edit form for a server.
     */
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

    /**
     * Persist changes to a server.
     */
    public function update($id): void
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

        $data = [
            'name' => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'port' => $_POST['port'] ?? 25565,
            'category_ids' => $_POST['category_ids'] ?? [],
            'description' => cleanHtml($_POST['description'] ?? ''),
            'website' => $_POST['website'] ?? '',
            'country' => $_POST['country'] ?? '',
            'youtube_id' => $_POST['youtube_id'] ?? '',
            'votifier_public_key' => $_POST['votifier_public_key'] ?? '',
            'votifier_ip' => $_POST['votifier_ip'] ?? '',
            'votifier_port' => $_POST['votifier_port'] ?? 8192
        ];

        $currentUser = auth();
        $result = Servers::updateAdmin($id, $currentUser->id, $data, $_FILES);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect("/admin/servers/edit/{$id}");
    }

    /**
     * Delete a server listing.
     */
    public function delete($id): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $currentUser = auth();
        $result = Servers::performAdminAction($id, $currentUser->id, 'delete');

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/servers');
    }

    /**
     * Perform a server moderation action (activate/deactivate/privacy/highlight/delete).
     */
    public function action($id): void
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
        $result = Servers::performAdminAction($id, $currentUser->id, $action);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        if ($result['redirect'] === 'list') {
            redirect('/admin/servers');
        } else {
            redirect("/admin/servers/edit/{$id}");
        }
    }
}
