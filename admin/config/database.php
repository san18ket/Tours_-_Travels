<?php
/**
 * Professional Database Configuration
 * Environment-driven with strict production validation and local development support.
 */

$appEnv = strtolower((string)env('APP_ENV', 'development'));
$isProduction = in_array($appEnv, ['production', 'prod', 'live'], true);

$dbDriver   = (string)env('DB_DRIVER', 'mysql');
$dbHost     = env('DB_HOST');
$dbPort     = env('DB_PORT');
$dbName     = env('DB_DATABASE');
$dbUser     = env('DB_USERNAME');
$dbPassword = env('DB_PASSWORD');

if ($isProduction) {
    // In production, strictly require explicit configuration and refuse localhost defaults
    $missing = [];
    if ($dbHost === null || trim((string)$dbHost) === '') {
        $missing[] = 'DB_HOST';
    }
    if ($dbName === null || trim((string)$dbName) === '') {
        $missing[] = 'DB_DATABASE';
    }
    if ($dbUser === null || trim((string)$dbUser) === '') {
        $missing[] = 'DB_USERNAME';
    }
    if ($dbPassword === null || (string)$dbPassword === '') {
        $missing[] = 'DB_PASSWORD';
    }

    if (!empty($missing)) {
        // Enforce strictly on runtime requests; on /setup allow SetupController to display the diagnostic
        $isSetupRoute = (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/setup')) 
            || (isset($_SERVER['argv']) && in_array('--setup-test', $_SERVER['argv'], true));
        if (!$isSetupRoute) {
            throw new \App\Core\DatabaseConnectionException(
                'Unable to connect to the database. Please check the database settings.',
                'Missing required database environment variable(s): ' . implode(', ', $missing) . '. Please configure .env on the production server.'
            );
        }
    }

    $dbHost     = (string)$dbHost;
    $dbPort     = !empty($dbPort) ? (int)$dbPort : 3306;
    $dbName     = (string)$dbName;
    $dbUser     = (string)$dbUser;
    $dbPassword = (string)$dbPassword;
} else {
    // Local / development fallbacks when .env values are omitted
    $dbHost     = !empty($dbHost) ? (string)$dbHost : '127.0.0.1';
    $dbPort     = !empty($dbPort) ? (int)$dbPort : 3306;
    $dbName     = !empty($dbName) ? (string)$dbName : 'tours_travels_db';
    $dbUser     = !empty($dbUser) ? (string)$dbUser : 'root';
    $dbPassword = ($dbPassword !== null) ? (string)$dbPassword : '';
}

return [
    'default' => [
        'driver'    => $dbDriver,
        'host'      => $dbHost,
        'port'      => $dbPort,
        'database'  => $dbName,
        'username'  => $dbUser,
        'password'  => $dbPassword,
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ],

    // Account with privileges to create databases, tables and indexes
    'installer' => [
        'driver'    => $dbDriver,
        'host'      => env('DB_INSTALLER_HOST', $dbHost),
        'port'      => (int)env('DB_INSTALLER_PORT', $dbPort),
        'database'  => env('DB_INSTALLER_DATABASE', $dbName),
        'username'  => env('DB_INSTALLER_USERNAME', $dbUser),
        'password'  => env('DB_INSTALLER_PASSWORD', $dbPassword),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],

    // Migration locking timeout in seconds
    'lock_timeout' => 15,
];
