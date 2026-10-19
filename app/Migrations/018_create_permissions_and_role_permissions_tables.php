<?php
/**
 * Migration 018: Create Permissions, Role Permissions & User Permissions Tables
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Create permissions table
        $sqlPermissions = "CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `module` VARCHAR(50) NOT NULL,
            `action` VARCHAR(50) NOT NULL,
            `description` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_permissions_module` (`module`),
            INDEX `idx_permissions_action` (`action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlPermissions);

        // 2. Create role_permissions junction table
        $sqlRolePermissions = "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `role_id` INT NOT NULL,
            `permission_id` INT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_role_perm` (`role_id`, `permission_id`),
            INDEX `idx_rp_role_id` (`role_id`),
            INDEX `idx_rp_permission_id` (`permission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlRolePermissions);

        // 3. Create user_permissions table (for explicit user overrides)
        $sqlUserPermissions = "CREATE TABLE IF NOT EXISTS `user_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `permission_id` INT NOT NULL,
            `is_granted` TINYINT(1) NOT NULL DEFAULT 1,
            `expires_at` DATETIME NULL,
            `max_usage_count` INT NULL,
            `usage_count` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_perm` (`user_id`, `permission_id`),
            INDEX `idx_up_user_id` (`user_id`),
            INDEX `idx_up_permission_id` (`permission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sqlUserPermissions);

        // 4. Ensure default roles exist in roles table
        $now = date('Y-m-d H:i:s');
        $stmtRole = $pdo->prepare("INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `description` = VALUES(`description`), `is_system` = VALUES(`is_system`), `status` = VALUES(`status`)");

        $defaults = [
            ['Administrator', 'admin', 'Full command center authority, system settings, and administrative management', 1, 'active'],
            ['Dispatcher', 'dispatcher', 'Trip operations dispatch, chauffeur duty deployment, and fleet live radar', 1, 'active'],
            ['Chauffeur / Driver', 'driver', 'Mobile chauffeur duty portal, trip manifests, and transit execution', 1, 'active'],
            ['Customer', 'customer', 'Verified guest client portal, reservation booking, and invoice receipts', 1, 'active'],
        ];

        foreach ($defaults as $row) {
            $stmtRole->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $now, $now]);
        }

        // 5. Seed master permission registry
        \App\Services\PermissionRegistry::ensureSeeded($pdo);

        // 6. Seed role permissions for system roles
        $allPerms = $pdo->query("SELECT id, name FROM `permissions`")->fetchAll(PDO::FETCH_ASSOC);
        $permMap = [];
        foreach ($allPerms as $p) {
            $permMap[$p['name']] = (int)$p['id'];
        }

        $roles = $pdo->query("SELECT id, slug FROM `roles`")->fetchAll(PDO::FETCH_ASSOC);
        $roleMap = [];
        foreach ($roles as $r) {
            $roleMap[$r['slug']] = (int)$r['id'];
        }

        $rpInsert = $pdo->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, CURRENT_TIMESTAMP)");

        // Admin gets 100% of permissions
        if (isset($roleMap['admin'])) {
            $adminRoleId = $roleMap['admin'];
            foreach ($allPerms as $p) {
                $rpInsert->execute([$adminRoleId, (int)$p['id']]);
            }
        }

        // Dispatcher permissions
        if (isset($roleMap['dispatcher'])) {
            $dispatcherRoleId = $roleMap['dispatcher'];
            $dispatcherPerms = [
                'dashboard.view', 'dashboard.refresh', 'dashboard.export', 'dashboard.view_revenue',
                'live_tracking.view', 'live_tracking.locate_all', 'live_tracking.inspect_unit', 'live_tracking.telemetry_api',
                'customers.view', 'customers.create', 'customers.create_page', 'customers.store', 'customers.show',
                'bookings.view', 'bookings.create', 'bookings.create_page', 'bookings.store', 'bookings.show', 'bookings.edit', 'bookings.invoice',
                'vehicles.view', 'vehicles.show',
                'employees.view', 'employees.show',
                'trip_operations.assign_page', 'trip_operations.store', 'trip_operations.view', 'trip_operations.show', 'trip_operations.update_status', 'trip_operations.cancel', 'trip_operations.export'
            ];
            foreach ($dispatcherPerms as $permName) {
                if (isset($permMap[$permName])) {
                    $rpInsert->execute([$dispatcherRoleId, $permMap[$permName]]);
                }
            }
        }

        // Driver permissions
        if (isset($roleMap['driver'])) {
            $driverRoleId = $roleMap['driver'];
            $driverPerms = [
                'live_tracking.view',
                'trip_operations.view', 'trip_operations.show', 'trip_operations.update_status'
            ];
            foreach ($driverPerms as $permName) {
                if (isset($permMap[$permName])) {
                    $rpInsert->execute([$driverRoleId, $permMap[$permName]]);
                }
            }
        }

        // Customer permissions
        if (isset($roleMap['customer'])) {
            $customerRoleId = $roleMap['customer'];
            $customerPerms = [
                'bookings.view', 'bookings.create', 'bookings.create_page', 'bookings.store', 'bookings.show', 'bookings.invoice'
            ];
            foreach ($customerPerms as $permName) {
                if (isset($permMap[$permName])) {
                    $rpInsert->execute([$customerRoleId, $permMap[$permName]]);
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: no destructive drops
    }
};
