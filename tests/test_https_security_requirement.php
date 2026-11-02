<?php
/**
 * Test Suite for HTTPS Security Requirements & Cloudflare Tunnel Proxy Compatibility
 */
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

echo "============================================================\n";
echo "1. VERIFYING PHP HTTPS & CLOUDFLARE TUNNEL DETECTION\n";
echo "============================================================\n";

function test(string $desc, bool $condition): void {
    if ($condition) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        exit(1);
    }
}

// Case 1: Plain HTTP
$_SERVER['HTTPS'] = 'off';
unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
unset($_SERVER['HTTP_CF_VISITOR']);
$_SERVER['SERVER_PORT'] = '80';
test("Plain HTTP (port 80) is detected as insecure (is_https() === false)", is_https() === false);

// Case 2: Native Apache HTTPS
$_SERVER['HTTPS'] = 'on';
test("Native Apache HTTPS is detected (is_https() === true)", is_https() === true);

// Case 3: Cloudflare Tunnel / Reverse Proxy with X-Forwarded-Proto
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
test("Cloudflare Tunnel via X-Forwarded-Proto: https is detected (is_https() === true)", is_https() === true);

// Case 4: Cloudflare Tunnel with CF-Visitor JSON
unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
$_SERVER['HTTP_CF_VISITOR'] = json_encode(['scheme' => 'https']);
test("Cloudflare Tunnel via CF-Visitor JSON is detected (is_https() === true)", is_https() === true);

echo "\n============================================================\n";
echo "2. VERIFYING DYNAMIC BASE URL & ROUTE RESOLUTION\n";
echo "============================================================\n";

// Case A: Cloudflare Tunnel directly mapped to public root (e.g. https://tours.example.com/)
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'tours.trycloudflare.com';
test("Tunnel root start URL is '/'", url('/') === '/');
test("Tunnel root sw.js path is '/sw.js'", url('/sw.js') === '/sw.js');
test("Tunnel root push subscribe API is '/api/push/subscribe'", url('/api/push/subscribe') === '/api/push/subscribe');

// Case B: Cloudflare Tunnel mapped to XAMPP htdocs with subfolder
$_SERVER['SCRIPT_NAME'] = '/All-Projects/MY-PROJECTS/Tours_travels/public/index.php';
test("Subdirectory path is correctly prefixed in url('/')", url('/') === '/All-Projects/MY-PROJECTS/Tours_travels/public/');
test("Subdirectory sw.js is correctly prefixed", url('/sw.js') === '/All-Projects/MY-PROJECTS/Tours_travels/public/sw.js');
test("Subdirectory API is correctly prefixed", url('/api/push/status') === '/All-Projects/MY-PROJECTS/Tours_travels/public/api/push/status');

echo "\n============================================================\n";
echo "TEST SUMMARY: ALL HTTPS SECURITY CHECKS PASSED\n";
echo "============================================================\n";
