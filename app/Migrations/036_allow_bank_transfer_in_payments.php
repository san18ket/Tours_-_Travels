<?php
/**
 * Migration 036: Allow bank_transfer in payments payment_method enum
 * Safe additive migration.
 */

return new class {
    public function up(PDO $pdo): void
    {
        try {
            $pdo->exec("ALTER TABLE `payments` MODIFY COLUMN `payment_method` ENUM('upi', 'net_banking', 'card', 'cash', 'bank_transfer') NOT NULL DEFAULT 'upi'");
        } catch (\Throwable $e) {
            // Already modified or compatible
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe additive migration
    }
};

