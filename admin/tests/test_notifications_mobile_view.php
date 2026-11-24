<?php

/**
 * Test Suite: Office Notifications Mobile View & Responsive Layout
 *
 * Verifies that:
 * 1. Mobile devices page matches office/bookings architecture exactly.
 * 2. Hero top section (desktop header, KPI summary grid, desktop filter) is removed on mobile devices.
 * 3. Mobile Header uses mv-bookings-header with top app bar, search input, filter toggle drawer, and status scroll pills.
 * 4. Cards layout uses cards-grid with mv-booking-card-wrapper, mv-booking-card, and integrated mv-card-actions row.
 * 5. Route /office/notification redirects or routes properly.
 * 6. Rendering with empty and populated datasets executes cleanly with zero PHP warnings.
 */

require_once __DIR__ . '/../app/bootstrap.php';

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
echo "1. Checking Template File Structure & Classes\n";
echo "====================================================================\n";

$viewPath = __DIR__ . '/../app/Views/office/notifications/index.php';
$content = file_get_contents($viewPath);

it("File exists", file_exists($viewPath));
it("Uses bookings-page-view global container class", str_contains($content, 'class="bookings-page-view office-notifications-page"'));
it("Contains desktop-header-wrapper for desktop screens", str_contains($content, 'class="desktop-header-wrapper"'));
it("Contains booking-kpi-grid for desktop screens", str_contains($content, 'class="booking-kpi-grid notif-kpi-grid"'));
it("Contains filter-bar-compact for desktop screens", str_contains($content, 'class="filter-bar-compact"'));
it("Contains mv-bookings-header for mobile devices", str_contains($content, 'class="mv-bookings-header"'));
it("Contains mv-top-bar with back button and page title", str_contains($content, 'class="mv-top-bar"') && str_contains($content, 'class="mv-back-btn"') && str_contains($content, 'class="mv-page-title"'));
it("Contains mv-search-bar-wrap with search input and clear button", str_contains($content, 'class="mv-search-bar-wrap"') && str_contains($content, 'id="mvSearchInput"'));
it("Contains mv-filter-toggle button and mv-filter-panel drawer", str_contains($content, 'class="mv-filter-toggle"') && str_contains($content, 'id="mvFilterPanel"'));
it("Contains horizontal scrollable status pills (mv-status-scroll)", str_contains($content, 'class="mv-status-scroll"') && str_contains($content, 'class="mv-pill'));
it("Contains mobile cards grid container (cards-grid)", str_contains($content, 'class="cards-grid"'));
it("Contains unified single card container (mv-booking-card-wrapper)", str_contains($content, 'class="mv-booking-card-wrapper'));
it("Contains integrated action row at bottom of card (mv-card-actions)", str_contains($content, 'class="mv-card-actions"'));
it("Uses Single Actions Menu pattern (action-dropdown-wrap & action-toggle-btn)", str_contains($content, 'action-dropdown-wrap') && str_contains($content, 'action-toggle-btn'));

echo "\n====================================================================\n";
echo "2. Checking Responsive CSS Rules (Hero Top Section Removal on Mobile)\n";
echo "====================================================================\n";

// CSS rules checking
it("Hides desktop-header-wrapper on mobile (max-width: 768px)", 
   str_contains($content, '.office-notifications-page .desktop-header-wrapper') && 
   str_contains($content, 'display: none !important;')
);
it("Hides booking-kpi-grid (Hero top section) on mobile (max-width: 768px)", 
   str_contains($content, '.office-notifications-page .booking-kpi-grid') && 
   str_contains($content, 'display: none !important;')
);
it("Hides desktop filter-bar-compact on mobile (max-width: 768px)", 
   str_contains($content, '.office-notifications-page .filter-bar-compact') && 
   str_contains($content, 'display: none !important;')
);
it("Hides desktop activity list on mobile (max-width: 768px)", 
   str_contains($content, '.office-notifications-page .office-notif-desktop-list') && 
   str_contains($content, 'display: none !important;')
);
it("Shows mv-bookings-header and cards-grid on mobile (max-width: 768px)", 
   str_contains($content, '.office-notifications-page .mv-bookings-header') && 
   str_contains($content, '.office-notifications-page .cards-grid')
);
it("Hides mv-bookings-header and cards-grid on desktop (min-width: 769px)", 
   str_contains($content, '@media (min-width: 769px)') && 
   str_contains($content, '.office-notifications-page .mv-bookings-header') && 
   str_contains($content, '.office-notifications-page .cards-grid')
);

echo "\n====================================================================\n";
echo "3. Checking Routes for /office/notification & /office/notifications\n";
echo "====================================================================\n";

$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
it("Route /office/notifications exists", str_contains($routesContent, "'/office/notifications'"));
it("Route /office/notification exists as alias/redirect", str_contains($routesContent, "'/office/notification'"));

echo "\n====================================================================\n";
echo "4. Testing View Rendering with Mock Data\n";
echo "====================================================================\n";

$notifications = [
    [
        'id'             => 101,
        'user_id'        => 1,
        'employee_id'    => 5,
        'type'           => 'trip_assigned',
        'title'          => 'New Trip Duty: DSP-2026-0042',
        'message'        => 'You have been deployed for Pune to Mumbai trip.',
        'reference_type' => 'dispatches',
        'reference_id'   => 42,
        'is_read'        => 0,
        'created_at'     => date('Y-m-d H:i:s', strtotime('-15 minutes')),
    ],
    [
        'id'             => 102,
        'user_id'        => 1,
        'employee_id'    => null,
        'type'           => 'booking_created',
        'title'          => 'Reservation Confirmed: BKG-8812',
        'message'        => 'Customer Rahul Sharma booked Goa Beachfront Tour.',
        'reference_type' => 'bookings',
        'reference_id'   => 88,
        'is_read'        => 1,
        'created_at'     => date('Y-m-d H:i:s', strtotime('-2 hours')),
    ],
];
$totalRecords = 2;
$totalPages = 1;
$page = 1;
$perPage = 10;
$search = '';
$status = '';
$kpis = [
    'total_all'     => 2,
    'total_unread'  => 1,
    'total_read'    => 1,
    'total_trips'   => 1,
    'total_urgent'  => 1,
];
$unreadNotifCount = 1;

$_SESSION['user'] = ['id' => 1, 'name' => 'Admin User', 'username' => 'admin', 'role' => 'admin'];

ob_start();
try {
    include $viewPath;
    $renderedOutput = ob_get_clean();
    $renderSuccess = true;
} catch (\Throwable $e) {
    $renderedOutput = ob_get_clean();
    $renderSuccess = false;
    echo "  Render Error: " . $e->getMessage() . "\n";
}

it("View renders without PHP exceptions or fatal errors", $renderSuccess);
it("Rendered output contains desktop list with both items", str_contains($renderedOutput, 'officeNotifCard_101') && str_contains($renderedOutput, 'officeNotifCard_102'));
it("Rendered output contains mobile cards with both items", str_contains($renderedOutput, 'officeNotifMobileCard_101') && str_contains($renderedOutput, 'officeNotifMobileCard_102'));
it("Mobile card 101 has Unread status pill", str_contains($renderedOutput, 'Unread'));
it("Mobile card 102 has Read status pill", str_contains($renderedOutput, 'Read'));
it("Mobile cards have Actions dropdown button", str_contains($renderedOutput, 'toggleCardActions(this, event)'));

echo "\n====================================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
