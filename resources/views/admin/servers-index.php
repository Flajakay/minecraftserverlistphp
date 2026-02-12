<?php ob_start(); ?>


<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-server text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('servers_management') ?></h2>
                        <p class="text-muted mb-0">
                            <?= /** @noinspection PhpUndefinedVariableInspection */
                            sprintf(lang('total_servers_count'), number_format($totalServers)) ?>
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
                        <div class="col-lg-4">
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
                        <div class="col-lg-8">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                                
                                <div class="input-group" style="max-width: 180px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-tags text-muted"></i>
                                    </span>
                                    <select name="category_id" class="form-select border-start-0">
                                        <option value=""><?= lang('server_category') ?></option>
                                        <?php /** @noinspection PhpUndefinedVariableInspection */
                                        foreach ($categories as $category): ?>
                                            <option value="<?= $category->id ?>" <?= /** @noinspection PhpUndefinedVariableInspection */
                                            $filters['category_id'] == $category->id ? 'selected' : '' ?>>
                                                <?= sanitize($category->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="input-group" style="max-width: 150px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-wifi text-muted"></i>
                                    </span>
                                    <select name="status" class="form-select border-start-0">
                                        <option value=""><?= lang('server_status') ?></option>
                                        <option value="1" <?= /** @noinspection PhpUndefinedVariableInspection */
                                        $filters['status'] === '1' ? 'selected' : '' ?>><?= lang('active') ?></option>
                                        <option value="0" <?= $filters['status'] === '0' ? 'selected' : '' ?>><?= lang('inactive') ?></option>
                                    </select>
                                </div>
                                
                                <div class="input-group" style="max-width: 130px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-toggle-on text-muted"></i>
                                    </span>
                                    <select name="active" class="form-select border-start-0">
                                        <option value=""><?= lang('active_filter') ?></option>
                                        <option value="1" <?= $filters['active'] === '1' ? 'selected' : '' ?>><?= lang('active') ?></option>
                                        <option value="0" <?= $filters['active'] === '0' ? 'selected' : '' ?>><?= lang('inactive') ?></option>
                                    </select>
                                </div>
                                
                                <div class="input-group" style="max-width: 130px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-eye text-muted"></i>
                                    </span>
                                    <select name="private" class="form-select border-start-0">
                                        <option value=""><?= lang('visibility_filter') ?></option>
                                        <option value="0" <?= $filters['private'] === '0' ? 'selected' : '' ?>><?= lang('public') ?></option>
                                        <option value="1" <?= $filters['private'] === '1' ? 'selected' : '' ?>><?= lang('private') ?></option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-funnel me-1"></i><?= lang('filter') ?>
                                </button>
                                <a href="/admin/servers" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i><?= lang('clear_filters') ?>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servers Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-table text-primary me-2"></i><?= lang('servers_list') ?>
                        </h6>
                        <?php if (!empty($servers)): ?>
                            <small class="text-muted">
                                <?= sprintf(lang('showing_servers'), count($servers), number_format($totalServers)) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0" style="overflow: visible;">
                    <?php if (empty($servers)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-server text-muted" style="font-size: 3rem;"></i>
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
                                            <i class="bi bi-server me-1"></i><?= lang('server_name') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-globe me-1"></i><?= lang('server_address') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-person me-1"></i><?= lang('server_owner') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-tags me-1"></i><?= lang('server_category') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-wifi me-1"></i><?= lang('server_status') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-people me-1"></i><?= lang('players') ?>
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-arrow-up me-1"></i><?= lang('votes') ?>
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
                                    <?php foreach ($servers as $server): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $server->id ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <?php if ($server->image): ?>
                                                            <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                                                                 class="rounded" 
                                                                 width="32" 
                                                                 height="32" 
                                                                 style="object-fit: cover;"
                                                                 alt="<?= sanitize($server->name) ?>">
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 32px; height: 32px;">
                                                                <i class="bi bi-controller text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            <?= sanitize($server->name) ?>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if ($server->highlight): ?>
                                                                <i class="bi bi-star-fill text-warning" title="<?= lang('premium') ?>"></i>
                                                            <?php endif; ?>
                                                            <?php if ($server->private): ?>
                                                                <i class="bi bi-lock text-warning" title="<?= lang('private') ?>"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">
                                                    <?= sanitize($server->address) ?>:<?= $server->port ?>
                                                </code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 24px; height: 24px;">
                                                            <span class="text-white fw-bold" style="font-size: 0.7rem;">
                                                                <?= strtoupper(substr($server->owner_username, 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="text-muted"><?= sanitize($server->owner_username) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (isset($server->categories) && !empty($server->categories)): ?>
                                                    <?php foreach ($server->categories as $index => $category): ?>
                                                        <?php if ($index < 2): ?>
                                                            <span class="badge bg-light text-dark border me-1 mb-1">
                                                                <?= sanitize($category->category_name) ?>
                                                            </span>
                                                        <?php elseif ($index == 2): ?>
                                                            <span class="badge bg-secondary text-white me-1 mb-1" title="<?= implode(', ', array_slice(array_column($server->categories, 'category_name'), 2)) ?>">
                                                                +<?= count($server->categories) - 2 ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border"><?= lang('no_categories') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <?php if ($server->status): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-wifi me-1"></i><?= lang('active') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-wifi-off me-1"></i><?= lang('inactive') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!$server->active): ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-pause me-1"></i><?= lang('disabled') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?= number_format($server->players) ?>/<?= number_format($server->max_players) ?></span>
                                                    <?php if ($server->max_players > 0): ?>
                                                        <div class="progress" style="width: 40px; height: 4px;">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: <?= ($server->players / $server->max_players * 100) ?>%"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= number_format($server->votes) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= timeAgo($server->created_at) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm position-static">
                                                    <a href="/admin/servers/edit/<?= $server->id ?>" 
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
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&category_id=<?= urlencode($filters['category_id']) ?>&status=<?= urlencode($filters['status']) ?>&active=<?= urlencode($filters['active']) ?>&private=<?= urlencode($filters['private']) ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= urlencode($filters['category_id']) ?>&status=<?= urlencode($filters['status']) ?>&active=<?= urlencode($filters['active']) ?>&private=<?= urlencode($filters['private']) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&category_id=<?= urlencode($filters['category_id']) ?>&status=<?= urlencode($filters['status']) ?>&active=<?= urlencode($filters['active']) ?>&private=<?= urlencode($filters['private']) ?>">
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
<?php $title = lang('titles.edit_user') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
