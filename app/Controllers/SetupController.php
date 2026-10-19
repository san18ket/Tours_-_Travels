<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Session;
use App\Seeders\DatabaseSeeder;
use Throwable;

class SetupController extends Controller
{
    public function index(Request $request): Response
    {
        if (MigrationManager::isInstalled()) {
            return $this->render('setup/locked', [
                'title' => 'Installation Locked',
                'message' => 'The application has already been initialized and the installer is permanently disabled for security.'
            ], 'layouts/setup');
        }

        // Test database connectivity
        $config = Config::get('database.installer') ?? Config::get('database.default');
        $dbName = $config['database'];
        $canConnect = false;
        $dbExists = false;
        $connectionError = null;

        try {
            $pdo = Database::getInstallerConnection(false);
            $canConnect = true;
            $dbExists = Database::databaseExists($dbName);
        } catch (Throwable $e) {
            $connectionError = "Unable to connect to database server ({$config['host']}:{$config['port']}). Verify database credentials in config/database.php.";
            Logger::error("Setup connection test failed: " . $e->getMessage());
        }

        $pendingMigrations = [];
        if ($dbExists) {
            try {
                $runtimePdo = Database::getConnection();
                $pendingMigrations = MigrationManager::getPendingMigrations($runtimePdo);
            } catch (Throwable $e) {
                // Database exists but connection or schema check failed
                $pendingMigrations = MigrationManager::getAllMigrationFiles();
            }
        } else {
            $pendingMigrations = MigrationManager::getAllMigrationFiles();
        }

        return $this->render('setup/index', [
            'canConnect'         => $canConnect,
            'dbExists'           => $dbExists,
            'dbName'             => $dbName,
            'connectionError'    => $connectionError,
            'pendingMigrations'  => $pendingMigrations,
            'config'             => $config,
        ], 'layouts/setup');
    }

    public function run(Request $request): Response
    {
        if (MigrationManager::isInstalled()) {
            return $this->json(['success' => false, 'error' => 'Installation is locked.'], 403);
        }

        $seedData = (bool)$request->input('seed_data', true);
        $createDb = (bool)$request->input('create_db', true);

        try {
            // Step 1: Create Database if enabled and not existing
            if ($createDb) {
                Database::createDatabaseIfNotExists();
            }

            // Step 2: Connect to the runtime/installer DB
            $pdo = Database::getInstallerConnection(true);

            // Step 3: Run versioned idempotent migrations with locking
            $migrationResult = MigrationManager::runPending($pdo, true);
            if (!$migrationResult['success']) {
                $err = $migrationResult['errors'][0]['message'] ?? 'Migration failure.';
                throw new \RuntimeException("Migration error: {$err}");
            }

            // Step 4: Seed demo data if requested (idempotent, safe)
            $seedResult = [];
            if ($seedData) {
                $seedResult = DatabaseSeeder::run($pdo);
            }

            // Step 5: Lock installation to prevent unauthorized re-execution
            MigrationManager::lockInstallation([
                'seeded' => !empty($seedResult),
                'migrations_count' => count($migrationResult['migrated']),
            ]);

            Logger::info("Application installation and setup completed successfully.");

            if ($request->isJson()) {
                return $this->json([
                    'success'    => true,
                    'migrated'   => $migrationResult['migrated'],
                    'seeded'     => $seedResult,
                    'redirect'   => url('/login')
                ]);
            }

            Session::setFlash('success', 'Installation completed successfully! You may now sign in.');
            return $this->redirect(url('/login'));
        } catch (Throwable $e) {
            $incidentId = 'SETUP-ERR-' . strtoupper(substr(hash('sha256', uniqid('', true)), 0, 6));
            Logger::error("Setup failure [{$incidentId}]: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->isJson()) {
                return $this->json([
                    'success' => false,
                    'error'   => 'Installation failed. Incident code: ' . $incidentId,
                    'details' => $e->getMessage()
                ], 500);
            }

            return $this->render('setup/error', [
                'incidentId' => $incidentId,
                'message'    => $e->getMessage(),
            ], 'layouts/setup');
        }
    }
}
