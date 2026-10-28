<?php
/**
 * Test Suite: Booking Create Modern Form & Top Bar Removal Verification
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Models\Booking;
use App\Models\Customer;
use App\Controllers\BookingController;
use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;
use App\Controllers\VehicleController;
use App\Core\Request;

Session::start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$pdo = Database::getConnection();
$bookingModel = new Booking();
$customerModel = new Customer();

$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $message, &$passed, &$failed): void
{
    if ($cond) {
        echo "[PASS] {$message}\n";
        $passed++;
    } else {
        echo "[FAIL] {$message}\n";
        $failed++;
    }
}

echo "=== STARTING BOOKING CREATE MODERN FORM & TOP BAR TESTS ===\n\n";

// ---------------------------------------------------------
// 1. Verify Top Bar Back & Admin Panel Buttons Removed
// ---------------------------------------------------------
echo "--- 1. Testing Removal of Back and Admin Panel Buttons ---\n";

// 1.1 vehicles/create
$vehController = new VehicleController();
$vehRes = $vehController->create(new Request());
$vehContent = $vehRes instanceof \App\Core\Response ? $vehRes->getContent() : (string)$vehRes;
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $vehContent), "vehicles/create: Top bar back button removed", $passed, $failed);
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $vehContent), "vehicles/create: Top bar admin panel button removed", $passed, $failed);

// 1.2 customers/create
$custController = new CustomerController();
$custRes = $custController->create(new Request());
$custContent = $custRes instanceof \App\Core\Response ? $custRes->getContent() : (string)$custRes;
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $custContent), "customers/create: Top bar back button removed", $passed, $failed);
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $custContent), "customers/create: Top bar admin panel button removed", $passed, $failed);

// 1.3 employees/create
$empController = new EmployeeController();
$empRes = $empController->create(new Request());
$empContent = $empRes instanceof \App\Core\Response ? $empRes->getContent() : (string)$empRes;
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $empContent), "employees/create: Top bar back button removed", $passed, $failed);
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $empContent), "employees/create: Top bar admin panel button removed", $passed, $failed);

// ---------------------------------------------------------
// 2. Test GET /office/bookings/create Rendering
// ---------------------------------------------------------
echo "\n--- 2. Testing bookings/create Structure & Stepper Form ---\n";

$bookingController = new BookingController();
$bookRes = $bookingController->create(new Request());
$bookContent = $bookRes instanceof \App\Core\Response ? $bookRes->getContent() : (string)$bookRes;

// Top back button & admin button absent
assertCondition(!preg_match('/<a[^>]*class="[^"]*modern-back-btn/i', $bookContent), "bookings/create: Top modern-back-btn removed", $passed, $failed);
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $bookContent), "bookings/create: Admin panel button absent", $passed, $failed);

// 4-Step Stepper Header
assertCondition(strpos($bookContent, 'Client &amp; Guests') !== false || strpos($bookContent, 'Client & Guests') !== false, "Step 1 'Client & Guests' exists in stepper", $passed, $failed);
assertCondition(strpos($bookContent, 'Itinerary &amp; Route') !== false || strpos($bookContent, 'Itinerary & Route') !== false, "Step 2 'Itinerary & Route' exists in stepper", $passed, $failed);
assertCondition(strpos($bookContent, 'Fare &amp; Billing') !== false || strpos($bookContent, 'Fare & Billing') !== false, "Step 3 'Fare & Billing' exists in stepper", $passed, $failed);
assertCondition(strpos($bookContent, 'Review &amp; Reserve') !== false || strpos($bookContent, 'Review & Reserve') !== false, "Step 4 'Review & Reserve' exists in stepper", $passed, $failed);

// Step 1 Fields
assertCondition(strpos($bookContent, 'id="wizardPanel1"') !== false, "Step 1 panel wizardPanel1 present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="customer_id"') !== false, "Field 'customer_id' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="passenger_count"') !== false, "Field 'passenger_count' is present", $passed, $failed);

// Step 2 Fields
assertCondition(strpos($bookContent, 'id="wizardPanel2"') !== false, "Step 2 panel wizardPanel2 present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="pickup_address"') !== false, "Field 'pickup_address' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="drop_address"') !== false, "Field 'drop_address' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="start_date"') !== false, "Field 'start_date' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="end_date"') !== false, "Field 'end_date' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="pickup_time"') !== false, "Field 'pickup_time' is present", $passed, $failed);

// Step 3 Fields
assertCondition(strpos($bookContent, 'id="wizardPanel3"') !== false, "Step 3 panel wizardPanel3 present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="custom_subtotal"') !== false, "Field 'custom_subtotal' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="discount_percent"') !== false, "Field 'discount_percent' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="include_gst"') !== false, "Field 'include_gst' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'id="gst_rate"') !== false, "Field 'gst_rate' is present", $passed, $failed);
assertCondition(strpos($bookContent, 'fare-receipt-card') !== false, "Live fare receipt card is present", $passed, $failed);

// Step 4 Review & Dossier
assertCondition(strpos($bookContent, 'id="wizardPanel4"') !== false, "Step 4 panel wizardPanel4 present", $passed, $failed);
assertCondition(strpos($bookContent, 'review-hero-banner') !== false, "Executive Hero banner present in Step 4", $passed, $failed);
assertCondition(strpos($bookContent, 'review-edit-jump-btn') !== false, "Jump edit buttons present on review cards", $passed, $failed);
assertCondition(strpos($bookContent, 'id="btnSubmitBooking"') !== false, "Submit reservation button is present", $passed, $failed);

// ---------------------------------------------------------
// 3. Test POST /office/bookings Reservation Creation
// ---------------------------------------------------------
echo "\n--- 3. Testing POST /office/bookings Reservation Creation ---\n";

// Find or create test customer
$testCustomer = $customerModel->findBy('phone', '9876500001');
if (!$testCustomer) {
    $custId = $customerModel->create([
        'customer_code' => 'VG-CUST-TST',
        'name'          => 'Test Customer Booking Form',
        'phone'         => '9876500001',
        'email'         => 'testcustbooking@example.internal',
        'status'        => 'active',
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ]);
    $testCustomer = $customerModel->find($custId);
}

$postPayload = [
    'customer_id'       => (string)$testCustomer['id'],
    'pickup_address'    => 'Terminal 3, Indira Gandhi International Airport, New Delhi',
    'drop_address'      => 'The Oberoi Rajvilas, Babaji Ka Thikana, Jaipur, Rajasthan',
    'start_date'        => date('Y-m-d', strtotime('+2 days')),
    'end_date'          => date('Y-m-d', strtotime('+5 days')),
    'pickup_time'       => '10:30',
    'passenger_count'   => '4',
    'special_requests'  => 'Executive chauffeur requested with nameboard greeting',
    'custom_subtotal'   => '18000.00',
    'discount_percent'  => '10.0',
    'include_gst'       => '1',
    'gst_rate'          => '5.0',
    'idempotency_token' => bin2hex(random_bytes(16)),
];

$postRequest = new Request($postPayload);
$postRes = $bookingController->store($postRequest);

// Check if booking was created
$createdBooking = $pdo->query("SELECT * FROM `bookings` WHERE `pickup_address` LIKE '%Terminal 3%' ORDER BY `id` DESC LIMIT 1")->fetch();
assertCondition(!empty($createdBooking), "Booking was created in database", $passed, $failed);

if (!empty($createdBooking)) {
    assertCondition(!empty($createdBooking['booking_code']), "Booking has generated code ({$createdBooking['booking_code']})", $passed, $failed);
    assertCondition((int)$createdBooking['passenger_count'] === 4, "Passenger count saved as 4", $passed, $failed);
    assertCondition((float)$createdBooking['subtotal'] === 18000.00, "Subtotal saved as 18000.00", $passed, $failed);
    assertCondition((float)$createdBooking['discount_amount'] === 1800.00, "10% discount calculated as 1800.00 (Actual: {$createdBooking['discount_amount']})", $passed, $failed);

    // Clean up created test booking
    $pdo->exec("DELETE FROM `invoices` WHERE `booking_id` = {$createdBooking['id']}");
    $pdo->exec("DELETE FROM `dispatches` WHERE `booking_id` = {$createdBooking['id']}");
    $pdo->exec("DELETE FROM `bookings` WHERE `id` = {$createdBooking['id']}");
    echo "[INFO] Cleaned up temporary test booking ID {$createdBooking['id']}\n";
}

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
