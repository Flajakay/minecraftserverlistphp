<div class="sidebar bg-light p-3">
    <h5><?= lang('categories') ?></h5>
    <?php $categories = \App\Models$1::getWithServerCount(); ?>
    <ul class="list-unstyled">
        <?php foreach ($categories as $category): ?>
            <li class="mb-2">
                <a href="<?= url('/category/' . $category->url) ?>" class="text-decoration-none">
                    <?= htmlspecialchars($category->name) ?>
                    <span class="badge bg-secondary"><?= $category->server_count ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>

    <h6><?= lang('filters') ?></h6>
    <div class="mb-3">
        <label class="form-label"><?= lang('order_by') ?>:</label>
        <select class="form-select form-select-sm" onchange="updateFilter('order_by', this.value)">
            <option value=""><?= lang('order_by_latest') ?></option>
            <option value="votes"><?= lang('order_by_votes') ?></option>
            <option value="players"><?= lang('order_by_players') ?></option>
            <option value="favorites"><?= lang('order_by_favorites') ?></option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= lang('filter_status') ?>:</label>
        <select class="form-select form-select-sm" onchange="updateFilter('status', this.value)">
            <option value=""><?= lang('all') ?></option>
            <option value="1"><?= lang('filter_online') ?></option>
            <option value="0"><?= lang('filter_offline') ?></option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= lang('filter_country') ?>:</label>
        <select class="form-select form-select-sm" onchange="updateFilter('country', this.value)">
            <option value=""><?= lang('all_countries') ?></option>
            <?php foreach (getCountries() as $code => $name): ?>
                <option value="<?= $code ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (\App\Models$1::getValue('premium')): ?>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="premiumFilter" onchange="updateFilter('highlight', this.checked ? 1 : '')">
                <label class="form-check-label" for="premiumFilter">
                    <?= lang('premium_only') ?>
                </label>
            </div>
        </div>
    <?php endif; ?>

    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
        <?= lang('reset_filters') ?>
    </button>
</div>
