<?php
/**
 * Test: Responsive Auto-Sizing Message Boxes & Notifications
 * Tests: Success, Warning, Error & Notice/Info mobile-friendly responsive behavior
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

function assertRule($cond, $desc) {
    if ($cond) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "TEST: Responsive Auto-Sizing Notification & Message Box System\n";
echo "====================================================================\n\n";

$appCssPath = APP_ROOT . '/public/assets/css/app.css';
$appJsPath = APP_ROOT . '/public/assets/js/app.js';

assertRule(file_exists($appCssPath), "public/assets/css/app.css exists");
assertRule(file_exists($appJsPath), "public/assets/js/app.js exists");

$cssContent = file_get_contents($appCssPath);
$jsContent = file_get_contents($appJsPath);

// 1. Verify CSS Auto-Sizing & Alignment
echo "\n1. Verifying Desktop & Auto-Sizing CSS Architecture...\n";
assertRule(strpos($cssContent, '.vg-floating-container') !== false, "Contains .vg-floating-container definition");
assertRule(strpos($cssContent, 'align-items: flex-end') !== false, "Container aligns items to flex-end so card auto-sizes horizontally");
assertRule(strpos($cssContent, 'width: fit-content') !== false, "Card uses width: fit-content for auto-sizing");
assertRule(strpos($cssContent, 'min-width: min(300px') !== false, "Desktop card has responsive min-width constraint");
assertRule(strpos($cssContent, 'max-width: min(500px') !== false, "Desktop card has responsive max-width constraint");

// 2. Verify Themes for all message types
echo "\n2. Verifying Message Themes (Success, Warning, Error, Info)...\n";
assertRule(strpos($cssContent, '.vg-floating-msg.is-success') !== false, "Theme: is-success defined");
assertRule(strpos($cssContent, '.vg-floating-msg.is-warning') !== false, "Theme: is-warning defined");
assertRule(strpos($cssContent, '.vg-floating-msg.is-error') !== false, "Theme: is-error defined");
assertRule(strpos($cssContent, '.vg-floating-msg.is-info') !== false, "Theme: is-info defined");

assertRule(strpos($cssContent, '#10B981') !== false, "Success accent color #10B981 configured");
assertRule(strpos($cssContent, '#F59E0B') !== false, "Warning accent color #F59E0B configured");
assertRule(strpos($cssContent, '#EF4444') !== false, "Error accent color #EF4444 configured");
assertRule(strpos($cssContent, '#0284C7') !== false, "Info accent color #0284C7 configured");

// 3. Verify Mobile Responsiveness (<= 600px & <= 380px)
echo "\n3. Verifying Mobile-Friendly Responsive Media Queries...\n";
assertRule(strpos($cssContent, '@media (max-width: 600px)') !== false, "Contains @media (max-width: 600px) block");
assertRule(strpos($cssContent, 'align-items: center; /* Horizontally centered on mobile */') !== false, "Mobile container centers notification box horizontally");
assertRule(strpos($cssContent, 'min-width: min(270px, calc(100vw - 24px))') !== false, "Mobile card scales down min-width dynamically");
assertRule(strpos($cssContent, 'max-width: min(420px, calc(100vw - 24px))') !== false, "Mobile card caps max-width comfortably");
assertRule(strpos($cssContent, 'transform: translateY(-24px)') !== false, "Mobile uses vertical slide from top to avoid horizontal overflow");
assertRule(strpos($cssContent, '@media (max-width: 380px)') !== false, "Contains compact device breakpoint (<= 380px) for small phones");

// 4. Verify Shared Inline Alerts
echo "\n4. Verifying Shared Inline Alerts (.alert, .alert-success, etc.)...\n";
assertRule(strpos($cssContent, '.alert {') !== false, "Contains shared .alert base styles");
assertRule(strpos($cssContent, '.alert-success {') !== false, "Contains .alert-success theme");
assertRule(strpos($cssContent, '.alert-warning {') !== false, "Contains .alert-warning theme");
assertRule(strpos($cssContent, '.alert-danger') !== false, "Contains .alert-danger theme");
assertRule(strpos($cssContent, '.alert-info {') !== false, "Contains .alert-info theme");

// 5. Verify JavaScript Engine in app.js
echo "\n5. Verifying vgNotify JS Engine & Touch Swipe Dismiss...\n";
assertRule(strpos($jsContent, 'window.vgNotify = function') !== false, "vgNotify function exported globally on window");
assertRule(strpos($jsContent, 'const autoDuration') !== false, "Auto-calculates proportional reading duration for longer messages");
assertRule(strpos($jsContent, 'touchstart') !== false, "Listens to touchstart for mobile interactions");
assertRule(strpos($jsContent, 'touchmove') !== false, "Listens to touchmove for mobile swipe gesture");
assertRule(strpos($jsContent, 'touchend') !== false, "Listens to touchend for flick-to-dismiss gesture");
assertRule(strpos($jsContent, 'pauseTimer') !== false, "Pauses countdown on hover and touch interaction");

// 6. Verify Layout Integration
echo "\n6. Verifying Layout Integration (office, auth, driver)...\n";
$officeLayout = file_get_contents(APP_ROOT . '/app/Views/layouts/office.php');
$authLayout = file_get_contents(APP_ROOT . '/app/Views/layouts/auth.php');
$driverLayout = file_get_contents(APP_ROOT . '/app/Views/layouts/driver.php');

assertRule(strpos($officeLayout, 'window.vgNotify') !== false, "office.php layout emits via window.vgNotify");
assertRule(strpos($authLayout, 'window.vgNotify') !== false, "auth.php layout emits via window.vgNotify");
assertRule(strpos($driverLayout, 'window.vgNotify') !== false, "driver.php layout emits via window.vgNotify");

echo "\n====================================================================\n";
echo "SUCCESS: All responsive message box verification checks passed!\n";
echo "====================================================================\n";

