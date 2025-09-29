<div class="col-12">
    <label for="country" class="form-label fw-semibold"><?= lang('server_country') ?></label>
    <div class="searchable-select-container">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-geo-alt text-muted"></i>
            </span>
            <input type="text" 
                   class="form-control border-start-0 ps-0 searchable-select-input" 
                   id="country-search"
                   placeholder="<?= lang('select_country_placeholder') ?>"
                   autocomplete="new-password">
            <input type="hidden" name="country" id="country" value="<?= $selectedCountry ?? old('country') ?>">
        </div>
        <div class="searchable-select-dropdown" id="country-dropdown">
            <?php foreach ($countries as $code => $name): ?>
                <div class="searchable-select-option" data-value="<?= $code ?>">
                    <?= htmlspecialchars($name) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
window.searchableSelectData = {
    countries: <?= json_encode($countries) ?>,
    selectedValue: "<?= $selectedCountry ?? old('country') ?>"
};
</script>

<link rel="stylesheet" href="<?= url('/assets/css/searchable-select.css') ?>">
<script src="<?= url('/assets/js/searchable-select.js') ?>"></script>