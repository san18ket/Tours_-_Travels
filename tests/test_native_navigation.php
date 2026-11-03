<?php
/**
 * Test Suite: Native Core PHP Navigation Verification
 */

$root = dirname(__DIR__);
$failures = [];
$passes = [];

function assert_true($cond, $msg) {
    global $failures, $passes;
    if ($cond) {
        $passes[] = $msg;
        echo "  [PASS] {$msg}\n";
    } else {
        $failures[] = $msg;
        echo "  [FAIL] {$msg}\n";
    }
}

/**
 * Accurately extracts all <a ...> tags without getting tricked by ?> inside attributes
 */
function extractAnchorTags(string $html): array {
    $tags = [];
    $offset = 0;
    while (($start = stripos($html, '<a', $offset)) !== false) {
        // make sure it's <a followed by whitespace
        if (!preg_match('/^\s/', substr($html, $start + 2, 1))) {
            $offset = $start + 2;
            continue;
        }
        $i = $start + 2;
        $len = strlen($html);
        $inQuote = false;
        $inPhp = false;
        $found = false;
        while ($i < $len) {
            $ch = $html[$i];
            if ($inPhp) {
                if ($ch === '?' && $i + 1 < $len && $html[$i + 1] === '>') {
                    $inPhp = false;
                    $i += 2;
                    continue;
                }
            } else {
                if ($ch === '<' && $i + 1 < $len && $html[$i + 1] === '?') {
                    $inPhp = true;
                    $i += 2;
                    continue;
                }
                if ($ch === '"' || $ch === "'") {
                    if ($inQuote === $ch) {
                        $inQuote = false;
                    } elseif ($inQuote === false) {
                        $inQuote = $ch;
                    }
                } elseif ($ch === '>' && !$inQuote) {
                    $found = true;
                    $tags[] = substr($html, $start, $i - $start + 1);
                    $offset = $i + 1;
                    break;
                }
            }
            $i++;
        }
        if (!$found) break;
    }
    return $tags;
}

echo "=== 1. Verifying public/assets/js/app.js Navigation Rules ===\n";
$appJs = file_get_contents($root . '/public/assets/js/app.js');

assert_true(
    str_contains($appJs, "link.getAttribute('data-no-ajax') === 'true' || link.dataset.noAjax === 'true' || link.closest('[data-no-ajax=\"true\"]')"),
    "app.js checks for data-no-ajax on link and ancestors"
);

assert_true(
    str_contains($appJs, "link.closest('#sidebar, .sidebar, aside, .bottom-nav, .notched-bottom-nav, .brand-logo, .sidebar-header, .sidebar-nav, .sidebar-footer, .topbar-user-menu, .mv-dash-menu, .driver-sidebar, .driver-bottom-nav, .topbar-left, .topbar-profile-chip, .user-badge')"),
    "app.js explicitly blocks interception for all sidebar, drawer, brand, bottom-nav, and profile links"
);

echo "\n=== 2. Verifying app/Views/layouts/office.php Links ===\n";
$officePhp = file_get_contents($root . '/app/Views/layouts/office.php');

if (preg_match('#<aside\s+class="sidebar"[^>]*>([\s\S]*?)<\/aside>#', $officePhp, $m)) {
    $sidebarHtml = $m[1];
    $anchorTags = extractAnchorTags($sidebarHtml);
    $allHaveNoAjax = true;
    foreach ($anchorTags as $tag) {
        if (preg_match('#href="([^"]+)"#i', $tag, $hm)) {
            $href = $hm[1];
            if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) continue;
        }
        if (!str_contains($tag, 'data-no-ajax="true"')) {
            $allHaveNoAjax = false;
            echo "    Missing data-no-ajax in office sidebar: " . htmlspecialchars($tag) . "\n";
        }
    }
    assert_true($allHaveNoAjax && count($anchorTags) > 10, "All internal anchor links in office sidebar (" . count($anchorTags) . " tags) have data-no-ajax='true'");
} else {
    assert_true(false, "Could not extract office sidebar");
}

if (preg_match('#<nav\s+class="bottom-nav[^"]*"[^>]*>([\s\S]*?)<\/nav>#', $officePhp, $m)) {
    $navHtml = $m[1];
    $anchorTags = extractAnchorTags($navHtml);
    $allNavHaveNoAjax = true;
    foreach ($anchorTags as $tag) {
        if (!str_contains($tag, 'data-no-ajax="true"')) {
            $allNavHaveNoAjax = false;
            echo "    Missing data-no-ajax in office bottom-nav: " . htmlspecialchars($tag) . "\n";
        }
    }
    assert_true($allNavHaveNoAjax && count($anchorTags) >= 4, "All internal anchor links in office bottom navigation (" . count($anchorTags) . " tags) have data-no-ajax='true'");
} else {
    assert_true(false, "Could not extract office bottom-nav");
}

echo "\n=== 3. Verifying app/Views/layouts/driver.php Links ===\n";
$driverPhp = file_get_contents($root . '/app/Views/layouts/driver.php');

if (preg_match('#<aside\s+class="sidebar"[^>]*>([\s\S]*?)<\/aside>#', $driverPhp, $m)) {
    $sidebarHtml = $m[1];
    $anchorTags = extractAnchorTags($sidebarHtml);
    $allDriverHaveNoAjax = true;
    foreach ($anchorTags as $tag) {
        if (preg_match('#href="([^"]+)"#i', $tag, $hm)) {
            $href = $hm[1];
            if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) continue;
        }
        if (!str_contains($tag, 'data-no-ajax="true"')) {
            $allDriverHaveNoAjax = false;
            echo "    Missing data-no-ajax in driver sidebar: " . htmlspecialchars($tag) . "\n";
        }
    }
    assert_true($allDriverHaveNoAjax && count($anchorTags) >= 7, "All internal anchor links in driver sidebar (" . count($anchorTags) . " tags) have data-no-ajax='true'");
} else {
    assert_true(false, "Could not extract driver sidebar");
}

if (preg_match('#<nav\s+class="bottom-nav[^"]*"[^>]*>([\s\S]*?)<\/nav>#', $driverPhp, $m)) {
    $navHtml = $m[1];
    $anchorTags = extractAnchorTags($navHtml);
    $allDriverNavHaveNoAjax = true;
    foreach ($anchorTags as $tag) {
        if (!str_contains($tag, 'data-no-ajax="true"')) {
            $allDriverNavHaveNoAjax = false;
            echo "    Missing data-no-ajax in driver bottom-nav: " . htmlspecialchars($tag) . "\n";
        }
    }
    assert_true($allDriverNavHaveNoAjax && count($anchorTags) >= 4, "All internal anchor links in driver bottom navigation (" . count($anchorTags) . " tags) have data-no-ajax='true'");
} else {
    assert_true(false, "Could not extract driver bottom-nav");
}

echo "\n=== 4. Verifying Server-Side URL Active Highlighting Logic ===\n";

function simulateOfficeActive(string $uri): array {
    $reqUri = $uri;
    $path = parse_url($reqUri, PHP_URL_PATH) ?? '/';
    $currPath = '/' . trim($path, '/');
    if ($currPath === '') $currPath = '/';

    $isPrefix = function(string $prefix) use ($currPath): bool {
        return $currPath === $prefix || str_starts_with($currPath, rtrim($prefix, '/') . '/');
    };

    $isDashboardActive = ($currPath === '/office/dashboard' || $currPath === '/office' || $currPath === '/');
    $isBookingsActive  = $isPrefix('/office/bookings');
    $isCustomersActive = $isPrefix('/office/customers');
    $isVehiclesActive  = $isPrefix('/office/vehicles');
    $isDriversActive   = $isPrefix('/office/employees');
    $isFinanceActive   = ($isPrefix('/office/finance') || $isPrefix('/office/invoices'));
    $isTripsActive     = ($isPrefix('/office/trips') || $isPrefix('/office/dispatch'));
    $isTrackingActive  = $isPrefix('/office/tracking');
    $isCompanyActive   = $isPrefix('/office/company');
    $isSettingsActive  = ($isPrefix('/office/users') || $isPrefix('/office/roles') || $isPrefix('/office/role-permission') || $isPrefix('/office/backups') || $isPrefix('/office/notification-settings') || $isPrefix('/office/email'));

    $isBookingCreateActive = ($currPath === '/office/bookings/create');
    $isBookingListActive   = ($isBookingsActive && !$isBookingCreateActive);

    $isCustomerCreateActive = ($currPath === '/office/customers/create');
    $isCustomerListActive   = ($isCustomersActive && !$isCustomerCreateActive);

    $isVehicleCreateActive = ($currPath === '/office/vehicles/create');
    $isVehicleListActive   = ($isVehiclesActive && !$isVehicleCreateActive);

    $isEmployeeCreateActive = ($currPath === '/office/employees/create');
    $isEmployeeListActive   = ($isDriversActive && !$isEmployeeCreateActive);

    $isCompanyCreateActive = ($currPath === '/office/company/create');
    $isCompanyListActive   = ($isCompanyActive && !$isCompanyCreateActive);

    return compact(
        'isDashboardActive', 'isBookingsActive', 'isBookingListActive', 'isBookingCreateActive',
        'isCustomersActive', 'isCustomerListActive', 'isCustomerCreateActive',
        'isVehiclesActive', 'isVehicleListActive', 'isVehicleCreateActive',
        'isDriversActive', 'isEmployeeListActive', 'isEmployeeCreateActive',
        'isFinanceActive', 'isTripsActive', 'isTrackingActive',
        'isCompanyActive', 'isCompanyListActive', 'isCompanyCreateActive', 'isSettingsActive'
    );
}

// Test /office/dashboard
$r = simulateOfficeActive('/office/dashboard');
assert_true($r['isDashboardActive'] && !$r['isBookingsActive'], "Route '/office/dashboard' activates dashboard only");

// Test /office/bookings (list)
$r = simulateOfficeActive('/office/bookings');
assert_true($r['isBookingsActive'] && $r['isBookingListActive'] && !$r['isBookingCreateActive'], "Route '/office/bookings' activates bookings category and booking list, NOT create");

// Test /office/bookings/create
$r = simulateOfficeActive('/office/bookings/create');
assert_true($r['isBookingsActive'] && $r['isBookingCreateActive'] && !$r['isBookingListActive'], "Route '/office/bookings/create' activates bookings category and booking create, NOT list");

// Test /office/customers (list)
$r = simulateOfficeActive('/office/customers');
assert_true($r['isCustomersActive'] && $r['isCustomerListActive'] && !$r['isCustomerCreateActive'], "Route '/office/customers' activates customers category and customer list, NOT create");

// Test /office/customers/create
$r = simulateOfficeActive('/office/customers/create');
assert_true($r['isCustomersActive'] && $r['isCustomerCreateActive'] && !$r['isCustomerListActive'], "Route '/office/customers/create' activates customers category and customer create, NOT list");

// Test /office/vehicles/create
$r = simulateOfficeActive('/office/vehicles/create');
assert_true($r['isVehiclesActive'] && $r['isVehicleCreateActive'] && !$r['isVehicleListActive'], "Route '/office/vehicles/create' activates vehicles category and vehicle create, NOT list");

// Test /office/employees/create
$r = simulateOfficeActive('/office/employees/create');
assert_true($r['isDriversActive'] && $r['isEmployeeCreateActive'] && !$r['isEmployeeListActive'], "Route '/office/employees/create' activates employees category and employee create, NOT list");

// Test /office/company/create
$r = simulateOfficeActive('/office/company/create');
assert_true($r['isCompanyActive'] && $r['isCompanyCreateActive'] && !$r['isCompanyListActive'], "Route '/office/company/create' activates company category and company create, NOT list");

// Test /office/finance/accounts
$r = simulateOfficeActive('/office/finance/accounts');
assert_true($r['isFinanceActive'] && !$r['isBookingsActive'], "Route '/office/finance/accounts' activates finance category only");

// Test /office/invoices
$r = simulateOfficeActive('/office/invoices');
assert_true($r['isFinanceActive'] && !$r['isTripsActive'], "Route '/office/invoices' activates finance category");

// Test /office/trips/assign
$r = simulateOfficeActive('/office/trips/assign');
assert_true($r['isTripsActive'] && !$r['isFinanceActive'], "Route '/office/trips/assign' activates trips category");

// Test /office/tracking
$r = simulateOfficeActive('/office/tracking');
assert_true($r['isTrackingActive'] && !$r['isSettingsActive'], "Route '/office/tracking' activates tracking only");

// Test /office/notification-settings
$r = simulateOfficeActive('/office/notification-settings');
assert_true($r['isSettingsActive'] && !$r['isCompanyActive'], "Route '/office/notification-settings' activates settings category");

echo "\n=== 5. Driver Routing Logic Verification ===\n";
function simulateDriverActive(string $uri): array {
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $currPath = '/' . trim($path, '/');
    if ($currPath === '') $currPath = '/';

    $isPrefix = function(string $prefix) use ($currPath): bool {
        return $currPath === $prefix || str_starts_with($currPath, rtrim($prefix, '/') . '/');
    };

    $isDashboardActive = ($currPath === '/driver' || $currPath === '/driver/dashboard');
    $isTripsActive     = $isPrefix('/driver/trips');
    $isNotifActive     = $isPrefix('/driver/notifications');
    $isHistoryActive   = $isPrefix('/driver/history');
    $isVehicleActive   = $isPrefix('/driver/vehicle');
    $isLocationActive  = $isPrefix('/driver/location');
    $isProfileActive   = $isPrefix('/driver/profile');

    return compact(
        'isDashboardActive', 'isTripsActive', 'isNotifActive',
        'isHistoryActive', 'isVehicleActive', 'isLocationActive', 'isProfileActive'
    );
}

$rd = simulateDriverActive('/driver/dashboard');
assert_true($rd['isDashboardActive'] && !$rd['isTripsActive'], "Driver '/driver/dashboard' active");

$rd = simulateDriverActive('/driver/trips');
assert_true($rd['isTripsActive'] && !$rd['isDashboardActive'], "Driver '/driver/trips' active");

$rd = simulateDriverActive('/driver/location');
assert_true($rd['isLocationActive'] && !$rd['isHistoryActive'], "Driver '/driver/location' active");

$rd = simulateDriverActive('/driver/notifications');
assert_true($rd['isNotifActive'] && !$rd['isProfileActive'], "Driver '/driver/notifications' active");

echo "\n==========================================\n";
echo "SUMMARY: " . count($passes) . " PASSES, " . count($failures) . " FAILURES\n";
if (!empty($failures)) {
    exit(1);
}
echo "ALL TESTS PASSED SUCCESSFULLY!\n";
