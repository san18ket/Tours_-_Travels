<?php
/**
 * Comprehensive .env and Database Configuration Test Suite
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Env;
use App\Core\Config;
use App\Core\Database;
use App\Services\EmailSettingsService;
use App\Repositories\UserRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\BookingRepository;

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$description}\n";
        $passed++;
    } else {
        echo " [FAIL] {$description}" . ($details ? " - {$details}" : '') . "\n";
        $failed++;
    }
}

echo "===============================================================================\n";
echo " TOURS & TRAVELS — .ENV & DATABASE CONFIGURATION VERIFICATION\n";
echo "===============================================================================\n\n";

// 1. .env Loading Test
echo "--- 1. Environment Loading (.env) ---\n";
assertTest('.env is marked as loaded', Env::isLoaded());
assertTest('APP_NAME is loaded from .env', Env::get('APP_NAME') === 'Tours & Travels');
assertTest('APP_ENV is loaded as development', Env::get('APP_ENV') === 'development');
assertTest('DB_HOST is loaded from .env', Env::get('DB_HOST') === 'localhost');
assertTest('DB_PORT is loaded from .env', (int)Env::get('DB_PORT') === 3306);
assertTest('DB_DATABASE is loaded from .env', Env::get('DB_DATABASE') === 'tours_travels_db');
assertTest('DB_USERNAME is loaded from .env', Env::get('DB_USERNAME') === 'root');
assertTest('DB_PASSWORD is loaded from .env (empty for local XAMPP)', Env::get('DB_PASSWORD') === '');
assertTest('MAIL_HOST is loaded from .env', Env::get('MAIL_HOST') === 'smtp.gmail.com');
assertTest('MAIL_PORT is loaded from .env', (int)Env::get('MAIL_PORT') === 465);
assertTest('MAIL_USERNAME is loaded from .env', Env::get('MAIL_USERNAME') === 'sanketsidurkar720580@gmail.com');
assertTest('MAIL_PASSWORD is non-empty in .env', !empty(Env::get('MAIL_PASSWORD')));

// 2. Database Connection Test
echo "\n--- 2. Database Connection & Queries ---\n";
try {
    $dbConfig = Config::get('database.default');
    assertTest('Config::get("database.default") retrieves host from .env', $dbConfig['host'] === 'localhost');
    assertTest('Config::get("database.default") retrieves dbname from .env', $dbConfig['database'] === 'tours_travels_db');
    assertTest('Config::get("database.default") retrieves username from .env', $dbConfig['username'] === 'root');

    $pdo = Database::getConnection();
    assertTest('Database::getConnection() returns PDO instance', $pdo instanceof PDO);
    assertTest('Database::testConnection() returns true', Database::testConnection() === true);

    // Verify accessible tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    assertTest('Database contains tables (found ' . count($tables) . ')', count($tables) >= 40);

    // Test Model/Repository queries
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    assertTest("Users table accessible (found {$userCount} users)", $userCount !== false);

    $customerCount = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    assertTest("Customers table accessible (found {$customerCount} customers)", $customerCount !== false);

    $bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    assertTest("Bookings table accessible (found {$bookingCount} bookings)", $bookingCount !== false);

} catch (Throwable $e) {
    assertTest('Database operations succeeded', false, $e->getMessage());
}

// 3. Production Environment Validation Test (Fail-Safe Verification)
echo "\n--- 3. Production Validation & Security Fail-Safes ---\n";

// Test that Env::validate() throws in production when required database values are missing
$originalEnv = $_ENV;
try {
    // Simulate production with missing DB_PASSWORD
    $_ENV['APP_ENV'] = 'production';
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_DATABASE'] = 'live_db';
    $_ENV['DB_USERNAME'] = 'live_user';
    $_ENV['DB_PASSWORD'] = ''; // Missing password in production

    $threw = false;
    try {
        Env::validate();
    } catch (\RuntimeException $re) {
        $threw = true;
        assertTest('Production mode refuses empty DB_PASSWORD with RuntimeException', true);
        assertTest('Error message instructs to configure .env without leaking secrets', 
            str_contains($re->getMessage(), 'DB_PASSWORD') && !str_contains($re->getMessage(), 'root')
        );
    }
    if (!$threw) {
        assertTest('Production mode refuses empty DB_PASSWORD with RuntimeException', false, 'Did not throw');
    }

    // Simulate production with all valid values
    $_ENV['APP_ENV'] = 'production';
    $_ENV['DB_HOST'] = 'production-host';
    $_ENV['DB_DATABASE'] = 'production_db';
    $_ENV['DB_USERNAME'] = 'production_user';
    $_ENV['DB_PASSWORD'] = 'SecretProdPass123!';
    $_ENV['APP_DEBUG'] = false;
    $_ENV['MAIL_HOST'] = 'smtp.gmail.com';
    $_ENV['MAIL_USERNAME'] = 'test@example.com';
    $_ENV['MAIL_PASSWORD'] = 'app-password';

    $warnings = Env::validate();
    assertTest('Production mode passes validation when all required credentials are set', empty($warnings));

} finally {
    // Restore original environment
    $_ENV = $originalEnv;
    Config::reset('database');
}

// 4. SMTP Settings & Masking Verification
echo "\n--- 4. SMTP Settings & Masking ---\n";
EmailSettingsService::clearConfigCache();
$mailConfig = EmailSettingsService::getTransportConfig();
assertTest('EmailSettingsService reads host from .env', $mailConfig['host'] === 'smtp.gmail.com');
assertTest('EmailSettingsService reads port from .env', $mailConfig['port'] === 465);
assertTest('EmailSettingsService reads username from .env', $mailConfig['username'] === 'sanketsidurkar720580@gmail.com');
assertTest('EmailSettingsService reads password from .env', !empty($mailConfig['password']));

$displayConfig = EmailSettingsService::getDisplaySettings();
assertTest('EmailSettingsService masks password for UI display', $displayConfig['password_masked'] === '••••••••••••••••');
assertTest('Display settings does not contain raw password', !isset($displayConfig['password']) || $displayConfig['password'] === null);

// 5. Security & Git Files
echo "\n--- 5. .gitignore & .env.example Security ---\n";
$gitignore = file_get_contents(dirname(__DIR__) . '/.gitignore');
assertTest('.gitignore excludes .env', str_contains($gitignore, '.env'));
assertTest('.gitignore excludes .env.*', str_contains($gitignore, '.env.*'));
assertTest('.gitignore allows !.env.example', str_contains($gitignore, '!.env.example'));

$envExample = file_get_contents(dirname(__DIR__) . '/.env.example');
assertTest('.env.example exists', file_exists(dirname(__DIR__) . '/.env.example'));
assertTest('.env.example contains DB_HOST placeholder', str_contains($envExample, 'DB_HOST='));
assertTest('.env.example contains DB_DATABASE placeholder', str_contains($envExample, 'DB_DATABASE='));
assertTest('.env.example does NOT contain real Gmail App Password', !str_contains($envExample, 'fois tuso'));

// 6. Apache .htaccess Protection
echo "\n--- 6. Apache .htaccess Protection ---\n";
$rootHtaccess = file_get_contents(dirname(__DIR__) . '/.htaccess');
assertTest('Root .htaccess blocks .env via RewriteRule', str_contains($rootHtaccess, '^\.env.* - [F,L]'));
assertTest('Root .htaccess blocks .env via FilesMatch', str_contains($rootHtaccess, '<FilesMatch "(^\.env'));

$publicHtaccess = file_get_contents(dirname(__DIR__) . '/public/.htaccess');
assertTest('Public .htaccess blocks .env via RewriteRule', str_contains($publicHtaccess, '^\.env.* - [F,L]'));
assertTest('Public .htaccess blocks .env via FilesMatch', str_contains($publicHtaccess, '<FilesMatch "(^\.env'));

echo "\n===============================================================================\n";
echo " RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================================\n";

exit($failed > 0 ? 1 : 0);
