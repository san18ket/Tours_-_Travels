<?php
require __DIR__ . '/../app/bootstrap.php';

$pdo = \App\Core\Database::getConnection();
\App\Core\MigrationManager::runPending($pdo, true);

$stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` LIKE 'app_name%'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Company profile app_name columns:\n";
print_r($cols);
