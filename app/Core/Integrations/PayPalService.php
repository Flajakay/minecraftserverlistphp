<?php

namespace App\Core\Integrations;

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
    private $client;
    private $ordersController;

    public function __construct()
    {
        $this->client = $this->getClient();
        $this->ordersController = $this->client->getOrdersController();
    }

    private function getClient()
    {
        $clientId = Setting::getValue('paypal_client_id');
        $clientSecret = Setting::getValue('paypal_client_secret');
        $isSandbox = Setting::getValue('paypal_sandbox', 1);

        if (!$clientId || !$clientSecret) {
            throw new \Exception('PayPal client credentials not configured');
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

    public function createOrder($amount, $currency, $description)
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
            $response = $this->ordersController->createOrder([
                'body' => $orderRequest,
                'prefer' => 'return=representation'
            ]);
            return $response;
        } catch (\Exception $e) {
            error_log('PayPal order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function capturePayment($orderId)
    {
        try {
            $response = $this->ordersController->captureOrder([
                'id' => $orderId,
                'prefer' => 'return=representation'
            ]);
            return $response;
        } catch (\Exception $e) {
            error_log('PayPal payment capture failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function validateConfiguration()
    {
        $clientId = Setting::getValue('paypal_client_id');
        $clientSecret = Setting::getValue('paypal_client_secret');
        $email = Setting::getValue('paypal_email');

        return !empty($clientId) && !empty($clientSecret) && !empty($email);
    }

    public function calculateAmount($days)
    {
        $costPerDay = Setting::getValue('per_day_cost', 0.00);
        return $days * $costPerDay;
    }

    public function validateDays($days)
    {
        $minDays = Setting::getValue('minimum_days', 1);
        $maxDays = Setting::getValue('maximum_days', 30);

        return $days >= $minDays && $days <= $maxDays;
    }
}
