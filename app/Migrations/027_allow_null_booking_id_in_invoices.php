<?php
/**
 * Migration 027: Allow NULL booking_id in Invoices Table with ON DELETE SET NULL
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Check if foreign key exists and drop it safely
        $stmtFk = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND CONSTRAINT_NAME = 'fk_invoice_booking'");
        if ($stmtFk && $stmtFk->fetch()) {
            $pdo->exec("ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoice_booking`");
        }

        // 2. Modify booking_id column to INT NULL
        $pdo->exec("ALTER TABLE `invoices` MODIFY COLUMN `booking_id` INT NULL");

        // 3. Re-add foreign key constraint with ON DELETE SET NULL
        $stmtCheck = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND CONSTRAINT_NAME = 'fk_invoice_booking'");
        if ($stmtCheck && !$stmtCheck->fetch()) {
            $pdo->exec("ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL");
        }
    }

    public function down(PDO $pdo): void
    {
        $stmtFk = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND CONSTRAINT_NAME = 'fk_invoice_booking'");
        if ($stmtFk && $stmtFk->fetch()) {
            $pdo->exec("ALTER TABLE `invoices` DROP FOREIGN KEY `fk_invoice_booking`");
        }
        $pdo->exec("ALTER TABLE `invoices` MODIFY COLUMN `booking_id` INT NOT NULL");
        $pdo->exec("ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE RESTRICT");
    }
};

