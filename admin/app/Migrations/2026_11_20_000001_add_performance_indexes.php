<?php
/**
 * Migration: Add Performance Indexes for Rapid Querying
 * Adds composite indexes on high-frequency query paths:
 * - notifications: (user_id, is_read, created_at)
 * - payments: (status, paid_at)
 * - bookings: (status, start_date)
 * - invoices: (payment_status, issued_at)
 * - employees: (department, status)
 */

return new class {
    private function hasIndex(PDO $pdo, string $table, string $indexName): bool
    {
        try {
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            $safeIndex = preg_replace('/[^a-zA-Z0-9_]/', '', $indexName);
            $stmt = $pdo->query("SHOW INDEX FROM `{$safeTable}` WHERE `Key_name` = '{$safeIndex}'");
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hasTable(PDO $pdo, string $table): bool
    {
        try {
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            $stmt = $pdo->query("SHOW TABLES LIKE '{$safeTable}'");
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function up(PDO $pdo): void
    {
        // 1. notifications (user_id, is_read, created_at) for navbar unread badges & alerts
        if ($this->hasTable($pdo, 'notifications') && !$this->hasIndex($pdo, 'notifications', 'idx_notif_user_read_created')) {
            $pdo->exec("ALTER TABLE `notifications` ADD INDEX `idx_notif_user_read_created` (`user_id`, `is_read`, `created_at`)");
        }

        // 2. payments (status, paid_at) for dashboard revenue aggregation
        if ($this->hasTable($pdo, 'payments') && !$this->hasIndex($pdo, 'payments', 'idx_payments_status_paid_at')) {
            $pdo->exec("ALTER TABLE `payments` ADD INDEX `idx_payments_status_paid_at` (`status`, `paid_at`)");
        }

        // 3. bookings (status, start_date) for upcoming trips and active booking lookups
        if ($this->hasTable($pdo, 'bookings') && !$this->hasIndex($pdo, 'bookings', 'idx_bookings_status_start_date')) {
            $pdo->exec("ALTER TABLE `bookings` ADD INDEX `idx_bookings_status_start_date` (`status`, `start_date`)");
        }

        // 4. invoices (payment_status, issued_at) for financial revenue queries
        if ($this->hasTable($pdo, 'invoices') && !$this->hasIndex($pdo, 'invoices', 'idx_invoices_status_issued')) {
            $pdo->exec("ALTER TABLE `invoices` ADD INDEX `idx_invoices_status_issued` (`payment_status`, `issued_at`)");
        }

        // 5. employees (department, status) for directory filter & count queries
        if ($this->hasTable($pdo, 'employees') && !$this->hasIndex($pdo, 'employees', 'idx_employees_dept_status')) {
            $pdo->exec("ALTER TABLE `employees` ADD INDEX `idx_employees_dept_status` (`department`, `status`)");
        }
    }

    public function down(PDO $pdo): void
    {
        if ($this->hasTable($pdo, 'notifications') && $this->hasIndex($pdo, 'notifications', 'idx_notif_user_read_created')) {
            $pdo->exec("ALTER TABLE `notifications` DROP INDEX `idx_notif_user_read_created`");
        }
        if ($this->hasTable($pdo, 'payments') && $this->hasIndex($pdo, 'payments', 'idx_payments_status_paid_at')) {
            $pdo->exec("ALTER TABLE `payments` DROP INDEX `idx_payments_status_paid_at`");
        }
        if ($this->hasTable($pdo, 'bookings') && $this->hasIndex($pdo, 'bookings', 'idx_bookings_status_start_date')) {
            $pdo->exec("ALTER TABLE `bookings` DROP INDEX `idx_bookings_status_start_date`");
        }
        if ($this->hasTable($pdo, 'invoices') && $this->hasIndex($pdo, 'invoices', 'idx_invoices_status_issued')) {
            $pdo->exec("ALTER TABLE `invoices` DROP INDEX `idx_invoices_status_issued`");
        }
        if ($this->hasTable($pdo, 'employees') && $this->hasIndex($pdo, 'employees', 'idx_employees_dept_status')) {
            $pdo->exec("ALTER TABLE `employees` DROP INDEX `idx_employees_dept_status`");
        }
    }
};
