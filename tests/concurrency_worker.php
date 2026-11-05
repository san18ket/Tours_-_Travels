<?php
declare(strict_types=1);

/**
 * Concurrency Worker Process
 * Spun up in independent operating system processes to test real race conditions,
 * parallel database transactions, simultaneous token rotation, and grace periods.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once __DIR__ . '/TestEnvironment.php';

use Tests\TestEnvironment;
use App\Services\AuthService;
use App\Core\Database;

// Enforce test environment isolation
$pdo = TestEnvironment::init();

$options = getopt('', ['action:', 'cookie:', 'delay_ms:', 'user_id:']);
$action = $options['action'] ?? 'login';
$cookie = $options['cookie'] ?? '';
$delayMs = isset($options['delay_ms']) ? (int)$options['delay_ms'] : 0;
$userId = isset($options['user_id']) ? (int)$options['user_id'] : 0;

if ($delayMs > 0) {
    usleep($delayMs * 1000);
}

$authService = new AuthService();

try {
    switch ($action) {
        case 'login':
            // Capture any cookies set during login
            $setCookieHeader = null;
            $user = $authService->attemptRememberMeLogin($cookie);
            
            // Check headers or token table to see if it rotated
            [$selector] = explode(':', $cookie, 2);
            $stmt = $pdo->prepare("SELECT validator_hash, prev_validator_hash, rotated_at FROM remember_tokens WHERE selector = ?");
            $stmt->execute([$selector]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => $user !== null,
                'user_id' => $user ? (int)$user['id'] : null,
                'token_state' => $row,
                'timestamp' => microtime(true)
            ], JSON_UNESCAPED_SLASHES);
            break;

        case 'create_token':
            $tokenData = $authService->createRememberToken($userId);
            echo json_encode([
                'success' => true,
                'token' => $tokenData,
            ], JSON_UNESCAPED_SLASHES);
            break;

        case 'revoke':
            $authService->revokeRememberToken($cookie);
            echo json_encode([
                'success' => true,
            ], JSON_UNESCAPED_SLASHES);
            break;

        default:
            echo json_encode(['success' => false, 'error' => "Unknown action '{$action}'"]);
            break;
    }
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
    ], JSON_UNESCAPED_SLASHES);
}

