<?php
/**
 * Application Bootstrap
 */

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never leak raw stack traces to web output
ini_set('log_errors', '1');

// Setup autoloader
require_once __DIR__ . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Start performance profiler timer
\App\Core\Profiler::init();

// Load secure environment variables from root .env
\App\Core\Env::load();

// Load Composer packages if available
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load global helpers
require_once __DIR__ . '/Core/helpers.php';

use App\Core\Config;
use App\Core\Session;
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

// Validate environment configuration safely
$envWarnings = \App\Core\Env::validate();
if (!empty($envWarnings)) {
    foreach ($envWarnings as $warning) {
        Logger::warning($warning);
    }
}

// Set default timezone
date_default_timezone_set(Config::get('app.timezone', 'Asia/Kolkata'));

// Start secure session
Session::start();

// Initialize active language locale from session/cookie/user preference
\App\Core\Language::init();

// Custom global exception handler
set_exception_handler(function (\Throwable $e) {
    if ($e instanceof \App\Core\DatabaseInitializationException) {
        $e->renderResponse();
        exit;
    }

    if ($e instanceof \PDOException) {
        $code = (int) $e->getCode();
        $msg = $e->getMessage();

        // Self-healing recovery for missing database (1049) or missing tables (1146 / 42S02)
        if ($code === 1049 || $code === 1146 || str_contains($msg, '42S02') || str_contains($msg, '1146') || str_contains($msg, 'Unknown database') || str_contains($msg, "doesn't exist")) {
            try {
                \App\Core\MigrationManager::autoInitialize();
                if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                    $targetUri = $_SERVER['REQUEST_URI'] ?? '/login';
                    header("Location: " . $targetUri);
                    exit;
                }
            } catch (\Throwable $healEx) {
                Logger::error("Global exception auto-init recovery failed: " . $healEx->getMessage());
            }
        }

        $dbEx = \App\Core\DatabaseInitializationException::fromPDOException($e);
        $dbEx->renderResponse();
        exit;
    }

    $incidentId = 'INC-' . strtoupper(substr(hash('sha256', uniqid('', true)), 0, 8));
    Logger::error("Unhandled exception [{$incidentId}]: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    if (!headers_sent()) {
        http_response_code(500);
    }

    $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => 'A secure internal server error occurred.',
            'ref'     => $incidentId
        ]);
        exit;
    }

    try {
        echo View::render('errors/500', [
            'ref' => $incidentId,
            'message' => 'An unexpected internal error occurred. Please provide the incident reference code if contacting support.'
        ], 'layouts/error');
    } catch (\Throwable $nested) {
        echo "<!DOCTYPE html><html><head><title>System Error</title><meta name='viewport' content='width=device-width, initial-scale=1'></head><body style='font-family:system-ui;padding:40px;background:#0B192C;color:#fff;'><div style='max-width:500px;margin:auto;background:#1E3E62;padding:30px;border-radius:12px;border:1px solid #335384;'><h2 style='color:#EF4444;'>Service Temporarily Unavailable</h2><p>A protected server error occurred. Incident Code: <strong>{$incidentId}</strong></p></div></body></html>";
    }
    exit;
});
