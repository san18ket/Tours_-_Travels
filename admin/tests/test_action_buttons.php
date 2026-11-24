<?php
/**
 * Test Suite: Global Table Actions — One Professional Actions Menu
 * Verifies that tables across the entire Office panel follow the single Actions menu standard:
 * - One row -> one Actions button -> one menu -> all record actions
 * - Single Actions trigger: [ ⋮ Actions ]
 * - Standardized Actions column width (110px, min-width: 100px)
 * - Zero standalone row buttons (no separate [ View ] [ Edit ] cluttering rows)
 * - All record-level actions reside inside the single dropdown menu
 * - Mobile cards also adopt the single Actions menu
 * - Valid PHP syntax on all 27 modified views
 */

$rootDir = dirname(__DIR__);
$failures = [];
$passes = 0;

function assertCondition($name, $condition, $details = '') {
    global $failures, $passes;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passes++;
    } else {
        echo "  [FAIL] {$name}" . ($details ? ": {$details}" : "") . "\n";
        $failures[] = "{$name}" . ($details ? ": {$details}" : "");
    }
}

echo "=== 1. Verifying Shared CSS & Dropdown Standards ===\n";

$appCssPath = $rootDir . '/public/assets/css/app.css';
$appCss = file_get_contents($appCssPath);

assertCondition('app.css defines .action-dropdown-wrap', strpos($appCss, '.action-dropdown-wrap') !== false);
assertCondition('app.css defines .action-toggle-btn with text support', strpos($appCss, '.action-toggle-btn') !== false && strpos($appCss, 'min-width: 30px') !== false);
assertCondition('app.css defines .action-dropdown-menu', strpos($appCss, '.action-dropdown-menu') !== false);
assertCondition('app.css defines .action-dropdown-item', strpos($appCss, '.action-dropdown-item') !== false);
assertCondition('app.css defines .action-dropdown-divider', strpos($appCss, '.action-dropdown-divider') !== false);

$respCssPath = $rootDir . '/public/assets/css/responsive.css';
$respCss = file_get_contents($respCssPath);
assertCondition('responsive.css exists and loads correctly', strlen($respCss) > 0);

$appJsPath = $rootDir . '/public/assets/js/app.js';
$appJs = file_get_contents($appJsPath);
assertCondition('app.js defines OfficeActionMenu floating portal engine', strpos($appJs, 'OfficeActionMenu') !== false);
assertCondition('app.js defines toggleActions handler', strpos($appJs, 'window.toggleActions =') !== false || strpos($appJs, 'function toggleActions(') !== false);

echo "\n=== 2. Verifying Office Views PHP Syntax Validity ===\n";

$viewsToLint = [
    'app/Views/office/customers/index.php',
    'app/Views/office/bookings/index.php',
    'app/Views/office/employees/index.php',
    'app/Views/office/vehicles/index.php',
    'app/Views/office/invoices/index.php',
    'app/Views/office/emails/index.php',
    'app/Views/office/emails/history.php',
    'app/Views/office/company/index.php',
    'app/Views/office/backups/index.php',
    'app/Views/office/trip_categories/index.php',
    'app/Views/office/trips/history.php',
    'app/Views/office/tours/index.php',
    'app/Views/office/users/index.php',
    'app/Views/office/roles/index.php',
    'app/Views/office/role_permissions/index.php',
    'app/Views/office/dispatch/index.php',
    'app/Views/office/finance/accounts.php',
    'app/Views/office/finance/payments.php',
    'app/Views/office/finance/pending_payments.php',
    'app/Views/office/finance/expenses.php',
    'app/Views/office/finance/expense_categories.php',
    'app/Views/office/finance/driver_payments.php',
    'app/Views/office/finance/refunds.php',
    'app/Views/office/finance/advances.php',
    'app/Views/office/finance/transactions.php',
    'app/Views/office/finance/payroll.php',
    'app/Views/office/finance/payslips.php'
];

foreach ($viewsToLint as $viewRel) {
    $fullPath = $rootDir . '/' . $viewRel;
    assertCondition("File exists: {$viewRel}", file_exists($fullPath));
    $output = [];
    $returnVar = 0;
    $phpBin = defined('PHP_BINARY') && file_exists(PHP_BINARY) ? PHP_BINARY : 'E:\\xampp\\php\\php.exe';
    exec("\"{$phpBin}\" -l \"{$fullPath}\" 2>&1", $output, $returnVar);
    assertCondition("Syntax OK: {$viewRel}", $returnVar === 0, implode(' ', $output));
}

echo "\n=== 3. Verifying Absence of Multiple Row Action Clutter ===\n";

// Across all views, there should be ZERO standalone table-action-btn
$standaloneCheck = true;
foreach ($viewsToLint as $viewRel) {
    $fullPath = $rootDir . '/' . $viewRel;
    $content = file_get_contents($fullPath);
    // Ignore css blocks if any
    $contentNoStyles = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
    if (preg_match('/class=[\'"][^\'"]*table-action-btn[^\'"]*[\'"]/', $contentNoStyles, $m)) {
        assertCondition("No table-action-btn in {$viewRel}", false, "Found: " . $m[0]);
        $standaloneCheck = false;
    }
}
if ($standaloneCheck) {
    assertCondition("All 27 views: No standalone table-action-btn elements exist", true);
}

echo "\n=== 4. Verifying Single Actions Menu Implementation on Key Views ===\n";

// Helper to check for compact Actions column (either via semantic .table-col-actions or inline 110px)
function hasCompactActionsCol(string $content): bool {
    return strpos($content, 'table-col-actions') !== false || strpos($content, 'width: 110px; min-width: 100px;') !== false;
}

// 1. Customers
$cust = file_get_contents($rootDir . '/app/Views/office/customers/index.php');
assertCondition('Customers table has compact Actions column width (110px)', hasCompactActionsCol($cust));
assertCondition('Customers row uses single [ ⋮ Actions ] trigger', strpos($cust, 'class="action-toggle-btn"') !== false && strpos($cust, '<span>Actions</span>') !== false);
assertCondition('Customers dropdown contains View Profile', strpos($cust, 'View Profile') !== false);
assertCondition('Customers dropdown contains Edit Customer', strpos($cust, 'Edit Customer') !== false);
assertCondition('Customers dropdown contains New Booking', strpos($cust, 'New Booking') !== false);
assertCondition('Customers dropdown contains Delete Customer', strpos($cust, 'Delete Customer') !== false);
assertCondition('Customers mobile cards have single Actions menu', strpos($cust, 'id="customersCards"') !== false && strpos($cust, 'toggleCardActions(this, event)') !== false);

// 2. Bookings
$book = file_get_contents($rootDir . '/app/Views/office/bookings/index.php');
assertCondition('Bookings table has compact Actions column width (110px)', hasCompactActionsCol($book));
assertCondition('Bookings row uses single [ ⋮ Actions ] trigger', strpos($book, 'class="action-toggle-btn"') !== false);
assertCondition('Bookings dropdown contains View Booking', strpos($book, 'View Booking') !== false);
assertCondition('Bookings dropdown contains Edit Booking', strpos($book, 'Edit Booking') !== false);
assertCondition('Bookings dropdown contains Assign/Reassign Trip', strpos($book, 'Assign Trip') !== false || strpos($book, 'Reassign Trip') !== false);
assertCondition('Bookings dropdown contains Delete Booking', strpos($book, 'Delete Booking') !== false);

// 3. Employees
$emp = file_get_contents($rootDir . '/app/Views/office/employees/index.php');
assertCondition('Employees table has compact Actions column width (110px)', hasCompactActionsCol($emp));
assertCondition('Employees dropdown contains View Profile', strpos($emp, 'View Profile') !== false);
assertCondition('Employees dropdown contains Edit Employee', strpos($emp, 'Edit Employee') !== false);
assertCondition('Employees dropdown contains Delete Employee', strpos($emp, 'Delete Employee') !== false);

// 4. Vehicles
$veh = file_get_contents($rootDir . '/app/Views/office/vehicles/index.php');
assertCondition('Vehicles table has compact Actions column width (110px)', hasCompactActionsCol($veh));
assertCondition('Vehicles dropdown contains Assign Trip', strpos($veh, 'Assign Trip') !== false);
assertCondition('Vehicles dropdown contains Set Maintenance / Ready', strpos($veh, 'Set Maintenance') !== false || strpos($veh, 'Set Ready') !== false);
assertCondition('Vehicles dropdown contains Delete Vehicle', strpos($veh, 'Delete Vehicle') !== false);

// 5. Invoices
$inv = file_get_contents($rootDir . '/app/Views/office/invoices/index.php');
assertCondition('Invoices table has compact Actions column width (110px)', hasCompactActionsCol($inv));
assertCondition('Invoices dropdown contains View Invoice', strpos($inv, 'View Invoice') !== false);
assertCondition('Invoices dropdown contains Print Invoice', strpos($inv, 'Print Invoice') !== false);
assertCondition('Invoices dropdown contains Record Payment', strpos($inv, 'Record Payment') !== false);

// 6. Emails Center & History
$eml = file_get_contents($rootDir . '/app/Views/office/emails/index.php');
assertCondition('Emails index table has compact Actions column width (110px)', hasCompactActionsCol($eml));
assertCondition('Emails index dropdown contains View Details', strpos($eml, 'View Details') !== false);
assertCondition('Emails index dropdown contains Retry Sending', strpos($eml, 'Retry Sending') !== false);

$emlh = file_get_contents($rootDir . '/app/Views/office/emails/history.php');
assertCondition('Emails history table has compact Actions column width (110px)', hasCompactActionsCol($emlh));
assertCondition('Emails history dropdown contains View Details', strpos($emlh, 'View Details') !== false);
assertCondition('Emails history dropdown contains Retry Sending', strpos($emlh, 'Retry Sending') !== false);

// 7. Finance Views
$finRefunds = file_get_contents($rootDir . '/app/Views/office/finance/refunds.php');
assertCondition('Finance refunds has compact Actions column width (110px)', hasCompactActionsCol($finRefunds));
assertCondition('Finance refunds dropdown contains View Details', strpos($finRefunds, 'View Details') !== false);
assertCondition('Finance refunds dropdown contains Process Refund', strpos($finRefunds, 'Process Refund') !== false);

$finDriver = file_get_contents($rootDir . '/app/Views/office/finance/driver_payments.php');
assertCondition('Finance driver payments has compact Actions column width (110px)', hasCompactActionsCol($finDriver));
assertCondition('Finance driver payments dropdown contains Record Payment', strpos($finDriver, 'Record Payment') !== false);

$finAdvances = file_get_contents($rootDir . '/app/Views/office/finance/advances.php');
assertCondition('Finance advances has compact Actions column width (110px)', hasCompactActionsCol($finAdvances));
assertCondition('Finance advances dropdown contains View Details', strpos($finAdvances, 'View Details') !== false);
assertCondition('Finance advances dropdown contains Edit Advance', strpos($finAdvances, 'Edit Advance') !== false);
assertCondition('Finance advances dropdown contains Delete Advance', strpos($finAdvances, 'Delete Advance') !== false);

$finTx = file_get_contents($rootDir . '/app/Views/office/finance/transactions.php');
assertCondition('Finance transactions has compact Actions column width (110px)', hasCompactActionsCol($finTx));
assertCondition('Finance transactions dropdown contains View Voucher', strpos($finTx, 'View Voucher') !== false);
assertCondition('Finance transactions dropdown contains Edit Voucher', strpos($finTx, 'Edit Voucher') !== false);
assertCondition('Finance transactions dropdown contains Delete Voucher', strpos($finTx, 'Delete Voucher') !== false);

$finPayroll = file_get_contents($rootDir . '/app/Views/office/finance/payroll.php');
assertCondition('Finance payroll has compact Actions column width (110px)', hasCompactActionsCol($finPayroll));
assertCondition('Finance payroll dropdown contains View Payslips', strpos($finPayroll, 'View Payslips') !== false);

$finPayslips = file_get_contents($rootDir . '/app/Views/office/finance/payslips.php');
assertCondition('Finance payslips has compact Actions column width (110px)', hasCompactActionsCol($finPayslips));
assertCondition('Finance payslips dropdown contains Print Payslip', strpos($finPayslips, 'Print Payslip') !== false);
assertCondition('Finance payslips dropdown contains Financial Profile', strpos($finPayslips, 'Financial Profile') !== false);
assertCondition('Finance payslips dropdown contains Disburse Salary', strpos($finPayslips, 'Disburse Salary') !== false);

// 8. Roles & Trip Categories
$roles = file_get_contents($rootDir . '/app/Views/office/roles/index.php');
assertCondition('Roles dropdown contains Assign Permissions', strpos($roles, 'Assign Permissions') !== false);
assertCondition('Roles dropdown contains Edit Role', strpos($roles, 'Edit Role') !== false);
assertCondition('Roles dropdown contains Delete Role', strpos($roles, 'Delete Role') !== false);

$cats = file_get_contents($rootDir . '/app/Views/office/trip_categories/index.php');
assertCondition('Trip Categories dropdown contains Edit Category', strpos($cats, 'Edit Category') !== false);
assertCondition('Trip Categories dropdown contains Delete Category', strpos($cats, 'Delete Category') !== false);

echo "\n=== SUMMARY ===\n";
echo "Passes: {$passes}\n";
echo "Failures: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed items:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "All Single Actions Menu standardization checks passed successfully!\n";
exit(0);
