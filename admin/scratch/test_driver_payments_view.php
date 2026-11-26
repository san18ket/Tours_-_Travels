<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Controllers\FinanceController;
use App\Core\Request;
use App\Core\Session;

Session::set('user_id', 1);
Session::set('user_role', 'admin');
$_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'username' => 'owner'];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/finance/driver-payments?status=due';

$controller = new FinanceController();
$request = new Request([], ['status' => 'due'], [], [], []);

$response = $controller->driverPayments($request);
$output = $response->getBody();

echo "Response Status Code: " . $response->getStatusCode() . "\n";
echo "Body Length: " . strlen($output) . "\n";
echo "Has Dispatch #1: " . (strpos($output, 'DSP-261109-30EE') !== false ? 'YES' : 'NO') . "\n";
echo "Has Pay Button: " . (strpos($output, 'Pay') !== false ? 'YES' : 'NO') . "\n";
echo "Has Reverse Button: " . (strpos($output, 'Reverse') !== false ? 'YES' : 'NO') . "\n";
echo "Has Partially Paid badge: " . (strpos($output, 'Partially Paid') !== false ? 'YES' : 'NO') . "\n";
echo "Remaining ₹1,000 in output: " . (strpos($output, '1,000') !== false ? 'YES' : 'NO') . "\n";
