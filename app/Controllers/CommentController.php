<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Comment;
use App\Core\Auth;

class CommentController
{
    public function store()
    {
        if (!isLoggedIn()) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => 'Must be logged in']);
                return;
            }
            flash('error', 'You must be logged in to comment');
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $serverId = (int)($_POST['server_id'] ?? 0);
        $comment = sanitize($_POST['comment'] ?? '');
        $type = (int)($_POST['type'] ?? 0);

        if (empty($comment) || strlen($comment) < 5) {
            $message = 'Comment must be at least 5 characters long';
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        if (strlen($comment) > 512) {
            $message = 'Comment is too long (max 512 characters)';
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $server = Server::find($serverId);
        if (!$server) {
            $message = 'Server not found';
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $message]);
                return;
            }
            flash('error', $message);
            redirect('/servers');
        }

        Comment::create([
            'server_id' => $serverId,
            'user_id' => auth()->id,
            'type' => $type,
            'comment' => $comment
        ]);

        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
            return;
        }

        flash('success', 'Comment added successfully');
        redirect($_SERVER['HTTP_REFERER'] ?? "/server/{$server->address}:{$server->port}");
    }

    public function delete()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Must be logged in']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $comment = Comment::find($commentId);

        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Comment not found']);
            return;
        }

        $user = auth();
        $server = Server::find($comment->server_id);
        
        if ($comment->user_id != $user->id && $server->user_id != $user->id && $user->type < 1) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        Comment::delete($commentId);
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
    }

    public function loadMore()
    {
        $serverId = (int)($_GET['server_id'] ?? 0);
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);

        $comments = Comment::getServerComments($serverId, $limit, $offset);
        $hasMore = count($comments) === $limit;

        ob_start();
        foreach ($comments as $comment) {
            include dirname(__DIR__, 2) . '/resources/views/partials/comment-item.php';
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
