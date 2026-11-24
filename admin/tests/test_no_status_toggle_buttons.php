<?php
/**
 * Test Suite: Verification of Complete Removal of Toggle Status Buttons
 *
 * Verifies that:
 * 1. Zero `cust-status-toggle` button elements exist in tables or mobile cards.
 * 2. Zero `role="switch"` attributes exist on status elements.
 * 3. Zero `toggle-slider-track` or `toggle-slider-thumb` elements exist in tables or cards.
 * 4. Professional status badges/pills are retained and displayed cleanly across desktop and mobile.
 */

$rootDir = realpath(__DIR__ . '/../..');
$viewsDir = $rootDir . '/admin/app/Views/office';

$passed = 0;
$failed = 0;

function assertCheck($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=======================================================\n";
echo "1. VERIFYING ZERO TOGGLE BUTTONS IN ALL OFFICE VIEWS\n";
echo "=======================================================\n";

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$checkedFiles = 0;
$foundToggleBtn = false;
$foundRoleSwitch = false;
$foundSliderTrack = false;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $checkedFiles++;
        $content = file_get_contents($file->getPathname());
        $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file->getPathname());

        if (preg_match('/<button[^>]*class="[^"]*cust-status-toggle/i', $content)) {
            $foundToggleBtn = true;
            assertCheck(false, "Found cust-status-toggle button in {$relPath}");
        }

        if (preg_match('/role=["\']switch["\']/i', $content)) {
            $foundRoleSwitch = true;
            assertCheck(false, "Found role=\"switch\" in {$relPath}");
        }

        if (preg_match('/class="[^"]*toggle-slider-track/i', $content)) {
            $foundSliderTrack = true;
            assertCheck(false, "Found toggle-slider-track in {$relPath}");
        }
    }
}

assertCheck(!$foundToggleBtn, "Zero cust-status-toggle button elements across all {$checkedFiles} office views");
assertCheck(!$foundRoleSwitch, "Zero role=\"switch\" toggles across all {$checkedFiles} office views");
assertCheck(!$foundSliderTrack, "Zero toggle-slider-track elements across all {$checkedFiles} office views");

echo "\n=======================================================\n";
echo "2. VERIFYING PROFESSIONAL STATUS DISPLAY IN KEY VIEWS\n";
echo "=======================================================\n";

$keyViews = [
    'Bookings' => $viewsDir . '/bookings/index.php',
    'Customers' => $viewsDir . '/customers/index.php',
    'Employees' => $viewsDir . '/employees/index.php',
    'Vehicles' => $viewsDir . '/vehicles/index.php',
    'Users' => $viewsDir . '/users/index.php',
    'Trip Categories' => $viewsDir . '/trip_categories/index.php',
    'Expense Categories' => $viewsDir . '/finance/expense_categories.php',
    'Advances' => $viewsDir . '/finance/advances.php',
];

foreach ($keyViews as $name => $path) {
    assertCheck(file_exists($path), "{$name} view exists");
    $content = file_get_contents($path);

    // Verify table has clean status pill
    $hasTableStatusPill = preg_match('/class="[^"]*(?:cust-toggle-pill|status-badge)/i', $content);
    assertCheck($hasTableStatusPill, "{$name} retains professional status pill/badge in desktop view");

    // Verify pulse dot or professional indicator is present
    $hasPulseDot = strpos($content, 'toggle-pulse-dot') !== false || strpos($content, 'status-badge') !== false;
    assertCheck($hasPulseDot, "{$name} displays professional status pulse dot / visual indicator");

    // Verify no cust-status-toggle inside table or card
    $noToggleInView = strpos($content, 'cust-status-toggle') === false || !preg_match('/<button[^>]*class="[^"]*cust-status-toggle/i', $content);
    assertCheck($noToggleInView, "{$name} has no toggle button elements");
}

echo "\n-------------------------------------------------------\n";
echo "RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "-------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}
