<?php ob_start(); ?>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-database-gear text-primary" style="font-size: 2rem;"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold text-dark mb-1"><?= lang('migrations_management') ?></h2>
                <p class="text-muted mb-0"><?= lang('migrations_page_subtitle') ?></p>
            </div>
        </div>
        <span class="badge bg-light text-dark px-3 py-2">
            <i class="bi bi-shield-check me-1"></i><?= lang('admin_panel') ?>
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i><?= lang('migrations_status') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><?= lang('migrations_total') ?></span>
                        <span class="badge bg-light text-dark"><?= (int)($status['total'] ?? 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><?= lang('migrations_applied') ?></span>
                        <span class="badge bg-success"><?= (int)($status['applied'] ?? 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted"><?= lang('migrations_pending') ?></span>
                        <span class="badge <?= ((int)($status['pending'] ?? 0) > 0) ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                            <?= (int)($status['pending'] ?? 0) ?>
                        </span>
                    </div>

                    <div class="mt-4">
                        <form method="POST" action="<?= url('/admin/migrations/run-all') ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="btn btn-primary w-100" <?= ((int)($status['pending'] ?? 0) === 0) ? 'disabled' : '' ?>>
                                <i class="bi bi-play-fill me-2"></i><?= lang('run_all_migrations') ?>
                            </button>
                        </form>

                        <button type="submit" form="run-selected-migrations-form" class="btn btn-outline-primary w-100 mt-2" <?= ((int)($status['pending'] ?? 0) === 0) ? 'disabled' : '' ?>>
                            <i class="bi bi-play-circle me-2"></i><?= lang('run_selected_migrations') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">


                <div class="card-body p-0">
                    <?php if (empty($status['items'])): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-database text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0"><?= lang('no_migrations_found') ?></p>
                        </div>
                    <?php else: ?>
                        <form id="run-selected-migrations-form" method="POST" action="<?= url('/admin/migrations/run-selected') ?>" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold" style="width: 40px;"></th>
                                        <th class="fw-semibold"><?= lang('migration') ?></th>
                                        <th class="fw-semibold"><?= lang('direction') ?></th>
                                        <th class="fw-semibold"><?= lang('migration_status') ?></th>
                                        <th class="fw-semibold"><?= lang('applied_at') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($status['items'] ?? []) as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (empty($item['applied'])): ?>
                                                    <input class="form-check-input" type="checkbox" name="migrations[]" value="<?= htmlspecialchars($item['name']) ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($item['name']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark text-uppercase"><?= htmlspecialchars($item['direction']) ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($item['applied'])): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i><?= lang('applied') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock me-1"></i><?= lang('pending') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= !empty($item['applied_at']) ? htmlspecialchars($item['applied_at']) : '-' ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.migrations') . ' - ' . lang('admin_panel'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
