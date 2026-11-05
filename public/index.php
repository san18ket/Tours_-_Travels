<?php
/**
 * Tours & Travels Management System
 * Single Web Entry Point
 */

// If using PHP built-in web server, serve existing static files directly
if (php_sapi_name() === 'cli-server') {
    $staticPath = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (file_exists($staticPath) && !is_dir($staticPath)) {
        return false;
    }
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\MigrationManager;
use App\Core\Database;
use App\Core\Logger;
use App\Core\View;

$request = Request::capture();
$path = $request->getPath();

// Automatically ensure database and approved tables exist on application entry
if (!str_starts_with($path, '/assets')) {
    try {
        MigrationManager::autoInitialize();
    } catch (\Throwable $e) {
        Logger::error("Database auto-initialization error: " . $e->getMessage());
    }
}

// Installation check
$isInstalled = MigrationManager::isInstalled();

if (!$isInstalled) {
    if (!str_starts_with($path, '/setup') && !str_starts_with($path, '/assets')) {
        Response::redirect(url('/setup'))->send();
        exit;
    }
}

// Load router and dispatch request
/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/app/routes.php';
$response = $router->dispatch($request);
$response->send();
