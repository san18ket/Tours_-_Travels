<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Session;
use App\Core\Language;

$passed = 0;
$failed = 0;

function it(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  \033[32m✓ PASS:\033[0m {$desc}\n";
    } else {
        $failed++;
        echo "  \033[31m✗ FAIL:\033[0m {$desc}\n";
    }
}

function suite(string $name): void {
    echo "\n======================================================\n";
    echo " RUNNING SUITE: {$name}\n";
    echo "======================================================\n";
}

// =========================================================================
// SUITE 1: Layouts & Views Elimination of Static Alerts
// =========================================================================
suite("Removal of Static Alert Boxes across Layouts & Form Views");

$officeLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/office.php');
$driverLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/driver.php');
$authLayout   = file_get_contents(__DIR__ . '/../app/Views/layouts/auth.php');
$vehCreate    = file_get_contents(__DIR__ . '/../app/Views/office/vehicles/create.php');
$usrCreate    = file_get_contents(__DIR__ . '/../app/Views/office/users/create.php');
$empCreate    = file_get_contents(__DIR__ . '/../app/Views/office/employees/create.php');
$empEdit      = file_get_contents(__DIR__ . '/../app/Views/office/employees/edit.php');

it("1a. Office layout does NOT render static <div class=\"alert alert-", !str_contains($officeLayout, '<div class="alert alert-'));
it("1b. Office layout contains vgNotify flash emitter", str_contains($officeLayout, 'window.vgNotify('));
it("2a. Driver layout does NOT render static <div class=\"alert alert-", !str_contains($driverLayout, '<div class="alert alert-'));
it("2b. Driver layout contains vgNotify flash emitter", str_contains($driverLayout, 'window.vgNotify('));
it("3a. Auth layout does NOT render static <div class=\"alert alert-", !str_contains($authLayout, '<div class="alert alert-'));
it("3b. Auth layout contains vgNotify flash emitter", str_contains($authLayout, 'window.vgNotify('));

it("4a. Vehicle create does NOT render static <div class=\"alert alert-danger\"", !str_contains($vehCreate, '<div class="alert alert-danger'));
it("4b. Vehicle create emits error via window.vgNotify", str_contains($vehCreate, "window.vgNotify(firstErr, 'error')"));

it("5a. User create does NOT render static <div class=\"alert alert-danger\"", !str_contains($usrCreate, '<div class="alert alert-danger'));
it("5b. User create emits error via window.vgNotify", str_contains($usrCreate, "window.vgNotify(firstErr, 'error')"));

it("6a. Employee create does NOT render static <div class=\"alert alert-danger\"", !str_contains($empCreate, '<div class="alert alert-danger'));
it("6b. Employee create emits error via window.vgNotify", str_contains($empCreate, "window.vgNotify(firstErr, 'error')"));

it("7a. Employee edit does NOT render static <div class=\"alert alert-danger\"", !str_contains($empEdit, '<div class="alert alert-danger'));
it("7b. Employee edit emits error via window.vgNotify", str_contains($empEdit, "window.vgNotify(firstErr, 'error')"));


// =========================================================================
// SUITE 2: CSS Luxury Styling & Design Tokens
// =========================================================================
suite("Modern Luxury CSS Styling & 3-Second Progress Bar");

$appCss = file_get_contents(__DIR__ . '/../public/assets/css/app.css');

it("8a. CSS defines .vg-floating-container with fixed positioning and high z-index", 
    str_contains($appCss, '.vg-floating-container') && 
    str_contains($appCss, 'z-index: 99999999') &&
    str_contains($appCss, 'top: 24px') &&
    str_contains($appCss, 'right: 24px'));

it("8b. CSS defines .vg-floating-msg with glassmorphism backdrop-filter", 
    str_contains($appCss, '.vg-floating-msg') && 
    str_contains($appCss, 'backdrop-filter: blur(16px)') &&
    str_contains($appCss, 'background: rgba(11, 25, 44, 0.95)'));

it("8c. CSS defines active entry animation and dismissing animation", 
    str_contains($appCss, '.vg-floating-msg.is-active') && 
    str_contains($appCss, '.vg-floating-msg.is-dismissing'));

it("8d. CSS defines 3-second progress bar transition", 
    str_contains($appCss, '.vg-floating-msg-progress-bar') && 
    str_contains($appCss, 'transition: width 3000ms linear'));

it("8e. CSS pauses progress bar animation on hover", 
    str_contains($appCss, '.vg-floating-msg:hover .vg-floating-msg-progress-bar'));

it("8f. CSS defines professional Success color scheme (#10B981)", 
    str_contains($appCss, '.vg-floating-msg.is-success') && 
    str_contains($appCss, '#10B981'));

it("8g. CSS defines professional Warning color scheme (#F59E0B)", 
    str_contains($appCss, '.vg-floating-msg.is-warning') && 
    str_contains($appCss, '#F59E0B'));

it("8h. CSS defines professional Error color scheme (#EF4444)", 
    str_contains($appCss, '.vg-floating-msg.is-error') && 
    str_contains($appCss, '#EF4444'));

it("8i. CSS defines professional Info color scheme (#0284C7)", 
    str_contains($appCss, '.vg-floating-msg.is-info') && 
    str_contains($appCss, '#0284C7'));

it("8j. CSS includes mobile responsiveness media query", 
    str_contains($appCss, '@media (max-width: 600px)') && 
    str_contains($appCss, 'width: auto !important'));


// =========================================================================
// SUITE 3: JavaScript Engine & Behavior Logic
// =========================================================================
suite("JavaScript Floating Message Architecture & 3-Second Dismiss Timer");

$appJs = file_get_contents(__DIR__ . '/../public/assets/js/app.js');

it("9a. JS exports window.vgNotify with 3000ms default duration", 
    str_contains($appJs, "window.vgNotify = function (message, type = 'success', duration = 3000)"));

it("9b. JS sets uppercase status tags (SUCCESS, WARNING, ERROR, NOTICE)", 
    str_contains($appJs, "let tag = 'SUCCESS';") && 
    str_contains($appJs, "else if (safeType === 'warning') tag = 'WARNING';") &&
    str_contains($appJs, "else if (safeType === 'info') tag = 'NOTICE';"));

it("9c. JS creates crisp SVG icons for success, warning, error, info", 
    str_contains($appJs, "vg-floating-msg-icon-badge") && 
    str_contains($appJs, "polyline points=\"9 12 11 14 15 10\"") &&
    str_contains($appJs, "d=\"M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z\""));

it("9d. JS configures 3-second progress countdown animation", 
    str_contains($appJs, "progressBar.style.transition = `width \${remainingTime}ms linear`") && 
    str_contains($appJs, "progressBar.style.width = '0%'"));

it("9e. JS implements pause-on-hover interaction", 
    str_contains($appJs, "card.addEventListener('mouseenter', pauseTimer)") && 
    str_contains($appJs, "card.addEventListener('mouseleave', resumeTimer)"));

it("9f. JS provides manual close button dismissal", 
    str_contains($appJs, "closeBtn.onclick = (e) =>"));

it("9g. JS routes window.vgToast into window.vgNotify", 
    str_contains($appJs, "window.vgToast = function (msg, type, dur = 3000) { window.vgNotify(msg, type, dur); }"));

it("9h. VoyageAjax engine routes toast notifications directly to window.vgNotify", 
    str_contains($appJs, "window.vgNotify(message, type, duration)"));


// =========================================================================
// SUITE 4: Flash Message Simulation in Layout
// =========================================================================
suite("Layout Flash Rendering Simulation");

Session::start();
Session::setFlash('success', 'Operation completed successfully.');
Session::setFlash('error', 'Critical validation failed.');

$content = '<div>Driver Content</div>';
$output = '';
ob_start();
$_SERVER['REQUEST_URI'] = '/driver/dashboard';
require __DIR__ . '/../app/Views/layouts/driver.php';
$output = ob_get_clean();

it("10a. Rendered layout output does NOT contain static alert banner", !str_contains($output, 'class="alert alert-success"'));
it("10b. Rendered layout script passes success flash message", str_contains($output, 'Operation completed successfully.'));
it("10c. Rendered layout script passes error flash message", str_contains($output, 'Critical validation failed.'));
it("10d. Rendered layout contains dynamic loop calling window.vgNotify(f[k], k)", str_contains($output, 'window.vgNotify(f[k], k)'));


// =========================================================================
// TEST SUMMARY
// =========================================================================
echo "\n======================================================\n";
echo " TEST EXECUTION SUMMARY\n";
echo "======================================================\n";
echo " Passed: {$passed}\n";
echo " Failed: {$failed}\n";
echo " Total:  " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo " \033[32mALL FLOATING NOTIFICATION TESTS PASSED!\033[0m\n\n";
    exit(0);
} else {
    echo " \033[31mSOME TESTS FAILED!\033[0m\n\n";
    exit(1);
}
