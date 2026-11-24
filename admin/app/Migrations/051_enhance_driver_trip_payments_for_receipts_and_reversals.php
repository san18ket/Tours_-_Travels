<?php
/**
 * Migration 051: Enhance Driver Trip Payments for Professional Receipts, Snapshots & Auditable Reversals
 *
 * Adds unique sequential receipt numbers, financial balance snapshots at time of payment,
 * optional payment attachment file paths, and detailed reversal audit fields.
 */

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Add receipt_number
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'receipt_number'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `receipt_number` VARCHAR(50) NULL AFTER `payment_reference`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD UNIQUE INDEX `idx_dtp_receipt_number` (`receipt_number`)");
        }

        // 2. Add trip_amount_snapshot
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'trip_amount_snapshot'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `trip_amount_snapshot` DECIMAL(12, 2) NULL AFTER `amount`");
        }

        // 3. Add previous_paid_amount
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'previous_paid_amount'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `previous_paid_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `trip_amount_snapshot`");
        }

        // 4. Add total_paid_after
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'total_paid_after'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `total_paid_after` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `previous_paid_amount`");
        }

        // 5. Add remaining_after
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'remaining_after'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `remaining_after` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `total_paid_after`");
        }

        // 6. Add attachment_file
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'attachment_file'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `attachment_file` VARCHAR(255) NULL AFTER `notes`");
        }

        // 7. Add reversal audit columns
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'reversed_by'")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `reversed_by` INT NULL AFTER `status`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `reversed_at` DATETIME NULL AFTER `reversed_by`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` ADD COLUMN `reversal_reason` TEXT NULL AFTER `reversed_at`");
        }

        // 8. Backfill existing payment records with sequential receipt numbers & snapshots
        $existing = $pdo->query("SELECT dtp.id, dtp.payment_date, dtp.amount, d.driver_trip_amount 
                                FROM `driver_trip_payments` dtp 
                                LEFT JOIN `dispatches` d ON dtp.dispatch_id = d.id 
                                WHERE dtp.receipt_number IS NULL OR dtp.receipt_number = ''
                                ORDER BY dtp.id ASC")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($existing as $row) {
            $year = !empty($row['payment_date']) ? date('Y', strtotime($row['payment_date'])) : date('Y');
            $receiptNum = sprintf('DPR-%s-%05d', $year, (int)$row['id']);
            $tripAmt = $row['driver_trip_amount'] !== null ? (float)$row['driver_trip_amount'] : (float)$row['amount'];
            $amt = (float)$row['amount'];
            $rem = max(0.0, $tripAmt - $amt);

            $upd = $pdo->prepare("UPDATE `driver_trip_payments` SET 
                `receipt_number` = ?, 
                `trip_amount_snapshot` = ?,
                `previous_paid_amount` = 0.00,
                `total_paid_after` = ?,
                `remaining_after` = ?
                WHERE `id` = ?");
            $upd->execute([$receiptNum, $tripAmt, $amt, $rem, $row['id']]);
        }
    }

    public function down(PDO $pdo): void
    {
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'receipt_number'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `receipt_number`");
        }
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'trip_amount_snapshot'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `trip_amount_snapshot`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `previous_paid_amount`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `total_paid_after`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `remaining_after`");
        }
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'attachment_file'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `attachment_file`");
        }
        $cols = $pdo->query("SHOW COLUMNS FROM `driver_trip_payments` LIKE 'reversed_by'")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `reversed_by`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `reversed_at`");
            $pdo->exec("ALTER TABLE `driver_trip_payments` DROP COLUMN `reversal_reason`");
        }
    }
};

