<?php
/**
 * Migration 030: Create notifications table for in-app and professional alerts
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `notifications` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `employee_id` INT NULL,
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `type` VARCHAR(50) NOT NULL DEFAULT 'info',
                `reference_type` VARCHAR(50) NULL,
                `reference_id` INT NULL,
                `channel` VARCHAR(50) NOT NULL DEFAULT 'in_app',
                `data` LONGTEXT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `read_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_notif_user_id` (`user_id`),
                INDEX `idx_notif_employee_id` (`employee_id`),
                INDEX `idx_notif_is_read` (`is_read`),
                INDEX `idx_notif_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `notifications`");
    }
};
