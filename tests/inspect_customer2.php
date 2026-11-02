<?php
require_once __DIR__ . '/../app/Core/Autoloader.php';
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/Database.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=tours_travels_db', 'root', '');
$stmt = $pdo->query('SELECT * FROM customers WHERE id = 2');
$cust = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($cust);
