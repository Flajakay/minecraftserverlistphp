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

/**
 * PayPal integration wrapper.
 *
 * Encapsulates PayPal client initialization and order create/capture calls.
 */
class PayPalService
{
    private PaypalServerSdkClient $client;
    private OrdersController $ordersController;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = $this->getClient();
        $this->ordersController = $this->client->getOrdersController();
    }

    private function getClient(): PaypalServerSdkClient
    {
        $clientId = $this->config['client_id'] ?? '';
        $clientSecret = $this->config['client_secret'] ?? '';
        $isSandbox = $this->config['sandbox'] ?? true;

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

    public function verifyCapturedOrder($order, $payment, string $expectedPayeeEmail): bool
    {
        if (!$order || $order->getStatus() !== 'COMPLETED') {
            return false;
        }

        if ($order->getId() !== $payment->paypal_order_id) {
            return false;
        }

        $purchaseUnits = $order->getPurchaseUnits();
        if (!is_array($purchaseUnits) || count($purchaseUnits) !== 1) {
            return false;
        }

        $purchaseUnit = $purchaseUnits[0];
        $expectedAmount = number_format((float)$payment->revenue, 2, '.', '');
        $expectedCurrency = strtoupper((string)($payment->currency ?? 'USD'));

        $amount = $purchaseUnit->getAmount();
        if (!$amount || strtoupper($amount->getCurrencyCode()) !== $expectedCurrency || number_format((float)$amount->getValue(), 2, '.', '') !== $expectedAmount) {
            return false;
        }

        $payments = $purchaseUnit->getPayments();
        $captures = $payments ? $payments->getCaptures() : null;
        if (!is_array($captures) || empty($captures)) {
            return false;
        }

        $capture = $captures[0];
        $captureAmount = $capture->getAmount();
        if (
            $capture->getStatus() !== 'COMPLETED' ||
            !$captureAmount ||
            strtoupper($captureAmount->getCurrencyCode()) !== $expectedCurrency ||
            number_format((float)$captureAmount->getValue(), 2, '.', '') !== $expectedAmount
        ) {
            return false;
        }

        $expectedPayeeEmail = strtolower($expectedPayeeEmail);
        $payee = $purchaseUnit->getPayee();

        if ($expectedPayeeEmail !== '' && (!$payee || strtolower((string)$payee->getEmailAddress()) !== $expectedPayeeEmail)) {
            return false;
        }

        return true;
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
