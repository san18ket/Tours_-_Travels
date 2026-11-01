<?php
/**
 * Migration 031: Create push_subscriptions table for Web Push Notifications
 * Supports multiple devices per user, subscription revocation, and device lifecycle management
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'push_subscriptions'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `push_subscriptions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `endpoint` TEXT NOT NULL,
                `endpoint_hash` CHAR(64) NOT NULL,
                `p256dh` VARCHAR(255) NOT NULL,
                `auth` VARCHAR(255) NOT NULL,
                `content_encoding` VARCHAR(50) NOT NULL DEFAULT 'aes128gcm',
                `device_name` VARCHAR(150) NULL,
                `last_used_at` DATETIME NULL,
                `revoked_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_endpoint_hash` (`endpoint_hash`),
                INDEX `idx_push_user_id` (`user_id`),
                INDEX `idx_push_revoked_at` (`revoked_at`),
                INDEX `idx_push_created_at` (`created_at`),
                CONSTRAINT `fk_push_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `push_subscriptions`");
    }
};
