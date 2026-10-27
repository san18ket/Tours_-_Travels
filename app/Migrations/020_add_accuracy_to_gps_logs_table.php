<?php
/**
 * Migration 020: Add Accuracy (meters) to GPS Logs Table
 */
return new class {
    public function up(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `gps_logs` LIKE 'accuracy_m'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `gps_logs` ADD COLUMN `accuracy_m` DECIMAL(8, 2) NULL AFTER `heading_degrees`;");
        }
    }

    public function down(PDO $pdo): void {}
};
