<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-controller text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1">Games Management</h2>
                        <p class="text-muted mb-0">
                            <?= sprintf('Total: %d games', number_format($totalGames)) ?>
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i>Admin Panel
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0">
                                    <i class="bi bi-list-ul text-primary me-2"></i>Games List
                                </h6>
                                <form method="GET" class="d-flex">
                                    <div class="input-group" style="max-width: 400px;">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                                               placeholder="Search..." value="<?= sanitize($search) ?>">
                                        <button type="submit" class="btn btn-primary ms-2">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($games)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-controller text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">No results found</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="fw-semibold">ID</th>
                                                <th class="fw-semibold">Name</th>
                                                <th class="fw-semibold">Steam App ID</th>
                                                <th class="fw-semibold">Protocol</th>
                                                <th class="fw-semibold">Status</th>
                                                <th class="fw-semibold">Servers</th>
                                                <th class="fw-semibold text-center">Tools</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($games as $game): ?>
                                                <tr>
                                                    <td><span class="badge bg-light text-dark"><?= $game->id ?></span></td>
                                                    <td>
                                                        <div class="fw-semibold"><?= sanitize($game->name) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($game->steam_app_id): ?>
                                                            <code class="bg-light px-2 py-1 rounded"><?= $game->steam_app_id ?></code>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= sanitize($game->protocol) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($game->enabled): ?>
                                                            <span class="badge bg-success">Enabled</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Disabled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= number_format($game->server_count ?? 0) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="/admin/games/edit/<?= $game->id ?>" class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil me-1"></i>Edit
                                                            </a>
                                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                                                <span class="visually-hidden">Toggle Dropdown</span>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                                <li>
                                                                    <form method="POST" action="/admin/games/delete/<?= $game->id ?>" class="d-inline"
                                                                    onsubmit="return confirm('Delete this game? Servers using it will have game unset.')">
                                                                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="bi bi-trash me-2"></i>Delete
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer bg-light border-0">
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>">
                                                    <i class="bi bi-chevron-left"></i> Previous
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
                                                    Next <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-plus-circle text-primary me-2"></i>Add Game
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="steam_app_id" class="form-label fw-semibold">Steam App ID</label>
                                    <input type="number" class="form-control" id="steam_app_id" name="steam_app_id" placeholder="730">
                                </div>
                                <div class="mb-3">
                                    <label for="protocol" class="form-label fw-semibold">Protocol</label>
                                    <select class="form-select" id="protocol" name="protocol">
                                        <option value="steam_a2s">Steam</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled" checked>
                                        <label class="form-check-label fw-semibold" for="enabled">Enabled</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                    <i class="bi bi-plus-circle me-2"></i>Submit
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-bar-chart text-primary me-2"></i>Quick Stats
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="h5 text-primary mb-0"><?= number_format($totalGames) ?></div>
                                        <small class="text-muted">Total Games</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="h5 text-success mb-0"><?= number_format(array_sum(array_column($games, 'server_count'))) ?></div>
                                        <small class="text-muted">Total Servers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = 'Games Management'; ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
