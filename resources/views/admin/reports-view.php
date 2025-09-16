<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= lang('titles.view_report') ?></h2>
                <a href="/admin/reports" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Report #<?= $report->id ?></h5>
                        <div>
                            <?php if ($report->type == 1): ?>
                                <span class="badge bg-info">User Report</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Server Report</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2"><?= lang('reported_by') ?></h6>
                            <p class="mb-0"><?= htmlspecialchars($report->reporter_name) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2"><?= lang('reported_at') ?></h6>
                            <p class="mb-0"><?= date('M j, Y g:i A', strtotime($report->created_at)) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Reported Item</h6>
                            <p class="mb-0"><?= htmlspecialchars($report->reported_name ?? 'Deleted') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2"><?= lang('report_type') ?></h6>
                            <p class="mb-0"><?= $report->type == 1 ? 'User' : 'Server' ?></p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted mb-2"><?= lang('report_reason') ?></h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($report->message)) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <form method="POST" action="/admin/reports/action/<?= $report->id ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <input type="hidden" name="action" value="resolve">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Resolve Report
                                </button>
                            </form>
                            <form method="POST" action="/admin/reports/delete/<?= $report->id ?>" class="d-inline" 
                                  onsubmit="return confirm('<?= lang('confirm_delete') ?>')">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> <?= lang('delete') ?>
                                </button>
                            </form>
                            <a href="/admin/reports" class="btn btn-outline-secondary ms-auto">
                                Back to Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
