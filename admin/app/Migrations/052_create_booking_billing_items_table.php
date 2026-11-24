<?php
/**
 * Migration 052: Create Booking Billing Items Table for Normalized Itemized Trip Billing
 *
 * Stores individual charge line items (Distance, Daily, Halting, Parking, Toll, Permits, etc.)
 * for each vehicle booking and links them to the generated tax invoice.
 */

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `booking_billing_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id` INT NOT NULL,
            `invoice_id` INT NULL,
            `charge_category` VARCHAR(50) NOT NULL DEFAULT 'other',
            `description` VARCHAR(255) NOT NULL,
            `quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
            `unit` VARCHAR(50) NULL,
            `unit_rate` DECIMAL(10, 2) NULL DEFAULT NULL,
            `line_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `is_taxable` TINYINT(1) NOT NULL DEFAULT 1,
            `display_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_bbi_booking_id` (`booking_id`),
            INDEX `idx_bbi_invoice_id` (`invoice_id`),
            INDEX `idx_bbi_category` (`charge_category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `booking_billing_items`");
    }
};
