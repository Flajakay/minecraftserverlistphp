<div class="col-12">
    <label for="country" class="form-label fw-semibold"><?= lang('server_country') ?> *</label>
    <div class="searchable-select-container">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-geo-alt text-muted" aria-hidden="true"></i>
            </span>
            <input type="text" 
                   class="form-control border-start-0 ps-0 searchable-select-input" 
                   id="country-search"
                   placeholder="<?= lang('select_country_placeholder') ?>"
                   autocomplete="country-name"
                   role="combobox"
                   aria-autocomplete="list"
                   aria-expanded="false"
                   aria-controls="country-dropdown">
            <input type="hidden" name="country" id="country" value="<?= $selectedCountry ?? old('country') ?>">
        </div>
        <div class="searchable-select-dropdown" id="country-dropdown" role="listbox" aria-label="<?= lang('server_country') ?>">
            <?php /** @noinspection PhpUndefinedVariableInspection */
            foreach ($countries as $code => $name): ?>
                <div class="searchable-select-option" data-value="<?= $code ?>" role="option" aria-selected="false">
                    <?= sanitize($name) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= url('/assets/css/searchable-select.css') ?>">
<script src="<?= url('/assets/js/searchable-select.js') ?>" defer></script>
