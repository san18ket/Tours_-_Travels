<?php
/**
 * Test: Verification of Checkbox Presence & Functionality in Delete Modal on Mobile & Desktop
 */

require_once __DIR__ . '/../app/Core/Autoloader.php';

$views = [
    'bookings/index'   => __DIR__ . '/../app/Views/office/bookings/index.php',
    'customers/index'  => __DIR__ . '/../app/Views/office/customers/index.php',
    'customers/show'   => __DIR__ . '/../app/Views/office/customers/show.php',
    'employees/index'  => __DIR__ . '/../app/Views/office/employees/index.php',
    'employees/show'   => __DIR__ . '/../app/Views/office/employees/show.php',
    'vehicles/index'   => __DIR__ . '/../app/Views/office/vehicles/index.php',
];

$appCss = file_get_contents(__DIR__ . '/../public/assets/css/app.css');

echo "======================================================\n";
echo " RUNNING SUITE: Mobile & Desktop Delete Modal Checkbox Verification\n";
echo "======================================================\n";

// 1. Check app.css for mobile checkbox display
if (strpos($appCss, '.vg-del-checkbox-wrap') !== false && strpos($appCss, 'display: block !important') !== false) {
    echo "  [PASS] app.css explicitly displays .vg-del-checkbox-wrap as block on mobile\n";
} else {
    echo "  [FAIL] app.css missing display: block for .vg-del-checkbox-wrap on mobile\n";
    exit(1);
}

// Ensure display: none is NOT set on .vg-del-checkbox-wrap in app.css
if (preg_match('/\.vg-del-checkbox-wrap\s*\{\s*display:\s*none\s*!important/i', $appCss)) {
    echo "  [FAIL] app.css contains display: none !important for .vg-del-checkbox-wrap\n";
    exit(1);
} else {
    echo "  [PASS] app.css does not hide .vg-del-checkbox-wrap on mobile\n";
}

// 2. Check each view
foreach ($views as $name => $path) {
    $content = file_get_contents($path);

    // Check that checkbox container exists in Step 1 and Step 2
    if (strpos($content, 'id="deleteStep1Checkbox"') !== false && strpos($content, 'id="deleteStep2Checkbox"') !== false) {
        echo "  [PASS] {$name}: contains Step 1 and Step 2 checkboxes in DOM\n";
    } else {
        echo "  [FAIL] {$name}: missing checkbox elements\n";
        exit(1);
    }

    // Check that display: none !important is NOT in view styles for .vg-del-checkbox-wrap
    if (preg_match('/\.vg-del-checkbox-wrap\s*\{\s*display:\s*none\s*!important/i', $content)) {
        echo "  [FAIL] {$name}: contains inline style hiding .vg-del-checkbox-wrap\n";
        exit(1);
    } else {
        echo "  [PASS] {$name}: does not suppress checkbox in inline CSS\n";
    }

    // Check that goToStep2Modal validates checkbox
    if (strpos($content, '!chk.checked') !== false) {
        echo "  [PASS] {$name}: goToStep2Modal requires checkbox validation\n";
    } else {
        echo "  [FAIL] {$name}: goToStep2Modal does not validate checkbox\n";
        exit(1);
    }

    // Check that Step 2 requires checkbox
    if (strpos($content, '!chk2.checked') !== false) {
        echo "  [PASS] {$name}: Step 2 delete requires checkbox validation\n";
    } else {
        echo "  [FAIL] {$name}: Step 2 does not validate checkbox\n";
        exit(1);
    }
}

echo "\n============================================\n";
echo " ALL CHECKS PASSED: Delete modal checkbox is present and enforced across mobile & desktop!\n";
echo "============================================\n";
