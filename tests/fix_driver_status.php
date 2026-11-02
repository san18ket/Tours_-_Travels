<?php
require __DIR__ . '/../app/bootstrap.php';
$pdo = \App\Core\Database::getConnection();
$res = $pdo->query('SELECT dp.id, dp.duty_status, u.status as user_status FROM driver_profiles dp JOIN users u ON dp.user_id = u.id')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
if (!empty($res)) {
    $pdo->query("UPDATE driver_profiles SET duty_status = 'on_duty' WHERE id = " . $res[0]['id']);
    $pdo->query("UPDATE users SET status = 'active' WHERE id = (SELECT user_id FROM driver_profiles WHERE id = " . $res[0]['id'] . ")");
    echo "Driver status set to on_duty & active\n";
}
