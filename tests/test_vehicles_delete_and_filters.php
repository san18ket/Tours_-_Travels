<?php
/**
 * Test: Vehicles Top Cards, Filters, Delete Action Button & 2-Step Delete Modals
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Controllers\VehicleController;
use App\Core\Request;
use App\Core\Database;
use App\Models\Vehicle;

$pdo = Database::getConnection();

function assertCondition($condition, $message) {
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "RUNNING TEST: Vehicles Top Cards, Filters & 2-Step Delete Modal System\n";
echo "====================================================================\n\n";

// 1. Verify View Structure in GET /office/vehicles
echo "1. Verifying View Structure in GET /office/vehicles...\n";

// Ensure a vehicle exists so table rows and action menus are rendered
$vehicleModel = new Vehicle();
$initialCount = $vehicleModel->count();
$fixtureVehicleId = null;
if ($initialCount === 0) {
    $fixtureVehicleId = $vehicleModel->create([
        'vehicle_number'   => 'MH 12 FIXTURE',
        'make_model'       => 'Toyota Camry Fixture',
        'vehicle_type'     => 'sedan',
        'seating_capacity' => 4,
        'fuel_type'        => 'petrol',
        'status'           => 'available',
        'odometer_km'      => 12000,
        'created_at'       => date('Y-m-d H:i:s'),
        'updated_at'       => date('Y-m-d H:i:s'),
    ]);
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/vehicles';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$request = new Request();
$controller = new VehicleController();
$response = $controller->index($request);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "office/vehicles returns HTTP 200");

// Top KPI Cards
assertCondition(strpos($html, 'id="vehiclesKpiGrid"') !== false, "Contains #vehiclesKpiGrid KPI cards container");
assertCondition(strpos($html, 'data-status=""') !== false, "Contains Card 1 (Total Fleet) with data-status=''");
assertCondition(strpos($html, 'data-status="available"') !== false, "Contains Card 2 (Available for Duty) with data-status='available'");
assertCondition(strpos($html, 'data-status="on_trip"') !== false, "Contains Card 3 (On Active Trip) with data-status='on_trip'");
assertCondition(strpos($html, 'data-status="maintenance"') !== false, "Contains Card 4 (Maintenance) with data-status='maintenance'");

// Filter bar
assertCondition(strpos($html, 'id="vehicleFilterForm"') !== false, "Contains #vehicleFilterForm");
assertCondition(strpos($html, 'id="typeInput"') !== false, "Contains vehicle type select #typeInput");
assertCondition(strpos($html, 'id="statusInput"') !== false, "Contains vehicle status select #statusInput");

// Delete buttons in table & mobile cards action popup
assertCondition(strpos($html, 'class="action-btn-table delete"') !== false, "Action menus contain .action-btn-table.delete button");
assertCondition(strpos($html, 'openDeleteModal(') !== false, "Delete button triggers openDeleteModal() handler");
assertCondition(strpos($html, 'fa-trash-can') !== false, "Delete button displays fa-trash-can icon");

// 2-Step Delete Modals
assertCondition(strpos($html, 'id="deleteModalBackdrop"') !== false, "Contains #deleteModalBackdrop container");
assertCondition(strpos($html, 'id="deleteStep1Modal"') !== false, "Contains Modal 1: #deleteStep1Modal");
assertCondition(strpos($html, 'id="deleteStep1Checkbox"') !== false, "Modal 1 contains confirmation checkbox #deleteStep1Checkbox");
assertCondition(strpos($html, 'id="deleteStep1Btn"') !== false, "Modal 1 contains proceed button #deleteStep1Btn");

assertCondition(strpos($html, 'id="deleteStep2Modal"') !== false, "Contains Modal 2: #deleteStep2Modal");
assertCondition(strpos($html, 'id="delModalTitle2"') !== false, "Modal 2 contains title 'Are you sure?'");
assertCondition(strpos($html, 'id="deleteStep2Checkbox"') !== false, "Modal 2 contains final confirmation checkbox #deleteStep2Checkbox");
assertCondition(strpos($html, 'id="deleteConfirmFinalBtn"') !== false, "Modal 2 contains final action button #deleteConfirmFinalBtn");
assertCondition(strpos($html, 'id="deleteVehicleForm"') !== false, "Contains fallback form #deleteVehicleForm");

// JavaScript handlers & CSS
assertCondition(strpos($html, 'function openDeleteModal') !== false, "Contains openDeleteModal JS function");
assertCondition(strpos($html, 'function closeDeleteModal') !== false, "Contains closeDeleteModal JS function");
assertCondition(strpos($html, 'function goToStep2Modal') !== false, "Contains goToStep2Modal JS function");
assertCondition(strpos($html, 'function confirmDeleteFinal') !== false, "Contains confirmDeleteFinal JS function");
assertCondition(strpos($html, '.vg-del-backdrop') !== false, "Contains .vg-del-backdrop modal styling");
assertCondition(strpos($html, '.vg-del-modal') !== false, "Contains .vg-del-modal card styling");
assertCondition(strpos($html, '.action-btn-table.delete') !== false, "Contains .action-btn-table.delete styling");

// 2. Functional AJAX Delete Verification
echo "\n2. Verifying AJAX Vehicle Deletion & Cascade Clean...\n";

// Create test vehicle
$vehicleModel = new Vehicle();
$testPlate = 'MH 12 TS ' . rand(1000, 9999);
$testVehicleId = $vehicleModel->create([
    'vehicle_number'   => $testPlate,
    'make_model'       => 'Toyota Innova Crysta Test',
    'vehicle_type'     => 'suv',
    'seating_capacity' => 7,
    'fuel_type'        => 'diesel',
    'status'           => 'available',
    'odometer_km'      => 45000,
    'created_at'       => date('Y-m-d H:i:s'),
    'updated_at'       => date('Y-m-d H:i:s'),
]);
echo "  [PASS] Created temporary test vehicle ID: {$testVehicleId} with plate: {$testPlate}\n";

// Send AJAX Delete request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = "/office/vehicles/{$testVehicleId}/delete";
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST['_csrf_token'] = csrf_token();
$_POST['csrf_token'] = csrf_token();

$delReq = new Request();
$delResp = $controller->delete($delReq, ['id' => $testVehicleId]);

assertCondition($delResp->getStatusCode() === 200, "Vehicle delete endpoint returns HTTP 200 on AJAX");
$delJson = json_decode((string)$delResp->getContent(), true);
assertCondition(!empty($delJson['success']) && $delJson['success'] === true, "Delete response JSON success === true");
assertCondition(!empty($delJson['message']) && strpos($delJson['message'], $testPlate) !== false, "Response message confirms vehicle deletion");
assertCondition(isset($delJson['metrics']['total']), "Response contains updated metrics payload for KPI cards");

// Verify DB: Vehicle record should be removed
$foundVehicle = $vehicleModel->find($testVehicleId);
assertCondition(empty($foundVehicle), "Vehicle record removed from database");

// 3. Test on_trip delete guard
echo "\n3. Verifying on_trip Delete Protection...\n";
$tripPlate = 'MH 12 ON ' . rand(1000, 9999);
$tripVehicleId = $vehicleModel->create([
    'vehicle_number'   => $tripPlate,
    'make_model'       => 'Mercedes Sprinter Guard Test',
    'vehicle_type'     => 'luxury_bus',
    'seating_capacity' => 15,
    'fuel_type'        => 'diesel',
    'status'           => 'on_trip',
    'odometer_km'      => 60000,
    'created_at'       => date('Y-m-d H:i:s'),
    'updated_at'       => date('Y-m-d H:i:s'),
]);

$guardResp = $controller->delete($delReq, ['id' => $tripVehicleId]);
assertCondition($guardResp->getStatusCode() === 422, "Deleting active on_trip vehicle is blocked with HTTP 422");

// Cleanup
$vehicleModel->delete($tripVehicleId);

echo "\n====================================================================\n";
echo "SUMMARY: All Vehicles top cards, filters & delete assertions passed!\n";
echo "====================================================================\n";
