<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\User;

$pdo = Database::getConnection();

$adminUser = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
if (!$adminUser) {
    $adminUser = $pdo->query("SELECT * FROM users LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    if ($adminUser) {
        $adminUser['role'] = 'admin';
    }
}

if (!$adminUser) {
    echo "Admin user not found. Cannot proceed with test.\n";
    exit(1);
}

// Log in admin session using Session helper
Session::start();
Session::setUser([
    'id'    => (int)$adminUser['id'],
    'name'  => $adminUser['name'],
    'email' => $adminUser['email'],
    'role'  => 'admin',
]);
$csrfToken = Csrf::getToken();

$router = new Router();
require __DIR__ . '/../app/routes.php';

$failures = [];
$successCount = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "=== EMAIL CENTER TEST SUITE ===\n\n";

// 1. Test helper functions
assertCondition(function_exists('format_email_type_friendly'), "format_email_type_friendly() helper exists", $failures, $successCount);
assertCondition(function_exists('format_email_status_friendly'), "format_email_status_friendly() helper exists", $failures, $successCount);

assertCondition(format_email_type_friendly('booking/confirmed') === 'Booking Confirmation', "format_email_type_friendly('booking/confirmed') maps to 'Booking Confirmation'", $failures, $successCount);
assertCondition(format_email_type_friendly('customer/welcome') === 'Welcome Email (Customer)', "format_email_type_friendly('customer/welcome') maps to 'Welcome Email (Customer)'", $failures, $successCount);
assertCondition(format_email_type_friendly('payment/successful') === 'Payment Receipt', "format_email_type_friendly('payment/successful') maps to 'Payment Receipt'", $failures, $successCount);
assertCondition(format_email_type_friendly('finance/invoice-notification') === 'Invoice', "format_email_type_friendly('finance/invoice-notification') maps to 'Invoice'", $failures, $successCount);
assertCondition(format_email_type_friendly('driver/trip-assigned') === 'Driver Trip Assignment', "format_email_type_friendly('driver/trip-assigned') maps to 'Driver Trip Assignment'", $failures, $successCount);
assertCondition(format_email_type_friendly('auth/otp') === 'Login / Verification Code', "format_email_type_friendly('auth/otp') maps to friendly OTP label", $failures, $successCount);

$sentStatus = format_email_status_friendly('sent');
assertCondition($sentStatus['label'] === 'Sent' && $sentStatus['badge'] === 'badge-success', "format_email_status_friendly('sent') returns 'Sent' with badge-success", $failures, $successCount);
$failedStatus = format_email_status_friendly('failed');
assertCondition($failedStatus['label'] === 'Failed' && $failedStatus['badge'] === 'badge-danger', "format_email_status_friendly('failed') returns 'Failed' with badge-danger", $failures, $successCount);
$waitingStatus = format_email_status_friendly('pending_retry');
assertCondition($waitingStatus['label'] === 'Waiting' && $waitingStatus['badge'] === 'badge-warning', "format_email_status_friendly('pending_retry') returns 'Waiting' with badge-warning", $failures, $successCount);

// 2. Test GET /office/emails (Email Center main page)
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/emails']);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$html = ob_get_clean();

assertCondition($res && $res->getStatusCode() === 200, "GET /office/emails returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($html, 'Email Center'), "Page contains title 'Email Center'", $failures, $successCount);
assertCondition(str_contains($html, 'Check your email service, send a test email, and view recent email activity'), "Page contains friendly subtitle", $failures, $successCount);
assertCondition(str_contains($html, 'Emails Sent'), "Page contains 'Emails Sent' KPI card", $failures, $successCount);
assertCondition(str_contains($html, 'Emails Failed'), "Page contains 'Emails Failed' KPI card", $failures, $successCount);
assertCondition(str_contains($html, 'Emails Waiting'), "Page contains 'Emails Waiting' KPI card", $failures, $successCount);
assertCondition(str_contains($html, "Today's Emails"), "Page contains 'Today\\'s Emails' KPI card", $failures, $successCount);
assertCondition(str_contains($html, 'Email Service'), "Page contains 'Email Service' section", $failures, $successCount);
assertCondition(str_contains($html, 'Send Test Email'), "Page contains 'Send Test Email' section", $failures, $successCount);
assertCondition(str_contains($html, 'Recent Emails'), "Page contains 'Recent Emails' section", $failures, $successCount);

// Verify technical jargon was removed from user-facing copy
assertCondition(!str_contains($html, 'Transactional Dispatches'), "Technical jargon 'Transactional Dispatches' removed", $failures, $successCount);
assertCondition(!str_contains($html, 'SMTP Socket Handshake'), "Technical jargon 'SMTP Socket Handshake' removed", $failures, $successCount);
assertCondition(!str_contains($html, 'Delivery Audit Trail'), "Technical jargon 'Delivery Audit Trail' removed", $failures, $successCount);

// 3. Test GET /office/emails/history (Email History)
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/emails/history']);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$historyHtml = ob_get_clean();

assertCondition($res && $res->getStatusCode() === 200, "GET /office/emails/history returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($historyHtml, 'Email History'), "History page contains title 'Email History'", $failures, $successCount);
assertCondition(str_contains($historyHtml, 'Search Recipient / Subject'), "History page contains friendly search field", $failures, $successCount);
assertCondition(str_contains($historyHtml, 'Email Records'), "History page contains 'Email Records' table section", $failures, $successCount);

// 4. Test GET /office/emails/preview (Email Templates)
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/emails/preview']);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$previewHtml = ob_get_clean();

assertCondition($res && $res->getStatusCode() === 200, "GET /office/emails/preview returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($previewHtml, 'Email Templates'), "Templates page contains title 'Email Templates'", $failures, $successCount);
assertCondition(str_contains($previewHtml, 'Booking Confirmation'), "Templates page has friendly 'Booking Confirmation' option", $failures, $successCount);
assertCondition(str_contains($previewHtml, 'Payment Receipt'), "Templates page has friendly 'Payment Receipt' option", $failures, $successCount);
assertCondition(str_contains($previewHtml, 'Driver Trip Assignment'), "Templates page has friendly 'Driver Trip Assignment' option", $failures, $successCount);

// 5. Test GET /office/emails/settings (Email Settings)
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/emails/settings']);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$settingsHtml = ob_get_clean();

assertCondition($res && $res->getStatusCode() === 200, "GET /office/emails/settings returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($settingsHtml, 'Email Settings'), "Settings page contains title 'Email Settings'", $failures, $successCount);
assertCondition(str_contains($settingsHtml, 'Sender Information'), "Settings page contains 'Sender Information' card", $failures, $successCount);
assertCondition(str_contains($settingsHtml, 'Email Connection'), "Settings page contains 'Email Connection' card", $failures, $successCount);
assertCondition(str_contains($settingsHtml, 'Check Connection'), "Settings page contains 'Check Connection' button", $failures, $successCount);

// Verify password is not exposed in plain text
$dbMailPass = env('MAIL_PASSWORD', '');
if (!empty($dbMailPass) && strlen($dbMailPass) > 4) {
    assertCondition(!str_contains($settingsHtml, $dbMailPass), "Plaintext SMTP password is NOT exposed in HTML", $failures, $successCount);
}

// 6. Test Sidebar Link in Office Layout
$officeLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/office.php');
assertCondition(str_contains($officeLayout, '<span class="nav-text">Email Center</span>'), "Sidebar navigation displays 'Email Center'", $failures, $successCount);

// 7. Test POST /office/emails/test-smtp (AJAX test connection)
$req = new Request([], [], [
    'REQUEST_METHOD'        => 'POST',
    'REQUEST_URI'           => '/office/emails/test-smtp',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_X_CSRF_TOKEN'     => $csrfToken,
]);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$jsonStr = ob_get_clean();

$json = json_decode($jsonStr, true);
assertCondition(is_array($json) && (isset($json['success']) || isset($json['connected'])), "POST /office/emails/test-smtp returns JSON result", $failures, $successCount);

// 8. Test Details View with Mock Log Entry
$mockLogId = (int)$pdo->query("SELECT id FROM email_logs LIMIT 1")->fetchColumn();
if (!$mockLogId) {
    // Insert temporary simulation log
    $pdo->prepare("INSERT INTO email_logs (recipient_email, recipient_name, email_type, subject, status, html_body, created_at) VALUES ('guest@example.com', 'Test Guest', 'booking/confirmed', 'Your Booking Confirmation', 'sent', '<p>Hello Guest</p>', NOW())")->execute();
    $mockLogId = (int)$pdo->lastInsertId();
    $insertedMock = true;
} else {
    $insertedMock = false;
}

$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/emails/details/{$mockLogId}"]);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$detailHtml = ob_get_clean();

assertCondition($res && $res->getStatusCode() === 200, "GET /office/emails/details/{$mockLogId} returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($detailHtml, 'Email Information'), "Detail view contains 'Email Information'", $failures, $successCount);
assertCondition(str_contains($detailHtml, 'Message Preview'), "Detail view contains 'Message Preview'", $failures, $successCount);
assertCondition(str_contains($detailHtml, 'Technical Details'), "Detail view contains collapsible 'Technical Details'", $failures, $successCount);

if (!empty($insertedMock)) {
    $pdo->prepare("DELETE FROM email_logs WHERE id = ?")->execute([$mockLogId]);
}

echo "\n=== RESULTS ===\n";
echo "Passes: {$successCount}\n";
echo "Failures: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed assertions:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "\nALL EMAIL CENTER TESTS PASSED SUCCESSFULLY!\n";
exit(0);

