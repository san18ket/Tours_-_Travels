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
use App\Core\DatabaseInitializationException;
use App\Core\Logger;
use App\Core\View;

$request = Request::capture();
$path = $request->getPath();

// Serve public storage assets securely if front controller catches them
if (str_starts_with($path, '/storage/')) {
    $subPath = ltrim(substr($path, 9), '/');
    $candidateFiles = [
        __DIR__ . '/storage/' . $subPath,
        dirname(__DIR__) . '/storage/' . $subPath,
        dirname(__DIR__) . '/storage/uploads/' . $subPath,
        __DIR__ . '/assets/uploads/' . $subPath,
    ];
    foreach ($candidateFiles as $filePath) {
        if (file_exists($filePath) && !is_dir($filePath)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($filePath) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string)filesize($filePath));
            header('Cache-Control: public, max-age=86400');
            readfile($filePath);
            exit;
        }
    }
}

// Automatically ensure database and approved tables exist ONLY when required (fresh install, new migrations, deployment update, or login page load)
if (!str_starts_with($path, '/assets') && !str_starts_with($path, '/storage')) {
    $isLoginRoute = in_array($path, ['/login', '/admin/login'], true);

    if ($isLoginRoute || MigrationManager::needsInitialization()) {
        try {
            MigrationManager::autoInitialize();
        } catch (DatabaseInitializationException $e) {
            $e->renderResponse($request);
            exit;
        } catch (\Throwable $e) {
            Logger::error("Database auto-initialization error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            DatabaseInitializationException::renderGenericError($request, $e);
            exit;
        }
    }
}

// Installation check
$isInstalled = MigrationManager::isInstalled();

if (!$isInstalled) {
    if (!str_starts_with($path, '/setup') && !str_starts_with($path, '/assets') && !str_starts_with($path, '/storage')) {
        // If user is accessing login, auto-initialize directly instead of redirecting away to setup
        if (in_array($path, ['/login', '/admin/login'], true)) {
            try {
                MigrationManager::autoInitialize();
                $isInstalled = MigrationManager::isInstalled();
            } catch (\Throwable $e) {
                Logger::error("Login auto-initialization recovery failed: " . $e->getMessage());
            }
        }

        if (!$isInstalled) {
            Response::redirect(url('/setup'))->send();
            exit;
        }
    }
}

// Load router and dispatch request
/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/app/routes.php';
$response = $router->dispatch($request);

// Attach safe performance headers in development/admin mode
\App\Core\Profiler::attachResponseHeaders($response);

$response->send();
