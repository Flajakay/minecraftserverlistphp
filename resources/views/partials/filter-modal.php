<?php
$filterCount = 0;
if (isset($_GET['order_by']) && $_GET['order_by']) $filterCount++;
if (isset($_GET['country']) && $_GET['country']) $filterCount++;
if (isset($_GET['status']) && $_GET['status'] !== '') $filterCount++;
if (isset($_GET['highlight']) && $_GET['highlight']) $filterCount++;
if (isset($_GET['categories']) && $_GET['categories']) $filterCount++;

$modalTitle = lang('filters');
$selectedCategories = [];
if (isset($_GET['categories'])) {
    $selectedCategories = array_filter(array_map('intval', explode(',', $_GET['categories'])));
}
?>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white border-0" style="background: linear-gradient(45deg, #0d6efd, #0056b3) !important;">
                <h5 class="modal-title fw-bold" id="filterModalLabel">
                    <i class="bi bi-funnel-fill me-2"></i><?= $modalTitle ?>
                    <?php if ($filterCount > 0): ?>
                        <span class="badge bg-light text-dark ms-2" id="filterCount"><?= $filterCount ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= lang('close_modal') ?>"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-tags-fill me-2 text-primary"></i><?= lang('categories') ?>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <?php $categories = \App\Models\Category::getAllWithHierarchy(); ?>
                                <div class="category-grid" style="max-height: 400px; overflow-y: auto; padding: 15px;">
                                    <?php 
                                    $parentCategories = array_filter($categories, fn($c) => $c->parent_id == 0);
                                    foreach ($parentCategories as $cat): 
                                        $subcategories = array_filter($categories, fn($c) => $c->parent_id == $cat->id);
                                    ?>
                                        <div class="category-item mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input category-checkbox" type="checkbox" 
                                                       value="<?= $cat->id ?>" id="cat_<?= $cat->id ?>"
                                                       <?= in_array($cat->id, $selectedCategories) ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-semibold" for="cat_<?= $cat->id ?>">
                                                    <?= htmlspecialchars($cat->name) ?>
                                                </label>
                                            </div>
                                            <?php if (!empty($subcategories)): ?>
                                                <div class="ms-4 mt-2">
                                                    <?php foreach ($subcategories as $subcat): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input category-checkbox subcategory" type="checkbox" 
                                                                   value="<?= $subcat->id ?>" id="cat_<?= $subcat->id ?>"
                                                                   data-parent="<?= $cat->id ?>"
                                                                   <?= in_array($subcat->id, $selectedCategories) ? 'checked' : '' ?>>
                                                            <label class="form-check-label small" for="cat_<?= $subcat->id ?>">
                                                                <?= htmlspecialchars($subcat->name) ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-sliders me-2 text-primary"></i><?= lang('filters') ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3" id="filterOptions">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-dark"><?= lang('order_by') ?></label>
                                        <select class="form-select border-0 shadow-sm" id="orderByFilter" style="background-color: #fff;">
                                            <option value=""><?= lang('order_by_latest') ?></option>
                                            <option value="votes" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'votes') ? 'selected' : '' ?>><?= lang('order_by_votes') ?></option>
                                            <option value="players" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'players') ? 'selected' : '' ?>><?= lang('order_by_players') ?></option>
                                            <option value="favorites" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'favorites') ? 'selected' : '' ?>><?= lang('order_by_favorites') ?></option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-dark"><?= lang('filter_status') ?></label>
                                        <select class="form-select border-0 shadow-sm" id="statusFilter" style="background-color: #fff;">
                                            <option value=""><?= lang('all') ?></option>
                                            <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>>
                                                <?= lang('filter_online') ?>
                                            </option>
                                            <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : '' ?>>
                                                <?= lang('filter_offline') ?>
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-dark"><?= lang('filter_country') ?></label>
                                        <select class="form-select border-0 shadow-sm" id="countryFilter" style="background-color: #fff;">
                                            <option value=""><?= lang('all_countries') ?></option>
                                            <?php 
                                            $countriesWithServers = \App\Models\Server::getCountriesWithServerCount();
                                            foreach ($countriesWithServers as $countryData): 
                                            ?>
                                                <option value="<?= $countryData->country ?>" <?= (isset($_GET['country']) && $_GET['country'] == $countryData->country) ? 'selected' : '' ?>>
                                                    <?= getCountryName($countryData->country) ?> (<?= $countryData->server_count ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <?php if (\App\Models\Setting::getValue('premium')): ?>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold text-dark"><?= lang('premium_only') ?></label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" id="premiumFilter" 
                                                       <?= (isset($_GET['highlight']) && $_GET['highlight']) ? 'checked' : '' ?> 
                                                       style="transform: scale(1.2);">
                                                <label class="form-check-label fw-semibold" for="premiumFilter">
                                                    <i class="bi bi-star-fill text-warning me-1"></i><?= lang('premium_only') ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                    <i class="bi bi-arrow-clockwise me-1"></i><?= lang('reset_filters') ?>
                </button>
                <button type="button" class="btn btn-primary" id="applyFilters">
                    <i class="bi bi-check me-1"></i><?= lang('apply_filters') ?>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= lang('close_modal') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterState = {
        categories: <?= json_encode($selectedCategories) ?>,
        order_by: '<?= $_GET['order_by'] ?? '' ?>',
        status: '<?= $_GET['status'] ?? '' ?>',
        country: '<?= $_GET['country'] ?? '' ?>',
        highlight: <?= isset($_GET['highlight']) && $_GET['highlight'] ? 'true' : 'false' ?>
    };

    function updateFilterCount() {
        let count = 0;
        if (filterState.categories.length > 0) count++;
        if (filterState.order_by) count++;
        if (filterState.status !== '') count++;
        if (filterState.country) count++;
        if (filterState.highlight) count++;
        
        const badge = document.getElementById('filterCount');
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else {
                document.querySelector('.modal-title').insertAdjacentHTML('beforeend', 
                    '<span class="badge bg-light text-dark ms-2" id="filterCount">' + count + '</span>');
            }
        } else if (badge) {
            badge.remove();
        }
    }

    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const categoryId = parseInt(this.value);
            if (this.checked) {
                if (!filterState.categories.includes(categoryId)) {
                    filterState.categories.push(categoryId);
                }
            } else {
                filterState.categories = filterState.categories.filter(id => id !== categoryId);
            }
            updateFilterCount();
        });
    });

    document.getElementById('orderByFilter').addEventListener('change', function() {
        filterState.order_by = this.value;
        updateFilterCount();
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        filterState.status = this.value;
        updateFilterCount();
    });

    document.getElementById('countryFilter').addEventListener('change', function() {
        filterState.country = this.value;
        updateFilterCount();
    });

    const premiumFilter = document.getElementById('premiumFilter');
    if (premiumFilter) {
        premiumFilter.addEventListener('change', function() {
            filterState.highlight = this.checked;
            updateFilterCount();
        });
    }

    document.getElementById('applyFilters').addEventListener('click', function() {
        const url = new URL(window.location);
        url.search = '';
        
        if (filterState.categories.length > 0) {
            url.searchParams.set('categories', filterState.categories.join(','));
        }
        
        if (filterState.order_by) {
            url.searchParams.set('order_by', filterState.order_by);
        }
        
        if (filterState.status !== '') {
            url.searchParams.set('status', filterState.status);
        }
        
        if (filterState.country) {
            url.searchParams.set('country', filterState.country);
        }
        
        if (filterState.highlight) {
            url.searchParams.set('highlight', '1');
        }
        
        window.location.href = url.toString();
    });

    document.getElementById('clearFilters').addEventListener('click', function() {
        filterState.categories = [];
        filterState.order_by = '';
        filterState.status = '';
        filterState.country = '';
        filterState.highlight = false;
        
        document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('orderByFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('countryFilter').value = '';
        if (document.getElementById('premiumFilter')) {
            document.getElementById('premiumFilter').checked = false;
        }
        
        updateFilterCount();
        
        window.location.href = window.location.pathname;
    });
});
</script>
