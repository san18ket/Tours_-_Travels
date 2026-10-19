<?php
/**
 * Migration 013: Remove Preferred Contact Method and Travel Profile Columns from Customers
 * Streamlines customer profile schema by dropping:
 * - preferred_contact_method
 * - customer_type (and its index)
 * - company_name
 * - preferred_travel_category
 * - notes
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Drop index on customer_type if present
        $indexCheck = $pdo->query("SHOW INDEX FROM `customers` WHERE Key_name = 'idx_customers_type'")->fetch();
        if ($indexCheck) {
            $pdo->exec("ALTER TABLE `customers` DROP INDEX `idx_customers_type`");
        }

        // 2. Drop columns if present
        $columnsToDrop = [
            'preferred_contact_method',
            'customer_type',
            'company_name',
            'preferred_travel_category',
            'notes',
        ];

        foreach ($columnsToDrop as $col) {
            $check = $pdo->query("SHOW COLUMNS FROM `customers` LIKE '{$col}'")->fetch();
            if ($check) {
                $pdo->exec("ALTER TABLE `customers` DROP COLUMN `{$col}`");
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Additive recreation if rollback is ever executed
        $pdo->exec("ALTER TABLE `customers`
            ADD COLUMN `preferred_contact_method` ENUM('mobile', 'whatsapp', 'email') NOT NULL DEFAULT 'mobile' AFTER `email`,
            ADD COLUMN `customer_type` ENUM('individual', 'corporate') NOT NULL DEFAULT 'individual' AFTER `country`,
            ADD COLUMN `company_name` VARCHAR(150) NULL AFTER `customer_type`,
            ADD COLUMN `preferred_travel_category` VARCHAR(50) NULL AFTER `company_name`,
            ADD COLUMN `notes` TEXT NULL AFTER `preferred_travel_category`,
            ADD INDEX `idx_customers_type` (`customer_type`)
        ");
    }
};
