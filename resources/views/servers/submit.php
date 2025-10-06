<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-plus-circle text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('titles.submit') ?></h2>
                <p class="text-muted"><?= lang('submit_subtitle') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/submit') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <!-- Server Details Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-server text-primary me-2"></i><?= lang('server_details') ?>
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="address" class="form-label fw-semibold"><?= lang('server_address') ?> *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-globe text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 ps-0" 
                                               id="address" 
                                               name="address" 
                                               value="<?= old('address') ?>" 
                                               placeholder="<?= lang('address_placeholder') ?>"
                                               required>
                                    </div>
                                    <small class="text-muted"><?= lang('address_help') ?></small>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="port" class="form-label fw-semibold"><?= lang('server_connection_port') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-hash text-muted"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control border-start-0 ps-0" 
                                               id="port" 
                                               name="port" 
                                               value="<?= old('port', 25565) ?>" 
                                               min="1" 
                                               max="65535"
                                               placeholder="25565">
                                    </div>
                                    <small class="text-muted"><?= lang('port_default') ?></small>
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
                                               value="<?= old('name') ?>" 
                                               maxlength="64" 
                                               placeholder="<?= lang('server_name_placeholder') ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <?php include __DIR__ . '/../partials/category-selection.php'; ?>
                                
                                <?php include __DIR__ . '/../partials/country-selector.php'; ?>
                        </div>
					</div>
                        <!-- Description Section -->
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
                                          placeholder="<?= lang('description_submit_placeholder') ?>"><?= old('description') ?></textarea>
                                <small class="text-muted"><?= lang('description_submit_help') ?></small>
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
                                               value="<?= old('website') ?>"
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
                                               value="<?= old('youtube_id') ?>"
                                               placeholder="<?= lang('youtube_placeholder') ?>">
                                    </div>
                                    <small class="text-muted"><?= lang('youtube_id_help') ?></small>
                                </div>
                            </div>
                            
                            <div class="mt-3">
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
                                <small class="text-muted"><?= lang('banner_submit_help') ?></small>
                            </div>
                        </div>

                        <!-- Votifier Section -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <h5 class="fw-semibold text-dark mb-0 me-2">
                                    <i class="bi bi-shield-check text-primary me-2"></i><?= lang('votifier_settings') ?>
                                </h5>
                                <span class="badge bg-light text-dark"><?= lang('optional') ?></span>
                            </div>
                            <p class="text-muted mb-3"><?= lang('votifier_submit_info') ?></p>
                            
                            <div class="mb-3">
                                <label for="votifier_public_key" class="form-label fw-semibold"><?= lang('server_votifier_public_key') ?></label>
                                <textarea class="form-control" 
                                          id="votifier_public_key" 
                                          name="votifier_public_key" 
                                          rows="6"
                                          placeholder="<?= lang('votifier_key_placeholder') ?>"><?= old('votifier_public_key') ?></textarea>
                                <small class="text-muted"><?= lang('votifier_key_help') ?></small>
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
                                               value="<?= old('votifier_ip') ?>"
                                               placeholder="<?= lang('votifier_ip_placeholder') ?>">
                                    </div>
                                    <small class="text-muted"><?= lang('votifier_ip_help') ?></small>
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
                                               value="<?= old('votifier_port', 8192) ?>" 
                                               min="1" 
                                               max="65535"
                                               placeholder="8192">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-semibold">
                                <i class="bi bi-plus-circle me-2"></i><?= lang('submit_server') ?>
                            </button>
                            <p class="text-muted mt-2 mb-0 small">
                                <?= lang('submit_agreement') ?>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.submit') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>