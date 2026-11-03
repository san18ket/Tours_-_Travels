<?php
namespace App\Services;

use App\Models\EmailLog;
use App\Models\User;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Booking;
use App\Models\Invoice;
use App\Core\Config;
use App\Core\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Throwable;

/**
 * Reusable Professional Email Notification Service
 * Transmits SSL/TLS emails using PHPMailer via Gmail SMTP (Port 465)
 * Handles templating, validation, duplicate prevention, and secure audit logging.
 */
class EmailService
{
    private EmailLog $emailLog;
    private User $userModel;
    private Customer $customerModel;
    private Employee $employeeModel;

    public function __construct()
    {
        $this->emailLog = new EmailLog();
        $this->userModel = new User();
        $this->customerModel = new Customer();
        $this->employeeModel = new Employee();
    }

    /**
     * Send an email with HTML & plain-text alternative, validation, and idempotency protection
     *
     * @return array ['success' => bool, 'message' => string, 'duplicate' => bool, 'log_id' => ?int, 'error' => ?string]
     */
    public function send(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        string $emailType = 'general',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $cc = [],
        array $bcc = [],
        int $cooldownMinutes = 0
    ): array {
        $toEmail = trim($toEmail);
        $toName = $toName ? trim($toName) : null;

        // 1. Validate recipient email address
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL) || str_ends_with(strtolower($toEmail), '@internal.local') || str_ends_with(strtolower($toEmail), '@voyagego.internal')) {
            $msg = "Invalid recipient email address: '{$toEmail}'";
            Logger::warning("EmailService: {$msg}");
            $logId = $this->emailLog->record($toEmail, $toName, $emailType, $subject, $referenceType, $referenceId, 'failed', $msg);
            return [
                'success'   => false,
                'message'   => $msg,
                'duplicate' => false,
                'log_id'    => $logId,
                'error'     => $msg
            ];
        }

        // 2. Duplicate Prevention / Idempotency Check
        $idempotencyHash = EmailLog::makeHash($emailType, $referenceType, $referenceId, $toEmail);
        if ($this->emailLog->hasDuplicate($idempotencyHash, $cooldownMinutes)) {
            Logger::info("EmailService: Duplicate email prevented for {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail}");
            return [
                'success'   => true,
                'message'   => 'Email already dispatched (duplicate prevented).',
                'duplicate' => true,
                'log_id'    => null,
                'error'     => null
            ];
        }

        if ($cooldownMinutes === 0 && !empty($referenceType) && !empty($referenceId)) {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `email_logs` WHERE `email_type` = ? AND `reference_type` = ? AND `reference_id` = ?");
            $stmt->execute([$emailType, $referenceType, $referenceId]);
            if (((int)$stmt->fetchColumn()) > 0) {
                Logger::info("EmailService: Duplicate prevented by entity reference for {$emailType} [{$referenceType}:{$referenceId}]");
                return [
                    'success'   => true,
                    'message'   => 'Email already dispatched (duplicate prevented).',
                    'duplicate' => true,
                    'log_id'    => null,
                    'error'     => null
                ];
            }
        }

        // 3. Configure and transmit via PHPMailer
        try {
            $this->executeSmtpSend($toEmail, $toName, $subject, $htmlBody, $textBody, $cc, $bcc);

            // Record successful dispatch
            $logId = $this->emailLog->record(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'sent',
                null,
                $idempotencyHash
            );

            Logger::info("EmailService: Dispatched {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail}");

            return [
                'success'   => true,
                'message'   => 'Email transmitted successfully.',
                'duplicate' => false,
                'log_id'    => $logId,
                'error'     => null
            ];
        } catch (Throwable $e) {
            // Sanitize error message to ensure passwords are NEVER leaked
            $rawError = $e->getMessage();
            $sanitized = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $rawError);
            $sanitized = substr($sanitized, 0, 500);

            Logger::error("EmailService: SMTP dispatch error for {$emailType} to {$toEmail}: {$sanitized}");

            $logId = $this->emailLog->record(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'failed',
                $sanitized,
                $idempotencyHash
            );

            return [
                'success'   => false,
                'message'   => 'Email transmission failed.',
                'duplicate' => false,
                'log_id'    => $logId,
                'error'     => $sanitized
            ];
        }
    }

    /**
     * Dispatch an email asynchronously via safe background shutdown execution
     * Eliminates SMTP latency from blocking create/edit save responses and redirects.
     *
     * @return int|null Returns log ID if queued, null if rejected or duplicate
     */
    public function queue(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        string $emailType = 'general',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $cc = [],
        array $bcc = [],
        int $cooldownMinutes = 0
    ): ?int {
        $toEmail = trim($toEmail);
        $toName = $toName ? trim($toName) : null;

        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL) || str_ends_with(strtolower($toEmail), '@internal.local') || str_ends_with(strtolower($toEmail), '@voyagego.internal')) {
            $msg = "Invalid recipient email address: '{$toEmail}'";
            Logger::warning("EmailService [Queued]: {$msg}");
            return $this->emailLog->record($toEmail, $toName, $emailType, $subject, $referenceType, $referenceId, 'failed', $msg);
        }

        // Idempotency check upfront
        $idempotencyHash = EmailLog::makeHash($emailType, $referenceType, $referenceId, $toEmail);
        if ($this->emailLog->hasDuplicate($idempotencyHash, $cooldownMinutes)) {
            Logger::info("EmailService [Queued]: Duplicate prevented for {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail}");
            return null;
        }

        if ($cooldownMinutes === 0 && !empty($referenceType) && !empty($referenceId)) {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `email_logs` WHERE `email_type` = ? AND `reference_type` = ? AND `reference_id` = ? AND `status` IN ('sent', 'pending')");
            $stmt->execute([$emailType, $referenceType, $referenceId]);
            if (((int)$stmt->fetchColumn()) > 0) {
                Logger::info("EmailService [Queued]: Duplicate prevented by entity reference for {$emailType} [{$referenceType}:{$referenceId}]");
                return null;
            }
        }

        // Pre-record in pending status so it is immediately visible in logs
        $logId = $this->emailLog->record(
            $toEmail,
            $toName,
            $emailType,
            $subject,
            $referenceType,
            $referenceId,
            'pending',
            null,
            $idempotencyHash
        );

        // Register shutdown function for safe non-blocking background transmission
        register_shutdown_function(function () use ($logId, $toEmail, $toName, $subject, $htmlBody, $textBody, $emailType, $referenceType, $referenceId, $cc, $bcc) {
            try {
                $this->executeSmtpSend($toEmail, $toName, $subject, $htmlBody, $textBody, $cc, $bcc);
                $this->emailLog->updateStatus($logId, 'sent', null);
                Logger::info("EmailService [Queued]: Dispatched {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail} (Log #{$logId})");
            } catch (Throwable $e) {
                $rawError = $e->getMessage();
                $sanitized = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $rawError);
                $sanitized = substr($sanitized, 0, 500);
                $this->emailLog->updateStatus($logId, 'failed', $sanitized);
                Logger::error("EmailService [Queued]: SMTP dispatch error for {$emailType} to {$toEmail}: {$sanitized}");
            }
        });

        return $logId;
    }

    /**
     * Internal raw SMTP transmission via PHPMailer
     */
    private function executeSmtpSend(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = []
    ): void {
        if (str_ends_with(strtolower($toEmail), '@example.com') || str_ends_with(strtolower($toEmail), '@test.local') || str_ends_with(strtolower($toEmail), '@testdomain.org')) {
            return;
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = Config::get('mail.host', 'smtp.gmail.com');
        $mail->Port       = (int)Config::get('mail.port', 465);
        $mail->SMTPAuth   = (bool)Config::get('mail.auth', true);
        $mail->Username   = Config::get('mail.username', '');
        $mail->Password   = Config::get('mail.password', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Timeout    = (int)Config::get('mail.timeout', 15);
        $mail->CharSet    = 'UTF-8';

        $port = (int)Config::get('mail.port', 465);
        $encryption = strtolower((string)Config::get('mail.encryption', 'ssl'));
        if ($port === 465 || $encryption === 'ssl' || $encryption === 'smtps') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($port === 587 || $encryption === 'tls' || $encryption === 'starttls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $verifyPeer = (bool)Config::get('mail.verify_peer', true);
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => $verifyPeer,
                'verify_peer_name'  => $verifyPeer,
                'allow_self_signed' => !$verifyPeer
            ]
        ];

        $smtpUsername = Config::get('mail.username', '');
        $configuredFrom = Config::get('mail.from.address', '');
        $fromAddress = (!empty($configuredFrom) && filter_var($configuredFrom, FILTER_VALIDATE_EMAIL))
            ? $configuredFrom
            : ($smtpUsername ?: 'noreply@example.com');
        $fromName = Config::get('mail.from.name', CompanySettingsService::getCompanyName());
        $mail->setFrom($fromAddress, $fromName);

        $replyToAddress = Config::get('mail.reply_to.address', CompanySettingsService::getCompanyEmail());
        if (!empty($replyToAddress) && filter_var($replyToAddress, FILTER_VALIDATE_EMAIL)) {
            $replyToName = Config::get('mail.reply_to.name', $fromName);
            $mail->addReplyTo($replyToAddress, $replyToName);
        }

        $mail->addAddress($toEmail, $toName ?? '');

        foreach ($cc as $ccEmail) {
            if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($ccEmail);
            }
        }
        foreach ($bcc as $bccEmail) {
            if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addBCC($bccEmail);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: self::htmlToPlainText($htmlBody);

        $mail->send();
    }

    /**
     * Render an email template wrapped inside the branded master layout
     */
    public function renderTemplate(string $templateName, array $data = []): string
    {
        $viewPath = dirname(__DIR__) . '/Views/emails/' . $templateName . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Email template view '{$templateName}' not found at: {$viewPath}");
        }

        // Render template body
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Render master layout with wrapped content
        $layoutPath = dirname(__DIR__) . '/Views/emails/layouts/master.php';
        ob_start();
        $subject = $data['subject'] ?? ('Notification - ' . CompanySettingsService::getCompanyName());
        $previewText = $data['previewText'] ?? $subject;
        require $layoutPath;
        return ob_get_clean();
    }

    /**
     * Convert HTML content into structured plain-text fallback
     */
    public static function htmlToPlainText(string $html): string
    {
        // Replace headings & breaks with double newlines
        $text = preg_replace('/<\/(h[1-6]|p|div|tr)>/i', "\n", $html);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<td[^>]*>/i', "  ", $text);
        // Strip remaining tags
        $text = strip_tags($text);
        // Normalize multiple blank lines
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    // =========================================================================
    // SPECIFIC APPLICATION EVENT NOTIFICATION METHODS
    // =========================================================================

    /**
     * Event: Booking Created -> Send to Customer
     */
    public function sendBookingCreated(array $booking): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        if (empty($customer['email']) || str_ends_with(strtolower($customer['email']), '@internal.local') || str_ends_with(strtolower($customer['email']), '@voyagego.internal')) {
            Logger::warning("EmailService: Skipped booking_created email for #{$booking['id']} - Customer has no valid email address.");
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Booking Received: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/created', [
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your reservation {$booking['booking_code']} has been received and is pending confirmation."
        ]);

        return $this->send(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'booking_created',
            'booking',
            (int)$booking['id'],
            [],
            [],
            0 // Lifetime uniqueness for booking_created
        );
    }

    /**
     * Event: Booking Created (Queued / Asynchronous background delivery)
     */
    public function queueBookingCreated(array $booking): ?int
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        if (empty($customer['email']) || str_ends_with(strtolower($customer['email']), '@internal.local') || str_ends_with(strtolower($customer['email']), '@voyagego.internal')) {
            Logger::warning("EmailService [Queued]: Skipped booking_created email for #{$booking['id']} - Customer has no valid email address.");
            return null;
        }

        $subject = "Booking Received: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/created', [
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your reservation {$booking['booking_code']} has been received and is pending confirmation."
        ]);

        return $this->queue(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'booking_created',
            'booking',
            (int)$booking['id'],
            [],
            [],
            0
        );
    }

    /**
     * Event: Booking Confirmed -> Send to Customer
     */
    public function sendBookingConfirmed(array $booking): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        if (empty($customer['email'])) {
            Logger::warning("EmailService: Skipped booking_confirmed email for #{$booking['id']} - Customer has no email.");
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "✓ Booking Confirmed: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/confirmed', [
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Great news! Your booking {$booking['booking_code']} is officially confirmed."
        ]);

        return $this->send(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'booking_confirmed',
            'booking',
            (int)$booking['id'],
            [],
            [],
            0 // Lifetime uniqueness for confirmation
        );
    }

    /**
     * Event: Booking Cancelled -> Send to Customer and Relevant Administrator
     */
    public function sendBookingCancelled(array $booking, ?string $reason = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $subject = "Notice: Booking {$booking['booking_code']} Cancelled — {$company}";

        $html = $this->renderTemplate('booking/cancelled', [
            'booking'     => $booking,
            'customer'    => $customer,
            'reason'      => $reason,
            'subject'     => $subject,
            'previewText' => "Booking {$booking['booking_code']} has been cancelled."
        ]);

        // Send to Customer if email present
        $customerResult = ['success' => false];
        if (!empty($customer['email'])) {
            $customerResult = $this->send(
                $customer['email'],
                $customer['name'],
                $subject,
                $html,
                null,
                'booking_cancelled',
                'booking',
                (int)$booking['id'],
                [],
                [],
                0
            );
        }

        // Send notice copy to Administrator if configured
        $adminEmail = Config::get('mail.admin_address');
        if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $adminSubject = "[ADMIN ALERT] Booking Cancelled: {$booking['booking_code']}";
            $this->send(
                $adminEmail,
                'Operations Desk',
                $adminSubject,
                $html,
                null,
                'booking_cancelled_admin',
                'booking',
                (int)$booking['id'],
                [],
                [],
                0
            );
        }

        return $customerResult;
    }

    /**
     * Event: Payment Successful -> Send to Customer
     */
    public function sendPaymentSuccessful(array $payment, ?array $invoice = null, ?array $booking = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        // Resolve invoice & booking if not supplied
        if (!$invoice && !empty($payment['invoice_id'])) {
            $invoiceModel = new Invoice();
            $invoice = $invoiceModel->find((int)$payment['invoice_id']);
        }

        if (!$booking && !empty($invoice['booking_id'])) {
            $bookingModel = new Booking();
            $booking = $bookingModel->find((int)$invoice['booking_id']);
        }

        // Resolve customer
        $customer = [];
        if (!empty($invoice['customer_profile_id'])) {
            $customer = $this->customerModel->find((int)$invoice['customer_profile_id']) ?: [];
        } elseif (!empty($invoice['customer_id'])) {
            $customer = $this->userModel->find((int)$invoice['customer_id']) ?: [];
        } elseif ($booking) {
            $customer = $this->resolveCustomerDetails($booking);
        }

        if (empty($customer['email'])) {
            Logger::warning("EmailService: Skipped payment receipt for payment #{$payment['id']} - Customer email not found.");
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $formattedAmt = format_currency((float)($payment['amount'] ?? 0));
        $subject = "Payment Receipt: {$payment['payment_reference']} ({$formattedAmt}) — {$company}";

        $html = $this->renderTemplate('payment/successful', [
            'payment'     => $payment,
            'invoice'     => $invoice ?? [],
            'booking'     => $booking ?? [],
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Payment of {$formattedAmt} received successfully for {$payment['payment_reference']}."
        ]);

        return $this->send(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'payment_successful',
            'payment',
            (int)$payment['id'],
            [],
            [],
            0
        );
    }

    /**
     * Event: Driver Assigned -> Send to Assigned Driver AND to Customer
     */
    public function sendDriverAssigned(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $driverRecipient = $this->resolveDriverDetails($driverProfile);
        $customerRecipient = $this->resolveCustomerDetails($booking);

        $results = [
            'driver'   => ['success' => false],
            'customer' => ['success' => false]
        ];

        // 1. Send Dispatch Notification to the Assigned Driver Only
        if (!empty($driverRecipient['email'])) {
            $driverSubject = "🚍 New Trip Assignment: {$dispatch['dispatch_code']} — {$company}";
            $driverHtml = $this->renderTemplate('driver/trip-assigned', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => $driverRecipient,
                'customer'    => $customerRecipient,
                'subject'     => $driverSubject,
                'previewText' => "You have been deployed for trip assignment {$dispatch['dispatch_code']}."
            ]);

            $results['driver'] = $this->send(
                $driverRecipient['email'],
                $driverRecipient['name'],
                $driverSubject,
                $driverHtml,
                null,
                'driver_assigned_driver',
                'dispatch',
                (int)$dispatch['id'],
                [],
                [],
                0
            );
        } else {
            Logger::warning("EmailService: Assigned driver #{$driverProfile['id']} has no email address on record.");
        }

        // 2. Send Chauffeur & Vehicle Assigned Notification to Customer
        if (!empty($customerRecipient['email'])) {
            $custSubject = "Chauffeur & Fleet Assigned: {$booking['booking_code']} — {$company}";
            $custHtml = $this->renderTemplate('booking/driver-assigned', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => $driverRecipient,
                'customer'    => $customerRecipient,
                'subject'     => $custSubject,
                'previewText' => "Your executive chauffeur and vehicle details are ready for reservation {$booking['booking_code']}."
            ]);

            $results['customer'] = $this->send(
                $customerRecipient['email'],
                $customerRecipient['name'],
                $custSubject,
                $custHtml,
                null,
                'driver_assigned_customer',
                'dispatch',
                (int)$dispatch['id'],
                [],
                [],
                0
            );
        }

        return $results;
    }

    /**
     * Event: Trip Reminder -> Send to Customer and Assigned Driver
     */
    public function sendTripReminder(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $customer = $this->resolveCustomerDetails($booking);
        $driver = $this->resolveDriverDetails($driverProfile);

        $results = [];

        // Customer Reminder
        if (!empty($customer['email'])) {
            $company = CompanySettingsService::getCompanyName();
        $subject = "⏰ Reminder: Your Departure is Approaching ({$booking['booking_code']}) • {$company}";
            $html = $this->renderTemplate('trip/reminder', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => $driver,
                'recipient'   => $customer,
                'subject'     => $subject,
                'previewText' => "Gentle reminder for your upcoming journey on {$booking['start_date']}."
            ]);

            $results['customer'] = $this->send(
                $customer['email'],
                $customer['name'],
                $subject,
                $html,
                null,
                'trip_reminder_customer',
                'dispatch',
                (int)$dispatch['id'],
                [],
                [],
                360 // Cooldown: 6 hours
            );
        }

        // Driver Reminder
        if (!empty($driver['email'])) {
            $subject = "⏰ Duty Reminder: Upcoming Trip {$dispatch['dispatch_code']} ({$booking['booking_code']})";
            $html = $this->renderTemplate('trip/reminder', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => $driver,
                'recipient'   => $driver,
                'subject'     => $subject,
                'previewText' => "Upcoming duty reminder for {$dispatch['dispatch_code']}."
            ]);

            $results['driver'] = $this->send(
                $driver['email'],
                $driver['name'],
                $subject,
                $html,
                null,
                'trip_reminder_driver',
                'dispatch',
                (int)$dispatch['id'],
                [],
                [],
                360 // Cooldown: 6 hours
            );
        }

        return $results;
    }

    /**
     * Event: Trip Started -> Send to Customer
     */
    public function sendTripStarted(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $driver = $this->resolveDriverDetails($driverProfile);

        if (empty($customer['email'])) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "🚀 Your Journey Has Commenced: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('trip/started', [
            'dispatch'    => $dispatch,
            'booking'     => $booking,
            'vehicle'     => $vehicle,
            'driver'      => $driver,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your journey for reservation {$booking['booking_code']} has started."
        ]);

        return $this->send(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'trip_started',
            'dispatch',
            (int)$dispatch['id'],
            [],
            [],
            0
        );
    }

    /**
     * Event: Trip Completed -> Send to Customer
     */
    public function sendTripCompleted(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $customer = $this->resolveCustomerDetails($booking);
        $driver = $this->resolveDriverDetails($driverProfile);

        if (empty($customer['email'])) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $company = CompanySettingsService::getCompanyName();
        $subject = "✓ Journey Completed: {$booking['booking_code']} — Thank You for Choosing {$company}";
        $html = $this->renderTemplate('trip/completed', [
            'dispatch'    => $dispatch,
            'booking'     => $booking,
            'vehicle'     => $vehicle,
            'driver'      => $driver,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Thank you for traveling with {$company}! Your journey recap is ready."
        ]);

        return $this->send(
            $customer['email'],
            $customer['name'],
            $subject,
            $html,
            null,
            'trip_completed',
            'dispatch',
            (int)$dispatch['id'],
            [],
            [],
            0
        );
    }

    /**
     * Event: Password Reset -> Send to Account Owner
     */
    public function sendPasswordReset(array $user, ?string $temporaryPassword = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        if (empty($user['email'])) {
            return ['success' => false, 'message' => 'User account has no valid email address.'];
        }

        $subject = "Security Notice: Password Reset for {$user['username']} — {$company}";
        $html = $this->renderTemplate('account/password-reset', [
            'user'              => $user,
            'temporaryPassword' => $temporaryPassword,
            'subject'           => $subject,
            'previewText'       => "Your {$company} account credentials have been reset."
        ]);

        return $this->send(
            $user['email'],
            $user['name'] ?? $user['username'],
            $subject,
            $html,
            null,
            'password_reset',
            'user',
            (int)$user['id'],
            [],
            [],
            0
        );
    }

    /**
     * Diagnostic Test Email
     */
    public function sendTestEmail(string $recipientEmail): array
    {
        $company = CompanySettingsService::getCompanyName();
        $subject = "{$company} SMTP Test Message — " . date('d M Y, h:i A');
        $html = $this->renderTemplate('test', [
            'recipientEmail' => $recipientEmail,
            'smtpHost'       => Config::get('mail.host', 'smtp.gmail.com'),
            'smtpPort'       => Config::get('mail.port', 465),
            'sender'         => Config::get('mail.from.address', ''),
            'subject'        => $subject,
            'previewText'    => "Diagnostic verification test message from {$company} SMTP system."
        ]);

        return $this->send(
            $recipientEmail,
            'Diagnostic Tester',
            $subject,
            $html,
            null,
            'test_email',
            'diagnostic',
            0,
            [],
            [],
            0
        );
    }

    /**
     * Event: Customer Registration -> Send Welcome Email
     */
    public function sendCustomerWelcome(array $customer): array
    {
        if (empty($customer['email'])) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $code = $customer['customer_code'] ?? ('CUST-' . ($customer['id'] ?? 1));
        $recipientName = $customer['name'] ?? 'Valued Guest';
        $company = CompanySettingsService::getCompanyName();
        $subject = "Welcome to {$company} [{$code}]";
        $html = $this->renderTemplate('customer/welcome', [
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your customer membership account is active."
        ]);

        return $this->send(
            $customer['email'],
            $recipientName,
            $subject,
            $html,
            null,
            'customer_welcome',
            'customer',
            (int)($customer['id'] ?? 0),
            [],
            [],
            0
        );
    }

    /**
     * Event: Customer Registration -> Queue Welcome Email (Non-blocking background delivery)
     */
    public function queueCustomerWelcome(array $customer): ?int
    {
        if (empty($customer['email'])) {
            return null;
        }

        $code = $customer['customer_code'] ?? ('CUST-' . ($customer['id'] ?? 1));
        $recipientName = $customer['name'] ?? 'Valued Guest';
        $company = CompanySettingsService::getCompanyName();
        $subject = "Welcome to {$company} [{$code}]";
        $html = $this->renderTemplate('customer/welcome', [
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your customer membership account is active."
        ]);

        return $this->queue(
            $customer['email'],
            $recipientName,
            $subject,
            $html,
            null,
            'customer_welcome',
            'customer',
            (int)($customer['id'] ?? 0),
            [],
            [],
            0
        );
    }

    /**
     * Event: Employee Registration -> Send Welcome Email
     */
    public function sendEmployeeWelcome(array $employee): array
    {
        if (empty($employee['email'])) {
            return ['success' => false, 'message' => 'Employee has no valid email address.'];
        }

        $code = $employee['employee_code'] ?? ('EMP-' . ($employee['id'] ?? 1));
        $fullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        if (empty($fullName)) {
            $fullName = $employee['first_name'] ?? 'Team Member';
        }

        $company = CompanySettingsService::getCompanyName();
        $subject = "Welcome to {$company} Operations Team [{$code}]";
        $html = $this->renderTemplate('employee/welcome', [
            'employee'    => $employee,
            'subject'     => $subject,
            'previewText' => "Your staff operations profile has been created."
        ]);

        return $this->send(
            $employee['email'],
            $fullName,
            $subject,
            $html,
            null,
            'employee_welcome',
            'employee',
            (int)($employee['id'] ?? 0),
            [],
            [],
            0
        );
    }

    /**
     * Event: Employee Registration -> Queue Welcome Email (Non-blocking background delivery)
     */
    public function queueEmployeeWelcome(array $employee): ?int
    {
        if (empty($employee['email'])) {
            return null;
        }

        $code = $employee['employee_code'] ?? ('EMP-' . ($employee['id'] ?? 1));
        $fullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        if (empty($fullName)) {
            $fullName = $employee['first_name'] ?? 'Team Member';
        }

        $company = CompanySettingsService::getCompanyName();
        $subject = "Welcome to {$company} Operations Team [{$code}]";
        $html = $this->renderTemplate('employee/welcome', [
            'employee'    => $employee,
            'subject'     => $subject,
            'previewText' => "Your staff operations profile has been created."
        ]);

        return $this->queue(
            $employee['email'],
            $fullName,
            $subject,
            $html,
            null,
            'employee_welcome',
            'employee',
            (int)($employee['id'] ?? 0),
            [],
            [],
            0
        );
    }

    // =========================================================================
    // RECIPIENT IDENTITY RESOLUTION HELPERS
    // =========================================================================

    /**
     * Resolve Customer identity from booking details
     */
    private function resolveCustomerDetails(array $booking): array
    {
        $customer = [
            'id'    => null,
            'name'  => $booking['customer_name'] ?? 'Valued Customer',
            'email' => $booking['customer_email'] ?? '',
            'phone' => $booking['customer_phone'] ?? '',
        ];

        // 1. Try Customer Profile table
        if (!empty($booking['customer_profile_id'])) {
            $profile = $this->customerModel->find((int)$booking['customer_profile_id']);
            if ($profile) {
                $customer['id'] = $profile['id'];
                $customer['name'] = $profile['name'];
                $customer['email'] = $profile['email'] ?? $customer['email'];
                $customer['phone'] = $profile['phone'] ?? $customer['phone'];
                return $customer;
            }
        }

        // 2. Try User table (customer role)
        if (!empty($booking['customer_id'])) {
            $user = $this->userModel->find((int)$booking['customer_id']);
            if ($user) {
                $customer['id'] = $user['id'];
                $customer['name'] = $user['name'] ?? $customer['name'];
                $customer['email'] = $user['email'] ?? $customer['email'];
                $customer['phone'] = $user['phone'] ?? $customer['phone'];
                return $customer;
            }
        }

        return $customer;
    }

    /**
     * Resolve Driver identity from driver profile details
     */
    private function resolveDriverDetails(array $driverProfile): array
    {
        $driver = [
            'id'    => $driverProfile['id'] ?? null,
            'name'  => 'Chauffeur',
            'email' => '',
            'phone' => $driverProfile['emergency_contact'] ?? '',
        ];

        // 1. Check Employee record
        if (!empty($driverProfile['employee_id'])) {
            $emp = $this->employeeModel->find((int)$driverProfile['employee_id']);
            if ($emp) {
                $driver['name'] = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
                $driver['email'] = $emp['email'] ?? '';
                $driver['phone'] = $emp['phone'] ?? $driver['phone'];
            }
        }

        // 2. Fallback to User record
        if (empty($driver['email']) && !empty($driverProfile['user_id'])) {
            $user = $this->userModel->find((int)$driverProfile['user_id']);
            if ($user) {
                if (empty($driver['name']) || $driver['name'] === 'Chauffeur') {
                    $driver['name'] = $user['name'] ?? 'Chauffeur';
                }
                $driver['email'] = $user['email'] ?? '';
                $driver['phone'] = !empty($user['phone']) ? $user['phone'] : $driver['phone'];
            }
        }

        return $driver;
    }

    /**
     * Retry sending an email that previously failed or was recorded in email_logs
     */
    public function retryFailedEmail(int $logId): array
    {
        $log = $this->emailLog->find($logId);
        if (!$log) {
            return [
                'success' => false,
                'message' => "Email log #{$logId} not found.",
                'error'   => 'Log record not found.'
            ];
        }

        // Prevent duplicate delivery: if already sent, disallow retries
        if ($log['status'] === 'sent') {
            return [
                'success'   => false,
                'duplicate' => true,
                'message'   => "Email #{$logId} has already been sent successfully. Duplicate delivery prevented.",
                'error'     => 'Email already sent.'
            ];
        }

        $emailType = $log['email_type'] ?? '';
        $refType = $log['reference_type'] ?? null;
        $refId = !empty($log['reference_id']) ? (int)$log['reference_id'] : null;
        $result = ['success' => false, 'message' => 'Unknown email type.'];

        try {
            switch ($emailType) {
                case 'customer_welcome':
                    if ($refId) {
                        $cust = (new \App\Models\Customer())->find($refId);
                        if ($cust) {
                            $result = $this->sendCustomerWelcome($cust);
                            break;
                        }
                    }
                    $result = $this->send($log['recipient_email'], $log['recipient_name'], $log['subject'], $this->renderTemplate('customer/welcome', ['customer' => ['name' => $log['recipient_name'], 'email' => $log['recipient_email']]]), null, $emailType, $refType, $refId);
                    break;

                case 'employee_welcome':
                    if ($refId) {
                        $emp = (new \App\Models\Employee())->find($refId);
                        if ($emp) {
                            $result = $this->sendEmployeeWelcome($emp);
                            break;
                        }
                    }
                    $result = $this->send($log['recipient_email'], $log['recipient_name'], $log['subject'], $this->renderTemplate('employee/welcome', ['employee' => ['first_name' => $log['recipient_name'], 'last_name' => '', 'email' => $log['recipient_email']]]), null, $emailType, $refType, $refId);
                    break;

                case 'booking_created':
                    if ($refId) {
                        $b = (new \App\Models\Booking())->find($refId);
                        if ($b) {
                            $result = $this->sendBookingCreated($b);
                            break;
                        }
                    }
                    $result = ['success' => false, 'message' => 'Booking entity not found for retry.'];
                    break;

                case 'booking_confirmed':
                    if ($refId) {
                        $b = (new \App\Models\Booking())->find($refId);
                        if ($b) {
                            $result = $this->sendBookingConfirmed($b);
                            break;
                        }
                    }
                    $result = ['success' => false, 'message' => 'Booking entity not found for retry.'];
                    break;

                case 'booking_cancelled':
                    if ($refId) {
                        $b = (new \App\Models\Booking())->find($refId);
                        if ($b) {
                            $result = $this->sendBookingCancelled($b, 'Administrative retry');
                            break;
                        }
                    }
                    $result = ['success' => false, 'message' => 'Booking entity not found for retry.'];
                    break;

                case 'payment_successful':
                    if ($refId) {
                        $p = (new \App\Models\Payment())->find($refId);
                        if ($p) {
                            $result = $this->sendPaymentSuccessful($p);
                            break;
                        }
                    }
                    $result = ['success' => false, 'message' => 'Payment entity not found for retry.'];
                    break;

                case 'test':
                    $result = $this->sendTestEmail($log['recipient_email']);
                    break;

                default:
                    // Generic fallback for any other registered email template
                    $result = $this->send(
                        $log['recipient_email'],
                        $log['recipient_name'],
                        $log['subject'],
                        $this->renderTemplate('test', ['recipientEmail' => $log['recipient_email'], 'smtpHost' => \App\Core\Config::get('mail.host', 'smtp.gmail.com'), 'smtpPort' => 465, 'sender' => \App\Core\Config::get('mail.from.address', '')]),
                        null,
                        $emailType,
                        $refType,
                        $refId
                    );
                    break;
            }
        } catch (\Throwable $e) {
            $result = ['success' => false, 'message' => $e->getMessage(), 'error' => $e->getMessage()];
        }

        // Record retry outcome into original log record
        $this->emailLog->recordRetry(
            $logId,
            !empty($result['success']) ? 'sent' : 'failed',
            $result['error'] ?? ($result['message'] ?? null)
        );

        return $result;
    }
}
