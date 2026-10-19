<?php
/**
 * Migration 010: Create Vehicle Maintenance and Service Records Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `vehicle_maintenance` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `vehicle_id` INT NOT NULL,
            `maintenance_type` ENUM('scheduled_service', 'repair', 'inspection', 'tyre_replacement', 'bodywork') NOT NULL DEFAULT 'scheduled_service',
            `odometer_km` INT NOT NULL,
            `cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `service_center` VARCHAR(150) NOT NULL,
            `description` TEXT NOT NULL,
            `performed_at` DATE NOT NULL,
            `next_service_due_km` INT NULL,
            `created_by` INT NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_maint_vehicle` (`vehicle_id`),
            INDEX `idx_maint_date` (`performed_at`),
            CONSTRAINT `fk_maint_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_maint_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `vehicle_maintenance`;");
    }
};
