<?php
/**
 * Migration 021: Seed Core System Operational Roles & Standard Permission Catalogue
 * Ensures system roles (Admin, Dispatcher, Driver) and catalog permissions exist without breaking existing data
 */

use App\Services\PermissionRegistry;

return new class {
    public function up(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Seed Core System Roles
        $rolesToSeed = [
            [
                'name'        => 'System Administrator',
                'slug'        => 'admin',
                'description' => 'Full administrative, financial, fleet, and security access across all modules',
                'is_system'   => 1,
                'status'      => 'active'
            ],
            [
                'name'        => 'Operations Dispatcher',
                'slug'        => 'dispatcher',
                'description' => 'Fleet live radar, trip assignments, reservations dispatch and customer care',
                'is_system'   => 1,
                'status'      => 'active'
            ],
            [
                'name'        => 'Chauffeur',
                'slug'        => 'driver',
                'description' => 'Dedicated mobile driver console, assigned trips, odometer and live telemetry',
                'is_system'   => 1,
                'status'      => 'active'
            ],
        ];

        $checkRoleStmt = $pdo->prepare("SELECT id FROM `roles` WHERE `slug` = ? LIMIT 1");
        $insertRoleStmt = $pdo->prepare("INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($rolesToSeed as $r) {
            $checkRoleStmt->execute([$r['slug']]);
            if (!$checkRoleStmt->fetchColumn()) {
                $insertRoleStmt->execute([
                    $r['name'],
                    $r['slug'],
                    $r['description'],
                    $r['is_system'],
                    $r['status'],
                    $now,
                    $now
                ]);
            }
        }

        // 2. Seed Standard Permissions Catalogue from PermissionRegistry
        $flatPerms = PermissionRegistry::getFlatPermissions();
        $checkPermStmt = $pdo->prepare("SELECT id FROM `permissions` WHERE `name` = ? LIMIT 1");
        $insertPermStmt = $pdo->prepare("INSERT INTO `permissions` (`name`, `module`, `action`, `description`, `created_at`) VALUES (?, ?, ?, ?, ?)");

        foreach ($flatPerms as $perm) {
            $checkPermStmt->execute([$perm['name']]);
            if (!$checkPermStmt->fetchColumn()) {
                $insertPermStmt->execute([
                    $perm['name'],
                    $perm['module'],
                    $perm['action'],
                    $perm['description'],
                    $now
                ]);
            }
        }

        // 3. Grant Administrator Role full access in role_permissions
        $adminIdStmt = $pdo->query("SELECT id FROM `roles` WHERE `slug` = 'admin' LIMIT 1");
        $adminId = (int)$adminIdStmt->fetchColumn();

        if ($adminId > 0) {
            $allPermIds = $pdo->query("SELECT id FROM `permissions`")->fetchAll(PDO::FETCH_COLUMN);
            $checkJuncStmt = $pdo->prepare("SELECT 1 FROM `role_permissions` WHERE `role_id` = ? AND `permission_id` = ? LIMIT 1");
            $insertJuncStmt = $pdo->prepare("INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, ?)");

            foreach ($allPermIds as $pId) {
                $checkJuncStmt->execute([$adminId, $pId]);
                if (!$checkJuncStmt->fetchColumn()) {
                    $insertJuncStmt->execute([$adminId, $pId, $now]);
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: no destructive data loss
    }
};
