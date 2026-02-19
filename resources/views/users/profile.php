<?php ob_start(); ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php /** @noinspection PhpUndefinedVariableInspection */
                    if ($user->avatar): ?>
                        <img src="<?= url('public/uploads/avatars/' . $user->avatar) ?>" 
                             class="rounded-circle mb-3" 
                             width="120" height="120" 
                             alt="<?= sanitize($user->name) ?>">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person-fill text-white fs-1"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4 class="card-title"><?= sanitize($user->name) ?></h4>
                    <p class="text-muted">@<?= sanitize($user->username) ?></p>
                    
                    <?php if ($user->about): ?>
                        <p class="card-text"><?= sanitize($user->about) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($user->website): ?>
                        <a href="<?= sanitize($user->website) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-link-45deg"></i> <?= lang('website') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><?= lang('user_info') ?></h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6 text-muted"><?= lang('join_date') ?>:</div>
                        <div class="col-6"><?= date('M j, Y', strtotime($user->created_at)) ?></div>
                    </div>
                    
                    <?php if ($user->location): ?>
                    <div class="row mb-2">
                        <div class="col-6 text-muted"><?= lang('location') ?>:</div>
                        <div class="col-6"><?= sanitize($user->location) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-2">
                        <div class="col-6 text-muted"><?= lang('last_activity') ?>:</div>
                        <div class="col-6"><?= timeAgo($user->last_activity) ?></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 text-muted"><?= lang('total_servers') ?>:</div>
                        <div class="col-6"><?= /** @noinspection PhpUndefinedVariableInspection */
                            count($servers) ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ($user->facebook || $user->twitter): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><?= lang('headers.social_data') ?></h6>
                </div>
                <div class="card-body">
                    <?php if ($user->facebook): ?>
                        <a href="https://facebook.com/<?= sanitize($user->facebook) ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-facebook"></i> <?= lang('facebook') ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($user->twitter): ?>
                        <a href="https://twitter.com/<?= sanitize($user->twitter) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-twitter"></i> <?= lang('twitter') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-8">
            <?php if ($user->cover): ?>
            <div class="card mb-4">
                <img src="<?= url('public/uploads/covers/' . $user->cover) ?>" 
                     class="card-img-top" 
                     style="height: 200px; object-fit: cover;"
                     alt="<?= lang('cover') ?>">
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= lang('servers') ?></h5>
                    <span class="badge bg-primary"><?= count($servers) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($servers)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-server fs-1 d-block mb-2"></i>
                            <?= lang('no_servers') ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($servers as $server): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-1">
                                                    <a href="<?= url('server/' . $server->address . ':' . $server->port) ?>" 
                                                       class="text-decoration-none">
                                                        <?= sanitize($server->name) ?>
                                                    </a>
                                                </h6>
                                                <?php if ($server->status): ?>
                                                    <span class="badge bg-success"><?= lang('online') ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?= lang('offline') ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                <?= sanitize($server->address) ?>:<?= $server->port ?>
                                            </p>
                                            
                                            <?php if ($server->status): ?>
                                                <div class="d-flex justify-content-between text-sm">
                                                    <span><i class="bi bi-people"></i> <?= $server->players ?>/<?= $server->max_players ?></span>
                                                    <span><i class="bi bi-arrow-up"></i> <?= $server->votes ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.profile'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
