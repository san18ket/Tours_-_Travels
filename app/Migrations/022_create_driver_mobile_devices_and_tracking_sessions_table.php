<?php
/**
 * Migration 022: Create Driver Mobile Devices and Tracking Sessions Tables
 * Supports native mobile app authentication, revocable refresh tokens, device registration, and session audits
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. driver_mobile_devices
        $sqlDevices = "CREATE TABLE IF NOT EXISTS `driver_mobile_devices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `driver_id` INT NOT NULL,
            `device_uuid` VARCHAR(100) NOT NULL,
            `device_name` VARCHAR(150) NULL,
            `platform` ENUM('android', 'ios') NOT NULL DEFAULT 'android',
            `os_version` VARCHAR(50) NULL,
            `app_version` VARCHAR(50) NULL,
            `access_token` VARCHAR(255) NOT NULL UNIQUE,
            `access_token_expires_at` DATETIME NOT NULL,
            `refresh_token` VARCHAR(255) NOT NULL UNIQUE,
            `refresh_token_expires_at` DATETIME NOT NULL,
            `is_revoked` TINYINT(1) NOT NULL DEFAULT 0,
            `last_active_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_dmd_driver` (`driver_id`),
            INDEX `idx_dmd_device_uuid` (`device_uuid`),
            INDEX `idx_dmd_access_token` (`access_token`),
            INDEX `idx_dmd_refresh_token` (`refresh_token`),
            CONSTRAINT `fk_dmd_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlDevices);

        // 2. tracking_sessions
        $sqlSessions = "CREATE TABLE IF NOT EXISTS `tracking_sessions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `driver_id` INT NOT NULL,
            `dispatch_id` INT NULL,
            `device_id` INT NULL,
            `session_uuid` VARCHAR(64) NOT NULL UNIQUE,
            `status` ENUM('active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
            `started_at` DATETIME NOT NULL,
            `ended_at` DATETIME NULL,
            `points_count` INT NOT NULL DEFAULT 0,
            `distance_km` DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_ts_driver` (`driver_id`),
            INDEX `idx_ts_dispatch` (`dispatch_id`),
            INDEX `idx_ts_status` (`status`),
            CONSTRAINT `fk_ts_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ts_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_ts_device` FOREIGN KEY (`device_id`) REFERENCES `driver_mobile_devices` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlSessions);
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: no destructive drops
    }
};
