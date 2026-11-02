<?php
/**
 * Migration 033: Create email_logs table for audit logging & duplicate send prevention
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'email_logs'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `email_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `recipient_email` VARCHAR(150) NOT NULL,
                `recipient_name` VARCHAR(100) NULL,
                `to_email` VARCHAR(150) NULL,
                `to_name` VARCHAR(100) NULL,
                `email_type` VARCHAR(50) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `reference_type` VARCHAR(50) NULL,
                `reference_id` INT NULL,
                `idempotency_hash` VARCHAR(64) NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'sent',
                `retry_count` INT NOT NULL DEFAULT 0,
                `last_attempt_at` DATETIME NULL,
                `error_message` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_email_logs_type` (`email_type`),
                INDEX `idx_email_logs_ref` (`reference_type`, `reference_id`),
                INDEX `idx_email_logs_recipient` (`recipient_email`),
                INDEX `idx_email_logs_hash` (`idempotency_hash`),
                INDEX `idx_email_logs_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `email_logs`");
    }
};
