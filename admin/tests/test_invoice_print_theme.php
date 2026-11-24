<?php
/**
 * Test Suite: Customer Invoice Print Matching Theme & Verification
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Models\Invoice;

echo "=== TEST: CUSTOMER INVOICE PRINT MATCHING THEME SUITE ===\n\n";

$pdo = Database::getConnection();
$invoiceModel = new Invoice();

// Find sample invoice
$inv = $invoiceModel->findWithFullDetails(1);
if (!$inv) {
    echo "No invoice #1 found, testing with first available invoice...\n";
    $first = $pdo->query("SELECT id FROM invoices ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($first) {
        $inv = $invoiceModel->findWithFullDetails((int)$first['id']);
    }
}

if (!$inv) {
    echo "Creating dummy test invoice...\n";
    $pdo->query("INSERT INTO invoices (invoice_number, booking_id, customer_id, customer_profile_id, subtotal, tax_rate, tax_amount, discount_amount, total_amount, paid_amount, payment_status, due_date, is_immutable, issued_at, created_at)
                 VALUES ('INV-2026-TEST01', 1, 1, 1, 10000.00, 5.00, 500.00, 0.00, 10500.00, 10500.00, 'paid', '2026-11-20', 1, NOW(), NOW())");
    $invId = (int)$pdo->lastInsertId();
    $inv = $invoiceModel->findWithFullDetails($invId);
}

$invoiceId = (int)$inv['id'];
echo "Testing with Invoice ID: {$invoiceId} ({$inv['invoice_number']})\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $message, &$passCount, &$failCount) {
    if ($condition) {
        echo " [PASS] {$message}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$message}\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// 1. Enhanced Invoice Model: Full Details Query
// -------------------------------------------------------------
echo "1. Testing Enhanced Invoice Model findWithFullDetails...\n";
assertCondition(!empty($inv['customer_name']), "Customer name present: {$inv['customer_name']}", $passCount, $failCount);
assertCondition(!empty($inv['customer_code']), "Customer code present: {$inv['customer_code']}", $passCount, $failCount);
assertCondition(isset($inv['customer_address']), "Customer address field present", $passCount, $failCount);
assertCondition(isset($inv['vehicle_number']), "Vehicle number field present: " . ($inv['vehicle_number'] ?: 'N/A'), $passCount, $failCount);
assertCondition(isset($inv['driver_name']), "Driver name field present: " . ($inv['driver_name'] ?: 'N/A'), $passCount, $failCount);

// -------------------------------------------------------------
// 2. Testing Routes & Permissions on /office/invoices/{id}/print
// -------------------------------------------------------------
echo "\n2. Testing Roles & Permissions on /office/invoices/{id}/print...\n";

$router = new Router();
require __DIR__ . '/../app/routes.php';

// Test Admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['role'] = 'admin';
$adminReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/invoices/{$invoiceId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$adminResp = $router->dispatch($adminReq);
assertCondition($adminResp->getStatusCode() === 200, "Admin can access /office/invoices/{id}/print (HTTP 200)", $passCount, $failCount);

// Test Dispatcher
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'dispatcher';
$_SESSION['role'] = 'dispatcher';
$dispReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/invoices/{$invoiceId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$dispResp = $router->dispatch($dispReq);
assertCondition($dispResp->getStatusCode() === 200, "Dispatcher can access /office/invoices/{id}/print (HTTP 200)", $passCount, $failCount);

// Test Driver
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'driver';
$_SESSION['role'] = 'driver';
$drvReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/invoices/{$invoiceId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$drvResp = $router->dispatch($drvReq);
assertCondition($drvResp->getStatusCode() === 200, "Driver can access /office/invoices/{id}/print (HTTP 200)", $passCount, $failCount);

// -------------------------------------------------------------
// 3. Testing Print Template Matching Theme Features
// -------------------------------------------------------------
echo "\n3. Testing Print Template Matching Theme Features...\n";
$html = $adminResp->getContent();

assertCondition(strpos($html, 'class="toolbar"') !== false, "Contains interactive top toolbar", $passCount, $failCount);
assertCondition(strpos($html, 'class="swatches-track"') !== false, "Contains 8-swatch theme switcher track", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#10B981"') !== false, "Contains Emerald theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#0EA5E9"') !== false, "Contains Sky Blue theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#6366F1"') !== false, "Contains Indigo theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#F59E0B"') !== false, "Contains Amber theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#EF4444"') !== false, "Contains Red theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#8B5CF6"') !== false, "Contains Violet theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#1e3a5f"') !== false, "Contains Navy theme swatch", $passCount, $failCount);
assertCondition(strpos($html, 'data-color="#064e3b"') !== false, "Contains Forest theme swatch", $passCount, $failCount);

assertCondition(strpos($html, 'id="invoiceData"') !== false, "Contains #invoiceData element for dynamic rendering", $passCount, $failCount);
assertCondition(strpos($html, 'qrcode.min.js') !== false, "Contains QR code library", $passCount, $failCount);
assertCondition(strpos($html, 'id="qrContainer"') !== false, "Contains #qrContainer for dynamic QR canvas", $passCount, $failCount);
assertCondition(strpos($html, 'function buildInvoice') !== false, "Contains buildInvoice(c) dynamic theme compiler", $passCount, $failCount);
assertCondition(strpos($html, 'function triggerPrint') !== false, "Contains triggerPrint() function", $passCount, $failCount);
assertCondition(strpos($html, '@page') !== false && strpos($html, 'A4 portrait') !== false, "Configured with @page A4 portrait print rules", $passCount, $failCount);
assertCondition(strpos($html, 'Customer Tax Invoice') !== false, "Displays official Customer Tax Invoice header", $passCount, $failCount);

// -------------------------------------------------------------
// 4. Testing Public Digital Invoice Verification & Print Routes
// -------------------------------------------------------------
echo "\n4. Testing Public Digital Invoice Verification & Print Routes...\n";

// Clear session to test guest/public access
$_SESSION = [];

$verifyReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/invoice/verify/{$invoiceId}",
    'SCRIPT_NAME'    => '/index.php'
]);
$verifyResp = $router->dispatch($verifyReq);
assertCondition($verifyResp->getStatusCode() === 200, "Public visitor gets HTTP 200 on /invoice/verify/{id}", $passCount, $failCount);
assertCondition(strpos($verifyResp->getContent(), 'Digital Tax Invoice') !== false, "Verification view contains digital invoice badge", $passCount, $failCount);
assertCondition(strpos($verifyResp->getContent(), "/invoice/verify/{$invoiceId}/print") !== false, "Verification view links to /invoice/verify/{id}/print", $passCount, $failCount);

$pubPrintReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/invoice/verify/{$invoiceId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$pubPrintResp = $router->dispatch($pubPrintReq);
assertCondition($pubPrintResp->getStatusCode() === 200, "Public visitor gets HTTP 200 on /invoice/verify/{id}/print", $passCount, $failCount);
assertCondition(strpos($pubPrintResp->getContent(), 'class="toolbar"') !== false, "Public print view renders matching theme printable document", $passCount, $failCount);

// -------------------------------------------------------------
// 5. Testing Quick Print Buttons in office/invoices/index.php
// -------------------------------------------------------------
echo "\n5. Testing Quick Print Buttons in office/invoices/index.php...\n";

$indexContent = file_get_contents(__DIR__ . '/../app/Views/office/invoices/index.php');
assertCondition(strpos($indexContent, "url('/office/invoices/' . \$inv['id'] . '/print')") !== false, "Index contains print URLs for invoices", $passCount, $failCount);
assertCondition(strpos($indexContent, 'Print Invoice (Theme)') !== false, "Index contains 'Print Invoice (Theme)' action dropdown items", $passCount, $failCount);
assertCondition(strpos($indexContent, 'title="Print Invoice (Interactive Theme)"') !== false, "Table row has direct quick print action button", $passCount, $failCount);
assertCondition(strpos($indexContent, 'title="Print Invoice"') !== false, "Mobile cards have direct quick print action button", $passCount, $failCount);

echo "\n======================================================\n";
echo "SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================\n";

if ($failCount > 0) {
    exit(1);
}

