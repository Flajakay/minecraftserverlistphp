<?php
$prefix = $searchIdPrefix ?? '';
$formId = $prefix . 'filterForm';
$wrapInCard = $wrapInCard ?? true;
?>
<?php if ($wrapInCard): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
<?php endif; ?>
        <?php if (!isset($showHeader) || $showHeader): ?>
        <h5 class="fw-bold mb-3">
            <i class="bi bi-funnel text-primary me-2"></i><?= lang('filters') ?>
        </h5>
        <?php endif; ?>

        <form action="<?= url('/servers') ?>" method="GET" id="<?= $formId ?>">
            <!-- Keep existing query params -->
            <?php if (isset($_GET['q'])): ?>
                <input type="hidden" name="q" value="<?= sanitize($_GET['q']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted"><?= lang('categories') ?></label>
                <div class="category-list custom-scrollbar" style="max-height: 300px; overflow-y: auto;">
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
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('<?= $formId ?>');
                if (!form) return;
                
                const checkboxes = form.querySelectorAll('.category-check');
                const updateCategories = () => {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    document.getElementById('<?= $prefix ?>categoriesInput').value = selected.join(',');
                };
                
                checkboxes.forEach(cb => cb.addEventListener('change', updateCategories));
            });
            </script>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-uppercase text-muted"><?= lang('sort_by') ?></label>
                <select name="order_by" class="form-select">
                    <option value="votes" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'votes') ? 'selected' : '' ?>><?= lang('order_by_votes') ?></option>
                    <option value="newest" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'newest') ? 'selected' : '' ?>><?= lang('order_by_newest') ?></option>
                    <option value="players" <?= (isset($_GET['order_by']) && $_GET['order_by'] == 'players') ? 'selected' : '' ?>><?= lang('order_by_players') ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label
                    class="form-label small fw-semibold text-uppercase text-muted"><?= lang('server_country') ?></label>
                <select name="country" class="form-select">
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
                <label class="form-label small fw-semibold text-uppercase text-muted"><?= lang('status') ?></label>
                <select name="status" class="form-select">
                    <option value=""><?= lang('any_status') ?></option>
                    <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>>
                        <?= lang('filter_online') ?></option>
                    <option value="0" <?= (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : '' ?>>
                        <?= lang('filter_offline') ?></option>
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
<?php if ($wrapInCard): ?>
    </div>
</div>
<?php endif; ?>