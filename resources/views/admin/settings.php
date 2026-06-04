<?php
ob_start();
/** @var object $settings */
/** @var array $rateLimit */
$rateLimitRules = [
    'auth' => ['label' => 'Auth', 'routes' => '/login, /register, /lost-password, /reset-password'],
    'voting' => ['label' => 'Voting', 'routes' => '/vote, /vote/*'],
    'server_actions' => ['label' => 'Server Actions', 'routes' => '/submit, /edit-server, /server-claim'],
    'contact' => ['label' => 'Contact & Reports', 'routes' => '/contact, /report'],
    'payments' => ['label' => 'Payments', 'routes' => '/paypal/*'],
    'api' => ['label' => 'API', 'routes' => '/api/*, /comment/load-more'],
    'admin' => ['label' => 'Admin', 'routes' => '/admin/*'],
    'general' => ['label' => 'General', 'routes' => 'All other pages'],
];
?>

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

    <form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                    <i class="bi bi-house me-1"></i><?= lang('tab_general') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtp" type="button" role="tab" aria-controls="smtp" aria-selected="false">
                    <i class="bi bi-envelope-at me-1"></i><?= lang('tab_smtp') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="false">
                    <i class="bi bi-credit-card me-1"></i><?= lang('tab_payments') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance" aria-selected="false">
                    <i class="bi bi-palette me-1"></i><?= lang('tab_appearance') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                    <i class="bi bi-shield-check me-1"></i><?= lang('tab_security') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="danger-tab" data-bs-toggle="tab" data-bs-target="#danger" type="button" role="tab" aria-controls="danger" aria-selected="false">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i><span class="text-danger"><?= lang('tab_danger_zone') ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content bg-white border border-top-0 rounded-bottom p-4" id="settingsTabsContent">

            <!-- ==================== General ==================== -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">

                <div class="row g-4">
                    <div class="col-lg-6">
                        <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                            <i class="bi bi-house text-primary me-2"></i><?= lang('basic_settings') ?>
                        </h5>
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold"><?= lang('site_title') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-type text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="title" name="title" value="<?= sanitize($settings->title) ?>" placeholder="<?= lang('minecraft_server_list') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label fw-semibold"><?= lang('site_url') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-link text-muted"></i>
                                </span>
                                <input type="url" class="form-control border-start-0 ps-0" id="url" name="url" value="<?= sanitize($settings->url) ?>" placeholder="<?= lang('site_url_placeholder') ?>" required>
                            </div>
                            <small class="text-muted"><?= lang('include_trailing_slash') ?></small>
                        </div>
                        <div class="mb-0">
                            <label for="contact_email" class="form-label fw-semibold"><?= lang('contact_email') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0" id="contact_email" name="contact_email" value="<?= sanitize($settings->contact_email) ?>" placeholder="<?= lang('contact_email_placeholder') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                            <i class="bi bi-server text-primary me-2"></i><?= lang('server_settings') ?>
                        </h5>
                        <div class="mb-3">
                            <label for="servers_pagination" class="form-label fw-semibold"><?= lang('servers_per_page') ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-list text-muted"></i>
                                </span>
                                <input type="number" class="form-control border-start-0 ps-0" id="servers_pagination" name="servers_pagination" value="<?= $settings->servers_pagination ?>" min="5" max="50" placeholder="20">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="display_offline_servers" name="display_offline_servers" <?= $settings->display_offline_servers ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="display_offline_servers"><?= lang('display_offline_servers') ?></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="new_servers_visibility" name="new_servers_visibility" <?= $settings->new_servers_visibility ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="new_servers_visibility"><?= lang('new_servers_public_default') ?></label>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_confirmation" name="email_confirmation" <?= $settings->email_confirmation ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="email_confirmation"><?= lang('require_email_confirmation') ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                    <i class="bi bi-controller text-primary me-2"></i><?= lang('protocol_settings') ?>
                </h5>
                <p class="text-muted small mb-3"><?= lang('protocol_settings_help') ?></p>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="minecraft_java_enabled" name="minecraft_java_enabled" <?= ($settings->minecraft_java_enabled ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="minecraft_java_enabled"><?= lang('protocol_minecraft_java') ?></label>
                    </div>
                    <small class="text-muted ms-4">Default port: 25565</small>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="steam_a2s_enabled" name="steam_a2s_enabled" <?= ($settings->steam_a2s_enabled ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="steam_a2s_enabled"><?= lang('protocol_steam_a2s') ?></label>
                    </div>
                    <small class="text-muted ms-4">Default port: 27015</small>
                </div>
                <div class="mb-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="minecraft_bedrock_enabled" name="minecraft_bedrock_enabled" <?= ($settings->minecraft_bedrock_enabled ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="minecraft_bedrock_enabled"><?= lang('protocol_minecraft_bedrock') ?></label>
                    </div>
                    <small class="text-muted ms-4">Default port: 19132</small>
                </div>

                <hr class="my-4">

                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                    <i class="bi bi-image text-primary me-2"></i><?= lang('favicon_settings') ?>
                </h5>
                <p class="text-muted small mb-3"><?= lang('favicon_settings_help') ?></p>
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-start">
                    <?php if (!empty($settings->favicon_source)): ?>
                        <div class="border rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 80px; height: 80px;">
                            <img src="<?= asset('favicons/apple-touch-icon.png') ?>?v=<?= (int)($settings->favicon_version ?? 1) ?>" alt="<?= lang('current_favicon') ?>" class="img-fluid" style="max-width: 64px; max-height: 64px; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <label for="favicon" class="form-label fw-semibold mb-1"><?= lang('favicon_source_image') ?></label>
                        <input type="file" class="form-control" id="favicon" name="favicon" accept="image/jpeg,image/png,image/gif" aria-describedby="faviconHelp">
                        <small class="text-muted d-block mt-2" id="faviconHelp"><?= lang('favicon_upload_help') ?></small>
                    </div>
                </div>
            </div>

            <!-- ==================== SMTP ==================== -->
            <div class="tab-pane fade" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                <h5 class="fw-semibold mb-1">
                    <i class="bi bi-envelope-at text-primary me-2"></i><?= lang('smtp_settings') ?>
                </h5>
                <p class="text-muted small mb-4"><?= lang('smtp_settings_help') ?></p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="smtp_host" class="form-label fw-semibold"><?= lang('settings_smtp_host') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-server text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="smtp_host" name="smtp_host" value="<?= sanitize($settings->smtp_host ?? '') ?>" placeholder="<?= lang('smtp_gmail_example') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="smtp_port" class="form-label fw-semibold"><?= lang('settings_smtp_port') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-hash text-muted"></i></span>
                            <input type="number" class="form-control border-start-0 ps-0" id="smtp_port" name="smtp_port" value="<?= sanitize($settings->smtp_port ?? '') ?>" placeholder="587">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="smtp_user" class="form-label fw-semibold"><?= lang('settings_smtp_user') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="smtp_user" name="smtp_user" value="<?= sanitize($settings->smtp_user ?? '') ?>" placeholder="<?= lang('smtp_email_example') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="smtp_pass" class="form-label fw-semibold"><?= lang('settings_smtp_pass') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="smtp_pass" name="smtp_pass" value="<?= sanitize($settings->smtp_pass ?? '') ?>" placeholder="<?= lang('app_password_placeholder') ?>">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="smtp_secure" class="form-label fw-semibold"><?= lang('settings_smtp_secure') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                            <select class="form-select border-start-0" id="smtp_secure" name="smtp_secure">
                                <option value=""><?= lang('none') ?></option>
                                <option value="tls" <?= ($settings->smtp_secure ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($settings->smtp_secure ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== Payments ==================== -->
            <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                <h5 class="fw-semibold mb-1">
                    <i class="bi bi-credit-card text-primary me-2"></i><?= lang('paypal_settings') ?>
                </h5>
                <p class="text-muted small mb-4"><?= lang('paypal_settings_help') ?></p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="paypal_email" class="form-label fw-semibold"><?= lang('paypal_email') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="paypal_email" name="paypal_email" value="<?= sanitize($settings->paypal_email ?? '') ?>" placeholder="your-paypal@example.com">
                        </div>
                        <small class="text-muted"><?= lang('paypal_email_help') ?></small>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_currency" class="form-label fw-semibold"><?= lang('payment_currency') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-cash text-muted"></i></span>
                            <select class="form-select border-start-0" id="payment_currency" name="payment_currency">
                                <option value="USD" <?= ($settings->payment_currency ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD</option>
                                <option value="EUR" <?= ($settings->payment_currency ?? 'USD') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                <option value="GBP" <?= ($settings->payment_currency ?? 'USD') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                                <option value="CAD" <?= ($settings->payment_currency ?? 'USD') === 'CAD' ? 'selected' : '' ?>>CAD</option>
                                <option value="AUD" <?= ($settings->payment_currency ?? 'USD') === 'AUD' ? 'selected' : '' ?>>AUD</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="paypal_client_id" class="form-label fw-semibold"><?= lang('paypal_client_id') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="paypal_client_id" name="paypal_client_id" value="<?= sanitize($settings->paypal_client_id ?? '') ?>" placeholder="Your PayPal Client ID">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="paypal_client_secret" class="form-label fw-semibold"><?= lang('paypal_client_secret') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="paypal_client_secret" name="paypal_client_secret" value="<?= sanitize($settings->paypal_client_secret ?? '') ?>" placeholder="Your PayPal Client Secret">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="paypal_sandbox" name="paypal_sandbox" <?= ($settings->paypal_sandbox ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="paypal_sandbox"><?= lang('paypal_sandbox') ?></label>
                                </div>
                                <small class="text-muted"><?= lang('paypal_sandbox_help') ?></small>
                    </div>
                    <div class="col-md-6">
                        <label for="per_day_cost" class="form-label fw-semibold"><?= lang('per_day_cost') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-cash-stack text-muted"></i></span>
                            <input type="number" class="form-control border-start-0 ps-0" id="per_day_cost" name="per_day_cost" value="<?= $settings->per_day_cost ?? 0.00 ?>" min="0.01" max="100.00" step="0.01" placeholder="0.00">
                        </div>
                        <small class="text-muted"><?= lang('per_day_cost_help') ?></small>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="minimum_days" class="form-label fw-semibold"><?= lang('minimum_days') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-dash-circle text-muted"></i></span>
                            <input type="number" class="form-control border-start-0 ps-0" id="minimum_days" name="minimum_days" value="<?= $settings->minimum_days ?? 1 ?>" min="1" max="365" placeholder="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="maximum_days" class="form-label fw-semibold"><?= lang('maximum_days') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-plus-circle text-muted"></i></span>
                            <input type="number" class="form-control border-start-0 ps-0" id="maximum_days" name="maximum_days" value="<?= $settings->maximum_days ?? 30 ?>" min="1" max="365" placeholder="30">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== Appearance ==================== -->
            <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                <h5 class="fw-semibold mb-1">
                    <i class="bi bi-palette text-primary me-2"></i><?= lang('theme_settings') ?>
                </h5>
                <p class="text-muted small mb-4"><?= lang('active_theme_help') ?></p>

                <?php if (empty($themes)): ?>
                    <div class="alert alert-warning"><?= lang('theme_not_available') ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="active_theme" class="form-label fw-semibold"><?= lang('active_theme') ?></label>
                    <select class="form-select" id="active_theme" name="active_theme">
                        <?php foreach ($themes as $slug => $meta): ?>
                            <option value="<?= sanitize($slug) ?>" <?= ($settings->active_theme ?? 'default') === $slug ? 'selected' : '' ?>>
                                <?= sanitize($meta['name'] ?? $slug) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php
                $currentThemeSlug = $settings->active_theme ?? 'default';
                $currentTheme = $themes[$currentThemeSlug] ?? null;
                ?>

                <?php if ($currentTheme): ?>
                    <div class="card bg-light border-0 mt-3">
                        <div class="card-body">
                            <table class="table table-borderless mb-0 small">
                                <tbody>
                                    <tr>
                                        <th class="ps-0" style="width: 120px;"><?= lang('theme_name') ?></th>
                                        <td><?= sanitize($currentTheme['name'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0"><?= lang('theme_slug') ?></th>
                                        <td><code><?= sanitize($currentTheme['slug'] ?? '') ?></code></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0"><?= lang('theme_version') ?></th>
                                        <td><?= sanitize($currentTheme['version'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0"><?= lang('theme_author') ?></th>
                                        <td><?= sanitize($currentTheme['author'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0"><?= lang('description') ?></th>
                                        <td><?= sanitize($currentTheme['description'] ?? '') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ==================== Security ==================== -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <h5 class="fw-semibold mb-1">
                    <i class="bi bi-shield-check text-primary me-2"></i><?= lang('rate_limiting') ?>
                </h5>
                <p class="text-muted small mb-4"><?= lang('rate_limiting_help') ?></p>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rate_limit_enabled" name="rate_limit_enabled" <?= ($rateLimit['enabled'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="rate_limit_enabled"><?= lang('rate_limit_enable') ?></label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                        <th style="width: 20%;"><?= lang('rate_limit_rule') ?></th>
                                        <th style="width: 35%;"><?= lang('rate_limit_routes') ?></th>
                                        <th style="width: 20%;"><?= lang('rate_limit_limit') ?></th>
                                        <th style="width: 25%;"><?= lang('rate_limit_window') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rateLimitRules as $key => $rule): ?>
                                <tr>
                                    <td class="fw-semibold"><?= $rule['label'] ?></td>
                                    <td><code class="small"><?= $rule['routes'] ?></code></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" name="rate_limit_<?= $key ?>_limit" value="<?= $rateLimit['rules'][$key]['limit'] ?? 5 ?>" min="1" style="max-width: 100px;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" name="rate_limit_<?= $key ?>_window" value="<?= $rateLimit['rules'][$key]['window'] ?? 60 ?>" min="1" style="max-width: 120px;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="rate_limit_bypass_ips" class="form-label fw-semibold"><?= lang('rate_limit_bypass_ips') ?></label>
                        <textarea class="form-control" id="rate_limit_bypass_ips" name="rate_limit_bypass_ips" rows="3" placeholder="127.0.0.1"><?= implode(',', $rateLimit['bypass']['ips'] ?? ['127.0.0.1']) ?></textarea>
                        <small class="text-muted"><?= lang('rate_limit_bypass_ips_help') ?></small>
                    </div>
                </div>
            </div>

            <!-- ==================== Danger Zone ==================== -->
            <div class="tab-pane fade" id="danger" role="tabpanel" aria-labelledby="danger-tab">
                <div class="border border-danger rounded-3 p-5 text-center">
                    <i class="bi bi-arrow-clockwise text-danger" style="font-size: 3rem;"></i>
                    <h5 class="text-danger fw-semibold mt-3 mb-2"><?= lang('reset_votes') ?></h5>
                    <p class="text-muted mb-4"><?= lang('reset_votes_warning') ?></p>
                    <button type="button" class="btn btn-danger btn-lg px-5" data-bs-toggle="modal" data-bs-target="#resetVotesModal">
                        <?= lang('reset_votes') ?>
                    </button>
                </div>
            </div>

        </div>

        <div class="text-center mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-semibold">
                <i class="bi bi-save me-2"></i><?= lang('save_settings') ?>
            </button>
        </div>
    </form>
</div>

<!-- Reset Votes Modal -->
<div class="modal fade" id="resetVotesModal" tabindex="-1" aria-labelledby="resetVotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-semibold" id="resetVotesModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= lang('reset_votes') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fw-semibold mt-3 mb-0"><?= lang('confirm_reset_votes') ?></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <form method="POST" action="<?= url('/admin/reset-votes') ?>" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <button type="submit" class="btn btn-danger px-4">
                        <?= lang('reset_votes') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.website_settings'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
