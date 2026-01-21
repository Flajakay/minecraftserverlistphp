<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Core\Security\Auth;
use App\Core\Features\Comments;

/**
 * Server comments controller.
 *
 * Supports both standard form submissions (flash + redirect) and AJAX calls (JSON) depending on
 * the presence of `$_POST['ajax']`.
 */
class CommentController
{
    /**
     * Create a comment for a server.
     */
    public function store()
    {
        if (!isLoggedIn()) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => lang('logged_in_action')]);
                return;
            }
            flash('error', lang('login_required_comment'));
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $serverId = (int)($_POST['server_id'] ?? 0);
        $comment = sanitize($_POST['comment'] ?? '');
        $type = (int)($_POST['type'] ?? 0);
        $user = auth();

        $result = Comments::createComment($serverId, $user->id, $comment, $type);

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
     * Delete a comment.
     *
     * Authorization: comment author, server owner, or privileged user.
     */
    public function delete()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Must be logged in']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $user = auth();

        $result = Comments::deleteComment($commentId, $user->id);

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
