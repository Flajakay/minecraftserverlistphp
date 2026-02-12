<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('users_management') ?></h2>
                        <p class="text-muted mb-0">
                            <?= /** @noinspection PhpUndefinedVariableInspection */
                            sprintf(lang('total_users_count'), number_format($totalUsers)) ?>
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
                        <div class="col-md-4">
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
                        <div class="col-md-8">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                                
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-badge text-muted"></i>
                                    </span>
                                    <select name="type" class="form-select border-start-0">
                                        <option value=""><?= lang('user_type') ?></option>
                                        <option value="0" <?= /** @noinspection PhpUndefinedVariableInspection */
                                        $filters['type'] === '0' ? 'selected' : '' ?>><?= lang('user') ?></option>
                                        <option value="1" <?= $filters['type'] === '1' ? 'selected' : '' ?>><?= lang('administrator') ?></option>
                                        <option value="2" <?= $filters['type'] === '2' ? 'selected' : '' ?>><?= lang('owner') ?></option>
                                    </select>
                                </div>
                                
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-toggle-on text-muted"></i>
                                    </span>
                                    <select name="active" class="form-select border-start-0">
                                        <option value=""><?= lang('user_status') ?></option>
                                        <option value="1" <?= $filters['active'] === '1' ? 'selected' : '' ?>><?= lang('active') ?></option>
                                        <option value="0" <?= $filters['active'] === '0' ? 'selected' : '' ?>><?= lang('inactive') ?></option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-funnel me-1"></i><?= lang('filter') ?>
                                </button>
                                <a href="/admin/users" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i><?= lang('clear_filters') ?>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-table text-primary me-2"></i><?= lang('users_list') ?>
                        </h6>
                        <?php if (!empty($users)): ?>
                            <small class="text-muted">
                                <?= sprintf(lang('showing_users'), count($users), number_format($totalUsers)) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0" style="overflow: visible;">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0"><?= lang('no_results') ?></p>
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
                                            <i class="bi bi-person me-1"></i><?= lang('username') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-envelope me-1"></i><?= lang('email') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-card-text me-1"></i><?= lang('name') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i><?= lang('user_type') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-toggle-on me-1"></i><?= lang('user_status') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-calendar me-1"></i><?= lang('date') ?>
                                        </th>
                                        <th class="fw-semibold text-center">
                                            <i class="bi bi-tools me-1"></i><?= lang('tools') ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $user->id ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px;">
                                                            <span class="text-white fw-bold small">
                                                                <?= strtoupper(substr($user->username, 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            <?= sanitize($user->username) ?>
                                                            <?php if ($user->private): ?>
                                                                <i class="bi bi-lock text-warning ms-1" title="<?= lang('private') ?>"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= sanitize($user->email) ?></span>
                                            </td>
                                            <td>
                                                <?= sanitize($user->name) ?>
                                            </td>
                                            <td>
                                                <?php if ($user->type == 2): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-crown me-1"></i><?= lang('owner') ?>
                                                    </span>
                                                <?php elseif ($user->type == 1): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-shield me-1"></i><?= lang('administrator') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-person me-1"></i><?= lang('user') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user->active): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i><?= lang('active') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i><?= lang('inactive') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= timeAgo($user->created_at) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm position-static">
                                                    <a href="/admin/users/edit/<?= $user->id ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil me-1"></i><?= lang('edit') ?>
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
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>&active=<?= urlencode($filters['active']) ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>&active=<?= urlencode($filters['active']) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filters['type']) ?>&active=<?= urlencode($filters['active']) ?>">
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
<?php $title = lang('titles.users_management') . ' - ' . setting('title'); ?>

<?php include __DIR__ . '/../layouts/app.php'; ?>
