<?php
/**
 * Migration 008: Create Audit Logs Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NULL,
            `action` VARCHAR(100) NOT NULL,
            `entity_type` VARCHAR(50) NOT NULL,
            `entity_id` INT NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
            INDEX `idx_audit_user` (`user_id`),
            INDEX `idx_audit_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void {}
};
