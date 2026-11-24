<?php
/**
 * Migration 049: Create trip_categories table and associate with bookings
 */
return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create trip_categories table
        $sql = "CREATE TABLE IF NOT EXISTS `trip_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_trip_cat_status` (`status`),
            INDEX `idx_trip_cat_sort` (`sort_order`, `name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql);

        // 2. Seed authoritative default simple categories
        $defaultCategories = [
            [
                'name'        => 'Holiday / Tour',
                'slug'        => 'holiday-tour',
                'description' => 'Vacation, sightseeing, tour packages and holiday travel',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Wedding',
                'slug'        => 'wedding',
                'description' => 'Wedding functions, marriage ceremonies and guest transport',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Family Trip',
                'slug'        => 'family-trip',
                'description' => 'Family outings, personal family travel and leisure',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Business Trip',
                'slug'        => 'business-trip',
                'description' => 'Office travel, company meetings and employee transportation',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Airport Trip',
                'slug'        => 'airport-trip',
                'description' => 'Airport pickup, drop and terminal transfers',
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Train Station Trip',
                'slug'        => 'train-station-trip',
                'description' => 'Railway station pickup and drop transfers',
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Local Trip',
                'slug'        => 'local-trip',
                'description' => 'Travel within the same city, local sightseeing and transport',
                'sort_order'  => 7,
            ],
            [
                'name'        => 'Outstation Trip',
                'slug'        => 'outstation-trip',
                'description' => 'Intercity travel and outstation round journeys',
                'sort_order'  => 8,
            ],
            [
                'name'        => 'School / College Trip',
                'slug'        => 'school-college-trip',
                'description' => 'School trips, college excursions and student transport',
                'sort_order'  => 9,
            ],
            [
                'name'        => 'Religious Trip',
                'slug'        => 'religious-trip',
                'description' => 'Temple visits, pilgrimages and religious travel',
                'sort_order'  => 10,
            ],
            [
                'name'        => 'Event / Function',
                'slug'        => 'event-function',
                'description' => 'Parties, conferences, festivals, exhibitions and functions',
                'sort_order'  => 11,
            ],
            [
                'name'        => 'Hotel Trip',
                'slug'        => 'hotel-trip',
                'description' => 'Hotel and resort pickup, drop and hospitality transfers',
                'sort_order'  => 12,
            ],
            [
                'name'        => 'Other',
                'slug'        => 'other',
                'description' => 'General bookings not covered by standard trip categories',
                'sort_order'  => 13,
            ],
        ];

        $stmt = $pdo->prepare("INSERT INTO `trip_categories` (`name`, `slug`, `description`, `status`, `sort_order`, `created_at`, `updated_at`)
                               VALUES (?, ?, ?, 'active', ?, NOW(), NOW())
                               ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`), `sort_order` = VALUES(`sort_order`)");

        foreach ($defaultCategories as $cat) {
            $stmt->execute([
                $cat['name'],
                $cat['slug'],
                $cat['description'],
                $cat['sort_order']
            ]);
        }

        // 3. Add trip_category_id to bookings table if not exists
        $colCheck = $pdo->query("SHOW COLUMNS FROM `bookings` LIKE 'trip_category_id'");
        if (!$colCheck->fetch()) {
            $pdo->exec("ALTER TABLE `bookings` ADD COLUMN `trip_category_id` INT NULL DEFAULT NULL AFTER `tour_id`");
            $pdo->exec("ALTER TABLE `bookings` ADD INDEX `idx_booking_trip_category` (`trip_category_id`)");
            
            // Check if foreign key can be added safely
            try {
                $pdo->exec("ALTER TABLE `bookings` ADD CONSTRAINT `fk_booking_trip_category` FOREIGN KEY (`trip_category_id`) REFERENCES `trip_categories` (`id`) ON DELETE SET NULL");
            } catch (\Throwable $e) {
                // In case constraint addition fails due to table engine difference or existing data
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe rollback
        try {
            $pdo->exec("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_booking_trip_category`");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `bookings` DROP COLUMN `trip_category_id`");
        } catch (\Throwable $e) {}
        $pdo->exec("DROP TABLE IF EXISTS `trip_categories`");
    }
};

