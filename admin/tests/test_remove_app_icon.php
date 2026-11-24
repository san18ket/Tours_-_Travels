<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\CompanyProfile;
use App\Services\CompanySettingsService;

echo "=========================================================\n";
echo "TESTING REMOVE PWA APP ICON VIA ROUTER DISPATCH\n";
echo "=========================================================\n\n";

Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
$csrfToken = Csrf::getToken();

$companyModel = new CompanyProfile();
$company = $companyModel->getPrimary();

if (!$company) {
    echo "ERROR: Company not found!\n";
    exit(1);
}

// 1. Setup a test app icon file on disk and in database
$projectRoot = dirname(__DIR__);
$pwaDir = $projectRoot . '/public/storage/company/pwa';
if (!is_dir($pwaDir)) {
    @mkdir($pwaDir, 0777, true);
}
$testIconFile = $pwaDir . '/app-icon.png';
$img = imagecreatetruecolor(512, 512);
$green = imagecolorallocate($img, 16, 185, 129);
imagefilledrectangle($img, 0, 0, 512, 512, $green);
imagepng($img, $testIconFile);
imagedestroy($img);

$looseFile = $projectRoot . '/public/storage/company/app_icon_test_dummy.png';
file_put_contents($looseFile, 'test loose icon');

assert(file_exists($testIconFile), "Test icon exists on disk");
assert(file_exists($looseFile), "Loose icon exists on disk");

$companyModel->update((int)$company['id'], [
    'app_icon'        => 'company/pwa/app-icon.png',
    'app_icon_source' => 'custom',
]);
CompanySettingsService::clearCache();

echo "Step 1: Set up custom app icon in DB and on disk.\n";

// 2. Dispatch request through ROUTER (simulating real browser request)
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/office/company/remove-app-icon';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['HTTP_X_CSRF_TOKEN'] = $csrfToken;

$req = new Request();
require __DIR__ . '/../app/routes.php';
$response = $router->dispatch($req);

echo "Step 2: Executed request through Router::dispatch().\n";
echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response body: " . $response->getContent() . "\n";

assert($response->getStatusCode() === 200, "Response status is 200 OK (NOT 422 intercepted by {id})");
$json = json_decode($response->getContent(), true);
assert(!empty($json['success']) && $json['success'] === true, "JSON success is true");

// 3. Verify physical files are DELETED from storage folder
echo "\nStep 3: Verifying disk storage cleanup...\n";
$remainingPwaFiles = glob($pwaDir . '/*');
echo "Files remaining in storage/company/pwa: " . count($remainingPwaFiles) . "\n";
assert(count($remainingPwaFiles) === 0, "No leftover files in company/pwa directory");
assert(!file_exists($testIconFile), "app-icon.png was physically deleted");
assert(!file_exists($looseFile), "Loose test file was physically cleaned up");
echo "✓ Physical file storage cleanup verified (0 orphaned files left in folder)\n";

// 4. Verify Database and Service state
echo "\nStep 4: Verifying database & service state...\n";
$refreshed = $companyModel->getPrimary();
assert(empty($refreshed['app_icon']), "DB app_icon is null");
assert($refreshed['app_icon_source'] === 'company_logo', "DB app_icon_source is company_logo");
assert(CompanySettingsService::hasCustomAppIcon() === false, "Custom icon flag is false");

$previewUrl = CompanySettingsService::getPwaIconPreviewUrl();
echo "Reverted PWA preview URL: " . $previewUrl . "\n";
assert(!empty($previewUrl), "Preview URL is valid fallback");

echo "\n=========================================================\n";
echo "SUCCESS! ROUTER DISPATCH & STORAGE CLEANUP PASSED 100%!\n";
echo "=========================================================\n";
