<?php
/**
 * Test Suite: Simplified Sidebar Navigation & Direct Listing Pages
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Session;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Controllers\CustomerController;
use App\Controllers\VehicleController;
use App\Controllers\EmployeeController;
use App\Controllers\CompanyController;

echo "======================================================================\n";
echo "   SIMPLIFIED SIDEBAR NAVIGATION & DIRECT LISTINGS TEST SUITE\n";
echo "======================================================================\n";

Session::set('user_id', 1);
Session::set('user_role', 'admin');
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Helper to render layout with a specific URI
function renderSidebarForUri(string $uri): string {
    $_SERVER['REQUEST_URI'] = $uri;
    $user = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@manaswi.com', 'role' => 'admin'];
    $userRole = 'Admin';
    $flashes = [];
    $content = '<p>Test Page Content</p>';
    
    ob_start();
    include __DIR__ . '/../app/Views/layouts/office.php';
    return ob_get_clean();
}

$dashboardHtml = renderSidebarForUri('/office/dashboard');

// -----------------------------------------------------------------------------
// Test 1: Verify separate Add/Create links are REMOVED from the sidebar
// -----------------------------------------------------------------------------
$sidebarLinksToCheck = [
    '/office/bookings/create' => 'New Booking',
    '/office/customers/create' => 'Add Customer',
    '/office/vehicles/create' => 'Add Vehicle',
    '/office/employees/create' => 'Add Driver / Staff',
    '/office/company/create' => 'Add Company',
];

// Extract sidebar HTML only
preg_match('/<aside class="sidebar"[^>]*>(.*?)<\/aside>/s', $dashboardHtml, $sidebarMatch);
$sidebarHtml = $sidebarMatch[1] ?? '';

if (empty($sidebarHtml)) {
    echo " [FAIL] Could not isolate <aside class=\"sidebar\"> in layout output\n";
    exit(1);
}

foreach ($sidebarLinksToCheck as $href => $label) {
    if (strpos($sidebarHtml, 'href="' . $href . '"') !== false || strpos($sidebarHtml, '>' . $label . '<') !== false) {
        echo " [FAIL] Sidebar still contains Add/Create link: $label ($href)\n";
        exit(1);
    }
}
echo " [PASS] 1. All separate Add/Create links successfully removed from sidebar\n";

// -----------------------------------------------------------------------------
// Test 2: Verify main listing pages are converted to clean direct links
// -----------------------------------------------------------------------------
$directCategories = [
    'cat-dashboard' => '/office/dashboard',
    'cat-bookings'  => '/office/bookings',
    'cat-customers' => '/office/customers',
    'cat-vehicles'  => '/office/vehicles',
    'cat-drivers'   => '/office/employees',
    'cat-company'   => '/office/company',
    'cat-tracking'  => '/office/tracking',
    'cat-reports'   => '/office/finance/reports',
];

foreach ($directCategories as $catId => $expectedUrl) {
    // Check that it uses an <a> direct-link instead of <button toggleCategory>
    if (!preg_match('/<div class="sidebar-category" id="' . $catId . '">\s*<a href="[^"]*' . preg_quote($expectedUrl, '/') . '" class="category-header direct-link/s', $sidebarHtml)) {
        echo " [FAIL] Category [$catId] is not rendered as a direct-link pointing to $expectedUrl\n";
        exit(1);
    }
}
echo " [PASS] 2. Bookings, Customers, Vehicles, Employees, and Company are all direct-link listing items\n";

// -----------------------------------------------------------------------------
// Test 3: Active Highlighting across Listing, Create, Show, and Edit routes
// -----------------------------------------------------------------------------
$activeScenarios = [
    // Customers
    ['uri' => '/office/customers',        'catId' => 'cat-customers', 'shouldBeActive' => true],
    ['uri' => '/office/customers/create', 'catId' => 'cat-customers', 'shouldBeActive' => true],
    ['uri' => '/office/customers/12',     'catId' => 'cat-customers', 'shouldBeActive' => true],
    ['uri' => '/office/customers/12/edit','catId' => 'cat-customers', 'shouldBeActive' => true],
    ['uri' => '/office/customers',        'catId' => 'cat-bookings',  'shouldBeActive' => false],

    // Bookings
    ['uri' => '/office/bookings',         'catId' => 'cat-bookings',  'shouldBeActive' => true],
    ['uri' => '/office/bookings/create',  'catId' => 'cat-bookings',  'shouldBeActive' => true],
    ['uri' => '/office/bookings/10',      'catId' => 'cat-bookings',  'shouldBeActive' => true],
    ['uri' => '/office/bookings/10/edit', 'catId' => 'cat-bookings',  'shouldBeActive' => true],
    ['uri' => '/office/bookings',         'catId' => 'cat-customers', 'shouldBeActive' => false],

    // Vehicles
    ['uri' => '/office/vehicles',         'catId' => 'cat-vehicles',  'shouldBeActive' => true],
    ['uri' => '/office/vehicles/create',  'catId' => 'cat-vehicles',  'shouldBeActive' => true],
    ['uri' => '/office/vehicles/3/edit',  'catId' => 'cat-vehicles',  'shouldBeActive' => true],

    // Employees
    ['uri' => '/office/employees',        'catId' => 'cat-drivers',   'shouldBeActive' => true],
    ['uri' => '/office/employees/create', 'catId' => 'cat-drivers',   'shouldBeActive' => true],
    ['uri' => '/office/employees/2/edit', 'catId' => 'cat-drivers',   'shouldBeActive' => true],

    // Company
    ['uri' => '/office/company',          'catId' => 'cat-company',   'shouldBeActive' => true],
    ['uri' => '/office/company/create',   'catId' => 'cat-company',   'shouldBeActive' => true],
    ['uri' => '/office/company/1/edit',   'catId' => 'cat-company',   'shouldBeActive' => true],
];

foreach ($activeScenarios as $sc) {
    $html = renderSidebarForUri($sc['uri']);
    
    // Check if element with id="{catId}-header" contains 'has-active' in its class attribute
    $hasHeader = preg_match('/<a[^>]+id="' . $sc['catId'] . '-header"[^>]*>/s', $html, $m1);
    if (!$hasHeader) {
        preg_match('/<a[^>]+class="[^"]*"[^>]+id="' . $sc['catId'] . '-header"[^>]*>/s', $html, $m1);
    }
    $headerTag = $m1[0] ?? '';
    $isActive = (strpos($headerTag, 'has-active') !== false);
    
    if ($sc['shouldBeActive'] && !$isActive) {
        echo " [FAIL] Expected [{$sc['catId']}] to be active on URI [{$sc['uri']}], but was not! (Tag: $headerTag)\n";
        exit(1);
    }
    if (!$sc['shouldBeActive'] && $isActive) {
        echo " [FAIL] Expected [{$sc['catId']}] NOT to be active on URI [{$sc['uri']}], but it was! (Tag: $headerTag)\n";
        exit(1);
    }
}
echo " [PASS] 3. Active menu highlighting works accurately on Listing, Create, Show, and Edit routes\n";

// -----------------------------------------------------------------------------
// Test 4: Verify Multi-Page Dropdowns are Preserved (Finance, Trips, Settings)
// -----------------------------------------------------------------------------
$dropdownCategories = ['cat-finance', 'cat-trips', 'cat-settings'];
foreach ($dropdownCategories as $catId) {
    if (strpos($sidebarHtml, 'id="' . $catId . '-header"') === false ||
        strpos($sidebarHtml, 'onclick="toggleCategory(\'' . $catId . '\')"') === false) {
        echo " [FAIL] Multi-page dropdown [$catId] missing or not configured with toggleCategory\n";
        exit(1);
    }
}
echo " [PASS] 4. Multi-page dropdowns (Finance, Trips, Settings) preserved with accordion toggle\n";

// -----------------------------------------------------------------------------
// Test 5: Verify Header Add Buttons on Main Listing Pages
// -----------------------------------------------------------------------------
$listingViewsToCheck = [
    'Bookings'   => ['file' => __DIR__ . '/../app/Views/office/bookings/index.php',  'target' => '/office/bookings/create'],
    'Customers'  => ['file' => __DIR__ . '/../app/Views/office/customers/index.php', 'target' => '/office/customers/create'],
    'Vehicles'   => ['file' => __DIR__ . '/../app/Views/office/vehicles/index.php',  'target' => '/office/vehicles/create'],
    'Employees'  => ['file' => __DIR__ . '/../app/Views/office/employees/index.php', 'target' => '/office/employees/create'],
    'Company'    => ['file' => __DIR__ . '/../app/Views/office/company/index.php',   'target' => '/office/company/create'],
];

foreach ($listingViewsToCheck as $name => $info) {
    $content = file_get_contents($info['file']);
    if (strpos($content, $info['target']) === false) {
        echo " [FAIL] $name listing page is missing its header Add button pointing to {$info['target']}\n";
        exit(1);
    }
}
echo " [PASS] 5. All main listing pages retain header '+ Add' action buttons\n";

// -----------------------------------------------------------------------------
// Test 6: Verify Add/Create Controller Form Endpoints are fully functional
// -----------------------------------------------------------------------------
$controllersToTest = [
    'Bookings'  => [new BookingController(), '/office/bookings/create'],
    'Customers' => [new CustomerController(), '/office/customers/create'],
    'Vehicles'  => [new VehicleController(), '/office/vehicles/create'],
    'Employees' => [new EmployeeController(), '/office/employees/create'],
    'Company'   => [new CompanyController(), '/office/company/create'],
];

foreach ($controllersToTest as $name => [$controller, $uri]) {
    try {
        $_SERVER['REQUEST_URI'] = $uri;
        $req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $uri]);
        $res = $controller->create($req);
        if ($res->getStatusCode() !== 200 || empty($res->getContent())) {
            echo " [FAIL] $name create form failed with status " . $res->getStatusCode() . "\n";
            exit(1);
        }
        echo "   - $name create form OK (Status 200)\n";
    } catch (\Throwable $e) {
        echo " [FAIL] $name create form exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}
echo " [PASS] 6. Add/Create form pages render with HTTP 200 and all forms intact\n";

echo "======================================================================\n";
echo "SUCCESS: ALL SIMPLIFIED SIDEBAR TESTS PASSED 100%!\n";
echo "======================================================================\n";
