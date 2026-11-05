<?php
/**
 * Database Configuration
 * Separates runtime account from privileged installation account where needed.
 */

return [
    'default' => [
        'driver'    => getenv('DB_DRIVER') ?: 'mysql',
        'host'      => getenv('DB_HOST') ?: '127.0.0.1',
        'port'      => (int)(getenv('DB_PORT') ?: 3306),
        'database'  => getenv('DB_DATABASE') ?: 'tours_travels_db',
        'username'  => getenv('DB_USERNAME') ?: 'root',
        'password'  => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
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
        'driver'    => getenv('DB_DRIVER') ?: 'mysql',
        'host'      => getenv('DB_HOST') ?: '127.0.0.1',
        'port'      => (int)(getenv('DB_PORT') ?: 3306),
        'database'  => getenv('DB_DATABASE') ?: 'tours_travels_db',
        'username'  => getenv('DB_USERNAME') ?: 'root',
        'password'  => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
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
