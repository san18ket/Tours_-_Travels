<?php

/**
 * Test Suite: Global User-Friendly Message System & Dynamic Admin Dashboard
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';

use App\Core\ErrorMessageTranslator;
use App\Controllers\DashboardController;
use App\Core\Request;

$passed = 0;
$failed = 0;

function it(string $description, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($detail ? " - {$detail}" : "") . "\n";
        $failed++;
    }
}

echo "====================================================================\n";
echo "1. Testing Global User-Friendly Message System (ErrorMessageTranslator)\n";
echo "====================================================================\n";

// Test 1: Employee deletion with foreign key constraint (e.g. linked to trip/dispatch)
$fkException1 = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`tours_travels`.`trips`, CONSTRAINT `fk_trips_driver` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`))", 23000);
$msg1 = ErrorMessageTranslator::translate($fkException1, 'employee', 'delete');
it("Employee FK delete translates to user-friendly message", 
   $msg1 === "Cannot delete this employee because they are linked to a trip.",
   "Got: " . $msg1
);
it("Employee FK delete message contains no SQLSTATE or DB table name",
   !str_contains($msg1, 'SQLSTATE') && !str_contains($msg1, 'constraint') && !str_contains($msg1, 'trips') && !str_contains($msg1, 'foreign key')
);

// Test 2: Customer deletion with linked bookings
$fkException2 = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`tours_travels`.`bookings`, CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_profile_id`) REFERENCES `customer_profiles` (`id`))", 23000);
$msg2 = ErrorMessageTranslator::translate($fkException2, 'customer', 'delete');
it("Customer FK delete translates to user-friendly message", 
   $msg2 === "Cannot delete this customer because they have linked bookings.",
   "Got: " . $msg2
);

// Test 3: Vehicle deletion with linked trips
$fkException3 = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`tours_travels`.`dispatches`, CONSTRAINT `fk_dispatches_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`))", 23000);
$msg3 = ErrorMessageTranslator::translate($fkException3, 'vehicle', 'delete');
it("Vehicle FK delete translates to user-friendly message", 
   $msg3 === "Cannot delete this vehicle because it is linked to a trip.",
   "Got: " . $msg3
);

// Test 4: Booking deletion with linked records
$fkException4 = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`tours_travels`.`invoices`, CONSTRAINT `fk_invoices_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`))", 23000);
$msg4 = ErrorMessageTranslator::translate($fkException4, 'booking', 'delete');
it("Booking FK delete translates to user-friendly message", 
   $msg4 === "Cannot delete this booking because it has linked dispatches or invoices.",
   "Got: " . $msg4
);

// Test 5: Generic FK error with unknown context
$fkException5 = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row", 23000);
$msg5 = ErrorMessageTranslator::translate($fkException5);
it("Generic FK error translates to safe human message", 
   $msg5 === "Cannot delete this record because it is linked to other data.",
   "Got: " . $msg5
);

// Test 6: Duplicate email error (1062)
$dupEmail = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'admin@example.com' for key 'employees_email_unique'", 23000);
$msg6 = ErrorMessageTranslator::translate($dupEmail, 'employee', 'create');
it("Duplicate email translates to short friendly message", 
   $msg6 === "This email is already in use.",
   "Got: " . $msg6
);

// Test 7: Duplicate phone error (1062)
$dupPhone = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '9876543210' for key 'users_phone_unique'", 23000);
$msg7 = ErrorMessageTranslator::translate($dupPhone);
it("Duplicate phone translates to short friendly message", 
   $msg7 === "This phone number is already in use.",
   "Got: " . $msg7
);

// Test 8: Missing required column (1048 / 1364)
$nullViol = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'first_name' cannot be null", 23000);
$msg8 = ErrorMessageTranslator::translate($nullViol);
it("Null column violation translates to 'Please enter the required information.'", 
   $msg8 === "Please enter the required information.",
   "Got: " . $msg8
);

// Test 9: Permission error
$permEx = new \Exception("User does not have permission", 403);
$msg9 = ErrorMessageTranslator::translate($permEx);
it("Permission exception translates to 'You don't have permission to do this.'", 
   $msg9 === "You don't have permission to do this.",
   "Got: " . $msg9
);

// Test 10: Helper function human_error()
$msg10 = human_error($fkException1, 'employee', 'delete');
it("Global human_error() helper works identical to ErrorMessageTranslator", 
   $msg10 === "Cannot delete this employee because they are linked to a trip.",
   "Got: " . $msg10
);

echo "\n====================================================================\n";
echo "2. Testing Dynamic Admin Dashboard Controller & AJAX Endpoints\n";
echo "====================================================================\n";

// Set up session auth for controller
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin User', 'username' => 'admin', 'role' => 'admin'];

$dashboard = new DashboardController();
$pdo = \App\Core\Database::getConnection();

// Test 11: Call index()
$req = new Request();
$response = $dashboard->index($req);
it("DashboardController::index returns a Response instance", $response instanceof \App\Core\Response);

// Access controller data via reflection
$refClass = new \ReflectionClass(DashboardController::class);
$mTopDest = $refClass->getMethod('getTopDestinations');
$mTopDest->setAccessible(true);
$topDestinations = $mTopDest->invoke($dashboard, $pdo);
it("Top destinations returns an array", is_array($topDestinations));

$mUpcoming = $refClass->getMethod('getUpcomingTrips');
$mUpcoming->setAccessible(true);
$upcomingTrips = $mUpcoming->invoke($dashboard, $pdo);
it("Upcoming trips returns an array", is_array($upcomingTrips));

$mRecent = $refClass->getMethod('getRecentBookings');
$mRecent->setAccessible(true);
$recentBookings = $mRecent->invoke($dashboard, $pdo);
it("Recent bookings returns an array", is_array($recentBookings));

$mRevPeriod = $refClass->getMethod('getRevenuePeriodData');
$mRevPeriod->setAccessible(true);
$revThisMonth = $mRevPeriod->invoke($dashboard, $pdo, 'this_month');
it("Revenue period data has required structure", 
   isset($revThisMonth['total']) && isset($revThisMonth['formatted']) && isset($revThisMonth['curve_points']) && isset($revThisMonth['online_pct'])
);

$mBookingOverview = $refClass->getMethod('getBookingOverviewData');
$mBookingOverview->setAccessible(true);
$overview8m = $mBookingOverview->invoke($dashboard, $pdo, '8m');
it("Booking overview data returns labels and data arrays", 
   isset($overview8m['labels']) && isset($overview8m['data']) && count($overview8m['labels']) === count($overview8m['data'])
);

// Test 12: Revenue summary AJAX endpoint
$_GET['period'] = 'this_month';
$revAjax = $dashboard->revenueSummary(new Request());
it("revenueSummary AJAX returns a 200 JSON Response", $revAjax->getStatusCode() === 200);

// Test 13: Booking overview AJAX endpoint
$_GET['period'] = '6m';
$bookAjax = $dashboard->bookingOverview(new Request());
it("bookingOverview AJAX returns a 200 JSON Response", $bookAjax->getStatusCode() === 200);

echo "\n====================================================================\n";
echo "3. Testing Dashboard View Template (No hardcoded demo values)\n";
echo "====================================================================\n";

$viewFile = file_get_contents(__DIR__ . '/../app/Views/office/dashboard.php');

it("Dashboard view does NOT contain hardcoded fallback '124'", 
   !str_contains($viewFile, ": '124'") && !str_contains($viewFile, "? '124'")
);
it("Dashboard view does NOT contain hardcoded fallback '980'", 
   !str_contains($viewFile, ": '980'") && !str_contains($viewFile, "? '980'")
);
it("Dashboard view does NOT contain hardcoded fallback '28'", 
   !str_contains($viewFile, ": '28'") && !str_contains($viewFile, "? '28'")
);
it("Dashboard view does NOT contain hardcoded fallback '₹ 8,45,230'", 
   !str_contains($viewFile, ": '₹ 8,45,230'") && !str_contains($viewFile, "? '₹ 8,45,230'")
);
it("Dashboard view does NOT contain hardcoded demo bookings ('Goa Beachfront Tour')", 
   !str_contains($viewFile, 'Goa Beachfront Tour')
);
it("Dashboard view contains empty state for Top Destinations ('No destination data yet.')", 
   str_contains($viewFile, 'No destination data yet.')
);
it("Dashboard view contains empty state for Upcoming Trips ('No upcoming trips.')", 
   str_contains($viewFile, 'No upcoming trips.')
);
it("Dashboard view contains empty state for Recent Bookings ('No recent bookings.')", 
   str_contains($viewFile, 'No recent bookings.')
);
it("Dashboard view has revenue period select wired with updateRevenueSummary", 
   str_contains($viewFile, 'id="revenuePeriodSelect"') && str_contains($viewFile, 'updateRevenueSummary(this.value)')
);

echo "\n====================================================================\n";
echo "4. Testing View Rendering with Empty States\n";
echo "====================================================================\n";

// Simulate rendering dashboard with empty dataset
$auth_user = ['name' => 'Test Admin', 'username' => 'admin'];
$auth_role = 'admin';
$totalBookings = 0;
$totalCustomers = 0;
$totalVehicles = 0;
$totalRevenue = 0.0;
$availableFleet = 0;
$onTripFleet = 0;
$maintenanceFleet = 0;
$fleetReadinessRate = 100;
$driversOnDuty = 0;
$driversAvailable = 0;
$kpiTrends = [
    'bookings' => ['growth' => 0, 'dir' => 'up'],
    'customers' => ['growth' => 0, 'dir' => 'up'],
    'vehicles' => ['growth' => 0, 'dir' => 'up'],
    'revenue' => ['growth' => 0, 'dir' => 'up'],
];
$topDestinations = [];
$upcomingTrips = [];
$recentBookings = [];
$revenueData = [
    'total' => 0,
    'formatted' => '₹ 0.00',
    'growth' => 0,
    'dir' => 'up',
    'online_pct' => 0,
    'pending_pct' => 0,
    'curve_labels' => ['1', '7', '14', '21', '28'],
    'curve_points' => [0, 0, 0, 0, 0],
];
$onlinePayPercent = 0;
$pendingPayPercent = 0;
$bookingOverview = ['labels' => ['Jan'], 'data' => [0]];
$vehicleBreakdown = [];
$vehicleDonutLabels = [];
$vehicleDonutCounts = [];
$vehicleDonutColors = [];

ob_start();
try {
    include __DIR__ . '/../app/Views/office/dashboard.php';
    $renderedHtml = ob_get_clean();
    $renderSuccess = true;
} catch (\Throwable $e) {
    $renderedHtml = ob_get_clean();
    $renderSuccess = false;
    echo "  Render error: " . $e->getMessage() . "\n";
}

it("Dashboard view renders without error when all data is empty", $renderSuccess);
it("Rendered view shows 'No destination data yet.' when topDestinations is empty", 
   str_contains($renderedHtml, 'No destination data yet.')
);
it("Rendered view shows 'No upcoming trips.' when upcomingTrips is empty", 
   str_contains($renderedHtml, 'No upcoming trips.')
);
it("Rendered view shows 'No recent bookings.' when recentBookings is empty", 
   str_contains($renderedHtml, 'No recent bookings.')
);

echo "\n====================================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
