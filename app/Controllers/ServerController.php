<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\BlogPost;
use App\Core\Auth;
use App\Core\MinecraftPing;
use App\Core\Database;
use App\Core\SEO;

class ServerController
{
    public function index($page = 1)
    {
        SEO::setDescription(lang('seo.servers_description'));
        SEO::setKeywords(lang('seo.keywords_default'));
        
        $page = max(1, (int)$page);
        $perPage = Setting::getValue('servers_pagination', 15);
        $offset = ($page - 1) * $perPage;

        $filters = [
            'limit' => $perPage,
            'offset' => $offset
        ];

        if (isset($_GET['categories']) && !empty($_GET['categories'])) {
            $categoryIds = explode(',', $_GET['categories']);
            $categoryIds = array_filter(array_map('intval', $categoryIds));
            if (!empty($categoryIds)) {
                $filters['categories'] = $categoryIds;
            }
        }

        if (isset($_GET['include_subcategories']) && $_GET['include_subcategories'] == '1') {
            $filters['include_subcategories'] = true;
        }

        if (isset($_GET['order_by'])) {
            $filters['order_by'] = $_GET['order_by'];
        }

        if (isset($_GET['country'])) {
            $filters['country'] = $_GET['country'];
        }

        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        if (isset($_GET['highlight'])) {
            $filters['highlight'] = $_GET['highlight'];
        }

        $servers = Server::getAll($filters);
        $totalServers = Server::count($filters);
        $totalPages = ceil($totalServers / $perPage);

        $userFavorites = [];
        if (isLoggedIn()) {
            $serverIds = array_map(fn($s) => $s->id, $servers);
            $userFavorites = Favorite::getForUserByServerIds(auth()->id, $serverIds);
        }

        $categories = Category::getAllWithHierarchy();

        foreach ($servers as $server) {
            $server->categories = Server::getCategories($server->id);
            //$server->primary_category = Server::getPrimaryCategory($server->id);
        }

        view('servers.index', [
            'servers' => $servers,
            'categories' => $categories,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'filters' => $filters,
            'user_favorites' => $userFavorites
        ]);
    }

    public function show($address, $port)
    {
        $server = Server::findByAddress($address, $port);
        
        if (!$server || (!$server->active || $server->private)) {
            flash('error', 'Server not found');
            redirect('/servers');
        }
        
        SEO::configureServerPage($server);

        Vote::recordHit($server->id, $_SERVER['REMOTE_ADDR']);

        $comments = Comment::getServerComments($server->id, 10);
        $blogPosts = BlogPost::getServerPosts($server->id, 5);
        $blogPostsCount = BlogPost::countServerPosts($server->id);
        $categories = Server::getCategories($server->id);
        $statistics = Vote::getStatistics($server->id, 7);
        $monthlyVotes = Vote::getServerVotes($server->id, 'month');
        $monthlyHits = Vote::getServerHits($server->id, 'month');
        
        $isOwner = isLoggedIn() && auth()->id == $server->user_id;
        $canVote = isLoggedIn() && Vote::canVote($server->id, $_SERVER['REMOTE_ADDR']);
        $isFavorite = isLoggedIn() && Favorite::exists(auth()->id, $server->id);

        view('servers.show', [
            'server' => $server,
            'categories' => $categories,
            'comments' => $comments,
            'blog_posts' => $blogPosts,
            'blog_posts_count' => $blogPostsCount,
            'statistics' => $statistics,
            'monthly_votes' => $monthlyVotes,
            'monthly_hits' => $monthlyHits,
            'is_owner' => $isOwner,
            'can_vote' => $canVote,
            'is_favorite' => $isFavorite
        ]);
    }

    public function showSubmit()
    {
        if (!isLoggedIn()) {
            flash('error', 'You must be logged in to submit a server');
            redirect('/login');
        }
        
        $categories = Category::getAllForSelect();
        $countries = getCountries();

        view('servers.submit', [
            'categories' => $categories,
            'countries' => $countries
        ]);
    }

    public function submit()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $address = sanitize($_POST['address'] ?? '');
        $port = (int)($_POST['port'] ?? 25565);
        $name = sanitize($_POST['name'] ?? '');
        $categoryIds = $_POST['category_ids'] ?? [];
        //$primaryCategoryId = (int)($_POST['primary_category_id'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $country = sanitize($_POST['country'] ?? 'US');
        $youtubeId = sanitize($_POST['youtube_id'] ?? '');

        $errors = [];

        if (empty($address)) {
            $errors[] = 'Server address is required';
        }

        if (Server::exists($address, $port)) {
            $errors[] = 'Server already exists';
        }

        if (strlen($name) < 3 || strlen($name) > 64) {
            $errors[] = 'Server name must be between 3 and 64 characters';
        }

        if (empty($categoryIds) || !is_array($categoryIds)) {
            $errors[] = 'At least one category must be selected';
        } else {
            $categoryIds = array_filter(array_map('intval', $categoryIds));
            if (empty($categoryIds)) {
                $errors[] = 'Invalid categories selected';
            }
            
            if ($primaryCategoryId && !in_array($primaryCategoryId, $categoryIds)) {
                $errors[] = 'Primary category must be one of the selected categories';
            }
        }

        if (strlen($description) > 2560) {
            $errors[] = 'Description is too long (max 2560 characters)';
        }

        $serverStatus = MinecraftPing::checkServer($address, $port);
        if (!$serverStatus['online']) {
            $errors[] = 'Server is offline or unreachable';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('/submit');
        }

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], 'banners');
            if (!$image) {
                flash('error', 'Failed to upload banner image');
                redirect('/submit');
            }
        }

        $customData = [];
        if (!empty($_POST['votifier_public_key'])) {
            $customData['votifier_public_key'] = $_POST['votifier_public_key'];
            $customData['votifier_ip'] = $_POST['votifier_ip'] ?? $address;
            $customData['votifier_port'] = (int)($_POST['votifier_port'] ?? 8192);
        }

        $serverId = Server::create([
            'user_id' => auth()->id,
            'category_id' => $primaryCategoryId ?: $categoryIds[0],
            'address' => $address,
            'port' => $port,
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'website' => $website,
            'country' => $country,
            'youtube_id' => $youtubeId,
            'players' => $serverStatus['players'],
            'max_players' => $serverStatus['max_players'],
            'version' => $serverStatus['version'],
            'custom_data' => json_encode($customData)
        ]);

        Server::setCategories($serverId, $categoryIds, $primaryCategoryId ?: $categoryIds[0]);

        flash('success', 'Server added successfully! It will be reviewed before being made public.');
        redirect('/my-servers');
    }

    public function userServers()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $servers = Server::getUserServers(auth()->id);

        foreach ($servers as $server) {
            $server->categories = Server::getCategories($server->id);
            //$server->primary_category = Server::getPrimaryCategory($server->id);
        }

        $userFavorites = [];
        if (isLoggedIn()) {
            $serverIds = array_map(fn($s) => $s->id, $servers);
            $userFavorites = Favorite::getForUserByServerIds(auth()->id, $serverIds);
        }

        view('servers.user-servers', [
            'servers' => $servers,
            'user_favorites' => $userFavorites
        ]);
    }

    public function userFavorites()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $servers = Favorite::getUserFavorites(auth()->id);

        foreach ($servers as $server) {
            $server->categories = Server::getCategories($server->id);
            //$server->primary_category = Server::getPrimaryCategory($server->id);
        }

        $userFavorites = [];
        if (isLoggedIn()) {
            $serverIds = array_map(fn($s) => $s->id, $servers);
            $userFavorites = Favorite::getForUserByServerIds(auth()->id, $serverIds);
        }

        view('servers.user-favorites', [
            'servers' => $servers,
            'user_favorites' => $userFavorites
        ]);
    }

    public function edit($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find($id);
        if (!$server || $server->user_id != auth()->id) {
            flash('error', 'Server not found or access denied');
            redirect('/my-servers');
        }

        $categories = Category::getAllForSelect();
        $countries = getCountries();
        $serverCategories = Server::getCategories($id);

        view('servers.edit', [
            'server' => $server,
            'categories' => $categories,
            'countries' => $countries,
            'server_categories' => $serverCategories
        ]);
    }

    public function update($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find($id);
        if (!$server || $server->user_id != auth()->id) {
            flash('error', 'Server not found or access denied');
            redirect('/my-servers');
        }

        $name = sanitize($_POST['name'] ?? '');
        $categoryIds = $_POST['category_ids'] ?? [];
        $description = sanitize($_POST['description'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $country = sanitize($_POST['country'] ?? 'US');
        $youtubeId = sanitize($_POST['youtube_id'] ?? '');

        $errors = [];

        if (strlen($name) < 3 || strlen($name) > 64) {
            $errors[] = 'Server name must be between 3 and 64 characters';
        }

        if (empty($categoryIds) || !is_array($categoryIds)) {
            $errors[] = 'At least one category must be selected';
        } else {
            $categoryIds = array_filter(array_map('intval', $categoryIds));
            if (empty($categoryIds)) {
                $errors[] = 'Invalid categories selected';
            }
        }

        if (strlen($description) > 2560) {
            $errors[] = 'Description is too long (max 2560 characters)';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect("/edit-server/{$id}");
        }

        $updateData = [
            'name' => $name,
            'category_id' => $categoryIds[0],
            'description' => $description,
            'website' => $website,
            'country' => $country,
            'youtube_id' => $youtubeId
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], 'banners');
            if ($image) {
                $updateData['image'] = $image;
            }
        }

        $customData = [];
        if (!empty($_POST['votifier_public_key'])) {
            $customData['votifier_public_key'] = $_POST['votifier_public_key'];
            $customData['votifier_ip'] = $_POST['votifier_ip'] ?? $server->address;
            $customData['votifier_port'] = (int)($_POST['votifier_port'] ?? 8192);
        }
        
        if (!empty($customData)) {
            $updateData['custom_data'] = json_encode($customData);
        }

        Database::update('servers', $updateData, 'id = ?', [$id]);
        Server::setCategories($id, $categoryIds, $categoryIds[0]);

        flash('success', 'Server updated successfully');
        redirect('/my-servers');
    }

    public function action($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find($id);
        if (!$server || $server->user_id != auth()->id) {
            flash('error', 'Server not found or access denied');
            redirect('/my-servers');
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'make_public':
                Server::setPrivate($id, 0);
                flash('success', 'Server is now public');
                break;
                
            case 'make_private':
                Server::setPrivate($id, 1);
                flash('success', 'Server is now private');
                break;
                
            case 'delete':
                Server::delete($id);
                flash('success', 'Server deleted successfully');
                redirect('/my-servers');
                return;
                
            default:
                flash('error', 'Invalid action');
        }

        redirect('/edit-server/' . $id);
    }
}
