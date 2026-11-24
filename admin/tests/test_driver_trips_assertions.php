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

$_GET['status'] = 'assigned';
$_GET['search'] = '';
$_GET['per_page'] = '10';

$c = new \App\Controllers\DriverPortalController();
$req = new \App\Core\Request();

$res = $c->assignedTrips($req);
$html = $res->getBody();

$checks = [
    'Hero Page Header' => strpos($html, 'My Assigned Trips') !== false,
    'KPI 4 Cards Grid' => strpos($html, 'booking-kpi-grid') !== false && strpos($html, 'booking-kpi-card') !== false,
    'One-row LG CSS Rule' => (strpos($html, 'repeat(4, minmax(0, 1fr)) !important') !== false || strpos($html, 'repeat(4, 1fr) !important') !== false),
    'Filter Bar Compact' => strpos($html, 'filter-bar-compact') !== false,
    'Search Input' => strpos($html, 'name="search"') !== false,
    'Date Input' => strpos($html, 'name="date"') !== false,
    'Status Select' => strpos($html, 'name="status"') !== false,
    'Per Page Select' => strpos($html, 'name="per_page"') !== false,
    'Reset/Clear Button' => strpos($html, 'filterClearBtn') !== false,
    'Modern Table Container' => strpos($html, 'modern-table-container') !== false && strpos($html, 'modern-table') !== false,
    'Table Columns (Route, Fleet, Passenger)' => strpos($html, 'Route Itinerary') !== false && strpos($html, 'Assigned Fleet') !== false && strpos($html, 'Passenger / Contact') !== false,
    'Driver Amount Decoupling (no profit/margins)' => strpos($html, 'Customer Profit') === false && strpos($html, 'Margin') === false,
    'Action Dropdown / Actions' => strpos($html, 'action-toggle-btn') !== false && strpos($html, 'action-btns-popup') !== false,
    'Mobile UI & Filter Panel' => strpos($html, 'mv-filter-panel') !== false && strpos($html, 'mv-status-scroll') !== false && strpos($html, 'mv-booking-card-wrapper') !== false,
    'AJAX Script Engine' => strpos($html, 'filterTripsAjax') !== false && strpos($html, 'initDriverTripsAjaxFilter') !== false
];

$allPassed = true;
foreach ($checks as $label => $passed) {
    if (!$passed) $allPassed = false;
    echo ($passed ? '[PASS]' : '[FAIL]') . ' ' . $label . PHP_EOL;
}

if ($allPassed) {
    echo PHP_EOL . "ALL 15 DETAILED CHECKS PASSED SUCCESSFULLY." . PHP_EOL;
    exit(0);
} else {
    echo PHP_EOL . "SOME CHECKS FAILED." . PHP_EOL;
    exit(1);
}
