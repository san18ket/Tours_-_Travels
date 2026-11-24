<?php
/**
 * Migration 053: Add PWA App Icon Configuration to company_profiles table
 * Supports 'company_logo' or 'custom' source with dedicated app_icon storage.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $cols = [
            'app_icon_source' => "ALTER TABLE `company_profiles` ADD COLUMN `app_icon_source` VARCHAR(30) NOT NULL DEFAULT 'company_logo' AFTER `logo`",
            'app_icon'        => "ALTER TABLE `company_profiles` ADD COLUMN `app_icon` VARCHAR(255) NULL DEFAULT NULL AFTER `app_icon_source`",
        ];

        foreach ($cols as $colName => $alterSql) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE '{$colName}'");
            if (!$stmt->fetch()) {
                $pdo->exec($alterSql);
            }
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = ['app_icon', 'app_icon_source'];
        foreach ($cols as $colName) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE '{$colName}'");
            if ($stmt->fetch()) {
                $pdo->exec("ALTER TABLE `company_profiles` DROP COLUMN `{$colName}`");
            }
        }
    }
};
