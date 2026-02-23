<?php ob_start(); ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h1 class="h5 mb-0">
                        <i class="bi bi-key me-2" aria-hidden="true"></i><?= lang('headers.change_password') ?>
                    </h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/settings/password') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-3">
                            <label for="old_password" class="form-label"><?= lang('current_password') ?> *</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" autocomplete="current-password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?= lang('new_password') ?> *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" aria-describedby="newPasswordHelp" required minlength="6">
                            <div class="form-text" id="newPasswordHelp"><?= lang('minimum_6_characters') ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?= lang('confirm_password') ?> *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg" aria-hidden="true"></i> <?= lang('submit') ?>
                            </button>
                            <a href="<?= url('/settings/profile') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i> <?= lang('back_to_login') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
