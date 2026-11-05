<?php
/**
 * Migration 044: Create remember_tokens table for persistent authentication,
 * reference_versions table for browser cache synchronization,
 * and high-frequency lookup database performance indexes.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create or upgrade remember_tokens table for secure, revocable Remember Me authentication
        $usersExist = (bool)$pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        $fkConstraint = $usersExist ? ", CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE" : "";

        $pdo->exec("CREATE TABLE IF NOT EXISTS `remember_tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `selector` VARCHAR(32) NOT NULL UNIQUE,
            `validator_hash` VARCHAR(64) NOT NULL,
            `prev_validator_hash` VARCHAR(64) NULL,
            `rotated_at` DATETIME NULL,
            `expires_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            `last_used_at` DATETIME NULL,
            INDEX `idx_remember_user` (`user_id`),
            INDEX `idx_remember_expires` (`expires_at`)
            {$fkConstraint}
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Verify all required columns exist even if table pre-existed
        $cols = $pdo->query("SHOW COLUMNS FROM `remember_tokens`")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        if (!in_array('prev_validator_hash', $cols, true)) {
            $pdo->exec("ALTER TABLE `remember_tokens` ADD COLUMN `prev_validator_hash` VARCHAR(64) NULL AFTER `validator_hash`");
        }
        if (!in_array('rotated_at', $cols, true)) {
            $pdo->exec("ALTER TABLE `remember_tokens` ADD COLUMN `rotated_at` DATETIME NULL AFTER `prev_validator_hash`");
        }
        if (!in_array('last_used_at', $cols, true)) {
            $pdo->exec("ALTER TABLE `remember_tokens` ADD COLUMN `last_used_at` DATETIME NULL AFTER `created_at`");
        }

        // Verify foreign key constraint exists if users table is present
        if ($usersExist) {
            $fkCheck = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                                    WHERE TABLE_SCHEMA = DATABASE() 
                                      AND TABLE_NAME = 'remember_tokens' 
                                      AND CONSTRAINT_NAME = 'fk_remember_user'
                                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetch();
            if (!$fkCheck) {
                try {
                    $pdo->exec("ALTER TABLE `remember_tokens` ADD CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
                } catch (\Throwable $e) {
                    if (!str_contains($e->getMessage(), 'already exists') && !str_contains($e->getMessage(), 'Duplicate')) {
                        throw new \RuntimeException("Migration 044: Failed to attach foreign key 'fk_remember_user' on 'remember_tokens(user_id)': " . $e->getMessage(), 0, $e);
                    }
                }
            }

            // Assert constraint strictly exists
            $verifiedFk = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                                      WHERE TABLE_SCHEMA = DATABASE() 
                                        AND TABLE_NAME = 'remember_tokens' 
                                        AND CONSTRAINT_NAME = 'fk_remember_user'
                                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetch();
            if (!$verifiedFk) {
                throw new \RuntimeException("Migration 044: Foreign key constraint 'fk_remember_user' could not be verified on 'remember_tokens'. Ensure 'users' and 'remember_tokens' both use the InnoDB engine.");
            }
        }

        // 2. Create reference_versions table for tracking reference data changes & ETag validation
        $pdo->exec("CREATE TABLE IF NOT EXISTS `reference_versions` (
            `data_key` VARCHAR(64) NOT NULL PRIMARY KEY,
            `version` INT NOT NULL DEFAULT 1,
            `etag` VARCHAR(64) NOT NULL,
            `updated_at` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed initial reference versions idempotently
        $defaultKeys = [
            'locations',
            'vehicle_categories',
            'payment_methods',
            'dropdown_options',
            'company_branding',
            'app_config'
        ];

        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO `reference_versions` (`data_key`, `version`, `etag`, `updated_at`) 
                               VALUES (?, 1, ?, ?) 
                               ON DUPLICATE KEY UPDATE `updated_at` = VALUES(`updated_at`)");
        foreach ($defaultKeys as $key) {
            $etag = md5($key . '_v1_' . $now);
            $stmt->execute([$key, $etag, $now]);
        }

        // 3. Performance indexes on high-frequency query columns with safety checks
        $indexAdditions = [
            'bookings' => [
                'idx_bookings_created_at' => 'ALTER TABLE `bookings` ADD INDEX `idx_bookings_created_at` (`created_at`)',
            ],
            'customers' => [
                'idx_customers_created_at' => 'ALTER TABLE `customers` ADD INDEX `idx_customers_created_at` (`created_at`)',
            ],
            'dispatches' => [
                'idx_dispatches_driver_status' => 'ALTER TABLE `dispatches` ADD INDEX `idx_dispatches_driver_status` (`driver_id`, `status`)',
            ],
        ];

        foreach ($indexAdditions as $table => $indexMap) {
            $tCheck = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if (!$tCheck->fetch()) {
                continue;
            }

            foreach ($indexMap as $indexName => $alterSql) {
                $chkStmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?");
                $chkStmt->execute([$indexName]);
                if (!$chkStmt->fetch()) {
                    try {
                        $pdo->exec($alterSql);
                    } catch (\Throwable $e) {
                        if (!str_contains($e->getMessage(), 'Duplicate key') && !str_contains($e->getMessage(), 'already exists')) {
                            \App\Core\Logger::error("Migration 044: Failed to add index '{$indexName}' on '{$table}': " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `remember_tokens`");
        } catch (\Throwable $e) {
            \App\Core\Logger::error("Migration 044 down: Failed to drop remember_tokens: " . $e->getMessage());
        }

        try {
            $pdo->exec("DROP TABLE IF EXISTS `reference_versions`");
        } catch (\Throwable $e) {
            \App\Core\Logger::error("Migration 044 down: Failed to drop reference_versions: " . $e->getMessage());
        }

        $indexRemovals = [
            'bookings'   => 'idx_bookings_created_at',
            'customers'  => 'idx_customers_created_at',
            'dispatches' => 'idx_dispatches_driver_status',
        ];

        foreach ($indexRemovals as $table => $indexName) {
            $tCheck = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if (!$tCheck->fetch()) {
                continue;
            }

            $chk = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?");
            $chk->execute([$indexName]);
            if ($chk->fetch()) {
                try {
                    $pdo->exec("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                } catch (\Throwable $e) {
                    \App\Core\Logger::error("Migration 044 down: Failed to drop index '{$indexName}' on '{$table}': " . $e->getMessage());
                }
            }
        }
    }
};

