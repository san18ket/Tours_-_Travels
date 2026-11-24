<?php
/**
 * Migration 050: Create trip_evidence table
 * Stores vehicle inspection photos, meter readings, GPS fixes, and audit metadata
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `trip_evidence` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `dispatch_id` INT NOT NULL,
            `driver_id` INT NOT NULL,
            `evidence_type` ENUM('start_odometer', 'start_vehicle', 'end_odometer', 'end_vehicle', 'additional') NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `original_filename` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` INT NOT NULL,
            `odometer_reading` INT NULL,
            `captured_at` DATETIME NULL,
            `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `latitude` DECIMAL(10, 8) NULL,
            `longitude` DECIMAL(11, 8) NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_te_dispatch` (`dispatch_id`),
            INDEX `idx_te_driver` (`driver_id`),
            INDEX `idx_te_type` (`evidence_type`),
            CONSTRAINT `fk_te_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_te_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `trip_evidence`;");
    }
};

