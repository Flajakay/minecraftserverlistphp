<?php
$prefix = $searchIdPrefix ?? '';
$formId = $prefix . 'filterForm';
$wrapInCard = $wrapInCard ?? true;
$filtersHeadingId = $prefix . 'filtersHeading';
$categoriesLegendId = $prefix . 'categoriesLegend';
$orderById = $prefix . 'orderBy';
$countryId = $prefix . 'country';
$statusId = $prefix . 'status';
?>
<?php if ($wrapInCard): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
<?php endif; ?>
        <section aria-labelledby="<?= $filtersHeadingId ?>">
            <?php if (!isset($showHeader) || $showHeader): ?>
            <h2 class="h5 fw-bold mb-3" id="<?= $filtersHeadingId ?>">
                <i class="bi bi-funnel text-primary me-2" aria-hidden="true"></i><?= lang('filters') ?>
            </h2>
            <?php else: ?>
            <h2 class="visually-hidden" id="<?= $filtersHeadingId ?>"><?= lang('filters') ?></h2>
            <?php endif; ?>

            <form action="<?= url('/servers') ?>" method="GET" id="<?= $formId ?>" class="sidebar-filter-form" data-prefix="<?= $prefix ?>">
            <!-- Keep existing query params -->
            <?php if (isset($_GET['q'])): ?>
                <input type="hidden" name="q" value="<?= sanitize($_GET['q']) ?>">
            <?php endif; ?>

            <fieldset class="mb-3">
                <legend class="form-label small fw-semibold text-uppercase text-muted mb-2" id="<?= $categoriesLegendId ?>"><?= lang('categories') ?></legend>
                <div class="category-list custom-scrollbar" style="max-height: 300px; overflow-y: auto;" role="group" aria-labelledby="<?= $categoriesLegendId ?>">
                    <?php 
                    $categories = \App\Models\Category::getAllWithHierarchy();
                    $selectedCats = [];
                    if (isset($_GET['categories'])) {
                        $selectedCats = array_filter(array_map('intval', explode(',', $_GET['categories'])));
                    }
                    
                    $parentCategories = array_filter($categories, fn($c) => $c->parent_id == 0);
                    foreach ($parentCategories as $cat): 
                        $subcategories = array_filter($categories, fn($c) => $c->parent_id == $cat->id);
                        $isParentSelected = in_array($cat->id, $selectedCats);
                        $catId = $prefix . 'cat_' . $cat->id;
                    ?>
                        <div class="category-group mb-2">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input category-check shadow-sm border-2" type="checkbox" 
                                       value="<?= $cat->id ?>" id="<?= $catId ?>"
                                       <?= $isParentSelected ? 'checked' : '' ?>
                                       style="width: 1.1em; height: 1.1em;">
                                <label class="form-check-label fw-bold text-dark ms-2 small text-uppercase" for="<?= $catId ?>">
                                    <?= sanitize($cat->name) ?>
                                </label>
                            </div>
                            <?php if (!empty($subcategories)): ?>
                                <div class="ms-1 ps-3 border-start border-2 border-light mt-1">
                                    <?php foreach ($subcategories as $subcat): ?>
                                        <?php $subCatId = $prefix . 'cat_' . $subcat->id; ?>
                                        <div class="form-check py-1">
                                            <input class="form-check-input category-check shadow-sm" type="checkbox" 
                                                   value="<?= $subcat->id ?>" id="<?= $subCatId ?>"
                                                   <?= in_array($subcat->id, $selectedCats) ? 'checked' : '' ?>>
                                            <label class="form-check-label small text-secondary" for="<?= $subCatId ?>">
                                                <?= sanitize($subcat->name) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Hidden input to store comma-separated IDs -->
                <input type="hidden" name="categories" id="<?= $prefix ?>categoriesInput" value="<?= sanitize($_GET['categories'] ?? '') ?>">
            </fieldset>

            <script src="<?= asset('js/sidebar-filters.js') ?>" defer></script>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted" for="<?= $orderById ?>"><?= lang('sort_by') ?></label>
                <select name="order_by" class="form-select" id="<?= $orderById ?>">
                    <option value="votes" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'votes') ? 'selected' : '' ?>><?= lang('order_by_votes') ?></option>
                    <option value="newest" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'newest') ? 'selected' : '' ?>><?= lang('order_by_newest') ?></option>
                    <option value="players" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'players') ? 'selected' : '' ?>><?= lang('order_by_players') ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted" for="<?= $countryId ?>"><?= lang('server_country') ?></label>
                <select name="country" class="form-select" id="<?= $countryId ?>">
                    <option value=""><?= lang('any_country') ?></option>
                    <?php
                    $countries = getCountries();
                    foreach ($countries as $code => $name):
                        ?>
                        <option value="<?= $code ?>" <?= (isset($_GET['country']) && $_GET['country'] == $code) ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted" for="<?= $statusId ?>"><?= lang('status') ?></label>
                <select name="status" class="form-select" id="<?= $statusId ?>">
                    <option value=""><?= lang('any_status') ?></option>
                    <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>>
                        <?= lang('filter_online') ?></option>
                    <option value="0" <?= (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : '' ?>>
                        <?= lang('filter_offline') ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted" for="<?= $prefix ?>protocol"><?= lang('filter_protocol') ?></label>
                <select name="protocol" class="form-select" id="<?= $prefix ?>protocol">
                    <option value=""><?= lang('all') ?></option>
                    <option value="minecraft_java" <?= (isset($_GET['protocol']) && $_GET['protocol'] == 'minecraft_java') ? 'selected' : '' ?>>
                        <?= lang('protocol_minecraft_java') ?>
                    </option>
                    <option value="steam_a2s" <?= (isset($_GET['protocol']) && $_GET['protocol'] == 'steam_a2s') ? 'selected' : '' ?>>
                        <?= lang('protocol_steam_a2s') ?>
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted" for="<?= $prefix ?>game_id"><?= lang('game') ?></label>
                <select name="game_id" class="form-select" id="<?= $prefix ?>game_id">
                    <option value=""><?= lang('all') ?></option>
                    <?php
                    $allGames = \App\Models\Game::getEnabled();
                    $selectedGameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
                    foreach ($allGames as $g): ?>
                        <option value="<?= $g->id ?>" <?= $selectedGameId === $g->id ? 'selected' : '' ?>>
                            <?= sanitize($g->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="highlight" value="1" id="<?= $prefix ?>premiumOnly"
                        <?= (isset($_GET['highlight']) && $_GET['highlight']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= $prefix ?>premiumOnly">
                        <?= lang('premium_only') ?>
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <?= lang('apply_filters') ?>
                </button>
                <a href="<?= url('/servers') ?>" class="btn btn-outline-secondary btn-sm">
                    <?= lang('reset_filters') ?>
                </a>
            </div>
            </form>
        </section>
<?php if ($wrapInCard): ?>
    </div>
</div>
<?php endif; ?>
