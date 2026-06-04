<div class="card h-100 border-0 shadow-sm server-card <?= /** @noinspection PhpUndefinedVariableInspection */
$server->highlight ? 'border-warning border-2' : '' ?>">
    <?php if ($server->highlight): ?>
        <div class="card-header bg-warning text-dark py-2 border-0">
            <div class="d-flex align-items-center justify-content-center">
                <i class="bi bi-star-fill me-1" aria-hidden="true"></i>
                <small class="fw-semibold"><?= lang('premium_server') ?></small>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($server->image): ?>
        <div class="position-relative banner-container">
            <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                 class="card-img-top server-banner" 
                 alt="<?= sanitize($server->name) ?>" 
                 loading="lazy">
            <div class="position-absolute top-0 end-0 p-2">
                <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> shadow">
                    <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1" aria-hidden="true"></i>
                    <?= $server->status ? lang('active') : lang('inactive') ?>
                </span>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-light d-flex align-items-center justify-content-center position-relative banner-container" style="height: 120px;">
            <i class="bi bi-controller text-muted" style="font-size: 3rem;" aria-hidden="true"></i>
            <div class="position-absolute top-0 end-0 p-2">
                <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> shadow">
                    <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1" aria-hidden="true"></i>
                    <?= $server->status ? lang('active') : lang('inactive') ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-2">
            <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
               class="text-decoration-none text-dark stretched-link">
                <?= sanitize($server->name) ?>
            </a>
        </h5>
        
        <div class="mb-3">
            <?php if ($server->status && $server->max_players > 0): ?>
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-grow-1">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" 
                                 role="progressbar" 
                                 style="width: <?= $server->max_players > 0 ? ($server->players / $server->max_players * 100) : 0 ?>%"
                                 aria-valuemin="0"
                                 aria-valuemax="<?= $server->max_players ?>"
                                 aria-valuenow="<?= $server->players ?>">
                            </div>
                        </div>
                    </div>
                    <small class="text-muted ms-2">
                        <?= $server->players ?>/<?= $server->max_players ?>
                    </small>
                </div>
            <?php endif; ?>
            
            <span class="badge bg-light text-dark border me-1">
                <i class="bi bi-geo-alt me-1" aria-hidden="true"></i>
                <?= getCountryName($server->country) ?>
            </span>
        </div>
        
        <p class="card-text text-muted small flex-grow-1">
            <?= sanitize(substr(strip_tags(displayHtml($server->description)), 0, 100)) ?>
            <?php if (strlen(strip_tags(displayHtml($server->description))) > 100): ?>...<?php endif; ?>
        </p>
        
        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
            <div class="d-flex align-items-center">
                <span class="text-success">
                    <i class="bi bi-arrow-up me-1" aria-hidden="true"></i>
                    <small><?= number_format($server->votes) ?></small>
                </span>
            </div>
            <small class="text-muted">
                <?= sanitize($server->address) ?>:<?= sanitize($server->port) ?>
            </small>
        </div>
    </div>
</div>
