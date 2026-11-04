<?php
/**
 * Migration 043: Add database performance indexes on high-frequency lookup & foreign key columns
 */
return new class {
    private array $indexes = [
        'employees' => [
            'idx_employees_email' => 'ALTER TABLE `employees` ADD INDEX `idx_employees_email` (`email`)',
        ],
        'payments' => [
            'idx_payments_transaction_id' => 'ALTER TABLE `payments` ADD INDEX `idx_payments_transaction_id` (`transaction_id`)',
        ],
        'refunds' => [
            'idx_refunds_booking_id' => 'ALTER TABLE `refunds` ADD INDEX `idx_refunds_booking_id` (`booking_id`)',
            'idx_refunds_payment_id' => 'ALTER TABLE `refunds` ADD INDEX `idx_refunds_payment_id` (`payment_id`)',
            'idx_refunds_gateway_ref' => 'ALTER TABLE `refunds` ADD INDEX `idx_refunds_gateway_ref` (`gateway_refund_id`)',
        ],
        'notifications' => [
            'idx_notifications_ref' => 'ALTER TABLE `notifications` ADD INDEX `idx_notifications_ref` (`reference_type`, `reference_id`)',
        ],
        'online_transactions' => [
            'idx_online_tx_customer_id' => 'ALTER TABLE `online_transactions` ADD INDEX `idx_online_tx_customer_id` (`customer_id`)',
        ],
        'users' => [
            'idx_users_phone' => 'ALTER TABLE `users` ADD INDEX `idx_users_phone` (`phone`)',
        ],
        'trip_expenses' => [
            'idx_trip_expenses_receipt' => 'ALTER TABLE `trip_expenses` ADD INDEX `idx_trip_expenses_receipt` (`receipt_number`)',
        ],
        'bookings' => [
            'idx_bookings_customer_status' => 'ALTER TABLE `bookings` ADD INDEX `idx_bookings_customer_status` (`customer_profile_id`, `status`)',
        ],
        'finance_ledger' => [
            'idx_finance_ledger_perf' => 'ALTER TABLE `finance_ledger` ADD INDEX `idx_finance_ledger_perf` (`account_id`, `transaction_type`, `transacted_at`)',
        ],
    ];

    public function up(PDO $pdo): void
    {
        foreach ($this->indexes as $table => $indexMap) {
            // Check if table exists
            $tCheck = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if (!$tCheck->fetch()) {
                continue;
            }

            foreach ($indexMap as $indexName => $alterSql) {
                $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?");
                $stmt->execute([$indexName]);
                if (!$stmt->fetch()) {
                    try {
                        $pdo->exec($alterSql);
                    } catch (\Throwable $e) {
                        // Suppress if column is missing or index already exists under different name
                    }
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        foreach ($this->indexes as $table => $indexMap) {
            $tCheck = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if (!$tCheck->fetch()) {
                continue;
            }

            foreach ($indexMap as $indexName => $alterSql) {
                $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?");
                $stmt->execute([$indexName]);
                if ($stmt->fetch()) {
                    try {
                        $pdo->exec("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                    } catch (\Throwable $e) {
                        // Suppress
                    }
                }
            }
        }
    }
};

