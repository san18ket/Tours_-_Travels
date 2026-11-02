<?php
require __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();
$result = \App\Core\MigrationManager::runPending($pdo, true);
echo "Migration result:\n";
print_r($result);

$tables = $pdo->query("SHOW TABLES LIKE 'notifications'")->fetchAll(PDO::FETCH_COLUMN);
echo "Notifications table exists: " . (!empty($tables) ? 'YES' : 'NO') . "\n";
