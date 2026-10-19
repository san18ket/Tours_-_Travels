<?php
/**
 * VoyageGo Built-in PHP Server Router (Inside public/)
 * Usage: php -S 127.0.0.1:8080 -t public router.php
 */
declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$filePath = __DIR__ . $uri;

if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

require_once __DIR__ . '/index.php';
