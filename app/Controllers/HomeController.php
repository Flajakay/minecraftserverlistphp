<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Category;
use App\Models\Favorite;
use App\Core\SEO;

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

        $userFavorites = [];
        if (isLoggedIn()) {
            $allServers = array_merge($featuredServers, $recentServers, $topVotedServers);
            $serverIds = array_map(fn($s) => $s->id, $allServers);
            $serverIds = array_unique($serverIds);
            $userFavorites = Favorite::getForUserByServerIds(auth()->id, $serverIds);
        }
        
        $categories = Category::getWithServerCount();
        
        view('home', [
            'featured_servers' => $featuredServers,
            'recent_servers' => $recentServers,
            'top_voted_servers' => $topVotedServers,
            'categories' => $categories,
            'user_favorites' => $userFavorites
        ]);
    }
}
