<?php ob_start(); ?>

<div class="container py-5">
    <div class="d-flex align-items-center mb-4">
        <div class="me-3">
            <i class="bi bi-gear text-primary" style="font-size: 2rem;"></i>
        </div>
        <div>
            <h2 class="h4 fw-bold text-dark mb-1"><?= lang('admin_settings') ?></h2>
            <p class="text-muted mb-0"><?= lang('configure_platform') ?></p>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/settings') ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
        
        <div class="row g-4">
            <!-- Basic Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-house text-primary me-2"></i><?= lang('basic_settings') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold"><?= lang('site_title') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-type text-muted"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="title" 
                                       name="title" 
                                       value="<?= htmlspecialchars($settings->title) ?>" 
                                       placeholder="<?= lang('minecraft_server_list') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="url" class="form-label fw-semibold"><?= lang('site_url') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-link text-muted"></i>
                                </span>
                                <input type="url" 
                                       class="form-control border-start-0 ps-0" 
                                       id="url" 
                                       name="url" 
                                       value="<?= htmlspecialchars($settings->url) ?>" 
                                       placeholder="<?= lang('site_url_placeholder') ?>"
                                       required>
                            </div>
                            <small class="text-muted"><?= lang('include_trailing_slash') ?></small>
                        </div>
                        
                        <div class="mb-0">
                            <label for="contact_email" class="form-label fw-semibold"><?= lang('contact_email') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 ps-0" 
                                       id="contact_email" 
                                       name="contact_email" 
                                       value="<?= htmlspecialchars($settings->contact_email) ?>" 
                                       placeholder="<?= lang('contact_email_placeholder') ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-server text-primary me-2"></i><?= lang('server_settings') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="servers_pagination" class="form-label fw-semibold"><?= lang('servers_per_page') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-list text-muted"></i>
                                </span>
                                <input type="number" 
                                       class="form-control border-start-0 ps-0" 
                                       id="servers_pagination" 
                                       name="servers_pagination" 
                                       value="<?= $settings->servers_pagination ?>" 
                                       min="5" 
                                       max="50"
                                       placeholder="20">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="display_offline_servers" 
                                       name="display_offline_servers" 
                                       <?= $settings->display_offline_servers ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="display_offline_servers">
                                    <?= lang('display_offline_servers') ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="new_servers_visibility" 
                                       name="new_servers_visibility" 
                                       <?= $settings->new_servers_visibility ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="new_servers_visibility">
                                    <?= lang('new_servers_public_default') ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="email_confirmation" 
                                       name="email_confirmation" 
                                       <?= $settings->email_confirmation ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="email_confirmation">
                                    <?= lang('require_email_confirmation') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMTP Settings -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="fw-semibold mb-1">
                    <i class="bi bi-envelope-at text-primary me-2"></i><?= lang('smtp_settings') ?>
                </h5>
                <p class="text-muted mb-0 small"><?= lang('smtp_settings_help') ?></p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="smtp_host" class="form-label fw-semibold"><?= lang('settings_smtp_host') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-server text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0" 
                                   id="smtp_host" 
                                   name="smtp_host" 
                                   value="<?= htmlspecialchars($settings->smtp_host ?? '') ?>" 
                                   placeholder="<?= lang('smtp_gmail_example') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="smtp_port" class="form-label fw-semibold"><?= lang('settings_smtp_port') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-hash text-muted"></i>
                            </span>
                            <input type="number" 
                                   class="form-control border-start-0 ps-0" 
                                   id="smtp_port" 
                                   name="smtp_port" 
                                   value="<?= htmlspecialchars($settings->smtp_port ?? '') ?>" 
                                   placeholder="587">
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="smtp_user" class="form-label fw-semibold"><?= lang('settings_smtp_user') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0" 
                                   id="smtp_user" 
                                   name="smtp_user" 
                                   value="<?= htmlspecialchars($settings->smtp_user ?? '') ?>"
                                   placeholder="<?= lang('smtp_email_example') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="smtp_pass" class="form-label fw-semibold"><?= lang('settings_smtp_pass') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0" 
                                   id="smtp_pass" 
                                   name="smtp_pass" 
                                   value="<?= htmlspecialchars($settings->smtp_pass ?? '') ?>"
                                   placeholder="<?= lang('app_password_placeholder') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="smtp_secure" class="form-label fw-semibold"><?= lang('settings_smtp_secure') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-shield-lock text-muted"></i>
                            </span>
                            <select class="form-select border-start-0" id="smtp_secure" name="smtp_secure">
                                <option value=""><?= lang('none') ?></option>
                                <option value="tls" <?= ($settings->smtp_secure ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($settings->smtp_secure ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <!-- Danger Zone -->
            <div class="col-lg-6">
                <div class="card border-danger shadow-sm h-100">
                    <div class="card-header bg-danger text-white py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= lang('danger_zone') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-danger fw-semibold"><?= lang('reset_votes') ?></h6>
                        <p class="text-muted mb-3"><?= lang('reset_votes_warning') ?></p>
                        <button type="button" 
                                class="btn btn-outline-danger"
                                onclick="if(confirm('<?= lang('confirm_reset_votes') ?>')) { 
                                    fetch('<?= url('/admin/reset-votes') ?>', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                        body: 'csrf_token=<?= csrf() ?>'
                                    }).then(() => location.reload());
                                }">
                            <i class="bi bi-arrow-clockwise me-2"></i><?= lang('reset_votes') ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-lightning text-primary me-2"></i><?= lang('quick_links') ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="<?= url('/admin/users') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-people text-primary me-3"></i>
                                <span class="fw-semibold"><?= lang('users_management') ?></span>
                            </a>
                            <a href="<?= url('/admin/servers') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-server text-success me-3"></i>
                                <span class="fw-semibold"><?= lang('servers_management') ?></span>
                            </a>
                            <a href="<?= url('/admin/categories') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-tags text-warning me-3"></i>
                                <span class="fw-semibold"><?= lang('categories_management') ?></span>
                            </a>
                            <a href="<?= url('/admin/reports') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-flag text-danger me-3"></i>
                                <span class="fw-semibold"><?= lang('reports_management') ?></span>
                            </a>
                            <a href="<?= url('/admin/blog-posts') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-journal-text text-info me-3"></i>
                                <span class="fw-semibold"><?= lang('blog_posts_management') ?></span>
                            </a>
                            <a href="<?= url('/admin/audit') ?>" class="list-group-item list-group-item-action border-0 py-3">
                                <i class="bi bi-clock-history text-secondary me-3"></i>
                                <span class="fw-semibold"><?= lang('audit_logs') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="text-center mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-semibold">
                <i class="bi bi-save me-2"></i><?= lang('save_settings') ?>
            </button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.website_settings') . ' - ' . lang('admin_panel'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
