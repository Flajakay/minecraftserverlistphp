<?php ob_start(); ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-key me-2"></i><?= lang('headers.change_password') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/settings/password') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-3">
                            <label for="old_password" class="form-label"><?= lang('current_password') ?> *</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?= lang('new_password') ?> *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <div class="form-text"><?= lang('minimum_6_characters') ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?= lang('confirm_password') ?> *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> <?= lang('submit') ?>
                            </button>
                            <a href="<?= url('/settings/profile') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> <?= lang('back_to_login') ?>
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