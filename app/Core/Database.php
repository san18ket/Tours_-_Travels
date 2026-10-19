<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $runtimeInstance = null;
    private static ?PDO $installerInstance = null;
    private static array $acquiredLocks = [];

    /**
     * Get runtime PDO instance
     */
    public static function getConnection(): PDO
    {
        if (self::$runtimeInstance === null) {
            $config = Config::get('database.default');
            if (!$config) {
                throw new RuntimeException('Database configuration missing.');
            }

            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$runtimeInstance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options'] ?? []
                );
            } catch (PDOException $e) {
                // If database does not exist (1049), automatically create it and retry connection
                if ($e->getCode() === 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                    try {
                        self::createDatabaseIfNotExists();
                        self::$runtimeInstance = new PDO(
                            $dsn,
                            $config['username'],
                            $config['password'],
                            $config['options'] ?? []
                        );
                        return self::$runtimeInstance;
                    } catch (\Throwable $createEx) {
                        Logger::error('Auto database creation on connection failed: ' . $createEx->getMessage());
                    }
                }

                Logger::error('Runtime database connection failed: ' . $e->getMessage(), [
                    'host' => $config['host'],
                    'database' => $config['database'],
                    'code' => $e->getCode()
                ]);
                throw new RuntimeException('Database connection failed. Please contact the administrator.');
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
        
        $dsn = sprintf(
            '%s:host=%s;port=%d%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $selectDatabase ? ';dbname=' . $config['database'] : '',
            $config['charset']
        );

        try {
            return new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? []
            );
        } catch (PDOException $e) {
            Logger::error('Installer database connection failed: ' . $e->getMessage(), [
                'host' => $config['host'],
                'selectDatabase' => $selectDatabase
            ]);
            throw new RuntimeException('Installer database connection failed: ' . $e->getMessage());
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
     * Test if a specific database exists on the server
     */
    public static function databaseExists(string $dbName): bool
    {
        try {
            $pdo = self::getInstallerConnection(false);
            $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
            $stmt->execute([$dbName]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Create the configured database if permitted and not existing
     */
    public static function createDatabaseIfNotExists(): bool
    {
        $config = Config::get('database.installer') ?? Config::get('database.default');
        $dbName = $config['database'];

        // Validate database name contains only alphanumeric and underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
            throw new RuntimeException('Invalid database name provided in configuration.');
        }

        try {
            $pdo = self::getInstallerConnection(false);
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
            $charset = $config['charset'] ?? 'utf8mb4';
            
            $sql = sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                $dbName,
                $charset,
                $collation
            );
            $pdo->exec($sql);
            Logger::info("Database '{$dbName}' verified/created successfully.");
            return true;
        } catch (PDOException $e) {
            Logger::error("Failed to create database '{$dbName}': " . $e->getMessage());
            throw new RuntimeException("Could not create database. Privileged account lacks required permissions.");
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
    }
}
