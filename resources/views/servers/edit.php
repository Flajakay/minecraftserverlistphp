<?php ob_start(); ?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3">
                    <i class="bi bi-pencil-square text-primary" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <h2 class="h4 fw-bold text-dark mb-1"><?= lang('edit_server') ?></h2>
                    <p class="text-muted mb-0"><?= htmlspecialchars($server->name) ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/edit-server/' . $server->id) ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <!-- Basic Details Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-server text-primary me-2"></i><?= lang('basic_details') ?>
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold"><?= lang('server_name') ?> *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-card-text text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="name" 
                                               name="name" 
                                               value="<?= htmlspecialchars($server->name) ?>" 
                                               required 
                                               maxlength="64">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label fw-semibold"><?= lang('server_category') ?> *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-tags text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="category_id" name="category_id" required>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category->id ?>" <?= $category->id == $server->category_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-8">
                                    <label for="address" class="form-label fw-semibold"><?= lang('server_address') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-secondary border-end-0">
                                            <i class="bi bi-globe text-white"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0 bg-light" 
                                               id="address" 
                                               value="<?= htmlspecialchars($server->address) ?>" 
                                               readonly>
                                    </div>
                                    <small class="text-muted"><?= lang('server_address_readonly') ?></small>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="port" class="form-label fw-semibold"><?= lang('server_connection_port') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-secondary border-end-0">
                                            <i class="bi bi-hash text-white"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control border-start-0 ps-0 bg-light" 
                                               id="port" 
                                               value="<?= $server->port ?>" 
                                               readonly>
                                    </div>
                                    <small class="text-muted"><?= lang('port_readonly') ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Media Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-file-text text-primary me-2"></i><?= lang('description_media') ?>
                            </h5>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold"><?= lang('server_description') ?></label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="6" 
                                          maxlength="2560" 
                                          placeholder="<?= lang('description_placeholder') ?>"><?= htmlspecialchars($server->description) ?></textarea>
                                <small class="text-muted"><?= lang('description_help') ?></small>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="website" class="form-label fw-semibold"><?= lang('website') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-link text-muted"></i>
                                        </span>
                                        <input type="url" 
                                               class="form-control border-start-0 ps-0" 
                                               id="website" 
                                               name="website" 
                                               value="<?= htmlspecialchars($server->website) ?>" 
                                               maxlength="128"
                                               placeholder="https://yourserver.com">
                                    </div>
                                    <small class="text-muted"><?= lang('website_help') ?></small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="country" class="form-label fw-semibold"><?= lang('server_country') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-geo-alt text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="country" name="country">
                                            <?php foreach ($countries as $code => $name): ?>
                                                <option value="<?= $code ?>" <?= $code == $server->country ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label for="youtube_id" class="form-label fw-semibold"><?= lang('youtube_video') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-youtube text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="youtube_id" 
                                               name="youtube_id" 
                                               value="<?= htmlspecialchars($server->youtube_id) ?>" 
                                               maxlength="32"
                                               placeholder="dQw4w9WgXcQ">
                                    </div>
                                    <small class="text-muted"><?= lang('youtube_help') ?></small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="image" class="form-label fw-semibold"><?= lang('server_banner') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-image text-muted"></i>
                                        </span>
                                        <input type="file" 
                                               class="form-control border-start-0" 
                                               id="image" 
                                               name="image" 
                                               accept="image/*">
                                    </div>
                                    <small class="text-muted"><?= lang('server_banner_help') ?></small>
                                </div>
                            </div>
                            
                            <?php if ($server->image): ?>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <label class="form-label fw-semibold mb-2"><?= lang('current_banner') ?>:</label>
                                    <div>
                                        <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                                             alt="<?= lang('current_banner') ?>" 
                                             style="max-width: 300px;" 
                                             class="border rounded shadow-sm">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Votifier Section -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <h5 class="fw-semibold text-dark mb-0 me-2">
                                    <i class="bi bi-shield-check text-primary me-2"></i><?= lang('votifier_settings') ?>
                                </h5>
                                <span class="badge bg-light text-dark"><?= lang('optional') ?></span>
                            </div>
                            
                            <div class="alert alert-info border-0 mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <?= lang('votifier_info') ?>
                            </div>
                            
                            <?php 
                            $customData = json_decode($server->custom_data ?? '{}', true);
                            ?>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="votifier_ip" class="form-label fw-semibold"><?= lang('server_votifier_ip') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-globe text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="votifier_ip" 
                                               name="votifier_ip" 
                                               value="<?= htmlspecialchars($customData['votifier_ip'] ?? '') ?>"
                                               placeholder="<?= lang('votifier_ip_placeholder') ?>">
                                    </div>
                                    <small class="text-muted"><?= lang('server_votifier_ip_help') ?></small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="votifier_port" class="form-label fw-semibold"><?= lang('server_votifier_port') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-hash text-muted"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control border-start-0 ps-0" 
                                               id="votifier_port" 
                                               name="votifier_port" 
                                               value="<?= $customData['votifier_port'] ?? 8192 ?>" 
                                               min="1" 
                                               max="65535"
                                               placeholder="8192">
                                    </div>
                                    <small class="text-muted"><?= lang('votifier_port_help') ?></small>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label for="votifier_public_key" class="form-label fw-semibold"><?= lang('server_votifier_public_key') ?></label>
                                <textarea class="form-control" 
                                          id="votifier_public_key" 
                                          name="votifier_public_key" 
                                          rows="8"
                                          placeholder="<?= lang('votifier_key_placeholder') ?>"><?= htmlspecialchars($customData['votifier_public_key'] ?? '') ?></textarea>
                                <small class="text-muted"><?= lang('server_votifier_public_key_help') ?></small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-3 border-top">
                            <a href="<?= url('/my-servers') ?>" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-2"></i><?= lang('back_to_my_servers') ?>
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-save me-2"></i><?= lang('update_server') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Server Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-activity text-primary me-2"></i><?= lang('server_status') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> me-2">
                            <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                            <?= $server->status ? lang('active') : lang('inactive') ?>
                        </span>
                        <?php if ($server->status): ?>
                            <span class="text-muted"><?= $server->players ?>/<?= $server->max_players ?> <?= lang('players') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <div class="h5 text-success mb-0"><?= number_format($server->votes) ?></div>
                                <small class="text-muted"><?= lang('votes') ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <div class="h5 text-danger mb-0"><?= number_format($server->favorites) ?></div>
                                <small class="text-muted"><?= lang('favorites') ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <div class="h5 text-<?= $server->active ? 'success' : 'warning' ?> mb-0">
                                    <?= $server->active ? lang('active') : lang('inactive') ?>
                                </div>
                                <small class="text-muted"><?= lang('server_status') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($server->last_check): ?>
                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i><?= lang('last_checked') ?>: <?= timeAgo($server->last_check) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Server Settings Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-gear text-primary me-2"></i><?= lang('server_settings') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                        <span class="fw-semibold"><?= lang('visibility') ?></span>
                        <span class="badge bg-<?= $server->private ? 'warning' : 'success' ?>">
                            <i class="bi bi-<?= $server->private ? 'lock' : 'unlock' ?> me-1"></i>
                            <?= $server->private ? lang('private') : lang('public') ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                        <span class="fw-semibold"><?= lang('premium_status') ?></span>
                        <span class="badge bg-<?= $server->highlight ? 'warning' : 'secondary' ?>">
                            <i class="bi bi-<?= $server->highlight ? 'star-fill' : 'star' ?> me-1"></i>
                            <?= $server->highlight ? lang('premium') : lang('standard') ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                        <span class="fw-semibold"><?= lang('created') ?></span>
                        <small class="text-muted"><?= date('M j, Y', strtotime($server->created_at)) ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i><?= lang('quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-eye me-2"></i><?= lang('view_server_page') ?>
                    </a>
                    
                    <?php if ($server->private): ?>
                        <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <input type="hidden" name="action" value="make_public">
                            <button type="submit" 
                                    class="btn btn-outline-success w-100 mb-2" 
                                    onclick="return confirm('<?= lang('confirm_make_public') ?>')">
                                <i class="bi bi-unlock me-2"></i><?= lang('make_public') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <input type="hidden" name="action" value="make_private">
                            <button type="submit" 
                                    class="btn btn-outline-warning w-100 mb-2" 
                                    onclick="return confirm('<?= lang('confirm_make_private') ?>')">
                                <i class="bi bi-lock me-2"></i><?= lang('make_private') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" 
                                class="btn btn-outline-danger w-100" 
                                onclick="return confirm('<?= lang('confirm_delete_server') ?>')">
                            <i class="bi bi-trash me-2"></i><?= lang('delete_server') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.edit_server') . ': ' . htmlspecialchars($server->name) . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
