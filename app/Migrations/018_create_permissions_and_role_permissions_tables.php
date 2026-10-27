<?php
/**
 * Migration 018: Create Permissions, Role Permissions & User Permissions Tables
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create permissions table
        $sqlPermissions = "CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `module` VARCHAR(50) NOT NULL,
            `action` VARCHAR(50) NOT NULL,
            `description` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_permissions_module` (`module`),
            INDEX `idx_permissions_action` (`action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlPermissions);

        // 2. Create role_permissions junction table
        $sqlRolePermissions = "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `role_id` INT NOT NULL,
            `permission_id` INT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_role_perm` (`role_id`, `permission_id`),
            INDEX `idx_rp_role_id` (`role_id`),
            INDEX `idx_rp_permission_id` (`permission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlRolePermissions);

        // 3. Create user_permissions table (for explicit user overrides)
        $sqlUserPermissions = "CREATE TABLE IF NOT EXISTS `user_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `permission_id` INT NOT NULL,
            `is_granted` TINYINT(1) NOT NULL DEFAULT 1,
            `expires_at` DATETIME NULL,
            `max_usage_count` INT NULL,
            `usage_count` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_perm` (`user_id`, `permission_id`),
            INDEX `idx_up_user_id` (`user_id`),
            INDEX `idx_up_permission_id` (`permission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlUserPermissions);

        // 4. Keep permissions, role_permissions, and user_permissions blank like other tables - do not insert dummy records
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: no destructive drops
    }
};
