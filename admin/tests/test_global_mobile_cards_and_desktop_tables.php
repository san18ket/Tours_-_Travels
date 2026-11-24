<?php
/**
 * Test Suite: Global Mobile Cards + Desktop Tables Responsive System
 * 
 * Verifies:
 * 1. Desktop devices (>= 769px / 1024px+): Existing Tables ONLY, Cards COMPLETELY HIDDEN.
 * 2. Mobile devices (<= 768px): Cards ONLY, Tables COMPLETELY HIDDEN.
 * 3. Never both visible, never both hidden.
 * 4. Mobile Card Actions appear in ONE horizontal row on a single line with overflow-x scrolling.
 * 5. Desktop Action column / [ ⋮ Actions ] dropdown menu is preserved.
 * 6. Mobile cards exist across all data/listing views.
 * 7. PHP syntax validity across all modified views.
 */

$rootDir = dirname(__DIR__);
$passCount = 0;
$failCount = 0;

function assertCondition(string $name, bool $expr, string $extra = '') {
    global $passCount, $failCount;
    if ($expr) {
        $passCount++;
        echo "  [PASS] {$name}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$name}" . ($extra ? " -- {$extra}" : '') . "\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING RESPONSIVE VISIBILITY IN CSS (app.css & responsive.css)\n";
echo "=======================================================\n";

$appCssPath = $rootDir . '/public/assets/css/app.css';
assertCondition("app.css exists", file_exists($appCssPath));
$appCss = file_get_contents($appCssPath);

$responsiveCssPath = $rootDir . '/public/assets/css/responsive.css';
assertCondition("responsive.css exists", file_exists($responsiveCssPath));
$responsiveCss = file_get_contents($responsiveCssPath);

// Desktop: Cards hidden
assertCondition("app.css hides cards-grid on desktop (min-width: 769px)", 
    preg_match('/@media\s*\(\s*min-width:\s*769px\s*\)[\s\S]*?\.cards-grid[\s\S]*?display:\s*none\s*!important/i', $appCss) === 1
);

assertCondition("responsive.css hides cards-grid on desktop (min-width: 769px)", 
    preg_match('/@media\s*\(\s*min-width:\s*769px\s*\)[\s\S]*?\.cards-grid[\s\S]*?display:\s*none\s*!important/i', $responsiveCss) === 1
);

// Mobile: Tables hidden, cards visible
assertCondition("app.css hides tables on mobile (max-width: 768px)", 
    preg_match('/@media\s*\(\s*max-width:\s*768px\s*\)[\s\S]*?\.modern-table-container[\s\S]*?display:\s*none\s*!important/i', $appCss) === 1
);

assertCondition("app.css shows cards on mobile (max-width: 768px)", 
    preg_match('/@media\s*\(\s*max-width:\s*768px\s*\)[\s\S]*?\.cards-grid[\s\S]*?display:\s*flex\s*!important/i', $appCss) === 1
);

assertCondition("responsive.css hides tables on mobile (max-width: 768px)", 
    preg_match('/@media\s*\(\s*max-width:\s*768px\s*\)[\s\S]*?\.modern-table-container[\s\S]*?display:\s*none\s*!important/i', $responsiveCss) === 1
);

assertCondition("responsive.css shows cards on mobile (max-width: 768px)", 
    preg_match('/@media\s*\(\s*max-width:\s*768px\s*\)[\s\S]*?\.cards-grid[\s\S]*?display:\s*flex\s*!important/i', $responsiveCss) === 1
);

// Bookings specific mobile override
assertCondition("bookings-page-view hides modern-table-container on mobile",
    preg_match('/\.bookings-page-view\s+\.modern-table-container\s*\{[^}]*display:\s*none\s*!important/i', $appCss) === 1
);

assertCondition("bookings-page-view shows cards-grid on mobile",
    preg_match('/\.bookings-page-view\s+\.cards-grid\s*\{[^}]*display:\s*flex\s*!important/i', $appCss) === 1
);

echo "\n=======================================================\n";
echo "2. VERIFYING MOBILE ACTIONS: ONE HORIZONTAL ROW\n";
echo "=======================================================\n";

// Action toggle button removed/hidden on mobile cards
assertCondition("Action toggle button is hidden on mobile cards", 
    preg_match('/\.mv-card-actions\s+\.action-toggle-btn[^{]*\{[^}]*display:\s*none\s*!important/i', $appCss) === 1
);

// Actions dropdown menu transformed into horizontal flex row
assertCondition("Action items displayed as horizontal flex row", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-menu[^{]*\{[^}]*display:\s*flex\s*!important[^}]*flex-direction:\s*row\s*!important/i', $appCss) === 1
);

// Horizontal scrolling container with nowrap
assertCondition("Mobile card actions container enables horizontal scroll without wrapping", 
    preg_match('/\.mv-card-actions[^{]*\{[\s\S]*?overflow-x:\s*auto\s*!important[\s\S]*?white-space:\s*nowrap\s*!important/i', $appCss) === 1
);

// Action items keep single-line, touch-friendly height and flex-shrink: 0
assertCondition("Action items prevent squishing with flex-shrink: 0 and touch target 34px", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-item[\s\S]*?height:\s*34px\s*!important/i', $appCss) === 1 &&
    preg_match('/\.mv-card-actions\s+\.action-dropdown-item[\s\S]*?flex-shrink:\s*0\s*!important/i', $appCss) === 1
);

// Dividers hidden in horizontal layout
assertCondition("Dropdown dividers hidden in horizontal card action row", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-divider[^{]*\{[^}]*display:\s*none\s*!important/i', $appCss) === 1
);

echo "\n=======================================================\n";
echo "3. VERIFYING DESKTOP ACTIONS PRESERVATION\n";
echo "=======================================================\n";

// Desktop table retains single action dropdown
assertCondition("Desktop table actions column retains compact width", 
    strpos($appCss, '.table-col-actions') !== false
);

$appJs = file_get_contents($rootDir . '/public/assets/js/app.js');
assertCondition("OfficeActionMenu floating portal engine is preserved", 
    strpos($appJs, 'window.OfficeActionMenu') !== false
);

assertCondition("toggleActions trigger handler is preserved in app.js", 
    strpos($appJs, 'window.toggleActions') !== false
);

echo "\n=======================================================\n";
echo "4. VERIFYING MOBILE CARDS PRESENCE ACROSS ALL DATA VIEWS\n";
echo "=======================================================\n";

$dataViews = [
    'bookings/index.php'            => 'Bookings',
    'customers/index.php'           => 'Customers',
    'employees/index.php'           => 'Employees',
    'vehicles/index.php'            => 'Vehicles',
    'trips/history.php'             => 'Trips History',
    'invoices/index.php'            => 'Invoices',
    'dispatch/index.php'            => 'Dispatch',
    'fleet/index.php'               => 'Fleet',
    'tours/index.php'               => 'Tours',
    'trip_categories/index.php'     => 'Trip Categories',
    'users/index.php'               => 'Users',
    'roles/index.php'               => 'Roles',
    'emails/index.php'              => 'Emails',
    'emails/history.php'            => 'Emails History',
    'backups/index.php'             => 'Backups',
    'finance/accounts.php'          => 'Finance Accounts',
    'finance/advances.php'          => 'Finance Advances',
    'finance/driver_payments.php'   => 'Finance Driver Payments',
    'finance/expenses.php'          => 'Finance Expenses',
    'finance/expense_categories.php'=> 'Finance Expense Categories',
    'finance/payments.php'          => 'Finance Payments',
    'finance/payroll.php'           => 'Finance Payroll',
    'finance/payslips.php'          => 'Finance Payslips',
    'finance/pending_payments.php'  => 'Finance Pending Payments',
    'finance/refunds.php'           => 'Finance Refunds',
    'finance/transactions.php'      => 'Finance Transactions'
];

foreach ($dataViews as $relPath => $viewName) {
    $fullPath = $rootDir . '/app/Views/office/' . $relPath;
    assertCondition("View exists: {$viewName}", file_exists($fullPath));
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $hasCards = (strpos($content, 'cards-grid') !== false) || 
                    (strpos($content, 'mobile-cards-container') !== false) || 
                    (strpos($content, 'mobile-email-cards') !== false) ||
                    (strpos($content, 'mv-booking-card-wrapper') !== false);
        assertCondition("{$viewName} has mobile card container", $hasCards);

        if ($relPath !== 'fleet/index.php') {
            $hasCardActions = (strpos($content, 'mv-card-actions') !== false) || 
                              (strpos($content, 'mv-card-actions-bar') !== false) ||
                              (strpos($content, 'action-dropdown-wrap') !== false);
            assertCondition("{$viewName} has mobile action area", $hasCardActions);
        }
    }
}

echo "\n=======================================================\n";
echo "5. PHP SYNTAX LINT CHECK ON MODIFIED VIEWS\n";
echo "=======================================================\n";

$filesToLint = [
    'app/Views/office/emails/index.php',
    'app/Views/office/emails/history.php',
    'app/Views/office/finance/transactions.php',
    'app/Views/office/employees/index.php',
    'app/Views/office/bookings/index.php',
    'app/Views/office/customers/index.php'
];

foreach ($filesToLint as $f) {
    $path = $rootDir . '/' . $f;
    $output = [];
    $code = 0;
    exec("E:\\xampp\\php\\php.exe -l \"{$path}\" 2>&1", $output, $code);
    assertCondition("PHP syntax valid: {$f}", $code === 0, implode(' ', $output));
}

echo "\n-------------------------------------------------------\n";
echo "TEST RESULTS: Total Passes = {$passCount}, Total Failures = {$failCount}\n";
echo "-------------------------------------------------------\n";

exit($failCount === 0 ? 0 : 1);
