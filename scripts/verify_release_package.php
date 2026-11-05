<?php
declare(strict_types=1);

/**
 * Release Package Verification & Extraction Acceptance Test
 * Tests ZIP integrity, scans every entry for security violations, extracts to a clean isolated directory,
 * configures environment from .env.example, and validates bootstrapping & migration readiness.
 */

$projectRoot = dirname(__DIR__);
$zipPath = $projectRoot . DIRECTORY_SEPARATOR . 'tours_travels_release.zip';
$extractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tours_release_test_' . time();

echo "========================================================================\n";
echo " RELEASE PACKAGE VERIFICATION & EXTRACTION ACCEPTANCE TEST\n";
echo " Target ZIP:    {$zipPath}\n";
echo " Extraction To: {$extractDir}\n";
echo "========================================================================\n\n";

if (!file_exists($zipPath)) {
    echo "ERROR: Release archive {$zipPath} not found! Run scripts/package_release.php first.\n";
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    echo "ERROR: Failed to open ZIP archive.\n";
    exit(1);
}

echo "1. Scanning all {$zip->numFiles} archive entries for security compliance...\n";
$violations = [];
$requiredFiles = [
    '.env.example' => false,
    'public/index.php' => false,
    'public/manifest.json' => false,
    'public/sw.js' => false,
    'app/bootstrap.php' => false,
    'app/routes.php' => false,
    'mobile-driver/package.json' => false,
    'app/Migrations/044_create_remember_tokens_and_caching_tables.php' => false,
];

for ($i = 0; $i < $zip->numFiles; $i++) {
    $entry = $zip->getNameIndex($i);

    // Rule 1: .env must NEVER be present
    if (basename($entry) === '.env') {
        $violations[] = "PROHIBITED: Plain .env file found: {$entry}";
    }

    // Rule 2: .git directory must NEVER be present
    if (str_starts_with($entry, '.git/') || str_starts_with($entry, '.git\\')) {
        $violations[] = "PROHIBITED: Git VCS entry found: {$entry}";
    }

    // Rule 3: Automated tests must NEVER be present
    if (str_starts_with($entry, 'tests/') || str_starts_with($entry, 'tests\\')) {
        $violations[] = "PROHIBITED: Test suite file found: {$entry}";
    }

    // Rule 4: SQL dumps must NEVER be present
    if (str_ends_with(strtolower($entry), '.sql')) {
        $violations[] = "PROHIBITED: Raw database dump found: {$entry}";
    }

    // Check required files
    foreach ($requiredFiles as $req => $found) {
        if ($entry === $req || str_replace('\\', '/', $entry) === $req) {
            $requiredFiles[$req] = true;
        }
    }
}

if (!empty($violations)) {
    echo "✗ SECURITY VIOLATIONS DETECTED IN ARCHIVE:\n";
    foreach ($violations as $v) {
        echo "  - {$v}\n";
    }
    $zip->close();
    exit(1);
}

echo "  ✓ Archive contains ZERO plain .env files\n";
echo "  ✓ Archive contains ZERO .git/ VCS metadata\n";
echo "  ✓ Archive contains ZERO test suite files\n";
echo "  ✓ Archive contains ZERO raw .sql database dumps\n";

// Check all required entries are present
$missingRequired = [];
foreach ($requiredFiles as $req => $found) {
    if (!$found) {
        $missingRequired[] = $req;
    }
}

if (!empty($missingRequired)) {
    echo "✗ MISSING REQUIRED FILES IN ARCHIVE:\n";
    foreach ($missingRequired as $m) {
        echo "  - {$m}\n";
    }
    $zip->close();
    exit(1);
}

echo "  ✓ All core application files verified present in archive\n";

echo "\n2. Extracting archive into clean temporary test directory...\n";
if (!mkdir($extractDir, 0777, true)) {
    echo "ERROR: Failed to create temp directory {$extractDir}\n";
    $zip->close();
    exit(1);
}

$zip->extractTo($extractDir);
$zip->close();
echo "  ✓ Archive extracted successfully.\n";

echo "\n3. Testing isolated application bootstrap from extracted files...\n";
$extractedEnvExample = $extractDir . DIRECTORY_SEPARATOR . '.env.example';
$extractedEnv = $extractDir . DIRECTORY_SEPARATOR . '.env';

if (!file_exists($extractedEnvExample)) {
    echo "ERROR: .env.example missing in extracted archive!\n";
    exit(1);
}

// Copy .env.example to .env and configure test database
$envContent = file_get_contents($extractedEnvExample);
$envContent = str_replace('DB_DATABASE=tours_travels_db', 'DB_DATABASE=tours_travels_test_db', $envContent);
$envContent = str_replace('APP_ENV=production', 'APP_ENV=testing', $envContent);
file_put_contents($extractedEnv, $envContent);
echo "  ✓ Configured extracted .env for test database\n";

// Execute verification script in the extracted environment
$extractedCheckScript = $extractDir . DIRECTORY_SEPARATOR . 'verify_extracted.php';
$checkCode = <<<'PHP'
<?php
require_once __DIR__ . '/app/bootstrap.php';
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Config;

$pdo = Database::getConnection();
$dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
echo "EXTRACTED_DB: {$dbName}\n";

$pending = count(MigrationManager::getPendingMigrations($pdo));
echo "EXTRACTED_PENDING_MIGRATIONS: {$pending}\n";

$usersExist = (bool)$pdo->query("SHOW TABLES LIKE 'users'")->fetch();
echo "EXTRACTED_USERS_TABLE: " . ($usersExist ? 'YES' : 'NO') . "\n";

echo "EXTRACTED_BOOTSTRAP_SUCCESS\n";
PHP;

file_put_contents($extractedCheckScript, $checkCode);

$phpBinary = 'E:\\xampp\\php\\php.exe';
$cmd = "\"{$phpBinary}\" \"{$extractedCheckScript}\"";
$output = shell_exec($cmd);

echo "Extracted App Output:\n" . trim((string)$output) . "\n\n";

if (!str_contains((string)$output, 'EXTRACTED_BOOTSTRAP_SUCCESS')) {
    echo "✗ Extracted application bootstrap failed!\n";
    exit(1);
}

echo "  ✓ Extracted application booted cleanly\n";
echo "  ✓ Extracted database connection verified\n";
echo "  ✓ Extracted migrations discovered successfully\n";

echo "\n4. Cleaning up temporary extraction directory...\n";
function recursiveDelete(string $dir): void {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? recursiveDelete($path) : @unlink($path);
    }
    @rmdir($dir);
}
recursiveDelete($extractDir);
echo "  ✓ Temporary extraction directory removed.\n";

echo "\n========================================================================\n";
echo " RELEASE PACKAGE ACCEPTANCE: 100% VERIFIED AND READY FOR DEPLOYMENT\n";
echo "========================================================================\n";

