<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Env;
use App\Core\Config;
use App\Core\Database;
use App\Services\TripEvidenceService;
use App\Services\DispatchService;
use App\Models\Dispatch;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\Booking;

echo "=== TOURS & TRAVELS: LIVE SERVER DB & EVIDENCE VERIFICATION ===\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$label}\n";
        $passed++;
    } else {
        echo " [FAIL] {$label}\n";
        $failed++;
    }
}

// -------------------------------------------------------------
// 1. Verify Environment Loading & Discovery
// -------------------------------------------------------------
echo "1. Testing Environment Loading & Discovery...\n";
$loadedPath = Env::getLoadedPath();
assertTest(!empty($loadedPath) && basename($loadedPath) === '.env', "Root .env loaded correctly from: " . ($loadedPath ?: 'none'));
assertTest(!str_ends_with((string)$loadedPath, '.env.example'), "Strictly does NOT load .env.example");

// -------------------------------------------------------------
// 2. Verify Localhost Database Connectivity
// -------------------------------------------------------------
echo "\n2. Testing Localhost Database Connectivity...\n";
Database::resetInstance();
$canConnect = Database::testConnection();
assertTest($canConnect, "Localhost runtime database connection successful");

$config = Config::get('database.default');
$dbExists = Database::databaseExists($config['database']);
assertTest($dbExists, "Target database '{$config['database']}' exists and is accessible directly");

// -------------------------------------------------------------
// 3. Verify Production Configuration Strictness
// -------------------------------------------------------------
echo "\n3. Testing Production Configuration Strictness...\n";
// Temporarily simulate production environment with missing password
$origEnv = $_ENV['APP_ENV'] ?? 'development';
$_ENV['APP_ENV'] = 'production';
putenv('APP_ENV=production');

// Test 3a: Throws exception when required production variable is missing
try {
    $_SERVER['REQUEST_URI'] = '/office/dashboard';
    $prodConfig = require dirname(__DIR__) . '/config/database.php';
    assertTest(false, "Should have thrown exception on missing DB_PASSWORD in production");
} catch (\Throwable $e) {
    assertTest(str_contains($e->getMessage(), 'Missing required database environment variable'), "Production mode strictly rejects missing credentials: " . $e->getMessage());
}

// Test 3b: When valid production variables are provided, loads them accurately
$_ENV['DB_HOST'] = 'sql.myhosting.com';
$_ENV['DB_DATABASE'] = 'cust_tours_db';
$_ENV['DB_USERNAME'] = 'cust_dbuser';
$_ENV['DB_PASSWORD'] = 'SecurePass!123';
putenv('DB_HOST=sql.myhosting.com');
putenv('DB_DATABASE=cust_tours_db');
putenv('DB_USERNAME=cust_dbuser');
putenv('DB_PASSWORD=SecurePass!123');

$prodConfigValid = require dirname(__DIR__) . '/config/database.php';
assertTest($prodConfigValid['default']['host'] === 'sql.myhosting.com', "Production DB_HOST loaded: {$prodConfigValid['default']['host']}");
assertTest($prodConfigValid['default']['database'] === 'cust_tours_db', "Production DB_DATABASE loaded: {$prodConfigValid['default']['database']}");
assertTest($prodConfigValid['default']['username'] === 'cust_dbuser', "Production DB_USERNAME loaded: {$prodConfigValid['default']['username']}");
assertTest($prodConfigValid['default']['password'] === 'SecurePass!123', "Production DB_PASSWORD loaded correctly");

// Restore environment
$_ENV['APP_ENV'] = $origEnv;
putenv("APP_ENV={$origEnv}");
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_DATABASE'] = 'tours_travels_db';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = '';
putenv('DB_HOST=localhost');
putenv('DB_DATABASE=tours_travels_db');
putenv('DB_USERNAME=root');
putenv('DB_PASSWORD=');

// -------------------------------------------------------------
// 4. Verify Setup Controller Diagnostics
// -------------------------------------------------------------
echo "\n4. Testing Setup Controller Error Diagnostics...\n";
$setupController = new \App\Controllers\SetupController();
$reflector = new ReflectionClass($setupController);
$method = $reflector->getMethod('diagnoseConnectionError');
$method->setAccessible(true);

// Test 4a: Bad Credentials (1045)
$ex1045 = new PDOException("SQLSTATE[HY000] [1045] Access denied for user 'hosting_user'@'localhost' (using password: YES)", 1045);
$diag1045 = $method->invoke($setupController, $ex1045, ['host' => 'localhost', 'port' => 3306, 'username' => 'hosting_user'], 'test_db');
assertTest(str_contains($diag1045, 'authentication failed') && str_contains($diag1045, 'DB_PASSWORD'), "1045 diagnosed as authentication failure with .env guidance: " . $diag1045);

// Test 4b: Unknown Database (1049)
$ex1049 = new PDOException("SQLSTATE[HY000] [1049] Unknown database 'nonexistent_db'", 1049);
$diag1049 = $method->invoke($setupController, $ex1049, ['host' => 'localhost', 'port' => 3306, 'username' => 'hosting_user'], 'nonexistent_db');
assertTest(str_contains($diag1049, 'was not found') && str_contains($diag1049, 'cPanel'), "1049 diagnosed as missing database with control panel guidance: " . $diag1049);

// Test 4c: Host Unreachable (2002)
$ex2002 = new PDOException("SQLSTATE[HY000] [2002] Connection refused", 2002);
$diag2002 = $method->invoke($setupController, $ex2002, ['host' => 'sql.hosting.com', 'port' => 3306, 'username' => 'user'], 'test_db');
assertTest(str_contains($diag2002, 'unavailable') && str_contains($diag2002, 'running'), "2002 diagnosed as server unavailable: " . $diag2002);

// Test 4d: Host Resolution Error (getaddrinfo)
$exDns = new PDOException("php_network_getaddresses: getaddrinfo for invalid.hostname.local failed", 2002);
$diagDns = $method->invoke($setupController, $exDns, ['host' => 'invalid.hostname.local', 'port' => 3306, 'username' => 'user'], 'test_db');
assertTest(str_contains($diagDns, 'Could not resolve host') && str_contains($diagDns, 'DB_HOST'), "DNS resolution error diagnosed accurately: " . $diagDns);

// Test 4e: Insufficient Privileges (1044)
$ex1044 = new PDOException("SQLSTATE[42000] [1044] Access denied for user 'user'@'localhost' to database 'test_db'", 1044);
$diag1044 = $method->invoke($setupController, $ex1044, ['host' => 'localhost', 'port' => 3306, 'username' => 'user'], 'test_db');
assertTest(str_contains($diag1044, 'Insufficient privileges') && str_contains($diag1044, 'ALL PRIVILEGES'), "1044 diagnosed as insufficient privileges: " . $diag1044);

// -------------------------------------------------------------
// 5. Verify Pre-Trip Evidence & Trip Commencement
// -------------------------------------------------------------
echo "\n5. Testing Pre-Trip Evidence & Trip Commencement...\n";

// Ensure directories exist
$pubDir = dirname(__DIR__) . '/public/storage/trip_evidence';
$storDir = dirname(__DIR__) . '/storage/trip_evidence';
assertTest(is_dir($pubDir), "public/storage/trip_evidence directory is verified");
assertTest(is_dir($storDir), "storage/trip_evidence directory is verified");

// Test evidence storage with base64 PNG
$evidenceService = new TripEvidenceService();
$dispatchModel = new Dispatch();
$vehicleModel = new Vehicle();
$driverModel = new DriverProfile();
$bookingModel = new Booking();

// Get an existing dispatch or use ID 1
$dispatch = $dispatchModel->find(1);
if ($dispatch) {
    $driverId = (int)$dispatch['driver_id'];
    $dispatchId = (int)$dispatch['id'];

    // 1x1 transparent PNG data url
    $sampleBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $stored = $evidenceService->storeEvidence(
        $dispatchId,
        $driverId,
        'start_vehicle',
        $sampleBase64,
        15000,
        20.9320,
        77.7523,
        'Pre-trip vehicle check passed'
    );

    assertTest(!empty($stored['id']), "Evidence stored successfully (ID: {$stored['id']})");
    assertTest(str_contains($stored['url'], '/storage/trip_evidence/'), "Evidence URL formatted for web: {$stored['url']}");

    $savedRelative = ltrim(str_replace('\\', '/', $stored['file_path']), '/');
    $savedPublic = dirname(__DIR__) . '/public/storage/' . $savedRelative;
    $savedStorage = dirname(__DIR__) . '/storage/' . $savedRelative;

    assertTest(file_exists($savedPublic) || file_exists($savedStorage), "Evidence physically written to storage disk");
}

echo "\n============================================\n";
echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
