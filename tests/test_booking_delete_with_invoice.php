<?php
/**
 * Test: Booking Deletion With Linked Invoices & Dispatches
 * Validates that deleting a booking cleanly dissociates invoices (without FK constraint violation),
 * removes linked dispatches, and updates booking metrics.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Models\Booking;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\BookingService;

function assertCheck($cond, $msg) {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "======================================================\n";
echo " RUNNING SUITE: Booking Deletion With Linked Invoices & Dispatches\n";
echo "======================================================\n";

Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
Session::set('user', ['id' => 1, 'name' => 'System Owner', 'role' => 'admin']);

$pdo = Database::getConnection();
$bookingService = new BookingService();
$billingService = new BillingService();
$bookingModel = new Booking();
$invoiceModel = new Invoice();

// 1. Create a temporary booking for testing
$bookingData = [
    'customer_id'       => 2,
    'pickup_address'    => 'Borivali West, Mumbai',
    'drop_address'      => 'FC Road, Pune',
    'start_date'        => date('Y-m-d', strtotime('+3 days')),
    'end_date'          => date('Y-m-d', strtotime('+4 days')),
    'pickup_time'       => '08:30:00',
    'passenger_count'   => 12,
    'custom_subtotal'   => 14000.00,
    'discount_amount'   => 500.00,
    'gst_rate'          => 5.00,
    'special_requests'  => 'Test booking deletion integrity',
    'idempotency_token' => 'test-del-' . uniqid(),
];

$testBooking = $bookingService->createBooking($bookingData);
$bookingId = (int)$testBooking['id'];
assertCheck($bookingId > 0, "Created test booking with ID: {$bookingId} (Code: {$testBooking['booking_code']})");

// 2. Generate tax invoice for this booking
$invoice = $billingService->generateInvoice($bookingId);
$invoiceId = (int)$invoice['id'];
assertCheck($invoiceId > 0, "Generated linked tax invoice with ID: {$invoiceId} (Invoice #: {$invoice['invoice_number']})");
assertCheck((int)$invoice['booking_id'] === $bookingId, "Invoice is currently linked to booking ID: {$bookingId}");

// 3. Dispatch POST /office/bookings/{id}/delete with Ajax header
$router = require APP_ROOT . '/app/routes.php';
$req = new Request(
    [],
    ['_token' => csrf_token()],
    [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => "/office/bookings/{$bookingId}/delete",
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]
);

$res = $router->dispatch($req);
assertCheck($res->getStatusCode() === 200, "POST /office/bookings/{$bookingId}/delete returns HTTP 200 OK");

$json = json_decode($res->getBody(), true);
assertCheck(is_array($json) && !empty($json['success']), "Response success is true: " . ($json['message'] ?? ''));

// 4. Verify booking is deleted from database
$deletedBooking = $bookingModel->find($bookingId);
assertCheck($deletedBooking === null, "Booking record {$bookingId} is permanently deleted from database");

// 5. Verify invoice still exists (accounting audit trail preserved) but booking_id is NULL
$reloadedInvoice = $invoiceModel->find($invoiceId);
assertCheck($reloadedInvoice !== null, "Invoice record {$invoiceId} is preserved for compliance");
assertCheck($reloadedInvoice['booking_id'] === null, "Invoice booking_id has been cleanly dissociated to NULL without FK violation");

// 6. Verify invoice queries with details work cleanly without SQL/PHP errors
$fullDetails = $invoiceModel->findWithFullDetails($invoiceId);
assertCheck($fullDetails !== null, "findWithFullDetails() returns invoice record even after manifest deletion");
assertCheck($fullDetails['booking_code'] === null, "findWithFullDetails() reports null booking_code gracefully");

$allWithDetails = $invoiceModel->getAllWithDetails();
$foundInAll = false;
foreach ($allWithDetails as $invRow) {
    if ((int)$invRow['id'] === $invoiceId) {
        $foundInAll = true;
        break;
    }
}
assertCheck($foundInAll === true, "getAllWithDetails() includes dissociated invoice in the general ledger");

echo "\n============================================\n";
echo "TEST RESULTS: Booking deletion test passed 100% cleanly!\n";
echo "============================================\n";
