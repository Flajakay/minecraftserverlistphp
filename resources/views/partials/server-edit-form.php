<?php
$config = $config ?? [];
$isAdmin = $config['isAdmin'] ?? false;
?>

<form method="POST" <?= /** @noinspection PhpUndefinedVariableInspection */
!$isAdmin ? 'action="' . url('/edit-server/' . $server->id) . '"' : '' ?> enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
    
    <div class="mb-4">
        <h5 class="fw-semibold text-dark mb-3">
            <i class="bi bi-server text-primary me-2" aria-hidden="true"></i><?= lang('server_details') ?>
        </h5>
        
        <div class="row g-3">
            <div class="col-md-8">
                <label for="address" class="form-label fw-semibold"><?= lang('server_address') ?></label>
                <div class="input-group">
                    <span class="input-group-text <?= $isAdmin ? 'bg-light' : 'bg-secondary' ?> border-end-0">
                        <i class="bi bi-globe <?= $isAdmin ? 'text-muted' : 'text-white' ?>" aria-hidden="true"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0 <?= $isAdmin ? '' : 'bg-light' ?>" 
                           id="address" 
                           <?= $isAdmin ? 'name="address"' : '' ?>
                           value="<?= /** @noinspection PhpUndefinedVariableInspection */
                           sanitize($server->address) ?>"
                           autocomplete="off"
                           <?= $isAdmin ? 'required' : 'readonly' ?>>
                </div>
                <?php if (!$isAdmin): ?>
                        <small class="text-muted" id="addressHelp"><?= lang('server_address_readonly') ?></small>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <label for="port" class="form-label fw-semibold"><?= lang('server_connection_port') ?></label>
                <div class="input-group">
                    <span class="input-group-text <?= $isAdmin ? 'bg-light' : 'bg-secondary' ?> border-end-0">
                        <i class="bi bi-hash <?= $isAdmin ? 'text-muted' : 'text-white' ?>" aria-hidden="true"></i>
                    </span>
                    <input type="number" 
                           class="form-control border-start-0 ps-0 <?= $isAdmin ? '' : 'bg-light' ?>" 
                           id="port" 
                           <?= $isAdmin ? 'name="port"' : '' ?>
                           value="<?= $server->port ?>" 
                           autocomplete="off"
                           <?= $isAdmin ? 'min="1" max="65535"' : 'readonly' ?>>
                </div>
                <?php if (!$isAdmin): ?>
                        <small class="text-muted" id="portHelp"><?= lang('port_readonly') ?></small>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-12">
                <label for="name" class="form-label fw-semibold"><?= lang('server_name') ?> *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-card-text text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           id="name" 
                           name="name" 
                           value="<?= sanitize($server->name) ?>" 
                           autocomplete="off"
                           required 
                           maxlength="64">
                </div>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <?php include __DIR__ . '/category-selection.php'; ?>

            <div class="col-12">
                <?php $selectedCountry = $server->country; ?>
                <?php include __DIR__ . '/country-selector.php'; ?>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h5 class="fw-semibold text-dark mb-3">
            <i class="bi bi-file-text text-primary me-2" aria-hidden="true"></i><?= lang('description_media') ?>
        </h5>
        
        <div class="mb-3">
            <label for="description" class="form-label fw-semibold"><?= lang('server_description') ?></label>
            <textarea class="form-control" 
                      id="description" 
                      name="description" 
                      rows="5" 
                      maxlength="2560" 
                      aria-describedby="descriptionHelp"
                      placeholder="<?= lang('description_placeholder') ?>"><?= $server->description ?></textarea>
            <small class="text-muted" id="descriptionHelp"><?= lang('description_help') ?></small>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="website" class="form-label fw-semibold"><?= lang('website') ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-link text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="url" 
                           class="form-control border-start-0 ps-0" 
                           id="website" 
                           name="website" 
                           value="<?= sanitize($server->website) ?>" 
                           maxlength="128"
                           autocomplete="url"
                           placeholder="https://yourserver.com">
                </div>
            </div>
            
            <div class="col-md-6">
                <label for="youtube_id" class="form-label fw-semibold"><?= lang('youtube_video') ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-youtube text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           id="youtube_id" 
                           name="youtube_id" 
                           value="<?= sanitize($server->youtube_id) ?>" 
                           maxlength="32"
                           aria-describedby="youtubeHelp"
                           placeholder="dQw4w9WgXcQ">
                </div>
                <small class="text-muted" id="youtubeHelp"><?= lang('youtube_help') ?></small>
            </div>
        </div>

        <div class="mt-3">
            <label for="image" class="form-label fw-semibold"><?= lang('server_banner') ?></label>
            <div class="upload-preview-container border rounded p-3 bg-white position-relative">
                <div id="bannerPreviewBox" class="preview-box mb-3 <?= $server->image ? '' : 'd-none' ?>">
                    <div class="position-relative d-inline-block">
                        <img id="bannerPreview" 
                             src="<?= $server->image ? url('/uploads/banners/' . $server->image) : '' ?>" 
                             alt="<?= lang('server_banner') ?>" 
                             class="img-fluid rounded shadow-sm preview-image-banner">
                        <button type="button" 
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 preview-close-btn-banner" 
                                aria-label="<?= lang('delete') ?>"
                                onclick="clearImagePreview('image', 'bannerPreview', 'bannerPreviewBox')">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-image text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="file" 
                           class="form-control border-start-0" 
                           id="image" 
                           name="image" 
                           accept="image/*"
                           aria-describedby="bannerHelp"
                           onchange="previewImage(this, 'bannerPreview', 'bannerPreviewBox')">
                </div>
                <small class="text-muted d-block mt-2" id="bannerHelp"><?= lang('server_banner_help') ?></small>
            </div>
        </div>

        <div class="mt-3">
            <label for="icon" class="form-label fw-semibold"><?= lang('server_icon') ?></label>
            <div class="upload-preview-container border rounded p-3 bg-white position-relative">
                <div id="iconPreviewBox" class="preview-box mb-3 <?= $server->icon ? '' : 'd-none' ?>">
                    <div class="position-relative d-inline-block">
                        <img id="iconPreview" 
                             src="<?= $server->icon ? url('/uploads/icons/' . $server->icon) : '' ?>" 
                             alt="<?= lang('server_icon') ?>" 
                             class="rounded shadow-sm preview-image-icon">
                        <button type="button" 
                                class="btn btn-sm btn-danger position-absolute preview-close-btn-icon" 
                                aria-label="<?= lang('delete') ?>"
                                onclick="clearImagePreview('icon', 'iconPreview', 'iconPreviewBox')">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-box-seam text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="file" 
                           class="form-control border-start-0" 
                           id="icon" 
                           name="icon" 
                           accept="image/*"
                           aria-describedby="iconHelp"
                           onchange="previewImage(this, 'iconPreview', 'iconPreviewBox')">
                </div>
                <small class="text-muted d-block mt-2" id="iconHelp"><?= lang('server_icon_help') ?></small>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <h5 class="fw-semibold text-dark mb-0 me-2">
                <i class="bi bi-shield-check text-primary me-2" aria-hidden="true"></i><?= lang('votifier_settings') ?>
            </h5>
            <span class="badge bg-light text-dark"><?= lang('optional') ?></span>
        </div>
        
        <?php
        $customData = json_decode($server->custom_data ?? '{}', true);
        ?>
        
        <div class="mb-3">
            <label for="votifier_public_key" class="form-label fw-semibold"><?= lang('server_votifier_public_key') ?></label>
            <textarea class="form-control" 
                      id="votifier_public_key" 
                      name="votifier_public_key" 
                      rows="6"
                      aria-describedby="votifierKeyHelp"
                      placeholder="<?= lang('votifier_key_placeholder') ?>"><?= sanitize($customData['votifier_public_key'] ?? '') ?></textarea>
            <small class="text-muted" id="votifierKeyHelp"><?= lang('server_votifier_public_key_help') ?></small>
        </div>
        
        <div class="row g-3">
            <div class="col-md-8">
                <label for="votifier_ip" class="form-label fw-semibold"><?= lang('server_votifier_ip') ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-globe text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           id="votifier_ip" 
                           name="votifier_ip" 
                           value="<?= sanitize($customData['votifier_ip'] ?? '') ?>"
                           aria-describedby="votifierIpHelp"
                           placeholder="<?= lang('votifier_ip_placeholder') ?>">
                </div>
                <small class="text-muted" id="votifierIpHelp"><?= lang('server_votifier_ip_help') ?></small>
            </div>
            
            <div class="col-md-4">
                <label for="votifier_port" class="form-label fw-semibold"><?= lang('server_votifier_port') ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-hash text-muted" aria-hidden="true"></i>
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
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-3 border-top">
        <a href="<?= $isAdmin ? '/admin/servers' : url('/profile/' . auth()->username) ?>" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i><?= $isAdmin ? lang('back_to_servers') : lang('back_to_my_profile') ?>
        </a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-<?= $isAdmin ? 'check-lg' : 'save' ?> me-2" aria-hidden="true"></i><?= $isAdmin ? lang('submit') : lang('update_server') ?>
        </button>
    </div>
</form>

<script src="<?= asset('js/server-edit-form.js') ?>" defer></script>
