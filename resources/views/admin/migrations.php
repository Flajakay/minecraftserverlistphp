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
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted"><?= lang('migrations_ignored') ?></span>
                        <span class="badge bg-light text-dark"><?= (int)($status['ignored'] ?? 0) ?></span>
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
                                                    <input class="form-check-input" type="checkbox" name="migrations[]" value="<?= sanitize($item['name']) ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= sanitize($item['name']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark text-uppercase"><?= sanitize($item['direction']) ?></span>
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
                                                <small class="text-muted"><?= !empty($item['applied_at']) ? sanitize($item['applied_at']) : '-' ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-light border-0">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                            <?= lang('next') ?> <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($lastResult)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-clipboard-data text-primary me-2"></i><?= lang('migration_last_result') ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($lastResult['failed'])): ?>
                            <?php $failed = $lastResult['failed']; ?>
                            <div class="alert alert-danger mb-3">
                                <div class="fw-semibold mb-1"><?= lang('migration_failed') ?>: <?= sanitize($failed['name'] ?? '') ?></div>
                                <div class="small"><?= sanitize($failed['message'] ?? '') ?></div>
                                <div class="small text-muted mt-1">
                                    <?= lang('migration_statement') ?>: <?= sanitize((string)($failed['statement_index'] ?? '-')) ?>
                                    <?php if (!empty($failed['sqlstate'])): ?>
                                        | SQLSTATE: <?= sanitize((string)$failed['sqlstate']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($failed['driver_code'])): ?>
                                        | <?= lang('migration_driver_code') ?>: <?= sanitize((string)$failed['driver_code']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($failed['statement'])): ?>
                                <pre class="bg-light border rounded p-3 mb-0 small text-break"><code><?= sanitize($failed['statement']) ?></code></pre>
                            <?php endif; ?>
                        <?php elseif (!empty($lastResult['executed'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th><?= lang('migration') ?></th>
                                            <th><?= lang('migration_status') ?></th>
                                            <th><?= lang('migration_statements') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lastResult['executed'] as $migrationResult): ?>
                                            <?php
                                            $statementCount = count($migrationResult['statements'] ?? []);
                                            $skippedCount = count(array_filter($migrationResult['statements'] ?? [], static fn ($statement) => ($statement['status'] ?? '') === 'skipped_idempotent'));
                                            ?>
                                            <tr>
                                                <td><code><?= sanitize($migrationResult['name'] ?? '') ?></code></td>
                                                <td>
                                                    <?php if (($migrationResult['status'] ?? '') === 'applied'): ?>
                                                        <span class="badge bg-success"><?= lang('applied') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= lang('migration_skipped_idempotent') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= sprintf(lang('migration_statement_summary'), $statementCount, $skippedCount) ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0"><?= lang('migration_no_changes') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($status['ignored_items'])): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-skip-forward text-secondary me-2"></i><?= lang('migrations_ignored_files') ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= lang('migration') ?></th>
                                        <th><?= lang('reason') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($status['ignored_items'] as $item): ?>
                                        <tr>
                                            <td><code><?= sanitize($item['name']) ?></code></td>
                                            <td><small class="text-muted"><?= lang('migration_down_ignored') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.migrations'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
