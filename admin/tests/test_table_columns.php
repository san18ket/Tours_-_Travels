<?php
/**
 * test_table_columns.php
 *
 * Automated verification suite for Global Professional Table Columns:
 * - CSS system in public/assets/css/app.css
 * - Column classes (table-col-index, table-col-code, table-col-name, table-col-amount, etc.)
 * - Concise, mixed-case headers (no forced uppercase)
 * - Right-aligned amounts and currency
 * - Reference implementation: app/Views/office/trips/history.php
 * - Global implementation across all key Office table views
 * - PHP syntax validity across all modified views
 */

$rootDir = dirname(__DIR__);
$pass = 0;
$fail = 0;

function assert_true($cond, $label) {
    global $pass, $fail;
    if ($cond) {
        echo "  [PASS] {$label}\n";
        $pass++;
    } else {
        echo "  [FAIL] {$label}\n";
        $fail++;
    }
}

echo "\n=======================================================\n";
echo "1. VERIFYING GLOBAL CSS ARCHITECTURE IN app.css\n";
echo "=======================================================\n";

$cssPath = $rootDir . '/public/assets/css/app.css';
assert_true(file_exists($cssPath), "app.css exists");
$css = file_get_contents($cssPath);

// Verify uppercase text-transform was removed/overridden from th
assert_true(strpos($css, 'text-transform: none;') !== false, "Table header text-transform set to none (clean mixed-case readable headers)");

// Verify semantic column classes
$expectedClasses = [
    'table-col-index',
    'table-col-code',
    'table-col-name',
    'table-col-person',
    'table-col-vehicle',
    'table-col-amount',
    'table-col-odometer',
    'table-col-schedule',
    'table-col-status',
    'table-col-actions',
    'table-cell-group',
    'table-cell-primary',
    'table-cell-secondary',
    'table-cell-amount'
];

foreach ($expectedClasses as $cls) {
    assert_true(strpos($css, '.' . $cls) !== false, "CSS contains definition for .{$cls}");
}

// Verify numeric alignment
assert_true(preg_match('/\.table-col-amount[^{]*\{[^}]*text-align:\s*right/i', $css), "table-col-amount aligns text right");
assert_true(preg_match('/\.table-col-actions[^{]*\{[^}]*width:\s*110px/i', $css), "table-col-actions sets compact width 110px");


echo "\n=======================================================\n";
echo "2. VERIFYING REFERENCE IMPLEMENTATION: trips/history.php\n";
echo "=======================================================\n";

$tripHistPath = $rootDir . '/app/Views/office/trips/history.php';
assert_true(file_exists($tripHistPath), "trips/history.php exists");
$tripHist = file_get_contents($tripHistPath);

// Verify concise headers
assert_true(strpos($tripHist, 'Dispatch / Booking') !== false, "Header has concise 'Dispatch / Booking'");
assert_true(strpos($tripHist, 'Passenger') !== false, "Header has concise 'Passenger'");
assert_true(strpos($tripHist, 'Driver') !== false, "Header has concise 'Driver'");
assert_true(strpos($tripHist, 'Vehicle') !== false, "Header has concise 'Vehicle'");
assert_true(strpos($tripHist, 'Driver Payout') !== false, "Header has 'Driver Payout'");
assert_true(strpos($tripHist, 'Odometer') !== false, "Header has concise 'Odometer'");
assert_true(strpos($tripHist, 'Status') !== false, "Header has concise 'Status'");
assert_true(strpos($tripHist, 'Actions') !== false, "Header has concise 'Actions'");

// Verify no old loud headers remain
assert_true(strpos($tripHist, 'DISPATCH & BOOKING') === false, "Old uppercase 'DISPATCH & BOOKING' header removed");
assert_true(strpos($tripHist, 'CHAUFFEUR & PHONE') === false, "Old uppercase 'CHAUFFEUR & PHONE' header removed");
assert_true(strpos($tripHist, 'ODOMETER START / END') === false, "Old uppercase 'ODOMETER START / END' header removed");

// Verify 2-line cell hierarchy
assert_true(strpos($tripHist, 'table-cell-group') !== false, "Uses .table-cell-group in cell hierarchy");
assert_true(strpos($tripHist, 'table-cell-primary') !== false, "Uses .table-cell-primary for prominent information");
assert_true(strpos($tripHist, 'table-cell-secondary') !== false, "Uses .table-cell-secondary for secondary meta");

// Verify driver payout right alignment
assert_true(strpos($tripHist, 'table-col-amount') !== false, "Uses .table-col-amount on Driver Payout");

// Verify mobile cards have distinct structured sections
assert_true(strpos($tripHist, 'mv-booking-card-wrapper') !== false, "Mobile cards have mv-booking-card-wrapper");
assert_true(strpos($tripHist, 'mv-booking-card') !== false, "Mobile cards have mv-booking-card");
assert_true(strpos($tripHist, 'mv-card-actions') !== false, "Mobile cards have mv-card-actions");


echo "\n=======================================================\n";
echo "3. VERIFYING GLOBAL OFFICE TABLE VIEWS\n";
echo "=======================================================\n";

$tablesToVerify = [
    'bookings/index.php' => ['table-col-index', 'table-col-code', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'customers/index.php' => ['table-col-index', 'table-col-name', 'table-col-person', 'table-col-status', 'table-col-actions'],
    'employees/index.php' => ['table-col-index', 'table-col-name', 'table-col-person', 'table-col-status', 'table-col-actions'],
    'vehicles/index.php' => ['table-col-index', 'table-col-code', 'table-col-name', 'table-col-status', 'table-col-actions'],
    'invoices/index.php' => ['table-col-index', 'table-col-code', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'dispatch/index.php' => ['table-col-code', 'table-col-name', 'table-col-driver', 'table-col-vehicle', 'table-col-status', 'table-col-actions'],
    'finance/driver_payments.php' => ['table-col-index', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/expenses.php' => ['table-col-code', 'table-col-date', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/transactions.php' => ['table-col-index', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/advances.php' => ['table-col-index', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/refunds.php' => ['table-col-code', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/accounts.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/pending_payments.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/expense_categories.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/payroll.php' => ['table-col-code', 'table-col-schedule', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/payslips.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'finance/payments.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-actions'],
    'tours/index.php' => ['table-col-index', 'table-col-name', 'table-col-amount', 'table-col-status', 'table-col-actions'],
    'trip_categories/index.php' => ['table-col-index', 'table-col-name', 'table-col-code', 'table-col-status', 'table-col-actions'],
    'users/index.php' => ['table-col-index', 'table-col-name', 'table-col-status', 'table-col-actions'],
    'roles/index.php' => ['table-col-index', 'table-col-name', 'table-col-status', 'table-col-actions'],
    'emails/index.php' => ['table-col-index', 'table-col-name', 'table-col-status', 'table-col-actions'],
    'emails/history.php' => ['table-col-index', 'table-col-name', 'table-col-status', 'table-col-actions'],
    'fleet/index.php' => ['table-col-index', 'table-col-code', 'table-col-name', 'table-col-status'],
];

foreach ($tablesToVerify as $relPath => $classes) {
    $fullPath = $rootDir . '/app/Views/office/' . $relPath;
    assert_true(file_exists($fullPath), "View exists: {$relPath}");
    $content = file_get_contents($fullPath);
    foreach ($classes as $cls) {
        assert_true(strpos($content, $cls) !== false, "{$relPath} uses class .{$cls}");
    }
}


echo "\n=======================================================\n";
echo "4. RUNNING PHP SYNTAX LINT CHECK ON MODIFIED FILES\n";
echo "=======================================================\n";

$filesToLint = [
    'app/Models/Dispatch.php',
    'app/Views/office/trips/history.php',
    'app/Views/office/bookings/index.php',
    'app/Views/office/customers/index.php',
    'app/Views/office/employees/index.php',
    'app/Views/office/vehicles/index.php',
    'app/Views/office/invoices/index.php',
    'app/Views/office/dispatch/index.php',
    'app/Views/office/finance/driver_payments.php',
    'app/Views/office/finance/expenses.php',
    'app/Views/office/finance/transactions.php',
    'app/Views/office/finance/advances.php',
    'app/Views/office/finance/refunds.php',
    'app/Views/office/finance/accounts.php',
    'app/Views/office/finance/pending_payments.php',
    'app/Views/office/finance/expense_categories.php',
    'app/Views/office/finance/payroll.php',
    'app/Views/office/finance/payslips.php',
    'app/Views/office/finance/payments.php',
    'app/Views/office/tours/index.php',
    'app/Views/office/trip_categories/index.php',
    'app/Views/office/users/index.php',
    'app/Views/office/roles/index.php',
    'app/Views/office/emails/index.php',
    'app/Views/office/emails/history.php',
    'app/Views/office/fleet/index.php',
    'app/Views/office/backups/index.php'
];

foreach ($filesToLint as $file) {
    $full = $rootDir . '/' . $file;
    $cmd = "E:\\xampp\\php\\php.exe -l " . escapeshellarg($full);
    exec($cmd, $out, $returnCode);
    assert_true($returnCode === 0, "PHP syntax lint passed: {$file}");
}

echo "\n-------------------------------------------------------\n";
echo "TEST RESULTS: Total Passes = {$pass}, Total Fails = {$fail}\n";
echo "-------------------------------------------------------\n";

exit($fail > 0 ? 1 : 0);
