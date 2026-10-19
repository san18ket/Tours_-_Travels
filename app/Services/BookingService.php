<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\AuditLog;
use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class BookingService
{
    private Booking $bookingModel;
    private Tour $tourModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->tourModel = new Tour();
        $this->auditLog = new AuditLog();
    }

    /**
     * Compute booking price breakdown centrally
     */
    public function calculatePricing(?int $tourId, int $passengerCount, float $customSubtotal = 0.0, float $discount = 0.0, ?float $customTaxRate = null): array
    {
        $subtotal = $customSubtotal;

        if ($tourId) {
            $tour = $this->tourModel->find($tourId);
            if ($tour) {
                $subtotal = (float)$tour['base_price'];
                // If passengers exceed 4, small extra per-passenger charge
                if ($passengerCount > 4) {
                    $subtotal += ($passengerCount - 4) * ($subtotal * 0.10);
                }
            }
        }

        $discount = max(0.0, min($discount, $subtotal));
        $taxableAmount = max(0.0, $subtotal - $discount);
        $taxRate = $customTaxRate !== null ? max(0.0, (float)$customTaxRate) : 5.0;
        $taxAmount = round(($taxableAmount * $taxRate) / 100, 2);
        $totalAmount = round($taxableAmount + $taxAmount, 2);

        return [
            'subtotal'        => round($subtotal, 2),
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'discount_amount' => round($discount, 2),
            'total_amount'    => $totalAmount,
        ];
    }

    /**
     * Create booking with idempotency and transaction protection
     */
    public function createBooking(array $data): array
    {
        $pdo = Database::getConnection();

        // Check idempotency token
        if (!empty($data['idempotency_token'])) {
            $existing = $this->bookingModel->findBy('idempotency_token', $data['idempotency_token']);
            if ($existing) {
                Logger::info("Duplicate booking request prevented by idempotency token: {$data['idempotency_token']}");
                return $existing;
            }
        }

        $tourId = !empty($data['tour_id']) ? (int)$data['tour_id'] : null;
        $passengers = max(1, (int)($data['passenger_count'] ?? 1));

        $customTaxRate = null;
        if (isset($data['include_gst']) && empty($data['include_gst'])) {
            $customTaxRate = 0.0;
        } elseif (isset($data['gst_rate'])) {
            $customTaxRate = (float)$data['gst_rate'];
        }

        $pricing = $this->calculatePricing(
            $tourId,
            $passengers,
            (float)($data['custom_subtotal'] ?? 0.0),
            (float)($data['discount_amount'] ?? 0.0),
            $customTaxRate
        );

        $bookingCode = 'VG-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $pdo->beginTransaction();
        try {
            $bookingId = $this->bookingModel->create([
                'booking_code'        => $bookingCode,
                'customer_id'         => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
                'customer_profile_id' => !empty($data['customer_profile_id']) ? (int)$data['customer_profile_id'] : null,
                'tour_id'             => $tourId,
                'pickup_address'    => $data['pickup_address'],
                'drop_address'      => $data['drop_address'],
                'start_date'        => $data['start_date'],
                'end_date'          => $data['end_date'],
                'pickup_time'       => $data['pickup_time'] ?? '09:00:00',
                'passenger_count'   => $passengers,
                'special_requests'  => $data['special_requests'] ?? null,
                'subtotal'          => $pricing['subtotal'],
                'tax_amount'        => $pricing['tax_amount'],
                'discount_amount'   => $pricing['discount_amount'],
                'total_amount'      => $pricing['total_amount'],
                'status'            => 'pending',
                'idempotency_token' => $data['idempotency_token'] ?? null,
            ]);

            $this->auditLog->record('booking_created', 'bookings', $bookingId, null, [
                'booking_code' => $bookingCode,
                'total_amount' => $pricing['total_amount']
            ]);

            $pdo->commit();
            return $this->bookingModel->find($bookingId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Failed to create booking: " . $e->getMessage());
            throw new RuntimeException("Could not create booking reservation. Please try again.");
        }
    }

    public function updateStatus(int $bookingId, string $status, ?string $reason = null): bool
    {
        $validStatuses = ['pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            throw new RuntimeException("Invalid booking status transition.");
        }

        $booking = $this->bookingModel->find($bookingId);
        if (!$booking) {
            throw new RuntimeException("Booking not found.");
        }

        $oldStatus = $booking['status'];
        $updated = $this->bookingModel->update($bookingId, ['status' => $status]);

        if ($updated) {
            $this->auditLog->record('booking_status_updated', 'bookings', $bookingId, ['status' => $oldStatus], ['status' => $status, 'reason' => $reason]);
        }

        return $updated;
    }
}
