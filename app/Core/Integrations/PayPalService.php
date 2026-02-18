<?php

namespace App\Core\Integrations;

use Exception;
use PaypalServerSdkLib\Controllers\OrdersController;
use PaypalServerSdkLib\Http\ApiResponse;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use App\Models\Setting;

/**
 * PayPal integration wrapper.
 *
 * Encapsulates client initialization from settings and provides order creation/capture.
 */
class PayPalService
{
    private PaypalServerSdkClient $client;
    private OrdersController $ordersController;

    public function __construct()
    {
        $this->client = $this->getClient();
        $this->ordersController = $this->client->getOrdersController();
    }

    private function getClient(): PaypalServerSdkClient
    {
        $config = require __DIR__ . '/../../../config/app.php';
        $paypalConfig = $config['paypal'] ?? [];

        $clientId = $paypalConfig['client_id'] ?? '';
        $clientSecret = $paypalConfig['client_secret'] ?? '';
        $isSandbox = $paypalConfig['sandbox'] ?? true;

        if (!$clientId || !$clientSecret) {
            throw new Exception('PayPal client credentials not configured');
        }

        $environment = $isSandbox ? Environment::SANDBOX : Environment::PRODUCTION;
        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $clientId,
                    $clientSecret
                )
            )
            ->environment($environment)
            ->build();
    }

    public function createOrder($amount, $currency, $description): ApiResponse
    {
        $orderRequest = OrderRequestBuilder::init(
            CheckoutPaymentIntent::CAPTURE,
            [
                PurchaseUnitRequestBuilder::init(
                    AmountWithBreakdownBuilder::init(
                        $currency,
                        number_format($amount, 2, '.', '')
                    )->build()
                )
                ->description($description)
                ->build()
            ]
        )->build();
        try {
            return $this->ordersController->createOrder([
                'body' => $orderRequest,
                'prefer' => 'return=representation'
            ]);
        } catch (Exception $e) {
            error_log('PayPal order creation failed: [REDACTED]');
            throw new Exception('Failed to create PayPal order');
        }
    }

    public function capturePayment($orderId): ApiResponse
    {
        try {
            return $this->ordersController->captureOrder([
                'id' => $orderId,
                'prefer' => 'return=representation'
            ]);
        } catch (Exception $e) {
            error_log('PayPal payment capture failed: [REDACTED]');
            throw new Exception('Payment capture failed');
        }
    }

    public function validateConfiguration(): bool
    {
        $config = require __DIR__ . '/../../../config/app.php';
        $paypalConfig = $config['paypal'] ?? [];

        $clientId = $paypalConfig['client_id'] ?? '';
        $clientSecret = $paypalConfig['client_secret'] ?? '';
        $email = $paypalConfig['email'] ?? '';

        return !empty($clientId) && !empty($clientSecret) && !empty($email);
    }

    public function calculateAmount($days): float|int
    {
        $costPerDay = Setting::getValue('per_day_cost', 0.00);
        return $days * $costPerDay;
    }

    public function validateDays($days): bool
    {
        $minDays = Setting::getValue('minimum_days', 1);
        $maxDays = Setting::getValue('maximum_days', 30);

        return $days >= $minDays && $days <= $maxDays;
    }
}
