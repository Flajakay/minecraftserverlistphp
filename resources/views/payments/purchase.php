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
                                            <?= htmlspecialchars($server->name) ?> (<?= $server->address ?>:<?= $server->port ?>)
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
                                    <div class="card bg-light">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span id="cost-display">
                                                    <span id="days-display"><?= $min_days ?></span> <?= lang('days') ?> × $<?= /** @noinspection PhpUndefinedVariableInspection */
                                                    number_format($cost_per_day, 2) ?>/<?= lang('day') ?>
                                                </span>
                                                <strong id="total-display">$<?= number_format($min_days * $cost_per_day, 2) ?> <?= /** @noinspection PhpUndefinedVariableInspection */
                                                    $currency ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal Button Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold text-dark mb-3">
                                <i class="bi bi-credit-card text-primary me-2"></i><?= lang('payment') ?>
                            </h5>

                            <div id="paypal-button-container" class="text-center">
                                <?php if (empty(setting('paypal_client_id')) || empty(setting('paypal_client_secret'))): ?>
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

<?php if (!empty(setting('paypal_client_id')) && !empty(setting('paypal_client_secret'))): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= htmlspecialchars(setting('paypal_client_id')) ?>&currency=<?= $currency ?>&intent=capture"></script>
<?php endif; ?>

<?php if (!empty(setting('paypal_client_id')) && !empty(setting('paypal_client_secret'))): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('payment-form');
    const serverSelect = document.getElementById('server_id');
    const daysInput = document.getElementById('days');
    const costDisplay = document.getElementById('cost-display');
    const totalDisplay = document.getElementById('total-display');
    const daysDisplay = document.getElementById('days-display');
    const paypalContainer = document.getElementById('paypal-button-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    const messagesDiv = document.getElementById('payment-messages');

    const costPerDay = <?= $cost_per_day ?>;
    const currency = '<?= $currency ?>';

    function updateCost() {
        const days = parseInt(daysInput.value) || 0;
        const total = days * costPerDay;
        daysDisplay.textContent = days;
        totalDisplay.textContent = '$' + total.toFixed(2) + ' ' + currency;
    }

    function showMessage(message, type = 'info') {
        messagesDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    function showLoading(show) {
        loadingSpinner.classList.toggle('d-none', !show);
        paypalContainer.classList.toggle('d-none', show);
    }

    daysInput.addEventListener('input', updateCost);
    updateCost();

    serverSelect.addEventListener('change', function() {
        if (this.value) {
            renderPayPalButton();
        } else {
            paypalContainer.innerHTML = '<p class="text-muted"><?= lang('complete_payment_paypal') ?></p>';
        }
    });

    function renderPayPalButton() {
        paypalContainer.innerHTML = '';

        paypal.Buttons({
            createOrder: function(data, actions) {
                showLoading(true);

                return fetch('<?= url('/paypal/create-order') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        csrf_token: document.querySelector('[name="csrf_token"]').value,
                        server_id: serverSelect.value,
                        days: daysInput.value
                    })
                })
                .then(response => response.json())
                .then(orderData => {
                    showLoading(false);
                    if (orderData.error) {
                        throw new Error(orderData.error);
                    }
                    return orderData.order_id;
                })
                .catch(error => {
                    showLoading(false);
                    showMessage('Failed to create payment: ' + error.message, 'danger');
                    throw error;
                });
            },

            onApprove: function(data, actions) {
                showLoading(true);

                return fetch('<?= url('/paypal/capture-payment') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        csrf_token: document.querySelector('[name="csrf_token"]').value,
                        order_id: data.orderID,
                        server_id: serverSelect.value,
                        days: daysInput.value
                    })
                })
                .then(response => response.json())
                .then(captureData => {
                    showLoading(false);
                    if (captureData.error) {
                        throw new Error(captureData.error);
                    }

                    showMessage('<?= lang('payment_successful') ?>', 'success');
                    setTimeout(() => {
                        window.location.href = '<?= url('/profile/' . auth()->username) ?>';
                    }, 2000);
                })
                .catch(error => {
                    showLoading(false);
                    showMessage('Payment failed: ' + error.message, 'danger');
                });
            },

            onError: function(err) {
                showLoading(false);
                showMessage('PayPal error occurred', 'danger');
                console.error('PayPal error:', err);
            },

            onCancel: function(data) {
                showLoading(false);
                showMessage('Payment was cancelled', 'warning');
            }
        }).render('#paypal-button-container');
    }

    if (serverSelect.value) {
        renderPayPalButton();
    }
});
</script>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.purchase_highlight') . ' - ' . setting('title'); ?>

<?php include __DIR__ . '/../layouts/app.php'; ?>
