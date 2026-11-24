<?php
/**
 * Migration 035: Enhance Finance Module
 * Additive migration — adds expense categories table, refund_date, and account tracking for customer payments.
 * Preserves all existing tables and historical data.
 */

return new class {
    public function up(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Add account_id to payments table if not exists
        $colCheck = $pdo->query("SHOW COLUMNS FROM `payments` LIKE 'account_id'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE `payments` ADD COLUMN `account_id` INT NULL AFTER `invoice_id`, ADD INDEX `idx_pay_account` (`account_id`)");
        }

        // 2. Add refund_date to refunds table if not exists
        $refColCheck = $pdo->query("SHOW COLUMNS FROM `refunds` LIKE 'refund_date'")->fetch();
        if (!$refColCheck) {
            $pdo->exec("ALTER TABLE `refunds` ADD COLUMN `refund_date` DATE NULL AFTER `amount`");
            $pdo->exec("UPDATE `refunds` SET `refund_date` = DATE(created_at) WHERE `refund_date` IS NULL");
        }

        // 3. Widen business_expenses category to VARCHAR(100) to support dynamic categories
        try {
            $pdo->exec("ALTER TABLE `business_expenses` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT 'misc'");
        } catch (\Throwable $e) {
            // Already modified or compatible
        }

        // 4. Create expense_categories table
        $sqlCat = "CREATE TABLE IF NOT EXISTS `expense_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(60) NOT NULL UNIQUE,
            `name` VARCHAR(100) NOT NULL,
            `description` VARCHAR(255) NULL,
            `is_default` TINYINT(1) NOT NULL DEFAULT 0,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_expcat_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlCat);

        // Seed default categories
        $defaultCategories = [
            ['slug' => 'office_rent',      'name' => 'Office Rent',              'description' => 'Commercial office, branch premise rent & lease dues'],
            ['slug' => 'utilities',        'name' => 'Electricity and Internet', 'description' => 'Office electricity, broadband, mobile telephony, water & utility bills'],
            ['slug' => 'fuel_bulk',        'name' => 'Fuel and Travel',          'description' => 'Bulk diesel, petrol, highway toll taxes & travel operational fuel'],
            ['slug' => 'maintenance',      'name' => 'Vehicle Maintenance',      'description' => 'Fleet engine servicing, brake overhaul, tyres, insurance & RTO fitness'],
            ['slug' => 'driver_allowance', 'name' => 'Employee Allowances',      'description' => 'Driver daily food/night halt bata, travel DA & field allowances'],
            ['slug' => 'marketing',        'name' => 'Marketing and Advertising','description' => 'Google ads, local pamphlets, hoardings, signage & social media promotions'],
            ['slug' => 'office_supplies',  'name' => 'Office Supplies',          'description' => 'Printing stationery, passenger water bottles, sanitizers & pantry supplies'],
            ['slug' => 'software_saas',    'name' => 'Software & Subscriptions', 'description' => 'GPS tracking API, accounting software, domain & cloud servers'],
            ['slug' => 'legal_accounting', 'name' => 'Legal & Accounting',       'description' => 'Auditor fees, GST filing charges, commercial legal consultations'],
            ['slug' => 'misc',             'name' => 'Other Expenses',           'description' => 'General petty operational disbursements & miscellaneous overheads'],
        ];

        $checkCatStmt = $pdo->prepare("SELECT id FROM `expense_categories` WHERE `slug` = ? LIMIT 1");
        $insCatStmt = $pdo->prepare("INSERT INTO `expense_categories` (`slug`, `name`, `description`, `is_default`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, 1, 'active', ?, ?)");

        foreach ($defaultCategories as $cat) {
            $checkCatStmt->execute([$cat['slug']]);
            if (!$checkCatStmt->fetchColumn()) {
                $insCatStmt->execute([$cat['slug'], $cat['name'], $cat['description'], $now, $now]);
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe additive migration
    }
};
