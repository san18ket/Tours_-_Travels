<?php
/**
 * Migration 024: Add Profile Photo Column to Customers Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $check = $pdo->query("SHOW COLUMNS FROM `customers` LIKE 'profile_photo'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE `customers` ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `country`");
        }
    }

    public function down(PDO $pdo): void
    {
        $check = $pdo->query("SHOW COLUMNS FROM `customers` LIKE 'profile_photo'")->fetch();
        if ($check) {
            $pdo->exec("ALTER TABLE `customers` DROP COLUMN `profile_photo`");
        }
    }
};
