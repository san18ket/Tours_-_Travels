<?php
/**
 * Migration 001: Create Users Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `phone` VARCHAR(30) NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'dispatcher', 'driver', 'customer') NOT NULL DEFAULT 'customer',
            `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_users_role` (`role`),
            INDEX `idx_users_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: never drop tables automatically in production
    }
};
