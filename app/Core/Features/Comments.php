<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\Comment;
use App\Models\User;

class Comments
{
    public static function validateComment($comment)
    {
        $errors = [];

        if (empty($comment) || strlen($comment) < 5) {
            $errors[] = 'Comment must be at least 5 characters long';
        }

        if (strlen($comment) > 512) {
            $errors[] = 'Comment is too long (max 512 characters)';
        }

        return $errors;
    }

    public static function canDeleteComment($user, $comment, $server)
    {
        if (!$user || !$comment) {
            return false;
        }

        // Comment author can delete their own comment
        if ($comment->user_id == $user->id) {
            return true;
        }

        // Server owner can moderate comments on their server
        if ($server && $server->user_id == $user->id) {
            return true;
        }

        // Admins can delete any comment
        return $user->type >= 1;
    }

    public static function createComment($serverId, $userId, $comment, $type = 0)
    {
        $errors = self::validateComment($comment);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => $errors[0]
            ];
        }

        $server = Server::find($serverId);
        if (!$server) {
            return [
                'success' => false,
                'error' => 'Server not found'
            ];
        }

        $commentId = Comment::create([
            'server_id' => $serverId,
            'user_id' => $userId,
            'type' => $type,
            'comment' => $comment
        ]);

        return [
            'success' => true,
            'message' => lang('comment_added'),
            'commentId' => $commentId,
            'server' => $server
        ];
    }

    public static function deleteComment($commentId, $userId)
    {
        $comment = Comment::find($commentId);
        if (!$comment) {
            return [
                'success' => false,
                'error' => 'Comment not found'
            ];
        }

        $server = Server::find($comment->server_id);
        $user = User::find($userId);

        if (!self::canDeleteComment($user, $comment, $server)) {
            return [
                'success' => false,
                'error' => 'Access denied'
            ];
        }

        Comment::delete($commentId);

        return [
            'success' => true,
            'message' => 'Comment deleted successfully'
        ];
    }
}
