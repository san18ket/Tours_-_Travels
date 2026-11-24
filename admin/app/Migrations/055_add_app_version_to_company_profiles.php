<?php
/**
 * Migration 055: Add App Version to company_profiles table
 * Stores semantic app version string (e.g. 2.4.0)
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE 'app_version'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `company_profiles` ADD COLUMN `app_version` VARCHAR(30) NOT NULL DEFAULT '2.4.0' AFTER `app_name`");
        }
    }

    public function down(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE 'app_version'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `company_profiles` DROP COLUMN `app_version`");
        }
    }
};
