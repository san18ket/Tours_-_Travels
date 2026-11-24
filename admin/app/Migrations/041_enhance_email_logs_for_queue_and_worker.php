<?php
/**
 * Migration 041: Enhance email_logs for reliable background queue, outbox payload storage,
 * retry schedule, row locking, and worker heartbeat telemetry.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // 1. Add queue payload and tracking columns to email_logs
        $cols = [
            'html_body'       => "ALTER TABLE `email_logs` ADD COLUMN `html_body` MEDIUMTEXT NULL AFTER `subject`",
            'text_body'       => "ALTER TABLE `email_logs` ADD COLUMN `text_body` TEXT NULL AFTER `html_body`",
            'cc'              => "ALTER TABLE `email_logs` ADD COLUMN `cc` TEXT NULL AFTER `text_body`",
            'bcc'             => "ALTER TABLE `email_logs` ADD COLUMN `bcc` TEXT NULL AFTER `cc`",
            'attempt_count'   => "ALTER TABLE `email_logs` ADD COLUMN `attempt_count` INT NOT NULL DEFAULT 0 AFTER `retry_count`",
            'max_attempts'    => "ALTER TABLE `email_logs` ADD COLUMN `max_attempts` INT NOT NULL DEFAULT 5 AFTER `attempt_count`",
            'next_attempt_at' => "ALTER TABLE `email_logs` ADD COLUMN `next_attempt_at` DATETIME NULL AFTER `max_attempts`",
            'locked_at'       => "ALTER TABLE `email_logs` ADD COLUMN `locked_at` DATETIME NULL AFTER `next_attempt_at`",
            'locked_by'       => "ALTER TABLE `email_logs` ADD COLUMN `locked_by` VARCHAR(100) NULL AFTER `locked_at`",
            'sent_at'         => "ALTER TABLE `email_logs` ADD COLUMN `sent_at` DATETIME NULL AFTER `locked_by`",
            'updated_at'      => "ALTER TABLE `email_logs` ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
        ];

        foreach ($cols as $colName => $alterSql) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `email_logs` LIKE '{$colName}'");
            if (!$stmt->fetch()) {
                $pdo->exec($alterSql);
            }
        }

        // Synchronize attempt_count from existing retry_count
        $pdo->exec("UPDATE `email_logs` SET `attempt_count` = `retry_count` WHERE `attempt_count` = 0 AND `retry_count` > 0");
        $pdo->exec("UPDATE `email_logs` SET `sent_at` = `created_at` WHERE `status` = 'sent' AND `sent_at` IS NULL");

        // 2. Add indexing for high-performance outbox polling and status filtering
        $indexes = [
            'idx_email_logs_queue'  => "ALTER TABLE `email_logs` ADD INDEX `idx_email_logs_queue` (`status`, `next_attempt_at`, `locked_at`)",
            'idx_email_logs_status' => "ALTER TABLE `email_logs` ADD INDEX `idx_email_logs_status` (`status`)",
        ];

        foreach ($indexes as $idxName => $indexSql) {
            $stmt = $pdo->query("SHOW INDEX FROM `email_logs` WHERE Key_name = '{$idxName}'");
            if (!$stmt->fetch()) {
                try {
                    $pdo->exec($indexSql);
                } catch (\Throwable $e) {
                    // Index may already exist or duplicate
                }
            }
        }

        // 3. Create email_worker_heartbeats table for worker health monitoring
        $pdo->exec("CREATE TABLE IF NOT EXISTS `email_worker_heartbeats` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `worker_id` VARCHAR(100) NOT NULL UNIQUE,
            `hostname` VARCHAR(100) NULL,
            `pid` INT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'running',
            `processed_count` INT NOT NULL DEFAULT 0,
            `failed_count` INT NOT NULL DEFAULT 0,
            `last_processed_at` DATETIME NULL,
            `last_heartbeat_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_worker_heartbeat` (`last_heartbeat_at`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `email_worker_heartbeats`");
    }
};

