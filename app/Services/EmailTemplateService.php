<?php
namespace App\Services;

use RuntimeException;

/**
 * Reusable Email Template Rendering Service
 * Renders mobile-first responsive HTML email views wrapped inside the dynamic theme master layout.
 * Injects current company branding, logo, and active website theme colors.
 */
class EmailTemplateService
{
    /**
     * Render an email template wrapped inside the branded master layout
     *
     * @param string $templateName Path relative to app/Views/emails/ (e.g. 'booking/confirmed')
     * @param array $data Variables to pass to the view
     * @return string Complete HTML document
     */
    public function render(string $templateName, array $data = []): string
    {
        $baseDir = dirname(__DIR__) . '/Views/emails/';
        $viewPath = $baseDir . $templateName . '.php';

        if (!file_exists($viewPath)) {
            // Check fallback for flat or legacy paths
            $altPath = $baseDir . str_replace('/', '_', $templateName) . '.php';
            if (file_exists($altPath)) {
                $viewPath = $altPath;
            } else {
                throw new RuntimeException("Email template view '{$templateName}' not found at: {$viewPath}");
            }
        }

        // Retrieve dynamic theme colors and company metadata
        $themeColors = CompanySettingsService::getThemeColors();
        $companyName = CompanySettingsService::getCompanyName();
        $companyTagline = company_tagline();
        $companyLogoUrl = CompanySettingsService::getLogoUrl(true);
        $companyEmail = CompanySettingsService::getCompanyEmail();
        $companyPhone = CompanySettingsService::getPhone();
        $companyWebsite = CompanySettingsService::getWebsite();
        $companyAddress = CompanySettingsService::getAddress();

        // Merge branding into data if not overridden
        $mergedData = array_merge([
            'themeColors'    => $themeColors,
            'primaryColor'   => $themeColors['primary'],
            'secondaryColor' => $themeColors['secondary'],
            'accentColor'    => $themeColors['accent'],
            'companyName'    => $companyName,
            'companyTagline' => $companyTagline,
            'companyLogoUrl' => $companyLogoUrl,
            'companyEmail'   => $companyEmail,
            'companyPhone'   => $companyPhone,
            'companyWebsite' => $companyWebsite,
            'companyAddress' => $companyAddress,
        ], $data);

        // 1. Render template body content
        extract($mergedData, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // 2. Wrap within master layout
        $layoutPath = $baseDir . 'layouts/master.php';
        if (!file_exists($layoutPath)) {
            $layoutPath = $baseDir . 'layouts/main.php';
        }

        ob_start();
        $subject = $mergedData['subject'] ?? ('Notification — ' . $companyName);
        $previewText = $mergedData['previewText'] ?? $subject;
        require $layoutPath;
        return ob_get_clean();
    }

    /**
     * Convert HTML content into structured plain-text fallback
     */
    public static function htmlToPlainText(string $html): string
    {
        // Convert links <a href="url">text</a> to text (url)
        $text = preg_replace('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 ($1)', $html);
        // Replace headings & breaks with double newlines
        $text = preg_replace('/<\/(h[1-6]|p|div|tr)>/i', "\n", $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<td[^>]*>/i', "  ", $text);
        // Strip remaining tags
        $text = strip_tags($text);
        // Normalize multiple blank lines
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Get list of all registered templates and their human-readable labels
     */
    public static function getRegisteredTemplates(): array
    {
        return [
            'auth/otp'                    => 'Registration OTP',
            'auth/recovery-username-otp'  => 'Username Recovery OTP',
            'auth/recovery-password-otp'  => 'Password Recovery OTP',
            'auth/username-recovered'     => 'Username Recovery Notification',
            'customer/welcome'            => 'Customer Welcome Onboarding',
            'employee/welcome'            => 'Employee Welcome Onboarding',
            'booking/created'             => 'Booking Created (Pending)',
            'booking/confirmed'           => 'Booking Confirmed',
            'booking/cancelled'           => 'Booking Cancelled',
            'booking/driver-assigned'     => 'Customer Chauffeur Assignment',
            'driver/trip-assigned'        => 'Driver Duty Assignment',
            'payment/successful'          => 'Payment Receipt',
            'finance/invoice-notification'=> 'Tax Invoice Copy',
            'finance/refund-confirmation' => 'Refund Confirmation',
            'trip/reminder'               => 'Upcoming Trip Reminder',
            'trip/started'                => 'Trip Live / Started',
            'trip/completed'              => 'Trip Completed',
            'test'                        => 'SMTP Diagnostic Test Message',
        ];
    }

    /**
     * Generate realistic mock sample data for template preview in dashboard
     */
    public function getSampleDataForTemplate(string $templateName): array
    {
        $companyName = CompanySettingsService::getCompanyName();
        $themeColors = CompanySettingsService::getThemeColors();

        $sampleCustomer = [
            'id'            => 101,
            'customer_code' => 'VG-CUST-0101',
            'name'          => 'Vikramaditya Sharma',
            'first_name'    => 'Vikramaditya',
            'email'         => 'vikramaditya.sharma@example.com',
            'phone'         => '+91 98220 54321',
            'city'          => 'Nagpur',
            'status'        => 'active',
        ];

        $sampleEmployee = [
            'id'            => 52,
            'employee_code' => 'VG-EMP-0052',
            'first_name'    => 'Pooja',
            'last_name'     => 'Kulkarni',
            'name'          => 'Pooja Kulkarni',
            'email'         => 'pooja.kulkarni@example.com',
            'phone'         => '+91 98500 12345',
            'department'    => 'Fleet Operations',
            'designation'   => 'Senior Fleet Coordinator',
            'status'        => 'active',
        ];

        $sampleBooking = [
            'id'             => 304,
            'booking_code'   => 'BK-2026-304',
            'customer_name'  => 'Vikramaditya Sharma',
            'customer_email' => 'vikramaditya.sharma@example.com',
            'customer_phone' => '+91 98220 54321',
            'pickup_address' => 'Dr. Babasaheb Ambedkar International Airport (NAG)',
            'drop_address'   => 'Radisson Blu Hotel, Wardha Road, Nagpur',
            'start_date'     => date('Y-m-d', strtotime('+2 days')),
            'end_date'       => date('Y-m-d', strtotime('+3 days')),
            'pickup_time'    => '11:30 AM',
            'passenger_count'=> 3,
            'vehicle_type'   => 'Premium Chauffeur Sedan',
            'base_fare'      => 3500.00,
            'tax_amount'     => 175.00,
            'total_amount'   => 3675.00,
            'status'         => 'confirmed',
            'special_notes'  => 'Flight AI-630 arrival. Baggage assistance requested.',
        ];

        $sampleVehicle = [
            'id'               => 14,
            'vehicle_number'   => 'MH-31-EQ-7788',
            'make_model'       => 'Toyota Innova Crysta (Luxury Cap)',
            'vehicle_type'     => 'SUV / Luxury MPV',
            'seating_capacity' => 7,
            'fuel_type'        => 'Diesel',
            'color'            => 'Pearl White',
        ];

        $sampleDriver = [
            'id'                => 29,
            'name'              => 'Rajesh R. Patil',
            'first_name'        => 'Rajesh',
            'phone'             => '+91 98231 99887',
            'email'             => 'driver.rajesh@example.com',
            'license_number'    => 'MH-31-2019-0044521',
            'emergency_contact' => '+91 98230 11223',
        ];

        $sampleDispatch = [
            'id'           => 189,
            'dispatch_code'=> 'DSP-2026-189',
            'booking_id'   => 304,
            'vehicle_id'   => 14,
            'driver_id'    => 29,
            'reporting_time'=> '11:00 AM (30 mins prior to arrival)',
            'status'       => 'assigned',
        ];

        $sampleInvoice = [
            'id'             => 215,
            'invoice_number' => 'INV-2026-0215',
            'booking_id'     => 304,
            'total_amount'   => 3675.00,
            'paid_amount'    => 3675.00,
            'balance_amount' => 0.00,
            'payment_status' => 'paid',
            'invoice_date'   => date('Y-m-d'),
            'due_date'       => date('Y-m-d'),
        ];

        $samplePayment = [
            'id'             => 412,
            'payment_code'   => 'PAY-2026-0412',
            'invoice_id'     => 215,
            'amount'         => 3675.00,
            'payment_method' => 'UPI / NetBanking',
            'transaction_id' => 'TXN992847102948',
            'paid_at'        => date('Y-m-d H:i:s'),
            'status'         => 'success',
        ];

        $sampleRefund = [
            'id'             => 38,
            'refund_number'  => 'RF-2026-0038',
            'booking_id'     => 304,
            'invoice_id'     => 215,
            'amount'         => 1200.00,
            'refund_method'  => 'Bank Transfer / Original Source',
            'reason'         => 'Trip schedule adjustment prior to dispatch cut-off.',
            'status'         => 'processed',
            'processed_at'   => date('Y-m-d H:i:s'),
        ];

        switch ($templateName) {
            case 'auth/otp':
                return [
                    'subject'        => "Your Verification Code: 4892 — {$companyName}",
                    'previewText'    => "Your 4-digit verification code is 4892. Valid for 5 minutes.",
                    'category'       => 'EMAIL VERIFICATION',
                    'heading'        => 'Verify your email address',
                    'subheading'     => 'Enter the 4-digit verification code below to complete your registration.',
                    'recipientName'  => 'Vikramaditya Sharma',
                    'recipientEmail' => 'vikramaditya.sharma@example.com',
                    'otp'            => '4892',
                    'flow'           => 'customer',
                    'greetingLead'   => "Hello Vikramaditya, please use the 4-digit verification code below to verify your email address.",
                ];

            case 'auth/recovery-username-otp':
                return [
                    'subject'        => "Your account username recovery code — {$companyName}",
                    'previewText'    => "Your 4-digit username recovery code is 7821. Valid for 5 minutes.",
                    'category'       => 'ACCOUNT SECURITY',
                    'heading'        => 'Recover your username',
                    'subheading'     => 'Use this 4-digit code to view your registered account username.',
                    'recipientName'  => 'Vikramaditya Sharma',
                    'recipientEmail' => 'vikramaditya.sharma@example.com',
                    'otp'            => '7821',
                    'purpose'        => 'recovery_username',
                    'greetingLead'   => "Hello Vikramaditya, we received a request to recover your {$companyName} account username.",
                ];

            case 'auth/recovery-password-otp':
                return [
                    'subject'        => "Password Reset Verification Code — {$companyName}",
                    'previewText'    => "Your 4-digit password reset code is 6319. Valid for 5 minutes.",
                    'category'       => 'ACCOUNT SECURITY',
                    'heading'        => 'Reset your account password',
                    'subheading'     => 'Use this 4-digit code to securely verify your identity and set a new password.',
                    'recipientName'  => 'Vikramaditya Sharma',
                    'recipientEmail' => 'vikramaditya.sharma@example.com',
                    'otp'            => '6319',
                    'purpose'        => 'recovery_password',
                    'greetingLead'   => "Hello Vikramaditya, we received a request to reset the password for your {$companyName} account.",
                ];

            case 'auth/username-recovered':
                return [
                    'subject'        => "Your Username Recovery Request — {$companyName}",
                    'previewText'    => "Your account username has been successfully recovered.",
                    'category'       => 'ACCOUNT SECURITY',
                    'heading'        => 'Your username has been recovered',
                    'subheading'     => 'Your registered login username has been securely retrieved.',
                    'recipientName'  => 'Vikramaditya Sharma',
                    'recipientEmail' => 'vikramaditya.sharma@example.com',
                    'username'       => 'vikram_sharma_nagpur',
                    'greetingLead'   => "Hello Vikramaditya, we received a request to recover your account username.",
                ];

            case 'customer/welcome':
                return [
                    'subject'        => "Welcome to {$companyName} — Your Account is Ready",
                    'previewText'    => "Thank you for registering with {$companyName}. Your customer profile is active.",
                    'customer'       => $sampleCustomer,
                    'category'       => 'CUSTOMER ONBOARDING',
                    'heading'        => "Welcome to {$companyName}",
                    'subheading'     => 'Your customer account has been established successfully.',
                ];

            case 'employee/welcome':
                return [
                    'subject'        => "Welcome to the Team — {$companyName}",
                    'previewText'    => "Your employee profile has been created at {$companyName}.",
                    'employee'       => $sampleEmployee,
                    'category'       => 'STAFF ONBOARDING',
                    'heading'        => "Welcome to the Team, Pooja!",
                    'subheading'     => "Your employee profile has been created with role: {$sampleEmployee['designation']}.",
                ];

            case 'booking/created':
                return [
                    'subject'        => "Booking Received: {$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "Your booking request {$sampleBooking['booking_code']} has been received and is pending confirmation.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'category'       => 'BOOKING RESERVATION',
                    'heading'        => 'Booking Request Received',
                    'subheading'     => "Reservation #{$sampleBooking['booking_code']} is under operational review.",
                ];

            case 'booking/confirmed':
                return [
                    'subject'        => "Booking Confirmed: {$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "Great news! Your booking {$sampleBooking['booking_code']} is officially confirmed.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'category'       => 'CONFIRMED RESERVATION',
                    'heading'        => 'Booking Confirmed!',
                    'subheading'     => "Your travel booking #{$sampleBooking['booking_code']} is locked in and ready.",
                ];

            case 'booking/cancelled':
                return [
                    'subject'        => "Booking Cancellation: {$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "Your booking {$sampleBooking['booking_code']} has been cancelled.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'reason'         => 'Client schedule adjustment requested via customer portal.',
                    'category'       => 'BOOKING UPDATE',
                    'heading'        => 'Booking Cancelled',
                    'subheading'     => "Reservation #{$sampleBooking['booking_code']} has been cancelled.",
                ];

            case 'booking/driver-assigned':
                return [
                    'subject'        => "Chauffeur & Fleet Assigned: {$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "Chauffeur {$sampleDriver['name']} and {$sampleVehicle['make_model']} assigned to your trip.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'vehicle'        => $sampleVehicle,
                    'driver'         => $sampleDriver,
                    'dispatch'       => $sampleDispatch,
                    'category'       => 'TRIP OPERATIONS',
                    'heading'        => 'Your Chauffeur Has Been Assigned',
                    'subheading'     => "Chauffeur details for your upcoming trip #{$sampleBooking['booking_code']}.",
                ];

            case 'driver/trip-assigned':
                return [
                    'subject'        => "Duty Assignment: Trip #{$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "New trip assigned to you for {$sampleBooking['start_date']}.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'vehicle'        => $sampleVehicle,
                    'driver'         => $sampleDriver,
                    'dispatch'       => $sampleDispatch,
                    'category'       => 'CHAUFFEUR DUTY DISPATCH',
                    'heading'        => 'New Trip Duty Assigned',
                    'subheading'     => "Please review your schedule and report 30 minutes prior to pickup time.",
                ];

            case 'payment/successful':
                return [
                    'subject'        => "Payment Receipt: {$samplePayment['payment_code']} — {$companyName}",
                    'previewText'    => "Thank you! We received your payment of " . format_currency($samplePayment['amount']) . ".",
                    'payment'        => $samplePayment,
                    'invoice'        => $sampleInvoice,
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'category'       => 'PAYMENT RECEIPT',
                    'heading'        => 'Payment Received Successfully',
                    'subheading'     => "Thank you! Your payment of " . format_currency($samplePayment['amount']) . " has been credited.",
                ];

            case 'finance/invoice-notification':
                return [
                    'subject'        => "Tax Invoice: {$sampleInvoice['invoice_number']} — {$companyName}",
                    'previewText'    => "Your tax invoice {$sampleInvoice['invoice_number']} from {$companyName} is ready.",
                    'invoice'        => $sampleInvoice,
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'category'       => 'TAX INVOICE',
                    'heading'        => "Tax Invoice #{$sampleInvoice['invoice_number']}",
                    'subheading'     => 'Here is the itemized tax invoice copy for your recent booking.',
                ];

            case 'finance/refund-confirmation':
                return [
                    'subject'        => "Refund Processed: {$sampleRefund['refund_number']} — {$companyName}",
                    'previewText'    => "Your refund of " . format_currency($sampleRefund['amount']) . " has been successfully processed.",
                    'refund'         => $sampleRefund,
                    'invoice'        => $sampleInvoice,
                    'customer'       => $sampleCustomer,
                    'category'       => 'FINANCIAL TRANSACTION',
                    'heading'        => 'Refund Processed Successfully',
                    'subheading'     => "Your refund credit of " . format_currency($sampleRefund['amount']) . " has been initiated.",
                ];

            case 'trip/reminder':
                return [
                    'subject'        => "Trip Reminder: Tomorrow's Travel — {$companyName}",
                    'previewText'    => "Reminder for your upcoming trip #{$sampleBooking['booking_code']} tomorrow.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'vehicle'        => $sampleVehicle,
                    'driver'         => $sampleDriver,
                    'dispatch'       => $sampleDispatch,
                    'category'       => 'TRIP REMINDER',
                    'heading'        => "Ready for Tomorrow's Trip?",
                    'subheading'     => "Here is a quick reminder of your itinerary details for booking #{$sampleBooking['booking_code']}.",
                ];

            case 'trip/started':
                return [
                    'subject'        => "Trip Underway: {$sampleBooking['booking_code']} — {$companyName}",
                    'previewText'    => "Your chauffeur has commenced the trip. Have a safe and pleasant journey!",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'vehicle'        => $sampleVehicle,
                    'driver'         => $sampleDriver,
                    'category'       => 'LIVE TRIP STATUS',
                    'heading'        => 'Your Trip is Underway',
                    'subheading'     => 'Chauffeur has started the journey. Live tracking is active.',
                ];

            case 'trip/completed':
                return [
                    'subject'        => "Trip Completed: Thank You for Choosing {$companyName}",
                    'previewText'    => "We hope you enjoyed your journey. Here is your trip summary.",
                    'booking'        => $sampleBooking,
                    'customer'       => $sampleCustomer,
                    'vehicle'        => $sampleVehicle,
                    'category'       => 'TRIP SUMMARY',
                    'heading'        => 'Trip Completed Successfully',
                    'subheading'     => 'Thank you for traveling with us. We hope you had a pleasant experience.',
                ];

            case 'test':
            default:
                $cfg = EmailSettingsService::getDisplaySettings();
                return [
                    'subject'        => "SMTP Diagnostic Test — {$companyName}",
                    'previewText'    => "This is an automated SMTP verification message from {$companyName}.",
                    'recipientEmail' => 'admin@example.com',
                    'smtpHost'       => $cfg['host'],
                    'smtpPort'       => $cfg['port'],
                    'sender'         => $cfg['from_address'],
                    'category'       => 'SYSTEM DIAGNOSTICS',
                    'heading'        => 'SMTP Diagnostic Test Successful',
                    'subheading'     => 'Your mail transport connection is operational and delivering responsive HTML messages.',
                ];
        }
    }
}
