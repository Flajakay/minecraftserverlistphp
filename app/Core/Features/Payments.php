<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\Payment;
use App\Models\Setting;
use App\Core\Integrations\PayPalService;
use App\Core\Support\Config;
use App\Core\System\Database;
use Exception;

/**
 * Feature class for handling premium payments.
 */
class Payments
{
    public static function initiateOrder(int $userId, int $serverId, int $days): array
    {
        $paypalConfig = self::paypalConfig();
        if (!self::paypalConfigured($paypalConfig)) {
            throw new Exception('PayPal is not configured');
        }

        $paypalService = new PayPalService($paypalConfig);

        $server = Server::find($serverId);
        if (!$server || $server->user_id != $userId) {
            throw new Exception('Invalid server selection');
        }

        if (!$paypalService->validateDays($days)) {
            throw new Exception('Invalid number of days');
        }

        $amount = $paypalService->calculateAmount($days);
        $currency = Setting::getValue('payment_currency', 'USD');
        $description = "Server highlight for {$server->name} - {$days} days";

        $order = $paypalService->createOrder($amount, $currency, $description);
        $orderId = $order->getResult()->getId();

        Payment::create([
            'user_id' => $userId,
            'server_id' => $serverId,
            'highlighted_days' => $days,
            'revenue' => $amount,
            'currency' => $currency,
            'email' => auth()->email,
            'status' => 'pending',
            'paypal_order_id' => $orderId
        ]);
        
        return [
            'order_id' => $orderId,
            'server_id' => $serverId,
            'days' => $days,
            'amount' => $amount,
            'currency' => $currency
        ];
    }

    public static function completePayment(int $userId, string $orderId): bool
    {
        $paypalConfig = self::paypalConfig();
        if (!self::paypalConfigured($paypalConfig)) {
            throw new Exception('PayPal is not configured');
        }

        $paypalService = new PayPalService($paypalConfig);

        if (empty($orderId)) {
            throw new Exception('Missing payment data');
        }

        $payment = Payment::findByPayPalOrderId($orderId);
        if (!$payment || $payment->user_id != $userId) {
            throw new Exception('Invalid payment');
        }

        if ($payment->status !== 'pending') {
            throw new Exception('Payment already processed');
        }

        if (!$paypalService->validateDays((int)$payment->highlighted_days)) {
            throw new Exception('Invalid payment duration');
        }

        $server = Server::find((int)$payment->server_id);
        if (!$server || $server->user_id != $userId) {
            throw new Exception('Invalid server');
        }

        $capture = $paypalService->capturePayment($orderId);
        if (!$paypalService->verifyCapturedOrder($capture->getResult(), $payment, (string)$paypalConfig['email'])) {
            throw new Exception('Payment verification failed');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            if (Payment::markCompleted($payment->id) !== 1) {
                throw new Exception('Payment already processed');
            }

            Server::updateHighlight($payment->server_id, 1);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        return true;
    }

    private static function paypalConfig(): array
    {
        return Config::get('app.paypal', []);
    }

    private static function paypalConfigured(array $config): bool
    {
        return !empty($config['email'] ?? '')
            && !empty($config['client_id'] ?? '')
            && !empty($config['client_secret'] ?? '');
    }
}
