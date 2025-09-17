<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-tags text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('categories_management') ?></h2>
                        <p class="text-muted mb-0">
                            <?= sprintf(lang('total_categories_count'), number_format($totalCategories)) ?>
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i><?= lang('admin_panel') ?>
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <!-- Categories List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0">
                                    <i class="bi bi-list-ul text-primary me-2"></i><?= lang('categories_list') ?>
                                </h6>
                                <form method="GET" class="d-flex">
                                    <div class="input-group" style="max-width: 400px;">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               name="search" 
                                               class="form-control border-start-0 ps-0" 
                                               placeholder="<?= lang('search') ?>" 
                                               value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary ms-2">
                                            <?= lang('search_button') ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($categories)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
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
                                                    <i class="bi bi-tag me-1"></i><?= lang('name') ?>
                                                </th>
                                                <th class="fw-semibold">
                                                    <i class="bi bi-link-45deg me-1"></i><?= lang('url') ?>
                                                </th>
                                                <th class="fw-semibold">
                                                    <i class="bi bi-diagram-3 me-1"></i><?= lang('category_parent') ?>
                                                </th>
                                                <th class="fw-semibold">
                                                    <i class="bi bi-server me-1"></i><?= lang('servers') ?>
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
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-light text-dark"><?= $category->id ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="fw-semibold">
                                                                <?= htmlspecialchars($category->name) ?>
                                                            </div>
                                                            <?php if ($category->description): ?>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars(substr($category->description, 0, 20)) ?>
                                                                    <?php if (strlen($category->description) > 20): ?>...<?php endif; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($category->url) ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if ($category->parent_name): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="bi bi-arrow-up me-1"></i><?= htmlspecialchars($category->parent_name) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary">
                                                                <i class="bi bi-house me-1"></i><?= lang('root') ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-server me-1"></i><?= number_format($category->server_count) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?= timeAgo($category->created_at) ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="/admin/categories/edit/<?= $category->id ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil me-1"></i><?= lang('edit') ?>
                                                            </a>
                                                            <?php if ($category->id != 1): ?>
                                                                <button type="button" 
                                                                        class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                                        data-bs-toggle="dropdown">
                                                                    <span class="visually-hidden"><?= lang('toggle_dropdown') ?></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                                    <li>
                                                                        <form method="POST" action="/admin/categories/delete/<?= $category->id ?>" class="d-inline" 
                                                                        onsubmit="return confirm('<?= lang('category_confirm_delete') ?>')">
                                                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                                                            <button type="submit" class="dropdown-item text-danger">
                                                                                <i class="bi bi-trash me-2"></i><?= lang('delete') ?>
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                </ul>
                                                            <?php endif; ?>
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
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer bg-light border-0">
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Category Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-plus-circle text-primary me-2"></i><?= lang('admin_add_category_header') ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold"><?= lang('admin_add_category_name') ?> *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-tag text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="name" 
                                               name="name" 
                                               placeholder="<?= lang('enter_category_name') ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="url" class="form-label fw-semibold"><?= lang('admin_add_category_url') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-link text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="url" 
                                               name="url"
                                               placeholder="<?= lang('category_url_placeholder') ?>">
                                    </div>
                                    <small class="text-muted"><?= lang('admin_add_category_url_help') ?></small>
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold"><?= lang('admin_add_category_title') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-card-text text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="title" 
                                               name="title"
                                               placeholder="<?= lang('seo_page_title') ?>">
                                    </div>
                                    <small class="text-muted"><?= lang('admin_add_category_title_help') ?></small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold"><?= lang('admin_add_category_description') ?></label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="<?= lang('category_description_placeholder') ?>"></textarea>
                                    <small class="text-muted"><?= lang('admin_add_category_description_help') ?></small>
                                </div>

                                <div class="mb-4">
                                    <label for="parent_id" class="form-label fw-semibold"><?= lang('admin_add_category_parent') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-diagram-3 text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="parent_id" name="parent_id">
                                            <option value="0"><?= lang('none_root_category') ?></option>
                                            <?php foreach ($parentCategories as $parent): ?>
                                                <option value="<?= $parent->id ?>"><?= htmlspecialchars($parent->name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                    <i class="bi bi-plus-circle me-2"></i><?= lang('submit') ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-bar-chart text-primary me-2"></i><?= lang('quick_stats') ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="h5 text-primary mb-0"><?= number_format($totalCategories) ?></div>
                                        <small class="text-muted"><?= lang('total_categories') ?></small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="h5 text-success mb-0">
                                            <?= number_format(array_sum(array_column($categories, 'server_count'))) ?>
                                        </div>
                                        <small class="text-muted"><?= lang('total_servers') ?></small>
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
<?php $title = lang('titles.categories_management') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
