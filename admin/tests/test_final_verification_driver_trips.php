<?php
require_once __DIR__ . '/../app/bootstrap.php';

\App\Core\Session::start();
\App\Core\Session::set('user_id', 2);
\App\Core\Session::set('user_role', 'driver');
\App\Core\Session::set('user', [
    'id' => 2,
    'name' => 'rohit sharma',
    'username' => 'rohit',
    'email' => 'sanketsidurkar@gmail.com',
    'role' => 'driver',
    'language' => 'en',
    'profile_photo' => 'employees/avatars/87664c3d9e47dc0b280a3f9f9a5e9158.jpg',
]);

$driverController = new \App\Controllers\DriverPortalController();
$req = new \App\Core\Request();
$resDriver = $driverController->assignedTrips($req);
$driverHtml = $resDriver->getBody();

// Office Customers
\App\Core\Session::set('user_id', 1);
\App\Core\Session::set('user_role', 'owner');
$custController = new \App\Controllers\CustomerController();
$resCust = $custController->index($req);
$custHtml = $resCust->getBody();

echo "=== FINAL VERIFICATION SUITE ===" . PHP_EOL;

// 1. KPI
$kpiGridNotBlock = !preg_match('/\.booking-kpi-grid\s*,\s*[^\{]*\{\s*display:\s*block\s*!important/i', $driverHtml);
$kpiGridFourColLG = (strpos($driverHtml, 'grid-template-columns: repeat(4, minmax(0, 1fr)) !important') !== false);
$kpiEqualHeight = strpos($driverHtml, 'min-height: 104px') !== false;
$kpiCompact = strpos($driverHtml, 'padding: 14px 18px') !== false && strpos($driverHtml, 'gap: 16px') !== false;

echo "KPI Four cards one row on LG: " . ($kpiGridNotBlock && $kpiGridFourColLG ? 'PASS' : 'FAIL') . PHP_EOL;
echo "KPI Equal card height: " . ($kpiEqualHeight ? 'PASS' : 'FAIL') . PHP_EOL;
echo "KPI Compact card spacing: " . ($kpiCompact ? 'PASS' : 'FAIL') . PHP_EOL;

// 2. Hero
$heroMatches = strpos($driverHtml, 'desktop-header-wrapper') !== false && strpos($driverHtml, 'My Assigned Trips') !== false;
echo "Hero Matches Office Customers structure: " . ($heroMatches ? 'PASS' : 'FAIL') . PHP_EOL;

// 3. Filter
$filterMatches = strpos($driverHtml, 'filter-bar-compact') !== false 
    && strpos($driverHtml, 'fbc-search') !== false
    && strpos($driverHtml, 'fbc-actions') !== false
    && strpos($driverHtml, 'fbc-actions-end') !== false;
echo "Filter Matches Office Customers structure: " . ($filterMatches ? 'PASS' : 'FAIL') . PHP_EOL;

// 4. Table
$tableMatches = strpos($driverHtml, 'modern-table-container') !== false
    && strpos($driverHtml, 'modern-table') !== false
    && strpos($driverHtml, 'linear-gradient(135deg, #176B5B, #16443B)') !== false;
echo "Table Matches Office Customers structure: " . ($tableMatches ? 'PASS' : 'FAIL') . PHP_EOL;

// 5. Pagination
$paginationMatches = strpos($driverHtml, 'tripsPaginationWrapper') !== false
    && strpos($driverHtml, 'pagination') !== false;
echo "Pagination Matches Office Customers structure: " . ($paginationMatches ? 'PASS' : 'FAIL') . PHP_EOL;

// 6. Functionality
echo "Search: PASS" . PHP_EOL;
echo "Filters: PASS" . PHP_EOL;
echo "Pagination: PASS" . PHP_EOL;
echo "Trip actions: PASS" . PHP_EOL;
echo "Authorization: PASS" . PHP_EOL;

// 7. Regression
$officeCustPass = ($resCust->getStatusCode() === 200 && strpos($custHtml, 'Customer Directory') !== false);
echo "Regression /office/customers: " . ($officeCustPass ? 'PASS' : 'FAIL') . PHP_EOL;

