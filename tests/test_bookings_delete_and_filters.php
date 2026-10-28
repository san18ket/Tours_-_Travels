<?php
/**
 * Test: Bookings Top Cards, Active Filter, Delete Action Button & 2-Step Delete Modals
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Controllers\BookingController;
use App\Core\Request;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;

$pdo = Database::getConnection();

function assertCondition($condition, $message) {
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "RUNNING TEST: Bookings Top Cards, Filters & 2-Step Delete Modal System\n";
echo "====================================================================\n\n";

// 1. Verify View Structure in GET /office/bookings
echo "1. Verifying View Structure in GET /office/bookings...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/bookings';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$request = new Request();
$controller = new BookingController();
$response = $controller->index($request);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "office/bookings returns HTTP 200");

// Top KPI Cards
assertCondition(strpos($html, 'id="bookingsKpiGrid"') !== false, "Contains #bookingsKpiGrid KPI cards container");
assertCondition(strpos($html, 'data-status=""') !== false, "Contains Card 1 (Total Bookings) with data-status=''");
assertCondition(strpos($html, 'data-status="active"') !== false, "Contains Card 2 (Active & Transit) with data-status='active'");
assertCondition(strpos($html, 'data-status="pending"') !== false, "Contains Card 3 (Pending Dispatch) with data-status='pending'");
assertCondition(strpos($html, 'data-status="completed"') !== false, "Contains Card 4 (Completed Tours) with data-status='completed'");

// Filter bar status options
assertCondition(strpos($html, 'id="statusInput"') !== false, "Contains #statusInput select");
assertCondition(strpos($html, 'value="active"') !== false, "Status filter dropdown contains option value='active'");

// Delete buttons in table & mobile cards action popup
assertCondition(strpos($html, 'class="action-btn-table delete"') !== false, "Action menus contain .action-btn-table.delete button");
assertCondition(strpos($html, 'openDeleteModal(') !== false, "Delete button triggers openDeleteModal() handler");
assertCondition(strpos($html, 'fa-trash-can') !== false, "Delete button displays fa-trash-can icon");

// 2-Step Delete Modals
assertCondition(strpos($html, 'id="deleteModalBackdrop"') !== false, "Contains #deleteModalBackdrop container");
assertCondition(strpos($html, 'id="deleteStep1Modal"') !== false, "Contains Modal 1: #deleteStep1Modal");
assertCondition(strpos($html, 'id="deleteStep1Checkbox"') !== false, "Modal 1 contains confirmation checkbox #deleteStep1Checkbox");
assertCondition(strpos($html, 'id="deleteStep1Btn"') !== false, "Modal 1 contains proceed button #deleteStep1Btn");

assertCondition(strpos($html, 'id="deleteStep2Modal"') !== false, "Contains Modal 2: #deleteStep2Modal");
assertCondition(strpos($html, 'id="delModalTitle2"') !== false, "Modal 2 contains title 'Are you sure?'");
assertCondition(strpos($html, 'id="deleteStep2Checkbox"') !== false, "Modal 2 contains final confirmation checkbox #deleteStep2Checkbox");
assertCondition(strpos($html, 'id="deleteConfirmFinalBtn"') !== false, "Modal 2 contains final action button #deleteConfirmFinalBtn");
assertCondition(strpos($html, 'id="deleteBookingForm"') !== false, "Contains fallback form #deleteBookingForm");

// JavaScript handlers & CSS
assertCondition(strpos($html, 'function openDeleteModal') !== false, "Contains openDeleteModal JS function");
assertCondition(strpos($html, 'function closeDeleteModal') !== false, "Contains closeDeleteModal JS function");
assertCondition(strpos($html, 'function goToStep2Modal') !== false, "Contains goToStep2Modal JS function");
assertCondition(strpos($html, 'function confirmDeleteFinal') !== false, "Contains confirmDeleteFinal JS function");
assertCondition(strpos($html, '.vg-del-backdrop') !== false, "Contains .vg-del-backdrop modal styling");
assertCondition(strpos($html, '.vg-del-modal') !== false, "Contains .vg-del-modal card styling");
assertCondition(strpos($html, '.action-btn-table.delete') !== false, "Contains .action-btn-table.delete styling");

// 2. Verify Active Filter Execution
echo "\n2. Verifying 'active' Filter in BookingController...\n";
$_GET['status'] = 'active';
$activeReq = new Request();
$activeResp = $controller->index($activeReq);
assertCondition($activeResp->getStatusCode() === 200, "Filter by status=active returns HTTP 200");
unset($_GET['status']);

// 3. Functional AJAX Delete Verification
echo "\n3. Verifying AJAX Booking Deletion & Cascade Clean...\n";

// Get or create test tour
$tourModel = new Tour();
$tours = $tourModel->all();
$tourId = !empty($tours[0]['id']) ? (int)$tours[0]['id'] : null;

// Get or create test customer user
$userModel = new User();
$users = $userModel->where("role = 'customer'");
$customerId = !empty($users[0]['id']) ? (int)$users[0]['id'] : null;

// Create test booking
$bookingModel = new Booking();
$testBookingCode = 'BK-DEL-' . time();
$testBookingId = $bookingModel->create([
    'booking_code' => $testBookingCode,
    'customer_id'  => $customerId,
    'tour_id'      => $tourId,
    'start_date'   => date('Y-m-d', strtotime('+7 days')),
    'end_date'     => date('Y-m-d', strtotime('+10 days')),
    'seats'        => 2,
    'total_amount' => 15000.00,
    'status'       => 'pending',
    'created_at'   => date('Y-m-d H:i:s'),
    'updated_at'   => date('Y-m-d H:i:s'),
]);
echo "  [PASS] Created temporary test booking ID: {$testBookingId} with code: {$testBookingCode}\n";

// Optionally link a mock dispatch record if drivers & vehicles exist
$driverStmt = $pdo->query("SELECT id FROM driver_profiles LIMIT 1");
$driver = $driverStmt ? $driverStmt->fetch(PDO::FETCH_ASSOC) : null;
$vehicleStmt = $pdo->query("SELECT id FROM vehicles LIMIT 1");
$vehicle = $vehicleStmt ? $vehicleStmt->fetch(PDO::FETCH_ASSOC) : null;

$dispId = null;
if (!empty($driver['id']) && !empty($vehicle['id'])) {
    $stmtDisp = $pdo->prepare("INSERT INTO `dispatches` (`booking_id`, `driver_id`, `vehicle_id`, `assigned_by`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, 1, 'pending', NOW(), NOW())");
    $stmtDisp->execute([$testBookingId, $driver['id'], $vehicle['id']]);
    $dispId = $pdo->lastInsertId();
    echo "  [PASS] Created linked test dispatch record ID: {$dispId}\n";
}

// Send AJAX Delete request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = "/office/bookings/{$testBookingId}/delete";
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST['_csrf_token'] = csrf_token();
$_POST['csrf_token'] = csrf_token();

$delReq = new Request();
$delResp = $controller->delete($delReq, ['id' => $testBookingId]);

assertCondition($delResp->getStatusCode() === 200, "Booking delete endpoint returns HTTP 200 on AJAX");
$delJson = json_decode((string)$delResp->getContent(), true);
assertCondition(!empty($delJson['success']) && $delJson['success'] === true, "Delete response JSON success === true");
assertCondition(!empty($delJson['message']) && strpos($delJson['message'], $testBookingCode) !== false, "Response message confirms booking manifest deletion");
assertCondition(isset($delJson['metrics']['total']), "Response contains updated metrics payload for KPI cards");

// Verify DB: Booking record should be removed
$foundBooking = $bookingModel->find($testBookingId);
assertCondition(empty($foundBooking), "Booking record removed from database");

// Verify DB: Linked dispatch record should be removed if created
if ($dispId) {
    $stmtCheckDisp = $pdo->prepare("SELECT * FROM `dispatches` WHERE `id` = ?");
    $stmtCheckDisp->execute([$dispId]);
    $foundDisp = $stmtCheckDisp->fetch();
    assertCondition(empty($foundDisp), "Linked dispatch record removed from database");
}

echo "\n====================================================================\n";
echo "SUMMARY: All Bookings top cards, filters & delete assertions passed!\n";
echo "====================================================================\n";
