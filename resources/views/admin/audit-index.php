<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-journal-text text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('audit_logs') ?></h2>
                        <p class="text-muted mb-0">
                            <?= sprintf(lang('total_results'), number_format($totalLogs)) ?> audit entries
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i><?= lang('admin_panel') ?>
                    </span>
                    <div class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        <small><?= sprintf(lang('last_updated'), date('M j, g:i A')) ?></small>
                    </div>
                </div>
            </div>

            <!-- Search and Filters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                    <i class="bi bi-search text-primary me-2"></i><?= lang('search_audit_logs') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="<?= lang('search_users_actions') ?>" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <?= lang('search_button') ?>
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="<?= url('/admin/audit') ?>" class="btn btn-outline-secondary" title="<?= lang('clear_search') ?>">
                                        <i class="bi bi-x-circle me-1"></i><?= lang('clear_search') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Table Card -->
            <div class="card border-0 shadow-sm">
                <?php if (empty($logs)): ?>
                    <!-- Empty State -->
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3 mb-2"><?= lang('no_results') ?></h5>
                        <p class="text-muted mb-3">
                            <?php if (!empty($search)): ?>
                                <?= sprintf(lang('no_matching_logs'), htmlspecialchars($search)) ?>
                            <?php else: ?>
                                <?= lang('no_audit_logs') ?>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search)): ?>
                            <a href="<?= url('/admin/audit') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i><?= lang('view_all_logs') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Table Header -->
                    <div class="card-header bg-transparent border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-table text-primary me-2"></i><?= lang('audit_log_entries') ?>
                            </h6>
                            <small class="text-muted">
                                <?= sprintf(lang('showing_entries'), count($logs), number_format($totalLogs)) ?>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-hash me-1"></i>ID
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-person me-1"></i><?= lang('audit_user') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-lightning me-1"></i><?= lang('audit_action') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-table me-1"></i><?= lang('audit_table') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-tag me-1"></i><?= lang('record_id') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-info-circle me-1"></i><?= lang('audit_details') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-geo-alt me-1"></i><?= lang('audit_ip') ?>
                                    </th>
                                    <th class="fw-semibold py-3">
                                        <i class="bi bi-clock me-1"></i><?= lang('audit_time') ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark"><?= $log->id ?></span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?php if ($log->username): ?>
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px;">
                                                            <span class="text-white fw-bold small">
                                                                <?= strtoupper(substr($log->username, 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px;">
                                                            <i class="bi bi-gear text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($log->username ?? 'System') ?></div>
                                                    <small class="text-muted">
                                                        <?= $log->username ? lang('user_action') : lang('system_action') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <?php
                                            $actionConfig = [
                                                'create' => ['class' => 'success', 'icon' => 'plus-circle'],
                                                'update' => ['class' => 'warning', 'icon' => 'pencil'],
                                                'delete' => ['class' => 'danger', 'icon' => 'trash'],
                                                'activate' => ['class' => 'info', 'icon' => 'check-circle'],
                                                'deactivate' => ['class' => 'secondary', 'icon' => 'x-circle'],
                                                'login' => ['class' => 'primary', 'icon' => 'box-arrow-in-right'],
                                                'logout' => ['class' => 'dark', 'icon' => 'box-arrow-right'],
                                                'vote' => ['class' => 'info', 'icon' => 'hand-thumbs-up'],
                                                'favorite' => ['class' => 'danger', 'icon' => 'heart'],
                                                'submit' => ['class' => 'success', 'icon' => 'upload']
                                            ];
                                            $config = $actionConfig[$log->action] ?? ['class' => 'secondary', 'icon' => 'circle'];
                                            ?>
                                            <span class="badge bg-<?= $config['class'] ?> d-inline-flex align-items-center">
                                                <i class="bi bi-<?= $config['icon'] ?> me-1"></i>
                                                <?= ucfirst($log->action) ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($log->table_name) ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <?php if ($log->record_id): ?>
                                                <span class="badge bg-primary">#<?= $log->record_id ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <?php if ($log->details): ?>
                                                <div class="text-truncate" style="max-width: 200px;" 
                                                     title="<?= htmlspecialchars($log->details) ?>"
                                                     data-bs-toggle="tooltip">
                                                    <small class="text-muted"><?= htmlspecialchars($log->details) ?></small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-geo-alt text-muted me-1"></i>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <?= htmlspecialchars($log->ip_address) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex flex-column" 
                                                 title="<?= date('M j, Y g:i:s A', strtotime($log->created_at)) ?>"
                                                 data-bs-toggle="tooltip">
                                                <span class="fw-semibold small text-primary"><?= timeAgo($log->created_at) ?></span>
                                                <small class="text-muted"><?= date('M j, g:i A', strtotime($log->created_at)) ?></small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-light border-0 py-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-2">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>">
                                            <?= lang('next') ?> <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <?= sprintf(lang('page_of'), $currentPage, $totalPages) ?> 
                                (<?= sprintf(lang('total_results'), number_format($totalLogs)) ?>)
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.audit_logs') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
