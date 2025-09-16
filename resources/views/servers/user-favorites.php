<?php ob_start(); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-heart-fill text-danger" style="font-size: 2rem;"></i>
            </div>
            <div>
                <h2 class="h3 fw-bold text-dark mb-1"><?= lang('titles.my_favorites') ?></h2>
                <p class="text-muted mb-0">
                    <?php if (!empty($servers)): ?>
                        <?= count($servers) ?> <?= count($servers) !== 1 ? lang('servers') : lang('server') ?> <?= lang('in_your_favorites') ?>
                    <?php else: ?>
                        <?= lang('start_building_favorites') ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <a href="<?= url('/servers') ?>" class="btn btn-primary px-4">
            <i class="bi bi-search me-2"></i><?= lang('browse_more_servers') ?>
        </a>
    </div>

    <?php if (!empty($servers)): ?>
        <div class="servers-list">
            <?php 
            $show_favorites_actions = true; // Flag to indicate this is favorites page
            foreach ($servers as $server): ?>
                <?php include __DIR__ . '/../partials/server-row-item.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-heart text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 fw-bold text-dark mb-3"><?= lang('no_favorite_servers_yet') ?></h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                <?= lang('favorites_empty_description') ?>
            </p>
            <a href="<?= url('/servers') ?>" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-search me-2"></i><?= lang('browse_more_servers') ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.my_favorites') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>