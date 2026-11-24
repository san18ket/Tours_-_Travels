<?php
/**
 * Migration 054: Add App Name and App Name Source to company_profiles table
 * Supports 'company_name' (derived) or 'custom' source with dedicated app_name field.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $cols = [
            'app_name_source' => "ALTER TABLE `company_profiles` ADD COLUMN `app_name_source` VARCHAR(30) NOT NULL DEFAULT 'company_name' AFTER `company_name`",
            'app_name'        => "ALTER TABLE `company_profiles` ADD COLUMN `app_name` VARCHAR(150) NULL DEFAULT NULL AFTER `app_name_source`",
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
        $cols = ['app_name', 'app_name_source'];
        foreach ($cols as $colName) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE '{$colName}'");
            if ($stmt->fetch()) {
                $pdo->exec("ALTER TABLE `company_profiles` DROP COLUMN `{$colName}`");
            }
        }
    }
};
