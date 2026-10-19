<?php
/**
 * Database Configuration
 * Separates runtime account from privileged installation account where needed.
 */

return [
    'default' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'port'      => 3306,
        'database'  => 'tours_travels_db',
        'username'  => 'root',
        'password'  => '',
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
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'port'      => 3306,
        'database'  => 'tours_travels_db',
        'username'  => 'root',
        'password'  => '',
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
