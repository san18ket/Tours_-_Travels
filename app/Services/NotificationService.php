<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use App\Core\Logger;
use App\Core\Database;
use PDO;

class NotificationService
{
    private Notification $notificationModel;
    private AuditLog $auditLog;
    private Employee $employeeModel;
    private User $userModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->auditLog = new AuditLog();
        $this->employeeModel = new Employee();
        $this->userModel = new User();
    }

    /**
     * Dispatch a professional duty assignment notification to the assigned chauffeur/employee
     */
    public function notifyDriverTripAssigned(array $dispatch, array $booking, array $vehicle, array $driverProfile, ?int $assignedByUserId = null): array
    {
        $pdo = Database::getConnection();

        // 1. Resolve Chauffeur & Employee details
        $driverUserId = !empty($driverProfile['user_id']) ? (int)$driverProfile['user_id'] : null;
        $driverEmployeeId = !empty($driverProfile['employee_id']) ? (int)$driverProfile['employee_id'] : null;

        $employee = null;
        if ($driverEmployeeId) {
            $employee = $this->employeeModel->find($driverEmployeeId);
        }
        if (!$employee && $driverUserId) {
            $employee = $this->employeeModel->findBy('user_id', $driverUserId);
            if ($employee && !$driverEmployeeId) {
                $driverEmployeeId = (int)$employee['id'];
            }
        }

        $user = null;
        if ($driverUserId) {
            $user = $this->userModel->find($driverUserId);
        }

        // Determine employee recipient identity
        $employeeCode = $employee['employee_code'] ?? 'EMP-CHAUFFEUR';
        $recipientName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        if (empty($recipientName)) {
            $recipientName = $user['name'] ?? 'Chauffeur';
        }

        $recipientPhone = $employee['phone'] ?? $user['phone'] ?? $driverProfile['emergency_contact'] ?? '';
        $recipientEmail = $employee['email'] ?? $user['email'] ?? '';

        // Formatted Date & Time
        $startDateRaw = $booking['start_date'] ?? date('Y-m-d H:i:s');
        $formattedDate = date('d M Y, h:i A', strtotime($startDateRaw));

        $dispatchCode = $dispatch['dispatch_code'] ?? 'DSP-PENDING';
        $bookingCode  = $booking['booking_code'] ?? 'BKG-TOUR';
        $pickupLoc    = $booking['pickup_address'] ?? 'Designated Pickup Location';
        $dropLoc      = $booking['drop_address'] ?? 'Destination';
        $vehicleInfo  = trim(($vehicle['make_model'] ?? 'Fleet Vehicle') . ' (' . ($vehicle['vehicle_number'] ?? 'N/A') . ')');
        $customerName = $booking['customer_name'] ?? 'Guest Passenger';
        $customerPhone = $booking['customer_phone'] ?? 'N/A';
        $guestCount   = (int)($booking['passenger_count'] ?? 1);

        // 2. Build Executive Professional In-App Message
        $notifTitle = "🚍 New Trip Assignment: {$dispatchCode}";
        $notifMessage = "Dear {$recipientName},\n"
            . "You have been deployed for Trip Duty [{$dispatchCode}] for Reservation [{$bookingCode}].\n\n"
            . "• Route: {$pickupLoc} → {$dropLoc}\n"
            . "• Reporting Time: {$formattedDate}\n"
            . "• Assigned Fleet: {$vehicleInfo}\n"
            . "• Guest: {$customerName} ({$guestCount} guests) · Ph: {$customerPhone}\n\n"
            . "Please access your Chauffeur Portal to review your itinerary and accept duty.";

        // Structured payload for interactive UI components
        $payload = [
            'dispatch_id'    => (int)($dispatch['id'] ?? 0),
            'dispatch_code'  => $dispatchCode,
            'booking_id'     => (int)($booking['id'] ?? 0),
            'booking_code'   => $bookingCode,
            'pickup_address' => $pickupLoc,
            'drop_address'   => $dropLoc,
            'reporting_time' => $formattedDate,
            'vehicle_name'   => $vehicle['make_model'] ?? 'Fleet Vehicle',
            'vehicle_number' => $vehicle['vehicle_number'] ?? 'N/A',
            'customer_name'  => $customerName,
            'customer_phone' => $customerPhone,
            'passenger_count'=> $guestCount,
            'recipient_name' => $recipientName,
            'employee_code'  => $employeeCode,
            'recipient_phone'=> $recipientPhone,
        ];

        // 3. Persist In-App Notification
        $notifId = $this->notificationModel->create([
            'user_id'        => $driverUserId,
            'employee_id'    => $driverEmployeeId,
            'title'          => $notifTitle,
            'message'        => $notifMessage,
            'type'           => 'trip_assigned',
            'reference_type' => 'dispatches',
            'reference_id'   => (int)($dispatch['id'] ?? 0),
            'channel'        => 'in_app',
            'data'           => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_read'        => 0,
        ]);

        // 4. Build Professional SMS / WhatsApp Dispatch Text
        $compTitle = strtoupper(function_exists('company_display_name') ? company_display_name() : 'TOURS & TRAVELS');
        $smsText = "{$compTitle} — TRIP DUTY DISPATCH\n"
            . "Hello {$recipientName} ({$employeeCode}),\n"
            . "You have been assigned to Trip Duty: {$dispatchCode} (Booking: {$bookingCode})\n\n"
            . "• Route: {$pickupLoc} -> {$dropLoc}\n"
            . "• Date/Time: {$formattedDate}\n"
            . "• Vehicle: {$vehicleInfo}\n"
            . "• Passenger: {$customerName} ({$guestCount} Guests) | Contact: {$customerPhone}\n\n"
            . "Please log in to your Chauffeur Console to accept this duty:\n"
            . url('/driver/trips/' . ($dispatch['id'] ?? ''));

        // 5. Send & Log SMS / WhatsApp Dispatch
        $this->dispatchSmsNotification($recipientPhone, $smsText, [
            'dispatch_id'   => $dispatch['id'] ?? null,
            'employee_code' => $employeeCode,
            'driver_id'     => $driverProfile['id'] ?? null,
        ]);

        // 6. Record Audit Log Entry
        $this->auditLog->record(
            'trip_notification_dispatched',
            'dispatches',
            (int)($dispatch['id'] ?? 0),
            null,
            [
                'recipient_employee_id' => $driverEmployeeId,
                'recipient_user_id'     => $driverUserId,
                'recipient_name'        => $recipientName,
                'recipient_phone'       => $recipientPhone,
                'notification_id'       => $notifId,
                'dispatch_code'         => $dispatchCode,
                'booking_code'          => $bookingCode,
            ]
        );

        return [
            'success'         => true,
            'notification_id' => $notifId,
            'employee_name'   => $recipientName,
            'employee_code'   => $employeeCode,
            'employee_phone'  => $recipientPhone,
            'employee_email'  => $recipientEmail,
            'sms_preview'     => $smsText,
            'in_app_title'    => $notifTitle,
        ];
    }

    /**
     * Dispatch & Log SMS/WhatsApp Notification
     */
    public function dispatchSmsNotification(string $recipientPhone, string $message, array $context = []): bool
    {
        $logDir = dirname(dirname(__DIR__)) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/sms_notifications.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';

        $entry = "================================================================================\n"
               . "[{$timestamp}] OUTGOING SMS/WHATSAPP DISPATCH\n"
               . "Recipient: " . ($recipientPhone ?: 'Not Provided') . " | Context:{$contextStr}\n"
               . "--------------------------------------------------------------------------------\n"
               . $message . "\n"
               . "================================================================================\n\n";

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        Logger::info("Chauffeur SMS notification dispatched to {$recipientPhone}");
        return true;
    }

    /**
     * Get recent notifications for driver portal
     */
    public function getDriverNotifications(int $userId, ?int $employeeId = null, int $limit = 5): array
    {
        return $this->notificationModel->getForRecipient($userId, $employeeId, $limit);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(int $userId, ?int $employeeId = null): int
    {
        return $this->notificationModel->countUnread($userId, $employeeId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id, ?int $userId = null): bool
    {
        return $this->notificationModel->markAsRead($id, $userId);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(int $userId, ?int $employeeId = null): bool
    {
        return $this->notificationModel->markAllAsRead($userId, $employeeId);
    }
}
