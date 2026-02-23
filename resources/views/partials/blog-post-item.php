<?php
/** @noinspection PhpUndefinedVariableInspection */
$blogPostId = $blogPost->id;
$blogTitleId = 'blog_title_' . $blogPostId;
$blogContentId = 'blog_content_' . $blogPostId;
$blogTextPlain = trim(preg_replace('/\s+/', ' ', strip_tags($blogPost->content)));
?>

<article class="blog-post-item mb-4" data-blog-id="<?= $blogPostId ?>" tabindex="0" role="article" aria-labelledby="<?= $blogTitleId ?>" aria-describedby="<?= $blogContentId ?>">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h5 class="mb-1 fw-semibold" id="<?= $blogTitleId ?>"><?= sanitize($blogPost->title) ?></h5>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted"><?= timeAgo($blogPost->created_at) ?></small>
            <?php if (isLoggedIn() && (auth()->id == $blogPost->user_id || auth()->type >= 1)): ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="<?= lang('more') ?>">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <?php if (auth()->id == $blogPost->user_id): ?>
                            <li><a class="dropdown-item" href="<?= url('/blog/edit/' . $blogPost->id) ?>">
                                <i class="bi bi-pencil me-1"></i><?= lang('edit') ?>
                            </a></li>
                        <?php endif; ?>
                        <li><button class="dropdown-item text-danger" onclick="deleteBlogPost(<?= $blogPost->id ?>)">
                            <i class="bi bi-trash me-1"></i><?= lang('delete') ?>
                        </button></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="blog-content" id="<?= $blogContentId ?>" tabindex="0" aria-label="<?= sanitize($blogPost->title, ENT_QUOTES) ?>. <?= sanitize($blogTextPlain, ENT_QUOTES) ?>">
        <?= $blogPost->content ?>
    </div>
    <div class="blog-meta mt-3 pt-2 border-top">
        <small class="text-muted">
            <i class="bi bi-person me-1" aria-hidden="true"></i>
            <?= lang('by') ?> <?= sanitize($blogPost->name ?? $blogPost->username) ?>
            <?php if ($blogPost->updated_at !== $blogPost->created_at): ?>
                <span class="ms-2">
                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>
                    <?= lang('updated') ?> <?= timeAgo($blogPost->updated_at) ?>
                </span>
            <?php endif; ?>
        </small>
    </div>
</article>
