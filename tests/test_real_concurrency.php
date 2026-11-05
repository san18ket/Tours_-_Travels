<?php
declare(strict_types=1);

/**
 * REAL MULTI-PROCESS CONCURRENCY TEST SUITE
 * Uses proc_open to launch separate PHP processes with isolated database connections
 * against tours_travels_test_db to verify real concurrency, atomic token rotation,
 * grace period protection, and anti-DoS isolation.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once __DIR__ . '/TestEnvironment.php';

use Tests\TestEnvironment;
use App\Services\AuthService;

$pdo = TestEnvironment::init();
$phpBinary = 'E:\\xampp\\php\\php.exe';
$workerPath = __DIR__ . '/concurrency_worker.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertCondition(string $name, bool $condition, string $details = ''): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$name}" . ($details ? " - {$details}" : "") . "\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$name}" . ($details ? " - {$details}" : "") . "\n";
    }
}

function runParallelProcesses(array $commands): array {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $processes = [];
    $pipes = [];

    // Launch all processes simultaneously
    foreach ($commands as $key => $cmd) {
        $proc = proc_open($cmd, $descriptors, $pipes[$key]);
        if (is_resource($proc)) {
            $processes[$key] = $proc;
        }
    }

    $outputs = [];
    foreach ($processes as $key => $proc) {
        $stdout = stream_get_contents($pipes[$key][1]);
        $stderr = stream_get_contents($pipes[$key][2]);
        fclose($pipes[$key][0]);
        fclose($pipes[$key][1]);
        fclose($pipes[$key][2]);
        $exitCode = proc_close($proc);

        $outputs[$key] = [
            'exit_code' => $exitCode,
            'stdout' => trim($stdout),
            'stderr' => trim($stderr),
            'json' => json_decode(trim($stdout), true)
        ];
    }

    return $outputs;
}

echo "========================================================================\n";
echo " REAL MULTI-PROCESS CONCURRENCY & RACE-CONDITION TEST SUITE\n";
echo " PHP CLI Binary: {$phpBinary}\n";
echo " Target DB:      " . TestEnvironment::TEST_DB_NAME . "\n";
echo "========================================================================\n\n";

// 1. Setup isolated test user
TestEnvironment::cleanupTestData();
$testUser = TestEnvironment::createTestUser([
    'name' => 'Concurrency Auditor',
    'username' => 'concur_audit_' . bin2hex(random_bytes(3)),
    'role' => 'admin'
]);
$userId = $testUser['id'];
$authService = new AuthService();

echo "--- Test 1: Real Simultaneous Token Rotation (Race Condition) ---\n";
// Create initial token
$cookie1 = $authService->createRememberToken($userId);
[$selector1] = explode(':', $cookie1, 2);

// Launch 2 parallel processes simultaneously with the exact same initial cookie
$cmd1 = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$cookie1}\"";
$cmd2 = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$cookie1}\"";

$parallelResults = runParallelProcesses(['proc1' => $cmd1, 'proc2' => $cmd2]);

$p1 = $parallelResults['proc1']['json'] ?? [];
$p2 = $parallelResults['proc2']['json'] ?? [];

assertCondition("Process 1 authenticates successfully", ($p1['success'] ?? false) === true && ($p1['user_id'] ?? 0) === $userId);
assertCondition("Process 2 authenticates successfully", ($p2['success'] ?? false) === true && ($p2['user_id'] ?? 0) === $userId);

// Verify database row state: prev_validator_hash should be populated, rotated_at should be recent
$stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE selector = ?");
$stmt->execute([$selector1]);
$dbRow = $stmt->fetch(PDO::FETCH_ASSOC);

assertCondition("Token record still exists in database", !empty($dbRow));
assertCondition("Token prev_validator_hash was recorded during rotation", !empty($dbRow['prev_validator_hash']));
assertCondition("Token rotated_at is populated", !empty($dbRow['rotated_at']));


echo "\n--- Test 2: Grace Window Authentication (Within 60s) ---\n";
// The original validator should still authenticate in the grace window
$cmd3 = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$cookie1}\"";
$res3 = runParallelProcesses(['grace_proc' => $cmd3]);
$p3 = $res3['grace_proc']['json'] ?? [];

assertCondition("Request using previous validator within 60s grace window succeeds", ($p3['success'] ?? false) === true && ($p3['user_id'] ?? 0) === $userId);


echo "\n--- Test 3: Post-Grace Window Replay Attack (> 60s) ---\n";
// Simulate grace window expiration by updating rotated_at to 65 seconds ago
$pdo->prepare("UPDATE remember_tokens SET rotated_at = DATE_SUB(NOW(), INTERVAL 65 SECOND) WHERE selector = ?")
    ->execute([$selector1]);

$cmd4 = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$cookie1}\"";
$res4 = runParallelProcesses(['replay_proc' => $cmd4]);
$p4 = $res4['replay_proc']['json'] ?? [];

assertCondition("Replay attempt outside grace window (>60s) is strictly rejected", ($p4['success'] ?? false) === false);

// Check that the token was revoked
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM remember_tokens WHERE selector = ?");
$stmtCheck->execute([$selector1]);
$remainingCount = (int)$stmtCheck->fetchColumn();
assertCondition("Compromised token was revoked from database", $remainingCount === 0);


echo "\n--- Test 4: Multi-Device Isolation (No Denial of Service to Other Devices) ---\n";
// Create Device A and Device B tokens for same user
$devACookie = $authService->createRememberToken($userId);
$devBCookie = $authService->createRememberToken($userId);
[$devASelector, $devAValidator] = explode(':', $devACookie, 2);
[$devBSelector, $devBValidator] = explode(':', $devBCookie, 2);

assertCondition("Device A and Device B have distinct selectors", $devASelector !== $devBSelector);

// Simulate rotation on Device A to a new hash, placing devAValidator in prev_validator_hash with rotated_at > 60s ago
$devANewHash = hash('sha256', bin2hex(random_bytes(32)));
$pdo->prepare("UPDATE remember_tokens SET validator_hash = ?, prev_validator_hash = ?, rotated_at = DATE_SUB(NOW(), INTERVAL 70 SECOND) WHERE selector = ?")
    ->execute([$devANewHash, hash('sha256', $devAValidator), $devASelector]);

// Replay with Device A's old token
$cmdDevA = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$devACookie}\"";
$resDevA = runParallelProcesses(['devA' => $cmdDevA]);
$pDevA = $resDevA['devA']['json'] ?? [];
assertCondition("Device A replayed token is rejected", ($pDevA['success'] ?? false) === false);

// Device B must still be able to authenticate normally without DoS!
$cmdDevB = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$devBCookie}\"";
$resDevB = runParallelProcesses(['devB' => $cmdDevB]);
$pDevB = $resDevB['devB']['json'] ?? [];
assertCondition("Device B remains valid and authenticates without DoS", ($pDevB['success'] ?? false) === true && ($pDevB['user_id'] ?? 0) === $userId);


echo "\n--- Test 5: Rejection of Expired Token and Future rotated_at ---\n";
// Expired token
$expCookie = $authService->createRememberToken($userId);
[$expSelector] = explode(':', $expCookie, 2);
$pdo->prepare("UPDATE remember_tokens SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE selector = ?")
    ->execute([$expSelector]);

$cmdExp = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$expCookie}\"";
$resExp = runParallelProcesses(['exp' => $cmdExp]);
$pExp = $resExp['exp']['json'] ?? [];
assertCondition("Expired token is strictly rejected", ($pExp['success'] ?? false) === false);

// Future rotated_at token (clock tampering attack on rotated validator)
$futureCookie = $authService->createRememberToken($userId);
[$futureSelector, $futureValidator] = explode(':', $futureCookie, 2);
$newFutureHash = hash('sha256', bin2hex(random_bytes(32)));
$pdo->prepare("UPDATE remember_tokens SET validator_hash = ?, prev_validator_hash = ?, rotated_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE selector = ?")
    ->execute([$newFutureHash, hash('sha256', $futureValidator), $futureSelector]);

$cmdFuture = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$futureCookie}\"";
$resFuture = runParallelProcesses(['future' => $cmdFuture]);
$pFuture = $resFuture['future']['json'] ?? [];
assertCondition("Future rotated_at timestamp is rejected", ($pFuture['success'] ?? false) === false);


echo "\n--- Test 6: Multi-Process High-Concurrency Stress Test (10 Simultaneous Processes) ---\n";
$stressCookies = [];
for ($i = 0; $i < 5; $i++) {
    $stressCookies[] = $authService->createRememberToken($userId);
}

// Generate 10 simultaneous login commands across the 5 tokens
$stressCmds = [];
for ($i = 0; $i < 10; $i++) {
    $c = $stressCookies[$i % 5];
    $stressCmds["stress_{$i}"] = "\"{$phpBinary}\" \"{$workerPath}\" --action=login --cookie=\"{$c}\"";
}

$stressStartTime = microtime(true);
$stressResults = runParallelProcesses($stressCmds);
$stressDurationMs = round((microtime(true) - $stressStartTime) * 1000, 2);

$stressSuccesses = 0;
foreach ($stressResults as $res) {
    if (($res['json']['success'] ?? false) === true) {
        $stressSuccesses++;
    }
}

assertCondition("All 10 concurrent processes completed without deadlock ({$stressDurationMs} ms)", count($stressResults) === 10);
assertCondition("All concurrent logins authenticated cleanly ({$stressSuccesses}/10 successes)", $stressSuccesses === 10);


// Cleanup
TestEnvironment::cleanupTestData();

echo "\n========================================================================\n";
echo " CONCURRENCY TEST SUMMARY\n";
echo " Total Assertions: {$totalTests}\n";
echo " Passed:           {$passedTests}\n";
echo " Failed:           {$failedTests}\n";
echo " Pass Rate:        " . round(($passedTests / max(1, $totalTests)) * 100, 1) . "%\n";
echo "========================================================================\n";

exit($failedTests === 0 ? 0 : 1);
