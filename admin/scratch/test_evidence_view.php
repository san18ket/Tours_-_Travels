<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Controllers\DispatchController;
use App\Core\Request;

// 1. Simulating standard browser GET request
$server = [
    'REQUEST_METHOD' => 'GET',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
];
$req = new Request([], [], [], [], $server);

echo "isAjax: " . ($req->isAjax() ? 'true' : 'false') . "\n";
echo "wantsJson: " . ($req->wantsJson() ? 'true' : 'false') . "\n";

$c = new DispatchController();
try {
    $res = $c->tripEvidence($req, ['id' => 1]);
    echo "Response status: " . $res->getStatusCode() . "\n";
    echo "Content preview (first 200 chars):\n";
    echo substr($res->getContent(), 0, 200) . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

