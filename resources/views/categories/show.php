<?php ob_start(); ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none"><?= lang('menu.home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= url('/servers') ?>" class="text-decoration-none"><?= lang('titles.servers') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($category->name) ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-start">
                <?php if ($category->image): ?>
                    <img src="<?= url('/uploads/categories/' . $category->image) ?>" 
                         class="rounded me-3" 
                         width="60" 
                         height="60" 
                         alt="<?= htmlspecialchars($category->name) ?>"
                         style="object-fit: cover;">
                <?php endif; ?>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-2">
                        <?= htmlspecialchars($category->name) ?> <?= lang('titles.servers') ?>
                    </h1>
                    <?php if ($category->description): ?>
                        <p class="text-muted mb-1"><?= htmlspecialchars($category->description) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <?php
            $filterCount = 0;
            if (isset($_GET['order_by']) && $_GET['order_by']) $filterCount++;
            if (isset($_GET['country']) && $_GET['country']) $filterCount++;
            if (isset($_GET['status']) && $_GET['status'] !== '') $filterCount++;
            if (isset($_GET['highlight']) && $_GET['highlight']) $filterCount++;
            ?>
            <button type="button" class="btn btn-primary px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel me-md-2"></i>
                <span class="d-none d-md-inline"><?= lang('filters') ?><?= $filterCount > 0 ? ' (' . $filterCount . ')' : '' ?></span>
                <?php if ($filterCount > 0): ?>
                    <span class="d-md-none badge bg-light text-dark ms-1"><?= $filterCount ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <?php
    $activeFilters = [];
    if (isset($_GET['order_by']) && $_GET['order_by']) $activeFilters['order_by'] = $_GET['order_by'];
    if (isset($_GET['country']) && $_GET['country']) $activeFilters['country'] = $_GET['country'];
    if (isset($_GET['status']) && $_GET['status'] !== '') $activeFilters['status'] = $_GET['status'];
    if (isset($_GET['highlight']) && $_GET['highlight']) $activeFilters['highlight'] = $_GET['highlight'];
    ?>
    
    <?php if (!empty($activeFilters)): ?>
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small"><?= lang('filters') ?>:</span>
                <?php foreach ($activeFilters as $key => $value): ?>
                    <span class="badge bg-primary d-flex align-items-center gap-1">
                        <?php
                        switch($key) {
                            case 'order_by':
                                echo lang('order_by_' . $value);
                                break;
                            case 'country':
                                echo getCountryName($value);
                                break;
                            case 'status':
                                echo $value == '1' ? lang('filter_online') : lang('filter_offline');
                                break;
                            case 'highlight':
                                echo lang('premium_only');
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" onclick="removeFilter('<?= $key ?>')"></button>
                    </span>
                <?php endforeach; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                    <?= lang('reset_filters') ?>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($servers)): ?>
        <div class="servers-list">
            <?php foreach ($servers as $server): ?>
                <?php include dirname(__DIR__) . '/partials/server-row-item.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <nav aria-label="<?= lang('pagination_label') ?>" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/category/' . $category->url . '/' . ($current_page - 1) . (empty($_GET) ? '' : '?' . http_build_query($_GET))) ?>">
                                <i class="bi bi-chevron-left me-1"></i><?= lang('previous') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/category/' . $category->url . ($i > 1 ? '/' . $i : '') . (empty($_GET) ? '' : '?' . http_build_query($_GET))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/category/' . $category->url . '/' . ($current_page + 1) . (empty($_GET) ? '' : '?' . http_build_query($_GET))) ?>">
                                <?= lang('next') ?><i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-server text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 text-dark mb-3"><?= sprintf(lang('no_servers_in_category'), htmlspecialchars($category->name)) ?></h3>
            <p class="text-muted mb-4"><?= lang('no_servers_in_category_suggestion') ?></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= url('/servers') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i><?= lang('browse_all_servers') ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= url('/submit') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i><?= lang('add_server') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="bi bi-funnel me-2"></i><?= lang('filters') ?> - <?= htmlspecialchars($category->name) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('close_modal') ?>"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3"><?= lang('categories') ?></h6>
                        <?php $categories = \App\Models\Category::getWithServerCount(); ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= url('/category/' . $cat->url) ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 <?= $cat->id == $category->id ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat->name) ?>
                                    <span class="badge bg-secondary"><?= $cat->server_count ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3"><?= lang('filters') ?></h6>
                        
                        <div class="mb-3">
                            <label class="form-label"><?= lang('order_by') ?>:</label>
                            <select class="form-select" onchange="updateFilter('order_by', this.value)">
                                <option value=""><?= lang('order_by_latest') ?></option>
                                <option value="votes" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'votes') ? 'selected' : '' ?>><?= lang('order_by_votes') ?></option>
                                <option value="players" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'players') ? 'selected' : '' ?>><?= lang('order_by_players') ?></option>
                                <option value="favorites" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'favorites') ? 'selected' : '' ?>><?= lang('order_by_favorites') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= lang('filter_status') ?>:</label>
                            <select class="form-select" onchange="updateFilter('status', this.value)">
                                <option value=""><?= lang('all') ?></option>
                                <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>><?= lang('filter_online') ?></option>
                                <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : '' ?>><?= lang('filter_offline') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= lang('filter_country') ?>:</label>
                            <select class="form-select" onchange="updateFilter('country', this.value)">
                                <option value=""><?= lang('all_countries') ?></option>
                                <?php foreach (getCountries() as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= (isset($_GET['country']) && $_GET['country'] == $code) ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (\App\Models\Setting::getValue('premium')): ?>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="premiumFilter" onchange="updateFilter('highlight', this.checked ? 1 : '')" <?= (isset($_GET['highlight']) && $_GET['highlight']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="premiumFilter">
                                        <?= lang('premium_only') ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                    <?= lang('reset_filters') ?>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= lang('close_modal') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = htmlspecialchars($category->name) . ' ' . lang('titles.servers') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>