<?php
/**
 * Migration 026: Add Vehicle Photo and Documents to Vehicles Table
 */

return new class {
    public function up(PDO $pdo): void
    {
        $checkPhoto = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'vehicle_photo'")->fetch();
        if (!$checkPhoto) {
            $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `vehicle_photo` VARCHAR(255) NULL AFTER `insurance_expiry`");
        }

        $checkDoc = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'rc_document_file'")->fetch();
        if (!$checkDoc) {
            $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `rc_document_file` VARCHAR(255) NULL AFTER `vehicle_photo`");
        }

        $checkDocType = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'document_type'")->fetch();
        if (!$checkDocType) {
            $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `document_type` VARCHAR(100) NULL AFTER `rc_document_file`");
        }
    }

    public function down(PDO $pdo): void
    {
        $checkDocType = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'document_type'")->fetch();
        if ($checkDocType) {
            $pdo->exec("ALTER TABLE `vehicles` DROP COLUMN `document_type`");
        }

        $checkDoc = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'rc_document_file'")->fetch();
        if ($checkDoc) {
            $pdo->exec("ALTER TABLE `vehicles` DROP COLUMN `rc_document_file`");
        }

        $checkPhoto = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'vehicle_photo'")->fetch();
        if ($checkPhoto) {
            $pdo->exec("ALTER TABLE `vehicles` DROP COLUMN `vehicle_photo`");
        }
    }
};

