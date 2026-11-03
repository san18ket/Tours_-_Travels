<?php
/**
 * Migration 038: Create email_verifications table for 4-digit OTP email verification
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW TABLES LIKE 'email_verifications'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `email_verifications` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(150) NOT NULL,
                `flow` VARCHAR(50) NOT NULL,
                `otp_hash` VARCHAR(255) NOT NULL,
                `attempts` INT NOT NULL DEFAULT 0,
                `max_attempts` INT NOT NULL DEFAULT 5,
                `resend_available_at` DATETIME NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `status` ENUM('pending', 'verified', 'consumed', 'invalidated') NOT NULL DEFAULT 'pending',
                `verified_at` DATETIME NULL,
                `consumed_at` DATETIME NULL,
                `verification_token` VARCHAR(64) NULL,
                `ip_address` VARCHAR(45) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_email_verif_flow` (`email`, `flow`, `status`),
                INDEX `idx_email_verif_token` (`verification_token`),
                INDEX `idx_email_verif_expires` (`expires_at`),
                INDEX `idx_email_verif_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `email_verifications`");
    }
};

