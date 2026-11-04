<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\AuditLog;
use App\Core\Database;
use App\Core\Logger;
use App\Services\EmailService;
use RuntimeException;

class BillingService
{
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private Booking $bookingModel;
    private AuditLog $auditLog;
    public EmailService $emailService;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
        $this->auditLog = new AuditLog();
        $this->emailService = new EmailService();
    }

    /**
     * Generate immutable tax invoice for a booking
     */
    public function generateInvoice(int $bookingId): array
    {
        $booking = $this->bookingModel->find($bookingId);
        if (!$booking) {
            throw new RuntimeException("Booking not found.");
        }

        $existing = $this->invoiceModel->findByBookingId($bookingId);
        if ($existing) {
            return $existing;
        }

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string)($bookingId + 100), 5, '0', STR_PAD_LEFT);
        $cnt = 1;
        while ($this->invoiceModel->findBy('invoice_number', $invoiceNumber)) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string)($bookingId + 100 + $cnt), 5, '0', STR_PAD_LEFT);
            $cnt++;
        }

        // Safely resolve customer id for foreign key constraints
        $customerId = !empty($booking['customer_id']) ? (int)$booking['customer_id'] : null;
        if (!$customerId && !empty($booking['customer_profile_id'])) {
            $custModel = new \App\Models\Customer();
            $cp = $custModel->find((int)$booking['customer_profile_id']);
            if (!empty($cp['user_id'])) {
                $customerId = (int)$cp['user_id'];
            }
        }
        if (!$customerId) {
            $customerId = 1; // Default to admin/office user
        }

        $pdo = Database::getConnection();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }
        try {
            $invoiceId = $this->invoiceModel->create([
                'invoice_number'      => $invoiceNumber,
                'booking_id'          => $bookingId,
                'customer_id'         => $customerId,
                'customer_profile_id' => $booking['customer_profile_id'] ?? null,
                'subtotal'            => $booking['subtotal'],
                'tax_rate'            => 5.00,
                'tax_amount'          => $booking['tax_amount'],
                'discount_amount'     => $booking['discount_amount'],
                'total_amount'        => $booking['total_amount'],
                'paid_amount'         => 0.00,
                'payment_status'      => 'unpaid',
                'due_date'            => $booking['start_date'] ?? date('Y-m-d'),
                'is_immutable'        => 1,
                'issued_at'           => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record('invoice_issued', 'invoices', $invoiceId, null, [
                'invoice_number' => $invoiceNumber,
                'total_amount'   => $booking['total_amount']
            ]);

            if (!$inTx) {
                $pdo->commit();
            }
            return $this->invoiceModel->find($invoiceId);
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Failed to issue invoice: " . $e->getMessage());
            throw new RuntimeException("Could not generate tax invoice: " . $e->getMessage());
        }
    }

    /**
     * Record payment allocation with strict idempotency and transaction locking
     */
    public function recordPayment(int $invoiceId, float $amount, string $paymentMethod, ?string $transactionId = null, ?string $idempotencyKey = null, ?string $notes = null, ?int $accountId = null, ?string $paidAt = null): array
    {
        $amount = round($amount, 2);
        if ($amount <= 0.0) {
            throw new RuntimeException("Payment amount must be greater than zero.");
        }

        $idempotencyKey = $idempotencyKey ?: bin2hex(random_bytes(16));
        $transactionId  = $transactionId ?: ('TXN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));

        // Duplicate-request check using idempotency key
        $existingPayment = $this->paymentModel->findByIdempotencyKey($idempotencyKey);
        if ($existingPayment) {
            Logger::info("Prevented duplicate payment via idempotency key: {$idempotencyKey}");
            return $existingPayment;
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            throw new RuntimeException("Invoice not found.");
        }

        $currentPaid = (float)$invoice['paid_amount'];
        $total = (float)$invoice['total_amount'];
        $remaining = round(max(0.0, $total - $currentPaid), 2);

        if ($amount > ($remaining + 0.01)) {
            throw new RuntimeException("Payment amount of " . format_currency($amount) . " cannot exceed the remaining balance of " . format_currency($remaining) . ".");
        }

        $newPaid = round($currentPaid + $amount, 2);

        $newStatus = 'unpaid';
        if ($newPaid >= ($total - 0.01)) {
            $newStatus = 'paid';
        } elseif ($newPaid > 0) {
            $newStatus = 'partially_paid';
        }

        $paymentRef = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        // Resolve account if not explicitly passed
        if (!$accountId) {
            $accModel = new \App\Models\FinanceAccount();
            $targetType = ($paymentMethod === 'cash') ? 'cash' : 'bank';
            $matchingAcc = $accModel->where("status = 'active' AND account_type = ?", [$targetType], 'id ASC', 1);
            if (!empty($matchingAcc)) {
                $accountId = (int)$matchingAcc[0]['id'];
            } else {
                $anyAcc = $accModel->where("status = 'active'", [], 'id ASC', 1);
                if (!empty($anyAcc)) {
                    $accountId = (int)$anyAcc[0]['id'];
                }
            }
        }

        $paidAtTimestamp = $paidAt ? (strlen($paidAt) === 10 ? $paidAt . ' ' . date('H:i:s') : $paidAt) : date('Y-m-d H:i:s');

        $pdo = Database::getConnection();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }
        try {
            $paymentId = $this->paymentModel->create([
                'payment_reference' => $paymentRef,
                'invoice_id'        => $invoiceId,
                'account_id'        => $accountId,
                'amount'            => $amount,
                'payment_method'    => $paymentMethod,
                'transaction_id'    => $transactionId,
                'status'            => 'success',
                'idempotency_key'   => $idempotencyKey,
                'notes'             => $notes,
                'paid_at'           => $paidAtTimestamp,
            ]);

            $this->invoiceModel->update($invoiceId, [
                'paid_amount'    => $newPaid,
                'payment_status' => $newStatus,
            ]);

            // If account resolved, record in finance ledger and update account balance atomically
            if ($accountId) {
                $txnModel = new \App\Models\FinanceTransaction();
                $txnModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'credit',
                    'amount'           => $amount,
                    'category'         => 'Customer Payment',
                    'reference_type'   => 'payment',
                    'reference_id'     => $paymentId,
                    'payment_method'   => $paymentMethod,
                    'transaction_ref'  => $paymentRef,
                    'notes'            => "Payment for Invoice #{$invoice['invoice_number']}. " . ($notes ?? ''),
                    'created_by'       => \App\Core\Session::getUserId(),
                    'transacted_at'    => $paidAtTimestamp,
                ]);
            }

            $this->auditLog->record('payment_received', 'payments', $paymentId, null, [
                'invoice_id' => $invoiceId,
                'amount'     => $amount,
                'method'     => $paymentMethod
            ]);

            if (!$inTx) {
                $pdo->commit();
            }
            $payment = $this->paymentModel->find($paymentId);
            try {
                $emailResult = $this->emailService->sendPaymentSuccessful($payment, $invoice);
                $payment['email_sent'] = !empty($emailResult['success']);
            } catch (\Throwable $e) {
                Logger::warning("Could not send payment successful email: " . $e->getMessage());
                $payment['email_sent'] = false;
            }
            return $payment;
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Payment allocation failed: " . $e->getMessage());
            throw new RuntimeException("Payment processing failed: " . $e->getMessage());
        }
    }
}
