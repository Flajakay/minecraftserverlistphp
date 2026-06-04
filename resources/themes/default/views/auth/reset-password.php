<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0"><?= lang('headers.resetpassword') ?></h1>
                </div>
                <div class="card-body">
                    
                    <p class="text-muted mb-4">
                        <?= lang('reset_password_message') ?>
                    </p>
                    
                    <form method="POST" action="<?= /** @noinspection PhpUndefinedVariableInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    url('/reset-password/' . urlencode($email) . '/' . $code) ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label"><?= lang('new_password') ?></label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" aria-describedby="passwordHelp" minlength="6" autofocus required>
                            <div class="form-text" id="passwordHelp"><?= lang('password_too_short') ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?= lang('confirm_password') ?></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="6" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <?= lang('reset_password') ?>
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    <div class="text-center">
                        <a href="<?= url('/login') ?>" class="text-decoration-none">
                            <?= lang('back_to_login') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.resetpassword'); ?>
<?php include layout('app'); ?>
