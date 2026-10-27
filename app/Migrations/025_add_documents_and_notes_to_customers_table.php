<?php
/**
 * Migration 025: Add Identification Document details, Document File, and Notes to Customers table
 */

return new class {
    public function up(PDO $pdo): void
    {
        // Ensure profile_photo exists first
        $checkPhoto = $pdo->query("SHOW COLUMNS FROM `customers` LIKE 'profile_photo'")->fetch();
        if (!$checkPhoto) {
            $pdo->exec("ALTER TABLE `customers` ADD COLUMN `profile_photo` VARCHAR(255) NULL");
        }

        $cols = [
            'id_type'          => "VARCHAR(50) NULL",
            'id_number'        => "VARCHAR(100) NULL",
            'id_expiry'        => "DATE NULL",
            'id_document_file' => "VARCHAR(255) NULL",
            'notes'            => "TEXT NULL",
        ];

        foreach ($cols as $colName => $def) {
            $check = $pdo->query("SHOW COLUMNS FROM `customers` LIKE '{$colName}'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE `customers` ADD COLUMN `{$colName}` {$def}");
            }
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = ['id_type', 'id_number', 'id_expiry', 'id_document_file', 'notes'];
        foreach ($cols as $colName) {
            $check = $pdo->query("SHOW COLUMNS FROM `customers` LIKE '{$colName}'")->fetch();
            if ($check) {
                $pdo->exec("ALTER TABLE `customers` DROP COLUMN `{$colName}`");
            }
        }
    }
};
