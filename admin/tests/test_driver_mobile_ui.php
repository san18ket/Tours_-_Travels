<?php
declare(strict_types=1);

/**
 * Test Suite: Driver Earnings & History Mobile UI, Search & Filters Matching /driver/trips
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Dispatch;
use App\Models\DriverProfile;
use App\Controllers\DriverPortalController;
use App\Core\Request;

$pdo = Database::getConnection();
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

echo "=== DRIVER EARNINGS & HISTORY MOBILE UI TEST SUITE ===\n\n";

// -----------------------------------------------------------------------------
// 1. Verify Driver Earnings View Elements
// -----------------------------------------------------------------------------
echo "1. Verifying Mobile UI in app/Views/driver/earnings.php...\n";
$earningsContent = file_get_contents(__DIR__ . '/../app/Views/driver/earnings.php');

assertCondition(str_contains($earningsContent, 'class="mv-bookings-header"'), "Earnings view contains .mv-bookings-header", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-top-bar"'), "Earnings view contains .mv-top-bar", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-back-btn"'), "Earnings view contains .mv-back-btn back button", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-search-bar-wrap"'), "Earnings view contains .mv-search-bar-wrap", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="mvSearchForm"'), "Earnings view contains #mvSearchForm", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="mvSearchInput"'), "Earnings view contains #mvSearchInput", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="mvSearchClearBtn"'), "Earnings view contains #mvSearchClearBtn", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="mvFilterToggleBtn"'), "Earnings view contains #mvFilterToggleBtn", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="mvFilterPanel"'), "Earnings view contains collapsible #mvFilterPanel", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="earningsMvStatusScroll"'), "Earnings view contains horizontal status scroll #earningsMvStatusScroll", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-pill'), "Earnings view contains .mv-pill status buttons", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="earningsCards"'), "Earnings view contains mobile cards grid #earningsCards", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-booking-card-wrapper"'), "Earnings view contains .mv-booking-card-wrapper cards", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-thumb-wrap is-avatar"'), "Earnings view cards have .mv-thumb-wrap is-avatar", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-details"'), "Earnings view cards have .mv-details container", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-earnings-strip"'), "Earnings view cards have dedicated full-width .mv-earnings-strip", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'is-due-box'), "Earnings view cards have prominent .is-due-box badge", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'is-due-amount'), "Earnings view cards have highlighted .is-due-amount", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'Due Amount'), "Earnings view cards display explicit 'Due Amount' label", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'class="mv-card-actions"'), "Earnings view cards have .mv-card-actions 3-dots menu", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'id="earningsPaginationWrapper"'), "Earnings view contains #earningsPaginationWrapper", $failures, $successCount);
assertCondition(str_contains($earningsContent, 'initDriverEarningsAjaxFilter'), "Earnings view contains AJAX live filter engine initDriverEarningsAjaxFilter", $failures, $successCount);
assertCondition(str_contains($earningsContent, '@media (max-width: 768px)'), "Earnings view defines responsive media query for mobile screens", $failures, $successCount);

// -----------------------------------------------------------------------------
// 2. Verify Driver History View Elements
// -----------------------------------------------------------------------------
echo "\n2. Verifying Mobile UI in app/Views/driver/history.php...\n";
$historyContent = file_get_contents(__DIR__ . '/../app/Views/driver/history.php');

assertCondition(str_contains($historyContent, 'class="mv-bookings-header"'), "History view contains .mv-bookings-header", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-top-bar"'), "History view contains .mv-top-bar", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-back-btn"'), "History view contains .mv-back-btn back button", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-search-bar-wrap"'), "History view contains .mv-search-bar-wrap", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="mvSearchForm"'), "History view contains #mvSearchForm", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="mvSearchInput"'), "History view contains #mvSearchInput", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="mvSearchClearBtn"'), "History view contains #mvSearchClearBtn", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="mvFilterToggleBtn"'), "History view contains #mvFilterToggleBtn", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="mvFilterPanel"'), "History view contains collapsible #mvFilterPanel", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="historyMvStatusScroll"'), "History view contains horizontal status scroll #historyMvStatusScroll", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-pill'), "History view contains .mv-pill status buttons", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="historyCards"'), "History view contains mobile cards grid #historyCards", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-booking-card-wrapper"'), "History view contains .mv-booking-card-wrapper cards", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-thumb-wrap is-avatar"'), "History view cards have .mv-thumb-wrap is-avatar", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-details"'), "History view cards have .mv-details container", $failures, $successCount);
assertCondition(str_contains($historyContent, 'class="mv-card-actions"'), "History view cards have .mv-card-actions 3-dots menu", $failures, $successCount);
assertCondition(str_contains($historyContent, 'id="historyPaginationWrapper"'), "History view contains #historyPaginationWrapper", $failures, $successCount);
assertCondition(str_contains($historyContent, 'initDriverHistoryAjaxFilter'), "History view contains AJAX live filter engine initDriverHistoryAjaxFilter", $failures, $successCount);
assertCondition(str_contains($historyContent, '@media (max-width: 768px)'), "History view defines responsive media query for mobile screens", $failures, $successCount);

// -----------------------------------------------------------------------------
// 3. Verify Driver Payments View Elements
// -----------------------------------------------------------------------------
echo "\n3. Verifying Mobile UI in app/Views/driver/payments.php...\n";
$paymentsContent = file_get_contents(__DIR__ . '/../app/Views/driver/payments.php');

assertCondition(str_contains($paymentsContent, 'class="mv-bookings-header"'), "Payments view contains .mv-bookings-header", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-top-bar"'), "Payments view contains .mv-top-bar", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-back-btn"'), "Payments view contains .mv-back-btn back button", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-search-bar-wrap"'), "Payments view contains .mv-search-bar-wrap", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="mvSearchForm"'), "Payments view contains #mvSearchForm", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="mvSearchInput"'), "Payments view contains #mvSearchInput", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="mvSearchClearBtn"'), "Payments view contains #mvSearchClearBtn", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="mvFilterToggleBtn"'), "Payments view contains #mvFilterToggleBtn", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="mvFilterPanel"'), "Payments view contains collapsible #mvFilterPanel", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="paymentsMvStatusScroll"'), "Payments view contains horizontal status scroll #paymentsMvStatusScroll", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-pill'), "Payments view contains .mv-pill status buttons", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="paymentsCards"'), "Payments view contains mobile cards grid #paymentsCards", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-booking-card-wrapper"'), "Payments view contains .mv-booking-card-wrapper cards", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-thumb-wrap is-avatar"'), "Payments view cards have .mv-thumb-wrap is-avatar", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-details"'), "Payments view cards have .mv-details container", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-earnings-strip"'), "Payments view cards have dedicated full-width .mv-earnings-strip", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'Paid Amount'), "Payments view cards display 'Paid Amount' terminology", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'class="mv-card-actions"'), "Payments view cards have .mv-card-actions 3-dots menu", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'id="paymentsPaginationWrapper"'), "Payments view contains #paymentsPaginationWrapper", $failures, $successCount);
assertCondition(str_contains($paymentsContent, 'initDriverPaymentsAjaxFilter'), "Payments view contains AJAX live filter engine initDriverPaymentsAjaxFilter", $failures, $successCount);
assertCondition(str_contains($paymentsContent, '@media (max-width: 768px)'), "Payments view defines responsive media query for mobile screens", $failures, $successCount);

// -----------------------------------------------------------------------------
// 4. Controller Execution & Render Testing
// -----------------------------------------------------------------------------
echo "\n4. Testing DriverPortalController::earnings, history, and paymentHistory Render...\n";

// Get a driver user
$stmtDriver = $pdo->query("SELECT dp.*, u.id as user_id, u.name as user_name, u.role FROM driver_profiles dp JOIN users u ON dp.user_id = u.id LIMIT 1");
$driverFixture = $stmtDriver->fetch(\PDO::FETCH_ASSOC);

if ($driverFixture) {
    $_SESSION['user_id'] = (int)$driverFixture['user_id'];
    $_SESSION['user_role'] = 'driver';
    $_SESSION['user_name'] = $driverFixture['user_name'];

    $controller = new DriverPortalController();

    // Test Earnings GET
    $reqEarnings = new Request([], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml'
    ]);
    $resEarnings = $controller->earnings($reqEarnings);
    assertCondition($resEarnings->getStatusCode() === 200, "Driver earnings endpoint returns HTTP 200", $failures, $successCount);
    assertCondition(str_contains($resEarnings->getContent(), 'Trip Earnings'), "Driver earnings rendered page contains 'Trip Earnings'", $failures, $successCount);
    assertCondition(str_contains($resEarnings->getContent(), 'earningsMvStatusScroll'), "Rendered earnings contains #earningsMvStatusScroll", $failures, $successCount);
    assertCondition(str_contains($resEarnings->getContent(), 'mvSearchInput'), "Rendered earnings contains #mvSearchInput", $failures, $successCount);
    assertCondition(str_contains($resEarnings->getContent(), 'mvFilterToggleBtn'), "Rendered earnings contains #mvFilterToggleBtn", $failures, $successCount);

    // Test Earnings Filter query
    $reqEarningsFilter = new Request(['status' => 'due', 'search' => 'test'], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ]);
    $resEarningsFilter = $controller->earnings($reqEarningsFilter);
    assertCondition($resEarningsFilter->getStatusCode() === 200, "Driver earnings with filters returns HTTP 200", $failures, $successCount);

    // Test History GET
    $reqHistory = new Request([], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml'
    ]);
    $resHistory = $controller->history($reqHistory);
    assertCondition($resHistory->getStatusCode() === 200, "Driver history endpoint returns HTTP 200", $failures, $successCount);
    assertCondition(str_contains($resHistory->getContent(), 'Trip History'), "Driver history rendered page contains 'Trip History'", $failures, $successCount);
    assertCondition(str_contains($resHistory->getContent(), 'historyMvStatusScroll'), "Rendered history contains #historyMvStatusScroll", $failures, $successCount);
    assertCondition(str_contains($resHistory->getContent(), 'mvSearchInput'), "Rendered history contains #mvSearchInput", $failures, $successCount);
    assertCondition(str_contains($resHistory->getContent(), 'mvFilterToggleBtn'), "Rendered history contains #mvFilterToggleBtn", $failures, $successCount);

    // Test History Filter query
    $reqHistoryFilter = new Request(['status' => 'completed', 'search' => 'test'], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ]);
    $resHistoryFilter = $controller->history($reqHistoryFilter);
    assertCondition($resHistoryFilter->getStatusCode() === 200, "Driver history with filters returns HTTP 200", $failures, $successCount);

    // Test Payment History GET
    $reqPayments = new Request([], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml'
    ]);
    $resPayments = $controller->paymentHistory($reqPayments);
    assertCondition($resPayments->getStatusCode() === 200, "Driver payment history endpoint returns HTTP 200", $failures, $successCount);
    assertCondition(str_contains($resPayments->getContent(), 'Payment History'), "Driver payments rendered page contains 'Payment History'", $failures, $successCount);
    assertCondition(str_contains($resPayments->getContent(), 'paymentsMvStatusScroll'), "Rendered payments contains #paymentsMvStatusScroll", $failures, $successCount);
    assertCondition(str_contains($resPayments->getContent(), 'mvSearchInput'), "Rendered payments contains #mvSearchInput", $failures, $successCount);
    assertCondition(str_contains($resPayments->getContent(), 'mvFilterToggleBtn'), "Rendered payments contains #mvFilterToggleBtn", $failures, $successCount);

    // Test Payment History Filter query
    $reqPaymentsFilter = new Request(['search' => 'PAY', 'per_page' => '10'], [], [], [], [
        'REQUEST_METHOD' => 'GET',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ]);
    $resPaymentsFilter = $controller->paymentHistory($reqPaymentsFilter);
    assertCondition($resPaymentsFilter->getStatusCode() === 200, "Driver payment history with filters returns HTTP 200", $failures, $successCount);
} else {
    echo " [SKIP] No driver profile found in database for execution test.\n";
}

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n============================================\n";
echo "TEST RESULTS: {$successCount} PASSED, " . count($failures) . " FAILED\n";
echo "============================================\n";

if (!empty($failures)) {
    exit(1);
}
exit(0);

