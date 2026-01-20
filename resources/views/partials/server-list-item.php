<div class="d-flex align-items-center">
    <div class="flex-shrink-0 me-3">
        <?php if ($server->image): ?>
            <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                 class="rounded shadow-sm" 
                 width="48" 
                 height="48" 
                 style="object-fit: cover;"
                 alt="<?= htmlspecialchars($server->name) ?>">
        <?php else: ?>
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center shadow-sm" 
                 style="width: 48px; height: 48px;">
                <i class="bi bi-controller text-white"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="flex-grow-1 min-w-0">
        <div class="d-flex align-items-start justify-content-between">
            <div class="flex-grow-1 min-w-0">
                <h6 class="mb-1 fw-semibold">
                    <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
                       class="text-decoration-none text-dark stretched-link">
                        <?= htmlspecialchars($server->name) ?>
                    </a>
                    <?php if ($server->highlight): ?>
                        <i class="bi bi-star-fill text-warning ms-1" title="<?= lang('premium_server') ?>"></i>
                    <?php endif; ?>
                </h6>
                
                <div class="mb-1">
                    <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> me-1">
                        <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                        <?= $server->status ? lang('active') : lang('inactive') ?>
                    </span>
                    <?php if ($server->status && $server->max_players > 0): ?>
                        <span class="badge bg-info me-1">
                            <i class="bi bi-people me-1"></i>
                            <?= $server->players ?>/<?= $server->max_players ?>
                        </span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= getCountryName($server->country) ?>
                    </span>
                </div>
                
                <div class="d-flex align-items-center text-muted small">
                    <span>
                        <i class="bi bi-arrow-up text-success me-1"></i>
                        <?= number_format($server->votes) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
