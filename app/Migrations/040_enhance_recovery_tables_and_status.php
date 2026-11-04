<?php
/**
 * Migration 040: Enhance recovery tables, status lifecycle, and email diagnostics
 * Adds 'draft' and 'failed' statuses to email_verifications for safe OTP activation.
 * Adds message_id to email_logs for delivery tracking.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // 1. Expand email_verifications.status ENUM
        $pdo->exec("ALTER TABLE `email_verifications` 
                    MODIFY COLUMN `status` ENUM('draft', 'pending', 'verified', 'consumed', 'invalidated', 'failed') NOT NULL DEFAULT 'draft'");

        // 2. Add message_id to email_logs if missing
        $stmt = $pdo->query("SHOW COLUMNS FROM `email_logs` LIKE 'message_id'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_logs` 
                        ADD COLUMN `message_id` VARCHAR(255) NULL DEFAULT NULL AFTER `idempotency_hash`,
                        ADD INDEX `idx_email_logs_msg_id` (`message_id`)");
        }

        // 3. Add composite index on email_logs for entity reference lookups
        $idxStmt = $pdo->query("SHOW INDEX FROM `email_logs` WHERE Key_name = 'idx_email_logs_entity_ref'");
        if (!$idxStmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_logs` 
                        ADD INDEX `idx_email_logs_entity_ref` (`email_type`, `reference_type`, `reference_id`)");
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `email_verifications` 
                    MODIFY COLUMN `status` ENUM('pending', 'verified', 'consumed', 'invalidated') NOT NULL DEFAULT 'pending'");

        $stmt = $pdo->query("SHOW COLUMNS FROM `email_logs` LIKE 'message_id'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_logs` DROP COLUMN `message_id`");
        }

        $idxStmt = $pdo->query("SHOW INDEX FROM `email_logs` WHERE Key_name = 'idx_email_logs_entity_ref'");
        if ($idxStmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_logs` DROP INDEX `idx_email_logs_entity_ref`");
        }
    }
};

