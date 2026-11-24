<?php
/**
 * Tours & Travels Database Migration CLI Runner
 *
 * Usage:
 *   php admin/bin/migrate.php
 *   php admin/bin/migrate.php --fresh (Drops and recreates all tables)
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Config;
use App\Seeders\DatabaseSeeder;

echo "\n=========================================================\n";
echo "       TOURS & TRAVELS — DATABASE MIGRATION RUNNER        \n";
echo "=========================================================\n";

$isFresh = in_array('--fresh', $argv ?? [], true);

try {
    $config = Config::get('database.default');
    $dbName = $config['database'] ?? 'unknown';
    $dbHost = $config['host'] ?? '127.0.0.1';
    echo "Connecting to target database: `{$dbName}` on `{$dbHost}`...\n";

    // 1. Ensure database exists
    Database::createDatabaseIfNotExists();
    $pdo = Database::getConnection();
    echo "[PASS] Connected successfully to database: `{$dbName}`\n";

    if ($isFresh) {
        echo "[WARN] --fresh flag detected: Dropping all existing tables...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
            echo "  - Dropped table: `{$table}`\n";
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "[PASS] All tables dropped successfully.\n";
    }

    // 2. Ensure schema_migrations table
    MigrationManager::ensureMigrationTable($pdo);

    // 3. Get pending migrations
    $pending = MigrationManager::getPendingMigrations($pdo);
    echo "Pending migrations to run: " . count($pending) . "\n\n";

    if (!empty($pending)) {
        $res = MigrationManager::runPending($pdo, true);
        if (!$res['success']) {
            echo "\n[FAIL] Migration failed!\n";
            foreach ($res['errors'] as $err) {
                echo "  • {$err['file']}: {$err['message']}\n";
            }
            exit(1);
        }
        foreach ($res['migrated'] as $m) {
            echo "  [OK] Migrated: {$m['file']} ({$m['duration_ms']}ms)\n";
        }
    } else {
        echo "Database is already up to date (0 pending migrations).\n";
    }

    // 4. Seed essential data
    echo "\nVerifying initial system seed accounts...\n";
    $seeded = DatabaseSeeder::run($pdo);
    if (!empty($seeded)) {
        foreach ($seeded as $item) {
            echo "  [SEED] {$item}\n";
        }
    } else {
        echo "  [INFO] Default seed accounts already exist.\n";
    }

    // 5. Update lock file
    MigrationManager::lockInstallation([
        'source'         => 'cli_runner',
        'database'       => $dbName,
        'migrated_count' => count($pending),
        'app_version'    => Config::get('app.version', '1.0.0'),
        'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    echo "\n=========================================================\n";
    echo "[SUCCESS] Database migration completed successfully!\n";
    echo "Default Owner Login Credentials:\n";
    echo "  • Username: owner\n";
    echo "  • Password: owner123\n";
    echo "=========================================================\n\n";
    exit(0);
} catch (\Throwable $e) {
    echo "\n[ERROR] Migration aborted: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
