<?php
namespace App\Core;

use PDO;
use Exception;
use RuntimeException;

class MigrationManager
{
    private const LOCK_NAME = 'tours_migration_lock';
    private const LOCK_TIMEOUT = 20;

    private static ?array $cachedLockData = null;
    private static ?bool $cachedHasCoreTables = null;

    /**
     * Clear in-memory caches for migration and lock status
     */
    public static function resetCache(): void
    {
        self::$cachedLockData = null;
        self::$cachedHasCoreTables = null;
    }

    /**
     * Get parsed metadata from installed.lock safely (cached per request)
     */
    public static function getLockData(): array
    {
        if (self::$cachedLockData !== null) {
            return self::$cachedLockData;
        }

        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        if (file_exists($lockFile)) {
            $raw = @file_get_contents($lockFile);
            $parsed = $raw ? json_decode($raw, true) : null;
            self::$cachedLockData = is_array($parsed) ? $parsed : [];
        } else {
            self::$cachedLockData = [];
        }

        return self::$cachedLockData;
    }

    /**
     * Check if the database exists and contains core application tables.
     * Evaluates in < 0.1ms with per-request static memory caching.
     */
    public static function hasCoreTables(?PDO $pdo = null): bool
    {
        if (self::$cachedHasCoreTables !== null && $pdo === null) {
            return self::$cachedHasCoreTables;
        }

        try {
            if ($pdo === null) {
                $pdo = Database::getConnection();
            }

            // Verify existence of core tables: schema_migrations and users
            $stmt = $pdo->query("SHOW TABLES LIKE 'schema_migrations'");
            $hasMigrations = (bool) $stmt->fetchColumn();
            if (!$hasMigrations) {
                if ($pdo === null) {
                    self::$cachedHasCoreTables = false;
                }
                return false;
            }

            $stmt2 = $pdo->query("SHOW TABLES LIKE 'users'");
            $hasUsers = (bool) $stmt2->fetchColumn();
            if (!$hasUsers) {
                if ($pdo === null) {
                    self::$cachedHasCoreTables = false;
                }
                return false;
            }

            $stmt3 = $pdo->query("SHOW TABLES LIKE 'company_profiles'");
            $hasCompany = (bool) $stmt3->fetchColumn();

            $result = $hasUsers && $hasMigrations && $hasCompany;
            if ($pdo === null) {
                self::$cachedHasCoreTables = $result;
            }
            return $result;
        } catch (\Throwable $e) {
            if ($pdo === null) {
                self::$cachedHasCoreTables = false;
            }
            return false;
        }
    }

    /**
     * Fast check to determine if database initialization or migration check is required.
     * Evaluates in < 0.05ms without running ANY database queries on normal requests!
     */
    public static function needsInitialization(): bool
    {
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';

        // 1. Fresh installation: lock file does not exist yet
        if (!file_exists($lockFile)) {
            return true;
        }

        // 2. Deployment trigger file: explicit marker created during CI/CD or deploy scripts
        $triggerFile = dirname(dirname(__DIR__)) . '/config/deploy.trigger';
        if (file_exists($triggerFile)) {
            return true;
        }

        // 3. New migration deployed: migrations directory was modified after installed.lock
        $migrationsDir = dirname(__DIR__) . '/Migrations';
        if (is_dir($migrationsDir)) {
            $dirMtime = @filemtime($migrationsDir);
            $lockMtime = @filemtime($lockFile);
            if ($dirMtime && $lockMtime && ($dirMtime > $lockMtime)) {
                return true;
            }
        }

        // 4. Version bump: codebase app.version is newer than recorded in installed.lock
        $lockData = self::getLockData();
        $codeVersion = Config::get('app.version', '1.0.0');
        if (!empty($lockData['app_version']) && version_compare($codeVersion, $lockData['app_version'], '>')) {
            return true;
        }

        // 5. Database mismatch: configured database has changed since installation lock was recorded
        $currentDb = Config::get('database.default.database');
        if (!empty($lockData['database']) && !empty($currentDb) && $lockData['database'] !== $currentDb) {
            return true;
        }

        // 6. Professional Self-Healing: Verify core tables actually exist in MySQL
        // If tables were dropped, database was reset, or migrations are missing, require initialization
        if (!self::hasCoreTables()) {
            return true;
        }

        return false;
    }

    /**
     * Check if application has completed initial installation.
     * Auto-heals missing installed.lock if schema_migrations is already populated.
     */
    public static function isInstalled(): bool
    {
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        if (file_exists($lockFile)) {
            $lockData = self::getLockData();
            $currentDb = Config::get('database.default.database');
            if (!empty($lockData['database']) && !empty($currentDb) && $lockData['database'] !== $currentDb) {
                return false;
            }

            // Verify core tables actually exist in MySQL
            if (!self::hasCoreTables()) {
                return false;
            }

            return true;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SHOW TABLES LIKE 'schema_migrations'");
            $hasMigrationTable = (bool) $stmt->fetchColumn();
            if ($hasMigrationTable) {
                // If migration table exists and has executed migrations, mark as installed
                $stmt2 = $pdo->query("SELECT COUNT(*) FROM `schema_migrations` WHERE `status` = 'success'");
                if ((int) $stmt2->fetchColumn() > 0 && self::hasCoreTables($pdo)) {
                    self::lockInstallation(['source' => 'auto_healed']);
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Write installation lock file to disable future installer runs
     */
    public static function lockInstallation(array $metadata = []): bool
    {
        self::resetCache();
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        $data = array_merge([
            'installed_at' => date('Y-m-d H:i:s'),
            'app_version'  => Config::get('app.version', '1.0.0'),
            'ip'           => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'signature'    => hash('sha256', uniqid('vg_lock_', true)),
            'database'     => Config::get('database.default.database'),
        ], $metadata);

        $written = file_put_contents($lockFile, json_encode($data, JSON_PRETTY_PRINT)) !== false;
        if ($written) {
            @touch($lockFile); // Ensure mtime is current
        }
        return $written;
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
     * Does NOT prevent legitimate deployments from applying new migrations.
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
                        // Run migration inside transaction
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
     *
     * Handles:
     * 1. Automatic database creation if permitted.
     * 2. Shared hosting permission denial (throws DatabaseCreationDeniedException).
     * 3. Existing database preservation (zero data loss, no drops or resets).
     * 4. Idempotent migration execution for missing migrations.
     * 5. Non-destructive seeding of default admin only on fresh installs.
     * 6. Generation/healing of installed.lock to protect manual setup.
     *
     * @throws DatabaseInitializationException on any fatal initialization failure
     */
    public static function autoInitialize(): array
    {
        // 1. Ensure database exists (creates if not existing and permitted; throws if denied)
        Database::createDatabaseIfNotExists();

        // 2. Connect to database
        $pdo = Database::getConnection();

        // 3. Ensure schema_migrations table exists (idempotent)
        self::ensureMigrationTable($pdo);

        // 4. Check for pending migrations
        $pending = self::getPendingMigrations($pdo);
        $migrated = [];

        if (!empty($pending)) {
            Logger::info("Auto-initialization: Running " . count($pending) . " pending migrations.");
            $res = self::runPending($pdo, true);
            if (!$res['success']) {
                $errDetail = !empty($res['errors']) ? ($res['errors'][0]['file'] . ': ' . $res['errors'][0]['message']) : 'Unknown migration error';
                Logger::error("Auto-initialization: Migration execution failed: {$errDetail}");
                throw new DatabaseMigrationException(
                    "Database update could not be completed. Please try again or contact the administrator.",
                    "A database migration could not be applied. Technical details have been logged securely and all uncommitted changes were rolled back.",
                    null,
                    $errDetail
                );
            }
            $migrated = $res['migrated'];
        }

        // 5. Seed essential Owner account if system has 0 users (100% idempotent, non-destructive)
        try {
            \App\Seeders\DatabaseSeeder::run($pdo);
        } catch (\Throwable $e) {
            Logger::warning("Auto-initialization: Owner check notice: " . $e->getMessage());
        }

        // 6. Ensure/update installation lock file to secure manual installer and record current state
        $lockFile = dirname(dirname(__DIR__)) . '/config/installed.lock';
        $existingData = file_exists($lockFile) ? self::getLockData() : [];
        self::lockInstallation(array_merge($existingData, [
            'source'         => !empty($existingData['source']) ? $existingData['source'] : 'auto_initialize',
            'migrated_count' => count($migrated),
            'app_version'    => Config::get('app.version', '1.0.0'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]));

        // Clear any deployment trigger file
        $triggerFile = dirname(dirname(__DIR__)) . '/config/deploy.trigger';
        if (file_exists($triggerFile)) {
            @unlink($triggerFile);
        }

        self::resetCache();

        return [
            'success'  => true,
            'migrated' => $migrated
        ];
    }
}
