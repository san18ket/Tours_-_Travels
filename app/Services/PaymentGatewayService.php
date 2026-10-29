<?php
namespace App\Services;

use App\Models\OnlineTransaction;
use RuntimeException;

class PaymentGatewayService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('finance.gateways.razorpay', []);
    }

    public function createOrder(int $invoiceId, int $customerId, float $amount, string $currency = 'INR'): array
    {
        $orderId = 'order_' . bin2hex(random_bytes(10));

        $model = new OnlineTransaction();
        $txId = $model->create([
            'gateway'     => 'razorpay',
            'order_id'    => $orderId,
            'invoice_id'  => $invoiceId,
            'customer_id' => $customerId,
            'amount'      => $amount,
            'currency'    => $currency,
            'status'      => 'created',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return [
            'id'       => $txId,
            'order_id' => $orderId,
            'amount'   => $amount,
            'currency' => $currency,
            'key'      => $this->config['key_id'] ?? '',
        ];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = $this->config['key_secret'] ?? '';
        if (empty($secret)) {
            // In development / demo mode without secret
            return true;
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);
        return hash_equals($expected, $signature);
    }
}

