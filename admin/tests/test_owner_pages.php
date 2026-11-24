<?php
require __DIR__ . '/../app/bootstrap.php';

\App\Core\Session::start();
\App\Core\Session::set('user_id', 1);
\App\Core\Session::set('user_role', 'owner');
\App\Core\Session::set('user', [
    'id' => 1,
    'name' => 'Admin Owner',
    'username' => 'admin',
    'email' => 'admin@voyage.test',
    'role' => 'owner',
    'language' => 'en',
]);

$req = new \App\Core\Request();

// 1. Office Dashboard
try {
    $c = new \App\Controllers\DashboardController();
    $res = $c->index($req);
    echo "OWNER [Dashboard]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "OWNER [Dashboard]: ERROR - " . $e->getMessage() . "\n";
}

// 2. Employees Index
try {
    $c = new \App\Controllers\EmployeeController();
    $res = $c->index($req);
    echo "OWNER [Employees Index]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "OWNER [Employees Index]: ERROR - " . $e->getMessage() . "\n";
}

// 3. Employee Edit
try {
    $c = new \App\Controllers\EmployeeController();
    $res = $c->edit($req, ['id' => 1]);
    echo "OWNER [Employee Edit 1]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "OWNER [Employee Edit 1]: ERROR - " . $e->getMessage() . "\n";
}

// 4. Bookings Index
try {
    $c = new \App\Controllers\BookingController();
    $res = $c->index($req);
    echo "OWNER [Bookings Index]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "OWNER [Bookings Index]: ERROR - " . $e->getMessage() . "\n";
}

// 5. Customer Directory Index
try {
    $c = new \App\Controllers\CustomerController();
    $res = $c->index($req);
    echo "OWNER [Customers Index]: HTTP {$res->getStatusCode()} (Length: " . strlen($res->getBody()) . " bytes)\n";
} catch (\Throwable $e) {
    echo "OWNER [Customers Index]: ERROR - " . $e->getMessage() . "\n";
}

