<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\BlogPost;
use App\Models\User;

class Blog
{
    public static function validateBlogPost($title, $content)
    {
        $errors = [];

        if (empty($title) || strlen($title) < 3) {
            $errors[] = lang('blog_title_required');
        }

        if (empty($content) || strlen($content) < 10) {
            $errors[] = lang('blog_content_required');
        }

        return $errors;
    }

    public static function canCreateBlogPost($user, $server)
    {
        if (!$user || !$server) {
            return false;
        }

        return $server->user_id == $user->id || $user->type >= 1;
    }

    public static function canManageBlogPost($user, $blogPost, $server = null)
    {
        if (!$user || !$blogPost) {
            return false;
        }

        if ($blogPost->user_id == $user->id) {
            return true;
        }

        if ($server && $server->user_id == $user->id) {
            return true;
        }

        return $user->type >= 1;
    }

    public static function createBlogPost($serverId, $userId, $title, $content)
    {
        $errors = self::validateBlogPost($title, $content);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => $errors[0],
                'errors' => $errors
            ];
        }

        $server = Server::find($serverId);
        if (!$server) {
            return [
                'success' => false,
                'error' => lang('server_not_found')
            ];
        }

        $user = User::find($userId);
        if (!self::canCreateBlogPost($user, $server)) {
            return [
                'success' => false,
                'error' => lang('cant_access')
            ];
        }

        $blogPostId = BlogPost::create([
            'server_id' => $serverId,
            'user_id' => $userId,
            'title' => $title,
            'content' => $content
        ]);

        return [
            'success' => true,
            'message' => lang('blog_post_created'),
            'blogPostId' => $blogPostId,
            'server' => $server
        ];
    }

    public static function updateBlogPost($blogPostId, $userId, $title, $content)
    {
        $errors = self::validateBlogPost($title, $content);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => $errors[0]
            ];
        }

        $blogPost = BlogPost::find($blogPostId);
        if (!$blogPost) {
            return [
                'success' => false,
                'error' => lang('blog_post_not_found')
            ];
        }

        $server = Server::find($blogPost->server_id);
        if (!$server) {
            return [
                'success' => false,
                'error' => lang('server_not_found')
            ];
        }

        $user = User::find($userId);
        if (!self::canManageBlogPost($user, $blogPost, $server)) {
            return [
                'success' => false,
                'error' => lang('cant_access')
            ];
        }

        BlogPost::update($blogPostId, [
            'title' => $title,
            'content' => $content
        ]);

        return [
            'success' => true,
            'message' => lang('blog_post_updated'),
            'server' => $server
        ];
    }

    public static function deleteBlogPost($blogPostId, $userId)
    {
        $blogPost = BlogPost::find($blogPostId);
        if (!$blogPost) {
            return [
                'success' => false,
                'error' => lang('blog_post_not_found')
            ];
        }

        $server = Server::find($blogPost->server_id);
        $user = User::find($userId);
        
        if (!self::canManageBlogPost($user, $blogPost, $server)) {
            return [
                'success' => false,
                'error' => lang('cant_access')
            ];
        }

        BlogPost::delete($blogPostId);

        return [
            'success' => true,
            'message' => lang('blog_post_deleted')
        ];
    }

    public static function getBlogPostForEdit($blogPostId, $userId)
    {
        $blogPost = BlogPost::find($blogPostId);
        if (!$blogPost) {
            return [
                'success' => false,
                'error' => lang('blog_post_not_found')
            ];
        }

        $server = Server::find($blogPost->server_id);
        if (!$server) {
            return [
                'success' => false,
                'error' => lang('server_not_found')
            ];
        }

        $user = User::find($userId);
        if (!self::canManageBlogPost($user, $blogPost, $server)) {
            return [
                'success' => false,
                'error' => lang('cant_access')
            ];
        }

        return [
            'success' => true,
            'blogPost' => $blogPost,
            'server' => $server
        ];
    }
}
