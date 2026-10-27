<?php
/**
 * Migration 012: Create Dedicated Customers Table & Non-Destructive Booking Linkages
 * Separates customer profiles from staff login accounts.
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create dedicated customers table
        $sqlCustomers = "CREATE TABLE IF NOT EXISTS `customers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_code` VARCHAR(30) NOT NULL UNIQUE,
            `user_id` INT NULL UNIQUE,
            `title` VARCHAR(20) NULL,
            `name` VARCHAR(150) NOT NULL,
            `date_of_birth` DATE NULL,
            `gender` ENUM('male', 'female', 'non_binary', 'prefer_not_to_say', 'other') NULL,
            `phone` VARCHAR(30) NOT NULL,
            `alternate_phone` VARCHAR(30) NULL,
            `email` VARCHAR(150) NULL,
            `preferred_contact_method` ENUM('mobile', 'whatsapp', 'email') NOT NULL DEFAULT 'mobile',
            `address_line` VARCHAR(255) NULL,
            `city` VARCHAR(100) NULL,
            `state` VARCHAR(100) NULL,
            `postal_code` VARCHAR(20) NULL,
            `country` VARCHAR(100) NOT NULL DEFAULT 'India',
            `profile_photo` VARCHAR(255) NULL,
            `id_type` VARCHAR(50) NULL,
            `id_number` VARCHAR(100) NULL,
            `id_expiry` DATE NULL,
            `id_document_file` VARCHAR(255) NULL,
            `customer_type` ENUM('individual', 'corporate') NOT NULL DEFAULT 'individual',
            `company_name` VARCHAR(150) NULL,
            `preferred_travel_category` VARCHAR(50) NULL,
            `notes` TEXT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `marketing_consent` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_customers_phone` (`phone`),
            INDEX `idx_customers_email` (`email`),
            INDEX `idx_customers_type` (`customer_type`),
            INDEX `idx_customers_status` (`status`),
            CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlCustomers);

        // 2. Add customer_profile_id to bookings table if not present
        $colCheckBooking = $pdo->query("SHOW COLUMNS FROM `bookings` LIKE 'customer_profile_id'");
        if (!$colCheckBooking->fetch()) {
            $pdo->exec("ALTER TABLE `bookings` ADD COLUMN `customer_profile_id` INT NULL AFTER `customer_id`");
            $pdo->exec("ALTER TABLE `bookings` ADD INDEX `idx_booking_customer_profile` (`customer_profile_id`)");
            $pdo->exec("ALTER TABLE `bookings` ADD CONSTRAINT `fk_booking_customer_profile` FOREIGN KEY (`customer_profile_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL");
            $pdo->exec("ALTER TABLE `bookings` MODIFY COLUMN `customer_id` INT NULL");
        }

        // 3. Add customer_profile_id to invoices table if not present
        $colCheckInvoice = $pdo->query("SHOW COLUMNS FROM `invoices` LIKE 'customer_profile_id'");
        if (!$colCheckInvoice->fetch()) {
            $pdo->exec("ALTER TABLE `invoices` ADD COLUMN `customer_profile_id` INT NULL AFTER `customer_id`");
            $pdo->exec("ALTER TABLE `invoices` ADD INDEX `idx_invoice_customer_profile` (`customer_profile_id`)");
            $pdo->exec("ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoice_customer_profile` FOREIGN KEY (`customer_profile_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL");
            $pdo->exec("ALTER TABLE `invoices` MODIFY COLUMN `customer_id` INT NULL");
        }

        // 4. Backfill any existing users with role = 'customer' into customers table
        $stmtUsers = $pdo->query("SELECT * FROM `users` WHERE `role` = 'customer'");
        $existingCustomers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $checkCust = $pdo->prepare("SELECT `id` FROM `customers` WHERE `user_id` = ? OR `email` = ?");
        $insertCust = $pdo->prepare("INSERT INTO `customers` (`customer_code`, `user_id`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $updateBooking = $pdo->prepare("UPDATE `bookings` SET `customer_profile_id` = ? WHERE `customer_id` = ? AND `customer_profile_id` IS NULL");
        $updateInvoice = $pdo->prepare("UPDATE `invoices` SET `customer_profile_id` = ? WHERE `customer_id` = ? AND `customer_profile_id` IS NULL");

        foreach ($existingCustomers as $cu) {
            $checkCust->execute([$cu['id'], $cu['email']]);
            $existingRow = $checkCust->fetch(PDO::FETCH_ASSOC);
            $profileId = $existingRow['id'] ?? null;

            if (!$profileId) {
                $code = 'VG-CUST-' . str_pad((string)$cu['id'], 4, '0', STR_PAD_LEFT);
                $phone = !empty($cu['phone']) ? $cu['phone'] : '+91 90000 00000';
                $insertCust->execute([
                    $code,
                    $cu['id'],
                    $cu['name'],
                    $phone,
                    $cu['email'],
                    $cu['status'] ?? 'active',
                    $cu['created_at'],
                    $cu['updated_at']
                ]);
                $profileId = (int)$pdo->lastInsertId();
            }

            if ($profileId) {
                $updateBooking->execute([$profileId, $cu['id']]);
                $updateInvoice->execute([$profileId, $cu['id']]);
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: non-destructive down migration
    }
};
