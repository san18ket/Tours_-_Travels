<?php
declare(strict_types=1);

/**
 * ============================================================================
 * VoyageGo - Autonomous Database-Backed CLI Email Queue Worker
 * ============================================================================
 *
 * Dispatches pending outbox emails in the background with atomic row locking,
 * exponential retry scheduling, stale lock recovery, and heartbeat telemetry.
 *
 * Usage:
 *   php bin/email_worker.php [options]
 *
 * Options:
 *   --once          Process eligible due pending emails and exit (ideal for cron/Task Scheduler)
 *   --daemon        Run continuously in background polling loop
 *   --batch=<n>     Max emails to claim per batch (default: 10)
 *   --sleep=<sec>   Sleep seconds between polling cycles in daemon mode (default: 5)
 *   --health        Inspect and display current worker status, queue counts, and health
 *   --help, -h      Display this documentation and usage examples
 *
 * Examples:
 *   # Windows Scheduled Task / Linux Cron (runs every 1 minute)
 *   php bin/email_worker.php --once
 *
 *   # Dedicated long-running background service
 *   php bin/email_worker.php --daemon --batch=15 --sleep=3
 *
 *   # CLI Health Check
 *   php bin/email_worker.php --health
 * ============================================================================
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI execution only.\n";
    exit(1);
}

// Adjust error display for CLI
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Bootstrap VoyageGo core
require_once dirname(__DIR__) . '/app/bootstrap.php';

// CLI exception handler (prevents rendering HTML error templates in CLI)
set_exception_handler(function (\Throwable $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
});

use App\Models\EmailLog;
use App\Services\EmailService;
use App\Core\Logger;

// Parse CLI options
$options = getopt('h', ['once', 'daemon', 'batch:', 'sleep:', 'health', 'help']);

if (isset($options['help']) || isset($options['h'])) {
    printHelp();
    exit(0);
}

$emailLog = new EmailLog();
$emailService = new EmailService();

// Health Check Option
if (isset($options['health'])) {
    runHealthCheck($emailLog);
    exit(0);
}

$isDaemon = isset($options['daemon']);
$batchSize = isset($options['batch']) ? max(1, (int)$options['batch']) : 10;
$sleepSeconds = isset($options['sleep']) ? max(1, (int)$options['sleep']) : 5;

// Generate unique telemetry identity for this worker instance
$hostname = gethostname() ?: 'localhost';
$pid = getmypid() ?: 0;
$workerId = sprintf('worker_%s_%d_%s', preg_replace('/[^a-zA-Z0-9_\-]/', '', $hostname), $pid, bin2hex(random_bytes(3)));

$running = true;
$processedTotal = 0;
$failedTotal = 0;

// Setup signal handling for graceful shutdown where available
if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
    pcntl_async_signals(true);
    $shutdownHandler = function () use (&$running, $workerId, $emailLog, &$processedTotal, &$failedTotal) {
        cliLog("Graceful termination signal received. Stopping worker...", 'WARN');
        $running = false;
        $emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'stopped');
    };
    pcntl_signal(SIGTERM, $shutdownHandler);
    pcntl_signal(SIGINT, $shutdownHandler);
}

// Windows Control Break Handler if supported
if (function_exists('sapi_windows_set_ctrl_handler')) {
    sapi_windows_set_ctrl_handler(function () use (&$running, $workerId, $emailLog, &$processedTotal, &$failedTotal) {
        cliLog("Windows shutdown / break detected. Stopping worker...", 'WARN');
        $running = false;
        $emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'stopped');
        exit(0);
    });
}

// Ensure worker status is updated to stopped on normal process termination
register_shutdown_function(function () use ($workerId, $emailLog, &$processedTotal, &$failedTotal) {
    try {
        $emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'stopped');
    } catch (\Throwable $e) {
        // Suppress on shutdown
    }
});

// Banner
printBanner($workerId, $isDaemon ? 'Daemon (Continuous)' : 'Once (Single Run)', $batchSize, $sleepSeconds);

// Initial heartbeat
$emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'running');

do {
    try {
        // Update heartbeat at start of each iteration
        $emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'running');

        // Atomically claim pending eligible batch (also auto-recovers stale locks > 300s)
        $batch = $emailLog->claimPendingBatch($workerId, $batchSize, 300);
        $count = count($batch);

        if ($count > 0) {
            cliLog(sprintf("Claimed batch of %d outbox item(s)", $count), 'INFO');

            foreach ($batch as $logItem) {
                $start = microtime(true);
                $logId = (int)$logItem['id'];
                $to = $logItem['recipient_email'] ?? ($logItem['to_email'] ?? 'unknown');
                $type = $logItem['email_type'] ?? 'general';

                try {
                    $result = $emailService->processQueuedEmail($logItem);
                    $durationMs = round((microtime(true) - $start) * 1000);

                    if (!empty($result['success'])) {
                        $processedTotal++;
                        $msgId = $result['message_id'] ? " (MsgID: {$result['message_id']})" : '';
                        cliLog(sprintf("[OK] Outbox #%d | %s | %s | Sent%s [%dms]", $logId, $type, $to, $msgId, $durationMs), 'SUCCESS');
                    } else {
                        $failedTotal++;
                        $err = $result['error'] ?? 'Delivery failed';
                        cliLog(sprintf("[FAIL] Outbox #%d | %s | %s | %s [%dms]", $logId, $type, $to, $err, $durationMs), 'ERROR');
                    }
                } catch (\Throwable $itemErr) {
                    $failedTotal++;
                    $durationMs = round((microtime(true) - $start) * 1000);
                    $err = substr($itemErr->getMessage(), 0, 200);
                    $emailLog->markFailed($logId, $err);
                    cliLog(sprintf("[ERR] Outbox #%d | %s | %s | Exception: %s [%dms]", $logId, $type, $to, $err, $durationMs), 'ERROR');
                }

                // Update heartbeat during batch
                $emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'running');

                if (!$running) {
                    break;
                }
            }
        } else {
            if (!$isDaemon) {
                cliLog("No due pending emails in queue.", 'INFO');
            }
        }

        // If --once mode, repeat loop if there were items and more might be immediately due, otherwise exit
        if (!$isDaemon) {
            if ($count === 0) {
                break;
            }
            continue;
        }

        // In daemon mode, sleep before next poll
        if ($running) {
            sleep($sleepSeconds);
        }

    } catch (\Throwable $loopErr) {
        cliLog("Worker cycle error: " . $loopErr->getMessage(), 'ERROR');
        Logger::error("EmailWorker exception: " . $loopErr->getMessage(), ['trace' => $loopErr->getTraceAsString()]);
        if ($isDaemon && $running) {
            sleep($sleepSeconds);
        } else {
            break;
        }
    }

} while ($running && $isDaemon);

// Mark stopped upon completion
$emailLog->updateWorkerHeartbeat($workerId, $processedTotal, $failedTotal, 'stopped');
cliLog(sprintf("Worker stopped. Total processed: %d, Total failed: %d", $processedTotal, $failedTotal), 'INFO');
exit(0);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function cliLog(string $message, string $level = 'INFO'): void
{
    $time = date('Y-m-d H:i:s');
    $color = match ($level) {
        'SUCCESS' => "\033[32m",
        'WARN'    => "\033[33m",
        'ERROR'   => "\033[31m",
        default   => "\033[36m",
    };
    $reset = "\033[0m";

    // Detect if output supports ANSI colors
    if (DIRECTORY_SEPARATOR === '\\' && !getenv('ANSICON') && !getenv('WT_SESSION')) {
        // Windows CMD without ANSI
        echo sprintf("[%s] [%s] %s\n", $time, $level, $message);
    } else {
        echo sprintf("%s[%s]%s %s[%s]%s %s\n", $color, $time, $reset, $color, $level, $reset, $message);
    }
}

function printBanner(string $workerId, string $mode, int $batchSize, int $sleep): void
{
    echo "====================================================================\n";
    echo " VoyageGo Background Email Outbox Worker\n";
    echo "====================================================================\n";
    echo " Worker ID   : {$workerId}\n";
    echo " Run Mode    : {$mode}\n";
    echo " Batch Size  : {$batchSize}\n";
    if (str_contains($mode, 'Daemon')) {
        echo " Sleep Int.  : {$sleep}s\n";
    }
    echo " Started At  : " . date('Y-m-d H:i:s') . "\n";
    echo "====================================================================\n\n";
}

function printHelp(): void
{
    echo <<<HELP
VoyageGo Email Outbox Worker (CLI)
==================================

Processes background queued emails stored in `email_logs` outbox table.

Usage:
  php bin/email_worker.php [options]

Options:
  --once            Process currently eligible due pending emails and exit immediately.
                    Recommended for Windows Task Scheduler or Linux Cron (e.g. every 1-5 mins).

  --daemon          Run continuously as a persistent background service daemon.
                    Polls for pending emails every --sleep seconds.

  --batch=<n>       Maximum number of emails to claim and process per batch. (Default: 10)

  --sleep=<sec>     Interval in seconds to sleep between polls when in --daemon mode. (Default: 5)

  --health          Check and display worker heartbeat status and queue diagnostics.

  --help, -h        Show this help screen.

Exit Codes:
  0                 Success / Clean termination
  1                 General error

HELP;
}

function runHealthCheck(EmailLog $emailLog): void
{
    $health = $emailLog->getWorkerHealth();
    $counts = $emailLog->getQueueCounts();

    echo "====================================================================\n";
    echo " VoyageGo Email Outbox & Worker Health Check\n";
    echo "====================================================================\n";
    echo " Checked At       : " . date('Y-m-d H:i:s') . "\n";
    echo " Worker Status    : " . ($health['is_healthy'] ? "HEALTHY [ACTIVE]" : "INACTIVE / STALE") . "\n";
    echo " Active Workers   : " . ($health['active_workers_count'] ?? 0) . "\n";
    echo " Last Heartbeat   : " . ($health['last_heartbeat_at'] ?? 'Never') . "\n";
    echo "--------------------------------------------------------------------\n";
    echo " Queue Diagnostics:\n";
    echo "   Pending Due    : " . ($counts['pending'] ?? 0) . "\n";
    echo "   In Processing  : " . ($counts['processing'] ?? 0) . "\n";
    echo "   Sent (24h)     : " . ($counts['sent_24h'] ?? 0) . "\n";
    echo "   Failed (24h)   : " . ($counts['failed_24h'] ?? 0) . "\n";
    echo "====================================================================\n";
}
