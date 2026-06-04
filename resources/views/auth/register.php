<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-controller text-primary" style="font-size: 2.5rem;" aria-hidden="true"></i>
                </div>
                <h1 class="h3 fw-bold text-dark"><?= lang('join_community') ?></h1>
                <p class="text-muted"><?= lang('create_account_subtitle') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/register') ?>">
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
                                       placeholder="<?= lang('choose_username') ?>"
                                       autocomplete="username"
                                       autocapitalize="none"
                                       spellcheck="false"
                                       autofocus
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold"><?= lang('email') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 ps-0" 
                                       id="email" 
                                       name="email" 
                                       value="<?= old('email') ?>" 
                                       placeholder="<?= lang('enter_email') ?>"
                                       autocomplete="email"
                                       autocapitalize="none"
                                       spellcheck="false"
                                       inputmode="email"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold"><?= lang('display_name') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-card-text text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="name" 
                                       name="name" 
                                       value="<?= old('name') ?>" 
                                       placeholder="<?= lang('your_display_name') ?>"
                                       autocomplete="nickname"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold"><?= lang('password') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 ps-0" 
                                       id="password" 
                                       name="password" 
                                       placeholder="<?= lang('create_strong_password') ?>"
                                       autocomplete="new-password"
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-person-plus me-2" aria-hidden="true"></i><?= lang('create_account') ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <?= lang('have_account') ?> 
                    <a href="<?= url('/login') ?>" class="text-primary text-decoration-none fw-semibold">
                        <?= lang('sign_in_here') ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.register'); ?>
<?php include layout('app'); ?>
