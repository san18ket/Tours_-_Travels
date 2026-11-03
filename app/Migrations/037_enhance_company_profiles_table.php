<?php
/**
 * Migration 037: Enhance company_profiles table for Document Images (Paid Stamp, Signatures) and Complete Business Details
 * Safe additive migration.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $existingColumns = [];
        $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[strtolower($row['Field'])] = true;
        }

        $columnsToAdd = [
            'owner_name'       => "VARCHAR(150) NULL AFTER `company_name`",
            'paid_stamp'       => "VARCHAR(255) NULL AFTER `logo`",
            'signature'        => "VARCHAR(255) NULL AFTER `paid_stamp`",
            'signature_stamp'  => "VARCHAR(255) NULL AFTER `signature`",
            'alternate_phone'  => "VARCHAR(50) NULL AFTER `phone`",
            'gst_number'       => "VARCHAR(50) NULL AFTER `website`",
            'pan_number'       => "VARCHAR(50) NULL AFTER `gst_number`",
            'city'             => "VARCHAR(100) NULL AFTER `address`",
            'state'            => "VARCHAR(100) NULL AFTER `city`",
            'pin_code'         => "VARCHAR(20) NULL AFTER `state`",
        ];

        foreach ($columnsToAdd as $col => $definition) {
            if (!isset($existingColumns[strtolower($col)])) {
                try {
                    $pdo->exec("ALTER TABLE `company_profiles` ADD COLUMN `{$col}` {$definition}");
                } catch (\Throwable $e) {
                    // Safe additive
                }
            }
        }

        // Ensure email allows NULL as specified in requirements
        try {
            $pdo->exec("ALTER TABLE `company_profiles` MODIFY COLUMN `email` VARCHAR(150) NULL");
        } catch (\Throwable $e) {
            // Ignore if already nullable
        }
    }

    public function down(PDO $pdo): void
    {
        // Additive migration, non-destructive down
    }
};

