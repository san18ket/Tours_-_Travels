<?php
/**
 * Migration 048: Create user_notification_preferences table for responsive Office notification settings
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'user_notification_preferences'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `user_notification_preferences` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `preferences` LONGTEXT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_notif_pref_user` (`user_id`),
                INDEX `idx_notif_pref_user` (`user_id`),
                CONSTRAINT `fk_notif_pref_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `user_notification_preferences`");
    }
};

