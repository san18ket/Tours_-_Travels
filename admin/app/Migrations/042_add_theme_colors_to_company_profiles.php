<?php
/**
 * Migration 042: Add theme colors to company_profiles table for dynamic email branding
 */
return new class {
    public function up(PDO $pdo): void
    {
        $cols = [
            'primary_color'   => "ALTER TABLE `company_profiles` ADD COLUMN `primary_color` VARCHAR(20) NULL DEFAULT '#176B5B' AFTER `tagline`",
            'secondary_color' => "ALTER TABLE `company_profiles` ADD COLUMN `secondary_color` VARCHAR(20) NULL DEFAULT '#16443B' AFTER `primary_color`",
            'accent_color'    => "ALTER TABLE `company_profiles` ADD COLUMN `accent_color` VARCHAR(20) NULL DEFAULT '#D6B77A' AFTER `secondary_color`",
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
        $cols = ['primary_color', 'secondary_color', 'accent_color'];
        foreach ($cols as $colName) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE '{$colName}'");
            if ($stmt->fetch()) {
                $pdo->exec("ALTER TABLE `company_profiles` DROP COLUMN `{$colName}`");
            }
        }
    }
};

