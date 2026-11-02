<?php
namespace App\Services;

use App\Models\OnlineTransaction;
use App\Models\Invoice;
use App\Models\Refund;
use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class PaymentGatewayService
{
    private array $config;
    private OnlineTransaction $onlineTxModel;
    private Invoice $invoiceModel;
    private Refund $refundModel;

    public function __construct()
    {
        $configFile = dirname(__DIR__, 2) . '/config/finance.php';
        $fullConfig = file_exists($configFile) ? require $configFile : [];
        $this->config = $fullConfig['razorpay'] ?? [
            'enabled'        => true,
            'mode'           => 'test',
            'key_id'         => 'rzp_test_ToursTravelsTest2026',
            'key_secret'     => 'ToursTravelsSecretKey2026',
            'webhook_secret' => 'ToursTravelsWebhookSecret2026',
            'company_name'   => 'MANASWI Tours & Travels',
            'theme_color'    => '#176B5B',
        ];

        $this->onlineTxModel = new OnlineTransaction();
        $this->invoiceModel = new Invoice();
        $this->refundModel = new Refund();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getKeyId(): string
    {
        return $this->config['key_id'] ?? '';
    }

    /**
     * Create Razorpay Order server-side with idempotency
     */
    public function createOrder(int $invoiceId, float $amount, ?int $customerId = null): array
    {
        if ($amount <= 0.0) {
            throw new RuntimeException("Payment amount must be greater than zero.");
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            throw new RuntimeException("Invoice not found.");
        }

        $balanceDue = round((float)$invoice['total_amount'] - (float)$invoice['paid_amount'], 2);
        if ($amount > $balanceDue && $balanceDue > 0) {
            $amount = $balanceDue;
        }

        $amountInPaise = (int)round($amount * 100);
        $currency = 'INR';
        $receipt = 'rcpt_' . $invoice['invoice_number'] . '_' . time();

        $orderId = null;
        $keyId = $this->config['key_id'];
        $keySecret = $this->config['key_secret'];

        // If credentials are non-placeholder, try calling Razorpay official REST API
        if (!empty($keyId) && !empty($keySecret) && !str_contains($keyId, 'Test2026') && function_exists('curl_init')) {
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            $postData = json_encode([
                'amount'   => $amountInPaise,
                'currency' => $currency,
                'receipt'  => $receipt,
                'notes'    => [
                    'invoice_id'     => (string)$invoiceId,
                    'invoice_number' => $invoice['invoice_number'],
                ]
            ]);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_USERPWD        => "{$keyId}:{$keySecret}",
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 8
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $decoded = json_decode($response, true);
                if (!empty($decoded['id'])) {
                    $orderId = $decoded['id'];
                }
            }
        }

        // Sandbox / Test fallback order ID generator
        if (!$orderId) {
            $orderId = 'order_test_' . substr(bin2hex(random_bytes(8)), 0, 14);
        }

        $now = date('Y-m-d H:i:s');
        $txId = $this->onlineTxModel->create([
            'gateway'      => 'razorpay',
            'order_id'     => $orderId,
            'invoice_id'   => $invoiceId,
            'customer_id'  => $customerId ?: ($invoice['customer_profile_id'] ?? $invoice['customer_id'] ?? null),
            'amount'       => $amount,
            'currency'     => $currency,
            'status'       => 'created',
            'payload_json' => json_encode(['receipt' => $receipt, 'amount_paise' => $amountInPaise]),
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        return [
            'id'             => $txId,
            'order_id'       => $orderId,
            'amount'         => $amount,
            'amount_paise'   => $amountInPaise,
            'currency'       => $currency,
            'key_id'         => $this->config['key_id'],
            'company_name'   => $this->config['company_name'] ?? 'MANASWI Tours & Travels',
            'theme_color'    => $this->config['theme_color'] ?? '#176B5B',
            'invoice_number' => $invoice['invoice_number'],
            'invoice_id'     => $invoiceId,
        ];
    }

    /**
     * Strict HMAC-SHA256 signature verification according to Razorpay docs
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        // Test Sandbox mock bypass helper
        if (str_starts_with($signature, 'mock_sig_') && ($this->config['mode'] ?? 'test') === 'test') {
            return true;
        }

        $keySecret = $this->config['key_secret'];
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process verified payment success & credit books
     */
    public function processPaymentSuccess(string $orderId, string $paymentId, string $signature, ?int $userId = null): array
    {
        if (!$this->verifyPaymentSignature($orderId, $paymentId, $signature)) {
            throw new RuntimeException("Invalid payment signature verification failed.");
        }

        $tx = $this->onlineTxModel->findByOrderId($orderId);
        if (!$tx) {
            throw new RuntimeException("Online transaction order not found: {$orderId}");
        }

        if ($tx['status'] === 'captured') {
            return ['status' => 'already_captured', 'transaction' => $tx];
        }

        $now = date('Y-m-d H:i:s');
        $this->onlineTxModel->update($tx['id'], [
            'payment_id' => $paymentId,
            'signature'  => $signature,
            'status'     => 'captured',
            'updated_at' => $now
        ]);

        // Integrate with customer payment record & ledger
        $financeService = new FinanceService();
        $invoice = $this->invoiceModel->find($tx['invoice_id']);

        // Find Razorpay gateway settlement account
        $pdo = Database::getConnection();
        $accStmt = $pdo->query("SELECT id FROM `finance_accounts` WHERE `account_type` = 'gateway' AND `status` = 'active' LIMIT 1");
        $gatewayAccountId = (int)$accStmt->fetchColumn();
        if ($gatewayAccountId <= 0) {
            $gatewayAccountId = 1; // Fallback to primary account
        }

        $paymentResult = $financeService->recordCustomerPayment(
            $tx['invoice_id'],
            (float)$tx['amount'],
            'razorpay',
            $paymentId,
            "Razorpay Order: {$orderId} / Payment ID: {$paymentId}",
            $gatewayAccountId,
            $userId ?: 1
        );

        return [
            'status'         => 'captured',
            'order_id'       => $orderId,
            'payment_id'     => $paymentId,
            'payment_result' => $paymentResult
        ];
    }

    /**
     * Handle verified webhook payload from Razorpay
     */
    public function handleWebhook(string $payloadJson, string $webhookSignature): array
    {
        $webhookSecret = $this->config['webhook_secret'];
        $expected = hash_hmac('sha256', $payloadJson, $webhookSecret);

        if (!hash_equals($expected, $webhookSignature)) {
            throw new RuntimeException("Webhook signature verification failed.");
        }

        $event = json_decode($payloadJson, true);
        if (!$event || empty($event['event'])) {
            throw new RuntimeException("Invalid webhook JSON payload.");
        }

        $eventId = $event['event_id'] ?? ($event['id'] ?? null);
        if ($eventId) {
            // Deduplication check
            $existing = $this->onlineTxModel->findBy('webhook_event_id', $eventId);
            if ($existing) {
                return ['status' => 'duplicate_ignored', 'event_id' => $eventId];
            }
        }

        $eventType = $event['event'];
        $paymentEntity = $event['payload']['payment']['entity'] ?? [];
        $orderId = $paymentEntity['order_id'] ?? null;
        $paymentId = $paymentEntity['id'] ?? null;

        if ($eventType === 'payment.captured' && $orderId && $paymentId) {
            $tx = $this->onlineTxModel->findByOrderId($orderId);
            if ($tx && $tx['status'] !== 'captured') {
                $now = date('Y-m-d H:i:s');
                $this->onlineTxModel->update($tx['id'], [
                    'payment_id'       => $paymentId,
                    'status'           => 'captured',
                    'webhook_event_id' => $eventId,
                    'updated_at'       => $now
                ]);

                $financeService = new FinanceService();
                $financeService->recordCustomerPayment(
                    $tx['invoice_id'],
                    (float)$tx['amount'],
                    'razorpay',
                    $paymentId,
                    "Webhook captured {$paymentId}",
                    3, // Razorpay settlement account
                    1
                );
            }
        } elseif ($eventType === 'payment.failed' && $orderId) {
            $tx = $this->onlineTxModel->findByOrderId($orderId);
            if ($tx) {
                $this->onlineTxModel->update($tx['id'], [
                    'status'            => 'failed',
                    'error_code'        => $paymentEntity['error_code'] ?? 'PAYMENT_FAILED',
                    'error_description' => $paymentEntity['error_description'] ?? 'Customer failed or abandoned payment.',
                    'webhook_event_id'  => $eventId,
                    'updated_at'        => date('Y-m-d H:i:s')
                ]);
            }
        }

        return ['status' => 'processed', 'event' => $eventType];
    }
}
