<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-key text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('headers.resetpassword') ?></h2>
                <p class="text-muted"><?= lang('lost_password_message1') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <?= lang('lost_password_message2') ?>
                    </div>
                    
                    <form method="POST" action="<?= url('/lost-password') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold"><?= lang('email') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 ps-0" 
                                       id="email" 
                                       name="email" 
                                       value="<?= old('email') ?>" 
                                       placeholder="<?= lang('enter_email')?>"
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-send me-2"></i><?= lang('send_reset_link') ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <?= lang('remember_your_password')?>
                    <a href="<?= url('/login') ?>" class="text-primary text-decoration-none fw-semibold">
                        <?= lang('back_to_login')?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = 'Reset Password - Minecraft Server List'; ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
