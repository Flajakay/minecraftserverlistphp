<?php

namespace App\Controllers;

use App\Core\Security\Auth;
use App\Core\Features\Blog;

/**
 * Server blog posts controller.
 *
 * Handles HTTP layer for create/update/delete operations for blog posts attached to a server.
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
        $user = auth();

        $result = Blog::createBlogPost($serverId, $user->id, $title, $content);

        if (!$result['success']) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $result['error']]);
                return;
            }
            flash('error', $result['error']);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'message' => $result['message']]);
            return;
        }

        $server = $result['server'];
        flash('success', $result['message']);
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

        $user = auth();
        $result = Blog::getBlogPostForEdit($id, $user->id);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/servers');
        }

        view('servers.blog-edit', [
            'blog_post' => $result['blogPost'],
            'server' => $result['server']
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

        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $user = auth();

        $result = Blog::updateBlogPost($id, $user->id, $title, $content);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect("/blog/edit/{$id}");
        }

        $server = $result['server'];
        flash('success', $result['message']);
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
        $user = auth();
        
        $result = Blog::deleteBlogPost($blogPostId, $user->id);

        echo json_encode($result);
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
