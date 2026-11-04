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
        'reference_type',
        'reference_id',
        'idempotency_hash',
        'message_id',
        'status',
        'retry_count',
        'last_attempt_at',
        'error_message',
        'created_at',
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
     * Check if a duplicate successful email was already sent
     *
     * @param string $idempotencyHash
     * @param int $cooldownMinutes 0 means lifetime uniqueness (never send twice for the same ref & type)
     */
    public function hasDuplicate(string $idempotencyHash, int $cooldownMinutes = 0): bool
    {
        if ($cooldownMinutes > 0) {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ? 
                      AND `created_at` >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$idempotencyHash, $cooldownMinutes]);
        } else {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ?";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$idempotencyHash]);
        }

        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Record an outgoing email dispatch attempt
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
        ?string $messageId = null
    ): int {
        if ($idempotencyHash === null) {
            $idempotencyHash = self::makeHash($emailType, $referenceType, $referenceId, $recipientEmail);
        }

        // Sanitize error message: never store SMTP credentials or passwords
        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending'], true) ? $status : ($status === 'sent' ? 'sent' : 'failed');

        return $this->create([
            'recipient_email'  => strtolower(trim($recipientEmail)),
            'recipient_name'   => $recipientName,
            'to_email'         => strtolower(trim($recipientEmail)),
            'to_name'          => $recipientName,
            'email_type'       => $emailType,
            'subject'          => $subject,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'idempotency_hash' => $idempotencyHash,
            'message_id'       => $messageId,
            'status'           => $validStatus,
            'retry_count'      => 0,
            'last_attempt_at'  => date('Y-m-d H:i:s'),
            'error_message'    => $sanitizedError,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Record a retry attempt on an existing log
     */
    public function recordRetry(int $logId, string $status, ?string $errorMessage = null): bool
    {
        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending'], true) ? $status : 'failed';

        $stmt = $this->getPdo()->prepare("UPDATE `{$this->table}` SET `status` = ?, `retry_count` = `retry_count` + 1, `last_attempt_at` = NOW(), `error_message` = ? WHERE `id` = ?");
        return $stmt->execute([$validStatus, $sanitizedError, $logId]);
    }

    /**
     * Update status and error message on an existing log (e.g. from pending to sent/failed)
     */
    public function updateStatus(int $logId, string $status, ?string $errorMessage = null): bool
    {
        $sanitizedError = null;
        if (!empty($errorMessage)) {
            $sanitizedError = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $errorMessage);
            $sanitizedError = substr($sanitizedError, 0, 1000);
        }

        $validStatus = in_array($status, ['sent', 'failed', 'skipped', 'pending'], true) ? $status : 'failed';

        $stmt = $this->getPdo()->prepare("UPDATE `{$this->table}` SET `status` = ?, `last_attempt_at` = NOW(), `error_message` = ? WHERE `id` = ?");
        return $stmt->execute([$validStatus, $sanitizedError, $logId]);
    }

    /**
     * Retrieve recent email logs for administrative view
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->all("id DESC LIMIT " . (int)$limit);
    }
}
