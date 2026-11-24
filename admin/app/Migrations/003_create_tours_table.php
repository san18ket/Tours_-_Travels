<?php
/**
 * Migration 003: Create Tours and Tour Packages Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tours` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `title` VARCHAR(200) NOT NULL,
            `category` ENUM('heritage', 'hill_station', 'pilgrimage', 'wildlife', 'beach', 'custom') NOT NULL DEFAULT 'heritage',
            `duration_days` INT NOT NULL DEFAULT 1,
            `duration_nights` INT NOT NULL DEFAULT 0,
            `pickup_city` VARCHAR(100) NOT NULL,
            `destination_city` VARCHAR(100) NOT NULL,
            `base_price` DECIMAL(10, 2) NOT NULL,
            `description` TEXT NULL,
            `itinerary_json` TEXT NULL,
            `inclusions` TEXT NULL,
            `exclusions` TEXT NULL,
            `max_passengers` INT NOT NULL DEFAULT 10,
            `image_url` VARCHAR(255) NULL,
            `status` ENUM('published', 'draft', 'archived') NOT NULL DEFAULT 'published',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_tour_category` (`category`),
            INDEX `idx_tour_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void {}
};
