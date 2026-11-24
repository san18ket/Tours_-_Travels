<?php
/**
 * Migration 011: Create Trip Expenses and Operational Costs Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `trip_expenses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `dispatch_id` INT NOT NULL,
            `expense_category` ENUM('fuel', 'toll', 'parking', 'driver_allowance', 'permit', 'maintenance', 'misc') NOT NULL DEFAULT 'fuel',
            `amount` DECIMAL(10, 2) NOT NULL,
            `receipt_number` VARCHAR(50) NULL,
            `notes` VARCHAR(255) NULL,
            `logged_by` INT NOT NULL,
            `incurred_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_expense_dispatch` (`dispatch_id`),
            INDEX `idx_expense_category` (`expense_category`),
            CONSTRAINT `fk_expense_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_expense_logger` FOREIGN KEY (`logged_by`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `trip_expenses`;");
    }
};
