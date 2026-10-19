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

// Set default timezone
date_default_timezone_set(Config::get('app.timezone', 'Asia/Kolkata'));

// Start secure session
Session::start();

// Custom global exception handler
set_exception_handler(function (\Throwable $e) {
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
