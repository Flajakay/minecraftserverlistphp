<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Server;
use App\Core\SEO;

class CategoryController
{
    public function show($url, $page = 1)
    {
        $category = Category::findByUrl($url);
        if (!$category) {
            flash('error', 'Category not found');
            redirect('/servers');
        }
        
        SEO::configureCategoryPage($category);

        $page = max(1, (int)$page);
        $perPage = Setting::getValue('servers_pagination', 15);
        $offset = ($page - 1) * $perPage;

        $filters = [
            'category_id' => $category->id,
            'limit' => $perPage,
            'offset' => $offset
        ];

        if (isset($_GET['order_by'])) {
            $filters['order_by'] = $_GET['order_by'];
        }

        if (isset($_GET['country'])) {
            $filters['country'] = $_GET['country'];
        }

        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $servers = Server::getAll($filters);
        $totalServers = Server::count($filters);
        $totalPages = ceil($totalServers / $perPage);

        view('categories.show', [
            'category' => $category,
            'servers' => $servers,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'filters' => $filters
        ]);
    }
}
