<?php
/**
 * Migration 032: Add Profile Photo, ID documents, License Scan, Detailed Address & Notes to Employees & Drivers
 */

return new class {
    public function up(PDO $pdo): void
    {
        $employeeCols = [
            'profile_photo'          => "VARCHAR(255) NULL AFTER `email`",
            'date_of_birth'          => "DATE NULL AFTER `profile_photo`",
            'gender'                 => "VARCHAR(20) NULL AFTER `date_of_birth`",
            'nationality'            => "VARCHAR(100) NULL DEFAULT 'Indian' AFTER `gender`",
            'address_line'           => "VARCHAR(255) NULL AFTER `address`",
            'city'                   => "VARCHAR(100) NULL AFTER `address_line`",
            'state'                  => "VARCHAR(100) NULL AFTER `city`",
            'postal_code'            => "VARCHAR(20) NULL AFTER `state`",
            'country'                => "VARCHAR(100) NULL DEFAULT 'India' AFTER `postal_code`",
            'emergency_contact_name' => "VARCHAR(100) NULL AFTER `emergency_contact`",
            'emergency_contact_phone'=> "VARCHAR(30) NULL AFTER `emergency_contact_name`",
            'id_type'                => "VARCHAR(50) NULL AFTER `country`",
            'id_number'              => "VARCHAR(100) NULL AFTER `id_type`",
            'id_expiry'              => "DATE NULL AFTER `id_number`",
            'id_document_file'       => "VARCHAR(255) NULL AFTER `id_expiry`",
            'license_document_file'  => "VARCHAR(255) NULL AFTER `id_document_file`",
            'notes'                  => "TEXT NULL AFTER `license_document_file`",
        ];

        foreach ($employeeCols as $colName => $def) {
            $check = $pdo->query("SHOW COLUMNS FROM `employees` LIKE '{$colName}'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE `employees` ADD COLUMN `{$colName}` {$def}");
            }
        }

        // Add license_document_file to driver_profiles if not present
        $checkDriverLicense = $pdo->query("SHOW COLUMNS FROM `driver_profiles` LIKE 'license_document_file'")->fetch();
        if (!$checkDriverLicense) {
            $pdo->exec("ALTER TABLE `driver_profiles` ADD COLUMN `license_document_file` VARCHAR(255) NULL AFTER `experience_years`");
        }
    }

    public function down(PDO $pdo): void
    {
        $employeeCols = [
            'profile_photo', 'date_of_birth', 'gender', 'nationality',
            'address_line', 'city', 'state', 'postal_code', 'country',
            'emergency_contact_name', 'emergency_contact_phone',
            'id_type', 'id_number', 'id_expiry', 'id_document_file',
            'license_document_file', 'notes'
        ];

        foreach ($employeeCols as $colName) {
            $check = $pdo->query("SHOW COLUMNS FROM `employees` LIKE '{$colName}'")->fetch();
            if ($check) {
                $pdo->exec("ALTER TABLE `employees` DROP COLUMN `{$colName}`");
            }
        }

        $checkDriverLicense = $pdo->query("SHOW COLUMNS FROM `driver_profiles` LIKE 'license_document_file'")->fetch();
        if ($checkDriverLicense) {
            $pdo->exec("ALTER TABLE `driver_profiles` DROP COLUMN `license_document_file`");
        }
    }
};
