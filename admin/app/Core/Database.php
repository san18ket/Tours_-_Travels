<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class Database
{
    private static ?PDO $runtimeInstance = null;
    private static ?PDO $installerInstance = null;
    private static array $acquiredLocks = [];

    /**
     * Try establishing PDO connection with environment-aware fallback
     */
    public static function tryConnect(array $config, bool $selectDatabase = true): PDO
    {
        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $buildDsn = function(string $host) use ($config, $selectDatabase): string {
            return sprintf(
                '%s:host=%s;port=%d%s;charset=%s',
                $config['driver'] ?? 'mysql',
                $host,
                $config['port'] ?? 3306,
                $selectDatabase && !empty($config['database']) ? ';dbname=' . $config['database'] : '',
                $config['charset'] ?? 'utf8mb4'
            );
        };

        $primaryHost = $config['host'] ?? '127.0.0.1';

        try {
            return new PDO(
                $buildDsn($primaryHost),
                $config['username'] ?? '',
                $config['password'] ?? '',
                $options
            );
        } catch (PDOException $e) {
            // In development only, allow localhost <-> 127.0.0.1 socket/port fallback
            $isDev = !Env::isProduction();
            if ($isDev && ($e->getCode() === 2002 || str_contains($e->getMessage(), '2002'))) {
                $altHost = ($primaryHost === 'localhost') ? '127.0.0.1' : (($primaryHost === '127.0.0.1') ? 'localhost' : null);
                if ($altHost !== null) {
                    try {
                        return new PDO(
                            $buildDsn($altHost),
                            $config['username'] ?? '',
                            $config['password'] ?? '',
                            $options
                        );
                    } catch (PDOException $altEx) {
                        // ignore alt fallback error, throw original below
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * Get runtime PDO instance.
     * Automatically attempts creation if database does not exist (1049).
     * Distinguishes connection/credential failures from missing database.
     */
    public static function getConnection(): PDO
    {
        if (self::$runtimeInstance === null) {
            $config = Config::get('database.default');
            if (!$config) {
                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Database configuration is missing."
                );
            }

            try {
                self::$runtimeInstance = self::tryConnect($config, true);
            } catch (PDOException $e) {
                $code = (int) $e->getCode();
                $msg = $e->getMessage();

                // If database does not exist (Error 1049), automatically attempt creation if allowed
                if ($code === 1049 || str_contains($msg, 'Unknown database') || str_contains($msg, '1049')) {
                    // Attempt creation (will throw DatabaseCreationDeniedException if not permitted)
                    self::createDatabaseIfNotExists();

                    try {
                        self::$runtimeInstance = self::tryConnect($config, true);
                        return self::$runtimeInstance;
                    } catch (PDOException $reconnectEx) {
                        Logger::error('Runtime database reconnect failed after creation: ' . $reconnectEx->getMessage());
                        throw new DatabaseConnectionException(
                            "Unable to connect to the database. Please check the database settings.",
                            "Database was created but connection failed. Please verify user privileges.",
                            $reconnectEx
                        );
                    }
                }

                // If authentication failed (1045) or host unreachable (2002)
                Logger::error('Runtime database connection failed: ' . $msg, [
                    'host' => $config['host'] ?? '',
                    'database' => $config['database'] ?? '',
                    'code' => $code
                ]);

                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Please verify your database host, username, and password in the .env configuration.",
                    $e
                );
            }
        }

        return self::$runtimeInstance;
    }

    /**
     * Get privileged installer PDO instance (connects without specific db if needed)
     */
    public static function getInstallerConnection(bool $selectDatabase = true): PDO
    {
        $config = Config::get('database.installer') ?? Config::get('database.default');
        if (!$config) {
            throw new DatabaseConnectionException(
                "Unable to connect to the database. Please check the database settings.",
                "Installer database configuration is missing."
            );
        }

        try {
            return self::tryConnect($config, $selectDatabase);
        } catch (PDOException $e) {
            $code = (int) $e->getCode();
            $msg = $e->getMessage();

            Logger::error('Installer database connection failed: ' . $msg, [
                'host' => $config['host'] ?? '',
                'selectDatabase' => $selectDatabase,
                'code' => $code
            ]);

            if ($code === 1045 || $code === 2002 || str_contains($msg, 'using password') || str_contains($msg, 'Connection refused')) {
                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Please verify your database host, username, and password in the .env configuration.",
                    $e
                );
            }

            throw $e;
        }
    }

    /**
     * Test if the runtime database is accessible and responsive
     */
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query('SELECT 1');
            return $stmt !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Test if a specific database exists on the server.
     * Works on unprivileged shared hosting accounts by testing direct connection first.
     * Never falsely reports missing database on bad credentials.
     */
    public static function databaseExists(string $dbName): bool
    {
        $runtimeConfig = Config::get('database.default');
        if (!$runtimeConfig) {
            return false;
        }

        // Strategy 1: Direct database connection (works on unprivileged shared hosting like cPanel/Plesk)
        try {
            $pdo = self::tryConnect($runtimeConfig, true);
            $stmt = $pdo->query('SELECT 1');
            if ($stmt !== false) {
                return true;
            }
        } catch (PDOException $e) {
            $code = (int) $e->getCode();
            $msg = $e->getMessage();

            // Error 1049: Unknown database (means host is reachable, credentials valid, DB does not exist yet)
            if ($code === 1049 || str_contains($msg, 'Unknown database') || str_contains($msg, '1049')) {
                return false;
            }

            // Error 1045: Bad credentials. Error 2002: Host down.
            // Do NOT fall through to claim database is missing!
            if ($code === 1045 || $code === 2002 || str_contains($msg, 'using password') || str_contains($msg, 'Connection refused')) {
                Logger::error("Database existence check failed due to invalid credentials or unreachable host: " . $msg);
                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Please check your database credentials in .env.",
                    $e
                );
            }

            // If other error (e.g. 1044 where user has no access to this specific DB name), proceed to Strategy 2
        } catch (\Throwable $e) {
            if ($e instanceof DatabaseInitializationException) {
                throw $e;
            }
        }

        // Strategy 2: Query INFORMATION_SCHEMA if unselected connection is permitted
        $installerConfig = Config::get('database.installer') ?? $runtimeConfig;
        try {
            $pdo = self::tryConnect($installerConfig, false);
            $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
            $stmt->execute([$dbName]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $code = (int) $e->getCode();
            $msg = $e->getMessage();

            if ($code === 1045 || $code === 2002 || str_contains($msg, 'using password') || str_contains($msg, 'Connection refused')) {
                Logger::error("Installer database connection failed during existence check: " . $msg);
                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Please check your database credentials in .env.",
                    $e
                );
            }

            // Access denied on INFORMATION_SCHEMA or connecting without dbname
            return false;
        } catch (\Throwable $e) {
            if ($e instanceof DatabaseInitializationException) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Create the configured database if permitted and not existing.
     * If database already exists: does NOT recreate, drop, or alter existing data.
     * If database creation is denied (shared hosting): throws DatabaseCreationDeniedException.
     */
    public static function createDatabaseIfNotExists(): bool
    {
        $runtimeConfig = Config::get('database.default');
        $installerConfig = Config::get('database.installer') ?? $runtimeConfig;
        $dbName = (string)($runtimeConfig['database'] ?? ($installerConfig['database'] ?? ''));

        if (empty($dbName)) {
            throw new DatabaseConnectionException(
                "Unable to connect to the database. Please check the database settings.",
                "Database name is not configured in .env."
            );
        }

        // If database already exists and is accessible, skip creation completely (Zero data loss)
        if (self::databaseExists($dbName)) {
            return true;
        }

        // Validate database name contains only alphanumeric and underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
            throw new InvalidArgumentException('Invalid database name provided in configuration.');
        }

        try {
            $pdo = self::tryConnect($installerConfig, false);
            $collation = $installerConfig['collation'] ?? ($runtimeConfig['collation'] ?? 'utf8mb4_unicode_ci');
            $charset = $installerConfig['charset'] ?? ($runtimeConfig['charset'] ?? 'utf8mb4');

            $sql = sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                $dbName,
                $charset,
                $collation
            );
            $pdo->exec($sql);
            Logger::info("Database '{$dbName}' created/verified successfully.");
            return true;
        } catch (PDOException $e) {
            $code = (int) $e->getCode();
            $msg = $e->getMessage();

            Logger::error("Failed to create database '{$dbName}': " . $msg, [
                'code' => $code,
                'db'   => $dbName
            ]);

            // Shared hosting permission denied:
            // MySQL 1044: Access denied for user to database
            // MySQL 1142: CREATE command denied to user
            // Access denied connecting without dbname or executing CREATE DATABASE
            if ($code === 1044 || $code === 1142 || str_contains($msg, 'Access denied') || str_contains($msg, 'command denied')) {
                throw new DatabaseCreationDeniedException(
                    "Database setup is incomplete. Please create the database and check the database settings.",
                    "Your hosting provider does not permit automatic database creation. Please create the database manually in your hosting control panel (e.g. cPanel MySQL Databases) and update DB_DATABASE in your .env file.",
                    $e,
                    $msg
                );
            }

            // Authentication failure on installer user
            if ($code === 1045 || $code === 2002 || str_contains($msg, 'using password') || str_contains($msg, 'Connection refused')) {
                throw new DatabaseConnectionException(
                    "Unable to connect to the database. Please check the database settings.",
                    "Could not connect to the database server. Please check the database settings.",
                    $e,
                    $msg
                );
            }

            throw new DatabaseCreationDeniedException(
                "Database setup is incomplete. Please create the database and check the database settings.",
                "Database creation could not be completed. Please create the database manually in your hosting control panel.",
                $e,
                $msg
            );
        }
    }

    /**
     * Acquire a distributed advisory lock using MySQL GET_LOCK
     */
    public static function acquireLock(string $lockName, int $timeoutSeconds = 15): bool
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare('SELECT GET_LOCK(?, ?)');
            $stmt->execute([$lockName, $timeoutSeconds]);
            $result = (int) $stmt->fetchColumn();

            if ($result === 1) {
                self::$acquiredLocks[$lockName] = true;
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Logger::warning("Could not acquire lock '{$lockName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Release an advisory lock
     */
    public static function releaseLock(string $lockName): bool
    {
        try {
            if (!isset(self::$acquiredLocks[$lockName])) {
                return true;
            }

            $pdo = self::getConnection();
            $stmt = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $stmt->execute([$lockName]);
            $result = (int) $stmt->fetchColumn();

            unset(self::$acquiredLocks[$lockName]);
            return $result === 1;
        } catch (\Throwable $e) {
            Logger::warning("Could not release lock '{$lockName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset connection instances (useful during test suites)
     */
    public static function resetConnections(): void
    {
        self::$runtimeInstance = null;
        self::$installerInstance = null;
        self::$acquiredLocks = [];
    }

    public static function resetInstance(): void
    {
        self::resetConnections();
    }
}
