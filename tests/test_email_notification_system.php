<?php
/**
 * Test Suite: VoyageGo Professional Email Notification System & Push Removal Verification
 * Tests SMTP config, validation, HTML templates, duplicate protection, event dispatch, PWA & offline support.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\Database;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\Dispatch;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\EmailService;
use App\Services\BookingService;
use App\Services\BillingService;
use App\Services\DispatchService;

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, ?string $details = null): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($details ? " -> {$details}" : "") . "\n";
        $failed++;
    }
}

echo "============================================================\n";
echo "1. VERIFYING SECURE ENVIRONMENT & SMTP CONFIGURATION\n";
echo "============================================================\n";

assertTest(".env loader is operational", Env::isLoaded() || file_exists(dirname(__DIR__) . '/.env'));
assertTest("MAIL_HOST config is 'smtp.gmail.com'", Config::get('mail.host') === 'smtp.gmail.com');
assertTest("MAIL_PORT config is 465", (int)Config::get('mail.port') === 465);
assertTest("MAIL_ENCRYPTION config is 'ssl'", strtolower(Config::get('mail.encryption')) === 'ssl');
assertTest("MAIL_AUTH config is enabled (true)", (bool)Config::get('mail.auth') === true);
assertTest(".env is excluded in .gitignore", str_contains(file_get_contents(dirname(__DIR__) . '/.gitignore'), '.env'));

$envExample = file_get_contents(dirname(__DIR__) . '/.env.example');
assertTest(".env.example template contains MAIL_HOST", str_contains($envExample, 'MAIL_HOST=smtp.gmail.com'));
assertTest(".env.example template contains MAIL_PORT", str_contains($envExample, 'MAIL_PORT=465'));
assertTest(".env.example template contains MAIL_USERNAME", str_contains($envExample, 'MAIL_USERNAME'));
assertTest(".env.example template contains MAIL_PASSWORD", str_contains($envExample, 'MAIL_PASSWORD'));

echo "\n============================================================\n";
echo "2. VERIFYING COMPLETE REMOVAL OF BROWSER PUSH NOTIFICATIONS\n";
echo "============================================================\n";

assertTest("WebPushService.php file is removed", !file_exists(dirname(__DIR__) . '/app/Services/WebPushService.php'));
assertTest("PushSubscriptionController.php is removed", !file_exists(dirname(__DIR__) . '/app/Controllers/PushSubscriptionController.php'));
assertTest("PushSubscription.php model is removed", !file_exists(dirname(__DIR__) . '/app/Models/PushSubscription.php'));
assertTest("config/push.php is removed", !file_exists(dirname(__DIR__) . '/config/push.php'));
assertTest("Migration 031 push_subscriptions is removed", !file_exists(dirname(__DIR__) . '/app/Migrations/031_create_push_subscriptions_table.php'));

$pdo = Database::getConnection();
$tblStmt = $pdo->query("SHOW TABLES LIKE 'push_subscriptions'");
assertTest("Database table 'push_subscriptions' is dropped", $tblStmt->fetch() === false);

$swContent = file_get_contents(dirname(__DIR__) . '/public/sw.js');
assertTest("sw.js does NOT contain 'push' event listener", !str_contains($swContent, "addEventListener('push'"));
assertTest("sw.js does NOT contain 'notificationclick'", !str_contains($swContent, "addEventListener('notificationclick'"));
assertTest("sw.js does NOT contain 'notificationclose'", !str_contains($swContent, "addEventListener('notificationclose'"));

$pwaJsContent = file_get_contents(dirname(__DIR__) . '/public/assets/js/tours-pwa.js');
assertTest("tours-pwa.js does NOT call Notification.requestPermission()", !str_contains($pwaJsContent, "Notification.requestPermission"));
assertTest("tours-pwa.js does NOT use pushManager.subscribe", !str_contains($pwaJsContent, "pushManager.subscribe"));
assertTest("tours-pwa.js does NOT call /api/push/subscribe", !str_contains($pwaJsContent, "/api/push/"));

$routesContent = file_get_contents(dirname(__DIR__) . '/app/routes.php');
assertTest("routes.php does NOT contain PushSubscriptionController", !str_contains($routesContent, "PushSubscriptionController"));
assertTest("routes.php does NOT contain /api/push/", !str_contains($routesContent, "/api/push/"));

echo "\n============================================================\n";
echo "3. VERIFYING PWA FUNCTIONALITY PRESERVATION\n";
echo "============================================================\n";

$manifest = json_decode(file_get_contents(dirname(__DIR__) . '/public/manifest.json'), true);
assertTest("manifest.json exists and is valid", is_array($manifest));
assertTest("PWA App Name is preserved ('Tours')", ($manifest['name'] ?? '') === 'Tours');
assertTest("PWA display is 'standalone'", ($manifest['display'] ?? '') === 'standalone');
assertTest("sw.js retains Network-First caching", str_contains($swContent, "caches.open"));
assertTest("sw.js retains offline fallback shell", str_contains($swContent, "Connection Offline"));
assertTest("tours-pwa.js retains beforeinstallprompt handling", str_contains($pwaJsContent, "beforeinstallprompt"));
assertTest("tours-pwa.js retains install prompt execution", str_contains($pwaJsContent, "promptEvent.prompt()"));

echo "\n============================================================\n";
echo "4. VERIFYING EMAIL LOGS DATABASE & IDEMPOTENCY MODEL\n";
echo "============================================================\n";

$logTblStmt = $pdo->query("SHOW TABLES LIKE 'email_logs'");
assertTest("Database table 'email_logs' exists", (bool)$logTblStmt->fetch());

$emailLogModel = new EmailLog();
$testHash = EmailLog::makeHash('test_event', 'booking', 99999, 'guest@voyagego.com');
assertTest("makeHash generates 64-char sha256 hash", strlen($testHash) === 64);

$logId = $emailLogModel->record(
    'guest@voyagego.com',
    'Test Guest',
    'test_event',
    'Test Email Subject',
    'booking',
    99999,
    'sent',
    null,
    $testHash
);
assertTest("EmailLog::record creates database log entry (ID: {$logId})", $logId > 0);
assertTest("EmailLog::hasDuplicate detects existing log entry", $emailLogModel->hasDuplicate($testHash, 0));

$dummyHash = EmailLog::makeHash('other_event', 'booking', 88888, 'nobody@voyagego.com');
assertTest("EmailLog::hasDuplicate returns false for new unique hash", !$emailLogModel->hasDuplicate($dummyHash, 0));

// Cleanup test entry
$pdo->exec("DELETE FROM `email_logs` WHERE `id` = {$logId}");

echo "\n============================================================\n";
echo "5. VERIFYING REUSABLE EmailService & VALIDATION\n";
echo "============================================================\n";

$emailService = new EmailService();

// Test invalid recipient validation
$invalidResult = $emailService->send(
    'invalid-email-address',
    'Bad User',
    'Test Subject',
    '<p>Hello</p>',
    'Hello',
    'test_validation'
);
assertTest("EmailService rejects invalid email addresses safely", $invalidResult['success'] === false);
assertTest("EmailService logs rejection to email_logs", ($invalidResult['log_id'] ?? 0) > 0);

// Verify plain-text fallback generator
$htmlSample = "<h1>Welcome to VoyageGo</h1><p>Your journey is scheduled.<br>Thank you!</p>";
$plainSample = EmailService::htmlToPlainText($htmlSample);
assertTest("htmlToPlainText converts HTML headings & paragraphs", str_contains($plainSample, "Welcome to VoyageGo"));
assertTest("htmlToPlainText strips HTML tags cleanly", !str_contains($plainSample, "<h1"));

echo "\n============================================================\n";
echo "6. VERIFYING HTML EMAIL TEMPLATES RENDERING\n";
echo "============================================================\n";

$dummyCustomer = ['id' => 1, 'name' => 'Aditya Sharma', 'email' => 'aditya@example.com', 'phone' => '+91 98765 43210'];
$dummyBooking = [
    'id' => 101,
    'booking_code' => 'VG-2026-TEST01',
    'pickup_address' => 'Mumbai International Airport T2',
    'drop_address' => 'JW Marriott, Juhu, Mumbai',
    'start_date' => '2026-11-15',
    'end_date' => '2026-11-16',
    'pickup_time' => '10:30:00',
    'passenger_count' => 3,
    'special_requests' => 'Child seat required',
    'subtotal' => 4500.00,
    'tax_amount' => 225.00,
    'discount_amount' => 0.00,
    'total_amount' => 4725.00,
];
$dummyVehicle = ['id' => 1, 'make_model' => 'Mercedes-Benz E-Class', 'vehicle_number' => 'MH-02-EE-1234'];
$dummyDriver = ['id' => 2, 'name' => 'Sanket Sidurkar', 'email' => 'sanket@voyagego.com', 'phone' => '+91 98220 99999'];
$dummyInvoice = ['id' => 50, 'invoice_number' => 'INV-2026-00150', 'total_amount' => 4725.00, 'paid_amount' => 4725.00];
$dummyPayment = [
    'id' => 77,
    'payment_reference' => 'PAY-20261102-ABC123',
    'amount' => 4725.00,
    'payment_method' => 'upi',
    'transaction_id' => 'TXN-UPI-9876543210',
    'paid_at' => '2026-11-02 10:00:00'
];
$dummyDispatch = [
    'id' => 30,
    'dispatch_code' => 'DSP-261102-8888',
    'booking_id' => 101,
    'vehicle_id' => 1,
    'driver_id' => 2,
    'start_odometer' => 12500,
    'end_odometer' => 12548,
    'start_time' => '2026-11-15 10:30:00',
    'end_time' => '2026-11-15 12:15:00',
];
$dummyUser = ['id' => 1, 'username' => 'aditya_voyage', 'name' => 'Aditya Sharma', 'email' => 'aditya@example.com'];

$templates = [
    'booking/created' => ['booking' => $dummyBooking, 'customer' => $dummyCustomer],
    'booking/confirmed' => ['booking' => $dummyBooking, 'customer' => $dummyCustomer],
    'booking/cancelled' => ['booking' => $dummyBooking, 'customer' => $dummyCustomer, 'reason' => 'Schedule changed by passenger'],
    'payment/successful' => ['payment' => $dummyPayment, 'invoice' => $dummyInvoice, 'booking' => $dummyBooking, 'customer' => $dummyCustomer],
    'driver/trip-assigned' => ['dispatch' => $dummyDispatch, 'booking' => $dummyBooking, 'vehicle' => $dummyVehicle, 'driver' => $dummyDriver, 'customer' => $dummyCustomer],
    'booking/driver-assigned' => ['dispatch' => $dummyDispatch, 'booking' => $dummyBooking, 'vehicle' => $dummyVehicle, 'driver' => $dummyDriver, 'customer' => $dummyCustomer],
    'trip/reminder' => ['dispatch' => $dummyDispatch, 'booking' => $dummyBooking, 'vehicle' => $dummyVehicle, 'driver' => $dummyDriver, 'recipient' => $dummyCustomer],
    'trip/started' => ['dispatch' => $dummyDispatch, 'booking' => $dummyBooking, 'vehicle' => $dummyVehicle, 'driver' => $dummyDriver, 'customer' => $dummyCustomer],
    'trip/completed' => ['dispatch' => $dummyDispatch, 'booking' => $dummyBooking, 'vehicle' => $dummyVehicle, 'driver' => $dummyDriver, 'customer' => $dummyCustomer],
    'account/password-reset' => ['user' => $dummyUser, 'temporaryPassword' => 'TempPass#2026'],
    'test' => ['recipientEmail' => 'admin@voyagegotravels.com', 'smtpHost' => 'smtp.gmail.com', 'smtpPort' => 465, 'sender' => 'contact@voyagego.com'],
];

foreach ($templates as $tmpl => $data) {
    try {
        $html = $emailService->renderTemplate($tmpl, $data);
        assertTest("Template '{$tmpl}' renders valid HTML", str_contains($html, '<!DOCTYPE html>') && (str_contains($html, \App\Services\CompanySettingsService::getCompanyName()) || str_contains($html, e(\App\Services\CompanySettingsService::getCompanyName()))));
    } catch (\Throwable $e) {
        assertTest("Template '{$tmpl}' renders valid HTML", false, $e->getMessage());
    }
}

echo "\n============================================================\n";
echo "7. VERIFYING NON-BLOCKING EVENT INTEGRATION & DUPLICATE PROTECTION\n";
echo "============================================================\n";

// Test duplicate prevention logic on sendBookingCreated
$bookingService = new BookingService();
$billingService = new BillingService();
$dispatchService = new DispatchService();

assertTest("BookingService has EmailService instantiated", (new ReflectionClass($bookingService))->hasProperty('emailService'));
assertTest("BillingService has EmailService instantiated", (new ReflectionClass($billingService))->hasProperty('emailService'));
assertTest("DispatchService has EmailService instantiated", (new ReflectionClass($dispatchService))->hasProperty('emailService'));
assertTest("DispatchService has sendTripReminder method", method_exists($dispatchService, 'sendTripReminder'));

// Test non-blocking behavior: attempt send with dummy unauthenticated SMTP
// It should log a failure safely in email_logs and return false without crashing or throwing an unhandled exception.
$pdo->exec("DELETE FROM `email_logs` WHERE `email_type` = 'test_resilience'");
$safeResult = $emailService->send(
    'test.recipient@example.com',
    'Test Recipient',
    'Non-Blocking Test',
    '<p>Testing non-blocking failure</p>',
    'Testing non-blocking failure',
    'test_resilience',
    'test',
    123
);
assertTest("EmailService fails gracefully without throwing unhandled exceptions", is_array($safeResult));
assertTest("EmailService recorded attempt in email_logs", ($safeResult['log_id'] ?? 0) > 0);

// Verify that passwords are never logged in email_logs
$checkLogStmt = $pdo->query("SELECT error_message FROM `email_logs` WHERE `id` = " . (int)$safeResult['log_id']);
$loggedError = (string)$checkLogStmt->fetchColumn();
assertTest("Email error log does NOT contain raw passwords", !str_contains(strtolower($loggedError), 'password=your-app-password'));

echo "\n============================================================\n";
echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
