<?php
/**
 * test_global_table_responsive_system.php
 *
 * Automated verification suite for Global Professional Table & Responsive Layout Improvement:
 * 1. Global Table Standard & Single-Line Values across tables
 * 2. Compact, consistent table row height (48px)
 * 3. Ellipsis text truncation and tooltip system
 * 4. Desktop responsive column distribution and header alignment
 * 5. Full-width content & table expansion on sidebar collapse (no blank void)
 * 6. Smooth sidebar transition & dynamic ResizeObserver / event dispatch
 * 7. Mobile controlled horizontal scrolling with sticky Actions column
 * 8. Portaled action dropdown menu (no container clipping)
 * 9. Exact agreed 8-column Bookings table architecture (No Booking ID column)
 * 10. PHP syntax and regression safety
 */

$rootDir = dirname(__DIR__);
$passes = 0;
$failures = 0;

function assertTest(bool $condition, string $label, string $details = '') {
    global $passes, $failures;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passes++;
    } else {
        echo "  [FAIL] {$label}" . ($details ? " -> {$details}" : "") . "\n";
        $failures++;
    }
}

echo "\n=======================================================\n";
echo "1. VERIFYING GLOBAL CSS LAYOUT & SIDEBAR EXPANSION\n";
echo "=======================================================\n";

$appCssPath = $rootDir . '/public/assets/css/app.css';
assertTest(file_exists($appCssPath), "app.css exists");
$appCss = file_get_contents($appCssPath);

// Verify .main-content does not have fixed 1680px constraint with centering margin
assertTest(strpos($appCss, 'max-width: 1680px') === false, "Fixed 1680px max-width removed from .main-content");
assertTest(preg_match('/\.main-content\s*\{[^}]*width:\s*100%/i', $appCss) === 1, ".main-content uses flexible width: 100%");
assertTest(preg_match('/\.main-content\s*\{[^}]*margin:\s*0/i', $appCss) === 1, ".main-content uses margin: 0 (no large blank gap between sidebar and content)");

// Verify seamless full-width content expansion when sidebar is collapsed
assertTest(strpos($appCss, '.sidebar.collapsed ~ .main-wrapper') !== false, "CSS defines fluid expansion when sidebar is collapsed");
assertTest(strpos($appCss, 'body.sidebar-collapsed .main-wrapper') !== false, "CSS supports body.sidebar-collapsed selector");

echo "\n=======================================================\n";
echo "2. VERIFYING SINGLE-LINE TABLE VALUES & COMPACT ROW HEIGHT\n";
echo "=======================================================\n";

// Single-line table values: white-space: nowrap !important
assertTest(preg_match('/\.modern-table\s+th,\s*\.modern-table\s+td\s*\{[^}]*white-space:\s*nowrap\s*!important/i', $appCss) === 1, "Table th and td enforce white-space: nowrap !important for single-line values");

// Compact row height (48px)
assertTest(preg_match('/\.modern-table\s+tbody\s+tr\s*\{[^}]*height:\s*48px/i', $appCss) === 1, "Table rows enforce compact 48px height");
assertTest(preg_match('/\.modern-table\s+tbody\s+td\s*\{[^}]*height:\s*48px/i', $appCss) === 1, "Table cells enforce compact 48px height");

// Text truncation with ellipsis
assertTest(strpos($appCss, '.table-cell-truncate') !== false, "CSS defines .table-cell-truncate");
assertTest(preg_match('/\.table-cell-truncate[^{]*\{[^}]*text-overflow:\s*ellipsis/i', $appCss) === 1, ".table-cell-truncate uses text-overflow: ellipsis");
assertTest(preg_match('/\.table-cell-truncate[^{]*\{[^}]*overflow:\s*hidden/i', $appCss) === 1, ".table-cell-truncate uses overflow: hidden");

echo "\n=======================================================\n";
echo "3. VERIFYING COLUMN ALIGNMENT & RESPONSIVE WIDTHS\n";
echo "=======================================================\n";

// Alignment checks
assertTest(preg_match('/\.modern-table\s+(?:th|td)\.table-col-amount[^{]*\{[^}]*text-align:\s*right\s*!important/i', $appCss) === 1, "Amount column is strictly right-aligned with !important");
assertTest(preg_match('/\.modern-table\s+(?:th|td)\.table-col-status[^{]*\{[^}]*text-align:\s*center\s*!important/i', $appCss) === 1, "Status column is strictly center-aligned with !important");
assertTest(preg_match('/\.modern-table\s+(?:th|td)\.table-col-index[^{]*\{[^}]*text-align:\s*center\s*!important/i', $appCss) === 1, "Index column is strictly center-aligned with !important");
assertTest(preg_match('/\.modern-table\s+(?:th|td)\.table-col-actions[^{]*\{[^}]*text-align:\s*right\s*!important/i', $appCss) === 1, "Actions column is strictly right-aligned with !important");

// Route columns (From & To)
assertTest(strpos($appCss, 'table-col-from') !== false, "CSS defines .table-col-from");
assertTest(strpos($appCss, 'table-col-to') !== false, "CSS defines .table-col-to");
assertTest(strpos($appCss, 'table-col-route') !== false, "CSS defines .table-col-route");

// Actions column zero clipping
assertTest(preg_match('/\.modern-table\s+(?:th|td)\.table-col-actions[^{]*\{[^}]*overflow:\s*visible\s*!important/i', $appCss) === 1, "Actions column has overflow: visible !important to prevent menu clipping");

echo "\n=======================================================\n";
echo "4. VERIFYING TOOLTIP ENGINE & ACCESSIBILITY STYLING\n";
echo "=======================================================\n";

assertTest(strpos($appCss, '.app-table-tooltip') !== false, "CSS defines .app-table-tooltip styling");
assertTest(strpos($appCss, '.app-table-tooltip.is-visible') !== false, "CSS defines .app-table-tooltip.is-visible transition");
assertTest(strpos($appCss, '.app-table-tooltip-arrow') !== false, "CSS defines tooltip directional arrow");

echo "\n=======================================================\n";
echo "5. VERIFYING MOBILE CONTROLLED HORIZONTAL SCROLLING\n";
echo "=======================================================\n";

assertTest(preg_match('/\.modern-table-container[^{]*\{[^}]*overflow-x:\s*auto\s*!important/i', $appCss) === 1, "Table container uses overflow-x: auto !important for controlled mobile scrolling");
assertTest(preg_match('/@media\s*\([^)]*max-width:\s*991\.98px[^)]*\)\s*\{[^}]*\.modern-table-container[^{]*\{[^}]*display:\s*block\s*!important/is', $appCss) === 1, "Table container remains visible on mobile/tablet (not hidden)");
assertTest(preg_match('/@media\s*\([^)]*max-width:\s*991\.98px[^)]*\)\s*\{[\s\S]*?\.modern-table\s*\{[^}]*min-width:\s*680px/is', $appCss) === 1, "Table enforces minimum readable width on mobile (680px) to prevent squishing");
assertTest(strpos($appCss, 'position: sticky;') !== false, "Actions column stays sticky on mobile horizontal scroll");

echo "\n=======================================================\n";
echo "6. VERIFYING JAVASCRIPT SIDEBAR ENGINE & TOOLTIPS\n";
echo "=======================================================\n";

$appJsPath = $rootDir . '/public/assets/js/app.js';
assertTest(file_exists($appJsPath), "app.js exists");
$appJs = file_get_contents($appJsPath);

// Verify sidebar toggle syncs body.sidebar-collapsed
assertTest(strpos($appJs, "document.body.classList.toggle('sidebar-collapsed', isCollapsed)") !== false, "toggleSidebarCollapse syncs body.sidebar-collapsed");
assertTest(strpos($appJs, "window.dispatchEvent(new Event('resize'))") !== false, "toggleSidebarCollapse dispatches resize event");
assertTest(strpos($appJs, "window.recalculateTableLayouts()") !== false, "toggleSidebarCollapse calls recalculateTableLayouts");

// Verify initSidebarState restores body.sidebar-collapsed
assertTest(strpos($appJs, "document.body.classList.add('sidebar-collapsed')") !== false, "initSidebarState restores body.sidebar-collapsed");

// Verify tooltips engine
assertTest(strpos($appJs, "initGlobalTableTooltips") !== false, "app.js defines initGlobalTableTooltips");
assertTest(strpos($appJs, "showTableTooltip") !== false, "app.js defines showTableTooltip");
assertTest(strpos($appJs, "isElementTruncated") !== false, "app.js defines isElementTruncated checking scrollWidth > clientWidth");
assertTest(strpos($appJs, "initTableResizeObserver") !== false, "app.js defines initTableResizeObserver");

echo "\n=======================================================\n";
echo "7. VERIFYING EXACT 8-COLUMN BOOKING TABLE ARCHITECTURE\n";
echo "=======================================================\n";

$bookingsPath = $rootDir . '/app/Views/office/bookings/index.php';
assertTest(file_exists($bookingsPath), "bookings/index.php exists");
$bookingsHtml = file_get_contents($bookingsPath);

// Check for the 8 agreed columns in the table header
assertTest(strpos($bookingsHtml, '<th class="col-priority-1 table-col-index text-center" data-column-priority="primary">#</th>') !== false, "Booking table has # column");
assertTest(strpos($bookingsHtml, '<th class="col-priority-1 table-col-name" data-column-priority="primary">Customer</th>') !== false, "Booking table has Customer column");
assertTest(strpos($bookingsHtml, '<th class="col-priority-2 table-col-schedule" data-column-priority="secondary">Travel Dates</th>') !== false, "Booking table has Travel Dates column");
assertTest(strpos($bookingsHtml, '<th class="col-priority-2 table-col-route table-col-from" data-column-priority="secondary">From</th>') !== false, "Booking table has From column");
assertTest(strpos($bookingsHtml, '<th class="col-priority-2 table-col-route table-col-to" data-column-priority="secondary">To</th>') !== false, "Booking table has To column");
assertTest(strpos($bookingsHtml, '<th class="col-priority-1 table-col-amount text-right" data-column-priority="primary" style="text-align: right;">Total Fare</th>') !== false, "Booking table has Total Fare column (right-aligned)");
assertTest(strpos($bookingsHtml, '<th class="col-priority-1 table-col-status text-center" data-column-priority="primary" style="text-align: center;">Booking Status</th>') !== false, "Booking table has Booking Status column (centered)");
assertTest(strpos($bookingsHtml, '<th class="col-priority-1 table-col-actions text-right" data-column-priority="primary" style="text-align: right;">Actions</th>') !== false, "Booking table has Actions column (right-aligned)");

// Verify Booking ID is NOT a visible listing column
assertTest(strpos($bookingsHtml, '<th>Booking</th>') === false && strpos($bookingsHtml, '<th>Booking ID</th>') === false && strpos($bookingsHtml, '<th>Ref</th>') === false, "Booking ID column is NOT in the listing table");

// Verify From and To use pickup_address and drop_address
assertTest(strpos($bookingsHtml, '$b[\'pickup_address\']') !== false, "Booking row displays pickup_address in From column");
assertTest(strpos($bookingsHtml, '$b[\'drop_address\']') !== false, "Booking row displays drop_address in To column");

// Verify Single Action Menu with Three Dots
assertTest(strpos($bookingsHtml, 'class="action-toggle-btn"') !== false, "Booking row uses single Actions menu toggle");
assertTest(strpos($bookingsHtml, 'View Booking') !== false, "Actions menu contains View Booking");
assertTest(strpos($bookingsHtml, 'Edit Booking') !== false, "Actions menu contains Edit Booking");
assertTest(strpos($bookingsHtml, 'Delete Booking') !== false, "Actions menu contains Delete Booking");

echo "\n=======================================================\n";
echo "8. RUNNING PHP SYNTAX LINT CHECK ON MODIFIED FILES\n";
echo "=======================================================\n";

$filesToLint = [
    'app/Views/office/bookings/index.php',
    'app/Views/layouts/office.php',
    'app/Views/office/customers/index.php',
    'app/Views/office/trips/history.php',
    'app/Views/office/vehicles/index.php',
    'app/Views/office/invoices/index.php'
];

foreach ($filesToLint as $rel) {
    $full = $rootDir . '/' . $rel;
    $cmd = "\"E:\\xampp\\php\\php.exe\" -l " . escapeshellarg($full) . " 2>&1";
    exec($cmd, $out, $ret);
    assertTest($ret === 0, "PHP syntax valid: {$rel}");
}

echo "\n-------------------------------------------------------\n";
echo "TEST RESULTS: Total Passes = {$passes}, Total Failures = {$failures}\n";
echo "-------------------------------------------------------\n";

exit($failures > 0 ? 1 : 0);
