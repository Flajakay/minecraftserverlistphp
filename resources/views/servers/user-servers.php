<?php ob_start(); ?>

<div class="container py-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
        <div class="mb-3 mb-sm-0">
            <h2 class="h3 fw-bold text-dark mb-1"><?= lang('my_servers') ?></h2>
            <p class="text-muted mb-0"><?= lang('manage_servers') ?></p>
        </div>
        <a href="<?= url('/submit') ?>" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-plus-circle me-2"></i><?= lang('add_new_server') ?>
        </a>
    </div>

    <?php if (!empty($servers)): ?>
        <div class="my-servers-list">
            <?php foreach ($servers as $server): ?>
                <?php 
                // Set flag for management actions in the partial
                $show_management_actions = true;
                include __DIR__ . '/../partials/server-row-item.php'; 
                ?>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="fw-semibold text-primary fs-5"><?= count($servers) ?></div>
                    <small class="text-muted"><?= lang('total_servers') ?></small>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-success fs-5">
                        <?= array_sum(array_column($servers, 'votes')) ?>
                    </div>
                    <small class="text-muted"><?= lang('total_votes') ?></small>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-danger fs-5">
                        <?= array_sum(array_column($servers, 'favorites')) ?>
                    </div>
                    <small class="text-muted"><?= lang('total_favorites') ?></small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-server text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 text-dark mb-3"><?= lang('no_servers_yet') ?></h3>
            <p class="text-muted mb-4"><?= lang('add_first_server_message') ?></p>
            <a href="<?= url('/submit') ?>" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-plus-circle me-2"></i><?= lang('add_your_first_server') ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('my_servers') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
