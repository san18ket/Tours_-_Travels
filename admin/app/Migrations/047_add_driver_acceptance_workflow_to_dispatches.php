<?php
/**
 * Migration 047: Add Driver Acceptance Workflow Fields to Dispatches Table
 *
 * Supports driver response tracking (accepted / unable to accept),
 * response deadline and overdue detection, customer notification idempotency timestamp,
 * driver reminder cooldown, and reason tracking.
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Extend status ENUM to include 'unable_to_accept'
        $pdo->exec("ALTER TABLE `dispatches` MODIFY COLUMN `status` ENUM('assigned', 'driver_accepted', 'unable_to_accept', 'en_route_pickup', 'trip_started', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned'");

        // 2. Add driver_response_at
        $chkResAt = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'driver_response_at'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($chkResAt)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `driver_response_at` DATETIME NULL AFTER `status`");
        }

        // 3. Add unable_to_accept_reason
        $chkReason = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'unable_to_accept_reason'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($chkReason)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `unable_to_accept_reason` VARCHAR(255) NULL AFTER `driver_response_at`");
        }

        // 4. Add response_deadline
        $chkDeadline = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'response_deadline'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($chkDeadline)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `response_deadline` DATETIME NULL AFTER `unable_to_accept_reason`");
            $pdo->exec("ALTER TABLE `dispatches` ADD INDEX `idx_dispatch_response_deadline` (`response_deadline`)");
        }

        // 5. Add customer_notified_at
        $chkNotifAt = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'customer_notified_at'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($chkNotifAt)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `customer_notified_at` DATETIME NULL AFTER `response_deadline`");
            $pdo->exec("ALTER TABLE `dispatches` ADD INDEX `idx_dispatch_customer_notified_at` (`customer_notified_at`)");
        }

        // 6. Add last_reminded_at
        $chkRemind = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE 'last_reminded_at'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($chkRemind)) {
            $pdo->exec("ALTER TABLE `dispatches` ADD COLUMN `last_reminded_at` DATETIME NULL AFTER `customer_notified_at`");
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = ['last_reminded_at', 'customer_notified_at', 'response_deadline', 'unable_to_accept_reason', 'driver_response_at'];
        foreach ($cols as $col) {
            $exists = $pdo->query("SHOW COLUMNS FROM `dispatches` LIKE '{$col}'")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($exists)) {
                $pdo->exec("ALTER TABLE `dispatches` DROP COLUMN `{$col}`");
            }
        }
        $pdo->exec("ALTER TABLE `dispatches` MODIFY COLUMN `status` ENUM('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned'");
    }
};

