<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\TripCategory;
use App\Models\Booking;

$pdo = Database::getConnection();

$adminUser = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
if (!$adminUser) {
    $adminUser = $pdo->query("SELECT * FROM users LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    if ($adminUser) {
        $adminUser['role'] = 'admin';
    }
}

if (!$adminUser) {
    echo "Admin user not found. Cannot proceed with controller test.\n";
    exit(1);
}

// Log in admin session using Session helper
Session::start();
Session::setUser([
    'id'    => (int)$adminUser['id'],
    'name'  => $adminUser['name'],
    'email' => $adminUser['email'],
    'role'  => 'admin',
]);
$csrfToken = Csrf::getToken();

$router = new Router();
require __DIR__ . '/../app/routes.php';

$tripCatModel = new TripCategory();
$bookingModel = new Booking();

$failures = [];
$successCount = 0;

function assertCond(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "=== TRIP CATEGORIES CONTROLLER & ROUTE TEST ===\n\n";

// 1. GET /office/trip-categories
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trip-categories']);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$output = ob_get_clean();

assertCond(str_contains($output, 'Trip Categories') || str_contains($output, 'trip-categories'), "GET /office/trip-categories renders categories management page", $failures, $successCount);
assertCond(str_contains($output, 'Holiday / Tour'), "Management page lists 'Holiday / Tour'", $failures, $successCount);
assertCond(str_contains($output, 'Outstation Trip'), "Management page lists 'Outstation Trip'", $failures, $successCount);

// 2. POST /office/trip-categories (Add new category)
$newCatName = 'VIP Delegation ' . time();
$req = new Request([], [
    'name' => $newCatName,
    'description' => 'VIP Dignitaries and Escorts',
    'status' => 'active',
    'sort_order' => 50,
    '_csrf_token' => $csrfToken,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/trip-categories']);
ob_start();
$res = $router->dispatch($req);
ob_get_clean();

$createdCat = $tripCatModel->findByName($newCatName);
assertCond(!empty($createdCat), "POST /office/trip-categories created new category '{$newCatName}'", $failures, $successCount);
assertCond($createdCat && $createdCat['status'] === 'active', "New category has active status", $failures, $successCount);

// 3. POST /office/trip-categories/{id} (Update category)
$createdCatId = (int)($createdCat['id'] ?? 0);
$updatedCatName = $newCatName . ' Updated';
$req = new Request([], [
    'name' => $updatedCatName,
    'description' => 'Updated Description',
    'status' => 'active',
    'sort_order' => 55,
    '_csrf_token' => $csrfToken,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/office/trip-categories/{$createdCatId}"]);
ob_start();
$res = $router->dispatch($req);
ob_get_clean();

$reloadedCat = $tripCatModel->find($createdCatId);
assertCond($reloadedCat && $reloadedCat['name'] === $updatedCatName, "POST /office/trip-categories/{id} updated category name", $failures, $successCount);
assertCond($reloadedCat && (int)$reloadedCat['sort_order'] === 55, "Category sort_order updated to 55", $failures, $successCount);

// 4. POST /office/trip-categories/{id}/toggle-status (Toggle status via AJAX)
$req = new Request([], [
    '_csrf_token' => $csrfToken,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI' => "/office/trip-categories/{$createdCatId}/toggle-status",
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);
ob_start();
$res = $router->dispatch($req);
if ($res) $res->send();
$jsonOutput = ob_get_clean();

$json = json_decode($jsonOutput, true);
assertCond(is_array($json) && ($json['success'] ?? false) === true, "toggle-status AJAX returned success: true", $failures, $successCount);
assertCond(($json['status'] ?? '') === 'inactive', "toggle-status switched status to 'inactive'", $failures, $successCount);

// 5. Test booking edit GET route: /office/bookings/{id}/edit
// Get an existing booking id
$bRow = $pdo->query("SELECT id FROM bookings ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
if ($bRow) {
    $bId = (int)$bRow['id'];
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/bookings/{$bId}/edit"]);
    ob_start();
    $res = $router->dispatch($req);
    if ($res) $res->send();
    $editHtml = ob_get_clean();

    assertCond(str_contains($editHtml, 'Edit Booking') || str_contains($editHtml, 'trip_category_id'), "GET /office/bookings/{id}/edit rendered edit form with trip_category_id", $failures, $successCount);
}

// 6. POST /office/trip-categories/{id}/delete (Clean up the created category)
$req = new Request([], [
    '_csrf_token' => $csrfToken,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI' => "/office/trip-categories/{$createdCatId}/delete",
]);
ob_start();
$res = $router->dispatch($req);
ob_get_clean();

$postDeleteCat = $tripCatModel->find($createdCatId);
assertCond(empty($postDeleteCat), "POST /office/trip-categories/{id}/delete successfully deleted unused category", $failures, $successCount);

echo "\n=== RESULTS ===\n";
echo "Passes: {$successCount}\n";
echo "Failures: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed assertions:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "\nALL CONTROLLER & ROUTE TESTS PASSED SUCCESSFULLY!\n";
exit(0);
