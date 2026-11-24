<?php
/**
 * Migration 045: Add Optional Driver Trip Amount to Dispatches Table
 *
 * Adds driver_trip_amount DECIMAL(12,2) NULL to dispatches table.
 * Must allow NULL to distinguish unspecified amount from explicit ₹0.00.
 */

return new class {
    public function up(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_trip_amount'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `driver_trip_amount` DECIMAL(12, 2) NULL DEFAULT NULL AFTER `assigned_by`");
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_trip_amount'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `dispatches` DROP COLUMN `driver_trip_amount`");
        }
    }
};

