<?php
/**
 * Migration 028: Create Finance Module Tables
 * Creates accounts, ledger, business expenses, salary structures, payrolls, advances, refunds, and online transactions.
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Finance Accounts (Cash, Bank, Gateway)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `finance_accounts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `account_name` VARCHAR(100) NOT NULL,
            `account_number` VARCHAR(50) NULL,
            `bank_name` VARCHAR(100) NULL,
            `ifsc_code` VARCHAR(20) NULL,
            `account_type` ENUM('cash', 'bank', 'gateway') NOT NULL DEFAULT 'bank',
            `opening_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `current_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_account_type` (`account_type`),
            INDEX `idx_account_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Finance Ledger (Immutable transaction entries)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `finance_ledger` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `account_id` INT NOT NULL,
            `transaction_type` ENUM('credit', 'debit') NOT NULL,
            `amount` DECIMAL(12, 2) NOT NULL,
            `balance_after` DECIMAL(12, 2) NOT NULL,
            `category` VARCHAR(60) NOT NULL,
            `reference_type` VARCHAR(50) NULL,
            `reference_id` INT NULL,
            `payment_method` VARCHAR(30) NULL,
            `transaction_ref` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `created_by` INT NULL,
            `transacted_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_ledger_account` (`account_id`),
            INDEX `idx_ledger_type` (`transaction_type`),
            INDEX `idx_ledger_category` (`category`),
            INDEX `idx_ledger_transacted_at` (`transacted_at`),
            CONSTRAINT `fk_ledger_account` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Business Expenses
        $pdo->exec("CREATE TABLE IF NOT EXISTS `business_expenses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `expense_code` VARCHAR(30) NOT NULL UNIQUE,
            `category` ENUM('office_rent', 'utilities', 'maintenance', 'software_saas', 'marketing', 'fuel_bulk', 'driver_allowance', 'office_supplies', 'legal_accounting', 'misc') NOT NULL,
            `title` VARCHAR(150) NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `expense_date` DATE NOT NULL,
            `payment_method` ENUM('cash', 'bank_transfer', 'card', 'upi', 'cheque') NOT NULL DEFAULT 'bank_transfer',
            `account_id` INT NULL,
            `vendor_name` VARCHAR(100) NULL,
            `receipt_file` VARCHAR(255) NULL,
            `status` ENUM('pending', 'approved', 'paid', 'rejected') NOT NULL DEFAULT 'paid',
            `logged_by` INT NULL,
            `approved_by` INT NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_expense_category` (`category`),
            INDEX `idx_expense_status` (`status`),
            INDEX `idx_expense_date` (`expense_date`),
            CONSTRAINT `fk_expense_account` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Salary Structures
        $pdo->exec("CREATE TABLE IF NOT EXISTS `salary_structures` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL UNIQUE,
            `basic_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `hra` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `transport_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `medical_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `special_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `pf_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `esi_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `professional_tax` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `tds_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `other_deductions` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `gross_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `net_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `effective_date` DATE NOT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_salary_employee` (`employee_id`),
            CONSTRAINT `fk_salary_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Payrolls (Monthly Batches)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payrolls` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payroll_code` VARCHAR(30) NOT NULL UNIQUE,
            `month` TINYINT NOT NULL,
            `year` SMALLINT NOT NULL,
            `total_gross` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `total_deductions` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `total_net` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `status` ENUM('draft', 'approved', 'paid') NOT NULL DEFAULT 'draft',
            `payment_date` DATE NULL,
            `approved_by` INT NULL,
            `paid_by` INT NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_payroll_period` (`year`, `month`),
            INDEX `idx_payroll_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Payroll Items (Per-employee payslips)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payroll_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payroll_id` INT NOT NULL,
            `employee_id` INT NOT NULL,
            `payslip_number` VARCHAR(50) NOT NULL UNIQUE,
            `basic_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `allowances_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `gross_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `deductions_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `advance_deducted` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `net_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
            `payment_method` VARCHAR(30) NULL,
            `payment_ref` VARCHAR(100) NULL,
            `paid_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_payroll_item_payroll` (`payroll_id`),
            INDEX `idx_payroll_item_employee` (`employee_id`),
            INDEX `idx_payroll_item_status` (`payment_status`),
            CONSTRAINT `fk_payroll_item_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_payroll_item_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Employee Advances
        $pdo->exec("CREATE TABLE IF NOT EXISTS `employee_advances` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `advance_code` VARCHAR(30) NOT NULL UNIQUE,
            `employee_id` INT NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `status` ENUM('pending', 'approved', 'rejected', 'recovered') NOT NULL DEFAULT 'pending',
            `recovered_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `requested_date` DATE NOT NULL,
            `approved_by` INT NULL,
            `approved_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_advance_employee` (`employee_id`),
            INDEX `idx_advance_status` (`status`),
            CONSTRAINT `fk_advance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Refunds
        $pdo->exec("CREATE TABLE IF NOT EXISTS `refunds` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `refund_number` VARCHAR(50) NOT NULL UNIQUE,
            `booking_id` INT NULL,
            `invoice_id` INT NULL,
            `payment_id` INT NULL,
            `customer_id` INT NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `status` ENUM('pending', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'pending',
            `refund_method` ENUM('gateway', 'bank_transfer', 'cash', 'upi') NOT NULL DEFAULT 'bank_transfer',
            `gateway_refund_id` VARCHAR(100) NULL,
            `processed_by` INT NULL,
            `processed_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_refund_booking` (`booking_id`),
            INDEX `idx_refund_customer` (`customer_id`),
            INDEX `idx_refund_status` (`status`),
            CONSTRAINT `fk_refund_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_refund_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Online Transactions (Payment Gateways)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `online_transactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `gateway` VARCHAR(30) NOT NULL,
            `order_id` VARCHAR(100) NOT NULL,
            `payment_id` VARCHAR(100) NULL,
            `invoice_id` INT NULL,
            `customer_id` INT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
            `status` ENUM('created', 'authorized', 'captured', 'failed', 'refunded') NOT NULL DEFAULT 'created',
            `signature` VARCHAR(255) NULL,
            `error_code` VARCHAR(100) NULL,
            `error_description` TEXT NULL,
            `payload_json` LONGTEXT NULL,
            `webhook_event_id` VARCHAR(100) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_online_order` (`order_id`),
            INDEX `idx_online_payment` (`payment_id`),
            INDEX `idx_online_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `online_transactions`");
        $pdo->exec("DROP TABLE IF EXISTS `refunds`");
        $pdo->exec("DROP TABLE IF EXISTS `employee_advances`");
        $pdo->exec("DROP TABLE IF EXISTS `payroll_items`");
        $pdo->exec("DROP TABLE IF EXISTS `payrolls`");
        $pdo->exec("DROP TABLE IF EXISTS `salary_structures`");
        $pdo->exec("DROP TABLE IF EXISTS `business_expenses`");
        $pdo->exec("DROP TABLE IF EXISTS `finance_ledger`");
        $pdo->exec("DROP TABLE IF EXISTS `finance_accounts`");
    }
};

