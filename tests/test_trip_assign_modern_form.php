<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Employee;

$testName = "Trip Assign Modern Form Suite (/office/trips/assign)";
echo "======================================================\n";
echo " RUNNING SUITE: {$testName}\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

// 1. Verify View File & Structural Elements
$viewPath = dirname(__DIR__) . '/app/Views/office/trips/assign.php';
assertCondition(file_exists($viewPath), "trips/assign.php view file exists");
$content = file_get_contents($viewPath);

echo "\n--- 1. Testing Removal of Back Buttons & Step Tabs ---\n";
// Back button must be absent
assertCondition(!preg_match('/class="[^"]*modern-back-btn/i', $content), "Top back button is absent");
// Stepper tabs must be absent (as explicitly requested: 'just dont add steps')
assertCondition(strpos($content, 'class="modern-stepper-track"') === false, "Stepper track is absent (No steps added)");
assertCondition(strpos($content, 'class="modern-step-item"') === false, "Stepper step items are absent");
assertCondition(strpos($content, 'class="modern-step-panel"') === false, "Step wizard panels are absent");

echo "\n--- 2. Testing Modern Centered Header & Card Layout ---\n";
assertCondition(strpos($content, 'class="modern-form-wrapper"') !== false, ".modern-form-wrapper is present");
assertCondition(strpos($content, 'class="modern-header-centered-card"') !== false, "Centered header card is present");
assertCondition(strpos($content, 'class="modern-form-card"') !== false, "Master .modern-form-card is present");
assertCondition(substr_count($content, 'class="modern-form-card"') === 1, "Exactly one master .modern-form-card wraps form elements");

echo "\n--- 3. Testing Full-Width Coverage CSS Rules ---\n";
assertCondition(preg_match('/\.modern-form-wrapper\s*\{[^}]*width:\s*100%/s', $content) === 1, ".modern-form-wrapper width: 100% defined");
assertCondition(preg_match('/\.modern-form-wrapper\s*\{[^}]*max-width:\s*100%/s', $content) === 1, ".modern-form-wrapper max-width: 100% defined");
assertCondition(preg_match('/\.modern-header-centered-card\s*\{[^}]*width:\s*100%/s', $content) === 1, ".modern-header-centered-card width: 100% defined");
assertCondition(preg_match('/\.modern-form-card\s*\{[^}]*width:\s*100%/s', $content) === 1, ".modern-form-card width: 100% defined");
assertCondition(strpos($content, '@media (max-width: 768px)') !== false, "Tablet responsive breakpoint defined");
assertCondition(strpos($content, '@media (max-width: 480px)') !== false, "Mobile compact breakpoint defined");

echo "\n--- 4. Testing Form Fields & Manifest Summary Box ---\n";
assertCondition(strpos($content, 'name="booking_id"') !== false, "booking_id dropdown is present");
assertCondition(strpos($content, 'name="vehicle_id"') !== false, "vehicle_id dropdown is present");
assertCondition(strpos($content, 'name="driver_id"') !== false, "driver_id dropdown is present");
assertCondition(strpos($content, 'id="bookingSummaryBox"') !== false, "bookingSummaryBox route details card is present");
assertCondition(strpos($content, 'id="btnDeployTrip"') !== false, "Deploy trip submit button is present");
assertCondition(strpos($content, 'csrf_field()') !== false, "CSRF security field is included");

echo "\n--- 5. Testing HTTP Route Execution (GET /office/trips/assign) ---\n";
Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
$userModel = new User();
Session::set('user', $userModel->find(1));

$router = require dirname(__DIR__) . '/app/routes.php';
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/assign']);
$res = $router->dispatch($req);

assertCondition($res->getStatusCode() === 200, "GET /office/trips/assign returns HTTP 200 OK");
$html = $res->getBody();
assertCondition(strpos($html, 'Assign Trip Duty to Driver') !== false || strpos($html, 'Dispatch & Assign Trip') !== false, "Response body contains page title");
assertCondition(strpos($html, 'modern-header-centered-card') !== false, "Response body renders modern centered header card");
assertCondition(strpos($html, 'modern-form-card') !== false, "Response body renders modern form card");
assertCondition(strpos($html, 'bookingSummaryBox') !== false, "Response body renders bookingSummaryBox");

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
