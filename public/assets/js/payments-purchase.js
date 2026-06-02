/**
 * Payments Purchase Page JavaScript
 * Handles calculations and PayPal integrations for premium highlights
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const configEl = document.getElementById('payment-config');
        if (!configEl) return;

        let config;
        try {
            config = JSON.parse(configEl.textContent);
        } catch (e) {
            console.error('Failed to parse payment config:', e);
            return;
        }

        // 1. Interactive cost calculator logic
        const daysInput = document.getElementById('days');
        const totalDisplay = document.getElementById('total-display');
        const daysDisplay = document.getElementById('days-display');

        if (daysInput && totalDisplay && daysDisplay) {
            const costPerDay = parseFloat(config.costPerDay);
            const currency = config.currency;
            const minDays = parseInt(config.minDays, 10);
            const maxDays = parseInt(config.maxDays, 10);

            function normalizeDays() {
                const days = parseInt(daysInput.value, 10);
                if (Number.isNaN(days)) {
                    return minDays;
                }
                return Math.min(Math.max(days, minDays), maxDays);
            }

            function updateCost() {
                const days = normalizeDays();
                const total = days * costPerDay;

                daysInput.value = days;
                daysDisplay.textContent = days;
                totalDisplay.textContent = '$' + total.toFixed(2) + ' ' + currency;
            }

            daysInput.addEventListener('input', updateCost);
            daysInput.addEventListener('change', updateCost);
            updateCost();
        }

        // 2. PayPal Integration logic
        if (config.hasPaypal && typeof paypal !== 'undefined') {
            const serverSelect = document.getElementById('server_id');
            const paypalContainer = document.getElementById('paypal-button-container');
            const loadingSpinner = document.getElementById('loading-spinner');
            const messagesDiv = document.getElementById('payment-messages');

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

            serverSelect.addEventListener('change', function() {
                if (this.value) {
                    renderPayPalButton();
                } else {
                    paypalContainer.innerHTML = `<p class="text-muted">${config.lang.completePayment}</p>`;
                }
            });

            function renderPayPalButton() {
                paypalContainer.innerHTML = '';

                paypal.Buttons({
                    createOrder: function(data, actions) {
                        showLoading(true);

                        return fetch(config.urls.createOrder, {
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

                        return fetch(config.urls.capturePayment, {
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

                            showMessage(config.lang.paymentSuccessful, 'success');
                            setTimeout(() => {
                                window.location.href = config.urls.redirect;
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
        }
    });
})();
