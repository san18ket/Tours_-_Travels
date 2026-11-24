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
use App\Core\Env;
use App\Seeders\DatabaseSeeder;
use Throwable;
use PDOException;

class SetupController extends Controller
{
    public function index(Request $request): Response
    {
        if (MigrationManager::isInstalled()) {
            // Self-healing check: if the database is missing core tables, allow autoInitialize() to recover
            try {
                $pdo = Database::getConnection();
                $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
                if (!$tableCheck) {
                    MigrationManager::autoInitialize();
                    return Response::redirect(url('/setup/success'));
                }
            } catch (\Throwable $ex) {
                // Connection or table error, proceed with setup
            }

            return $this->render('setup/locked', [
                'title' => 'Installation Locked',
                'message' => 'The application has already been initialized and the installer is permanently disabled for security.'
            ], 'layouts/setup');
        }

        $config = Config::get('database.installer') ?? Config::get('database.default') ?? [];
        $dbName = (string)($config['database'] ?? '');
        $canConnect = false;
        $dbExists = false;
        $connectionError = null;

        // Check for missing production configuration
        if (Env::isProduction()) {
            $missing = [];
            foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'] as $envKey) {
                if (empty(Env::get($envKey))) {
                    $missing[] = $envKey;
                }
            }
            if (!empty($missing)) {
                $connectionError = 'Missing required production database configuration in root .env: ' . implode(', ', $missing) . '. Please populate these variables.';
            }
        }

        if ($connectionError === null) {
            try {
                // Tier 1: Try connecting directly to target DB first (works on restricted hosting like cPanel/Plesk)
                $pdo = Database::getInstallerConnection(true);
                $canConnect = true;
                $dbExists = true;
            } catch (Throwable $e) {
                // Check if target database simply does not exist yet (Error 1049)
                $isUnknownDb = ($e instanceof PDOException && (int)$e->getCode() === 1049) 
                    || str_contains($e->getMessage(), 'Unknown database');

                if ($isUnknownDb) {
                    // Try connecting to database server without selecting database
                    try {
                        $pdoServer = Database::getInstallerConnection(false);
                        $canConnect = true;
                        $dbExists = false;
                    } catch (Throwable $serverEx) {
                        $canConnect = false;
                        $connectionError = $this->diagnoseConnectionError($serverEx, $config, $dbName);
                    }
                } else {
                    $canConnect = false;
                    $connectionError = $this->diagnoseConnectionError($e, $config, $dbName);
                }
            }
        }

        $pendingMigrations = [];
        if ($dbExists) {
            try {
                $runtimePdo = Database::getConnection();
                $pendingMigrations = MigrationManager::getPendingMigrations($runtimePdo);
            } catch (Throwable $e) {
                // Database exists but schema check failed, list all available migrations
                $pendingMigrations = MigrationManager::getAllMigrationFiles();
            }
        } else {
            $pendingMigrations = MigrationManager::getAllMigrationFiles();
        }

        $envPath = Env::getLoadedPath();
        $envDisplay = $envPath ? basename(dirname($envPath)) . '/' . basename($envPath) : '.env';

        return $this->render('setup/index', [
            'canConnect'         => $canConnect,
            'dbExists'           => $dbExists,
            'dbName'             => $dbName,
            'connectionError'    => $connectionError,
            'pendingMigrations'  => $pendingMigrations,
            'config'             => $config,
            'envDisplay'         => $envDisplay,
        ], 'layouts/setup');
    }

    public function run(Request $request): Response
    {
        if (MigrationManager::isInstalled()) {
            return $this->json(['success' => false, 'error' => 'Installation is locked.'], 403);
        }

        $seedData = (bool)$request->input('seed_data', true);
        $createDb = (bool)$request->input('create_db', false);
        $config = Config::get('database.installer') ?? Config::get('database.default') ?? [];
        $dbName = (string)($config['database'] ?? '');

        try {
            // Step 1: Create Database only if requested and it doesn't already exist
            if ($createDb && !Database::databaseExists($dbName)) {
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

            $userMessage = ($e instanceof \App\Core\DatabaseInitializationException) 
                ? $e->getUserMessage() 
                : ($e instanceof PDOException 
                    ? \App\Core\DatabaseInitializationException::fromPDOException($e)->getUserMessage() 
                    : $e->getMessage());

            if ($request->isJson()) {
                return $this->json([
                    'success' => false,
                    'error'   => 'Installation failed. Incident code: ' . $incidentId,
                    'details' => $userMessage
                ], 500);
            }

            return $this->render('setup/error', [
                'incidentId' => $incidentId,
                'message'    => $userMessage,
            ], 'layouts/setup');
        }
    }

    /**
     * Provide precise, actionable diagnostic messages without leaking passwords
     */
    private function diagnoseConnectionError(Throwable $e, array $config, string $dbName): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $username = $config['username'] ?? '';
        $msg = $e->getMessage();
        $code = (int)$e->getCode();

        // 1. Host resolution failure (DNS / unknown host)
        if (str_contains($msg, 'getaddrinfo') || str_contains($msg, 'Name or service not known') || str_contains($msg, 'php_network_getaddresses')) {
            return "Incorrect database hostname: Could not resolve host '{$host}'. Please verify DB_HOST in your root .env file.";
        }

        // 2. Insufficient Privileges on Database (1044 / 1142)
        if ($code === 1044 || str_contains($msg, 'to database') || $code === 1142 || str_contains($msg, 'command denied')) {
            return "Insufficient privileges: Database user '{$username}' does not have sufficient permissions on database '{$dbName}'. Grant ALL PRIVILEGES in your hosting control panel.";
        }

        // 3. Authentication failure / Bad credentials (1045)
        if ($code === 1045 || str_contains($msg, 'using password') || str_contains($msg, 'Access denied')) {
            return "Database authentication failed: Access denied for user '{$username}' on host '{$host}'. Please verify DB_USERNAME and DB_PASSWORD in your root .env file.";
        }

        // 4. Unknown Database (1049)
        if ($code === 1049 || str_contains($msg, 'Unknown database')) {
            return "Database server connected, but target database '{$dbName}' was not found. Please create it in your hosting control panel (e.g. cPanel MySQL Databases) or check DB_DATABASE in .env.";
        }

        // 5. Server unavailable / Connection refused / Timeout (2002)
        if ($code === 2002 || str_contains($msg, 'Connection refused') || str_contains($msg, 'timed out') || str_contains($msg, 'actively refused')) {
            return "Database server unavailable: Could not establish a connection to '{$host}:{$port}'. Verify that MySQL/MariaDB service is running and accessible on that port.";
        }

        // 6. Generic safe fallback (never expose raw PDO passwords or trace)
        return "Unable to connect to database server ({$host}:{$port}). Verify database credentials in root .env.";
    }
}
