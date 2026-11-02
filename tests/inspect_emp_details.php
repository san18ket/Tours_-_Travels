<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();

echo "--- DRIVER_PROFILES COLUMNS ---\n";
$cols = $pdo->query('DESCRIBE driver_profiles')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n--- USERS COLUMNS ---\n";
$cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n--- EMPLOYEE 2 findWithDetails ---\n";
$empModel = new \App\Models\Employee();
print_r($empModel->findWithDetails(2));
