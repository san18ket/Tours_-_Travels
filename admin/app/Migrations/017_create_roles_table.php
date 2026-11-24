<?php
/**
 * Migration 017: Create Dedicated Roles Table & Seed Standard Operational Roles
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create roles table
        $sql = "CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(80) NOT NULL UNIQUE,
            `slug` VARCHAR(80) NOT NULL UNIQUE,
            `description` VARCHAR(255) NULL,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_roles_slug` (`slug`),
            INDEX `idx_roles_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);

        // 2. Ensure users.role is VARCHAR(50) so newly added roles can be assigned seamlessly
        $colCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'");
        $colInfo = $colCheck->fetch(PDO::FETCH_ASSOC);
        if ($colInfo && str_starts_with(strtolower($colInfo['Type'] ?? ''), 'enum')) {
            $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'customer'");
        }

        // 3. Keep roles table blank like other tables - do not insert dummy records
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: no destructive drops
    }
};
