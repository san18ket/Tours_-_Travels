<?php
/**
 * Comprehensive Test Suite for AJAX Filters and Top KPI Cards across all Office Pages
 *
 * Verifies that:
 * 1. Customers (/office/customers)
 * 2. Bookings (/office/bookings)
 * 3. Employees (/office/employees)
 * 4. Vehicles (/office/vehicles)
 * 5. Trip History (/office/trips/history)
 * 6. Invoices (/office/invoices)
 * 7. Users (/office/users)
 * 8. Roles (/office/roles)
 * 9. Role Permissions (/office/role-permission)
 *
 * all render successfully with AJAX headers, contain the required DOM IDs,
 * interactive card attributes, filter inputs, pagination wrappers, and client-side AJAX engines.
 */

// Define application root and environment
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Controllers\CustomerController;
use App\Controllers\BookingController;
use App\Controllers\EmployeeController;
use App\Controllers\VehicleController;
use App\Controllers\DispatchController;
use App\Controllers\InvoiceController;
use App\Controllers\UserController;
use App\Controllers\RoleController;

// Initialize Session for CLI test
Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

$passedTests = 0;
$totalTests = 0;

function assertCondition(bool $cond, string $message) {
    global $passedTests, $totalTests;
    $totalTests++;
    if ($cond) {
        $passedTests++;
        echo "  [PASS] {$message}\n";
    } else {
        echo "  [FAIL] {$message}\n";
    }
}

echo "====================================================================\n";
echo "RUNNING COMPREHENSIVE AJAX FILTERS & KPI CARDS TEST SUITE\n";
echo "====================================================================\n\n";

// Helper to simulate request
function createMockRequest(string $uri, array $query = [], array $headers = []): Request {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $uri;
    $_GET = $query;
    $_POST = [];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    foreach ($headers as $k => $v) {
        $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
    }
    return new Request();
}

// -----------------------------------------------------------------------------
// 1. Customers (/office/customers)
// -----------------------------------------------------------------------------
echo "1. Testing Customers (/office/customers)...\n";
try {
    $req = createMockRequest('/office/customers', ['status' => 'active', 'search' => 'test']);
    $controller = new CustomerController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Customers index returns HTTP 200");
    assertCondition(strpos($html, 'id="customersKpiGrid"') !== false, "Contains #customersKpiGrid");
    assertCondition(strpos($html, 'id="customersSection"') !== false, "Contains #customersSection");
    assertCondition(strpos($html, 'id="customersPaginationWrapper"') !== false, "Contains #customersPaginationWrapper");
    assertCondition(strpos($html, 'initCustomerAjaxFilter') !== false, "Contains initCustomerAjaxFilter");
    assertCondition(strpos($html, 'updateCustomerMetrics') !== false, "Contains updateCustomerMetrics");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 2. Bookings (/office/bookings)
// -----------------------------------------------------------------------------
echo "\n2. Testing Bookings (/office/bookings)...\n";
try {
    $req = createMockRequest('/office/bookings', ['status' => 'confirmed']);
    $controller = new BookingController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Bookings index returns HTTP 200");
    assertCondition(strpos($html, 'id="bookingsKpiGrid"') !== false, "Contains #bookingsKpiGrid");
    assertCondition(strpos($html, 'id="bookingsSection"') !== false, "Contains #bookingsSection");
    assertCondition(strpos($html, 'id="bookingsPaginationWrapper"') !== false, "Contains #bookingsPaginationWrapper");
    assertCondition(strpos($html, 'id="bookingsMvStatusScroll"') !== false, "Contains #bookingsMvStatusScroll");
    assertCondition(strpos($html, 'updateBookingMetrics') !== false, "Contains updateBookingMetrics");
    assertCondition(strpos($html, 'initBookingAjaxFilter') !== false, "Contains initBookingAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 3. Employees (/office/employees)
// -----------------------------------------------------------------------------
echo "\n3. Testing Employees (/office/employees)...\n";
try {
    $req = createMockRequest('/office/employees', ['status' => 'active']);
    $controller = new EmployeeController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Employees index returns HTTP 200");
    assertCondition(strpos($html, 'id="employeesKpiGrid"') !== false, "Contains #employeesKpiGrid");
    assertCondition(strpos($html, 'id="employeesSection"') !== false, "Contains #employeesSection");
    assertCondition(strpos($html, 'id="employeesPaginationWrapper"') !== false, "Contains #employeesPaginationWrapper");
    assertCondition(strpos($html, 'id="employeesMvStatusScroll"') !== false, "Contains #employeesMvStatusScroll");
    assertCondition(strpos($html, 'updateEmployeeMetrics') !== false, "Contains updateEmployeeMetrics");
    assertCondition(strpos($html, 'initEmployeeAjaxFilter') !== false, "Contains initEmployeeAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 4. Vehicles (/office/vehicles)
// -----------------------------------------------------------------------------
echo "\n4. Testing Vehicles (/office/vehicles)...\n";
try {
    $req = createMockRequest('/office/vehicles', ['status' => 'active']);
    $controller = new VehicleController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Vehicles index returns HTTP 200");
    assertCondition(strpos($html, 'id="vehiclesKpiGrid"') !== false, "Contains #vehiclesKpiGrid");
    assertCondition(strpos($html, 'id="vehiclesSection"') !== false, "Contains #vehiclesSection");
    assertCondition(strpos($html, 'id="vehiclesPaginationWrapper"') !== false, "Contains #vehiclesPaginationWrapper");
    assertCondition(strpos($html, 'id="vehiclesMvStatusScroll"') !== false, "Contains #vehiclesMvStatusScroll");
    assertCondition(strpos($html, 'updateVehicleMetrics') !== false, "Contains updateVehicleMetrics");
    assertCondition(strpos($html, 'initVehicleAjaxFilter') !== false, "Contains initVehicleAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 5. Trip History (/office/trips/history)
// -----------------------------------------------------------------------------
echo "\n5. Testing Trip History (/office/trips/history)...\n";
try {
    $req = createMockRequest('/office/trips/history', ['status' => 'completed']);
    $controller = new DispatchController();
    $resp = $controller->history($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Trip history returns HTTP 200");
    assertCondition(strpos($html, 'id="tripsKpiGrid"') !== false, "Contains #tripsKpiGrid");
    assertCondition(strpos($html, 'id="tripsSection"') !== false, "Contains #tripsSection");
    assertCondition(strpos($html, 'id="tripsPaginationWrapper"') !== false, "Contains #tripsPaginationWrapper");
    assertCondition(strpos($html, 'id="tripsMvStatusScroll"') !== false, "Contains #tripsMvStatusScroll");
    assertCondition(strpos($html, 'updateTripMetrics') !== false, "Contains updateTripMetrics");
    assertCondition(strpos($html, 'initTripAjaxFilter') !== false, "Contains initTripAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 6. Invoices (/office/invoices)
// -----------------------------------------------------------------------------
echo "\n6. Testing Invoices (/office/invoices)...\n";
try {
    $req = createMockRequest('/office/invoices', ['status' => 'paid']);
    $controller = new InvoiceController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Invoices index returns HTTP 200");
    assertCondition(strpos($html, 'id="invoicesKpiGrid"') !== false, "Contains #invoicesKpiGrid");
    assertCondition(strpos($html, 'id="invoicesSection"') !== false, "Contains #invoicesSection");
    assertCondition(strpos($html, 'id="invoicesPaginationWrapper"') !== false, "Contains #invoicesPaginationWrapper");
    assertCondition(strpos($html, 'id="invoicesMvStatusScroll"') !== false, "Contains #invoicesMvStatusScroll");
    assertCondition(strpos($html, 'initInvoiceAjaxFilter') !== false, "Contains initInvoiceAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 7. Users (/office/users)
// -----------------------------------------------------------------------------
echo "\n7. Testing Users (/office/users)...\n";
try {
    $req = createMockRequest('/office/users', ['role' => 'admin', 'status' => 'active']);
    $controller = new UserController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Users index returns HTTP 200");
    assertCondition(strpos($html, 'id="usersKpiGrid"') !== false, "Contains #usersKpiGrid");
    assertCondition(strpos($html, 'id="usersSection"') !== false, "Contains #usersSection");
    assertCondition(strpos($html, 'id="usersPaginationWrapper"') !== false, "Contains #usersPaginationWrapper");
    assertCondition(strpos($html, 'id="usersMvStatusScroll"') !== false, "Contains #usersMvStatusScroll");
    assertCondition(strpos($html, 'updateUserMetrics') !== false, "Contains updateUserMetrics");
    assertCondition(strpos($html, 'initUserAjaxFilter') !== false, "Contains initUserAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 8. Roles (/office/roles)
// -----------------------------------------------------------------------------
echo "\n8. Testing Roles (/office/roles)...\n";
try {
    $req = createMockRequest('/office/roles', ['status' => 'active', 'search' => 'admin']);
    $controller = new RoleController();
    $resp = $controller->index($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Roles index returns HTTP 200");
    assertCondition(strpos($html, 'id="rolesKpiGrid"') !== false, "Contains #rolesKpiGrid");
    assertCondition(strpos($html, 'id="rolesSection"') !== false, "Contains #rolesSection");
    assertCondition(strpos($html, 'id="rolesPaginationWrapper"') !== false, "Contains #rolesPaginationWrapper");
    assertCondition(strpos($html, 'id="rolesMvStatusScroll"') !== false, "Contains #rolesMvStatusScroll");
    assertCondition(strpos($html, 'initRoleAjaxFilter') !== false, "Contains initRoleAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// 9. Role Permissions Directory (/office/role-permission)
// -----------------------------------------------------------------------------
echo "\n9. Testing Role Permissions Directory (/office/role-permission)...\n";
try {
    $req = createMockRequest('/office/role-permission', ['type' => 'system']);
    $controller = new RoleController();
    $resp = $controller->permissionIndex($req);
    $html = (string)$resp->getContent();

    assertCondition($resp->getStatusCode() === 200, "Role Permissions index returns HTTP 200");
    assertCondition(strpos($html, 'id="rolePermsKpiGrid"') !== false, "Contains #rolePermsKpiGrid");
    assertCondition(strpos($html, 'id="rolePermFilterForm"') !== false, "Contains #rolePermFilterForm");
    assertCondition(strpos($html, 'id="rolePermsSection"') !== false, "Contains #rolePermsSection");
    assertCondition(strpos($html, 'id="rolePermsPaginationWrapper"') !== false, "Contains #rolePermsPaginationWrapper");
    assertCondition(strpos($html, 'initRolePermissionAjaxFilter') !== false, "Contains initRolePermissionAjaxFilter");
} catch (\Throwable $e) {
    echo "  [EXCEPTION] " . $e->getMessage() . "\n";
}

echo "\n====================================================================\n";
echo "SUMMARY: {$passedTests} / {$totalTests} tests passed.\n";
echo "====================================================================\n";
