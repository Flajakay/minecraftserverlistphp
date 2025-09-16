<footer class="bg-dark text-light py-4 mt-auto border-top border-secondary">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-controller text-primary fs-3 me-2"></i>
                    <h5 class="mb-0 text-white"><?= setting('title') ?></h5>
                </div>
                <p class="text-muted mb-0">
                    <?= lang('index_description') ?>
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-white mb-3"><?= lang('categories') ?></h6>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="<?= url('/servers') ?>" class="text-decoration-none text-muted">
                                    <i class="bi bi-server me-1"></i><?= lang('menu.home') ?>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= url('/submit') ?>" class="text-decoration-none text-muted">
                                    <i class="bi bi-plus-circle me-1"></i><?= lang('menu.submit') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="<?= url('/contact') ?>" class="text-decoration-none text-muted">
                                    <i class="bi bi-envelope me-1"></i><?= lang('contact') ?>
                                </a>
                            </li>
                            <?php if (!isLoggedIn()): ?>
                            <li class="mb-2">
                                <a href="<?= url('/register') ?>" class="text-decoration-none text-muted">
                                    <i class="bi bi-person-plus me-1"></i><?= lang('menu.register') ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    <a href="<?= url('/terms-of-service') ?>" class="text-decoration-none text-muted me-2">
                        <?= lang('terms-of-service') ?>
                    </a>
                    <a href="<?= url('/privacy-policy') ?>" class="text-decoration-none text-muted">
                        <?= lang('privacy-policy') ?>
                    </a>
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <?= lang('language') ?>
                    <?php foreach(getAvailableLanguages() as $language_name): ?>
                        <a href="?language=<?= $language_name ?>" class="text-decoration-none text-muted mx-1"><?= $language_name ?></a>
                    <?php endforeach; ?>
                </small>
            </div>
        </div>
    </div>
</footer>