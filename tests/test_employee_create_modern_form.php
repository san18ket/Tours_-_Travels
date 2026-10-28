<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\EmployeeController;
use App\Core\Request;
use App\Core\Session;
use App\Models\Employee;
use App\Models\DriverProfile;
use App\Models\User;

echo "======================================================\n";
echo " RUNNING TEST: Employee Create Form Modernization\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

// 1. Setup Admin session
Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

// 2. Test GET /office/employees/create rendering
echo "\n1. Verifying View Structure in GET /office/employees/create...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/employees/create';
$_GET = [];
$_POST = [];

$controller = new EmployeeController();
$req = new Request();
$response = $controller->create($req);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "employees/create returns HTTP 200");
assertCondition(strpos($html, 'Staff Roster Code') === false, "Staff Roster Code input is REMOVED from form");
assertCondition(strpos($html, 'user_form.role') === false, "Untranslated user_form.role key is REMOVED");
assertCondition(preg_match('/<label[^>]*for="role"[^>]*>\s*Role\s*<span/i', $html) === 1, "Role label is cleanly displayed as 'Role'");
assertCondition(strpos($html, 'value="driver" selected') !== false || strpos($html, 'value="driver"  selected') !== false, "Role dropdown has driver selected by default");
assertCondition(strpos($html, 'name="department"') === false, "Department dropdown is REMOVED from form");
assertCondition(strpos($html, 'name="designation"') === false, "Designation input is REMOVED from form");
assertCondition(strpos($html, 'name="status" value="active"') !== false, "Status is set to default 'active'");
assertCondition(strpos($html, 'name="address_line"') !== false, "Address part includes address_line (matching customers/create)");
assertCondition(strpos($html, 'name="city"') !== false, "Address part includes city (matching customers/create)");
assertCondition(strpos($html, 'name="state"') !== false, "Address part includes state (matching customers/create)");
assertCondition(strpos($html, 'name="country"') !== false, "Address part includes country (matching customers/create)");
assertCondition(strpos($html, 'name="postal_code"') !== false, "Address part includes postal_code (matching customers/create)");
assertCondition(strpos($html, 'name="license_number"') !== false, "Step 3 contains driving licence input (license_number)");
assertCondition(strpos($html, 'name="license_expiry"') !== false, "Step 3 contains License Expiry Date (license_expiry)");
assertCondition(strpos($html, 'name="experience_years"') !== false, "Step 3 contains experience_years");
assertCondition(strpos($html, 'name="license_document_file"') !== false, "Step 3 contains license upload document dropzone/input");
assertCondition(strpos($html, 'name="id_type"') !== false, "Step 3 contains document upload dropdown (id_type)");
assertCondition(strpos($html, 'name="id_document_file"') !== false, "Step 3 contains ID document upload dropzone/input");
assertCondition(strpos($html, 'id="licenseDropzone"') !== false, "Step 3 contains Driving Licence upload dropzone");
assertCondition(strpos($html, 'id="wizardPanel4"') !== false && strpos($html, 'Credentials') !== false, "Step 4 is dedicated Credentials step");
assertCondition(strpos($html, 'name="username"') !== false && strpos($html, 'name="password"') !== false, "Step 4 contains username and password inputs");
assertCondition(strpos($html, 'generateCredentials()') !== false, "Step 4 contains Auto-Generate credentials feature");
assertCondition(strpos($html, 'id="wizardPanel5"') !== false && strpos($html, 'Review &amp; Submit') !== false, "Step 5 is dedicated Review & Submit step");
assertCondition(strpos($html, 'id="sumCardLicenseDoc"') !== false, "Step 5 includes rich Driving Licence document preview card");
assertCondition(strpos($html, 'id="sumCardIdDoc"') !== false, "Step 5 includes rich Government ID document preview card");
assertCondition(strpos($html, 'id="docPreviewModal"') !== false, "Document lightbox preview modal is present");

// 3. Test POST /office/employees submission
echo "\n2. Verifying POST /office/employees submission & automatic status/department mapping...\n";
$uniqueSuffix = rand(1000, 9999);
$testFirstName = "TestDriver" . $uniqueSuffix;
$testLastName = "Chauffeur";
$testPhone = "+91 98" . rand(10000000, 99999999);
$testEmail = "driver" . $uniqueSuffix . "@testcorp.internal";
$testLicense = "DL-" . strtoupper(bin2hex(random_bytes(4)));

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/office/employees';
$_POST = [
    'first_name'             => $testFirstName,
    'last_name'              => $testLastName,
    'phone'                  => $testPhone,
    'email'                  => $testEmail,
    'role'                   => 'driver',
    'employment_type'        => 'full_time',
    'joining_date'           => date('Y-m-d'),
    'status'                 => 'active',
    'address_line'           => '104 Luxury Boulevard, Marine Drive',
    'city'                   => 'Mumbai',
    'state'                  => 'Maharashtra',
    'country'                => 'India',
    'postal_code'            => '400020',
    'license_number'         => $testLicense,
    'license_expiry'         => date('Y-m-d', strtotime('+3 years')),
    'experience_years'       => 7,
    'id_type'                => 'aadhaar',
    'id_number'              => '9988 7766 5544',
    'notes'                  => 'Auto-created via verification test',
    'create_account'         => '1',
    'username'               => 'tdriver.' . $uniqueSuffix,
    'password'               => 'TestPass@123',
    'password_confirmation'  => 'TestPass@123',
];

$storeReq = new Request();
$postResponse = $controller->store($storeReq);

assertCondition($postResponse->getStatusCode() === 302, "Store redirects with HTTP 302 on success");

// Verify in DB
$employeeModel = new Employee();
$createdEmp = $employeeModel->findBy('phone', $testPhone);

assertCondition(!empty($createdEmp), "Employee record was inserted into database");
if ($createdEmp) {
    assertCondition($createdEmp['first_name'] === $testFirstName, "First name is stored accurately");
    assertCondition($createdEmp['status'] === 'active', "Status is saved as 'active' by default");
    assertCondition($createdEmp['department'] === 'fleet_chauffeur', "Department automatically resolved to 'fleet_chauffeur' for driver role");
    assertCondition($createdEmp['designation'] === 'Chauffeur / Driver', "Designation automatically resolved to 'Chauffeur / Driver'");
    assertCondition(strpos($createdEmp['address'], 'Marine Drive') !== false, "Address string concatenated and stored correctly");
    assertCondition(strpos($createdEmp['address'], 'Mumbai') !== false, "City stored in address correctly");
    assertCondition(strpos($createdEmp['address'], 'Maharashtra') !== false, "State stored in address correctly");

    // Check driver_profiles
    $driverModel = new DriverProfile();
    $driverProf = $driverModel->findBy('employee_id', (int)$createdEmp['id']);
    assertCondition(!empty($driverProf), "Linked driver_profiles record automatically created");
    if ($driverProf) {
        assertCondition($driverProf['license_number'] === $testLicense, "License number saved to driver profile");
        assertCondition((int)$driverProf['experience_years'] === 7, "Experience years saved to driver profile");
    }

    // Clean up test records
    $pdo = App\Core\Database::getConnection();
    if ($driverProf) {
        $pdo->exec("DELETE FROM `driver_profiles` WHERE id = " . (int)$driverProf['id']);
    }
    if (!empty($createdEmp['user_id'])) {
        $pdo->exec("DELETE FROM `users` WHERE id = " . (int)$createdEmp['user_id']);
    }
    $pdo->exec("DELETE FROM `employees` WHERE id = " . (int)$createdEmp['id']);
    echo "  [INFO] Cleaned up test database records.\n";
}

echo "\n======================================================\n";
echo "SUMMARY: {$passed} / " . ($passed + $failed) . " assertions passed.\n";
echo "======================================================\n";

