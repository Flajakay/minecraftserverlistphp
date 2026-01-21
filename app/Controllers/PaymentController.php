<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Payment;
use App\Core\Integrations\PayPalService;
use App\Models\Setting;

/**
 * Premium purchase/payment controller.
 *
 * Renders the purchase page and exposes JSON endpoints used by the PayPal client integration.
 */
class PaymentController
{
    /**
     * Render the premium purchase UI.
     *
     * Availability is controlled by settings (per-day cost > 0).
     */
    public function showPurchase()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        if (!Setting::getValue('per_day_cost', 0) > 0) {
            flash('error', lang('premium_not_available'));
            redirect('/');
        }
        $userServers = Server::getUserServers(auth()->id);
        $currency = Setting::getValue('payment_currency', 'USD');
        $costPerDay = Setting::getValue('per_day_cost', 0.00);
        $minDays = Setting::getValue('minimum_days', 1);
        $maxDays = Setting::getValue('maximum_days', 30);

        view('payments.purchase', [
            'servers' => $userServers,
            'currency' => $currency,
            'cost_per_day' => $costPerDay,
            'min_days' => $minDays,
            'max_days' => $maxDays
        ]);
    }

    /**
     * Create a PayPal order.
     *
     * JSON-only endpoint used by the PayPal JS SDK.
     */
    public function createOrder()
    {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $paypalService = new PayPalService();


            $serverId = (int)($_POST['server_id'] ?? 0);
            $days = (int)($_POST['days'] ?? 0);

            $server = Server::find($serverId);
            if (!$server || $server->user_id != auth()->id) {
                throw new \Exception('Invalid server selection');
            }

            if (!$paypalService->validateDays($days)) {
                throw new \Exception('Invalid number of days');
            }

            $amount = $paypalService->calculateAmount($days);
            $currency = Setting::getValue('payment_currency', 'USD');
            $description = "Server highlight for {$server->name} - {$days} days";

            $order = $paypalService->createOrder($amount, $currency, $description);
            echo json_encode([
                'order_id' => $order->getResult()->getId(),
                'server_id' => $serverId,
                'days' => $days,
                'amount' => $amount
            ]);

        } catch (\Exception $e) {
            error_log('PayPal order creation error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create payment order']);
        }
    }

    /**
     * Capture a PayPal payment after approval.
     *
     * JSON-only endpoint. On success it records the payment and enables server highlighting.
     */
    public function capturePayment()
    {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $paypalService = new PayPalService();

            $orderId = $_POST['order_id'] ?? '';
            $serverId = (int)($_POST['server_id'] ?? 0);
            $days = (int)($_POST['days'] ?? 0);

            if (empty($orderId) || !$serverId || !$days) {
                throw new \Exception('Missing payment data');
            }

            $server = Server::find($serverId);
            if (!$server || $server->user_id != auth()->id) {
                throw new \Exception('Invalid server');
            }
            $capture = $paypalService->capturePayment($orderId);
            if ($capture->getResult()->getStatus() !== 'COMPLETED') {
                throw new \Exception('Payment not completed');
            }

            $amount = $paypalService->calculateAmount($days);
            $currency = Setting::getValue('payment_currency', 'USD');

            $paymentId = Payment::create([
                'user_id' => auth()->id,
                'server_id' => $serverId,
                'highlighted_days' => $days,
                'revenue' => $amount,
                'email' => auth()->email,
                'status' => 'completed',
                'paypal_order_id' => $orderId
            ]);

            Server::updateHighlight($serverId, 1);

            flash('success', lang('payment_successful_highlight'));
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            error_log('Payment capture error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => 'Payment processing failed']);
        }
    }

    /**
     * Handle a canceled PayPal checkout and return the user to the premium page.
     */
    public function cancelPayment()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        flash('info', lang('payment_cancelled'));
        redirect('/premium');
    }
}
