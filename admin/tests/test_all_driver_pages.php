<?php
require __DIR__ . '/../app/bootstrap.php';

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

$c = new \App\Controllers\DriverPortalController();
$req = new \App\Core\Request();

$pages = [
    'dashboard'      => 'dashboard',
    'assignedTrips'  => 'assignedTrips',
    'history'        => 'history',
    'earnings'       => 'earnings',
    'paymentHistory' => 'paymentHistory',
    'vehicle'        => 'vehicle',
    'location'       => 'location',
    'profile'        => 'profile',
];

foreach ($pages as $name => $action) {
    try {
        $res = $c->$action($req);
        echo "PAGE [{$name}]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
    } catch (\Throwable $e) {
        echo "PAGE [{$name}]: EXCEPTION - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// Test tripDetails
try {
    $res = $c->tripDetails($req, ['id' => 1]);
    echo "PAGE [tripDetails]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "PAGE [tripDetails]: EXCEPTION - " . $e->getMessage() . "\n";
}
