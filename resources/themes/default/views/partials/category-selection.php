<div class="col-12">
    <fieldset class="mb-0">
        <legend class="form-label fw-semibold mb-2"><?= lang('server_category') ?> *</legend>

        <div class="category-cloud-container" aria-describedby="categoryHelp">
            <div id="category-cloud" class="category-cloud" role="group" aria-label="<?= lang('server_category') ?>"></div>
            <div id="hidden-category-inputs"></div>
        </div>

        <small class="text-muted" id="categoryHelp">
            <?= lang('how_to_select_categories'); ?>
        </small>
    </fieldset>
</div>

<script id="category-cloud-config" type="application/json">
{
    "categories": <?= json_encode($categories) ?>,
    "selectedCategoryIds": <?= json_encode(isset($server_categories) ? array_column($server_categories, 'category_id') : []) ?>
}
</script>

<link rel="stylesheet" href="<?= themeAsset('css/category-cloud.css', 'default') ?>">
<script src="<?= url('/assets/js/category-cloud.js') ?>" defer></script>
