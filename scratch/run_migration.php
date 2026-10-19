<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\MigrationManager;
use App\Seeders\DatabaseSeeder;

$pdo = Database::getConnection();
$res = MigrationManager::runPending($pdo, true);
echo "Migrations executed: \n";
print_r($res);

$seeded = DatabaseSeeder::run($pdo);
echo "\nSeeded results: \n";
print_r($seeded);

$stmt = $pdo->query("SELECT id, name, username, email, role, status FROM users");
echo "\nCurrent Users in DB: \n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
