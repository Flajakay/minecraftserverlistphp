<?php

namespace App\Controllers\Admin;

use App\Models\BlogPost;
use App\Models\Server;
use App\Models\User;

/**
 * Admin blog posts controller.
 *
 * Allows admins to browse and delete blog posts across all servers/users.
 */
class BlogController
{
    /**
     * List blog posts with optional filters.
     */
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = sanitize($_GET['search'] ?? '');
        $limit = 20;

        $filters = [
            'server_id' => $_GET['server_id'] ?? '',
            'user_id' => $_GET['user_id'] ?? ''
        ];

        $blogPosts = BlogPost::getAllPaginated($page, $limit, $search, $filters);
        $totalBlogPosts = BlogPost::countAllAdmin($search, $filters);
        $totalPages = ceil($totalBlogPosts / $limit);

        $servers = Server::getAll(['limit' => 1000]);
        $users = User::getAll(['limit' => 1000]);

        view('admin.blog-posts', [
            'blog_posts' => $blogPosts,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_blog_posts' => $totalBlogPosts,
            'search' => $search,
            'filters' => $filters,
            'servers' => $servers,
            'users' => $users
        ]);
    }

    /**
     * Delete a blog post.
     *
     * Supports both AJAX (JSON) and standard form submissions (flash + redirect).
     */
    public function delete($id)
    {
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $blogPost = BlogPost::find($id);
        if (!$blogPost) {
            echo json_encode(['success' => false, 'message' => 'Blog post not found']);
            return;
        }

        BlogPost::delete($id);

        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully']);
            return;
        }

        flash('success', 'Blog post deleted successfully');
        redirect('/admin/blog-posts');
    }
}
