<?php
/**
 * Migration 015: Add employee_id linkage to driver_profiles
 */

return new class {
    public function up(PDO $pdo): void
    {
        $colCheck = $pdo->query("SHOW COLUMNS FROM `driver_profiles` LIKE 'employee_id'");
        if (!$colCheck->fetch()) {
            $pdo->exec("ALTER TABLE `driver_profiles` ADD COLUMN `employee_id` INT NULL AFTER `user_id`");
            $pdo->exec("ALTER TABLE `driver_profiles` ADD UNIQUE KEY `idx_driver_employee_id` (`employee_id`)");
            $pdo->exec("ALTER TABLE `driver_profiles` ADD CONSTRAINT `fk_driver_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL");
        }
    }

    public function down(PDO $pdo): void
    {
        $colCheck = $pdo->query("SHOW COLUMNS FROM `driver_profiles` LIKE 'employee_id'");
        if ($colCheck->fetch()) {
            $pdo->exec("ALTER TABLE `driver_profiles` DROP FOREIGN KEY `fk_driver_employee`");
            $pdo->exec("ALTER TABLE `driver_profiles` DROP COLUMN `employee_id`");
        }
    }
};
