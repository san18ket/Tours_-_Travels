<?php
namespace App\Core;

use PDO;
use Exception;
use RuntimeException;

class MigrationManager
{
    private const LOCK_NAME = 'voyagego_migration_lock';
    private const LOCK_TIMEOUT = 20;

    /**
     * Check if application has completed initial installation
     */
    public static function isInstalled(): bool
    {
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        if (!file_exists($lockFile)) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SHOW TABLES LIKE 'schema_migrations'");
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Write installation lock file to disable future installer runs
     */
    public static function lockInstallation(array $metadata = []): bool
    {
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        $data = array_merge([
            'installed_at' => date('Y-m-d H:i:s'),
            'app_version'  => Config::get('app.version', '1.0.0'),
            'ip'           => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'signature'    => hash('sha256', uniqid('vg_lock_', true)),
        ], $metadata);

        return file_put_contents($lockFile, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Create the migration history table if not exists (Idempotent)
     */
    public static function ensureMigrationTable(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'success',
            `executed_at` DATETIME NOT NULL,
            `execution_time_ms` INT NOT NULL DEFAULT 0,
            INDEX `idx_batch` (`batch`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
    }

    /**
     * Get list of all migration filenames sorted in chronological order
     */
    public static function getAllMigrationFiles(): array
    {
        $dir = dirname(__DIR__) . '/Migrations';
        if (!is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);
        $migrations = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrations[] = $file;
            }
        }

        sort($migrations, SORT_NATURAL);
        return $migrations;
    }

    /**
     * Get list of already executed migrations
     */
    public static function getExecutedMigrations(PDO $pdo): array
    {
        try {
            $stmt = $pdo->query("SELECT `migration` FROM `schema_migrations` WHERE `status` = 'success'");
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get pending migrations
     */
    public static function getPendingMigrations(?PDO $pdo = null): array
    {
        if ($pdo === null) {
            try {
                $pdo = Database::getConnection();
            } catch (\Throwable $e) {
                return self::getAllMigrationFiles();
            }
        }

        $all = self::getAllMigrationFiles();
        $executed = self::getExecutedMigrations($pdo);

        return array_values(array_diff($all, $executed));
    }

    /**
     * Lightweight check to see if database schema matches code version
     * (Safe for every request without acquiring DDL locks or altering database)
     */
    public static function isSchemaCurrent(): bool
    {
        if (!self::isInstalled()) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $pending = self::getPendingMigrations($pdo);
            return count($pending) === 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run all pending migrations securely inside transaction with advisory locking.
     * Never drops existing tables or silently overwrites business data.
     */
    public static function runPending(PDO $pdo, bool $isAuthorized = false): array
    {
        if (!$isAuthorized && self::isInstalled()) {
            throw new RuntimeException('Unauthorized migration attempt. Migrations must be executed via controlled installer or CLI.');
        }

        // Acquire advisory lock to prevent concurrent initialization
        if (!Database::acquireLock(self::LOCK_NAME, self::LOCK_TIMEOUT)) {
            throw new RuntimeException('Another migration process is currently running. Please try again shortly.');
        }

        $results = [
            'migrated' => [],
            'errors'   => [],
            'success'  => true,
        ];

        try {
            self::ensureMigrationTable($pdo);

            // Determine next batch number
            $stmt = $pdo->query("SELECT MAX(`batch`) FROM `schema_migrations`");
            $nextBatch = ((int) $stmt->fetchColumn()) + 1;

            $pending = self::getPendingMigrations($pdo);
            $dir = dirname(__DIR__) . '/Migrations';

            foreach ($pending as $file) {
                $filePath = $dir . '/' . $file;
                $startTime = microtime(true);

                try {
                    $migrationClass = require $filePath;
                    if (is_object($migrationClass) && method_exists($migrationClass, 'up')) {
                        // Run migration
                        if (!$pdo->inTransaction()) {
                            $pdo->beginTransaction();
                        }
                        $migrationClass->up($pdo);
                        if ($pdo->inTransaction()) {
                            $pdo->commit();
                        }

                        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

                        $insertStmt = $pdo->prepare(
                            "INSERT INTO `schema_migrations` (`migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) 
                             VALUES (?, ?, 'success', NOW(), ?)
                             ON DUPLICATE KEY UPDATE `status` = 'success', `batch` = VALUES(`batch`), `executed_at` = NOW()"
                        );
                        $insertStmt->execute([$file, $nextBatch, $durationMs]);

                        $results['migrated'][] = [
                            'file' => $file,
                            'duration_ms' => $durationMs,
                            'batch' => $nextBatch
                        ];

                        Logger::info("Migration executed successfully: {$file} ({$durationMs}ms)");
                    } else {
                        throw new RuntimeException("Migration file {$file} must return an object with an 'up(PDO \$pdo)' method.");
                    }
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $results['success'] = false;
                    $results['errors'][] = [
                        'file' => $file,
                        'message' => $e->getMessage()
                    ];

                    Logger::error("Migration failed for {$file}: " . $e->getMessage(), [
                        'file' => $file,
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Stop on first failure to prevent partial inconsistent states
                    break;
                }
            }
        } finally {
            Database::releaseLock(self::LOCK_NAME);
        }

        return $results;
    }

    /**
     * Automatically ensure database and approved tables exist.
     * Idempotent, safe, and runs with advisory locking.
     */
    public static function autoInitialize(): void
    {
        // 1. Ensure database exists
        try {
            Database::createDatabaseIfNotExists();
        } catch (\Throwable $e) {
            Logger::error("Auto-initialization: Database creation check failed: " . $e->getMessage());
            return;
        }

        // 2. Connect to database
        try {
            $pdo = Database::getConnection();
        } catch (\Throwable $e) {
            Logger::error("Auto-initialization: Database connection failed: " . $e->getMessage());
            return;
        }

        // 3. Check for pending migrations
        try {
            $pending = self::getPendingMigrations($pdo);
            if (!empty($pending)) {
                Logger::info("Auto-initialization: Running " . count($pending) . " pending migrations.");
                $res = self::runPending($pdo, true);
                if (!$res['success']) {
                    Logger::error("Auto-initialization: Migration execution encountered errors.");
                }

                // Ensure only the essential Owner account exists (no dummy data)
                try {
                    \App\Seeders\DatabaseSeeder::run($pdo);
                } catch (\Throwable $e) {
                    Logger::warning("Auto-initialization: Owner check failed: " . $e->getMessage());
                }

                // Ensure installation lock file exists
                if (!file_exists(dirname(dirname(__DIR__)) . '/config/installed.lock')) {
                    self::lockInstallation(['source' => 'auto_initialize']);
                }
            }
        } catch (\Throwable $e) {
            Logger::error("Auto-initialization: Migration execution failed: " . $e->getMessage());
        }
    }
}
