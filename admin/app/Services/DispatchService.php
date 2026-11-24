<?php
namespace App\Services;

use App\Models\Dispatch;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\AuditLog;
use App\Services\NotificationService;
use App\Core\Database;
use App\Core\Logger;
use App\Services\EmailService;
use RuntimeException;

class DispatchService
{
    private Dispatch $dispatchModel;
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private AuditLog $auditLog;
    private NotificationService $notificationService;
    public EmailService $emailService;

    public function __construct()
    {
        $this->dispatchModel = new Dispatch();
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->auditLog = new AuditLog();
        $this->notificationService = new NotificationService();
        $this->emailService = new EmailService();
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
    /**
     * Create dispatch alias for programmatic API compatibility
     */
    public function createDispatch(array $data): array
    {
        $amount = null;
        if (isset($data['driver_trip_amount']) && $data['driver_trip_amount'] !== '' && $data['driver_trip_amount'] !== null) {
            $amount = (float)$data['driver_trip_amount'];
        }
        return $this->assignBooking(
            (int)$data['booking_id'],
            (int)$data['vehicle_id'],
            (int)$data['driver_id'],
            $data['assigned_by'] ?? null,
            $amount
        );
    }

    public function assignBooking(int $bookingId, int $arg2, int $arg3, ?int $assignedByUserId = null, ?float $driverTripAmount = null): array
    {
        $assignedByUserId = $assignedByUserId ?? (\App\Core\Session::getUserId() ?: 1);

        $vCandidate = $this->vehicleModel->find($arg2);
        $dCandidate = $this->driverModel->find($arg3);

        if ($vCandidate && $dCandidate) {
            $vehicleId = $arg2;
            $driverId = $arg3;
        } else {
            $vCandidate2 = $this->vehicleModel->find($arg3);
            $dCandidate2 = $this->driverModel->find($arg2);
            if ($vCandidate2 && $dCandidate2) {
                $vehicleId = $arg3;
                $driverId = $arg2;
            } else {
                $vehicleId = $arg2;
                $driverId = $arg3;
            }
        }

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
            throw new RuntimeException("Driver is currently executing another active trip.");
        }

        $existingDispatch = $this->dispatchModel->findByBookingId($bookingId);
        if ($existingDispatch && !in_array($existingDispatch['status'], ['cancelled'], true)) {
            throw new RuntimeException("A dispatch record already exists for this booking.");
        }

        $dispatchCode = 'DSP-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        // Calculate response deadline (2 hours default, or min 15 mins before pickup if upcoming)
        $nowTs = time();
        $responseDeadline = date('Y-m-d H:i:s', $nowTs + 7200);
        if (!empty($booking['start_date'])) {
            $pickupTs = strtotime($booking['start_date']);
            if ($pickupTs > $nowTs && ($pickupTs - $nowTs) < 7200) {
                $responseDeadline = date('Y-m-d H:i:s', max($nowTs + 900, $pickupTs - 1800));
            }
        }

        $pdo->beginTransaction();
        try {
            $dispatchId = $this->dispatchModel->create([
                'dispatch_code'           => $dispatchCode,
                'booking_id'              => $bookingId,
                'vehicle_id'              => $vehicleId,
                'driver_id'               => $driverId,
                'assigned_by'             => $assignedByUserId,
                'driver_trip_amount'      => $driverTripAmount !== null ? round($driverTripAmount, 2) : null,
                'status'                  => 'assigned',
                'start_odometer'          => null,
                'response_deadline'       => $responseDeadline,
                'driver_response_at'      => null,
                'unable_to_accept_reason' => null,
                'customer_notified_at'    => null,
                'last_reminded_at'        => null,
            ]);

            $this->bookingModel->update($bookingId, ['status' => 'assigned']);
            $this->vehicleModel->update($vehicleId, ['status' => 'on_trip']);
            $this->driverModel->update($driverId, ['duty_status' => 'on_trip']);

            $this->recordStatusHistory($dispatchId, null, 'assigned', $assignedByUserId, null, null, 'Initial dispatch assignment — Awaiting driver acceptance');

            $this->auditLog->record('dispatch_assigned', 'dispatches', $dispatchId, null, [
                'booking_id'         => $bookingId,
                'vehicle_id'         => $vehicleId,
                'driver_id'          => $driverId,
                'driver_trip_amount' => $driverTripAmount !== null ? round($driverTripAmount, 2) : 'Not specified',
                'response_deadline'  => $responseDeadline,
            ]);

            $pdo->commit();

            $dispatch = $this->dispatchModel->find($dispatchId);

            // Dispatch professional duty assignment notification to this particular driver/employee
            try {
                $notifResult = $this->notificationService->notifyDriverTripAssigned(
                    $dispatch,
                    $booking,
                    $vehicle,
                    $driver,
                    $assignedByUserId
                );
                $dispatch['notification_info'] = $notifResult;
            } catch (\Throwable $ne) {
                Logger::error("Failed to send driver trip assignment notification: " . $ne->getMessage());
            }

            // Send assignment notification to the Driver ONLY.
            // Customer confirmation is intentionally withheld until driver accepts.
            try {
                $emailResults = $this->emailService->sendDriverAssigned($dispatch, $booking, $vehicle, $driver);
                $dispatch['email_results'] = $emailResults;
            } catch (\Throwable $e) {
                Logger::warning("Could not dispatch driver assignment email: " . $e->getMessage());
            }

            return $dispatch;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Dispatch assignment failed: " . $e->getMessage());
            throw new RuntimeException("Failed to assign fleet and driver. " . $e->getMessage());
        }
    }


    public function sendTripReminder(int $dispatchId): array
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Dispatch not found.");
        }
        $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
        $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
        $driver = $this->driverModel->find((int)$dispatch['driver_id']);

        return $this->emailService->sendTripReminder($dispatch, $booking, $vehicle, $driver);
    }

    /**
     * Driver Acceptance with Concurrency Locking & Idempotent Customer Confirmation
     */
    public function driverAccept(int $dispatchId, int $driverProfileId): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Concurrency lock
            $stmt = $pdo->prepare("SELECT * FROM `dispatches` WHERE `id` = ? FOR UPDATE");
            $stmt->execute([$dispatchId]);
            $dispatch = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
                $pdo->rollBack();
                throw new RuntimeException("Unauthorized: This trip assignment does not belong to you.");
            }

            // Prevent duplicate acceptance / idempotent return
            if ($dispatch['status'] === 'driver_accepted') {
                $pdo->commit();
                return true;
            }

            if ($dispatch['status'] !== 'assigned') {
                $pdo->rollBack();
                throw new RuntimeException("Invalid status transition: Trip is already '{$dispatch['status']}'.");
            }

            $oldStatus = $dispatch['status'];
            $now = date('Y-m-d H:i:s');

            $updateStmt = $pdo->prepare("UPDATE `dispatches` SET `status` = 'driver_accepted', `driver_response_at` = ?, `customer_notified_at` = COALESCE(`customer_notified_at`, ?), `updated_at` = ? WHERE `id` = ?");
            $updateStmt->execute([$now, $now, $now, $dispatchId]);

            $this->recordStatusHistory($dispatchId, $oldStatus, 'driver_accepted', null, null, null, 'Accepted by driver via driver portal');
            $this->auditLog->record('trip_accepted_by_driver', 'dispatches', $dispatchId, ['status' => $oldStatus], [
                'status'             => 'driver_accepted',
                'driver_response_at' => $now
            ]);

            $pdo->commit();

            // Refetch fresh record
            $refreshedDispatch = $this->dispatchModel->find($dispatchId);

            // 1. Notify Owner / Office Operations
            try {
                $driverProfile = $this->driverModel->find($driverProfileId);
                if ($driverProfile) {
                    $this->notificationService->notifyOfficeDriverAction('accepted', $refreshedDispatch, $driverProfile);
                }
            } catch (\Throwable $ae) {
                Logger::warning("Office notification skipped for trip accept: " . $ae->getMessage());
            }

            // 2. Authoritative Customer Confirmation (Idempotent: once only)
            if (empty($dispatch['customer_notified_at'])) {
                try {
                    $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
                    $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
                    $driverProfile = $driverProfile ?? $this->driverModel->find($driverProfileId);

                    if ($booking && $vehicle && $driverProfile) {
                        $this->emailService->sendCustomerDriverConfirmation($refreshedDispatch, $booking, $vehicle, $driverProfile);
                    }
                } catch (\Throwable $ce) {
                    // Resilient: Driver acceptance remains valid even if email transport fails
                    Logger::error("Customer confirmation email failed for dispatch {$dispatchId}: " . $ce->getMessage());
                }
            }

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Driver Unable to Accept Action with Reason & Immediate Office Notification
     */
    public function driverUnableToAccept(int $dispatchId, int $driverProfileId, ?string $reason = null): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM `dispatches` WHERE `id` = ? FOR UPDATE");
            $stmt->execute([$dispatchId]);
            $dispatch = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
                $pdo->rollBack();
                throw new RuntimeException("Unauthorized: This trip assignment does not belong to you.");
            }

            // Idempotent return if already recorded as unable to accept
            if ($dispatch['status'] === 'unable_to_accept') {
                $pdo->commit();
                return true;
            }

            if ($dispatch['status'] !== 'assigned') {
                $pdo->rollBack();
                throw new RuntimeException("Invalid status transition: Cannot decline trip with status '{$dispatch['status']}'.");
            }

            $oldStatus = $dispatch['status'];
            $now = date('Y-m-d H:i:s');
            $cleanReason = $reason ? trim(substr($reason, 0, 255)) : 'Schedule conflict / Unavailable';

            $updateStmt = $pdo->prepare("UPDATE `dispatches` SET `status` = 'unable_to_accept', `driver_response_at` = ?, `unable_to_accept_reason` = ?, `updated_at` = ? WHERE `id` = ?");
            $updateStmt->execute([$now, $cleanReason, $now, $dispatchId]);

            // Release driver back to on_duty so driver is available for other duties
            $this->driverModel->update($driverProfileId, ['duty_status' => 'on_duty']);

            $this->recordStatusHistory($dispatchId, $oldStatus, 'unable_to_accept', null, null, null, "Driver unable to accept: {$cleanReason}");
            $this->auditLog->record('trip_declined_by_driver', 'dispatches', $dispatchId, ['status' => $oldStatus], [
                'status'                  => 'unable_to_accept',
                'unable_to_accept_reason' => $cleanReason,
                'driver_response_at'      => $now
            ]);

            $pdo->commit();

            // Customer confirmation remains strictly unsent (customer_notified_at is null)

            // Notify Office / Operations immediately: Reassignment Required
            try {
                $driverProfile = $this->driverModel->find($driverProfileId);
                if ($driverProfile) {
                    $refreshedDispatch = $this->dispatchModel->find($dispatchId);
                    $this->notificationService->notifyOfficeDriverAction('unable_to_accept', $refreshedDispatch, $driverProfile, $cleanReason);
                }
            } catch (\Throwable $ne) {
                Logger::error("Failed to notify office of driver unable to accept: " . $ne->getMessage());
            }

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Remind Driver with 15-minute cooldown and audit logging
     */
    public function remindDriver(int $dispatchId, int $userId): array
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Dispatch assignment not found.");
        }

        if ($dispatch['status'] !== 'assigned') {
            throw new RuntimeException("Reminder can only be sent for trips awaiting driver response.");
        }

        // Cooldown: at least 15 minutes between reminders
        if (!empty($dispatch['last_reminded_at'])) {
            $lastReminded = strtotime($dispatch['last_reminded_at']);
            $cooldownSec = 900; // 15 mins
            $remaining = ($lastReminded + $cooldownSec) - time();
            if ($remaining > 0) {
                $remainingMins = ceil($remaining / 60);
                throw new RuntimeException("Reminder was sent recently. Please wait {$remainingMins} minute(s) before sending another reminder.");
            }
        }

        $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
        $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
        $driver = $this->driverModel->find((int)$dispatch['driver_id']);

        $now = date('Y-m-d H:i:s');
        $this->dispatchModel->update($dispatchId, ['last_reminded_at' => $now]);

        // Dispatch in-app and email reminder to the driver
        try {
            $this->notificationService->notifyDriverTripReminder($dispatch, $booking, $vehicle, $driver, $userId);
        } catch (\Throwable $ne) {
            Logger::warning("Driver in-app reminder failed: " . $ne->getMessage());
        }

        try {
            $this->emailService->sendDriverAssignmentReminder($dispatch, $booking, $vehicle, $driver);
        } catch (\Throwable $ee) {
            Logger::warning("Driver email reminder failed: " . $ee->getMessage());
        }

        $this->auditLog->record('driver_reminded', 'dispatches', $dispatchId, null, [
            'last_reminded_at' => $now,
            'reminded_by'      => $userId,
        ], $userId);

        return [
            'success'          => true,
            'message'          => 'Reminder dispatched to driver successfully.',
            'last_reminded_at' => $now,
        ];
    }

    /**
     * Reassign Trip to New Driver (Preserves assignment history & defers customer email)
     */
    public function reassignDriver(int $dispatchId, int $newDriverId, ?int $newVehicleId = null, ?int $assignedByUserId = null): array
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Dispatch assignment not found.");
        }

        if (!in_array($dispatch['status'], ['assigned', 'unable_to_accept'], true)) {
            throw new RuntimeException("Cannot reassign trip in status '{$dispatch['status']}'.");
        }

        $oldDriverId = (int)$dispatch['driver_id'];
        $oldVehicleId = (int)$dispatch['vehicle_id'];
        $targetVehicleId = $newVehicleId ?: $oldVehicleId;

        $newDriver = $this->driverModel->find($newDriverId);
        if (!$newDriver || $newDriver['duty_status'] === 'on_trip') {
            throw new RuntimeException("Selected driver is unavailable or already executing an active trip.");
        }

        $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
        $targetVehicle = $this->vehicleModel->find($targetVehicleId);

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Release old driver back to on_duty if still on_trip
            $this->driverModel->update($oldDriverId, ['duty_status' => 'on_duty']);

            // Update fleet if vehicle changed
            if ($targetVehicleId !== $oldVehicleId) {
                $this->vehicleModel->update($oldVehicleId, ['status' => 'available']);
                $this->vehicleModel->update($targetVehicleId, ['status' => 'on_trip']);
            }

            // Assign new driver to on_trip
            $this->driverModel->update($newDriverId, ['duty_status' => 'on_trip']);

            $now = date('Y-m-d H:i:s');
            $newDeadline = date('Y-m-d H:i:s', time() + 7200);

            $this->dispatchModel->update($dispatchId, [
                'driver_id'               => $newDriverId,
                'vehicle_id'              => $targetVehicleId,
                'status'                  => 'assigned',
                'driver_response_at'      => null,
                'unable_to_accept_reason' => null,
                'response_deadline'       => $newDeadline,
                'customer_notified_at'    => null, // Reset: customer confirmation deferred until new driver accepts
                'last_reminded_at'        => null,
                'assigned_by'             => $assignedByUserId ?: $dispatch['assigned_by'],
            ]);

            $this->recordStatusHistory(
                $dispatchId,
                $dispatch['status'],
                'assigned',
                $assignedByUserId,
                null,
                null,
                "Reassigned from Driver #{$oldDriverId} to Driver #{$newDriverId}"
            );

            $this->auditLog->record('dispatch_reassigned', 'dispatches', $dispatchId, [
                'previous_driver_id' => $oldDriverId,
                'previous_status'    => $dispatch['status'],
            ], [
                'new_driver_id'  => $newDriverId,
                'new_vehicle_id' => $targetVehicleId,
                'status'         => 'assigned',
            ], $assignedByUserId);

            $pdo->commit();

            $refreshedDispatch = $this->dispatchModel->find($dispatchId);

            // Notify new driver
            try {
                $this->notificationService->notifyDriverTripAssigned(
                    $refreshedDispatch,
                    $booking,
                    $targetVehicle,
                    $newDriver,
                    $assignedByUserId
                );
            } catch (\Throwable $ne) {
                Logger::error("Reassignment driver notification failed: " . $ne->getMessage());
            }

            try {
                $this->emailService->sendDriverAssigned(
                    $refreshedDispatch,
                    $booking,
                    $targetVehicle,
                    $newDriver
                );
            } catch (\Throwable $ee) {
                Logger::warning("Reassignment driver email failed: " . $ee->getMessage());
            }

            return $refreshedDispatch;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function startTrip(int $dispatchId, int $driverProfileId, int $startOdometer, ?string $notes = null, ?array $evidenceData = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
            throw new RuntimeException("Unauthorized: Assignment mismatch.");
        }

        if (!in_array($dispatch['status'], ['assigned', 'driver_accepted'], true)) {
            throw new RuntimeException("Invalid status transition: Cannot start trip with status '{$dispatch['status']}'.");
        }

        $vehicle = $this->vehicleModel->find($dispatch['vehicle_id']);
        if ($vehicle && $startOdometer < (int)$vehicle['odometer_km']) {
            throw new RuntimeException("Start odometer ({$startOdometer} km) cannot be less than vehicle reading ({$vehicle['odometer_km']} km).");
        }

        $oldStatus = $dispatch['status'];
        $now = date('Y-m-d H:i:s');
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->dispatchModel->update($dispatchId, [
                'status'         => 'trip_started',
                'start_odometer' => $startOdometer,
                'start_time'     => $now,
                'driver_notes'   => $notes ?: $dispatch['driver_notes']
            ]);

            $this->bookingModel->update($dispatch['booking_id'], ['status' => 'in_progress']);

            // Update vehicle and driver status to on_trip
            $this->vehicleModel->update($dispatch['vehicle_id'], ['status' => 'on_trip']);
            $this->driverModel->update($driverProfileId, ['duty_status' => 'on_trip']);

            // Auto-activate or link tracking session
            $stmtSession = $pdo->prepare("SELECT id, dispatch_id FROM `tracking_sessions` WHERE `driver_id` = ? AND `status` = 'active' ORDER BY id DESC LIMIT 1");
            $stmtSession->execute([$driverProfileId]);
            $activeSession = $stmtSession->fetch(\PDO::FETCH_ASSOC);

            if ($activeSession) {
                if (empty($activeSession['dispatch_id']) || (int)$activeSession['dispatch_id'] !== $dispatchId) {
                    $pdo->prepare("UPDATE `tracking_sessions` SET `dispatch_id` = ?, `updated_at` = ? WHERE `id` = ?")
                        ->execute([$dispatchId, $now, $activeSession['id']]);
                }
            } else {
                $sessionUuid = bin2hex(random_bytes(16));
                $insertSession = $pdo->prepare("INSERT INTO `tracking_sessions` 
                    (`driver_id`, `dispatch_id`, `device_id`, `session_uuid`, `status`, `started_at`, `points_count`, `distance_km`, `created_at`, `updated_at`)
                    VALUES (?, ?, NULL, ?, 'active', ?, 0, 0.00, ?, ?)");
                $insertSession->execute([$driverProfileId, $dispatchId, $sessionUuid, $now, $now, $now]);
            }

            // Save optional pre-trip inspection evidence if provided
            if (!empty($evidenceData)) {
                try {
                    $evidenceService = new \App\Services\TripEvidenceService();
                    foreach ($evidenceData as $ev) {
                        if (!empty($ev['file_data']) && !empty($ev['type'])) {
                            try {
                                $evidenceService->storeEvidence(
                                    $dispatchId,
                                    $driverProfileId,
                                    $ev['type'],
                                    $ev['file_data'],
                                    $startOdometer,
                                    $ev['latitude'] ?? null,
                                    $ev['longitude'] ?? null,
                                    $ev['notes'] ?? null
                                );
                            } catch (\Throwable $evItemError) {
                                Logger::warning("Pre-trip inspection evidence [{$ev['type']}] store skipped for dispatch #{$dispatchId}: " . $evItemError->getMessage());
                            }
                        }
                    }
                } catch (\Throwable $evBatchError) {
                    Logger::warning("Pre-trip inspection evidence batch failed for dispatch #{$dispatchId}: " . $evBatchError->getMessage());
                }
            }

            $this->recordStatusHistory($dispatchId, $oldStatus, 'trip_started', null, null, null, "Trip started at odometer {$startOdometer} km");

            $this->auditLog->record('trip_started', 'dispatches', $dispatchId, null, [
                'start_odometer' => $startOdometer,
                'start_time'     => $now
            ]);

            $pdo->commit();

            try {
                $driverProfile = $this->driverModel->find($driverProfileId);
                if ($driverProfile) {
                    $this->notificationService->notifyOfficeDriverAction('started', $dispatch, $driverProfile);
                }
            } catch (\Throwable $se) {
                Logger::warning("Office notification skipped for trip start: " . $se->getMessage());
            }

            try {
                $refreshedDispatch = $this->dispatchModel->find($dispatchId);
                $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
                $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
                $driver = $this->driverModel->find((int)$dispatch['driver_id']);
                $this->emailService->sendTripStarted($refreshedDispatch, $booking, $vehicle, $driver);
            } catch (\Throwable $e) {
                Logger::warning("Could not dispatch trip started email: " . $e->getMessage());
            }

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to record trip commencement: " . $e->getMessage());
        }
    }

    public function completeTrip(int $dispatchId, int $driverProfileId, int $endOdometer, ?string $notes = null, ?array $evidenceData = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch || (int)$dispatch['driver_id'] !== $driverProfileId) {
            throw new RuntimeException("Unauthorized: Assignment mismatch.");
        }

        if ($dispatch['status'] !== 'trip_started') {
            throw new RuntimeException("Invalid status transition: Only active trips with status 'trip_started' can be completed.");
        }

        $startOdo = (int)($dispatch['start_odometer'] ?? 0);
        if ($endOdometer < $startOdo) {
            throw new RuntimeException("End odometer ({$endOdometer} km) cannot be less than trip start reading ({$startOdo} km).");
        }

        $oldStatus = $dispatch['status'];
        $tripAmount = $dispatch['driver_trip_amount'] !== null ? (float)$dispatch['driver_trip_amount'] : null;
        $driverPaid = (float)($dispatch['driver_paid_amount'] ?? 0.00);
        $payStatus = 'amount_not_specified';
        if ($tripAmount !== null) {
            if ($driverPaid >= $tripAmount) {
                $payStatus = 'paid';
            } elseif ($driverPaid > 0) {
                $payStatus = 'partially_paid';
            } elseif ($tripAmount > 0) {
                $payStatus = 'due';
            } else {
                $payStatus = 'paid';
            }
        }

        $now = date('Y-m-d H:i:s');
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->dispatchModel->update($dispatchId, [
                'status'                => 'completed',
                'end_odometer'          => $endOdometer,
                'end_time'              => $now,
                'driver_payment_status' => $payStatus,
                'driver_notes'          => $notes ? ($dispatch['driver_notes'] . "\n" . $notes) : $dispatch['driver_notes']
            ]);

            // Release vehicle and update odometer reading
            $this->vehicleModel->update($dispatch['vehicle_id'], [
                'status'      => 'available',
                'odometer_km' => $endOdometer
            ]);

            // Release driver back to on_duty
            $this->driverModel->update($driverProfileId, [
                'duty_status' => 'on_duty'
            ]);

            // Complete booking
            $this->bookingModel->update($dispatch['booking_id'], ['status' => 'completed']);

            // Auto-complete active GPS tracking sessions
            $pdo->prepare("UPDATE `tracking_sessions` SET `status` = 'completed', `ended_at` = ?, `updated_at` = ? 
                           WHERE `driver_id` = ? AND `status` = 'active'")
                ->execute([$now, $now, $driverProfileId]);

            // Save optional post-trip inspection evidence if provided
            if (!empty($evidenceData)) {
                try {
                    $evidenceService = new \App\Services\TripEvidenceService();
                    foreach ($evidenceData as $ev) {
                        if (!empty($ev['file_data']) && !empty($ev['type'])) {
                            try {
                                $evidenceService->storeEvidence(
                                    $dispatchId,
                                    $driverProfileId,
                                    $ev['type'],
                                    $ev['file_data'],
                                    $endOdometer,
                                    $ev['latitude'] ?? null,
                                    $ev['longitude'] ?? null,
                                    $ev['notes'] ?? null
                                );
                            } catch (\Throwable $evItemError) {
                                Logger::warning("Post-trip inspection evidence [{$ev['type']}] store skipped for dispatch #{$dispatchId}: " . $evItemError->getMessage());
                            }
                        }
                    }
                } catch (\Throwable $evBatchError) {
                    Logger::warning("Post-trip inspection evidence batch failed for dispatch #{$dispatchId}: " . $evBatchError->getMessage());
                }
            }

            $this->recordStatusHistory($dispatchId, $oldStatus, 'completed', null, null, null, "Completed at odometer {$endOdometer} km");

            $this->auditLog->record('trip_completed', 'dispatches', $dispatchId, null, [
                'end_odometer' => $endOdometer,
                'km_travelled' => ($endOdometer - $startOdo),
                'end_time'     => $now
            ]);

            $pdo->commit();

            try {
                $driverProfile = $this->driverModel->find($driverProfileId);
                if ($driverProfile) {
                    $this->notificationService->notifyOfficeDriverAction('completed', $dispatch, $driverProfile);
                }
            } catch (\Throwable $ce) {
                Logger::warning("Office notification skipped for trip completion: " . $ce->getMessage());
            }

            try {
                $refreshedDispatch = $this->dispatchModel->find($dispatchId);
                $booking = $this->bookingModel->find((int)$dispatch['booking_id']);
                $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
                $driver = $this->driverModel->find((int)$dispatch['driver_id']);
                $this->emailService->sendTripCompleted($refreshedDispatch, $booking, $vehicle, $driver);
            } catch (\Throwable $e) {
                Logger::warning("Could not dispatch trip completed email: " . $e->getMessage());
            }

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

            try {
                $driverProfile = $this->driverModel->find($dispatch['driver_id']);
                if ($driverProfile) {
                    $this->notificationService->notifyDriverTripCancelled($dispatch, $driverProfile, $reason);
                }
            } catch (\Throwable $ce) {
                Logger::warning("Driver cancellation notification skipped: " . $ce->getMessage());
            }

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to cancel trip: " . $e->getMessage());
        }
    }

    /**
     * Update Driver Trip Amount with audit history
     */
    public function updateDriverTripAmount(int $dispatchId, ?float $amount, ?int $updatedByUserId = null): bool
    {
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Trip assignment not found.");
        }

        $oldAmount = $dispatch['driver_trip_amount'] !== null ? (float)$dispatch['driver_trip_amount'] : null;
        $newAmount = $amount !== null ? round($amount, 2) : null;
        $paidAmount = (float)($dispatch['driver_paid_amount'] ?? 0.00);

        if ($oldAmount === $newAmount) {
            return true;
        }

        // Validate that new amount does not conflict with money already disbursed
        if ($newAmount !== null && $paidAmount > 0 && $newAmount < $paidAmount) {
            throw new RuntimeException("Cannot reduce driver trip amount to " . format_currency($newAmount) . " because " . format_currency($paidAmount) . " has already been paid to the driver. Please adjust or reverse payment records first.");
        }

        // Recalculate payment status based on trip status and payment history
        $isCompleted = ($dispatch['status'] === 'completed');
        $newPaymentStatus = 'not_due';
        if ($isCompleted) {
            if ($newAmount === null) {
                $newPaymentStatus = 'amount_not_specified';
            } elseif ($paidAmount >= $newAmount) {
                $newPaymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $newPaymentStatus = 'partially_paid';
            } elseif ($newAmount > 0) {
                $newPaymentStatus = 'due';
            } else {
                $newPaymentStatus = 'paid';
            }
        }

        $updated = $this->dispatchModel->update($dispatchId, [
            'driver_trip_amount'    => $newAmount,
            'driver_payment_status' => $newPaymentStatus,
        ]);

        if ($updated) {
            $oldLabel = $oldAmount !== null ? format_currency($oldAmount) : 'Not specified';
            $newLabel = $newAmount !== null ? format_currency($newAmount) : 'Not specified';
            $this->auditLog->record(
                'dispatch_amount_updated',
                'dispatches',
                $dispatchId,
                [
                    'driver_trip_amount'    => $oldLabel,
                    'driver_payment_status' => $dispatch['driver_payment_status'] ?? 'not_due'
                ],
                [
                    'driver_trip_amount'    => $newLabel,
                    'driver_payment_status' => $newPaymentStatus,
                    'driver_paid_amount'    => $paidAmount,
                ],
                $updatedByUserId
            );
        }

        return $updated;
    }
}
