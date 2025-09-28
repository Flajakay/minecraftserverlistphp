<div class="col-12">
    <label class="form-label fw-semibold"><?= lang('server_category') ?> *</label>
    
    <div class="category-cloud-container">
        <div id="category-cloud" class="category-cloud"></div>
        <div id="hidden-category-inputs"></div>
    </div>
    
    <small class="text-muted">
        Click categories to select them. Main categories will show their subcategories when selected.
    </small>
</div>

<script>
window.categoryCloudData = <?= json_encode($categories) ?>;
</script>

<link rel="stylesheet" href="<?= url('/assets/css/category-cloud.css') ?>">
<script src="<?= url('/assets/js/category-cloud.js') ?>"></script>