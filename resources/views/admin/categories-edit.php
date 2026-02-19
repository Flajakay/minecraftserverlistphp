<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= lang('headers.edit_category') ?></h2>
                <a href="/admin/categories" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> <?= lang('back_to_categories') ?>
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= /** @noinspection PhpUndefinedVariableInspection */
                        sprintf(lang('editing_category'), sanitize($category->name)) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= lang('admin_add_category_name') ?> *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= sanitize($category->name) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label"><?= lang('admin_add_category_url') ?> *</label>
                            <input type="text" class="form-control" id="url" name="url" 
                                   value="<?= sanitize($category->url) ?>" required>
                            <small class="text-muted"><?= lang('admin_add_category_url_help') ?></small>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label"><?= lang('admin_add_category_title') ?></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= sanitize($category->title) ?>">
                            <small class="text-muted"><?= lang('admin_add_category_title_help') ?></small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label"><?= lang('admin_add_category_description') ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= sanitize($category->description) ?></textarea>
                            <small class="text-muted"><?= lang('admin_add_category_description_help') ?></small>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label"><?= lang('admin_add_category_parent') ?></label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="0" <?= $category->parent_id == 0 ? 'selected' : '' ?>><?= lang('none_root_category') ?></option>
                                <?php /** @noinspection PhpUndefinedVariableInspection */
                                foreach ($parentCategories as $parent): ?>
                                    <?php if ($parent->id != $category->id): ?>
                                        <option value="<?= $parent->id ?>" <?= $category->parent_id == $parent->id ? 'selected' : '' ?>>
                                            <?= sanitize($parent->name) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> <?= lang('submit') ?>
                            </button>
                            <a href="/admin/categories" class="btn btn-outline-secondary ms-2"><?= lang('cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.edit_category'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
