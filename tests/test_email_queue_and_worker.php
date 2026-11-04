<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Comprehensive Verification Test Suite: Email Delivery, Queue & Worker
 * ============================================================================
 *
 * Validates:
 * 1. Zero SMTP Latency: Outbox queuing completes in < 50ms (non-blocking).
 * 2. Outbox Schema Integrity: Storing html_body, text_body, cc, bcc, attempts.
 * 3. Event Queue Dispatches: Customer, Employee, Booking, Driver, Payment.
 * 4. Idempotency & Duplicate Prevention.
 * 5. Atomic Batch Claiming & Concurrency Row Locking.
 * 6. Stale Processing Lock Auto-Recovery.
 * 7. Tiered Retry Backoff Schedule (1m, 5m, 15m, 30m, max 5).
 * 8. Permanent Failure Handling on Max Attempts.
 * 9. Worker Heartbeat Telemetry & Health Checks.
 * 10. CLI Worker Execution (--once, --health).
 * 11. Security Redaction (no credentials/passwords leaked).
 * ============================================================================
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\EmailLog;
use App\Services\EmailService;

$pdo = Database::getConnection();
$emailLog = new EmailLog();
$emailService = new EmailService();

$passed = 0;
$failed = 0;
$totalTests = 0;

function assertTest(string $description, bool $condition, ?string $details = null): void
{
    global $passed, $failed, $totalTests;
    $totalTests++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

echo "====================================================================\n";
echo " Starting Email Queue, Worker & Delivery Automated Test Suite\n";
echo "====================================================================\n\n";

// Clean up any lingering test records from previous aborted runs
$pdo->prepare("DELETE FROM `email_logs` WHERE `recipient_email` LIKE '%@example.com'")->execute();
$pdo->prepare("DELETE FROM `email_worker_heartbeats` WHERE `worker_id` LIKE 'worker_test_%'")->execute();

$testRunBase = (int)(time() % 1000000) * 10;

// ============================================================================
// SECTION 1: Zero SMTP Latency & Outbox Queuing Performance
// ============================================================================
echo "--- Section 1: Non-Blocking Outbox Queue Performance (< 50ms) ---\n";

$testEmail = 'bench_test_' . time() . '_' . random_int(100, 999) . '@example.com';
$start = microtime(true);
$logId = $emailService->queue(
    $testEmail,
    'Benchmark Tester',
    'Benchmark Test Email Subject',
    '<p>Testing outbox queue latency.</p>',
    'Testing outbox queue latency.',
    'test_queue',
    'test',
    $testRunBase + 1
);
$elapsedMs = (microtime(true) - $start) * 1000;

assertTest(
    "Queue dispatch returned valid outbox log ID",
    is_int($logId) && $logId > 0,
    "Log ID: " . var_export($logId, true)
);

assertTest(
    "Queue dispatch executed with zero SMTP latency (< 50ms) [Actual: " . round($elapsedMs, 2) . "ms]",
    $elapsedMs < 50.0,
    "Execution took {$elapsedMs}ms"
);

// Inspect stored record
$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `id` = ?");
$stmt->execute([$logId]);
$logRow = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Outbox status is 'pending'",
    ($logRow['status'] ?? '') === 'pending'
);

assertTest(
    "Full HTML payload is persisted in email_logs",
    !empty($logRow['html_body']) && str_contains($logRow['html_body'], 'Testing outbox queue latency.')
);

assertTest(
    "Plain text fallback payload is persisted",
    !empty($logRow['text_body']) && str_contains($logRow['text_body'], 'Testing outbox queue latency.')
);

assertTest(
    "next_attempt_at is initialized to current time or past",
    !empty($logRow['next_attempt_at']) && strtotime($logRow['next_attempt_at']) <= time() + 5
);

// ============================================================================
// SECTION 2: Idempotency & Duplicate Prevention
// ============================================================================
echo "\n--- Section 2: Idempotency & Duplicate Prevention ---\n";

// Attempt duplicate queuing of identical event
$duplicateLogId = $emailService->queue(
    $testEmail,
    'Benchmark Tester',
    'Benchmark Test Email Subject',
    '<p>Testing outbox queue latency.</p>',
    null,
    'test_queue',
    'test',
    $testRunBase + 1
);

assertTest(
    "Duplicate queue request rejected upfront (returns null)",
    $duplicateLogId === null
);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `email_logs` WHERE `recipient_email` = ? AND `email_type` = 'test_queue'");
$countStmt->execute([$testEmail]);
$count = (int)$countStmt->fetchColumn();

assertTest(
    "Outbox contains exactly 1 entry for entity (no duplicate row created)",
    $count === 1
);

// ============================================================================
// SECTION 3: Business Event Queuing Methods
// ============================================================================
echo "\n--- Section 3: Business Event Queuing Methods ---\n";

// A. Booking Confirmed
$mockBooking = [
    'id'             => $testRunBase + 10,
    'booking_code'   => 'BK-CONF-' . ($testRunBase + 10),
    'customer_id'    => 1,
    'pickup_address' => 'Jaipur Airport, Terminal 2',
    'drop_address'   => 'ITC Rajputana, Jaipur',
    'start_date'     => '2026-11-20',
    'pickup_time'    => '10:00:00',
    'total_amount'   => 5000.00,
    'customer_name'  => 'Test Customer',
    'customer_email' => 'cust_conf_' . time() . '_' . random_int(100, 999) . '@example.com',
    'customer_phone' => '9876543210'
];

$confId = $emailService->queueBookingConfirmed($mockBooking);
assertTest("queueBookingConfirmed creates outbox record", is_int($confId) && $confId > 0, "Conf ID: " . var_export($confId, true));

// B. Booking Cancelled (distinct booking ID)
$mockBookingCancel = [
    'id'             => $testRunBase + 20,
    'booking_code'   => 'BK-CANC-' . ($testRunBase + 20),
    'customer_id'    => 1,
    'pickup_address' => 'Delhi Terminal 3',
    'drop_address'   => 'The Leela, Gurugram',
    'start_date'     => '2026-11-22',
    'pickup_time'    => '11:00:00',
    'total_amount'   => 4500.00,
    'customer_name'  => 'Cancel Guest',
    'customer_email' => 'cust_canc_' . time() . '_' . random_int(100, 999) . '@example.com',
    'customer_phone' => '9876543211'
];
$cancId = $emailService->queueBookingCancelled($mockBookingCancel, 'Customer requested schedule adjustment');
assertTest("queueBookingCancelled creates outbox record", is_int($cancId) && $cancId > 0, "Canc ID: " . var_export($cancId, true));

// C. Payment Successful
$mockPayment = [
    'id'                => $testRunBase + 30,
    'payment_reference' => 'PAY-TEST-' . ($testRunBase + 30),
    'amount'            => 2500.00,
    'invoice_id'        => null
];
$mockInvoice = [
    'id'             => $testRunBase + 31,
    'invoice_number' => 'INV-TEST-' . ($testRunBase + 31),
    'customer_name'  => 'Payer Test',
    'customer_email' => 'payer_' . time() . '_' . random_int(100, 999) . '@example.com',
    'total_amount'   => 5000.00,
    'paid_amount'    => 2500.00
];
$payLogId = $emailService->queuePaymentSuccessful($mockPayment, $mockInvoice);
assertTest("queuePaymentSuccessful creates outbox record", is_int($payLogId) && $payLogId > 0, "Pay ID: " . var_export($payLogId, true));

// D. Driver Assignment (distinct dispatch ID)
$mockDispatch = ['id' => $testRunBase + 40, 'dispatch_code' => 'DSP-TEST-' . ($testRunBase + 40), 'booking_id' => $testRunBase + 10];
$mockVehicle = ['id' => 1, 'registration_number' => 'MH-31-TEST', 'model_name' => 'Innova Crysta'];
$mockDriver = [
    'id'         => $testRunBase + 41,
    'first_name' => 'Ramesh',
    'last_name'  => 'Driver',
    'email'      => 'driver_assigned_' . time() . '_' . random_int(100, 999) . '@example.com',
    'phone'      => '9988776655'
];
$dispatchQueue = $emailService->queueDriverAssigned($mockDispatch, $mockBooking, $mockVehicle, $mockDriver);
assertTest(
    "queueDriverAssigned queues both driver and customer notifications non-blockingly",
    is_array($dispatchQueue) && is_int($dispatchQueue['driver']) && is_int($dispatchQueue['customer']),
    "Result: " . json_encode($dispatchQueue)
);

// ============================================================================
// SECTION 4: Atomic Batch Claiming & Concurrency Protection
// ============================================================================
echo "\n--- Section 4: Atomic Batch Claiming & Concurrency Locking ---\n";

$workerIdA = 'worker_test_alpha';
$workerIdB = 'worker_test_beta';

// Worker A claims batch of 5 items
$batchA = $emailLog->claimPendingBatch($workerIdA, 5, 300);
assertTest("Worker A claimed batch of pending emails", count($batchA) > 0);

if (!empty($batchA)) {
    $firstClaimedId = (int)$batchA[0]['id'];
    $chkStmt = $pdo->prepare("SELECT `status`, `locked_by`, `locked_at` FROM `email_logs` WHERE `id` = ?");
    $chkStmt->execute([$firstClaimedId]);
    $claimedRow = $chkStmt->fetch(PDO::FETCH_ASSOC);

    assertTest(
        "Claimed item status transitioned to 'processing'",
        $claimedRow['status'] === 'processing'
    );
    assertTest(
        "Claimed item locked_by matches Worker A ID",
        $claimedRow['locked_by'] === $workerIdA
    );
    assertTest(
        "Claimed item locked_at timestamp is set",
        !empty($claimedRow['locked_at'])
    );

    // Worker B immediately attempts to claim with same batch
    // Because Worker A already locked those rows, Worker B should NOT receive any of Worker A's claimed rows
    $batchB = $emailLog->claimPendingBatch($workerIdB, 5, 300);
    $claimedIdsA = array_column($batchA, 'id');
    $claimedIdsB = array_column($batchB, 'id');
    $intersection = array_intersect($claimedIdsA, $claimedIdsB);

    assertTest(
        "Worker B claimed 0 overlapping items with Worker A (safe atomic isolation)",
        empty($intersection),
        "Overlapping IDs: " . implode(', ', $intersection)
    );
}

// ============================================================================
// SECTION 5: Stale Processing Lock Auto-Recovery
// ============================================================================
echo "\n--- Section 5: Stale Lock Auto-Recovery ---\n";

// Insert an artificial crashed worker item locked 10 minutes ago
$staleWorkerId = 'worker_dead_process';
$staleEmail = 'stale_lock_' . time() . '@example.com';
$staleLogId = $emailLog->queueRecord(
    $staleEmail,
    'Stale Target',
    'test_stale',
    'Stale Lock Subject',
    '<p>Stale lock</p>'
);

// Manually simulate a crashed worker holding lock 10 minutes ago
$tenMinAgo = date('Y-m-d H:i:s', time() - 600);
$pdo->prepare("UPDATE `email_logs` SET `status` = 'processing', `locked_by` = ?, `locked_at` = ? WHERE `id` = ?")
    ->execute([$staleWorkerId, $tenMinAgo, $staleLogId]);

// New active worker runs claimPendingBatch with 300s timeout
$recoveredBatch = $emailLog->claimPendingBatch('worker_recovery_agent', 10, 300);
$recoveredIds = array_column($recoveredBatch, 'id');

assertTest(
    "Crashed worker item (>5m old) was automatically recovered and claimed by active worker",
    in_array($staleLogId, $recoveredIds, true)
);

// ============================================================================
// SECTION 6: Retry Delay Schedule & Backoff Calculations
// ============================================================================
echo "\n--- Section 6: Tiered Exponential Retry Backoff Schedule ---\n";

assertTest("1st retry backoff is 60 seconds (1 minute)", EmailLog::calculateRetryDelay(1) === 60);
assertTest("2nd retry backoff is 300 seconds (5 minutes)", EmailLog::calculateRetryDelay(2) === 300);
assertTest("3rd retry backoff is 900 seconds (15 minutes)", EmailLog::calculateRetryDelay(3) === 900);
assertTest("4th retry backoff is 1800 seconds (30 minutes)", EmailLog::calculateRetryDelay(4) === 1800);
assertTest("5th retry backoff is 1800 seconds (30 minutes)", EmailLog::calculateRetryDelay(5) === 1800);

// Test failure progression on a real record
$failEmail = 'retry_progression_' . time() . '@example.com';
$retryTestId = $emailLog->queueRecord($failEmail, 'Retry User', 'test_retry', 'Retry Test', '<p>Test</p>');

// Simulate attempt 1 failure
$emailLog->markFailed($retryTestId, 'Temporary SMTP timeout on connect');
$stmt = $pdo->prepare("SELECT `status`, `attempt_count`, `next_attempt_at`, `error_message` FROM `email_logs` WHERE `id` = ?");
$stmt->execute([$retryTestId]);
$attempt1 = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Attempt 1 failure keeps status 'pending' for retry",
    $attempt1['status'] === 'pending'
);
assertTest(
    "attempt_count incremented to 1",
    (int)$attempt1['attempt_count'] === 1
);
assertTest(
    "next_attempt_at scheduled in the future (~60s)",
    strtotime($attempt1['next_attempt_at']) >= (time() + 50)
);

// Simulate failures up to max attempts (5)
$pdo->prepare("UPDATE `email_logs` SET `attempt_count` = 4 WHERE `id` = ?")->execute([$retryTestId]);
$emailLog->markFailed($retryTestId, 'Final SMTP 550 User Unknown');

$stmt->execute([$retryTestId]);
$finalAttempt = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Item marked permanently 'failed' after exceeding max attempts (5)",
    $finalAttempt['status'] === 'failed'
);
assertTest(
    "attempt_count equals 5",
    (int)$finalAttempt['attempt_count'] === 5
);

// ============================================================================
// SECTION 7: Worker Heartbeat Telemetry & Health Monitoring
// ============================================================================
echo "\n--- Section 7: Worker Heartbeat Telemetry & Health Check ---\n";

$testWorkerUid = 'worker_telemetry_unit_' . time();
$emailLog->updateWorkerHeartbeat($testWorkerUid, 15, 1, 'running');

$health = $emailLog->getWorkerHealth();
assertTest("getWorkerHealth detects active worker", $health['is_active'] === true);
assertTest("getWorkerHealth reports is_healthy true", $health['is_healthy'] === true);
assertTest("Active worker heartbeat timestamp recorded", !empty($health['last_heartbeat_at']));

// Mark worker stopped
$emailLog->updateWorkerHeartbeat($testWorkerUid, 0, 0, 'stopped');

// Set all workers in table to stale heartbeat (> 300s ago)
$staleTime = date('Y-m-d H:i:s', time() - 400);
$pdo->prepare("UPDATE `email_worker_heartbeats` SET `last_heartbeat_at` = ?")->execute([$staleTime]);

$staleHealth = $emailLog->getWorkerHealth();
assertTest(
    "getWorkerHealth reports is_active false when heartbeats are stale (>120s)",
    $staleHealth['is_active'] === false
);

// ============================================================================
// SECTION 8: CLI Worker Execution (--health & --once)
// ============================================================================
echo "\n--- Section 8: CLI Worker Invocation ---\n";

// Execute bin/email_worker.php --health
$healthOutput = [];
$healthCode = 0;
exec('E:\\xampp\\php\\php.exe bin/email_worker.php --health', $healthOutput, $healthCode);

assertTest("Worker CLI --health exited with code 0", $healthCode === 0);
assertTest(
    "Worker CLI --health output contains diagnostic table",
    str_contains(implode("\n", $healthOutput), 'Health Check')
);

// Execute bin/email_worker.php --once
$onceOutput = [];
$onceCode = 0;
exec('E:\\xampp\\php\\php.exe bin/email_worker.php --once', $onceOutput, $onceCode);

assertTest("Worker CLI --once exited with code 0", $onceCode === 0);
assertTest(
    "Worker CLI --once output shows successful completion",
    str_contains(implode("\n", $onceOutput), 'Worker stopped')
);

// ============================================================================
// SECTION 9: Security Redaction & Password Masking
// ============================================================================
echo "\n--- Section 9: Security & Credential Redaction ---\n";

$secretTestId = $emailLog->queueRecord(
    'redact_test_' . time() . '@example.com',
    'Secret User',
    'test_redact',
    'Redaction Test',
    '<p>Testing redaction</p>'
);

// Simulate an SMTP exception containing raw credentials
$rawSecretError = 'SMTP Error: password=SuperSecretAppPassword123! authentication failed on port 465';
$emailLog->markFailed($secretTestId, $rawSecretError);

$stmt = $pdo->prepare("SELECT `error_message` FROM `email_logs` WHERE `id` = ?");
$stmt->execute([$secretTestId]);
$storedError = (string)$stmt->fetchColumn();

assertTest(
    "SMTP passwords in error traces are automatically redacted in outbox table",
    !str_contains($storedError, 'SuperSecretAppPassword123!') && str_contains($storedError, 'password=[REDACTED]'),
    "Stored error: {$storedError}"
);

// Clean up temporary benchmark test records
$pdo->prepare("DELETE FROM `email_logs` WHERE `recipient_email` LIKE '%@example.com'")->execute();
$pdo->prepare("DELETE FROM `email_worker_heartbeats` WHERE `worker_id` LIKE 'worker_test_%'")->execute();

echo "\n====================================================================\n";
echo " Test Suite Summary\n";
echo " Total Assertions : {$totalTests}\n";
echo " Passed           : {$passed}\n";
echo " Failed           : {$failed}\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
