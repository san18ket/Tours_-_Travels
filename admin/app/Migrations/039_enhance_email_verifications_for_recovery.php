<?php
/**
 * Migration 039: Enhance email_verifications table for Account Recovery
 * Adds user_id binding and token_expires_at for short-lived recovery authorizations.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // Check if user_id column already exists
        $stmt = $pdo->query("SHOW COLUMNS FROM `email_verifications` LIKE 'user_id'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_verifications` 
                        ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `flow`,
                        ADD INDEX `idx_email_verif_user` (`user_id`)");
        }

        // Check if token_expires_at column already exists
        $stmt = $pdo->query("SHOW COLUMNS FROM `email_verifications` LIKE 'token_expires_at'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_verifications` 
                        ADD COLUMN `token_expires_at` DATETIME NULL DEFAULT NULL AFTER `verification_token`,
                        ADD INDEX `idx_email_verif_token_exp` (`token_expires_at`)");
        }
    }

    public function down(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `email_verifications` LIKE 'user_id'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_verifications` DROP COLUMN `user_id`");
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM `email_verifications` LIKE 'token_expires_at'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `email_verifications` DROP COLUMN `token_expires_at`");
        }
    }
};

