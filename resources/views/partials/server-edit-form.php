<?php
$config = $config ?? [];
$isAdmin = $config['isAdmin'] ?? false;
?>

<form method="POST" <?= /** @noinspection PhpUndefinedVariableInspection */
!$isAdmin ? 'action="' . url('/edit-server/' . $server->id) . '"' : '' ?> enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
    
    <div class="mb-4">
        <h5 class="fw-semibold text-dark mb-3">
            <i class="bi bi-server text-primary me-2"></i><?= lang('server_details') ?>
        </h5>
        
        <div class="row g-3">
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
                           value="<?= /** @noinspection PhpUndefinedVariableInspection */
                           htmlspecialchars($server->address) ?>"
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
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-12">
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
            <?php include __DIR__ . '/category-selection.php'; ?>

            <div class="col-12">
                <?php $selectedCountry = $server->country; ?>
                <?php include __DIR__ . '/country-selector.php'; ?>
            </div>
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
                      rows="5" 
                      maxlength="2560" 
                      placeholder="<?= lang('description_placeholder') ?>"><?= $server->description ?></textarea>
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
            </div>
            
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
        </div>

        <div class="mt-3">
            <label for="image" class="form-label fw-semibold"><?= lang('server_banner') ?></label>
            <div class="upload-preview-container border rounded p-3 bg-white position-relative">
                <div id="bannerPreviewBox" class="preview-box mb-3 <?= $server->image ? '' : 'd-none' ?>">
                    <div class="position-relative d-inline-block">
                        <img id="bannerPreview" 
                             src="<?= $server->image ? url('/uploads/banners/' . $server->image) : '' ?>" 
                             alt="Banner preview" 
                             class="img-fluid rounded shadow-sm preview-image-banner">
                        <button type="button" 
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 preview-close-btn-banner" 
                                onclick="clearImagePreview('image', 'bannerPreview', 'bannerPreviewBox')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-image text-muted"></i>
                    </span>
                    <input type="file" 
                           class="form-control border-start-0" 
                           id="image" 
                           name="image" 
                           accept="image/*"
                           onchange="previewImage(this, 'bannerPreview', 'bannerPreviewBox')">
                </div>
                <small class="text-muted d-block mt-2"><?= lang('server_banner_help') ?></small>
            </div>
        </div>

        <div class="mt-3">
            <label for="icon" class="form-label fw-semibold"><?= lang('server_icon') ?></label>
            <div class="upload-preview-container border rounded p-3 bg-white position-relative">
                <div id="iconPreviewBox" class="preview-box mb-3 <?= $server->icon ? '' : 'd-none' ?>">
                    <div class="position-relative d-inline-block">
                        <img id="iconPreview" 
                             src="<?= $server->icon ? url('/uploads/icons/' . $server->icon) : '' ?>" 
                             alt="Icon preview" 
                             class="rounded shadow-sm preview-image-icon">
                        <button type="button" 
                                class="btn btn-sm btn-danger position-absolute preview-close-btn-icon" 
                                onclick="clearImagePreview('icon', 'iconPreview', 'iconPreviewBox')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-box-seam text-muted"></i>
                    </span>
                    <input type="file" 
                           class="form-control border-start-0" 
                           id="icon" 
                           name="icon" 
                           accept="image/*"
                           onchange="previewImage(this, 'iconPreview', 'iconPreviewBox')">
                </div>
                <small class="text-muted d-block mt-2"><?= lang('server_icon_help') ?></small>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <h5 class="fw-semibold text-dark mb-0 me-2">
                <i class="bi bi-shield-check text-primary me-2"></i><?= lang('votifier_settings') ?>
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
                      placeholder="<?= lang('votifier_key_placeholder') ?>"><?= htmlspecialchars($customData['votifier_public_key'] ?? '') ?></textarea>
            <small class="text-muted"><?= lang('server_votifier_public_key_help') ?></small>
        </div>
        
        <div class="row g-3">
            <div class="col-md-8">
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
            
            <div class="col-md-4">
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
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-3 border-top">
        <a href="<?= $isAdmin ? '/admin/servers' : url('/profile/' . auth()->username) ?>" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-2"></i><?= $isAdmin ? lang('back_to_servers') : lang('back_to_my_profile') ?>
        </a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-<?= $isAdmin ? 'check-lg' : 'save' ?> me-2"></i><?= $isAdmin ? lang('submit') : lang('update_server') ?>
        </button>
    </div>
</form>

<script>
function previewImage(input, previewId, previewBoxId) {
    const file = input.files[0];
    const previewImg = document.getElementById(previewId);
    const previewBox = document.getElementById(previewBoxId);
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewBox.classList.remove('d-none');
            
            // Update the info text to show it's a new upload
            const infoText = previewBox.querySelector('.text-muted i').nextSibling;
            if (previewId === 'bannerPreview') {
                infoText.textContent = '<?= lang('new_banner_preview') ?? 'New banner preview' ?>';
            } else {
                infoText.textContent = '<?= lang('new_icon_preview') ?? 'New icon preview' ?>';
            }
        };
        
        reader.readAsDataURL(file);
    }
}

function clearImagePreview(inputId, previewId, previewBoxId) {
    const input = document.getElementById(inputId);
    const previewBox = document.getElementById(previewBoxId);
    
    // Clear the file input
    input.value = '';
    
    // Hide the preview box with animation
    previewBox.style.opacity = '0';
    previewBox.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        previewBox.classList.add('d-none');
        previewBox.style.opacity = '';
        previewBox.style.transform = '';
    }, 300);
}
</script>
