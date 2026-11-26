<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Controllers\FinanceController;
use App\Core\Request;

$_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'username' => 'owner'];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/finance/driver-payments?status=due';

$controller = new FinanceController();
$request = new Request([], ['status' => 'due'], [], [], []);

// Let's inspect dispatch 1 status in dispatches table
$db = App\Core\Database::getConnection();
$disp = $db->query("SELECT * FROM dispatches WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
echo "Dispatch status in DB: " . $disp['status'] . "\n";
echo "driver_payment_status: " . $disp['driver_payment_status'] . "\n";
echo "driver_trip_amount: " . $disp['driver_trip_amount'] . "\n";
echo "driver_paid_amount: " . $disp['driver_paid_amount'] . "\n";

// Let's test the WHERE clause of driverPayments
$where = ["d.status = 'completed'"];
$params = [];
$status = 'due';
if (!empty($status)) {
    if ($status === 'due' || $status === 'pending') {
        $where[] = "(d.driver_trip_amount IS NOT NULL AND (d.driver_trip_amount - d.driver_paid_amount) > 0.009)";
    }
}
$sql = "SELECT d.id, d.dispatch_code, d.status, d.driver_trip_amount, d.driver_paid_amount FROM dispatches d WHERE " . implode(' AND ', $where);
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Matching rows in SQL:\n";
print_r($rows);

