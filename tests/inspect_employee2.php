<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();

echo "--- EMPLOYEES TABLE COLUMNS ---\n";
$cols = $pdo->query('DESCRIBE employees')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n--- EMPLOYEE ID 2 DATA ---\n";
$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->execute([2]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($emp);
