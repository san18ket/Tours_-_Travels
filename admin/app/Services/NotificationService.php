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
     * Dispatch a professional duty assignment notification to the assigned driver/employee
     */
    public function notifyDriverTripAssigned(array $dispatch, array $booking, array $vehicle, array $driverProfile, ?int $assignedByUserId = null): array
    {
        $pdo = Database::getConnection();

        // 1. Resolve Driver & Employee details
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
        $employeeCode = $employee['employee_code'] ?? 'EMP-DRIVER';
        $recipientName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        if (empty($recipientName)) {
            $recipientName = $user['name'] ?? 'Driver';
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
        $amountLine   = (!empty($dispatch['driver_trip_amount'])) ? ("• Driver Trip Amount: " . format_currency((float)$dispatch['driver_trip_amount']) . "\n") : "";

        // 2. Build Executive Professional In-App Message
        $notifTitle = "🚍 New Trip Assignment: {$dispatchCode}";
        $notifMessage = "Dear {$recipientName},\n"
            . "You have been deployed for Trip Duty [{$dispatchCode}] for Reservation [{$bookingCode}].\n\n"
            . "• Route: {$pickupLoc} → {$dropLoc}\n"
            . "• Reporting Time: {$formattedDate}\n"
            . "• Assigned Fleet: {$vehicleInfo}\n"
            . "• Guest: {$customerName} ({$guestCount} guests) · Ph: {$customerPhone}\n"
            . $amountLine . "\n"
            . "Please access your Driver Portal to review your itinerary and accept duty.";

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
            'driver_trip_amount' => $dispatch['driver_trip_amount'] ?? null,
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
            . "Please log in to your Driver Console to accept this duty:\n"
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

        Logger::info("Driver SMS notification dispatched to {$recipientPhone}");
        return true;
    }

    private static array $requestUnreadCounts = [];

    /**
     * Clear request-scoped unread count cache
     */
    public static function clearRequestCache(): void
    {
        self::$requestUnreadCounts = [];
    }

    /**
     * Get recent notifications for driver portal
     */
    public function getDriverNotifications(int $userId, ?int $employeeId = null, int $limit = 5): array
    {
        return $this->notificationModel->getForRecipient($userId, $employeeId, $limit);
    }

    /**
     * Get unread notification count (Request-scoped cache eliminates duplicate queries)
     */
    public function getUnreadCount(int $userId, ?int $employeeId = null): int
    {
        $cacheKey = "{$userId}:" . ($employeeId ?? 0);
        if (!isset(self::$requestUnreadCounts[$cacheKey])) {
            self::$requestUnreadCounts[$cacheKey] = $this->notificationModel->countUnread($userId, $employeeId);
        }
        return self::$requestUnreadCounts[$cacheKey];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id, ?int $userId = null): bool
    {
        self::clearRequestCache();
        return $this->notificationModel->markAsRead($id, $userId);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(int $userId, ?int $employeeId = null): bool
    {
        self::clearRequestCache();
        return $this->notificationModel->markAllAsRead($userId, $employeeId);
    }

    /**
     * Notify Office / Admin / Dispatcher of Driver Response Actions
     */
    public function notifyOfficeDriverAction(string $action, array $dispatch, array $driverProfile, ?string $reason = null): array
    {
        $pdo = Database::getConnection();
        $dispatchCode = $dispatch['dispatch_code'] ?? ('DSP-' . ($dispatch['id'] ?? ''));
        $bookingCode = $dispatch['booking_code'] ?? 'Booking';

        $driverName = $driverProfile['name'] ?? ($driverProfile['first_name'] ?? 'Driver');
        if (empty($driverName) && !empty($driverProfile['user_id'])) {
            $u = $this->userModel->find((int)$driverProfile['user_id']);
            if ($u) {
                $driverName = $u['name'];
            }
        }

        if ($action === 'accepted') {
            $title = "Driver Accepted Trip Duty: {$dispatchCode}";
            $message = "Driver {$driverName} has accepted Trip Duty {$dispatchCode} (Booking: {$bookingCode}). Customer confirmation has been dispatched.";
            $type = 'trip_accepted';
        } elseif ($action === 'unable_to_accept') {
            $reasonText = !empty($reason) ? " Reason: {$reason}." : "";
            $title = "Driver Unable to Accept Duty: {$dispatchCode}";
            $message = "Driver {$driverName} is unable to accept Trip Duty {$dispatchCode} (Booking: {$bookingCode}).{$reasonText} Immediate reassignment is required.";
            $type = 'trip_unable_to_accept';
        } elseif ($action === 'started') {
            $title = "Trip Commenced: {$dispatchCode}";
            $message = "Driver {$driverName} has commenced Trip Duty {$dispatchCode}.";
            $type = 'trip_started';
        } elseif ($action === 'completed') {
            $title = "Trip Completed: {$dispatchCode}";
            $message = "Driver {$driverName} has completed Trip Duty {$dispatchCode}.";
            $type = 'trip_completed';
        } else {
            $title = "Trip Update: {$dispatchCode}";
            $message = "Status updated for Trip Duty {$dispatchCode} by {$driverName}.";
            $type = 'trip_update';
        }

        // Send in-app notification to all active admin and dispatcher users
        $stmt = $pdo->query("SELECT u.id, u.email, u.name FROM users u WHERE u.role IN ('admin', 'superadmin', 'owner', 'dispatcher') AND u.status = 'active'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifIds = [];
        $payload = [
            'dispatch_id'   => (int)($dispatch['id'] ?? 0),
            'dispatch_code' => $dispatchCode,
            'driver_id'     => (int)($driverProfile['id'] ?? 0),
            'driver_name'   => $driverName,
            'action'        => $action,
            'reason'        => $reason,
        ];

        foreach ($adminUsers as $admin) {
            $nId = $this->notificationModel->create([
                'user_id'        => (int)$admin['id'],
                'employee_id'    => null,
                'title'          => $title,
                'message'        => $message,
                'type'           => $type,
                'reference_type' => 'dispatches',
                'reference_id'   => (int)($dispatch['id'] ?? 0),
                'channel'        => 'in_app',
                'data'           => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_read'        => 0,
            ]);
            $notifIds[] = $nId;
        }

        // Record audit log
        $this->auditLog->record(
            'office_notified_' . $action,
            'dispatches',
            (int)($dispatch['id'] ?? 0),
            null,
            [
                'action'        => $action,
                'driver_name'   => $driverName,
                'reason'        => $reason,
                'notif_count'   => count($notifIds),
            ]
        );

        return [
            'success'   => true,
            'action'    => $action,
            'notif_ids' => $notifIds,
            'title'     => $title,
            'message'   => $message,
        ];
    }

    /**
     * Dispatch Trip Assignment Reminder to Driver
     */
    public function notifyDriverTripReminder(array $dispatch, array $booking, array $vehicle, array $driverProfile, ?int $remindedByUserId = null): array
    {
        $driverUserId = !empty($driverProfile['user_id']) ? (int)$driverProfile['user_id'] : null;
        $driverEmployeeId = !empty($driverProfile['employee_id']) ? (int)$driverProfile['employee_id'] : null;

        $user = $driverUserId ? $this->userModel->find($driverUserId) : null;
        $recipientName = $driverProfile['name'] ?? $user['name'] ?? 'Driver';
        $recipientPhone = $user['phone'] ?? $driverProfile['emergency_contact'] ?? '';

        $dispatchCode = $dispatch['dispatch_code'] ?? 'DSP-PENDING';
        $bookingCode  = $booking['booking_code'] ?? 'BKG-TOUR';
        $pickupLoc    = $booking['pickup_address'] ?? 'Designated Pickup Location';
        $dropLoc      = $booking['drop_address'] ?? 'Destination';
        $formattedDate = date('d M Y, h:i A', strtotime($booking['start_date'] ?? date('Y-m-d H:i:s')));

        $title = "Action Required: Trip Assignment Awaiting Response: {$dispatchCode}";
        $message = "Dear {$recipientName},\n"
            . "Please review and accept or decline your assigned Trip Duty [{$dispatchCode}] for Reservation [{$bookingCode}].\n\n"
            . "• Route: {$pickupLoc} -> {$dropLoc}\n"
            . "• Reporting Time: {$formattedDate}\n\n"
            . "Please access your Driver Portal to submit your response promptly.";

        $notifId = $this->notificationModel->create([
            'user_id'        => $driverUserId,
            'employee_id'    => $driverEmployeeId,
            'title'          => $title,
            'message'        => $message,
            'type'           => 'trip_reminder',
            'reference_type' => 'dispatches',
            'reference_id'   => (int)($dispatch['id'] ?? 0),
            'channel'        => 'in_app',
            'data'           => json_encode([
                'dispatch_id'   => (int)($dispatch['id'] ?? 0),
                'dispatch_code' => $dispatchCode,
                'booking_code'  => $bookingCode,
                'reminded_by'   => $remindedByUserId,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_read'        => 0,
        ]);

        if (!empty($recipientPhone)) {
            $compTitle = strtoupper(function_exists('company_display_name') ? company_display_name() : 'TOURS & TRAVELS');
            $sms = "{$compTitle} — ACTION REQUIRED: Duty Assignment Reminder\n"
                 . "Trip: {$dispatchCode} (Booking: {$bookingCode})\n"
                 . "Please confirm or decline duty in your Driver Console:\n"
                 . url('/driver/trips/' . ($dispatch['id'] ?? ''));
            $this->dispatchSmsNotification($recipientPhone, $sms, [
                'dispatch_id' => $dispatch['id'] ?? null,
                'type'        => 'reminder',
            ]);
        }

        return ['success' => true, 'notification_id' => $notifId];
    }

    /**
     * Get KPI Summary counts for recipient
     */
    public function getKpiStats(?int $userId = null, ?int $employeeId = null): array
    {
        return $this->notificationModel->getKpiStats($userId, $employeeId);
    }

    /**
     * Count notifications matching search & filter status
     */
    public function countFiltered(?int $userId = null, ?int $employeeId = null, string $search = '', string $status = ''): int
    {
        return $this->notificationModel->countFiltered($userId, $employeeId, $search, $status);
    }

    /**
     * Get paginated notifications matching search & filter status
     */
    public function getPaginatedNotifications(?int $userId = null, ?int $employeeId = null, string $search = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        return $this->notificationModel->getPaginated($userId, $employeeId, $search, $status, $page, $perPage);
    }

    /**
     * Dispatch Trip Cancellation Alert to Driver
     */
    public function notifyDriverTripCancelled(array $dispatch, array $driverProfile, ?string $reason = null): array
    {
        $driverUserId = !empty($driverProfile['user_id']) ? (int)$driverProfile['user_id'] : null;
        $driverEmployeeId = !empty($driverProfile['employee_id']) ? (int)$driverProfile['employee_id'] : null;

        $user = $driverUserId ? $this->userModel->find($driverUserId) : null;
        $recipientName = $driverProfile['name'] ?? $user['name'] ?? 'Driver';
        $recipientPhone = $user['phone'] ?? $driverProfile['emergency_contact'] ?? '';

        $dispatchCode = $dispatch['dispatch_code'] ?? 'DSP-DUTY';
        $reasonText = !empty($reason) ? "\nReason: {$reason}" : '';

        $title = "Duty Cancellation: Trip {$dispatchCode}";
        $message = "Dear {$recipientName},\nTrip Duty {$dispatchCode} has been cancelled by operations.{$reasonText}\nPlease check your console for updated duty rosters.";

        $notifId = $this->notificationModel->create([
            'user_id'        => $driverUserId,
            'employee_id'    => $driverEmployeeId,
            'title'          => $title,
            'message'        => $message,
            'type'           => 'trip_cancelled',
            'reference_type' => 'dispatches',
            'reference_id'   => (int)($dispatch['id'] ?? 0),
            'channel'        => 'in_app',
            'data'           => json_encode([
                'dispatch_id'   => (int)($dispatch['id'] ?? 0),
                'dispatch_code' => $dispatchCode,
                'reason'        => $reason,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_read'        => 0,
        ]);

        if (!empty($recipientPhone)) {
            $compTitle = strtoupper(function_exists('company_display_name') ? company_display_name() : 'TOURS & TRAVELS');
            $sms = "{$compTitle} — TRIP DUTY CANCELLED\nDuty: {$dispatchCode}\n{$message}";
            $this->dispatchSmsNotification($recipientPhone, $sms, [
                'dispatch_id' => $dispatch['id'] ?? null,
                'type'        => 'cancelled',
            ]);
        }

        return ['success' => true, 'notification_id' => $notifId];
    }

    /**
     * Default notification preferences configuration
     */
    public function getDefaultPreferences(): array
    {
        return [
            // General Notifications
            'general_in_app'          => 1,
            'general_system_alerts'   => 1,
            'general_browser_push'    => 1,

            // Booking Notifications
            'booking_new'             => 1,
            'booking_status_changes'  => 1,
            'booking_cancellations'   => 1,

            // Payment & Finance Notifications
            'payment_received'        => 1,
            'payment_pending_alerts'  => 1,
            'payment_refund_requests' => 1,

            // Driver & Dispatch Notifications
            'driver_trip_assigned'    => 1,
            'driver_acceptance'       => 1,
            'driver_completion'       => 1,

            // Email & Telemetry Notifications
            'email_dispatch_alerts'   => 1,
            'email_worker_queue'      => 1,
            'email_delivery_failures' => 1,
        ];
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId): array
    {
        $defaults = $this->getDefaultPreferences();
        if ($userId <= 0) {
            return $defaults;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT `preferences` FROM `user_notification_preferences` WHERE `user_id` = ?");
            $stmt->execute([$userId]);
            $raw = $stmt->fetchColumn();

            if (!empty($raw) && is_string($raw)) {
                $saved = json_decode($raw, true);
                if (is_array($saved)) {
                    return array_merge($defaults, $saved);
                }
            }
        } catch (\Throwable $e) {
            Logger::error("Failed to load user notification preferences for user {$userId}: " . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Persist user notification preferences
     */
    public function saveUserPreferences(int $userId, array $preferences): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $defaults = $this->getDefaultPreferences();
        $cleaned = [];

        foreach ($defaults as $key => $defaultVal) {
            if (array_key_exists($key, $preferences)) {
                $cleaned[$key] = (!empty($preferences[$key]) && $preferences[$key] !== '0' && $preferences[$key] !== false) ? 1 : 0;
            } else {
                $cleaned[$key] = 0;
            }
        }

        try {
            $pdo = Database::getConnection();
            $json = json_encode($cleaned, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("INSERT INTO `user_notification_preferences` (`user_id`, `preferences`, `created_at`, `updated_at`) 
                                   VALUES (?, ?, NOW(), NOW()) 
                                   ON DUPLICATE KEY UPDATE `preferences` = VALUES(`preferences`), `updated_at` = NOW()");
            return $stmt->execute([$userId, $json]);
        } catch (\Throwable $e) {
            Logger::error("Failed to save user notification preferences for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a specific notification category is enabled for a user
     */
    public function isNotificationEnabled(int $userId, string $settingKey): bool
    {
        $prefs = $this->getUserPreferences($userId);
        return !empty($prefs[$settingKey]);
    }

    /**
     * Dispatch in-app booking notifications to active office staff
     */
    public function notifyBookingEvent(string $event, array $booking, ?int $byUserId = null): array
    {
        $pdo = Database::getConnection();
        $bookingCode = $booking['booking_code'] ?? ('BK-' . ($booking['id'] ?? ''));
        $customerName = $booking['customer_name'] ?? 'Guest';
        $prefKey = 'booking_' . $event;

        if ($event === 'new') {
            $title = "New Reservation Booking: {$bookingCode}";
            $message = "Reservation {$bookingCode} for {$customerName} has been booked. Awaiting trip dispatch assignment.";
            $type = 'booking_new';
        } elseif ($event === 'cancelled') {
            $title = "Booking Cancelled: {$bookingCode}";
            $message = "Reservation {$bookingCode} for {$customerName} has been cancelled.";
            $type = 'booking_cancellations';
        } else {
            $title = "Booking Updated: {$bookingCode}";
            $message = "Reservation {$bookingCode} for {$customerName} status changed to " . ($booking['status'] ?? 'updated') . ".";
            $type = 'booking_status_changes';
        }

        $stmt = $pdo->query("SELECT u.id FROM users u WHERE u.role IN ('admin', 'superadmin', 'owner', 'dispatcher') AND u.status = 'active'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifIds = [];
        $payload = [
            'booking_id'    => (int)($booking['id'] ?? 0),
            'booking_code'  => $bookingCode,
            'customer_name' => $customerName,
            'event'         => $event,
        ];

        foreach ($adminUsers as $admin) {
            $adminId = (int)$admin['id'];
            if (!$this->isNotificationEnabled($adminId, $prefKey)) {
                continue;
            }

            $nId = $this->notificationModel->create([
                'user_id'        => $adminId,
                'employee_id'    => null,
                'title'          => $title,
                'message'        => $message,
                'type'           => $type,
                'reference_type' => 'bookings',
                'reference_id'   => (int)($booking['id'] ?? 0),
                'channel'        => 'in_app',
                'data'           => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_read'        => 0,
            ]);
            $notifIds[] = $nId;
        }

        return ['success' => true, 'notif_ids' => $notifIds, 'title' => $title];
    }

    /**
     * Dispatch in-app payment / finance notifications to active office staff
     */
    public function notifyFinanceEvent(string $event, array $financeData, ?int $byUserId = null): array
    {
        $pdo = Database::getConnection();
        $refCode = $financeData['code'] ?? ($financeData['invoice_number'] ?? 'FIN');
        $amount = isset($financeData['amount']) ? format_currency((float)$financeData['amount']) : '';
        $prefKey = 'payment_' . $event;

        if ($event === 'received') {
            $title = "Payment Received: {$amount}";
            $message = "Payment of {$amount} recorded for {$refCode}.";
            $type = 'payment_received';
        } elseif ($event === 'refund_requests') {
            $title = "Refund Processed: {$refCode}";
            $message = "Refund of {$amount} processed for {$refCode}.";
            $type = 'payment_refund_requests';
        } else {
            $title = "Finance Alert: {$refCode}";
            $message = "Pending due balance alert for {$refCode}: {$amount}.";
            $type = 'payment_pending_alerts';
        }

        $stmt = $pdo->query("SELECT u.id FROM users u WHERE u.role IN ('admin', 'superadmin', 'owner', 'dispatcher') AND u.status = 'active'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifIds = [];
        $payload = array_merge($financeData, ['event' => $event]);

        foreach ($adminUsers as $admin) {
            $adminId = (int)$admin['id'];
            if (!$this->isNotificationEnabled($adminId, $prefKey)) {
                continue;
            }

            $nId = $this->notificationModel->create([
                'user_id'        => $adminId,
                'employee_id'    => null,
                'title'          => $title,
                'message'        => $message,
                'type'           => $type,
                'reference_type' => 'invoices',
                'reference_id'   => (int)($financeData['invoice_id'] ?? 0),
                'channel'        => 'in_app',
                'data'           => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_read'        => 0,
            ]);
            $notifIds[] = $nId;
        }

        return ['success' => true, 'notif_ids' => $notifIds, 'title' => $title];
    }
}
