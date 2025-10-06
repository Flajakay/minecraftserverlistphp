<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('payments_management') ?></h2>
                        <p class="text-muted mb-0">
                            <?= lang('monitor_payments') ?>
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
                        <div class="col-md-8">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text"
                                           name="search"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="<?= lang('search_payments') ?>"
                                           value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="bi bi-search me-1"></i><?= lang('search_button') ?>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Clear Filters -->
                        <div class="col-md-4">
                            <?php if (!empty($search)): ?>
                                <a href="/admin/payments" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i><?= lang('clear_filters') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-table text-primary me-2"></i><?= lang('payments_list') ?>
                        </h6>
                        <?php if (!empty($payments)): ?>
                            <small class="text-muted">
                                <?= sprintf(lang('showing_payments'), count($payments), $totalPayments) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0" style="overflow: visible;">
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0"><?= lang('no_payments_found') ?></p>
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
                                            <i class="bi bi-person me-1"></i>User
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-server me-1"></i>Server
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-calendar me-1"></i>Days
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-cash me-1"></i>Revenue
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-envelope me-1"></i>Email
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-clock me-1"></i>Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">#<?= $payment->id ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                             style="width: 32px; height: 32px;">
                                                            <span class="text-white fw-bold small">
                                                                <?= strtoupper(substr($payment->username, 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            <?= htmlspecialchars($payment->username) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars($payment->server_name) ?>
                                                </div>
                                                <small class="text-muted">Payment ID: <?= $payment->id ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-calendar-event me-1"></i><?= $payment->highlighted_days ?> <?= lang('days') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?= sprintf(lang('usd_revenue'), number_format($payment->revenue, 2)) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <?= htmlspecialchars($payment->email) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= timeAgo($payment->created_at) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('M j, Y g:i A', strtotime($payment->created_at)) ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('payments_management') . ' - ' . setting('title'); ?>

<?php include __DIR__ . '/../layouts/app.php'; ?>
