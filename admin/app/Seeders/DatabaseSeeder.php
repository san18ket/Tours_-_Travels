<?php
namespace App\Seeders;

use PDO;
use App\Core\Logger;
use App\Core\Config;

class DatabaseSeeder
{
    /**
     * Seed only essential system accounts safely and non-destructively.
     * 100% Idempotent and Non-destructive:
     * Never overwrites existing production records, never seeds dummy business data.
     */
    public static function run(PDO $pdo): array
    {
        $seeded = [];

        try {
            // Check if users table exists first
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
            if (!$tableCheck->fetchColumn()) {
                return $seeded;
            }

            // Ensure default System Owner exists idempotently
            $ownerStmt = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = 'owner' OR `email` = 'owner@tours.local'");
            $ownerStmt->execute();
            $existingOwner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingOwner) {
                $insertOwner = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) 
                    VALUES ('System Owner', 'owner', 'owner@tours.local', '+91 98000 00000', ?, 'admin', 'active', NOW(), NOW())");
                $insertOwner->execute([password_hash('owner123', PASSWORD_BCRYPT, ['cost' => 12])]);
                $seeded[] = "User: owner";
                Logger::info("DatabaseSeeder: Default owner account seeded.", ['seeded_count' => count($seeded)]);
            }

            // Ensure default Administrator exists idempotently
            $adminStmt = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = 'admin' OR `email` = 'admin@tours.local'");
            $adminStmt->execute();
            if (!$adminStmt->fetch(PDO::FETCH_ASSOC)) {
                $insertAdmin = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) 
                    VALUES ('Administrator', 'admin', 'admin@tours.local', '+91 98000 00001', ?, 'admin', 'active', NOW(), NOW())");
                $insertAdmin->execute([password_hash('Admin@12345', PASSWORD_BCRYPT, ['cost' => 12])]);
                $seeded[] = "User: admin";
                Logger::info("DatabaseSeeder: Default admin account seeded.", ['seeded_count' => count($seeded)]);
            }
        } catch (\Throwable $e) {
            Logger::warning("DatabaseSeeder check warning: " . $e->getMessage());
        }

        return $seeded;
    }
}
