<?php
/**
 * Migration 014: Create Dedicated Employees Directory Table & Linkage to Driver Profiles
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create employees table
        $sql = "CREATE TABLE IF NOT EXISTS `employees` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_code` VARCHAR(30) NOT NULL UNIQUE,
            `user_id` INT NULL UNIQUE,
            `first_name` VARCHAR(80) NOT NULL,
            `last_name` VARCHAR(80) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `alternate_phone` VARCHAR(30) NULL,
            `email` VARCHAR(150) NULL,
            `department` VARCHAR(80) NOT NULL DEFAULT 'operations',
            `designation` VARCHAR(100) NOT NULL DEFAULT 'Operations Executive',
            `employment_type` ENUM('full_time','part_time','contract','probation') NOT NULL DEFAULT 'full_time',
            `joining_date` DATE NOT NULL,
            `status` ENUM('active','on_leave','terminated') NOT NULL DEFAULT 'active',
            `emergency_contact` VARCHAR(100) NULL,
            `address` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_employee_department` (`department`),
            INDEX `idx_employee_status` (`status`),
            INDEX `idx_employee_phone` (`phone`),
            CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);

        // 2. Link employee_id in driver_profiles if not present
        $colCheck = $pdo->query("SHOW COLUMNS FROM `driver_profiles` LIKE 'employee_id'");
        if (!$colCheck->fetch()) {
            $pdo->exec("ALTER TABLE `driver_profiles` ADD COLUMN `employee_id` INT NULL AFTER `user_id`");
            $pdo->exec("ALTER TABLE `driver_profiles` ADD UNIQUE KEY `employee_id` (`employee_id`)");
            $pdo->exec("ALTER TABLE `driver_profiles` ADD CONSTRAINT `fk_driver_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL");
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `employees`");
    }
};
