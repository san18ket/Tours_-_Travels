<?php
namespace App\Services;

use App\Models\Dispatch;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\AuditLog;
use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class DispatchService
{
    private Dispatch $dispatchModel;
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->dispatchModel = new Dispatch();
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->auditLog = new AuditLog();
    }

    /**
     * Record transition to trip_status_history table
     */
    private function recordStatusHistory(int $dispatchId, ?string $fromStatus, string $toStatus, ?int $userId = null, ?float $lat = null, ?float $lng = null, ?string $notes = null): void
    {
        try {
            $pdo = Database::getConnection();
            $sql = "INSERT INTO `trip_status_history` (dispatch_id, from_status, to_status, changed_by_user_id, latitude, longitude, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dispatchId,
                $fromStatus,
                $toStatus,
                $userId,
                $lat,
                $lng,
                $notes,
                date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            Logger::error("Failed to insert into trip_status_history: " . $e->getMessage());
        }
    }

    /**
     * Dispatch fleet assignment with transaction safety
     */
    public function assignBooking(int $bookingId, int $vehicleId, int $driverId, int $assignedByUserId): array
    {
        $pdo = Database::getConnection();

        $booking = $this->bookingModel->find($bookingId);
        if (!$booking) {
            throw new RuntimeException("Booking not found.");
        }
        if ($booking['status'] === 'completed' || $booking['status'] === 'cancelled') {
            throw new RuntimeException("Cannot assign a {$booking['status']} booking.");
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle || $vehicle['status'] !== 'available') {
            throw new RuntimeException("Vehicle is currently unavailable or assigned to another duty.");
        }

        // Validate vehicle capacity vs passenger count
        if ((int)$vehicle['seating_capacity'] < (int)$booking['passenger_count']) {
            throw new RuntimeException("Vehicle seating capacity ({$vehicle['seating_capacity']}) is less than booking passenger count ({$booking['passenger_count']}).");
        }

        $driver = $this->driverModel->find($driverId);
        if (!$driver || $driver['duty_status'] === 'on_trip') {
            throw new RuntimeException("Chauffeur is currently executing another active trip.");
        }

        $existingDispatch = $this->dispatchModel->findByBookingId($bookingId);
        if ($existingDispatch && !in_array($existingDispatch['status'], ['cancelled'], true)) {
            throw new RuntimeException("A dispatch record already exists for this booking.");
        }

        $dispatchCode = 'DSP-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        $pdo->beginTransaction();
        try {
            $dispatchId = $this->dispatchModel->create([
                'dispatch_code'  => $dispatchCode,
                'booking_id'     => $bookingId,
                'vehicle_id'     => $vehicleId,
                'driver_id'      => $driverId,
                'assigned_by'    => $assignedByUserId,
                'status'         => 'assigned',
                'start_odometer' => $vehicle['odometer_km'],
            ]);

            $this->bookingModel->update($bookingId, ['status' => 'assigned']);
            $this->vehicleModel->update($vehicleId, ['status' => 'on_trip']);
            $this->driverModel->update($driverId, ['duty_status' => 'on_trip']);

            $this->recordStatusHistory($dispatchId, null, 'assigned', $assignedByUserId, null, null, 'Initial dispatch assignment');

            $this->auditLog->record('dispatch_assigned', 'dispatches', $dispatchId, null, [
                'booking_id' => $bookingId,
                'vehicle_id' => $vehicleId,
                'driver_id'  => $driverId
            ]);

            $pdo->commit();
            return $this->dispatchModel->find($dispatchId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Dispatch assignment failed: " . $e->getMessage());
            throw new RuntimeException("Failed to assign fleet and driver. " . $e->getMessage());
        }
    }

    public function driverAccept(int $dispatchId, int $driverProfileId): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
            throw new RuntimeException("Unauthorized: This trip assignment does not belong to you.");
        }

        $oldStatus = $dispatch['status'];
        $updated = $this->dispatchModel->update($dispatchId, ['status' => 'driver_accepted']);
        if ($updated) {
            $this->recordStatusHistory($dispatchId, $oldStatus, 'driver_accepted', null, null, null, 'Accepted by driver via mobile PWA');
            $this->auditLog->record('trip_accepted_by_driver', 'dispatches', $dispatchId, null, null);
        }
        return $updated;
    }

    public function startTrip(int $dispatchId, int $driverProfileId, int $startOdometer, ?string $notes = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
            throw new RuntimeException("Unauthorized: Assignment mismatch.");
        }

        $vehicle = $this->vehicleModel->find($dispatch['vehicle_id']);
        if ($vehicle && $startOdometer < (int)$vehicle['odometer_km']) {
            throw new RuntimeException("Start odometer ({$startOdometer} km) cannot be less than vehicle reading ({$vehicle['odometer_km']} km).");
        }

        $oldStatus = $dispatch['status'];
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->dispatchModel->update($dispatchId, [
                'status'         => 'trip_started',
                'start_odometer' => $startOdometer,
                'start_time'     => date('Y-m-d H:i:s'),
                'driver_notes'   => $notes
            ]);

            $this->bookingModel->update($dispatch['booking_id'], ['status' => 'in_progress']);

            $this->recordStatusHistory($dispatchId, $oldStatus, 'trip_started', null, null, null, "Trip started at odometer {$startOdometer} km");

            $this->auditLog->record('trip_started', 'dispatches', $dispatchId, null, [
                'start_odometer' => $startOdometer
            ]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to record trip commencement: " . $e->getMessage());
        }
    }

    public function completeTrip(int $dispatchId, int $driverProfileId, int $endOdometer, ?string $notes = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
            throw new RuntimeException("Unauthorized: Assignment mismatch.");
        }

        $startOdo = (int)($dispatch['start_odometer'] ?? 0);
        if ($endOdometer < $startOdo) {
            throw new RuntimeException("End odometer ({$endOdometer} km) cannot be less than trip start reading ({$startOdo} km).");
        }

        $oldStatus = $dispatch['status'];
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->dispatchModel->update($dispatchId, [
                'status'       => 'completed',
                'end_odometer' => $endOdometer,
                'end_time'     => date('Y-m-d H:i:s'),
                'driver_notes' => $notes ? ($dispatch['driver_notes'] . "\n" . $notes) : $dispatch['driver_notes']
            ]);

            // Release vehicle and update odometer reading
            $this->vehicleModel->update($dispatch['vehicle_id'], [
                'status'      => 'available',
                'odometer_km' => $endOdometer
            ]);

            // Release driver
            $this->driverModel->update($driverProfileId, [
                'duty_status' => 'on_duty'
            ]);

            // Complete booking
            $this->bookingModel->update($dispatch['booking_id'], ['status' => 'completed']);

            $this->recordStatusHistory($dispatchId, $oldStatus, 'completed', null, null, null, "Completed at odometer {$endOdometer} km");

            $this->auditLog->record('trip_completed', 'dispatches', $dispatchId, null, [
                'end_odometer' => $endOdometer,
                'km_travelled' => ($endOdometer - $startOdo)
            ]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to complete trip: " . $e->getMessage());
        }
    }

    /**
     * Cancel an active or pending trip assignment
     */
    public function cancelTrip(int $dispatchId, int $cancelledByUserId, ?string $reason = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Dispatch assignment not found.");
        }
        if ($dispatch['status'] === 'completed') {
            throw new RuntimeException("Cannot cancel an already completed trip.");
        }
        if ($dispatch['status'] === 'cancelled') {
            return true;
        }

        $oldStatus = $dispatch['status'];
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->dispatchModel->update($dispatchId, [
                'status'       => 'cancelled',
                'end_time'     => date('Y-m-d H:i:s'),
                'driver_notes' => $reason ? ($dispatch['driver_notes'] . "\n[Cancelled: {$reason}]") : $dispatch['driver_notes']
            ]);

            // Release vehicle back to available
            $this->vehicleModel->update($dispatch['vehicle_id'], ['status' => 'available']);

            // Release driver back to on_duty
            $this->driverModel->update($dispatch['driver_id'], ['duty_status' => 'on_duty']);

            // Reset booking status back to confirmed so it can be re-assigned if needed
            $this->bookingModel->update($dispatch['booking_id'], ['status' => 'confirmed']);

            $this->recordStatusHistory($dispatchId, $oldStatus, 'cancelled', $cancelledByUserId, null, null, $reason ?? 'Cancelled by office operations');

            $this->auditLog->record('dispatch_cancelled', 'dispatches', $dispatchId, ['status' => $oldStatus], [
                'status' => 'cancelled',
                'reason' => $reason
            ], $cancelledByUserId);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to cancel trip: " . $e->getMessage());
        }
    }
}
