<?php
/**
 * Test: Employee Details View (office/employees/{id}) Matching customer/show.php Executive Design
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Controllers\EmployeeController;
use App\Core\Request;
use App\Core\Database;
use App\Models\Employee;
use App\Models\User;
use App\Models\DriverProfile;

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
echo "RUNNING TEST: Employee Show View Modernization (Matching Customer View)\n";
echo "====================================================================\n\n";

// Find or create employee with ID 9 or any active employee
$empModel = new Employee();
$userModel = new User();
$driverModel = new DriverProfile();

$targetId = 9;
$emp = $empModel->find($targetId);

if (!$emp) {
    // If ID 9 does not exist, let's create employee 9 or use the latest existing employee
    $all = $empModel->all('id ASC');
    $existing = $all[0] ?? null;
    if ($existing) {
        $targetId = (int)$existing['id'];
        $emp = $existing;
        echo "  [INFO] Testing on existing employee ID: {$targetId}\n";
    } else {
        // Create one
        $testUserId = $userModel->create([
            'username'   => 'staff_test_' . time(),
            'name'       => 'Rajesh Sharma',
            'email'      => 'rajesh_' . time() . '@manaswi.com',
            'password'   => password_hash('Pass123!', PASSWORD_BCRYPT),
            'role'       => 'driver',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $targetId = $empModel->create([
            'employee_code'   => 'VG-EMP-0009',
            'user_id'         => $testUserId,
            'first_name'      => 'Rajesh',
            'last_name'       => 'Sharma',
            'phone'           => '+91 98765 43210',
            'email'           => 'rajesh_' . time() . '@manaswi.com',
            'department'      => 'fleet_chauffeur',
            'designation'     => 'Senior Commercial Chauffeur',
            'employment_type' => 'full_time',
            'joining_date'    => '2023-01-15',
            'status'          => 'active',
            'address_line'    => '45, MG Road, Shivaji Nagar',
            'city'            => 'Pune',
            'state'           => 'Maharashtra',
            'postal_code'     => '411005',
            'country'         => 'India',
            'license_document_file' => 'employees/documents/test_license.jpg',
            'id_document_file'      => 'employees/documents/test_aadhaar.jpg',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        $driverModel->create([
            'user_id'          => $testUserId,
            'employee_id'      => $targetId,
            'license_number'   => 'MH-12-20230009988',
            'license_expiry'   => '2028-06-30',
            'experience_years' => 7,
            'duty_status'      => 'on_duty',
            'rating'           => 4.9,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        echo "  [INFO] Created test employee ID: {$targetId} (VG-EMP-0009)\n";
    }
} else {
    echo "  [INFO] Found target employee ID 9: {$emp['first_name']} {$emp['last_name']}\n";
}

// 1. Render GET /office/employees/{id}
echo "\n1. Verifying View Structure in GET /office/employees/{$targetId}...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = "/office/employees/{$targetId}";
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$request = new Request();
$controller = new EmployeeController();
$response = $controller->show($request, ['id' => $targetId]);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "office/employees/{$targetId} returns HTTP 200");

// Root & Header Bar
assertCondition(strpos($html, 'class="customer-view-root employee-view-root"') !== false, "Contains .customer-view-root.employee-view-root container");
assertCondition(strpos($html, 'class="view-header-bar"') !== false, "Contains .view-header-bar matching customer view");
assertCondition(strpos($html, 'Staff Details') === false, "Header breadcrumb and 'Staff Details' title removed as requested");
assertCondition(strpos($html, 'back-btn') === false, "Header back button removed as requested");
assertCondition(strpos($html, 'id="btnEmployeeMore"') !== false, "Contains 3-dots more menu button #btnEmployeeMore");
assertCondition(strpos($html, 'id="employeeMoreMenu"') !== false, "Contains dropdown menu #employeeMoreMenu");

// Hero Profile Card
assertCondition(strpos($html, 'class="view-hero-card"') !== false, "Contains .view-hero-card scenic banner");
assertCondition(strpos($html, 'customer-scenic-bus.png') !== false, "Hero card features scenic tour bus backdrop");
assertCondition(strpos($html, 'class="view-avatar-wrap"') !== false, "Contains avatar wrap with monogram/photo");
assertCondition(strpos($html, 'class="view-avatar-camera-badge"') !== false, "Avatar includes verified camera badge");
assertCondition(strpos($html, 'class="cust-status-toggle') !== false, "Hero card contains live .cust-status-toggle switch");
assertCondition(strpos($html, 'id="heroStatusPill"') !== false, "Contains #heroStatusPill with pulse dot");
assertCondition(strpos($html, 'class="view-hero-quick-actions"') !== false, "Contains mobile quick action contact pills (Call, WhatsApp, Email)");

// 4 Top KPI Cards
assertCondition(strpos($html, 'class="view-kpi-grid"') !== false, "Contains .view-kpi-grid container");
assertCondition(strpos($html, 'Assigned Trips') !== false, "KPI 1: Assigned Trips card present");
assertCondition(strpos($html, 'Rating &amp; Availability') !== false || strpos($html, 'Rating & Availability') !== false, "KPI 2: Rating & Availability card present");
assertCondition(strpos($html, 'Experience &amp; Contract') !== false || strpos($html, 'Experience & Contract') !== false, "KPI 3: Experience & Contract card present");
assertCondition(strpos($html, 'Date of Joining') !== false, "KPI 4: Date of Joining card present");

// 5 Navigation Tabs
assertCondition(strpos($html, 'class="view-nav-tabs"') !== false, "Contains horizontal pill navigation tabs .view-nav-tabs");
assertCondition(strpos($html, 'data-tab="overview"') !== false, "Contains Overview tab pill");
assertCondition(strpos($html, 'data-tab="trips"') !== false, "Contains Trips / Dispatches tab pill");
assertCondition(strpos($html, 'data-tab="credentials"') !== false, "Contains Credentials & Access tab pill");
assertCondition(strpos($html, 'data-tab="documents"') !== false, "Contains Documents tab pill");
assertCondition(strpos($html, 'data-tab="notes"') !== false, "Contains Emergency & Notes tab pill");

// Tab Content: Overview
assertCondition(strpos($html, 'id="tabContent-overview"') !== false, "Contains #tabContent-overview pane");
assertCondition(strpos($html, 'class="view-layout-split"') !== false, "Overview contains split layout (main + side)");
assertCondition(strpos($html, 'Personal Information') !== false, "Main column contains Personal Information card");
assertCondition(strpos($html, 'id="infoStatusToggleBtn"') !== false, "Personal Information card contains secondary status toggle #infoStatusToggleBtn");
assertCondition(strpos($html, 'id="infoStatusPill"') !== false, "Personal Information card contains secondary status pill #infoStatusPill");
assertCondition(strpos($html, 'Employment &amp; Position Details') !== false || strpos($html, 'Employment & Position Details') !== false, "Main column contains Employment & Position Details card");
assertCondition(strpos($html, 'Identity &amp; Compliance Documents') !== false || strpos($html, 'Identity & Compliance Documents') !== false, "Main column contains Identity & Compliance Documents card");

// Right Column: Side Cards
assertCondition(strpos($html, 'System Login Account') !== false, "Side column contains System Login Account card");
assertCondition(strpos($html, 'Emergency Contact') !== false, "Side column contains Emergency Contact card");
assertCondition(strpos($html, 'Quick Actions') !== false, "Side column contains Quick Actions grid");

// Other Tab Panes
assertCondition(strpos($html, 'id="tabContent-trips"') !== false, "Contains #tabContent-trips pane");
assertCondition(strpos($html, 'id="tabContent-credentials"') !== false, "Contains #tabContent-credentials pane");
assertCondition(strpos($html, 'id="tabContent-documents"') !== false, "Contains #tabContent-documents pane");
assertCondition(strpos($html, 'id="tabContent-notes"') !== false, "Contains #tabContent-notes pane");

// Modals
assertCondition(strpos($html, 'id="docPreviewModal"') !== false, "Contains document lightbox modal #docPreviewModal");
assertCondition(strpos($html, 'id="deleteModalBackdrop"') !== false, "Contains 2-step delete backdrop #deleteModalBackdrop");
assertCondition(strpos($html, 'id="deleteStep1Modal"') !== false, "Contains Delete Step 1 Modal #deleteStep1Modal");
assertCondition(strpos($html, 'id="deleteStep2Modal"') !== false, "Contains Delete Step 2 Modal #deleteStep2Modal");

// Client-Side Scripts
assertCondition(strpos($html, 'function switchTab(') !== false, "Contains switchTab JS function");
assertCondition(strpos($html, 'function toggleEmployeeViewStatus(') !== false, "Contains toggleEmployeeViewStatus live AJAX switch function");
assertCondition(strpos($html, 'function openDocPreviewModal(') !== false, "Contains openDocPreviewModal JS function");
assertCondition(strpos($html, 'function openDeleteModal(') !== false, "Contains openDeleteModal JS function");
assertCondition(strpos($html, 'function confirmDeleteFinal(') !== false, "Contains confirmDeleteFinal JS function");

echo "\n====================================================================\n";
echo "SUMMARY: All assertions passed successfully!\n";
echo "====================================================================\n";

