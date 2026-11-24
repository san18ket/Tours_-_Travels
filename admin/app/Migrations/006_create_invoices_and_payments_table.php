<?php
/**
 * Migration 006: Create Invoices and Payments Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        // Invoices Table
        $sqlInvoices = "CREATE TABLE IF NOT EXISTS `invoices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
            `booking_id` INT NULL UNIQUE,
            `customer_id` INT NOT NULL,
            `subtotal` DECIMAL(10, 2) NOT NULL,
            `tax_rate` DECIMAL(5, 2) NOT NULL DEFAULT 5.00,
            `tax_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `total_amount` DECIMAL(10, 2) NOT NULL,
            `paid_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `payment_status` ENUM('unpaid', 'partially_paid', 'paid') NOT NULL DEFAULT 'unpaid',
            `due_date` DATE NOT NULL,
            `is_immutable` TINYINT(1) NOT NULL DEFAULT 1,
            `issued_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_invoice_status` (`payment_status`),
            INDEX `idx_invoice_customer` (`customer_id`),
            CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_invoice_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlInvoices);

        // Payments Table
        $sqlPayments = "CREATE TABLE IF NOT EXISTS `payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payment_reference` VARCHAR(64) NOT NULL UNIQUE,
            `invoice_id` INT NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `payment_method` ENUM('upi', 'net_banking', 'card', 'cash') NOT NULL DEFAULT 'upi',
            `transaction_id` VARCHAR(100) NULL,
            `status` ENUM('success', 'failed', 'refunded') NOT NULL DEFAULT 'success',
            `idempotency_key` VARCHAR(64) NOT NULL UNIQUE,
            `notes` VARCHAR(255) NULL,
            `paid_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_payment_invoice` (`invoice_id`),
            INDEX `idx_payment_status` (`status`),
            CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlPayments);
    }

    public function down(PDO $pdo): void {}
};
