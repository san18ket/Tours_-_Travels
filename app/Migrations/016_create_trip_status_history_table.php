<?php
/**
 * Migration 016: Create trip_status_history Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `trip_status_history` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `dispatch_id` INT NOT NULL,
            `from_status` VARCHAR(50) NULL,
            `to_status` VARCHAR(50) NOT NULL,
            `changed_by_user_id` INT NULL,
            `latitude` DECIMAL(10, 8) NULL,
            `longitude` DECIMAL(11, 8) NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_tsh_dispatch` (`dispatch_id`),
            INDEX `idx_tsh_created` (`created_at`),
            INDEX `fk_tsh_user` (`changed_by_user_id`),
            CONSTRAINT `fk_tsh_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tsh_user` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `trip_status_history`");
    }
};
