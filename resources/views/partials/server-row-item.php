<div class="server-row-item border rounded-3 p-3 mb-3 bg-white shadow-sm position-relative overflow-hidden">

    <div class="row align-items-center g-3">
        <!-- 1. Icon Section (Fixed Width) -->
        <div class="col-auto">
            <div class="server-icon-wrapper position-relative d-inline-block">
                <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" aria-label="<?= lang('view_details') ?>: <?= sanitize($server->name) ?>">
                    <?php if ($server->icon): ?>
                        <img src="<?= url('/uploads/icons/' . $server->icon) ?>"
                            class="server-icon-responsive rounded-3 shadow-sm bg-light"
                            style="width: 80px; height: 80px; object-fit: cover;"
                            alt="<?= sanitize($server->name) ?>">
                    <?php else: ?>
                        <div class="server-icon-responsive rounded-3 d-flex align-items-center justify-content-center shadow-sm bg-light text-secondary"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-controller fs-2" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- Status Dot (Replaces the text badge) -->
                <span class="status-dot bg-<?= $server->status ? 'success' : 'danger' ?>"
                    role="img"
                    aria-label="<?= $server->status ? lang('online') : lang('offline') ?>"
                    title="<?= $server->status ? lang('online') : lang('offline') ?>">
                </span>
            </div>
        </div>

        <!-- 2. Content Section (Fluid) -->
        <div class="col min-w-0"> <!-- min-w-0 prevents flex child overflow -->
            <div class="d-flex flex-column h-100 justify-content-center">

                <!-- Title Row -->
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="mb-0 fw-bold text-truncate pe-2">
                        <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>"
                            class="text-decoration-none text-dark stretched-link">
                            <?= sanitize($server->name) ?>
                        </a>
                        <?php if ($server->highlight): ?>
                            <i class="bi bi-patch-check-fill text-warning ms-1 small" aria-hidden="true"></i>
                            <span class="visually-hidden"><?= lang('premium_server') ?></span>
                        <?php endif; ?>
                    </h5>

                    <!-- Mobile Stats (Visible only on mobile) -->
                    <div class="d-md-none d-flex align-items-center gap-2 small">
                        <?php if ($server->status): ?>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people-fill text-muted me-1"></i><?= number_format($server->players) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Metadata Row (Categories & IP) -->
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 fw-normal">
                        <i class="bi bi-globe me-1"></i><?= sanitize($server->address) ?>
                    </span>

                    <?php if (isset($server->categories) && !empty($server->categories)): ?>
                        <?php foreach (array_slice($server->categories, 0, 2) as $category): ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 fw-normal d-none d-sm-inline-block">
                                <?= sanitize($category->category_name) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 fw-normal">
                        <?= sanitize($server->game_name ?? ($server->protocol === 'steam_a2s' ? 'Steam' : 'Minecraft')) ?>
                    </span>
                </div>

                <!-- Description (CSS Clamped & Hidden on Mobile) -->
                <?php if ($server->description): ?>
                    <p class="text-muted small mb-0 text-clamp-2 server-description-mobile-hide d-none d-md-block" style="line-height: 1.4;">
                        <?= strip_tags(displayHtml($server->description)) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Desktop Stats/Actions (Hidden on Mobile) -->
        <div class="col-md-3 d-none d-md-flex flex-column justify-content-center align-items-end ps-4">
            <div class="text-end mb-2">
                <div class="fw-bold fs-5 text-dark"><?= number_format($server->votes) ?></div>
                <div class="text-muted small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;"><?= lang('votes') ?>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>"
                    class="btn btn-sm btn-outline-primary position-relative z-2"> <!-- z-2 needed for stretched-link -->
                    <?= lang('view_details') ?>
                </a>
                <button type="button" class="btn btn-sm btn-light position-relative z-2" title="<?= lang('copy_ip') ?>" aria-label="<?= lang('copy_ip') ?>"
                    onclick="copyToClipboard(<?= json_encode($server->address) ?>)">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
    </div>
</div>
