<?php ob_start(); ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 fw-semibold">
                            <i class="bi bi-pencil text-primary me-2"></i><?= lang('edit_blog_post') ?>
                        </h4>
                        <a href="<?= /** @noinspection PhpUndefinedVariableInspection */
                        url("/server/{$server->address}:{$server->port}") ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i><?= lang('back_to_server') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="blogForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold"><?= lang('blog_title') ?> *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?= /** @noinspection PhpUndefinedVariableInspection */
                                   sanitize($blog_post->title) ?>"
                                   maxlength="255" 
                                   required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="form-label fw-semibold"><?= lang('blog_content') ?> *</label>
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control"
                                      required><?= sanitize($blog_post->content) ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i><?= lang('update_blog_post') ?>
                            </button>
                            <a href="<?= url("/server/{$server->address}:{$server->port}") ?>" class="btn btn-outline-secondary">
                                <?= lang('cancel') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Jodit Helper -->
<?php joditAssets(); ?>

<!-- Load Blog Edit JavaScript -->
<script src="<?= asset('js/blog-edit.js') ?>"></script>

<script>
// Global data for JavaScript
window.csrfToken = '<?= csrf() ?>';
window.lang = {
    content_placeholder: '<?= lang('content_placeholder') ?>',
    blog_content_required: '<?= lang('blog_content_required') ?>',
    jodit_code: '<?= lang('_jodit_code', 'en') ?>'
};
</script>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('edit_blog_post') . ' - ' . sanitize($server->name); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
