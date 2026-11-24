<?php
/**
 * Migration 019: Allow NULL dispatch_id in GPS Logs for On-Duty Breadcrumbs
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `gps_logs` MODIFY COLUMN `dispatch_id` INT NULL;");
    }

    public function down(PDO $pdo): void {}
};
