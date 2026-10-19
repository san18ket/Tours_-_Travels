<?php
namespace App\Seeders;

use PDO;
use App\Core\Logger;

class DatabaseSeeder
{
    /**
     * Seed only the essential Owner account (username and password).
     * No dummy data: all remaining tables remain completely blank.
     */
    public static function run(PDO $pdo): array
    {
        $seeded = [];

        // Ensure only default Owner exists
        $ownerStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'owner' OR email = 'owner@voyagego.com'");
        $ownerStmt->execute();
        $existingOwner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingOwner) {
            $insertOwner = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role, status, created_at, updated_at) 
                VALUES ('System Owner', 'owner', 'owner@voyagego.com', '+91 98000 00000', ?, 'admin', 'active', NOW(), NOW())");
            $insertOwner->execute([password_hash('owner123', PASSWORD_BCRYPT, ['cost' => 12])]);
            $seeded[] = "User: owner";
        }

        Logger::info("DatabaseSeeder finished: only Owner account created, remaining tables kept blank.", ['seeded_count' => count($seeded)]);
        return $seeded;
    }
}
