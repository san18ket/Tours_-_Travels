<?php
/**
 * Migration 034: Create company_profiles table for single-tenant installation company management & branding
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'company_profiles'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `company_profiles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(150) NOT NULL,
                `tagline` VARCHAR(255) NULL,
                `logo` VARCHAR(255) NULL,
                `email` VARCHAR(150) NOT NULL,
                `phone` VARCHAR(50) NULL,
                `website` VARCHAR(255) NULL,
                `address` TEXT NULL,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_company_status` (`status`),
                INDEX `idx_company_primary` (`is_primary`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `company_profiles`");
    }
};
