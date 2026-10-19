<?php
/**
 * VoyageGo Built-in PHP Server Router
 * Usage: php -S 127.0.0.1:8080 router.php
 */
declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

// Check if static asset exists inside public/
$publicPath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicPath) && !is_dir($publicPath)) {
    $mimeTypes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'pdf'   => 'application/pdf',
    ];
    $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($publicPath);
    exit;
}

// Check if static file exists in project root (e.g. manifest, robots)
$rootPath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($rootPath) && !is_dir($rootPath) && !str_ends_with($uri, '.php')) {
    return false;
}

// Route to public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/public/index.php';
