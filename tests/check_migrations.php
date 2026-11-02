<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();
$executed = \App\Core\MigrationManager::getExecutedMigrations($pdo);
echo "Total executed: " . count($executed) . "\n";
print_r($executed);
