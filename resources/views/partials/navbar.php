<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="<?= url('/') ?>">
            <i class="bi bi-controller text-primary"></i> 
            <span class="text-white"><?= setting('title') ?></span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link px-3" href="<?= url('/') ?>">
                        <i class="bi bi-house me-1"></i><?= lang('menu.home') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="<?= url('/servers') ?>">
                        <i class="bi bi-server me-1"></i><?= lang('titles.servers') ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= url('/submit') ?>">
                            <i class="bi bi-plus-circle me-1"></i><?= lang('menu.submit') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= url('/premium') ?>">
                            <i class="bi bi-star me-1"></i><?= lang('menu.purchase_highlight') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle px-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear me-1"></i><?= lang('menu.admin') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="<?= url('/admin/users') ?>"><i class="bi bi-people me-2"></i><?= lang('menu.users_management') ?></a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/servers') ?>"><i class="bi bi-server me-2"></i><?= lang('menu.servers_management') ?></a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/categories') ?>"><i class="bi bi-tags me-2"></i><?= lang('menu.categories_management') ?></a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/reports') ?>"><i class="bi bi-flag me-2"></i><?= lang('menu.reports_management') ?></a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/payments') ?>"><i class="bi bi-credit-card me-2"></i><?= lang('menu.payments_management') ?></a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/migrations') ?>"><i class="bi bi-database-gear me-2"></i><?= lang('menu.migrations') ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/settings') ?>"><i class="bi bi-sliders me-2"></i><?= lang('menu.settings') ?></a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= auth()->username ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= url('/my-servers') ?>"><i class="bi bi-server me-2"></i><?= lang('menu.my_servers') ?></a></li>
                        <li><a class="dropdown-item" href="<?= url('/my-favorites') ?>"><i class="bi bi-heart me-2"></i><?= lang('menu.my_favorites') ?></a></li>
                        <li><a class="dropdown-item" href="<?= url('/premium') ?>"><i class="bi bi-star me-2"></i><?= lang('menu.purchase_highlight') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('/profile/' . auth()->username) ?>"><i class="bi bi-person me-2"></i><?= lang('menu.my_profile') ?></a></li>
                        <li><a class="dropdown-item" href="<?= url('/settings/profile') ?>"><i class="bi bi-gear me-2"></i><?= lang('menu.settings') ?></a></li>
                        <li><a class="dropdown-item" href="<?= url('/settings/password') ?>"><i class="bi bi-key me-2"></i><?= lang('menu.change_password') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i><?= lang('menu.logout') ?></a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= url('/login') ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i><?= lang('menu.login') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-2" href="<?= url('/register') ?>">
                            <i class="bi bi-person-plus me-1"></i><?= lang('menu.register') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
