<?php
require_once __DIR__ . '/../app/bootstrap.php';

\App\Core\Session::start();
\App\Core\Session::set('user_id', 2);
\App\Core\Session::set('user_role', 'driver');
\App\Core\Session::set('user', [
    'id' => 2,
    'name' => 'rohit sharma',
    'username' => 'rohit',
    'email' => 'sanketsidurkar@gmail.com',
    'role' => 'driver',
    'language' => 'en',
    'profile_photo' => 'employees/avatars/87664c3d9e47dc0b280a3f9f9a5e9158.jpg',
]);

$scenarios = [
    'All trips (no filter)' => [],
    'Filter status=assigned' => ['status' => 'assigned'],
    'Filter status=driver_accepted' => ['status' => 'driver_accepted'],
    'Filter status=trip_started' => ['status' => 'trip_started'],
    'Search by name/code' => ['search' => 'BK'],
    'Search nonexistent' => ['search' => 'NONEXISTENT_XYZ_123'],
    'Filter by date' => ['date' => '2026-11-06'],
    'Pagination page=1 per_page=5' => ['per_page' => '5', 'page' => '1'],
];

$c = new \App\Controllers\DriverPortalController();

foreach ($scenarios as $desc => $params) {
    $_GET = $params;
    $req = new \App\Core\Request();
    try {
        $res = $c->assignedTrips($req);
        $body = $res->getBody();
        $hasEmptyState = strpos($body, 'No trips assigned yet') !== false;
        $hasTable = strpos($body, 'modern-table') !== false;
        $code = $res->getStatusCode();
        echo "SCENARIO [{$desc}]: HTTP {$code} | Modern Table Present: " . ($hasTable ? 'YES' : 'NO') . " | Empty Notice: " . ($hasEmptyState ? 'YES' : 'NO') . PHP_EOL;
    } catch (\Throwable $e) {
        echo "SCENARIO [{$desc}]: EXCEPTION " . $e->getMessage() . PHP_EOL;
    }
}

