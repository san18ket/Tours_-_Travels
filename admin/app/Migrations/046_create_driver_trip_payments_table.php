<?php
/**
 * Migration 046: Create Driver Trip Payments Table & Extend Dispatches
 *
 * Implements dedicated driver trip payables & payments structure.
 * Supports partial/full disbursements, accounts ledger integration,
 * server-side concurrency safeguards, and audit trails.
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Extend dispatches with driver_paid_amount and driver_payment_status
        $colsPaid = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_paid_amount'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($colsPaid)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `driver_paid_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `driver_trip_amount`");
        }

        $colsStatus = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_payment_status'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($colsStatus)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `driver_payment_status` ENUM('not_due', 'amount_not_specified', 'due', 'partially_paid', 'paid', 'reversed') NOT NULL DEFAULT 'not_due' AFTER `driver_paid_amount`");
            $pdo->exec("ALTER TABLE `dispatches` ADD INDEX `idx_dispatch_driver_pay_status` (`driver_payment_status`)");
        }

        // Initialize driver_payment_status on existing dispatches
        $pdo->exec("UPDATE `dispatches` SET `driver_payment_status` = 'due' WHERE `status` = 'completed' AND `driver_trip_amount` IS NOT NULL AND `driver_trip_amount` > 0 AND `driver_paid_amount` = 0");
        $pdo->exec("UPDATE `dispatches` SET `driver_payment_status` = 'amount_not_specified' WHERE `status` = 'completed' AND `driver_trip_amount` IS NULL");
        $pdo->exec("UPDATE `dispatches` SET `driver_payment_status` = 'not_due' WHERE `status` != 'completed'");

        // 2. Create driver_trip_payments table
        $sqlPayments = "CREATE TABLE IF NOT EXISTS `driver_trip_payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payment_reference` VARCHAR(60) NOT NULL UNIQUE,
            `dispatch_id` INT NOT NULL,
            `driver_id` INT NOT NULL,
            `employee_id` INT NULL,
            `booking_id` INT NULL,
            `account_id` INT NULL,
            `amount` DECIMAL(12, 2) NOT NULL,
            `payment_method` VARCHAR(30) NOT NULL DEFAULT 'bank_transfer',
            `transaction_ref` VARCHAR(100) NULL,
            `payment_date` DATE NOT NULL,
            `notes` TEXT NULL,
            `status` ENUM('completed', 'reversed') NOT NULL DEFAULT 'completed',
            `created_by` INT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_dtp_dispatch` (`dispatch_id`),
            INDEX `idx_dtp_driver` (`driver_id`),
            INDEX `idx_dtp_employee` (`employee_id`),
            INDEX `idx_dtp_account` (`account_id`),
            INDEX `idx_dtp_date` (`payment_date`),
            INDEX `idx_dtp_status` (`status`),
            CONSTRAINT `fk_dtp_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_dtp_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_dtp_account` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sqlPayments);

        // 3. Register permission 'finance.driver_payments' if not existing
        $chkPerm = $pdo->prepare("SELECT id FROM `permissions` WHERE `name` = 'finance.driver_payments' LIMIT 1");
        $chkPerm->execute();
        $permId = $chkPerm->fetchColumn();

        if (!$permId) {
            $insPerm = $pdo->prepare("INSERT INTO `permissions` (`name`, `module`, `description`, `created_at`) VALUES (?, ?, ?, NOW())");
            $insPerm->execute([
                'finance.driver_payments',
                'finance',
                'Manage, inspect, and disburse driver trip earnings and payouts'
            ]);
            $permId = (int)$pdo->lastInsertId();

            // Assign to admin and dispatcher roles
            $roleIds = $pdo->query("SELECT id FROM `roles` WHERE `name` IN ('admin', 'superadmin', 'owner', 'dispatcher', 'accountant')")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($roleIds as $rId) {
                $chkRolePerm = $pdo->prepare("SELECT 1 FROM `role_permissions` WHERE `role_id` = ? AND `permission_id` = ?");
                $chkRolePerm->execute([$rId, $permId]);
                if (!$chkRolePerm->fetchColumn()) {
                    $pdo->prepare("INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())")->execute([$rId, $permId]);
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `driver_trip_payments`;");

        $colsStatus = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_payment_status'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($colsStatus)) {
            $pdo->exec("ALTER TABLE `dispatches` DROP COLUMN `driver_payment_status`");
        }

        $colsPaid = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_paid_amount'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($colsPaid)) {
            $pdo->exec("ALTER TABLE `dispatches` DROP COLUMN `driver_paid_amount`");
        }

        $pdo->exec("DELETE FROM `permissions` WHERE `name` = 'finance.driver_payments'");
    }
};

