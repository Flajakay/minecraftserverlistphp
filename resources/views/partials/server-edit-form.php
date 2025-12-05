<?php
$config = $config ?? [];
$isAdmin = $config['isAdmin'] ?? false;
?>

<form method="POST" <?= !$isAdmin ? 'action="' . url('/edit-server/' . $server->id) . '"' : '' ?> enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
    
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
            
            
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-8">
                <label for="address" class="form-label fw-semibold"><?= lang('server_address') ?></label>
                <div class="input-group">
                    <span class="input-group-text <?= $isAdmin ? 'bg-light' : 'bg-secondary' ?> border-end-0">
                        <i class="bi bi-globe <?= $isAdmin ? 'text-muted' : 'text-white' ?>"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0 <?= $isAdmin ? '' : 'bg-light' ?>" 
                           id="address" 
                           <?= $isAdmin ? 'name="address"' : '' ?>
                           value="<?= htmlspecialchars($server->address) ?>" 
                           <?= $isAdmin ? 'required' : 'readonly' ?>>
                </div>
                <?php if (!$isAdmin): ?>
                    <small class="text-muted"><?= lang('server_address_readonly') ?></small>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <label for="port" class="form-label fw-semibold"><?= lang('server_connection_port') ?></label>
                <div class="input-group">
                    <span class="input-group-text <?= $isAdmin ? 'bg-light' : 'bg-secondary' ?> border-end-0">
                        <i class="bi bi-hash <?= $isAdmin ? 'text-muted' : 'text-white' ?>"></i>
                    </span>
                    <input type="number" 
                           class="form-control border-start-0 ps-0 <?= $isAdmin ? '' : 'bg-light' ?>" 
                           id="port" 
                           <?= $isAdmin ? 'name="port"' : '' ?>
                           value="<?= $server->port ?>" 
                           <?= $isAdmin ? 'min="1" max="65535"' : 'readonly' ?>>
                </div>
                <?php if (!$isAdmin): ?>
                    <small class="text-muted"><?= lang('port_readonly') ?></small>
                <?php endif; ?>
            </div>
			
			<?php include __DIR__ . '/category-selection.php'; ?>
        </div>
    </div>

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
                <?php $selectedCountry = $server->country; ?>
                <?php include __DIR__ . '/country-selector.php'; ?>
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

            <div class="col-md-6">
                <label for="icon" class="form-label fw-semibold"><?= lang('server_icon') ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-box-seam text-muted"></i>
                    </span>
                    <input type="file" 
                           class="form-control border-start-0" 
                           id="icon" 
                           name="icon" 
                           accept="image/*">
                </div>
                <small class="text-muted"><?= lang('server_icon_help') ?></small>
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

        <?php if ($server->icon): ?>
            <div class="mt-3 p-3 bg-light rounded">
                <label class="form-label fw-semibold mb-2"><?= lang('current_icon') ?>:</label>
                <div>
                    <img src="<?= url('/uploads/icons/' . $server->icon) ?>" 
                         alt="<?= lang('current_icon') ?>" 
                         style="width: 64px; height: 64px;" 
                         class="border rounded shadow-sm">
                </div>
            </div>
        <?php endif; ?>
    </div>

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

    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-3 border-top">
        <a href="<?= $isAdmin ? '/admin/servers' : url('/my-servers') ?>" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-2"></i><?= $isAdmin ? lang('back_to_servers') : lang('back_to_my_servers') ?>
        </a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-<?= $isAdmin ? 'check-lg' : 'save' ?> me-2"></i><?= $isAdmin ? lang('submit') : lang('update_server') ?>
        </button>
	</div>
</form>
