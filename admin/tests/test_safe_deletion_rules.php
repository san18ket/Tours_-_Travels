<?php
/**
 * Test Suite: Safe Deletion Rules for Employees, Customers, and Bookings
 *
 * Verifies all 17 required scenarios:
 * 1. Employee with no trips -> Delete allowed.
 * 2. Employee with an active trip -> Blocked.
 * 3. Employee with a pending trip -> Blocked.
 * 4. Employee with completed trip and pending salary/payment -> Blocked.
 * 5. Employee with completed trip and settled payment -> Delete allowed.
 * 6. Employee with multiple trips where one payment is pending -> Blocked.
 * 7. Customer with no bookings -> Delete allowed.
 * 8. Customer with active booking -> Blocked.
 * 9. Customer with pending booking -> Blocked.
 * 10. Customer with completed booking and pending payment -> Blocked.
 * 11. Customer with completed booking and settled payment -> Delete allowed.
 * 12. Customer with multiple bookings where one payment is pending -> Blocked.
 * 13. Booking that is pending -> Blocked.
 * 14. Booking that is active/in progress -> Blocked.
 * 15. Booking completed with pending customer payment -> Blocked.
 * 16. Booking completed with pending employee payment -> Blocked.
 * 17. Booking completed with all payments settled -> Delete allowed.
 *
 * Also verifies:
 * - Exact warning and success messages
 * - Zero exposure of SQLSTATE, queries, table names, or technical IDs
 * - Controller AJAX & HTTP redirect integration
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\SafeDeletionService;
use App\Controllers\EmployeeController;
use App\Controllers\CustomerController;
use App\Controllers\BookingController;
use App\Core\Request;
use App\Core\Session;

$pdo = Database::getConnection();
$service = new SafeDeletionService();

$passed = 0;
$failed = 0;

function it(string $description, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo "[PASS] " . $description . PHP_EOL;
        $passed++;
    } catch (\Throwable $e) {
        echo "[FAIL] " . $description . " - ERROR: " . $e->getMessage() . PHP_EOL;
        $failed++;
    }
}

function assertNoTechnicalLeak(string $message): void {
    $forbidden = ['SQLSTATE', 'CONSTRAINT', 'FOREIGN KEY', 'SELECT ', 'INSERT INTO', 'UPDATE ', 'DELETE FROM', 'dispatches', 'driver_trip_payments', 'payroll_items', 'tbl_', 'pdo'];
    foreach ($forbidden as $word) {
        if (stripos($message, $word) !== false) {
            throw new \Exception("Message leaked technical detail ('{$word}'): {$message}");
        }
    }
}

echo "========================================================" . PHP_EOL;
echo "  SAFE DELETION RULES TEST SUITE (17 SCENARIOS + UX)   " . PHP_EOL;
echo "========================================================" . PHP_EOL;

// -----------------------------------------------------------------------------
// HELPER: Generate unique test codes
// -----------------------------------------------------------------------------
$rand = bin2hex(random_bytes(4));

// =============================================================================
// EMPLOYEE SCENARIOS (1 - 6)
// =============================================================================

// Scenario 1: Employee with no trips -> Delete allowed
it("Scenario 1: Employee with no trips -> Delete allowed", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `email`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp1', 'NoTrips', '9999900001', 'emp1_{$rand}@test.com', 'operations', 'Staff', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T1-{$rand}"]);
    $empId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if (!$res['allowed']) {
        throw new \Exception("Expected allowed=true, got blocked with message: {$res['message']}");
    }
    if ($res['message'] !== 'Employee deleted successfully.') {
        throw new \Exception("Unexpected message: {$res['message']}");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$empId]);
});

// Scenario 2: Employee with an active trip -> Blocked
it("Scenario 2: Employee with an active trip -> Blocked", function() use ($pdo, $service, $rand) {
    // Create employee + driver profile + booking + active dispatch (trip_started)
    $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('DriverUser2', 'usr2_{$rand}', 'usr2_{$rand}@test.com', 'hash', 'driver', 'active', NOW(), NOW())")->execute();
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `user_id`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp2', 'ActiveTrip', '9999900002', ?, 'fleet_chauffeur', 'Driver', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T2-{$rand}", $userId]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `driver_profiles` (`user_id`, `employee_id`, `license_number`, `license_expiry`, `duty_status`, `created_at`, `updated_at`) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'on_trip', NOW(), NOW())")
        ->execute([$userId, $empId, "LIC-T2-{$rand}"]);
    $driverId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'in_progress', NOW(), NOW())")
        ->execute(["BK-T2-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'trip_started', NOW(), NOW())")
        ->execute(["DSP-T2-{$rand}", $bookingId, $driverId]);
    $dispatchId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this employee. They have an active trip.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$empId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

// Scenario 3: Employee with a pending trip -> Blocked
it("Scenario 3: Employee with a pending trip -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('DriverUser3', 'usr3_{$rand}', 'usr3_{$rand}@test.com', 'hash', 'driver', 'active', NOW(), NOW())")->execute();
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `user_id`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp3', 'PendingTrip', '9999900003', ?, 'fleet_chauffeur', 'Driver', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T3-{$rand}", $userId]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `driver_profiles` (`user_id`, `employee_id`, `license_number`, `license_expiry`, `duty_status`, `created_at`, `updated_at`) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'off_duty', NOW(), NOW())")
        ->execute([$userId, $empId, "LIC-T3-{$rand}"]);
    $driverId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'pending', NOW(), NOW())")
        ->execute(["BK-T3-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'assigned', NOW(), NOW())")
        ->execute(["DSP-T3-{$rand}", $bookingId, $driverId]);
    $dispatchId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this employee. They have an active trip.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$empId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

// Scenario 4: Employee with completed trip and pending salary/payment -> Blocked
it("Scenario 4: Employee with completed trip and pending salary/payment -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('DriverUser4', 'usr4_{$rand}', 'usr4_{$rand}@test.com', 'hash', 'driver', 'active', NOW(), NOW())")->execute();
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `user_id`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp4', 'TripPayPending', '9999900004', ?, 'fleet_chauffeur', 'Driver', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T4-{$rand}", $userId]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `driver_profiles` (`user_id`, `employee_id`, `license_number`, `license_expiry`, `duty_status`, `created_at`, `updated_at`) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'off_duty', NOW(), NOW())")
        ->execute([$userId, $empId, "LIC-T4-{$rand}"]);
    $driverId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T4-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    // Trip completed, but driver_payment_status = 'due'
    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'completed', 300, 0, 'due', NOW(), NOW())")
        ->execute(["DSP-T4-{$rand}", $bookingId, $driverId]);
    $dispatchId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this employee. Trip payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$empId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

// Scenario 5: Employee with completed trip and settled payment -> Delete allowed
it("Scenario 5: Employee with completed trip and settled payment -> Delete allowed", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('DriverUser5', 'usr5_{$rand}', 'usr5_{$rand}@test.com', 'hash', 'driver', 'active', NOW(), NOW())")->execute();
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `user_id`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp5', 'SettledPay', '9999900005', ?, 'fleet_chauffeur', 'Driver', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T5-{$rand}", $userId]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `driver_profiles` (`user_id`, `employee_id`, `license_number`, `license_expiry`, `duty_status`, `created_at`, `updated_at`) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'off_duty', NOW(), NOW())")
        ->execute([$userId, $empId, "LIC-T5-{$rand}"]);
    $driverId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T5-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    // Trip completed and driver payment fully settled ('paid', paid_amount == trip_amount)
    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'completed', 300, 300, 'paid', NOW(), NOW())")
        ->execute(["DSP-T5-{$rand}", $bookingId, $driverId]);
    $dispatchId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if (!$res['allowed']) {
        throw new \Exception("Expected allowed=true, got blocked with message: {$res['message']}");
    }
    if ($res['message'] !== 'Employee deleted successfully.') {
        throw new \Exception("Unexpected message: {$res['message']}");
    }
    assertNoTechnicalLeak($res['message']);

    // Test controller execution when allowed
    $controller = new EmployeeController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $empId);
    $data = json_decode($response->getContent(), true);

    if (empty($data['success'])) {
        throw new \Exception("Expected controller deletion success=true, got: " . var_export($data, true));
    }
    if ($data['message'] !== 'Employee deleted successfully.') {
        throw new \Exception("Expected success message 'Employee deleted successfully.', got: {$data['message']}");
    }

    // Verify employee was actually deleted
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `employees` WHERE `id` = ?");
    $stmtCheck->execute([$empId]);
    if ((int)$stmtCheck->fetchColumn() !== 0) {
        throw new \Exception("Employee was not deleted from database");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

// Scenario 6: Employee with multiple trips where one payment is pending -> Blocked
it("Scenario 6: Employee with multiple trips where one payment is pending -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('DriverUser6', 'usr6_{$rand}', 'usr6_{$rand}@test.com', 'hash', 'driver', 'active', NOW(), NOW())")->execute();
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `phone`, `user_id`, `department`, `designation`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES (?, 'TestEmp6', 'MultiTrips', '9999900006', ?, 'fleet_chauffeur', 'Driver', CURDATE(), 'active', NOW(), NOW())")
        ->execute(["EMP-T6-{$rand}", $userId]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `driver_profiles` (`user_id`, `employee_id`, `license_number`, `license_expiry`, `duty_status`, `created_at`, `updated_at`) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'off_duty', NOW(), NOW())")
        ->execute([$userId, $empId, "LIC-T6-{$rand}"]);
    $driverId = (int)$pdo->lastInsertId();

    // Trip A: Completed & Paid
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T6A-{$rand}"]);
    $bookingIdA = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'completed', 300, 300, 'paid', NOW(), NOW())")
        ->execute(["DSP-T6A-{$rand}", $bookingIdA, $driverId]);
    $dispatchIdA = (int)$pdo->lastInsertId();

    // Trip B: Completed & Partially Paid / Due
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 600, 'completed', NOW(), NOW())")
        ->execute(["BK-T6B-{$rand}"]);
    $bookingIdB = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, 1, 'completed', 400, 100, 'partially_paid', NOW(), NOW())")
        ->execute(["DSP-T6B-{$rand}", $bookingIdB, $driverId]);
    $dispatchIdB = (int)$pdo->lastInsertId();

    $res = $service->canDeleteEmployee($empId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this employee. Trip payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` IN (?, ?)")->execute([$dispatchIdA, $dispatchIdB]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` IN (?, ?)")->execute([$bookingIdA, $bookingIdB]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$empId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

// =============================================================================
// CUSTOMER SCENARIOS (7 - 12)
// =============================================================================

// Scenario 7: Customer with no bookings -> Delete allowed
it("Scenario 7: Customer with no bookings -> Delete allowed", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust7 NoBookings', '9888800007', 'cust7_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T7-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if (!$res['allowed']) {
        throw new \Exception("Expected allowed=true, got blocked with message: {$res['message']}");
    }
    if ($res['message'] !== 'Customer deleted successfully.') {
        throw new \Exception("Unexpected message: {$res['message']}");
    }
    assertNoTechnicalLeak($res['message']);

    // Test controller execution when allowed
    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $custId);
    $data = json_decode($response->getContent(), true);

    if (empty($data['success'])) {
        throw new \Exception("Expected controller deletion success=true, got: " . var_export($data, true));
    }
    if ($data['message'] !== 'Customer deleted successfully.') {
        throw new \Exception("Expected success message 'Customer deleted successfully.', got: {$data['message']}");
    }

    // Verify customer was deleted
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `customers` WHERE `id` = ?");
    $stmtCheck->execute([$custId]);
    if ((int)$stmtCheck->fetchColumn() !== 0) {
        throw new \Exception("Customer was not deleted from database");
    }
});

// Scenario 8: Customer with active booking -> Blocked
it("Scenario 8: Customer with active booking -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust8 Active', '9888800008', 'cust8_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T8-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'in_progress', NOW(), NOW())")
        ->execute(["BK-T8-{$rand}", $custId]);
    $bookingId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this customer. They have an active booking.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Test controller blocks it with 400 and same message
    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $custId);
    $data = json_decode($response->getContent(), true);

    if (!empty($data['success'])) {
        throw new \Exception("Expected controller deletion to fail, but succeeded");
    }
    if ($data['message'] !== $expectedMsg) {
        throw new \Exception("Expected controller message '{$expectedMsg}', got: {$data['message']}");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

// Scenario 9: Customer with pending booking -> Blocked
it("Scenario 9: Customer with pending booking -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust9 Pending', '9888800009', 'cust9_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T9-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'pending', NOW(), NOW())")
        ->execute(["BK-T9-{$rand}", $custId]);
    $bookingId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this customer. They have an active booking.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

// Scenario 10: Customer with completed booking and pending payment -> Blocked
it("Scenario 10: Customer with completed booking and pending payment -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust10 PayPending', '9888800010', 'cust10_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T10-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T10-{$rand}", $custId]);
    $bookingId = (int)$pdo->lastInsertId();

    // Invoice unpaid
    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 500, 500, 0, 'unpaid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T10-{$rand}", $bookingId, $custId]);
    $invId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this customer. Payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` = ?")->execute([$invId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

// Scenario 11: Customer with completed booking and settled payment -> Delete allowed
it("Scenario 11: Customer with completed booking and settled payment -> Delete allowed", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust11 Settled', '9888800011', 'cust11_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T11-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T11-{$rand}", $custId]);
    $bookingId = (int)$pdo->lastInsertId();

    // Invoice fully paid
    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 500, 500, 500, 'paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T11-{$rand}", $bookingId, $custId]);
    $invId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if (!$res['allowed']) {
        throw new \Exception("Expected allowed=true, got blocked with message: {$res['message']}");
    }
    if ($res['message'] !== 'Customer deleted successfully.') {
        throw new \Exception("Unexpected message: {$res['message']}");
    }
    assertNoTechnicalLeak($res['message']);

    // Controller execution check
    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $custId);
    $data = json_decode($response->getContent(), true);

    if (empty($data['success'])) {
        throw new \Exception("Expected customer deletion success=true, got: " . var_export($data, true));
    }
    if ($data['message'] !== 'Customer deleted successfully.') {
        throw new \Exception("Expected 'Customer deleted successfully.', got: {$data['message']}");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` = ?")->execute([$invId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

// Scenario 12: Customer with multiple bookings where one payment is pending -> Blocked
it("Scenario 12: Customer with multiple bookings where one payment is pending -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Cust12 Multi', '9888800012', 'cust12_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-T12-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    // Booking A: Completed & Paid
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T12A-{$rand}", $custId]);
    $bookingIdA = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 500, 500, 500, 'paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T12A-{$rand}", $bookingIdA, $custId]);
    $invIdA = (int)$pdo->lastInsertId();

    // Booking B: Completed & Part Paid / Unpaid
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 600, 'completed', NOW(), NOW())")
        ->execute(["BK-T12B-{$rand}", $custId]);
    $bookingIdB = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 600, 600, 200, 'partially_paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T12B-{$rand}", $bookingIdB, $custId]);
    $invIdB = (int)$pdo->lastInsertId();

    $res = $service->canDeleteCustomer($custId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this customer. Payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` IN (?, ?)")->execute([$invIdA, $invIdB]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` IN (?, ?)")->execute([$bookingIdA, $bookingIdB]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

// =============================================================================
// BOOKING SCENARIOS (13 - 17)
// =============================================================================

// Scenario 13: Booking that is pending -> Blocked
it("Scenario 13: Booking that is pending -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'pending', NOW(), NOW())")
        ->execute(["BK-T13-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteBooking($bookingId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this booking. The trip is still active.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Controller check
    $controller = new BookingController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $bookingId);
    $data = json_decode($response->getContent(), true);

    if (!empty($data['success'])) {
        throw new \Exception("Expected booking deletion to fail, but succeeded");
    }
    if ($data['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: {$data['message']}");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
});

// Scenario 14: Booking that is active/in progress -> Blocked
it("Scenario 14: Booking that is active/in progress -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'in_progress', NOW(), NOW())")
        ->execute(["BK-T14-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteBooking($bookingId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this booking. The trip is still active.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
});

// Scenario 15: Booking completed with pending customer payment -> Blocked
it("Scenario 15: Booking completed with pending customer payment -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T15-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, 500, 500, 100, 'partially_paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T15-{$rand}", $bookingId]);
    $invId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteBooking($bookingId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this booking. Payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` = ?")->execute([$invId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
});

// Scenario 16: Booking completed with pending employee payment -> Blocked
it("Scenario 16: Booking completed with pending employee payment -> Blocked", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T16-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    // Customer invoice is fully paid
    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, 500, 500, 500, 'paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T16-{$rand}", $bookingId]);
    $invId = (int)$pdo->lastInsertId();

    // Dispatch completed, but driver payment is pending (due)
    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, 1, 1, 'completed', 250, 0, 'due', NOW(), NOW())")
        ->execute(["DSP-T16-{$rand}", $bookingId]);
    $dispatchId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteBooking($bookingId);
    if ($res['allowed']) {
        throw new \Exception("Expected allowed=false, but deletion was allowed");
    }
    $expectedMsg = "Cannot delete this booking. Trip payment is still pending.";
    if ($res['message'] !== $expectedMsg) {
        throw new \Exception("Expected message '{$expectedMsg}', got: '{$res['message']}'");
    }
    assertNoTechnicalLeak($res['message']);

    // Clean up
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` = ?")->execute([$invId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
});

// Scenario 17: Booking completed with all payments settled -> Delete allowed
it("Scenario 17: Booking completed with all payments settled -> Delete allowed", function() use ($pdo, $service, $rand) {
    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-T17-{$rand}"]);
    $bookingId = (int)$pdo->lastInsertId();

    // Customer invoice fully paid
    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, 500, 500, 500, 'paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-T17-{$rand}", $bookingId]);
    $invId = (int)$pdo->lastInsertId();

    // Dispatch completed and driver trip amount fully paid
    $pdo->prepare("INSERT INTO `dispatches` (`dispatch_code`, `booking_id`, `vehicle_id`, `driver_id`, `assigned_by`, `status`, `driver_trip_amount`, `driver_paid_amount`, `driver_payment_status`, `created_at`, `updated_at`) VALUES (?, ?, 1, 1, 1, 'completed', 250, 250, 'paid', NOW(), NOW())")
        ->execute(["DSP-T17-{$rand}", $bookingId]);
    $dispatchId = (int)$pdo->lastInsertId();

    // Add settled driver_trip_payment to test FK cleanup
    $pdo->prepare("INSERT INTO `driver_trip_payments` (`payment_reference`, `receipt_number`, `dispatch_id`, `driver_id`, `booking_id`, `amount`, `payment_date`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, 1, ?, 250, CURDATE(), 'completed', NOW(), NOW())")
        ->execute(["PAY-T17-{$rand}", "REC-T17-{$rand}", $dispatchId, $bookingId]);
    $dtpId = (int)$pdo->lastInsertId();

    $res = $service->canDeleteBooking($bookingId);
    if (!$res['allowed']) {
        throw new \Exception("Expected allowed=true, got blocked with message: {$res['message']}");
    }
    if ($res['message'] !== 'Booking deleted successfully.') {
        throw new \Exception("Unexpected message: {$res['message']}");
    }
    assertNoTechnicalLeak($res['message']);

    // Test controller execution when allowed
    $controller = new BookingController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
    $response = $controller->delete($req, $bookingId);
    $data = json_decode($response->getContent(), true);

    if (empty($data['success'])) {
        throw new \Exception("Expected controller deletion success=true, got: " . var_export($data, true));
    }
    if ($data['message'] !== 'Booking deleted successfully.') {
        throw new \Exception("Expected success message 'Booking deleted successfully.', got: {$data['message']}");
    }

    // Verify booking was deleted
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `bookings` WHERE `id` = ?");
    $stmtCheck->execute([$bookingId]);
    if ((int)$stmtCheck->fetchColumn() !== 0) {
        throw new \Exception("Booking was not deleted from database");
    }

    // Clean up invoice if dissociated
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` = ?")->execute([$invId]);
});

// =============================================================================
// NON-AJAX FORM SUBMISSION & FLASH MESSAGES
// =============================================================================

it("Non-AJAX Employee Delete: Blocked sets flash and redirects cleanly", function() use ($pdo, $rand) {
    // Booking 9 is in_progress and linked to dispatch 10 which has driver 1 (Employee 1)
    $controller = new EmployeeController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST']); // Non-AJAX
    $response = $controller->delete($req, 1);

    if ($response->getStatusCode() !== 302) {
        throw new \Exception("Expected redirect status 302, got: {$response->getStatusCode()}");
    }
    $flash = Session::getFlash('danger');
    $expected = "Cannot delete this employee. They have an active trip.";
    if ($flash !== $expected) {
        throw new \Exception("Expected flash message '{$expected}', got: '{$flash}'");
    }
});

it("Non-AJAX Customer Delete: Blocked sets flash and redirects cleanly", function() use ($pdo, $rand) {
    // Customer 1 has booking 2 which is completed but dispatch 2 payment is due or customer payment check
    // Let's create a temporary customer with an active booking to be 100% deterministic
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'FlashCust', '9777700001', 'flash_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-FL-{$rand}"]);
    $custId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'in_progress', NOW(), NOW())")
        ->execute(["BK-FL-{$rand}", $custId]);
    $bookingId = (int)$pdo->lastInsertId();

    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST']); // Non-AJAX
    $response = $controller->delete($req, $custId);

    if ($response->getStatusCode() !== 302) {
        throw new \Exception("Expected redirect status 302, got: {$response->getStatusCode()}");
    }
    $flash = Session::getFlash('error');
    $expected = "Cannot delete this customer. They have an active booking.";
    if ($flash !== $expected) {
        throw new \Exception("Expected flash message '{$expected}', got: '{$flash}'");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` = ?")->execute([$custId]);
});

it("Non-AJAX Booking Delete: Blocked sets flash and redirects cleanly", function() use ($pdo, $rand) {
    // Booking 8 has status 'assigned'
    $controller = new BookingController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'POST']); // Non-AJAX
    $response = $controller->delete($req, 8);

    if ($response->getStatusCode() !== 302) {
        throw new \Exception("Expected redirect status 302, got: {$response->getStatusCode()}");
    }
    $flash = Session::getFlash('danger');
    $expected = "Cannot delete this booking. The trip is still active.";
    if ($flash !== $expected) {
        throw new \Exception("Expected flash message '{$expected}', got: '{$flash}'");
    }
});

echo "========================================================" . PHP_EOL;
echo "  SUMMARY: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "========================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
