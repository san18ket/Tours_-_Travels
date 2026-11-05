<?php
namespace Tests;

use App\Core\Config;
use App\Core\Database;
use App\Core\MigrationManager;
use PDO;
use RuntimeException;

/**
 * Isolated Test Environment & Production Database Guard
 * Strictly prevents automated tests from modifying or executing against production/development databases.
 */
class TestEnvironment
{
    public const TEST_DB_NAME = 'tours_travels_test_db';
    public const FORBIDDEN_PROD_DB = 'tours_travels_db';

    private static ?PDO $pdo = null;
    private static bool $initialized = false;

    /**
     * Initialize test environment with strict database isolation guards.
     */
    public static function init(): PDO
    {
        // 1. Enforce APP_ENV=testing
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        // 2. Set DB_DATABASE to test database
        putenv('DB_DATABASE=' . self::TEST_DB_NAME);
        $_ENV['DB_DATABASE'] = self::TEST_DB_NAME;
        $_SERVER['DB_DATABASE'] = self::TEST_DB_NAME;

        // Reset config cache so config/database.php re-evaluates getenv('DB_DATABASE')
        if (class_exists(Config::class)) {
            Config::reset('database');
        }

        // 3. Reset existing connections
        Database::resetConnections();

        // 4. Ensure test database exists on server
        self::ensureTestDatabaseExists();

        // 5. Connect and strictly assert database isolation
        $pdo = Database::getConnection();
        self::assertIsolation($pdo);

        // 6. Run migrations to ensure all tables exist
        if (!self::$initialized) {
            MigrationManager::ensureMigrationTable($pdo);
            try {
                MigrationManager::runPending($pdo, true);
            } catch (\Throwable $e) {
                // If tables already exist or partial migration, verify core tables
            }
            self::$initialized = true;
        }

        self::$pdo = $pdo;
        return $pdo;
    }

    /**
     * Strict isolation assertion: Aborts immediately if connected to production DB.
     */
    public static function assertIsolation(?PDO $pdo = null): void
    {
        $pdo = $pdo ?? Database::getConnection();
        $stmt = $pdo->query("SELECT DATABASE()");
        $currentDb = (string) $stmt->fetchColumn();

        if (strtolower($currentDb) === strtolower(self::FORBIDDEN_PROD_DB)) {
            $msg = "FATAL SAFETY VIOLATION: Test suite attempted to run against primary/development database '{$currentDb}'! " .
                   "All test executions MUST run against dedicated test database '" . self::TEST_DB_NAME . "' with APP_ENV=testing.";
            fwrite(STDERR, "\n\033[1;31m[SAFETY GUARD ALERT]\033[0m " . $msg . "\n\n");
            throw new RuntimeException($msg);
        }

        if (strtolower($currentDb) !== strtolower(self::TEST_DB_NAME)) {
            $msg = "SAFETY GUARD VIOLATION: Connected to unknown database '{$currentDb}'. Expected '" . self::TEST_DB_NAME . "'.";
            fwrite(STDERR, "\n\033[1;33m[SAFETY GUARD ALERT]\033[0m " . $msg . "\n\n");
            throw new RuntimeException($msg);
        }
    }

    private static function ensureTestDatabaseExists(): void
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = (int)(getenv('DB_PORT') ?: 3306);
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $rawPdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $rawPdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::TEST_DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            return self::init();
        }
        self::assertIsolation(self::$pdo);
        return self::$pdo;
    }

    /**
     * Helper to create a test user safely in the test DB
     */
    public static function createTestUser(array $attributes = []): array
    {
        $pdo = self::getPdo();
        self::assertIsolation($pdo);

        $username = $attributes['username'] ?? ('test_' . bin2hex(random_bytes(4)));
        $email = $attributes['email'] ?? ($username . '@test.example.com');
        $password = password_hash($attributes['password'] ?? 'Test@123456', PASSWORD_BCRYPT);
        $role = $attributes['role'] ?? 'admin';
        $status = $attributes['status'] ?? 'active';

        $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $username,
            $attributes['name'] ?? 'Test User',
            $email,
            $password,
            $role,
            $status
        ]);

        $userId = (int) $pdo->lastInsertId();
        return [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'password_plain' => $attributes['password'] ?? 'Test@123456'
        ];
    }

    /**
     * Clean up test records after tests
     */
    public static function cleanupTestData(): void
    {
        $pdo = self::getPdo();
        self::assertIsolation($pdo);
        $pdo->exec("DELETE FROM `remember_tokens` WHERE `user_id` IN (SELECT id FROM `users` WHERE `email` LIKE '%@test.example.com' OR `username` LIKE 'test_%')");
        $pdo->exec("DELETE FROM `users` WHERE `email` LIKE '%@test.example.com' OR `username` LIKE 'test_%'");
    }
}
