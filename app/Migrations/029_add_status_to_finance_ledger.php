<?php
/**
 * Migration 029: Add status to finance_ledger table
 */
return new class {
    public function up(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `finance_ledger` LIKE 'status'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `finance_ledger` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'completed' AFTER `notes`, ADD INDEX `idx_ledger_status` (`status`)");
        }
    }

    public function down(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `finance_ledger` LIKE 'status'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `finance_ledger` DROP COLUMN `status`");
        }
    }
};

