<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class EmailLog extends Model
{
    protected string $table = 'email_logs';
    protected array $fillable = [
        'recipient_email',
        'recipient_name',
        'to_email',
        'to_name',
        'email_type',
        'subject',
        'html_body',
        'text_body',
        'cc',
        'bcc',
        'reference_type',
        'reference_id',
        'idempotency_hash',
        'message_id',
        'status',
        'retry_count',
        'attempt_count',
        'max_attempts',
        'next_attempt_at',
        'locked_at',
        'locked_by',
        'sent_at',
        'last_attempt_at',
        'error_message',
        'created_at',
        'updated_at',
    ];

    /**
     * Compute a deterministic idempotency hash for an email event
     */
    public static function makeHash(string $emailType, ?string $refType, ?int $refId, string $recipientEmail): string
    {
        $normalizedEmail = strtolower(trim($recipientEmail));
        return hash('sha256', "{$emailType}:{$refType}:{$refId}:{$normalizedEmail}");
    }

    /**
     * Check if a duplicate email was already queued, processing, or sent
     *
     * @param string $idempotencyHash
     * @param int $cooldownMinutes 0 means lifetime uniqueness (never send twice for the same ref & type)
     */
    public function hasDuplicate(string $idempotencyHash, int $cooldownMinutes = 0): bool
    {
        if ($cooldownMinutes > 0) {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ? 
                      AND `status` IN ('sent', 'pending', 'processing')
                      AND `created_at` >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$idempotencyHash, $cooldownMinutes]);
        } else {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ?
                      AND `status` IN ('sent', 'pending', 'processing')";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$idempotencyHash]);
        }

        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Record an outgoing email dispatch attempt (sync or direct)
     */
    public function record(
        string $recipientEmail,
        ?string $recipientName,
        string $emailType,
        string $subject,
        ?string $referenceType,
        ?int $referenceId,
        string $status,
        ?string $errorMessage = null,
        ?string $idempotencyHash = null,
        ?string $messageId = null,
        ?string $htmlBody = null,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = []
    ): int {
        if ($idempotencyHash === null) {
            $idempotencyHash = self::makeHash($emailType, $referenceType, $referenceId, $recipientEmail);
        }

        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending', 'processing'], true) ? $status : 'failed';
        $now = date('Y-m-d H:i:s');

        return $this->create([
            'recipient_email'  => strtolower(trim($recipientEmail)),
            'recipient_name'   => $recipientName,
            'to_email'         => strtolower(trim($recipientEmail)),
            'to_name'          => $recipientName,
            'email_type'       => $emailType,
            'subject'          => $subject,
            'html_body'        => $htmlBody,
            'text_body'        => $textBody,
            'cc'               => !empty($cc) ? json_encode($cc, JSON_UNESCAPED_SLASHES) : null,
            'bcc'              => !empty($bcc) ? json_encode($bcc, JSON_UNESCAPED_SLASHES) : null,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'idempotency_hash' => $idempotencyHash,
            'message_id'       => $messageId,
            'status'           => $validStatus,
            'retry_count'      => 0,
            'attempt_count'    => ($validStatus === 'sent' || $validStatus === 'failed') ? 1 : 0,
            'max_attempts'     => 5,
            'next_attempt_at'  => ($validStatus === 'pending') ? $now : null,
            'locked_at'        => null,
            'locked_by'        => null,
            'sent_at'          => ($validStatus === 'sent') ? $now : null,
            'last_attempt_at'  => ($validStatus === 'sent' || $validStatus === 'failed') ? $now : null,
            'error_message'    => $sanitizedError,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    /**
     * Insert a message into the database outbox in pending status with full payload
     */
    public function queueRecord(
        string $recipientEmail,
        ?string $recipientName,
        string $emailType,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $cc = [],
        array $bcc = [],
        ?string $idempotencyHash = null,
        int $maxAttempts = 5
    ): int {
        if ($idempotencyHash === null) {
            $idempotencyHash = self::makeHash($emailType, $referenceType, $referenceId, $recipientEmail);
        }

        $now = date('Y-m-d H:i:s');

        return $this->create([
            'recipient_email'  => strtolower(trim($recipientEmail)),
            'recipient_name'   => $recipientName,
            'to_email'         => strtolower(trim($recipientEmail)),
            'to_name'          => $recipientName,
            'email_type'       => $emailType,
            'subject'          => $subject,
            'html_body'        => $htmlBody,
            'text_body'        => $textBody,
            'cc'               => !empty($cc) ? json_encode($cc, JSON_UNESCAPED_SLASHES) : null,
            'bcc'              => !empty($bcc) ? json_encode($bcc, JSON_UNESCAPED_SLASHES) : null,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'idempotency_hash' => $idempotencyHash,
            'message_id'       => null,
            'status'           => 'pending',
            'retry_count'      => 0,
            'attempt_count'    => 0,
            'max_attempts'     => $maxAttempts,
            'next_attempt_at'  => $now,
            'locked_at'        => null,
            'locked_by'        => null,
            'sent_at'          => null,
            'last_attempt_at'  => null,
            'error_message'    => null,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    /**
     * Atomically claim a batch of due pending emails for an active worker
     * Recovers stale processing locks (> lockTimeoutSeconds)
     *
     * @return array List of claimed email_logs records
     */
    public function claimPendingBatch(string $workerId, int $batchSize = 10, int $lockTimeoutSeconds = 300): array
    {
        $pdo = $this->getPdo();
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $pdo->beginTransaction();

                // 1. Recover stale locks if any worker crashed
                $staleThreshold = date('Y-m-d H:i:s', time() - $lockTimeoutSeconds);
                $recoverStmt = $pdo->prepare("UPDATE `{$this->table}` 
                                              SET `status` = 'pending', `locked_at` = NULL, `locked_by` = NULL 
                                              WHERE `status` = 'processing' AND `locked_at` < ?");
                $recoverStmt->execute([$staleThreshold]);

                // 2. Select eligible pending IDs
                $selectSql = "SELECT `id` FROM `{$this->table}` 
                              WHERE `status` = 'pending' 
                                AND (`next_attempt_at` IS NULL OR `next_attempt_at` <= NOW())
                                AND (`locked_at` IS NULL OR `locked_at` < ?)
                              ORDER BY `id` ASC 
                              LIMIT ? FOR UPDATE";
                $selectStmt = $pdo->prepare($selectSql);
                $selectStmt->bindValue(1, $staleThreshold);
                $selectStmt->bindValue(2, $batchSize, PDO::PARAM_INT);
                $selectStmt->execute();

                $ids = $selectStmt->fetchAll(PDO::FETCH_COLUMN);

                if (empty($ids)) {
                    $pdo->commit();
                    return [];
                }

                // 3. Atomically claim selected IDs
                $inClause = implode(',', array_map('intval', $ids));
                $now = date('Y-m-d H:i:s');
                $claimSql = "UPDATE `{$this->table}` 
                             SET `status` = 'processing', `locked_at` = ?, `locked_by` = ?, `updated_at` = ? 
                             WHERE `id` IN ({$inClause})";
                $claimStmt = $pdo->prepare($claimSql);
                $claimStmt->execute([$now, $workerId, $now]);

                $pdo->commit();

                // 4. Fetch the claimed records outside the transaction
                $fetchSql = "SELECT * FROM `{$this->table}` WHERE `id` IN ({$inClause}) ORDER BY `id` ASC";
                return $pdo->query($fetchSql)->fetchAll(PDO::FETCH_ASSOC);

            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // If deadlock (1213 / 40001) and attempts remaining, retry after brief jitter
                if ($attempts < $maxAttempts && str_contains($e->getMessage(), 'Deadlock')) {
                    usleep(random_int(10000, 50000)); // 10-50ms jitter
                    continue;
                }
                throw $e;
            }
        }

        return [];
    }

    /**
     * Mark an outbox email as successfully sent
     */
    public function markSent(int $logId, ?string $messageId = null): bool
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->getPdo()->prepare(
            "UPDATE `{$this->table}` 
             SET `status` = 'sent', 
                 `sent_at` = ?, 
                 `message_id` = ?, 
                 `last_attempt_at` = ?, 
                 `locked_at` = NULL, 
                 `locked_by` = NULL, 
                 `updated_at` = ? 
             WHERE `id` = ?"
        );
        return $stmt->execute([$now, $messageId, $now, $now, $logId]);
    }

    /**
     * Calculate retry delay based on attempt count
     * Schedule: 1st retry = 1 min, 2nd = 5 min, 3rd = 15 min, 4th+ = 30 min
     */
    public static function calculateRetryDelay(int $attemptCount): int
    {
        return match ($attemptCount) {
            0, 1 => 60,       // 1 minute
            2    => 300,      // 5 minutes
            3    => 900,      // 15 minutes
            default => 1800,  // 30 minutes
        };
    }

    /**
     * Record a failure on an outbox email and schedule next retry or mark permanently failed
     */
    public function markFailed(int $logId, string $error, int $nextDelaySeconds = 0, bool $permanent = false): bool
    {
        $sanitized = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $error);
        $sanitized = substr($sanitized, 0, 1000);
        $now = date('Y-m-d H:i:s');

        // Fetch current attempt count and max attempts
        $stmt = $this->getPdo()->prepare("SELECT `attempt_count`, `max_attempts` FROM `{$this->table}` WHERE `id` = ?");
        $stmt->execute([$logId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $newAttempts = ($row ? (int)$row['attempt_count'] : 0) + 1;
        $maxAttempts = $row ? (int)$row['max_attempts'] : 5;

        if ($permanent || $newAttempts >= $maxAttempts) {
            // Permanently failed
            $updateSql = "UPDATE `{$this->table}` 
                          SET `status` = 'failed', 
                              `attempt_count` = ?, 
                              `retry_count` = ?, 
                              `last_attempt_at` = ?, 
                              `error_message` = ?, 
                              `locked_at` = NULL, 
                              `locked_by` = NULL, 
                              `updated_at` = ? 
                          WHERE `id` = ?";
            $updateStmt = $this->getPdo()->prepare($updateSql);
            return $updateStmt->execute([$newAttempts, $newAttempts, $now, $sanitized, $now, $logId]);
        }

        // Schedule next retry
        $delay = $nextDelaySeconds > 0 ? $nextDelaySeconds : self::calculateRetryDelay($newAttempts);
        $nextAttemptAt = date('Y-m-d H:i:s', time() + $delay);

        $updateSql = "UPDATE `{$this->table}` 
                      SET `status` = 'pending', 
                          `attempt_count` = ?, 
                          `retry_count` = ?, 
                          `next_attempt_at` = ?, 
                          `last_attempt_at` = ?, 
                          `error_message` = ?, 
                          `locked_at` = NULL, 
                          `locked_by` = NULL, 
                          `updated_at` = ? 
                      WHERE `id` = ?";
        $updateStmt = $this->getPdo()->prepare($updateSql);
        return $updateStmt->execute([$newAttempts, $newAttempts, $nextAttemptAt, $now, $sanitized, $now, $logId]);
    }

    /**
     * Record a retry attempt on an existing log (for backward compatibility)
     */
    public function recordRetry(int $logId, string $status, ?string $errorMessage = null): bool
    {
        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending', 'processing'], true) ? $status : 'failed';
        $now = date('Y-m-d H:i:s');

        $stmt = $this->getPdo()->prepare("UPDATE `{$this->table}` SET `status` = ?, `retry_count` = `retry_count` + 1, `attempt_count` = `attempt_count` + 1, `last_attempt_at` = ?, `error_message` = ?, `locked_at` = NULL, `locked_by` = NULL, `updated_at` = ? WHERE `id` = ?");
        return $stmt->execute([$validStatus, $now, $sanitizedError, $now, $logId]);
    }

    /**
     * Update status and error message on an existing log
     */
    public function updateStatus(int $logId, string $status, ?string $errorMessage = null): bool
    {
        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending', 'processing'], true) ? $status : 'failed';
        $now = date('Y-m-d H:i:s');
        $sentAt = ($validStatus === 'sent') ? $now : null;

        $stmt = $this->getPdo()->prepare("UPDATE `{$this->table}` SET `status` = ?, `sent_at` = COALESCE(`sent_at`, ?), `last_attempt_at` = ?, `error_message` = ?, `locked_at` = NULL, `locked_by` = NULL, `updated_at` = ? WHERE `id` = ?");
        return $stmt->execute([$validStatus, $sentAt, $now, $sanitizedError, $now, $logId]);
    }

    /**
     * Retrieve recent email logs for administrative view
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->all("id DESC LIMIT " . (int)$limit);
    }

    /**
     * Retrieve filtered email logs with pagination
     */
    public function getFilteredLogs(?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $pdo = $this->getPdo();
        $params = [];
        $whereSql = "";

        if ($status && in_array($status, ['pending', 'processing', 'sent', 'failed', 'skipped'], true)) {
            $whereSql = "WHERE `status` = ?";
            $params[] = $status;
        }

        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY `id` DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $idx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($idx++, $p);
        }
        $stmt->bindValue($idx++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get aggregate counts of emails across different queue statuses
     */
    public function getQueueCounts(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT `status`, COUNT(*) as cnt FROM `{$this->table}` GROUP BY `status`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'total'      => 0,
            'pending'    => 0,
            'processing' => 0,
            'sent'       => 0,
            'failed'     => 0,
            'skipped'    => 0,
        ];

        foreach ($rows as $r) {
            $st = $r['status'];
            $c = (int)$r['cnt'];
            if (isset($counts[$st])) {
                $counts[$st] = $c;
            }
            $counts['total'] += $c;
        }

        // Get oldest pending email time
        $oldestStmt = $pdo->query("SELECT `created_at` FROM `{$this->table}` WHERE `status` = 'pending' ORDER BY `id` ASC LIMIT 1");
        $counts['oldest_pending_at'] = $oldestStmt->fetchColumn() ?: null;

        return $counts;
    }

    /**
     * Record worker heartbeat
     */
    public function updateWorkerHeartbeat(
        string $workerId,
        int $processedCount = 0,
        int $failedCount = 0,
        string $status = 'running'
    ): void {
        $pdo = $this->getPdo();
        $hostname = gethostname() ?: 'localhost';
        $pid = getmypid() ?: 0;
        $now = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO `email_worker_heartbeats` 
            (`worker_id`, `hostname`, `pid`, `status`, `processed_count`, `failed_count`, `last_processed_at`, `last_heartbeat_at`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                `status` = VALUES(`status`),
                `processed_count` = `processed_count` + VALUES(`processed_count`),
                `failed_count` = `failed_count` + VALUES(`failed_count`),
                `last_processed_at` = IF(VALUES(`processed_count`) > 0, VALUES(`last_processed_at`), `last_processed_at`),
                `last_heartbeat_at` = VALUES(`last_heartbeat_at`),
                `updated_at` = VALUES(`updated_at`)");

        $stmt->execute([
            $workerId,
            $hostname,
            $pid,
            $status,
            $processedCount,
            $failedCount,
            $processedCount > 0 ? $now : null,
            $now,
            $now,
            $now
        ]);
    }

    /**
     * Check current worker health across registered heartbeats
     */
    public function getWorkerHealth(): array
    {
        $pdo = $this->getPdo();
        // A worker is considered active if heartbeat was within last 120 seconds
        $threshold = date('Y-m-d H:i:s', time() - 120);

        $stmt = $pdo->prepare("SELECT * FROM `email_worker_heartbeats` WHERE `last_heartbeat_at` >= ? AND `status` IN ('running', 'active') ORDER BY `last_heartbeat_at` DESC LIMIT 1");
        $stmt->execute([$threshold]);
        $activeWorker = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastAnyStmt = $pdo->query("SELECT * FROM `email_worker_heartbeats` ORDER BY `last_heartbeat_at` DESC LIMIT 1");
        $lastWorker = $lastAnyStmt->fetch(PDO::FETCH_ASSOC);

        $queueCounts = $this->getQueueCounts();

        $isActive = !empty($activeWorker);

        return [
            'is_active'          => $isActive,
            'is_healthy'         => $isActive,
            'active_worker'      => $activeWorker ?: null,
            'active_workers_count' => $isActive ? 1 : 0,
            'last_heartbeat_at'  => $activeWorker['last_heartbeat_at'] ?? ($lastWorker['last_heartbeat_at'] ?? null),
            'last_processed_at'  => $activeWorker['last_processed_at'] ?? ($lastWorker['last_processed_at'] ?? null),
            'pending_count'      => $queueCounts['pending'],
            'failed_count'       => $queueCounts['failed'],
            'processing_count'   => $queueCounts['processing'],
            'sent_count'         => $queueCounts['sent'],
            'oldest_pending_at'  => $queueCounts['oldest_pending_at'],
        ];
    }
}
