<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Controllers\FinanceController;

Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

echo "=== 3. Testing Router Matching ===\n";
$router = require __DIR__ . '/../app/routes.php';

$testRoutes = [
    ['GET', '/office/finance/advances'],
    ['GET', '/office/finance/advances/1'],
    ['POST', '/office/finance/advances/1'],
    ['POST', '/office/finance/advances/1/toggle-status'],
    ['POST', '/office/finance/advances/1/delete'],
];

foreach ($testRoutes as [$method, $path]) {
    $match = $router->match($method, $path);
    if ($match) {
        $handler = $match['handler'];
        $handlerName = is_array($handler) ? $handler[1] : (string)$handler;
        echo "SUCCESS: {$method} {$path} matches handler -> {$handlerName}\n";
    } else {
        echo "FAIL: {$method} {$path} did not match any route!\n";
    }
}

echo "\n=== 4. Testing AJAX Status Toggle ===\n";
$_POST['status'] = 'approved';
$_SERVER['REQUEST_METHOD'] = 'POST';
$req = new Request();
$fc = new FinanceController();

$toggleRes = $fc->toggleAdvanceStatus($req, ['id' => 1]);
echo "Toggle response: " . $toggleRes->getBody() . "\n";

$_POST['status'] = 'pending';
$toggleRes2 = $fc->toggleAdvanceStatus($req, ['id' => 1]);
echo "Revert to pending: " . $toggleRes2->getBody() . "\n";
