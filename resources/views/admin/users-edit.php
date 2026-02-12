<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= lang('titles.edit_user') ?></h2>
                <a href="/admin/users" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> <?= lang('back_to_users') ?>
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= /** @noinspection PhpUndefinedVariableInspection */
                        sprintf(lang('editing_user'), sanitize($user->username)) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label"><?= lang('username') ?> *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= sanitize($user->username) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label"><?= lang('email') ?> *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= sanitize($user->email) ?>" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="name" class="form-label"><?= lang('name') ?> *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= sanitize($user->name) ?>" required>
                        </div>

                        <div class="mt-3">
                            <label for="about" class="form-label"><?= lang('about') ?></label>
                            <textarea class="form-control" id="about" name="about" rows="3" maxlength="128"><?= sanitize($user->about) ?></textarea>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="website" class="form-label"><?= lang('website') ?></label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?= sanitize($user->website) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label"><?= lang('location') ?></label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?= sanitize($user->location) ?>" maxlength="64">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="type" class="form-label"><?= lang('user_type') ?></label>
                            <select class="form-select" id="type" name="type">
                                <option value="0" <?= $user->type == 0 ? 'selected' : '' ?>><?= lang('user') ?></option>
                                <option value="1" <?= $user->type == 1 ? 'selected' : '' ?>><?= lang('administrator') ?></option>
                                <option value="2" <?= $user->type == 2 ? 'selected' : '' ?>><?= lang('owner') ?></option>
                            </select>
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" 
                                       <?= $user->active ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">
                                    <?= lang('active') ?>
                                </label>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="private" name="private" 
                                       <?= $user->private ? 'checked' : '' ?>>
                                <label class="form-check-label" for="private">
                                    <?= lang('private_profile') ?>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> <?= lang('submit') ?>
                            </button>
                            <a href="/admin/users" class="btn btn-outline-secondary ms-2"><?= lang('cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
