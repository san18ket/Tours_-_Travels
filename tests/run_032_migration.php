<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();

echo "Running pending migrations...\n";
$res = \App\Core\MigrationManager::runPending($pdo, true);
print_r($res);
