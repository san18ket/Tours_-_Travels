<?php
require_once __DIR__ . '/../app/bootstrap.php';

$errors = [];

// 1. Language app.name
$companyName = company_name();
$appName = \App\Core\Language::get('app.name');
if (empty($companyName) || str_contains(strtolower($companyName), 'voyagego')) {
    $errors[] = "company_name() must be dynamic and not contain VoyageGo, got '{$companyName}'";
}
if ($appName !== $companyName) {
    $errors[] = "Language::get('app.name') expected '{$companyName}', got '{$appName}'";
}

// 3. Office layout render
ob_start();
$companyName = company_name();
$companyTagline = company_tagline();
$companyLogoUrl = company_logo_url();
$pageTitle = 'Dashboard';
$content = '<p>Content</p>';
require __DIR__ . '/../app/Views/layouts/office.php';
$officeHtml = ob_get_clean();

if (strpos($officeHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in office layout!";
}

// 4. Auth layout render
ob_start();
$title = 'Sign In';
$content = '<form>Login</form>';
require __DIR__ . '/../app/Views/layouts/auth.php';
$authHtml = ob_get_clean();

if (strpos($authHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in auth layout!";
}

// 5. Driver layout render
ob_start();
$title = 'Driver Portal';
$content = '<div>Driver content</div>';
require __DIR__ . '/../app/Views/layouts/driver.php';
$driverHtml = ob_get_clean();

if (strpos($driverHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in driver layout!";
}

// 6. Setup layout render
ob_start();
$title = 'Setup Wizard';
$content = '<div>Setup</div>';
require __DIR__ . '/../app/Views/layouts/setup.php';
$setupHtml = ob_get_clean();

if (strpos($setupHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in setup layout!";
}

// 7. Check driver profile view
ob_start();
$driver = [
    'name' => 'Rajesh Sharma',
    'first_name' => 'Rajesh',
    'last_name' => 'Sharma',
    'employee_code' => 'DRV-001',
    'phone' => '9876543210',
    'email' => 'rajesh@example.com',
    'joining_date' => '2024-01-01',
    'license_number' => 'MH31-2020-001',
    'license_expiry' => '2030-01-01',
    'rating' => '4.9',
    'total_trips' => 120,
    'status' => 'active'
];
require __DIR__ . '/../app/Views/driver/profile.php';
$driverProfileHtml = ob_get_clean();
if (strpos($driverProfileHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in driver/profile.php!";
}

// 8. Check driver vehicle view
ob_start();
$vehicle = [
    'name' => 'Innova Crysta',
    'make_model' => 'Toyota Innova Crysta',
    'vehicle_number' => 'MH31-AB-1234',
    'registration_number' => 'MH31-AB-1234',
    'vehicle_type' => 'SUV',
    'type' => 'SUV',
    'seating_capacity' => 7,
    'registration_expiry' => '2027-01-01',
    'insurance_expiry' => '2027-01-01',
    'fitness_expiry' => '2027-01-01',
    'puc_expiry' => '2027-01-01',
    'status' => 'active'
];
require __DIR__ . '/../app/Views/driver/vehicle.php';
$driverVehicleHtml = ob_get_clean();
if (strpos($driverVehicleHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in driver/vehicle.php!";
}

// 9. Check office vehicles create view
ob_start();
require __DIR__ . '/../app/Views/office/vehicles/create.php';
$officeVehiclesHtml = ob_get_clean();
if (strpos($officeVehiclesHtml, 'VoyageGo') !== false) {
    $errors[] = "VoyageGo found in office/vehicles/create.php!";
}

if (empty($errors)) {
    echo "SUCCESS: All tested layouts and views cleanly render dynamic company name ('manaswi') with ZERO instances of 'VoyageGo'!\n";
    exit(0);
} else {
    echo "ERRORS FOUND:\n" . implode("\n", $errors) . "\n";
    exit(1);
}
