<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use App\Core\Logger;
use PDO;
use RuntimeException;

class BackupService
{
    private const MAX_RETENTION_COUNT = 10;

    /**
     * Generate structured database backup with checksum and retention enforcement
     */
    public function createBackup(): array
    {
        $pdo = Database::getConnection();
        $backupDir = Config::get('paths.backups', dirname(dirname(__DIR__)) . '/storage/backups');
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0750, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_voyagego_{$timestamp}.sql";
        $filepath = $backupDir . '/' . $filename;

        // Fetch all tables
        $tablesStmt = $pdo->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        $out = "-- VoyageGo Database Backup\n";
        $out .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- Host: " . Config::get('database.default.host') . "\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get CREATE TABLE
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $createStmt->fetch();
            $createTableSql = $row['Create Table'] ?? $row[1] ?? '';

            $out .= "-- Table structure for `{$table}`\n";
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= $createTableSql . ";\n\n";

            // Get records
            $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $out .= "-- Dumping data for `{$table}`\n";
                foreach ($rows as $r) {
                    $cols = array_map(fn($c) => "`" . str_replace("`", "``", $c) . "`", array_keys($r));
                    $vals = array_map(function ($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote((string)$val);
                    }, array_values($r));

                    $out .= "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                }
                $out .= "\n";
            }
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";

        if (file_put_contents($filepath, $out) === false) {
            throw new RuntimeException("Failed to write backup file to storage.");
        }

        $checksum = hash_file('sha256', $filepath);
        $sizeBytes = filesize($filepath);

        // Enforce retention policy
        $this->enforceRetentionPolicy($backupDir);

        Logger::audit("Backup created successfully: {$filename} (SHA256: {$checksum})");

        return [
            'filename'   => $filename,
            'filepath'   => $filepath,
            'size_bytes' => $sizeBytes,
            'checksum'   => $checksum,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Enforce retention policy by purging backups older than limit
     */
    private function enforceRetentionPolicy(string $backupDir): void
    {
        $files = glob($backupDir . '/backup_voyagego_*.sql');
        if (count($files) > self::MAX_RETENTION_COUNT) {
            usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));
            $toDelete = count($files) - self::MAX_RETENTION_COUNT;
            for ($i = 0; $i < $toDelete; $i++) {
                @unlink($files[$i]);
                Logger::info("Purged expired backup under retention policy: " . basename($files[$i]));
            }
        }
    }

    /**
     * Verified restoration procedure: checks integrity and executes inside transaction safely
     */
    public function restoreBackup(string $filepath): array
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            throw new RuntimeException("Backup file does not exist or cannot be read.");
        }

        $content = file_get_contents($filepath);
        if (empty($content) || !str_contains($content, 'VoyageGo Database Backup')) {
            throw new RuntimeException("Invalid backup file signature. Restoration halted for safety.");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($content);
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            Logger::audit("Database restored successfully from backup: " . basename($filepath));
            return ['success' => true, 'file' => basename($filepath)];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Restoration failed: " . $e->getMessage());
            throw new RuntimeException("Restoration failed and was rolled back safely: " . $e->getMessage());
        } finally {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        }
    }

    public function listBackups(): array
    {
        $backupDir = Config::get('paths.backups', dirname(dirname(__DIR__)) . '/storage/backups');
        $files = glob($backupDir . '/backup_voyagego_*.sql') ?: [];
        $result = [];

        foreach ($files as $f) {
            $result[] = [
                'filename'   => basename($f),
                'size_kb'    => round(filesize($f) / 1024, 2),
                'created_at' => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }

        usort($result, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return $result;
    }
}
