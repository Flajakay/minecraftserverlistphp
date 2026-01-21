<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Category;
use App\Core\SEO;

/**
 * Home page controller.
 *
 * Keeps the controller focused on orchestration: fetch a few server collections for the landing page,
 * enrich them with categories for display, and pass everything to the view.
 */
class HomeController
{

    public function index()
    {
        SEO::configureHomePage();
        
        $featuredServers = Server::getAll([
            'highlight' => 1,
            'limit' => 10
        ]);
        
        $recentServers = Server::getAll([
            'order_by' => 'created_at',
            'limit' => 10
        ]);
        
        $topVotedServers = Server::getAll([
            'order_by' => 'votes',
            'limit' => 10
        ]);

        $allServers = array_merge($featuredServers, $recentServers, $topVotedServers);
        foreach ($allServers as $server) {
            $server->categories = Server::getCategories($server->id);
        }
        
        $categories = Category::getWithServerCount();
        
        view('home', [
            'featured_servers' => $featuredServers,
            'recent_servers' => $recentServers,
            'top_voted_servers' => $topVotedServers,
            'categories' => $categories
        ]);
    }
}
