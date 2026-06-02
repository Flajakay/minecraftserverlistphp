<?php
/** @noinspection PhpUndefinedVariableInspection */
$commentAuthor = sanitize($comment->name ?: $comment->username);
$commentTextPlain = trim(preg_replace('/\s+/', ' ', sanitize($comment->comment)));
$commentMetaId = 'comment_meta_' . $comment->id;
$commentBodyId = 'comment_body_' . $comment->id;
?>

<article class="comment-item border-bottom py-3" tabindex="0" role="article" aria-labelledby="<?= $commentMetaId ?>" aria-describedby="<?= $commentBodyId ?>">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center mb-1" id="<?= $commentMetaId ?>">
                <strong class="me-2"><?= $commentAuthor ?></strong>
                <small class="text-muted"><?= timeAgo($comment->created_at) ?></small>
            </div>
            <p class="mb-0" id="<?= $commentBodyId ?>" tabindex="0" aria-label="<?= $commentAuthor ?>. <?= timeAgo($comment->created_at) ?>. <?= sanitize($commentTextPlain, ENT_QUOTES) ?>"><?= nl2br(sanitize($comment->comment)) ?></p>
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
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="deleteComment(<?= $comment->id ?>)" aria-label="<?= lang('delete') ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
