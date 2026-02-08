<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-person-gear text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('titles.settings') ?></h2>
                <p class="text-muted"><?= lang('profile_settings_subtitle') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <ul class="nav nav-pills justify-content-center" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-4" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="bi bi-person me-2"></i><?= lang('menu.profile_settings') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4" id="design-tab" data-bs-toggle="tab" data-bs-target="#design" type="button" role="tab">
                                <i class="bi bi-palette me-2"></i><?= lang('menu.design_settings') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link px-4" href="<?= url('/settings/password') ?>">
                                <i class="bi bi-key me-2"></i><?= lang('menu.change_password') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Profile Settings Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form method="POST" action="<?= url('/settings/profile') ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                
                                <!-- Basic Information Section -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-person text-primary me-2"></i><?= lang('basic_information') ?>
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-semibold"><?= lang('name') ?> *</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-card-text text-muted"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control border-start-0 ps-0" 
                                                       id="name" 
                                                       name="name" 
                                                       value="<?= /** @noinspection PhpUndefinedVariableInspection */
                                                       sanitize($user->name) ?>"
                                                       placeholder="<?= lang('display_name_placeholder') ?>"
                                                       required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="username" class="form-label fw-semibold"><?= lang('username') ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-secondary border-end-0">
                                                    <i class="bi bi-at text-white"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control border-start-0 ps-0 bg-light" 
                                                       value="<?= sanitize($user->username) ?>" 
                                                       readonly>
                                            </div>
                                            <small class="text-muted"><?= lang('username_readonly') ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label for="email" class="form-label fw-semibold"><?= lang('email') ?> *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-envelope text-muted"></i>
                                            </span>
                                            <input type="email" 
                                                   class="form-control border-start-0 ps-0" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= sanitize($user->email) ?>" 
                                                   placeholder="<?= lang('email_placeholder') ?>"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label for="about" class="form-label fw-semibold"><?= lang('about') ?></label>
                                        <textarea class="form-control" 
                                                  id="about" 
                                                  name="about" 
                                                  rows="3" 
                                                  maxlength="128"
                                                  placeholder="<?= lang('about_placeholder') ?>"><?= sanitize($user->about) ?></textarea>
                                        <small class="text-muted"><?= lang('about_help') ?></small>
                                    </div>
                                </div>

                                <!-- Contact Information Section -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-globe text-primary me-2"></i><?= lang('contact_information') ?>
                                    </h5>
                                    
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
                                                       value="<?= sanitize($user->website) ?>"
                                                       placeholder="<?= lang('website_placeholder') ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="location" class="form-label fw-semibold"><?= lang('location') ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-geo-alt text-muted"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control border-start-0 ps-0" 
                                                       id="location" 
                                                       name="location" 
                                                       value="<?= sanitize($user->location) ?>" 
                                                       maxlength="64"
                                                       placeholder="<?= lang('location_placeholder') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Social Media Section -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-share text-primary me-2"></i><?= lang('social_media') ?>
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="facebook" class="form-label fw-semibold"><?= lang('facebook') ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-facebook text-muted"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control border-start-0 ps-0" 
                                                       id="facebook" 
                                                       name="facebook" 
                                                       value="<?= sanitize($user->facebook) ?>" 
                                                       placeholder="<?= lang('social_placeholder') ?>">
                                            </div>
                                            <small class="text-muted"><?= lang('social_help') ?></small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="twitter" class="form-label fw-semibold"><?= lang('twitter') ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-twitter text-muted"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control border-start-0 ps-0" 
                                                       id="twitter" 
                                                       name="twitter" 
                                                       value="<?= sanitize($user->twitter) ?>" 
                                                       placeholder="<?= lang('social_placeholder') ?>">
                                            </div>
                                            <small class="text-muted"><?= lang('social_help') ?></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Privacy Settings Section -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-shield-check text-primary me-2"></i><?= lang('privacy_settings') ?>
                                    </h5>
                                    
                                    <div class="form-check p-3 bg-light rounded">
                                        <input class="form-check-input" type="checkbox" id="private" name="private" 
                                               <?= $user->private ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="private">
                                            <i class="bi bi-lock me-2"></i><?= lang('private_profile') ?>
                                        </label>
                                        <div class="text-muted small mt-1">
                                            <?= lang('private_profile_help') ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                                        <i class="bi bi-check-lg me-2"></i><?= lang('submit') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Design Settings Tab -->
                        <div class="tab-pane fade" id="design" role="tabpanel">
                            <form method="POST" action="<?= url('/settings/profile') ?>" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                
                                <!-- Profile Images Section -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold text-dark mb-3">
                                        <i class="bi bi-image text-primary me-2"></i><?= lang('profile_images') ?>
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="avatar" class="form-label fw-semibold"><?= lang('upload_avatar') ?></label>
                                            
                                            <div class="text-center mb-3 p-4 bg-light rounded">
                                                <?php if ($user->avatar): ?>
                                                    <img src="<?= url('public/uploads/avatars/' . $user->avatar) ?>" 
                                                         class="rounded-circle shadow-sm" 
                                                         width="120" 
                                                         height="120" 
                                                         alt="<?= lang('current_avatar') ?>"
                                                         style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" 
                                                         style="width: 120px; height: 120px;">
                                                        <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mt-3">
                                                    <small class="text-muted"><?= lang('current_avatar') ?></small>
                                                </div>
                                            </div>
                                            
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-person-circle text-muted"></i>
                                                </span>
                                                <input type="file" 
                                                       class="form-control border-start-0" 
                                                       id="avatar" 
                                                       name="avatar" 
                                                       accept="image/*">
                                            </div>
                                            <small class="text-muted"><?= lang('avatar_help') ?></small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="cover" class="form-label fw-semibold"><?= lang('upload_cover') ?></label>
                                            
                                            <div class="mb-3 p-3 bg-light rounded">
                                                <?php if ($user->cover): ?>
                                                    <img src="<?= url('public/uploads/covers/' . $user->cover) ?>" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="max-height: 120px; width: 100%; object-fit: cover;" 
                                                         alt="<?= lang('current_cover') ?>">
                                                <?php else: ?>
                                                    <div class="bg-secondary border rounded d-flex align-items-center justify-content-center" 
                                                         style="height: 120px;">
                                                        <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="text-center mt-2">
                                                    <small class="text-muted"><?= lang('current_cover') ?></small>
                                                </div>
                                            </div>
                                            
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-image text-muted"></i>
                                                </span>
                                                <input type="file" 
                                                       class="form-control border-start-0" 
                                                       id="cover" 
                                                       name="cover" 
                                                       accept="image/*">
                                            </div>
                                            <small class="text-muted"><?= lang('cover_help') ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                                        <i class="bi bi-upload me-2"></i><?= lang('submit') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.user_profile') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>