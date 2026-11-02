<?php
/**
 * Migration 028: Create Finance Management Module Tables & Seed Finance Permissions
 * Additive migration — preserves all existing tables and historical data.
 */

return new class {
    public function up(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Finance Accounts (Cash vaults, Bank accounts, Gateway settlement accounts)
        $sqlAccounts = "CREATE TABLE IF NOT EXISTS `finance_accounts` (
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
            INDEX `idx_acc_type` (`account_type`),
            INDEX `idx_acc_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlAccounts);

        // Seed default finance accounts if table empty
        $accCount = (int)$pdo->query("SELECT COUNT(*) FROM `finance_accounts`")->fetchColumn();
        if ($accCount === 0) {
            $pdo->prepare("INSERT INTO `finance_accounts` (`account_name`, `account_number`, `bank_name`, `ifsc_code`, `account_type`, `opening_balance`, `current_balance`, `status`, `created_at`, `updated_at`) VALUES
                ('Primary Cash Vault', 'CASH-MAIN-01', 'In-Office Cash Box', NULL, 'cash', 25000.00, 25000.00, 'active', ?, ?),
                ('HDFC Business Current Account', '50200012345678', 'HDFC Bank Ltd', 'HDFC0001234', 'bank', 150000.00, 150000.00, 'active', ?, ?),
                ('Razorpay Settlement Account', 'RZP-SETTLE-01', 'Razorpay Payment Gateway', NULL, 'gateway', 0.00, 0.00, 'active', ?, ?)
            ")->execute([$now, $now, $now, $now, $now, $now]);
        }

        // 2. Finance Ledger (Double-entry Cash & Bank transaction ledger)
        $sqlLedger = "CREATE TABLE IF NOT EXISTS `finance_ledger` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `account_id` INT NOT NULL,
            `transaction_type` ENUM('credit', 'debit') NOT NULL,
            `amount` DECIMAL(12, 2) NOT NULL,
            `balance_after` DECIMAL(12, 2) NOT NULL,
            `category` VARCHAR(60) NOT NULL,
            `reference_type` VARCHAR(50) NULL,
            `reference_id` INT NULL,
            `payment_method` VARCHAR(30) NOT NULL DEFAULT 'cash',
            `transaction_ref` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `created_by` INT NULL,
            `transacted_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_ledger_acc` (`account_id`),
            INDEX `idx_ledger_cat` (`category`),
            INDEX `idx_ledger_ref` (`reference_type`, `reference_id`),
            INDEX `idx_ledger_date` (`transacted_at`),
            CONSTRAINT `fk_ledger_account` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlLedger);

        // 3. Salary Structures (Employee compensation breakdown)
        $sqlSalaryStructures = "CREATE TABLE IF NOT EXISTS `salary_structures` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL UNIQUE,
            `basic_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `hra` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `transport_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `medical_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `special_allowance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `pf_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `esi_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `professional_tax` DECIMAL(10, 2) NOT NULL DEFAULT 200.00,
            `tds_deduction` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `other_deductions` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `gross_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `net_salary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `effective_date` DATE NOT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_salary_emp` (`employee_id`),
            CONSTRAINT `fk_salary_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlSalaryStructures);

        // 4. Employee Advances (Salary advances & recovery tracking)
        $sqlAdvances = "CREATE TABLE IF NOT EXISTS `employee_advances` (
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
            INDEX `idx_advance_emp` (`employee_id`),
            INDEX `idx_advance_status` (`status`),
            CONSTRAINT `fk_advance_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlAdvances);

        // 5. Payrolls (Monthly payroll processing batches)
        $sqlPayrolls = "CREATE TABLE IF NOT EXISTS `payrolls` (
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
            UNIQUE KEY `uk_month_year` (`month`, `year`),
            INDEX `idx_payroll_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlPayrolls);

        // 6. Payroll Items (Individual Employee Payslips)
        $sqlPayrollItems = "CREATE TABLE IF NOT EXISTS `payroll_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payroll_id` INT NOT NULL,
            `employee_id` INT NOT NULL,
            `payslip_number` VARCHAR(50) NOT NULL UNIQUE,
            `basic_salary` DECIMAL(10, 2) NOT NULL,
            `allowances_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `gross_salary` DECIMAL(10, 2) NOT NULL,
            `deductions_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `advance_deducted` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `net_salary` DECIMAL(10, 2) NOT NULL,
            `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
            `payment_method` VARCHAR(30) NULL,
            `payment_ref` VARCHAR(100) NULL,
            `paid_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_pitem_payroll` (`payroll_id`),
            INDEX `idx_pitem_emp` (`employee_id`),
            INDEX `idx_pitem_status` (`payment_status`),
            CONSTRAINT `fk_pitem_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pitem_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlPayrollItems);

        // 7. Business Expenses (Operational company costs beyond trip fuel/toll)
        $sqlExpenses = "CREATE TABLE IF NOT EXISTS `business_expenses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `expense_code` VARCHAR(30) NOT NULL UNIQUE,
            `category` ENUM('office_rent', 'utilities', 'maintenance', 'software_saas', 'marketing', 'fuel_bulk', 'driver_allowance', 'office_supplies', 'legal_accounting', 'misc') NOT NULL DEFAULT 'misc',
            `title` VARCHAR(150) NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `expense_date` DATE NOT NULL,
            `payment_method` ENUM('cash', 'bank_transfer', 'card', 'upi', 'cheque') NOT NULL DEFAULT 'bank_transfer',
            `account_id` INT NULL,
            `vendor_name` VARCHAR(100) NULL,
            `receipt_file` VARCHAR(255) NULL,
            `status` ENUM('pending', 'approved', 'paid', 'rejected') NOT NULL DEFAULT 'approved',
            `logged_by` INT NOT NULL,
            `approved_by` INT NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_exp_category` (`category`),
            INDEX `idx_exp_date` (`expense_date`),
            INDEX `idx_exp_status` (`status`),
            CONSTRAINT `fk_bexp_account` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_bexp_logger` FOREIGN KEY (`logged_by`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlExpenses);

        // 8. Refunds (Customer refund management)
        $sqlRefunds = "CREATE TABLE IF NOT EXISTS `refunds` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `refund_number` VARCHAR(50) NOT NULL UNIQUE,
            `booking_id` INT NULL,
            `invoice_id` INT NOT NULL,
            `payment_id` INT NULL,
            `customer_id` INT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `status` ENUM('pending', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'pending',
            `refund_method` ENUM('gateway', 'bank_transfer', 'cash', 'upi') NOT NULL DEFAULT 'gateway',
            `gateway_refund_id` VARCHAR(100) NULL,
            `processed_by` INT NULL,
            `processed_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_refund_inv` (`invoice_id`),
            INDEX `idx_refund_cust` (`customer_id`),
            INDEX `idx_refund_status` (`status`),
            CONSTRAINT `fk_refund_inv` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlRefunds);

        // 9. Online Transactions (Razorpay & Online gateway tracking)
        $sqlOnline = "CREATE TABLE IF NOT EXISTS `online_transactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `gateway` VARCHAR(30) NOT NULL DEFAULT 'razorpay',
            `order_id` VARCHAR(100) NOT NULL,
            `payment_id` VARCHAR(100) NULL,
            `invoice_id` INT NOT NULL,
            `customer_id` INT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
            `status` ENUM('created', 'authorized', 'captured', 'failed', 'refunded') NOT NULL DEFAULT 'created',
            `signature` VARCHAR(255) NULL,
            `error_code` VARCHAR(100) NULL,
            `error_description` TEXT NULL,
            `payload_json` LONGTEXT NULL,
            `webhook_event_id` VARCHAR(100) NULL UNIQUE,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_ot_order` (`order_id`),
            INDEX `idx_ot_payment` (`payment_id`),
            INDEX `idx_ot_invoice` (`invoice_id`),
            INDEX `idx_ot_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlOnline);

        // 10. Seed Default Finance Permissions
        $perms = [
            ['name' => 'finance.view',            'module' => 'finance', 'action' => 'view',   'description' => 'Access Finance Command Center and Dashboard'],
            ['name' => 'finance.payments',        'module' => 'finance', 'action' => 'view',   'description' => 'View and allocate customer payments'],
            ['name' => 'finance.invoices',        'module' => 'finance', 'action' => 'view',   'description' => 'Manage invoices, receipts, and printouts'],
            ['name' => 'finance.online_payments', 'module' => 'finance', 'action' => 'view',   'description' => 'Manage Razorpay and online payment transactions'],
            ['name' => 'finance.dues',            'module' => 'finance', 'action' => 'view',   'description' => 'Access outstanding customer receivables and aging reports'],
            ['name' => 'finance.refunds',         'module' => 'finance', 'action' => 'manage', 'description' => 'Process customer refunds and gateway reversals'],
            ['name' => 'finance.salaries',        'module' => 'finance', 'action' => 'manage', 'description' => 'Configure employee salary structures and allowances'],
            ['name' => 'finance.payroll',         'module' => 'finance', 'action' => 'manage', 'description' => 'Run monthly payroll, generate payslips, and approve payouts'],
            ['name' => 'finance.expenses',        'module' => 'finance', 'action' => 'manage', 'description' => 'Log and approve business and trip operational costs'],
            ['name' => 'finance.ledger',          'module' => 'finance', 'action' => 'view',   'description' => 'Access cash and bank ledgers, accounts, and reconciliation'],
            ['name' => 'finance.reports',         'module' => 'finance', 'action' => 'view',   'description' => 'Generate P&L, trip profitability, and GST tax reports'],
        ];

        $checkPermStmt = $pdo->prepare("SELECT id FROM `permissions` WHERE `name` = ? LIMIT 1");
        $insertPermStmt = $pdo->prepare("INSERT INTO `permissions` (`name`, `module`, `action`, `description`, `created_at`) VALUES (?, ?, ?, ?, ?)");

        foreach ($perms as $p) {
            $checkPermStmt->execute([$p['name']]);
            if (!$checkPermStmt->fetchColumn()) {
                $insertPermStmt->execute([$p['name'], $p['module'], $p['action'], $p['description'], $now]);
            }
        }

        // Grant Admin Role all newly created finance permissions
        $adminIdStmt = $pdo->query("SELECT id FROM `roles` WHERE `slug` = 'admin' LIMIT 1");
        $adminId = (int)$adminIdStmt->fetchColumn();

        if ($adminId > 0) {
            $checkJuncStmt = $pdo->prepare("SELECT 1 FROM `role_permissions` WHERE `role_id` = ? AND `permission_id` = ? LIMIT 1");
            $insertJuncStmt = $pdo->prepare("INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, ?)");

            foreach ($perms as $p) {
                $pIdStmt = $pdo->prepare("SELECT id FROM `permissions` WHERE `name` = ? LIMIT 1");
                $pIdStmt->execute([$p['name']]);
                $permId = (int)$pIdStmt->fetchColumn();
                if ($permId > 0) {
                    $checkJuncStmt->execute([$adminId, $permId]);
                    if (!$checkJuncStmt->fetchColumn()) {
                        $insertJuncStmt->execute([$adminId, $permId, $now]);
                    }
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Additive policy: Never destroy financial records
    }
};
