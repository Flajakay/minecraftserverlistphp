<?php ob_start(); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1"><?= lang('titles.servers') ?></h2>
            <p class="text-muted mb-0"><?= lang('servers_subtitle') ?></p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a href="<?= url('/submit') ?>" class="btn btn-primary px-4 fw-semibold">
                <i class="bi bi-plus-circle me-2"></i><?= lang('add_server') ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($servers)): ?>
        <div class="servers-list">
            <?php foreach ($servers as $server): ?>
                <?php include __DIR__ . '/../partials/server-row-item.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <nav aria-label="<?= lang('pagination_label') ?>" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/servers/' . ($current_page - 1)) ?>">
                                <i class="bi bi-chevron-left me-1"></i><?= lang('previous') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/servers/' . $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/servers/' . ($current_page + 1)) ?>">
                                <?= lang('next') ?><i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-server text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 text-dark mb-3"><?= lang('no_servers') ?></h3>
            <p class="text-muted mb-4"><?= lang('no_servers_suggestion') ?></p>
            <?php if (isLoggedIn()): ?>
                <a href="<?= url('/submit') ?>" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-plus-circle me-2"></i><?= lang('add_first_server') ?>
                </a>
            <?php else: ?>
                <a href="<?= url('/register') ?>" class="btn btn-outline-primary btn-lg px-4">
                    <i class="bi bi-person-plus me-2"></i><?= lang('join_to_add_servers') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.servers') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
