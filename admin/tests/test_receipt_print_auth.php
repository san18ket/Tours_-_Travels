<?php
/**
 * Test Suite: Driver Payment Receipt Print Authorization & Permissions
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Models\DriverTripPayment;

echo "=== TEST: DRIVER PAYMENT RECEIPT PRINT PERMISSION FIX ===\n\n";

$pdo = Database::getConnection();
$paymentModel = new DriverTripPayment($pdo);

// Find existing receipt #1 or create one if needed
$receipt1 = $paymentModel->findWithFullReceiptDetails(1);
if (!$receipt1) {
    echo "Creating dummy receipt for testing...\n";
    // Check if there is any receipt
    $anyReceipt = $pdo->query("SELECT id FROM driver_trip_payments ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($anyReceipt) {
        $receiptId = (int)$anyReceipt['id'];
        $receipt1 = $paymentModel->findWithFullReceiptDetails($receiptId);
    }
}
$receiptId = (int)($receipt1['id'] ?? 1);
$driverProfileId = (int)($receipt1['driver_id'] ?? 1);

// Find user_id for this driver profile
$stmt = $pdo->prepare("SELECT user_id FROM driver_profiles WHERE id = ?");
$stmt->execute([$driverProfileId]);
$driverUserId = (int)$stmt->fetchColumn();

echo "Testing with Receipt ID: {$receiptId}, Driver Profile ID: {$driverProfileId}, Driver User ID: {$driverUserId}\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $message, &$passCount, &$failCount) {
    if ($condition) {
        echo " [PASS] {$message}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$message}\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// Test 1: Driver logged in (user_id = $driverUserId, role = 'driver')
// Accessing /office/finance/driver-payments/receipt/{id}/print
// -------------------------------------------------------------
echo "1. Testing Driver Access to /office/finance/driver-payments/receipt/{id}/print...\n";

// Set up Router
$router = new Router();
require __DIR__ . '/../app/routes.php';

// Simulate driver login session
$_SESSION['user_id'] = $driverUserId;
$_SESSION['user_role'] = 'driver';
$_SESSION['role'] = 'driver';
$_SESSION['username'] = 'test_driver';

$request = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/finance/driver-payments/receipt/{$receiptId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);

$response = $router->dispatch($request);
assertTest($response instanceof Response, "Router dispatched request to Response object", $passCount, $failCount);
assertTest($response->getStatusCode() === 200, "Driver gets HTTP 200 (not 403) for their own receipt", $passCount, $failCount);
assertTest(strpos($response->getContent(), 'Driver Payment Voucher') !== false || strpos($response->getContent(), 'Payment Receipt') !== false, "Response contains printable receipt content", $passCount, $failCount);

// -------------------------------------------------------------
// Test 2: Driver trying to access another driver's receipt (IDOR check)
// -------------------------------------------------------------
echo "\n2. Testing IDOR Protection: Driver accessing another driver's receipt...\n";

// Create or find a receipt belonging to another driver
$otherStmt = $pdo->prepare("SELECT id, driver_id FROM driver_trip_payments WHERE driver_id != ? AND status != 'reversed' LIMIT 1");
$otherStmt->execute([$driverProfileId]);
$otherReceipt = $otherStmt->fetch(PDO::FETCH_ASSOC);

if ($otherReceipt) {
    $otherId = (int)$otherReceipt['id'];
    $otherRequest = new Request([], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => "/office/finance/driver-payments/receipt/{$otherId}/print",
        'SCRIPT_NAME'    => '/index.php'
    ]);
    $otherResponse = $router->dispatch($otherRequest);
    assertTest($otherResponse->getStatusCode() === 403, "Driver blocked with HTTP 403 when accessing another driver's receipt (ID: {$otherId})", $passCount, $failCount);
} else {
    echo " (Skipped other receipt check - no secondary driver receipt found in DB)\n";
}

// -------------------------------------------------------------
// Test 3: Admin logged in accessing /office/finance/driver-payments/receipt/{id}/print
// -------------------------------------------------------------
echo "\n3. Testing Admin Access to /office/finance/driver-payments/receipt/{id}/print...\n";

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

$adminRequest = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/finance/driver-payments/receipt/{$receiptId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$adminResponse = $router->dispatch($adminRequest);
assertTest($adminResponse->getStatusCode() === 200, "Admin gets HTTP 200 for receipt print", $passCount, $failCount);

// -------------------------------------------------------------
// Test 4: Dispatcher logged in accessing /office/finance/driver-payments/receipt/{id}/print
// -------------------------------------------------------------
echo "\n4. Testing Dispatcher Access to /office/finance/driver-payments/receipt/{id}/print...\n";

$_SESSION['user_id'] = 99;
$_SESSION['user_role'] = 'dispatcher';
$_SESSION['role'] = 'dispatcher';
$_SESSION['username'] = 'dispatcher_user';

$dispRequest = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/finance/driver-payments/receipt/{$receiptId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$dispResponse = $router->dispatch($dispRequest);
assertTest($dispResponse->getStatusCode() === 200, "Dispatcher gets HTTP 200 for receipt print", $passCount, $failCount);

// -------------------------------------------------------------
// Test 5: Public Access to /receipt/verify/{id}/print (Unauthenticated)
// -------------------------------------------------------------
echo "\n5. Testing Public Unauthenticated Access to /receipt/verify/{id}/print...\n";

// Clear session completely
$_SESSION = [];

$publicPrintRequest = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/receipt/verify/{$receiptId}/print",
    'SCRIPT_NAME'    => '/index.php'
]);
$publicPrintResponse = $router->dispatch($publicPrintRequest);
assertTest($publicPrintResponse->getStatusCode() === 200, "Public visitor gets HTTP 200 for /receipt/verify/{id}/print", $passCount, $failCount);
assertTest(strpos($publicPrintResponse->getContent(), 'Driver Payment Voucher') !== false, "Public print contains printable voucher", $passCount, $failCount);

// -------------------------------------------------------------
// Test 6: Driver graceful redirect from office receipt view
// -------------------------------------------------------------
echo "\n6. Testing Driver Graceful Redirect from /office/finance/driver-payments/receipt/{id}...\n";

$_SESSION['user_id'] = $driverUserId;
$_SESSION['user_role'] = 'driver';
$_SESSION['role'] = 'driver';

$driverOfficeViewRequest = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => "/office/finance/driver-payments/receipt/{$receiptId}",
    'SCRIPT_NAME'    => '/index.php'
]);
$driverOfficeViewResponse = $router->dispatch($driverOfficeViewRequest);
assertTest($driverOfficeViewResponse->getStatusCode() === 302, "Driver is redirected (HTTP 302) from office receipt view", $passCount, $failCount);
$headers = $driverOfficeViewResponse->getHeaders();
assertTest(isset($headers['Location']) && strpos($headers['Location'], "/driver/payments/receipt/{$receiptId}") !== false, "Driver is redirected to /driver/payments/receipt/{$receiptId}", $passCount, $failCount);

// -------------------------------------------------------------
// Test 7: Verification of $printUrl in receipt_verify.php
// -------------------------------------------------------------
echo "\n7. Testing receipt_verify.php print URL target...\n";
$verifyViewContent = file_get_contents(__DIR__ . '/../app/Views/office/finance/receipt_verify.php');
assertTest(strpos($verifyViewContent, "url('/receipt/verify/' . (int)\$receipt['id'] . '/print')") !== false, "receipt_verify.php links to public /receipt/verify/{id}/print", $passCount, $failCount);

echo "\n======================================================\n";
echo "SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================\n";

if ($failCount > 0) {
    exit(1);
}

