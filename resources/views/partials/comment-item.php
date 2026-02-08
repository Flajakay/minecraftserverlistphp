<div class="comment-item border-bottom py-3">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center mb-1">
                <strong class="me-2"><?= /** @noinspection PhpUndefinedVariableInspection */
                    htmlspecialchars($comment->name ?: $comment->username) ?></strong>
                <small class="text-muted"><?= timeAgo($comment->created_at) ?></small>
            </div>
            <p class="mb-0"><?= nl2br(htmlspecialchars($comment->comment)) ?></p>
        </div>
        <?php if (isLoggedIn()): ?>
            <?php 
            $user = auth();
            /** @noinspection PhpUndefinedVariableInspection */
            $canDelete = $comment->user_id == $user->id ||
                        ($server && $server->user_id == $user->id) || 
                        $user->type >= 1;
            ?>
            <?php if ($canDelete): ?>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="deleteComment(<?= $comment->id ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteComment(commentId) {
    if (!confirm('<?= lang('confirm_delete_comment') ?>')) {
        return;
    }
    
    fetch('<?= url('/comment/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            comment_id: commentId,
            csrf_token: '<?= csrf() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '<?= lang('error_occurred') ?>');
        }
    });
}
</script>
