<div class="server-row-item border rounded-3 p-3 mb-3 bg-white shadow-sm">
    <div class="row g-3 align-items-center">
        <!-- Banner Section -->
        <div class="col-md-4 col-lg-3">
            <div class="server-banner-container position-relative">
                <?php if ($server->image): ?>
                    <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                         class="server-banner-row img-fluid rounded shadow-sm" 
                         alt="<?= htmlspecialchars($server->name) ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="server-banner-placeholder bg-light rounded d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-controller text-muted fs-1"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Status Badge -->
                <div class="position-absolute top-0 end-0 mt-2 me-2">
                    <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> shadow-sm">
                        <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                        <?= $server->status ? lang('active') : lang('inactive') ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Server Info Section -->
        <div class="col-md-5 col-lg-6">
            <div class="server-info">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <h5 class="server-name mb-1 fw-bold">
                        <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
                           class="text-decoration-none text-dark">
                            <?= htmlspecialchars($server->name) ?>
                        </a>
                        <?php if ($server->highlight): ?>
                            <i class="bi bi-star-fill text-warning ms-1" title="<?= lang('premium_server') ?>"></i>
                        <?php endif; ?>
                    </h5>
                    <div class="d-flex gap-3 d-md-none">
                        <span class="text-success">
                            <i class="bi bi-arrow-up me-1"></i>
                            <span class="fw-semibold"><?= number_format($server->votes) ?></span>
                        </span>
                        <span class="text-danger">
                            <i class="bi bi-heart me-1"></i>
                            <span class="fw-semibold" id="favorite-count-mobile-<?= $server->id ?>"><?= number_format($server->favorites) ?></span>
                        </span>
                    </div>
                </div>
                
                <!-- Server Address -->
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-globe me-1"></i>
                        <?= $server->address ?>:<?= $server->port ?>
                    </small>
                </div>
                
                <!-- Badges -->
                <div class="mb-2">
                    <?php if ($server->status && $server->max_players > 0): ?>
                        <span class="badge bg-info me-1">
                            <i class="bi bi-people me-1"></i>
                            <?= $server->players ?>/<?= $server->max_players ?>
                        </span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark border me-1">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= getCountryName($server->country) ?>
                    </span>
                    <?php if (isset($server->category_name)): ?>
                        <span class="badge bg-secondary me-1">
                            <i class="bi bi-tag me-1"></i>
                            <?= htmlspecialchars($server->category_name) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <?php if ($server->description): ?>
                    <p class="server-description text-muted mb-0 small">
                        <?= htmlspecialchars(substr(strip_tags($server->description), 0, 150)) ?>
                        <?php if (strlen(strip_tags($server->description)) > 150): ?>...<?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stats & Actions Section -->
        <div class="col-md-3 col-lg-3">
            <div class="text-center text-md-end">
                <!-- Stats -->
                <div class="server-stats mb-3 d-none d-md-block">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3 mb-2">
                        <span class="text-success">
                            <i class="bi bi-arrow-up me-1"></i>
                            <span class="fw-semibold"><?= number_format($server->votes) ?></span>
                            <small class="text-muted"><?= lang('votes') ?></small>
                        </span>
                        <span class="text-danger">
                            <i class="bi bi-heart me-1"></i>
                            <span class="fw-semibold" id="favorite-count-<?= $server->id ?>"><?= number_format($server->favorites) ?></span>
                            <small class="text-muted"><?= lang('favs') ?></small>
                        </span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="server-actions d-none d-md-block">
                    <?php if (isset($show_management_actions) && $show_management_actions): ?>
                        <!-- Management Actions for My Servers -->
                        <div class="d-flex flex-column gap-2">
                            <a href="<?= url('/edit-server/' . $server->id) ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i><?= lang('server_edit') ?>
                            </a>
                            <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-1"></i><?= lang('show_more') ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Public Actions -->
                        <div class="d-flex flex-column gap-2">
                            <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i><?= lang('view_server_page') ?>
                            </a>
                            <?php if (isLoggedIn()): ?>
                                <?php $isFavorite = isset($user_favorites) && in_array((int)$server->id, $user_favorites); ?>
                                <button class="btn btn-<?= $isFavorite ? 'danger' : 'outline-danger' ?> btn-sm" 
                                        onclick="toggleFavorite(<?= $server->id ?>)"
                                        id="favorite-btn-<?= $server->id ?>">
                                    <i class="bi bi-heart<?= $isFavorite ? '-fill' : '' ?> me-1"></i>
                                    <?= $isFavorite ? lang('remove_from_favorites') : lang('add_to_favorites') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>