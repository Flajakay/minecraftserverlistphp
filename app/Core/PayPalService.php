<?php

namespace App\Core;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Models\Setting;

class PayPalService
{
    private $client;

    public function __construct()
    {
        $this->client = $this->getClient();
    }

    private function getClient()
    {
        $clientId = Setting::getValue('paypal_client_id');
        $clientSecret = Setting::getValue('paypal_client_secret');
        $isSandbox = Setting::getValue('paypal_sandbox', 1);

        if (!$clientId || !$clientSecret) {
            throw new \Exception('PayPal client credentials not configured');
        }

        if ($isSandbox) {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }

        return new PayPalHttpClient($environment);
    }

    public function createOrder($amount, $currency, $description)
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', '')
                ],
                'description' => $description
            ]],
            'application_context' => [
                'cancel_url' => url('/paypal/cancel'),
                'return_url' => url('/premium')
            ]
        ];

        try {
            $response = $this->client->execute($request);
            return $response;
        } catch (\Exception $e) {
            error_log('PayPal order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function capturePayment($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);

        try {
            $response = $this->client->execute($request);
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
