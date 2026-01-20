<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\BlogPost;
use App\Core\Auth;

/**
 * Server blog posts controller.
 *
 * Handles create/update/delete operations for blog posts attached to a server.
 * Most actions support both full-page flows (flash + redirect) and AJAX calls (JSON responses).
 */
class BlogController
{
    /**
     * Create a blog post for a server.
     *
     * Authorization:
     * - server owner (or admin/moderator) can post
     * - supports AJAX via `$_POST['ajax']`
     */
    public function store()
    {
        if (!isLoggedIn()) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => lang('logged_in_action')]);
                return;
            }
            flash('error', lang('logged_in_action'));
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $serverId = (int)($_POST['server_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if (empty($title) || strlen($title) < 3) {
            $message = lang('blog_title_required');
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        if (empty($content) || strlen($content) < 10) {
            $message = lang('blog_content_required');
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $server = Server::find($serverId);
        if (!$server) {
            $message = lang('server_not_found');
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect('/servers');
        }

        $user = auth();
        if ($server->user_id != $user->id && $user->type < 1) {
            $message = lang('cant_access');
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        BlogPost::create([
            'server_id' => $serverId,
            'user_id' => $user->id,
            'title' => $title,
            'content' => $content
        ]);

        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'message' => lang('blog_post_created')]);
            return;
        }

        flash('success', lang('blog_post_created'));
        redirect($_SERVER['HTTP_REFERER'] ?? "/server/{$server->address}:{$server->port}");
    }

    /**
     * Render the blog post edit form.
     */
    public function edit($id)
    {
        if (!isLoggedIn()) {
            flash('error', lang('logged_in_action'));
            redirect('/login');
        }

        $blogPost = BlogPost::find($id);
        if (!$blogPost) {
            flash('error', lang('blog_post_not_found'));
            redirect('/servers');
        }

        $server = Server::find($blogPost->server_id);
        if (!$server) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        $user = auth();
        if ($blogPost->user_id != $user->id && $user->type < 1) {
            flash('error', lang('cant_access'));
            redirect("/server/{$server->address}:{$server->port}");
        }

        view('servers.blog-edit', [
            'blog_post' => $blogPost,
            'server' => $server
        ]);
    }

    /**
     * Persist edits to an existing blog post.
     */
    public function update($id)
    {
        if (!isLoggedIn()) {
            flash('error', lang('logged_in_action'));
            redirect('/login');
        }

        $blogPost = BlogPost::find($id);
        if (!$blogPost) {
            flash('error', lang('blog_post_not_found'));
            redirect('/servers');
        }

        $server = Server::find($blogPost->server_id);
        if (!$server) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        $user = auth();
        if ($blogPost->user_id != $user->id && $user->type < 1) {
            flash('error', lang('cant_access'));
            redirect("/server/{$server->address}:{$server->port}");
        }

        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if (empty($title) || strlen($title) < 3) {
            flash('error', lang('blog_title_required'));
            redirect("/blog/edit/{$id}");
        }

        if (empty($content) || strlen($content) < 10) {
            flash('error', lang('blog_content_required'));
            redirect("/blog/edit/{$id}");
        }

        BlogPost::update($id, [
            'title' => $title,
            'content' => $content
        ]);

        flash('success', lang('blog_post_updated'));
        redirect("/server/{$server->address}:{$server->port}");
    }

    /**
     * Delete a blog post.
     *
     * This endpoint is intended for AJAX usage and always returns JSON.
     */
    public function delete()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => lang('logged_in_action')]);
            return;
        }

        $blogPostId = (int)($_POST['blog_post_id'] ?? 0);
        $blogPost = BlogPost::find($blogPostId);

        if (!$blogPost) {
            echo json_encode(['success' => false, 'message' => lang('blog_post_not_found')]);
            return;
        }

        $user = auth();
        $server = Server::find($blogPost->server_id);
        
        if ($blogPost->user_id != $user->id && $server->user_id != $user->id && $user->type < 1) {
            echo json_encode(['success' => false, 'message' => lang('cant_access')]);
            return;
        }

        BlogPost::delete($blogPostId);
        echo json_encode(['success' => true, 'message' => lang('blog_post_deleted')]);
    }

    /**
     * Paginated "load more" endpoint used by the server page.
     *
     * Returns rendered HTML for the next chunk plus pagination metadata.
     */
    public function loadMore()
    {
        $serverId = (int)($_GET['server_id'] ?? 0);
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 5);

        $blogPosts = BlogPost::getServerPosts($serverId, $limit, $offset);
        $hasMore = count($blogPosts) === $limit;

        ob_start();
        foreach ($blogPosts as $blogPost) {
            include dirname(__DIR__, 2) . '/resources/views/partials/blog-post-item.php';
        }
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'has_more' => $hasMore,
            'next_offset' => $offset + $limit
        ]);
    }
}
