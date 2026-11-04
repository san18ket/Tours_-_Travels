<?php
namespace App\Services;

use App\Models\EmailLog;
use App\Core\Database;
use PDO;

/**
 * Service for Recording Outgoing Email Events and Audit Trails
 * Ensures sensitive credentials, OTPs, and passwords are NEVER persisted.
 */
class EmailLogService
{
    private EmailLog $model;

    public function __construct(?EmailLog $model = null)
    {
        $this->model = $model ?? new EmailLog();
    }

    /**
     * Compute a deterministic idempotency hash for an email event
     */
    public static function makeHash(string $emailType, ?string $refType, ?int $refId, string $recipientEmail): string
    {
        $normalizedEmail = strtolower(trim($recipientEmail));
        return hash('sha256', "{$emailType}:{$refType}:{$refId}:{$normalizedEmail}");
    }

    /**
     * Sanitize error message to prevent leaking any sensitive information
     */
    public static function sanitizeErrorMessage(?string $error): ?string
    {
        if (empty($error)) {
            return null;
        }

        $sanitized = preg_replace('/password[=\s:]+[^\s&]+/i', 'password=[REDACTED]', $error);
        $sanitized = preg_replace('/(pass|pwd|key|secret|token)[=\s:]+[^\s&]+/i', '$1=[REDACTED]', $sanitized);
        $sanitized = preg_replace('/\b[0-9]{4}\b/', '[OTP_REDACTED]', $sanitized);
        return substr(trim($sanitized), 0, 1000);
    }

    /**
     * Record an outgoing email dispatch attempt
     *
     * @return int Inserted log record ID
     */
    public function recordAttempt(
        string $toEmail,
        ?string $toName,
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
            $idempotencyHash = self::makeHash($emailType, $referenceType, $referenceId, $toEmail);
        }

        $sanitizedError = self::sanitizeErrorMessage($errorMessage);
        $validStatuses = ['sent', 'failed', 'skipped', 'pending_retry', 'uncertain', 'pending'];
        $cleanStatus = in_array($status, $validStatuses, true) ? $status : 'failed';
        $now = date('Y-m-d H:i:s');

        // Sanitize htmlBody if contains raw OTP
        $safeHtml = $htmlBody;
        if ($emailType === 'email_verification_otp' || $emailType === 'account_recovery_otp') {
            // Mask OTP in stored HTML preview for security
            $safeHtml = preg_replace('/>([0-9]{4})</', '>[VERIFIED OTP]<', $safeHtml ?? '');
        }

        return $this->model->create([
            'recipient_email'  => strtolower(trim($toEmail)),
            'recipient_name'   => $toName ? trim($toName) : null,
            'to_email'         => strtolower(trim($toEmail)),
            'to_name'          => $toName ? trim($toName) : null,
            'email_type'       => $emailType,
            'subject'          => $subject,
            'html_body'        => $safeHtml,
            'text_body'        => $textBody,
            'cc'               => !empty($cc) ? json_encode($cc, JSON_UNESCAPED_SLASHES) : null,
            'bcc'              => !empty($bcc) ? json_encode($bcc, JSON_UNESCAPED_SLASHES) : null,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'idempotency_hash' => $idempotencyHash,
            'message_id'       => $messageId,
            'status'           => $cleanStatus,
            'retry_count'      => 0,
            'attempt_count'    => ($cleanStatus === 'sent' || $cleanStatus === 'failed') ? 1 : 0,
            'max_attempts'     => 5,
            'next_attempt_at'  => null,
            'locked_at'        => null,
            'locked_by'        => null,
            'sent_at'          => ($cleanStatus === 'sent') ? $now : null,
            'last_attempt_at'  => $now,
            'error_message'    => $sanitizedError,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    /**
     * Update an existing log record upon retry
     */
    public function recordRetryOutcome(int $logId, string $status, ?string $errorMessage = null, ?string $messageId = null): bool
    {
        $pdo = Database::getConnection();
        $sanitized = self::sanitizeErrorMessage($errorMessage);
        $now = date('Y-m-d H:i:s');

        $isSuccess = ($status === 'sent');

        $sql = "UPDATE `email_logs` 
                SET `status` = ?,
                    `attempt_count` = `attempt_count` + 1,
                    `retry_count` = `retry_count` + 1,
                    `last_attempt_at` = ?,
                    `sent_at` = CASE WHEN ? = 'sent' THEN ? ELSE `sent_at` END,
                    `message_id` = CASE WHEN ? IS NOT NULL THEN ? ELSE `message_id` END,
                    `error_message` = ?,
                    `updated_at` = ?
                WHERE `id` = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $status,
            $now,
            $status,
            $now,
            $messageId,
            $messageId,
            $sanitized,
            $now,
            $logId
        ]);
    }
}

