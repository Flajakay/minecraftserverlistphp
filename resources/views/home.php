<?php ob_start(); ?>

<!-- Hero Section -->
<div class="hero-section text-white py-5 mb-5 rounded-3 shadow">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?= lang('hero_title') ?></h1>
                <p class="lead mb-4"><?= lang('hero_subtitle') ?></p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?= url('/servers') ?>" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-search me-2"></i><?= lang('browse_servers') ?>
                    </a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= url('/register') ?>" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-person-plus me-2"></i><?= lang('join_community') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if (!empty($featured_servers)): ?>
        <!-- Featured Servers Section -->
        <section class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-star-fill text-warning fs-4 me-2"></i>
                <h2 class="h3 mb-0"><?= lang('featured_servers') ?></h2>
            </div>
            <div class="featured-servers">
                <?php foreach ($featured_servers as $server): ?>
                    <?php include __DIR__ . '/partials/server-row-item.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?= url('/servers') ?>" class="btn btn-primary">
                    <i class="bi bi-grid me-2"></i><?= lang('view_all_servers') ?>
                </a>
            </div>
        </section>
    <?php else: ?>
        <!-- No Featured Servers -->
        <section class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-star text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="h4 text-dark mb-3"><?= lang('no_featured_servers_yet') ?></h3>
            <p class="text-muted mb-4"><?= lang('no_featured_servers_description') ?></p>
        </section>
    <?php endif; ?>

    <!-- Quick Stats or Additional Info -->
    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="text-center p-4 bg-light rounded-3">
                <i class="bi bi-server text-primary fs-1 mb-3"></i>
                <h4 class="h5"><?= lang('active_servers') ?></h4>
                <p class="text-muted mb-0"><?= lang('active_servers_description') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4 bg-light rounded-3">
                <i class="bi bi-people text-success fs-1 mb-3"></i>
                <h4 class="h5"><?= lang('join_players') ?></h4>
                <p class="text-muted mb-0"><?= lang('join_players_description') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4 bg-light rounded-3">
                <i class="bi bi-star text-warning fs-1 mb-3"></i>
                <h4 class="h5"><?= lang('rate_review') ?></h4>
                <p class="text-muted mb-0"><?= lang('rate_review_description') ?></p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/app.php'; ?>