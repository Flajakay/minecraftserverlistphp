<div class="blog-post-item mb-4" data-blog-id="<?= /** @noinspection PhpUndefinedVariableInspection */
$blogPost->id ?>">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h5 class="mb-1 fw-semibold"><?= htmlspecialchars($blogPost->title) ?></h5>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted"><?= timeAgo($blogPost->created_at) ?></small>
            <?php if (isLoggedIn() && (auth()->id == $blogPost->user_id || auth()->type >= 1)): ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
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
    <div class="blog-content">
        <?= $blogPost->content ?>
    </div>
    <div class="blog-meta mt-3 pt-2 border-top">
        <small class="text-muted">
            <i class="bi bi-person me-1"></i>
            <?= lang('by') ?> <?= htmlspecialchars($blogPost->name ?? $blogPost->username) ?>
            <?php if ($blogPost->updated_at !== $blogPost->created_at): ?>
                <span class="ms-2">
                    <i class="bi bi-pencil me-1"></i>
                    <?= lang('updated') ?> <?= timeAgo($blogPost->updated_at) ?>
                </span>
            <?php endif; ?>
        </small>
    </div>
</div>
