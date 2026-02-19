<?php ob_start(); ?>

<div class="text-center py-5">
    <div class="mb-4">
        <i class="bi bi-exclamation-triangle display-1 text-muted"></i>
    </div>
    <h1 class="display-4">404</h1>
    <h2 class="mb-3"><?= lang('page_not_found_title') ?></h2>
    <p class="lead text-muted mb-4">
        <?= lang('page_not_found_description') ?>
    </p>
    <div class="d-grid gap-2 d-md-block">
        <a href="<?= url('/') ?>" class="btn btn-primary">
            <i class="bi bi-house"></i> <?= lang('go_home') ?>
        </a>
        <a href="<?= url('/servers') ?>" class="btn btn-outline-primary">
            <i class="bi bi-server"></i> <?= lang('browse_servers') ?>
        </a>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('page_not_found_title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
