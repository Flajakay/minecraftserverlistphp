<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-controller text-primary" style="font-size: 2.5rem;" aria-hidden="true"></i>
                </div>
                <h1 class="h3 fw-bold text-dark"><?= lang('welcome_back') ?></h1>
                <p class="text-muted"><?= lang('sign_in_subtitle') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/login') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold"><?= lang('username') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="username" 
                                       name="username" 
                                       value="<?= old('username') ?>" 
                                       placeholder="<?= lang('enter_username') ?>"
                                       autocomplete="username"
                                       autocapitalize="none"
                                       spellcheck="false"
                                       autofocus
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold"><?= lang('password') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 ps-0" 
                                       id="password" 
                                       name="password" 
                                       placeholder="<?= lang('enter_password') ?>"
                                       autocomplete="current-password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">
                                    <?= lang('remember_me') ?>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i><?= lang('sign_in') ?>
                        </button>
                        
                        <div class="text-center">
                            <a href="<?= url('/lost-password') ?>" class="text-muted text-decoration-none small">
                                <?= lang('forgot_password') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <?= lang('no_account') ?> 
                    <a href="<?= url('/register') ?>" class="text-primary text-decoration-none fw-semibold">
                        <?= lang('create_one_here') ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.login'); ?>
<?php include layout('app'); ?>
