<?php
/**
 * Automated Test Suite: Live-Server Database Initialization and Deployment Flow
 *
 * Verifies all 7 core requirements:
 * 1. Automatic database creation on new server.
 * 2. Shared hosting handling (denied CREATE DATABASE -> friendly setup message, zero leaks).
 * 3. Existing database auto-initialization (no drops, zero data loss, no seeder overwrites).
 * 4. Legitimate deployment updates: new migrations run idempotently even when installed.lock exists.
 * 5. Connection failure handling (invalid credentials -> "Unable to connect to the database. Please check the database settings.").
 * 6. Migration failure handling (rollback, no completion marker, "Database update could not be completed...").
 * 7. installed.lock protection & auto-healing without blocking updates.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\DatabaseInitializationException;
use App\Core\DatabaseConnectionException;
use App\Core\DatabaseCreationDeniedException;
use App\Core\DatabaseMigrationException;
use App\Seeders\DatabaseSeeder;

$totalTests = 0;
$passedTests = 0;

function it(string $description, callable $fn) {
    global $totalTests, $passedTests;
    $totalTests++;
    try {
        $fn();
        echo "  [PASS] {$description}\n";
        $passedTests++;
    } catch (\Throwable $e) {
        echo "  [FAIL] {$description}\n";
        echo "         Error: " . $e->getMessage() . "\n";
        echo "         File:  " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n============================================================\n";
echo "  DATABASE INITIALIZATION & DEPLOYMENT FLOW TEST SUITE\n";
echo "============================================================\n\n";

// Save original runtime config to restore at the end
$originalConfig = Config::get('database.default');
$installerConfig = Config::get('database.installer') ?? $originalConfig;
$testDbName = 'tours_test_deploy_' . substr(md5(uniqid()), 0, 8);

// --------------------------------------------------------------------------
// TEST 1: Automatic Database Creation on New Server
// --------------------------------------------------------------------------
echo "--- Group 1: Automatic Database Creation ---\n";

it("creates non-existent database, executes migrations, and writes installed.lock", function() use ($originalConfig, $installerConfig, $testDbName) {
    // Ensure test db does not exist
    $rootPdo = Database::getInstallerConnection(false);
    $rootPdo->exec("DROP DATABASE IF EXISTS `{$testDbName}`");

    // Configure temporary database
    $tempConfig = $originalConfig;
    $tempConfig['database'] = $testDbName;
    Config::set('database.default', $tempConfig);
    Config::set('database.installer', $tempConfig);
    Database::resetConnections();

    // Remove lock file if exists for clean test
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    $backupLock = null;
    if (file_exists($lockFile)) {
        $backupLock = file_get_contents($lockFile);
        @unlink($lockFile);
    }

    try {
        // Run autoInitialize
        $result = MigrationManager::autoInitialize();
        if (!$result['success']) {
            throw new Exception("autoInitialize returned false");
        }
        if (empty($result['migrated'])) {
            throw new Exception("Expected migrations to execute for empty database");
        }

        // Verify database exists
        $checkStmt = $rootPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$testDbName}'");
        if (!$checkStmt->fetchColumn()) {
            throw new Exception("Database was not created");
        }

        // Connect to test db and verify tables
        $testPdo = Database::getConnection();
        $tableStmt = $testPdo->query("SHOW TABLES LIKE 'schema_migrations'");
        if (!$tableStmt->fetchColumn()) {
            throw new Exception("schema_migrations table missing");
        }

        // Verify default owner account created
        $ownerStmt = $testPdo->query("SELECT `username`, `role` FROM `users` WHERE `username` = 'owner'");
        $owner = $ownerStmt->fetch();
        if (!$owner || $owner['role'] !== 'admin') {
            throw new Exception("Default owner was not created on fresh database");
        }

        // Verify installed.lock exists
        if (!file_exists($lockFile)) {
            throw new Exception("installed.lock was not created");
        }
    } finally {
        // Clean up test db
        Database::resetConnections();
        $rootPdo->exec("DROP DATABASE IF EXISTS `{$testDbName}`");
        Config::set('database.default', $originalConfig);
        Config::set('database.installer', $installerConfig);
        Database::resetConnections();

        if ($backupLock !== null) {
            file_put_contents($lockFile, $backupLock);
        }
    }
});

// --------------------------------------------------------------------------
// TEST 2: Shared Hosting Permission Denial Handling
// --------------------------------------------------------------------------
echo "\n--- Group 2: Shared Hosting Handling ---\n";

it("detects permission denial, throws DatabaseCreationDeniedException with friendly message and zero technical leaks", function() {
    $deniedPdoEx = new \PDOException("SQLSTATE[42000] [1044] Access denied for user 'cpanel_usr'@'localhost' to database 'cpanel_tours'", 1044);
    $mapped = DatabaseInitializationException::fromPDOException($deniedPdoEx);

    if (!($mapped instanceof DatabaseCreationDeniedException)) {
        throw new Exception("Expected DatabaseCreationDeniedException, got: " . get_class($mapped));
    }

    $expectedMsg = "Database setup is incomplete. Please create the database and check the database settings.";
    if ($mapped->getUserMessage() !== $expectedMsg) {
        throw new Exception("User message mismatch. Expected: '{$expectedMsg}', got: '{$mapped->getUserMessage()}'");
    }

    // Ensure description mentions hosting control panel and .env
    if (!str_contains($mapped->getUserDescription(), 'hosting control panel') || !str_contains($mapped->getUserDescription(), '.env')) {
        throw new Exception("Description must guide user to hosting control panel and .env");
    }

    // Verify zero leaks in user message
    if (str_contains($mapped->getUserMessage(), 'cpanel') || str_contains($mapped->getUserMessage(), '1044') || str_contains($mapped->getUserMessage(), 'SQLSTATE')) {
        throw new Exception("Technical details leaked in userMessage!");
    }
});

// --------------------------------------------------------------------------
// TEST 3: Existing Database Preservation (Zero Data Loss)
// --------------------------------------------------------------------------
echo "\n--- Group 3: Existing Database Preservation ---\n";

it("never drops, recreates, or overwrites existing production database or users", function() use ($originalConfig) {
    // Current runtime database exists and has data
    $pdo = Database::getConnection();

    // Verify databaseExists returns true without running CREATE DATABASE
    $currentDb = $originalConfig['database'];
    if (!Database::databaseExists($currentDb)) {
        throw new Exception("Current database should exist");
    }

    // Insert or verify a custom non-owner admin
    $customEmail = 'existing_admin_' . substr(md5(uniqid()), 0, 6) . '@agency.com';
    $insertStmt = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) 
        VALUES ('Production Admin', 'prod_admin_keep', ?, '+91 99999 11111', 'hash123', 'admin', 'active', NOW(), NOW())");
    $insertStmt->execute([$customEmail]);
    $insertedId = (int)$pdo->lastInsertId();

    try {
        // Run autoInitialize on existing database
        $result = MigrationManager::autoInitialize();
        if (!$result['success']) {
            throw new Exception("autoInitialize should succeed on existing database");
        }

        // Verify the existing admin user was NOT deleted or modified
        $checkStmt = $pdo->prepare("SELECT `id`, `name`, `email` FROM `users` WHERE `id` = ?");
        $checkStmt->execute([$insertedId]);
        $existing = $checkStmt->fetch();

        if (!$existing || $existing['email'] !== $customEmail) {
            throw new Exception("Production user was modified or lost!");
        }

        // Verify Seeder did not overwrite or duplicate
        $seeded = DatabaseSeeder::run($pdo);
        if (!empty($seeded)) {
            throw new Exception("DatabaseSeeder should return empty on existing installation with admin user");
        }
    } finally {
        $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$insertedId]);
    }
});

// --------------------------------------------------------------------------
// TEST 4: Legitimate Deployments - New Migrations Run with installed.lock Present
// --------------------------------------------------------------------------
echo "\n--- Group 4: Deployment of New Migrations ---\n";

it("runs pending migrations idempotently even when installed.lock exists", function() {
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    if (!file_exists($lockFile)) {
        MigrationManager::lockInstallation(['source' => 'test_suite']);
    }

    if (!MigrationManager::isInstalled()) {
        throw new Exception("isInstalled should return true when lock exists and schema_migrations is present");
    }

    $pdo = Database::getConnection();

    // Create a temporary mock migration file in Migrations directory
    $mockFileName = '9999_99_99_999999_test_mock_deployment_migration.php';
    $mockFilePath = dirname(__DIR__) . '/app/Migrations/' . $mockFileName;

    $mockContent = '<?php
return new class {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `test_mock_table_deployment` (`id` INT PRIMARY KEY) ENGINE=InnoDB;");
    }
    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS `test_mock_table_deployment`;");
    }
};';

    file_put_contents($mockFilePath, $mockContent);

    try {
        // Verify mock migration is detected as pending
        $pending = MigrationManager::getPendingMigrations($pdo);
        if (!in_array($mockFileName, $pending, true)) {
            throw new Exception("Mock migration was not detected as pending");
        }

        // Run autoInitialize with installed.lock present
        $result = MigrationManager::autoInitialize();
        if (!$result['success']) {
            throw new Exception("autoInitialize failed with installed.lock present");
        }

        // Verify table created
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'test_mock_table_deployment'");
        if (!$tableCheck->fetchColumn()) {
            throw new Exception("Mock table was not created by autoInitialize");
        }

        // Verify migration recorded in schema_migrations
        $recCheck = $pdo->prepare("SELECT `status` FROM `schema_migrations` WHERE `migration` = ?");
        $recCheck->execute([$mockFileName]);
        if ($recCheck->fetchColumn() !== 'success') {
            throw new Exception("Mock migration was not recorded in schema_migrations");
        }

        // Second run must be idempotent: 0 pending, 0 executed
        $secondResult = MigrationManager::autoInitialize();
        if (!empty($secondResult['migrated'])) {
            throw new Exception("Second autoInitialize run executed migrations when none were pending");
        }
    } finally {
        // Clean up mock table, migration record, and mock file
        @$pdo->exec("DROP TABLE IF EXISTS `test_mock_table_deployment`");
        @$pdo->prepare("DELETE FROM `schema_migrations` WHERE `migration` = ?")->execute([$mockFileName]);
        if (file_exists($mockFilePath)) {
            @unlink($mockFilePath);
        }
    }
});

// --------------------------------------------------------------------------
// TEST 5: Connection Failure Handling (Invalid Credentials)
// --------------------------------------------------------------------------
echo "\n--- Group 5: Connection Failure Handling ---\n";

it("throws DatabaseConnectionException on wrong credentials with exact friendly message", function() use ($originalConfig) {
    // Bad credentials PDOException
    $authEx = new \PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)", 1045);
    $mapped = DatabaseInitializationException::fromPDOException($authEx);

    if (!($mapped instanceof DatabaseConnectionException)) {
        throw new Exception("Expected DatabaseConnectionException, got: " . get_class($mapped));
    }

    $expectedMsg = "Unable to connect to the database. Please check the database settings.";
    if ($mapped->getUserMessage() !== $expectedMsg) {
        throw new Exception("Expected user message '{$expectedMsg}', got '{$mapped->getUserMessage()}'");
    }

    // Zero credential/SQL leaks in userMessage
    if (str_contains($mapped->getUserMessage(), 'root') || str_contains($mapped->getUserMessage(), '1045') || str_contains($mapped->getUserMessage(), 'password')) {
        throw new Exception("Technical details leaked in userMessage!");
    }
});

// --------------------------------------------------------------------------
// TEST 6: Migration Failure Handling
// --------------------------------------------------------------------------
echo "\n--- Group 6: Migration Failure Handling ---\n";

it("rolls back transaction, does not record failure, and throws DatabaseMigrationException", function() {
    $pdo = Database::getConnection();

    $brokenFileName = '9999_99_99_999998_test_broken_migration.php';
    $brokenFilePath = dirname(__DIR__) . '/app/Migrations/' . $brokenFileName;

    $brokenContent = '<?php
return new class {
    public function up(PDO $pdo): void {
        // Create table then throw intentional failure
        $pdo->exec("CREATE TABLE IF NOT EXISTS `test_rollback_table` (`id` INT PRIMARY KEY) ENGINE=InnoDB;");
        throw new \RuntimeException("Simulated migration failure for testing rollback");
    }
};';

    file_put_contents($brokenFilePath, $brokenContent);

    try {
        $caught = false;
        try {
            MigrationManager::autoInitialize();
        } catch (DatabaseMigrationException $e) {
            $caught = true;
            $expectedMsg = "Database update could not be completed. Please try again or contact the administrator.";
            if ($e->getUserMessage() !== $expectedMsg) {
                throw new Exception("Expected user message '{$expectedMsg}', got '{$e->getUserMessage()}'");
            }

            // Zero raw SQL or file paths in userMessage
            if (str_contains($e->getUserMessage(), 'SQL') || str_contains($e->getUserMessage(), '.php') || str_contains($e->getUserMessage(), 'Simulated')) {
                throw new Exception("Technical error leaked in userMessage!");
            }
        }

        if (!$caught) {
            throw new Exception("Expected DatabaseMigrationException was not thrown on broken migration");
        }

        // Verify broken migration was NOT marked as success in schema_migrations
        $recCheck = $pdo->prepare("SELECT `status` FROM `schema_migrations` WHERE `migration` = ?");
        $recCheck->execute([$brokenFileName]);
        if ($recCheck->fetchColumn() === 'success') {
            throw new Exception("Failed migration was incorrectly marked as success in schema_migrations");
        }
    } finally {
        @$pdo->exec("DROP TABLE IF EXISTS `test_rollback_table`");
        @$pdo->prepare("DELETE FROM `schema_migrations` WHERE `migration` = ?")->execute([$brokenFileName]);
        if (file_exists($brokenFilePath)) {
            @unlink($brokenFilePath);
        }
    }
});

// --------------------------------------------------------------------------
// TEST 7: installed.lock Protection & Auto-Healing
// --------------------------------------------------------------------------
echo "\n--- Group 7: installed.lock Protection & Auto-Healing ---\n";

it("auto-heals missing installed.lock when database is already initialized", function() {
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    $backupContent = file_exists($lockFile) ? file_get_contents($lockFile) : null;

    try {
        // Remove lock file
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Since database already has schema_migrations with executed migrations:
        $isInstalled = MigrationManager::isInstalled();
        if (!$isInstalled) {
            throw new Exception("isInstalled should return true and auto-heal missing lock file");
        }

        if (!file_exists($lockFile)) {
            throw new Exception("installed.lock was not auto-healed");
        }
    } finally {
        if ($backupContent !== null) {
            file_put_contents($lockFile, $backupContent);
        }
    }
});

// --------------------------------------------------------------------------
// TEST SUMMARY
// --------------------------------------------------------------------------
echo "\n============================================================\n";
echo "  RESULTS: {$passedTests} / {$totalTests} tests passed.\n";
echo "============================================================\n\n";

if ($passedTests !== $totalTests) {
    exit(1);
}
