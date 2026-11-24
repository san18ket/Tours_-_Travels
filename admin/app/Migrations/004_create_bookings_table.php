<?php
/**
 * Migration 004: Create Bookings Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bookings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_code` VARCHAR(30) NOT NULL UNIQUE,
            `customer_id` INT NOT NULL,
            `tour_id` INT NULL,
            `pickup_address` VARCHAR(255) NOT NULL,
            `drop_address` VARCHAR(255) NOT NULL,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `pickup_time` TIME NOT NULL DEFAULT '09:00:00',
            `passenger_count` INT NOT NULL DEFAULT 1,
            `special_requests` TEXT NULL,
            `subtotal` DECIMAL(10, 2) NOT NULL,
            `tax_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            `total_amount` DECIMAL(10, 2) NOT NULL,
            `status` ENUM('pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            `idempotency_token` VARCHAR(64) NULL UNIQUE,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_booking_status` (`status`),
            INDEX `idx_booking_customer` (`customer_id`),
            INDEX `idx_booking_dates` (`start_date`, `end_date`),
            CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_booking_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void {}
};
