<?php

require_once __DIR__ . '/../app/bootstrap.php';
use App\Core\View;

echo "====================================================\n";
echo "Testing Live HTML Rendering of Badges & Login Form\n";
echo "====================================================\n";

// 1. Office Layout with 0 notifications
$out0 = View::render('office/dashboard', [
    'user' => ['name' => 'Admin User', 'role' => 'super_admin', 'email' => 'admin@test.com'],
    'unreadNotifCount' => 0,
    'topbarNotifications' => []
], 'layouts/office');

if (str_contains($out0, 'id="desktopNotifBadge" data-count="0" style="display: none;"') && 
    str_contains($out0, 'id="mobileNotifBadge" data-count="0" style="display: none;"')) {
    echo "[PASS] Office layout 0 count: badges hidden with no text\n";
} else {
    echo "[FAIL] Office layout 0 count unexpected output\n";
    exit(1);
}

// 2. Office Layout with 24 notifications
$out24 = View::render('office/dashboard', [
    'user' => ['name' => 'Admin User', 'role' => 'super_admin', 'email' => 'admin@test.com'],
    'unreadNotifCount' => 24,
    'topbarNotifications' => []
], 'layouts/office');

if (str_contains($out24, 'id="desktopNotifBadge" data-count="24" style="display: inline-flex;" title="24 unread notifications">24</span>') &&
    str_contains($out24, 'id="mobileNotifBadge" data-count="24" style="display: inline-flex;" title="24 unread notifications">24</span>')) {
    echo "[PASS] Office layout 24 count: badges displayed with '24'\n";
} else {
    echo "[FAIL] Office layout 24 count unexpected output\n";
    exit(1);
}

// 3. Office Layout with 150 notifications
$out150 = View::render('office/dashboard', [
    'user' => ['name' => 'Admin User', 'role' => 'super_admin', 'email' => 'admin@test.com'],
    'unreadNotifCount' => 150,
    'topbarNotifications' => []
], 'layouts/office');

if (str_contains($out150, 'id="desktopNotifBadge" data-count="150" style="display: inline-flex;" title="150 unread notifications">99+</span>') &&
    str_contains($out150, 'id="mobileNotifBadge" data-count="150" style="display: inline-flex;" title="150 unread notifications">99+</span>')) {
    echo "[PASS] Office layout 150 count: badges displayed with '99+' and data-count='150'\n";
} else {
    echo "[FAIL] Office layout 150 count unexpected output\n";
    exit(1);
}

// 4. Driver Layout with 99+ notifications
$outDriver = View::render('driver/notifications', [
    'user' => ['name' => 'Driver User', 'role' => 'driver'],
    'unreadNotifCount' => 9999,
    'notifications' => []
], 'layouts/driver');

if (str_contains($outDriver, 'id="driverTopNotifBadge" data-count="9999" style="display: inline-flex;" title="9999 unread notifications">99+</span>') &&
    str_contains($outDriver, 'id="driverBottomNotifBadge" data-count="9999" style="display: inline-flex;" title="9999 unread notifications">99+</span>')) {
    echo "[PASS] Driver layout 9999 count: badges displayed with '99+' and data-count='9999'\n";
} else {
    echo "[FAIL] Driver layout 9999 count unexpected output\n";
    exit(1);
}

// 5. Login View Form Rendering
$outLogin = View::render('auth/login', [
    'oldLogin' => 'john@example.com'
], 'layouts/auth');

if (str_contains($outLogin, 'id="loginForm"') &&
    !str_contains($outLogin, 'id="loginForm" autocomplete="off"') &&
    str_contains($outLogin, 'name="login"') &&
    str_contains($outLogin, 'autocomplete="username"') &&
    str_contains($outLogin, 'name="password"') &&
    str_contains($outLogin, 'type="password"') &&
    str_contains($outLogin, 'autocomplete="current-password"') &&
    !str_contains($outLogin, 'data-lpignore') &&
    !str_contains($outLogin, 'masked-password-field')) {
    echo "[PASS] Login view rendering: standard password manager form attributes verified\n";
} else {
    echo "[FAIL] Login view rendering unexpected output\n";
    exit(1);
}

echo "====================================================\n";
echo "SUCCESS: ALL RENDERING ASSERTIONS PASSED!\n";
echo "====================================================\n";
