<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = App\Core\Database::getConnection();
$user1 = $pdo->query("SELECT * FROM users WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$user3 = $pdo->query("SELECT * FROM users WHERE id = 3")->fetch(PDO::FETCH_ASSOC);

echo "User 1 (owner): ";
echo "owner123: " . (password_verify('owner123', $user1['password']) ? 'MATCH' : 'NO') . " | ";
echo "admin123: " . (password_verify('admin123', $user1['password']) ? 'MATCH' : 'NO') . " | ";
echo "password: " . (password_verify('password', $user1['password']) ? 'MATCH' : 'NO') . "\n";

if ($user3) {
    echo "User 3 (admin): ";
    echo "admin123: " . (password_verify('admin123', $user3['password']) ? 'MATCH' : 'NO') . " | ";
    echo "owner123: " . (password_verify('owner123', $user3['password']) ? 'MATCH' : 'NO') . " | ";
    echo "password: " . (password_verify('password', $user3['password']) ? 'MATCH' : 'NO') . "\n";
}
