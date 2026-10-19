<?php
/**
 * Migration 005: Create Dispatches & Fleet Assignments Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `dispatches` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `dispatch_code` VARCHAR(30) NOT NULL UNIQUE,
            `booking_id` INT NOT NULL UNIQUE,
            `vehicle_id` INT NOT NULL,
            `driver_id` INT NOT NULL,
            `assigned_by` INT NOT NULL,
            `status` ENUM('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned',
            `start_odometer` INT NULL,
            `end_odometer` INT NULL,
            `start_time` DATETIME NULL,
            `end_time` DATETIME NULL,
            `driver_notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_dispatch_status` (`status`),
            INDEX `idx_dispatch_driver` (`driver_id`),
            INDEX `idx_dispatch_vehicle` (`vehicle_id`),
            CONSTRAINT `fk_dispatch_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_dispatch_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_dispatch_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_dispatch_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void {}
};
