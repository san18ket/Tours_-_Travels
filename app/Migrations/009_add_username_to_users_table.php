<?php
/**
 * Migration 009: Add Unique Username Column & Backfill Default Users
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Check if 'username' column exists in 'users'
        $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'username'");
        $columnExists = (bool)$stmt->fetch();

        if (!$columnExists) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `name`");
        }

        // 2. Backfill existing users with unique usernames based on email
        $usersStmt = $pdo->query("SELECT `id`, `name`, `email`, `username`, `role` FROM `users`");
        $existingUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

        $updateStmt = $pdo->prepare("UPDATE `users` SET `username` = ? WHERE `id` = ?");
        $assignedUsernames = [];

        // Pre-fill already assigned non-null usernames
        foreach ($existingUsers as $u) {
            if (!empty($u['username'])) {
                $assignedUsernames[strtolower($u['username'])] = true;
            }
        }

        foreach ($existingUsers as $u) {
            if (empty($u['username'])) {
                $base = '';
                if ($u['email'] === 'admin@voyagego.com') {
                    $base = 'admin';
                } elseif ($u['email'] === 'dispatcher@voyagego.com') {
                    $base = 'dispatcher';
                } elseif ($u['email'] === 'owner@voyagego.com') {
                    $base = 'owner';
                } else {
                    $parts = explode('@', $u['email']);
                    $base = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', $parts[0]));
                    if (empty($base)) {
                        $base = 'user_' . $u['id'];
                    }
                }

                $candidate = $base;
                $counter = 1;
                while (isset($assignedUsernames[strtolower($candidate)])) {
                    $candidate = $base . $counter;
                    $counter++;
                }

                $assignedUsernames[strtolower($candidate)] = true;
                $updateStmt->execute([$candidate, $u['id']]);
            }
        }

        // 3. Ensure 'owner' account exists idempotently (without overwriting existing passwords)
        $ownerStmt = $pdo->prepare("SELECT `id`, `password` FROM `users` WHERE `username` = 'owner' OR `email` = 'owner@voyagego.com'");
        $ownerStmt->execute();
        $existingOwner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingOwner) {
            $ownerHash = password_hash('owner123', PASSWORD_BCRYPT, ['cost' => 12]);
            $insertOwner = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`)
                VALUES ('System Owner', 'owner', 'owner@voyagego.com', '+91 98000 00000', ?, 'admin', 'active', NOW(), NOW())");
            $insertOwner->execute([$ownerHash]);
        } else {
            // Ensure username is set to 'owner' if matched by email
            $pdo->prepare("UPDATE `users` SET `username` = 'owner', `role` = 'admin', `status` = 'active' WHERE `id` = ?")
                ->execute([$existingOwner['id']]);
        }

        // 4. Enforce NOT NULL and UNIQUE constraint on username if index not present
        $indexStmt = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_username'");
        $indexExists = (bool)$indexStmt->fetch();

        if (!$indexExists) {
            $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `username` VARCHAR(50) NOT NULL, ADD UNIQUE INDEX `idx_users_username` (`username`)");
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe policy: do not drop columns or user data automatically
    }
};
