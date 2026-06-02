<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\BlogPost;
use App\Models\PlayerHistory;
use App\Core\Support\SEO;
use App\Core\Features\Servers;

class ServerController
{
    public function index($page = 1): void
    {
        SEO::setDescription(lang('seo.servers_description'));
        SEO::setKeywords(lang('seo.keywords_default'));

        $page = max(1, (int) $page);
        $perPage = Setting::getValue('servers_pagination', 15);
        $offset = ($page - 1) * $perPage;

        $filters = Servers::buildFiltersFromRequest($_GET, $perPage, $offset);

        $servers = Server::getAll($filters);
        $totalServers = Server::count($filters);
        $totalPages = ceil($totalServers / $perPage);

        $categories = Category::getAllWithHierarchy();

        foreach ($servers as $server) {
            $server->categories = Server::getCategories($server->id);
        }

        view('servers.index', [
            'servers' => $servers,
            'categories' => $categories,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'filters' => $filters
        ]);
    }

    public function show($address, $port): void
    {
        $server = Server::findByAddress($address, $port);
        $effectiveOwnerId = Server::getEffectiveOwnerUserId($server);
        $isOwner = isLoggedIn() && $effectiveOwnerId && auth()->id == $effectiveOwnerId;
        $isAdmin = isLoggedIn() && isAdmin();

        // Block access if: server doesn't exist, is inactive, or is private and user is neither owner nor admin
        if (!$server || !$server->active || ($server->private && !$isOwner && !$isAdmin)) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        SEO::configureServerPage($server);

        Vote::recordHit($server->id, $_SERVER['REMOTE_ADDR']);

        $comments = Comment::getServerComments($server->id, 10);
        $blogPosts = BlogPost::getServerPosts($server->id, 5);
        $blogPostsCount = BlogPost::countServerPosts($server->id);
        $categories = Server::getCategories($server->id);
        $statistics = PlayerHistory::getStatistics($server->id, 7);
        $monthlyVotes = Vote::getServerVotes($server->id, 'month');
        $monthlyHits = Vote::getServerHits($server->id, 'month');

        $canVote = isLoggedIn() && Vote::canVote($server->id, $_SERVER['REMOTE_ADDR']);

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
            'effective_owner_id' => $effectiveOwnerId,
            'is_verified' => (int) ($server->verification_status ?? 0) === 2,
            'can_vote' => $canVote
        ]);
    }

    public function showSubmit(): void
    {
        if (!isLoggedIn()) {
            flash('error', lang('login_required_submit'));
            redirect('/login');
        }

        $categories = Category::getAllForSelect();
        $countries = getCountries();

        view('servers.submit', [
            'categories' => $categories,
            'countries' => $countries
        ]);
    }

    public function submit(): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $data = [
            'address' => sanitize($_POST['address'] ?? ''),
            'port' => validatePort($_POST['port'] ?? 25565),
            'name' => sanitize($_POST['name'] ?? ''),
            'category_ids' => $_POST['category_ids'] ?? [],
            'primary_category_id' => (int) ($_POST['primary_category_id'] ?? 0),
            'description' => cleanHtml(trim($_POST['description'] ?? '')),
            'website' => sanitize($_POST['website'] ?? ''),
            'country' => sanitize($_POST['country'] ?? ''),
            'youtube_id' => sanitize($_POST['youtube_id'] ?? ''),
            'votifier_public_key' => sanitize($_POST['votifier_public_key'] ?? ''),
            'votifier_ip' => sanitize($_POST['votifier_ip'] ?? ''),
            'votifier_port' => validatePort($_POST['votifier_port'] ?? 8192)
        ];

        $result = Servers::submitServer(auth()->id, $data, $_FILES);

        if (!$result['success']) {
            foreach ($result['errors'] as $error) {
                flash('error', $error);
            }
            redirect('/submit');
        }

        flash('success', $result['message']);
        redirect('/profile/' . auth()->username);
    }

    public function edit($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $result = Servers::getEditPageData($id, auth()->id);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/profile/' . auth()->username);
        }

        view('servers.edit', [
            'server' => $result['server'],
            'categories' => $result['categories'],
            'countries' => $result['countries'],
            'server_categories' => $result['server_categories']
        ]);
    }

    public function update($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'category_ids' => $_POST['category_ids'] ?? [],
            'description' => cleanHtml($_POST['description'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'country' => sanitize($_POST['country'] ?? ''),
            'youtube_id' => sanitize($_POST['youtube_id'] ?? ''),
            'votifier_public_key' => sanitize($_POST['votifier_public_key'] ?? ''),
            'votifier_ip' => sanitize($_POST['votifier_ip'] ?? ''),
            'votifier_port' => validatePort($_POST['votifier_port'] ?? 8192)
        ];

        $result = Servers::updateServer($id, auth()->id, $data, $_FILES);

        if (!$result['success']) {
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    flash('error', $error);
                }
                redirect("/edit-server/{$id}");
            }
            flash('error', $result['error']);
            redirect('/profile/' . auth()->username);
        }

        flash('success', $result['message']);
        redirect('/profile/' . auth()->username);
    }

    public function action($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $action = $_POST['action'] ?? '';
        $result = Servers::performAction($id, auth()->id, $action);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/profile/' . auth()->username);
        }

        flash('success', $result['message']);

        if ($result['redirect'] === 'profile') {
            redirect('/profile/' . auth()->username);
        }

        redirect('/edit-server/' . $id);
    }
}
