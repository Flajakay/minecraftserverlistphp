<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\Payment;
use App\Models\Setting;
use App\Core\Integrations\PayPalService;

/**
 * Feature class for handling premium payments.
 */
class Payments
{
    public static function initiateOrder(int $userId, int $serverId, int $days): array
    {
        $paypalService = new PayPalService();

        $server = Server::find($serverId);
        if (!$server || $server->user_id != $userId) {
            throw new \Exception('Invalid server selection');
        }

        if (!$paypalService->validateDays($days)) {
            throw new \Exception('Invalid number of days');
        }

        $amount = $paypalService->calculateAmount($days);
        $currency = Setting::getValue('payment_currency', 'USD');
        $description = "Server highlight for {$server->name} - {$days} days";

        $order = $paypalService->createOrder($amount, $currency, $description);
        
        return [
            'order_id' => $order->getResult()->getId(),
            'server_id' => $serverId,
            'days' => $days,
            'amount' => $amount,
            'currency' => $currency
        ];
    }
    public static function completePayment(int $userId, string $orderId, int $serverId, int $days): bool
    {
        $paypalService = new PayPalService();

        if (empty($orderId) || !$serverId || !$days) {
            throw new \Exception('Missing payment data');
        }

        $server = Server::find($serverId);
        if (!$server || $server->user_id != $userId) {
            throw new \Exception('Invalid server');
        }

        $capture = $paypalService->capturePayment($orderId);
        if ($capture->getResult()->getStatus() !== 'COMPLETED') {
            throw new \Exception('Payment not completed');
        }

        $amount = $paypalService->calculateAmount($days);

        Payment::create([
            'user_id' => $userId,
            'server_id' => $serverId,
            'highlighted_days' => $days,
            'revenue' => $amount,
            'email' => auth()->email, // Assuming auth() helper is available as in controller
            'status' => 'completed',
            'paypal_order_id' => $orderId
        ]);

        Server::updateHighlight($serverId, 1);

        return true;
    }
}
