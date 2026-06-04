<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-star text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('titles.purchase_highlight') ?></h2>
                <p class="text-muted"><?= lang('highlight_description') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="payment-form">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                        <!-- Server Selection Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-server text-primary me-2"></i><?= lang('select_server') ?>
                            </h5>

                            <div class="mb-3">
                                <label for="server_id" class="form-label fw-semibold"><?= lang('choose_server') ?></label>
                                <select class="form-select" id="server_id" name="server_id" required>
                                    <option value=""><?= lang('select_a_server') ?></option>
                                    <?php /** @noinspection PhpUndefinedVariableInspection */
                                    foreach ($servers as $server): ?>
                                        <option value="<?= $server->id ?>" <?= $server->highlight ? 'disabled' : '' ?>>
                                            <?= sanitize($server->name) ?> (<?= sanitize($server->address) ?>:<?= sanitize($server->port) ?>)
                                            <?php if ($server->highlight): ?>
                                                <span class="text-warning">- <?= lang('already_highlighted') ?></span>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted"><?= lang('non_highlighted_only') ?></small>
                            </div>
                        </div>

                        <!-- Duration Selection Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-calendar text-primary me-2"></i><?= lang('select_duration') ?>
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="days" class="form-label fw-semibold"><?= lang('number_of_days') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-calendar-event text-muted"></i>
                                        </span>
                                        <input type="number"
                                               class="form-control border-start-0 ps-0"
                                               id="days"
                                               name="days"
                                               min="<?= /** @noinspection PhpUndefinedVariableInspection */
                                               $min_days ?>"
                                               max="<?= /** @noinspection PhpUndefinedVariableInspection */
                                               $max_days ?>"
                                               value="<?= $min_days ?>"
                                               required>
                                    </div>
                                    <small class="text-muted"><?= sprintf(lang('min_max_days'), $min_days, $max_days) ?></small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><?= lang('cost_calculation') ?></label>
                                    <div class="bg-white px-3 border-0 d-flex align-items-center" style="height: 38px; border-radius: 0.375rem;">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span id="cost-display" class="text-muted">
                                                <span id="days-display" class="text-dark fw-semibold"><?= $min_days ?></span> <?= lang('days') ?> × $<?= number_format($cost_per_day, 2) ?>/<?= lang('day') ?>
                                            </span>
                                            <strong id="total-display" class="text-dark fw-bold">$<?= number_format($min_days * $cost_per_day, 2) ?> <?= $currency ?></strong>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block">&nbsp;</small>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal Button Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-credit-card text-primary me-2"></i><?= lang('payment') ?>
                            </h5>

                            <div id="paypal-button-container" class="text-center">
                                <?php if (empty($paypal_configured)): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <?= lang('paypal_not_configured') ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted"><?= lang('complete_payment_paypal') ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <?= lang('highlight_info') ?>
                            </div>
                        </div>

                        <!-- Loading and Messages -->
                        <div id="loading-spinner" class="text-center d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden"><?= lang('processing') ?></span>
                            </div>
                            <p class="mt-2 text-muted"><?= lang('processing_payment') ?></p>
                        </div>

                        <div id="payment-messages"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($paypal_configured)): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= rawurlencode($paypal_client_id) ?>&currency=<?= rawurlencode($currency) ?>&intent=capture"></script>
<?php endif; ?>

<script id="payment-config" type="application/json">
{
    "costPerDay": <?= json_encode((float)$cost_per_day) ?>,
    "currency": <?= json_encode($currency) ?>,
    "minDays": <?= json_encode((int)$min_days) ?>,
    "maxDays": <?= json_encode((int)$max_days) ?>,
    "hasPaypal": <?= json_encode(!empty($paypal_configured)) ?>,
    "urls": {
        "createOrder": <?= json_encode(url('/paypal/create-order')) ?>,
        "capturePayment": <?= json_encode(url('/paypal/capture-payment')) ?>,
        "redirect": <?= json_encode(url('/profile/' . auth()->username)) ?>
    },
    "lang": {
        "completePayment": <?= json_encode(lang('complete_payment_paypal')) ?>,
        "paymentSuccessful": <?= json_encode(lang('payment_successful')) ?>
    }
}
</script>
<script src="<?= asset('js/payments-purchase.js') ?>" defer></script>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.purchase_highlight'); ?>

<?php include layout('app'); ?>
