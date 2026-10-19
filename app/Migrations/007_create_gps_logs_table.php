<?php
/**
 * Migration 007: Create GPS Logs Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `gps_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `dispatch_id` INT NOT NULL,
            `driver_id` INT NOT NULL,
            `latitude` DECIMAL(10, 8) NOT NULL,
            `longitude` DECIMAL(11, 8) NOT NULL,
            `speed_kmh` DECIMAL(5, 2) NULL DEFAULT 0.00,
            `heading_degrees` DECIMAL(5, 2) NULL,
            `battery_level` INT NULL,
            `recorded_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_gps_dispatch_time` (`dispatch_id`, `recorded_at`),
            INDEX `idx_gps_driver` (`driver_id`),
            CONSTRAINT `fk_gps_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_gps_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void {}
};
