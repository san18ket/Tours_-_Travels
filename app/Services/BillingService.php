<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\AuditLog;
use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class BillingService
{
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private Booking $bookingModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
        $this->auditLog = new AuditLog();
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

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $invoiceId = $this->invoiceModel->create([
                'invoice_number'  => $invoiceNumber,
                'booking_id'      => $bookingId,
                'customer_id'     => $booking['customer_id'],
                'subtotal'        => $booking['subtotal'],
                'tax_rate'        => 5.00,
                'tax_amount'      => $booking['tax_amount'],
                'discount_amount' => $booking['discount_amount'],
                'total_amount'    => $booking['total_amount'],
                'paid_amount'     => 0.00,
                'payment_status'  => 'unpaid',
                'due_date'        => $booking['start_date'],
                'is_immutable'    => 1,
                'issued_at'       => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record('invoice_issued', 'invoices', $invoiceId, null, [
                'invoice_number' => $invoiceNumber,
                'total_amount'   => $booking['total_amount']
            ]);

            $pdo->commit();
            return $this->invoiceModel->find($invoiceId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Failed to issue invoice: " . $e->getMessage());
            throw new RuntimeException("Could not generate tax invoice: " . $e->getMessage());
        }
    }

    /**
     * Record payment allocation with strict idempotency and transaction locking
     */
    public function recordPayment(int $invoiceId, float $amount, string $paymentMethod, ?string $transactionId, string $idempotencyKey, ?string $notes = null): array
    {
        if ($amount <= 0.0) {
            throw new RuntimeException("Payment amount must be greater than zero.");
        }

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
        $newPaid = round($currentPaid + $amount, 2);

        $newStatus = 'unpaid';
        if ($newPaid >= $total) {
            $newStatus = 'paid';
        } elseif ($newPaid > 0) {
            $newStatus = 'partially_paid';
        }

        $paymentRef = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $paymentId = $this->paymentModel->create([
                'payment_reference' => $paymentRef,
                'invoice_id'        => $invoiceId,
                'amount'            => $amount,
                'payment_method'    => $paymentMethod,
                'transaction_id'    => $transactionId,
                'status'            => 'success',
                'idempotency_key'   => $idempotencyKey,
                'notes'             => $notes,
                'paid_at'           => date('Y-m-d H:i:s'),
            ]);

            $this->invoiceModel->update($invoiceId, [
                'paid_amount'    => $newPaid,
                'payment_status' => $newStatus,
            ]);

            $this->auditLog->record('payment_received', 'payments', $paymentId, null, [
                'invoice_id' => $invoiceId,
                'amount'     => $amount,
                'method'     => $paymentMethod
            ]);

            $pdo->commit();
            return $this->paymentModel->find($paymentId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Payment allocation failed: " . $e->getMessage());
            throw new RuntimeException("Payment processing failed: " . $e->getMessage());
        }
    }
}
