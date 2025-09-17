<?php
$filterCount = 0;
if (isset($_GET['order_by']) && $_GET['order_by']) $filterCount++;
if (isset($_GET['country']) && $_GET['country']) $filterCount++;
if (isset($_GET['status']) && $_GET['status'] !== '') $filterCount++;
if (isset($_GET['highlight']) && $_GET['highlight']) $filterCount++;

$modalTitle = isset($category) ? lang('filters') . ' - ' . htmlspecialchars($category->name) : lang('filters');
$currentCategoryId = isset($category) ? $category->id : null;
?>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white border-0" style="background: linear-gradient(45deg, #0d6efd, #0056b3) !important;">
                <h5 class="modal-title fw-bold" id="filterModalLabel">
                    <i class="bi bi-funnel-fill me-2"></i><?= $modalTitle ?>
                    <?php if ($filterCount > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?= $filterCount ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= lang('close_modal') ?>"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-tags-fill me-2 text-primary"></i><?= lang('categories') ?>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <?php $categories = \App\Models\Category::getWithServerCount(); ?>
                                <div class="category-grid" style="max-height: 400px; overflow-y: auto;">
                                    <?php foreach ($categories as $cat): ?>
                                        <a href="<?= url('/category/' . $cat->url) ?>" 
                                           class="category-item d-flex justify-content-between align-items-center p-3 text-decoration-none border-bottom <?= $currentCategoryId && $cat->id == $currentCategoryId ? 'active-category' : '' ?>">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-controller me-3 text-primary fs-5"></i>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($cat->name) ?></span>
                                            </div>
                                            <span class="badge rounded-pill" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;"><?= $cat->server_count ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-sliders me-2 text-primary"></i><?= lang('filters') ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark"><?= lang('order_by') ?></label>
                                        <select class="form-select border-0 shadow-sm" onchange="updateFilter('order_by', this.value)" style="background-color: #fff;">
                                            <option value=""><?= lang('order_by_latest') ?></option>
                                            <option value="votes" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'votes') ? 'selected' : '' ?>><?= lang('order_by_votes') ?></option>
                                            <option value="players" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'players') ? 'selected' : '' ?>><?= lang('order_by_players') ?></option>
                                            <option value="favorites" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'favorites') ? 'selected' : '' ?>><?= lang('order_by_favorites') ?></option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark"><?= lang('filter_status') ?></label>
                                        <select class="form-select border-0 shadow-sm" onchange="updateFilter('status', this.value)" style="background-color: #fff;">
                                            <option value=""><?= lang('all') ?></option>
                                            <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>>
                                                <i class="bi bi-wifi"></i> <?= lang('filter_online') ?>
                                            </option>
                                            <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : '' ?>>
                                                <i class="bi bi-wifi-off"></i> <?= lang('filter_offline') ?>
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark"><?= lang('filter_country') ?></label>
                                        <select class="form-select border-0 shadow-sm" onchange="updateFilter('country', this.value)" style="background-color: #fff;">
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
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark"><?= lang('premium_only') ?></label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" id="premiumFilter" onchange="updateFilter('highlight', this.checked ? 1 : '')" <?= (isset($_GET['highlight']) && $_GET['highlight']) ? 'checked' : '' ?> style="transform: scale(1.2);">
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
                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                    <i class="bi bi-arrow-clockwise me-1"></i><?= lang('reset_filters') ?>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= lang('close_modal') ?>
                </button>
            </div>
        </div>
    </div>
</div>
