<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= lang('titles.edit_server') ?></h2>
                <a href="/admin/servers" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> <?= lang('back_to_servers') ?>
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= sprintf(lang('editing_server'), htmlspecialchars($server->name)) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label"><?= lang('server_name') ?> *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($server->name) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label"><?= lang('server_category') ?> *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category->id ?>" <?= $server->category_id == $category->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-8">
                                <label for="address" class="form-label"><?= lang('server_address') ?> *</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?= htmlspecialchars($server->address) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="port" class="form-label"><?= lang('server_connection_port') ?></label>
                                <input type="number" class="form-control" id="port" name="port" 
                                       value="<?= $server->port ?>" min="1" max="65535">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="description" class="form-label"><?= lang('server_description') ?></label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($server->description) ?></textarea>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="website" class="form-label"><?= lang('server_website') ?></label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?= htmlspecialchars($server->website) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label"><?= lang('server_country') ?></label>
                                <select class="form-select" id="country" name="country">
                                    <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?= $code ?>" <?= $server->country == $code ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="youtube_id" class="form-label"><?= lang('server_youtube_id') ?></label>
                            <input type="text" class="form-control" id="youtube_id" name="youtube_id" 
                                   value="<?= htmlspecialchars($server->youtube_id) ?>">
                            <small class="text-muted"><?= lang('server_youtube_id_help') ?></small>
                        </div>

                        <div class="mt-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" 
                                               <?= $server->active ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="active">
                                            <?= lang('active') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="private" name="private" 
                                               <?= $server->private ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="private">
                                            <?= lang('private') ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="highlight" name="highlight" 
                                               <?= $server->highlight ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="highlight">
                                            <?= lang('premium') ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> <?= lang('submit') ?>
                            </button>
                            <a href="/admin/servers" class="btn btn-outline-secondary ms-2"><?= lang('cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.edit_server') . ' - ' . setting('title'); ?>

<?php include __DIR__ . '/../layouts/app.php'; ?>
