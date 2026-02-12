<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-flag text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('reports_management') ?></h2>
                        <p class="text-muted mb-0">
                            <?= /** @noinspection PhpUndefinedVariableInspection */
                            sprintf(lang('total_reports_count'), number_format($totalReports)) ?>
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i><?= lang('admin_panel') ?>
                    </span>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-funnel text-primary me-2"></i><?= lang('search_and_filters') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Search Form -->
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control border-start-0 ps-0" 
                                           placeholder="<?= lang('search') ?>" 
                                           value="<?= /** @noinspection PhpUndefinedVariableInspection */
                                           sanitize($search) ?>">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="bi bi-search me-1"></i><?= lang('search_button') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Filter Form -->
                        <div class="col-md-6">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                                
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-flag text-muted"></i>
                                    </span>
                                    <select name="type" class="form-select border-start-0">
                                        <option value=""><?= lang('report_type') ?></option>
                                        <option value="1" <?= /** @noinspection PhpUndefinedVariableInspection */
                                        $filters['type'] === '1' ? 'selected' : '' ?>><?= lang('user_report') ?></option>
                                        <option value="2" <?= $filters['type'] === '2' ? 'selected' : '' ?>><?= lang('server_report') ?></option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-funnel me-1"></i><?= lang('filter') ?>
                                </button>
                                <a href="/admin/reports" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i><?= lang('clear_filters') ?>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-table text-primary me-2"></i><?= lang('reports_list') ?>
                        </h6>
                        <?php if (!empty($reports)): ?>
                            <small class="text-muted">
                                <?= sprintf(lang('showing_reports'), count($reports), number_format($totalReports)) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($reports)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-flag text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0"><?= lang('no_results') ?></p>
                            <small class="text-muted"><?= lang('no_reports_to_review') ?></small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">
                                            <i class="bi bi-hash me-1"></i>ID
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-flag me-1"></i><?= lang('report_type') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-person me-1"></i><?= lang('reported_by') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-target me-1"></i><?= lang('reported_item') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-chat-quote me-1"></i><?= lang('report_reason') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-calendar me-1"></i><?= lang('reported_at') ?>
                                        </th>
                                        <th class="fw-semibold text-center">
                                            <i class="bi bi-tools me-1"></i><?= lang('tools') ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $report->id ?></span>
                                            </td>
                                            <td>
                                                <?php if ($report->type == 1): ?>
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-person me-1"></i><?= lang('user_report') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-server me-1"></i><?= lang('server_report') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 28px; height: 28px;">
                                                            <span class="text-white fw-bold small">
                                                                <?= strtoupper(substr($report->reporter_name, 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small">
                                                            <?= sanitize($report->reporter_name) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($report->reported_name): ?>
                                                        <div class="me-2">
                                                            <div class="bg-<?= $report->type == 1 ? 'primary' : 'success' ?> rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 28px; height: 28px;">
                                                                <i class="bi bi-<?= $report->type == 1 ? 'person' : 'server' ?> text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold small">
                                                                <?= sanitize($report->reported_name) ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">
                                                            <i class="bi bi-trash me-1"></i><?= lang('deleted') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" title="<?= sanitize($report->message) ?>">
                                                    <i class="bi bi-quote text-muted me-1"></i>
                                                    <?= sanitize($report->message) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?= timeAgo($report->created_at) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/admin/reports/view/<?= $report->id ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i><?= lang('view') ?>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php /** @noinspection PhpUndefinedVariableInspection */
                if ($totalPages > 1): ?>
                    <div class="card-footer bg-light border-0">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php /** @noinspection PhpUndefinedVariableInspection */
                                if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>">
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
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.reports_management') . ' - ' . setting('title'); ?>

<?php include __DIR__ . '/../layouts/app.php'; ?>
