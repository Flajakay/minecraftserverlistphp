<?php ob_start(); ?>

<div class="container py-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
        <div class="mb-3 mb-sm-0">
            <h2 class="h3 fw-bold text-dark mb-1">My Servers</h2>
            <p class="text-muted mb-0">Manage your Minecraft servers</p>
        </div>
        <a href="<?= url('/submit') ?>" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-plus-circle me-2"></i>Add New Server
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
                    <small class="text-muted">Total Servers</small>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-success fs-5">
                        <?= array_sum(array_column($servers, 'votes')) ?>
                    </div>
                    <small class="text-muted">Total Votes</small>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-danger fs-5">
                        <?= array_sum(array_column($servers, 'favorites')) ?>
                    </div>
                    <small class="text-muted">Total Favorites</small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-server text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 text-dark mb-3">No servers yet</h3>
            <p class="text-muted mb-4">Add your first Minecraft server to get started.</p>
            <a href="<?= url('/submit') ?>" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-plus-circle me-2"></i>Add Your First Server
            </a>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = 'My Servers - Minecraft Server List'; ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
