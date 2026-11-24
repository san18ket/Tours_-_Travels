<?php
/**
 * Migration 002: Create Vehicles and Driver Profiles Tables
 */

return new class {
    public function up(PDO $pdo): void
    {
        // Vehicles Table
        $sqlVehicles = "CREATE TABLE IF NOT EXISTS `vehicles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `vehicle_number` VARCHAR(30) NOT NULL UNIQUE,
            `make_model` VARCHAR(100) NOT NULL,
            `vehicle_type` ENUM('sedan', 'suv', 'tempo_traveller', 'luxury_bus', 'van') NOT NULL DEFAULT 'suv',
            `seating_capacity` INT NOT NULL DEFAULT 4,
            `fuel_type` ENUM('diesel', 'petrol', 'cng', 'electric') NOT NULL DEFAULT 'diesel',
            `status` ENUM('available', 'on_trip', 'maintenance', 'retired') NOT NULL DEFAULT 'available',
            `odometer_km` INT NOT NULL DEFAULT 0,
            `registration_expiry` DATE NULL,
            `insurance_expiry` DATE NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_vehicle_status` (`status`),
            INDEX `idx_vehicle_type` (`vehicle_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlVehicles);

        // Driver Profiles Table
        $sqlDrivers = "CREATE TABLE IF NOT EXISTS `driver_profiles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL UNIQUE,
            `license_number` VARCHAR(50) NOT NULL UNIQUE,
            `license_expiry` DATE NOT NULL,
            `experience_years` INT NOT NULL DEFAULT 1,
            `emergency_contact` VARCHAR(30) NULL,
            `duty_status` ENUM('on_duty', 'off_duty', 'on_trip') NOT NULL DEFAULT 'off_duty',
            `rating` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
            `current_latitude` DECIMAL(10, 8) NULL,
            `current_longitude` DECIMAL(11, 8) NULL,
            `last_gps_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_driver_duty` (`duty_status`),
            CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlDrivers);
    }

    public function down(PDO $pdo): void {}
};
