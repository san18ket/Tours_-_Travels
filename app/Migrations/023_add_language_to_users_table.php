<?php
/**
 * Migration 023: Add Language Preference to Users Table
 */
return new class {
    public function up(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'language'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `language` VARCHAR(10) NOT NULL DEFAULT 'en' AFTER `status`;");
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'language'")->fetchAll();
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `users` DROP COLUMN `language`;");
        }
    }
};
