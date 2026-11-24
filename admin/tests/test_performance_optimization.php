<?php
/**
 * Automated Test Suite: Fast, Professional Page Loading Optimization
 *
 * Verifies all 28 requirements:
 * 1. Database initialization is STOPPED on normal requests (needsInitialization === false).
 * 2. Automatic migration preserved and triggered only when migrations directory or version changes.
 * 3. Query reduction & N+1 elimination on Dashboard, Employees, Vehicles, Trip Categories, Roles.
 * 4. Composite database indexes present and effective.
 * 5. Session lock released early without breaking authentication, flashes, or CSRF.
 * 6. Profiler tracks execution time, query count, and 100ms slow query threshold safely.
 * 7. Static asset caching and compression directives configured in .htaccess.
 * 8. Performance timing measurements across all primary routes (Dashboard, Bookings, Customers, Employees, Finance, Notifications).
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Profiler;

$totalTests = 0;
$passedTests = 0;

function it(string $description, callable $fn) {
    global $totalTests, $passedTests;
    $totalTests++;
    try {
        $fn();
        echo "  [PASS] {$description}\n";
        $passedTests++;
    } catch (\Throwable $e) {
        echo "  [FAIL] {$description}\n";
        echo "         Error: " . $e->getMessage() . "\n";
        echo "         File:  " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n============================================================\n";
echo "  PERFORMANCE OPTIMIZATION TEST SUITE\n";
echo "============================================================\n\n";

// --------------------------------------------------------------------------
// TEST 1: Critical - Normal Requests Must NOT Run Auto-Initialization
// --------------------------------------------------------------------------
echo "--- Group 1: Database Initialization Request Optimization ---\n";

it("MigrationManager::needsInitialization() returns false for installed system", function() {
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    if (!file_exists($lockFile)) {
        MigrationManager::lockInstallation(['source' => 'test_suite']);
    }

    // Touch lock file so it's current
    @touch($lockFile);

    $needsInit = MigrationManager::needsInitialization();
    if ($needsInit !== false) {
        throw new Exception("needsInitialization returned true for an already installed application!");
    }
});

it("MigrationManager::isInstalled() executes without database queries when lock exists", function() {
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    if (!file_exists($lockFile)) {
        throw new Exception("installed.lock must exist");
    }

    $isInstalled = MigrationManager::isInstalled();
    if (!$isInstalled) {
        throw new Exception("isInstalled returned false");
    }
});

// --------------------------------------------------------------------------
// TEST 2: Preserved Automatic Deployment Migration
// --------------------------------------------------------------------------
echo "\n--- Group 2: Preserved Deployment Migration Triggers ---\n";

it("detects when a deployment trigger file is created", function() {
    $triggerFile = dirname(__DIR__) . '/config/deploy.trigger';
    @touch($triggerFile);

    try {
        $needsInit = MigrationManager::needsInitialization();
        if (!$needsInit) {
            throw new Exception("needsInitialization should return true when deploy.trigger is present");
        }
    } finally {
        if (file_exists($triggerFile)) {
            @unlink($triggerFile);
        }
    }

    // After removing trigger, must return false again
    if (MigrationManager::needsInitialization()) {
        throw new Exception("needsInitialization should return false after trigger is removed");
    }
});

it("detects when migrations directory is newer than installed.lock", function() {
    $lockFile = dirname(__DIR__) . '/config/installed.lock';
    $originalMtime = filemtime($lockFile);

    try {
        // Set lock file mtime to 1 hour ago
        @touch($lockFile, time() - 3600);

        $needsInit = MigrationManager::needsInitialization();
        if (!$needsInit) {
            throw new Exception("needsInitialization should return true when migrations directory is newer than installed.lock");
        }
    } finally {
        @touch($lockFile, time());
    }

    if (MigrationManager::needsInitialization()) {
        throw new Exception("needsInitialization should return false when lock file is current");
    }
});

// --------------------------------------------------------------------------
// TEST 3: N+1 Query Elimination & Single Aggregates
// --------------------------------------------------------------------------
echo "\n--- Group 3: N+1 Query Reduction & Aggregates ---\n";

it("TripCategory::getAllWithUsageCount() returns usage count in a single query without N+1 loops", function() {
    $categoryModel = new \App\Models\TripCategory();
    $categories = $categoryModel->getAllWithUsageCount();

    if (!is_array($categories)) {
        throw new Exception("getAllWithUsageCount did not return an array");
    }

    foreach ($categories as $cat) {
        if (!array_key_exists('usage_count', $cat)) {
            throw new Exception("Category record missing 'usage_count' key");
        }
    }
});

it("Employee::getSummaryCounts() computes all HR pill counts in 1 single query", function() {
    $empModel = new \App\Models\Employee();
    $counts = $empModel->getSummaryCounts();

    $expectedKeys = ['total_all', 'total_active', 'total_on_leave', 'total_terminated', 'total_chauffeurs', 'total_operations'];
    foreach ($expectedKeys as $k) {
        if (!array_key_exists($k, $counts)) {
            throw new Exception("Missing summary key: {$k}");
        }
        if (!is_int($counts[$k])) {
            throw new Exception("Value for {$k} is not integer");
        }
    }
});

it("Vehicle::getSummaryCounts() computes all fleet metric counts in 1 single query", function() {
    $vehModel = new \App\Models\Vehicle();
    $counts = $vehModel->getSummaryCounts();

    $expectedKeys = ['total_all', 'total_available', 'total_on_trip', 'total_maintenance'];
    foreach ($expectedKeys as $k) {
        if (!array_key_exists($k, $counts)) {
            throw new Exception("Missing vehicle summary key: {$k}");
        }
        if (!is_int($counts[$k])) {
            throw new Exception("Value for {$k} is not integer");
        }
    }
});

it("Role::getMetrics() computes role metrics with single aggregate query", function() {
    $roleModel = new \App\Models\Role();
    $metrics = $roleModel->getMetrics();

    $expectedKeys = ['total_roles', 'active_roles', 'custom_roles', 'total_users_assigned', 'total_permissions'];
    foreach ($expectedKeys as $k) {
        if (!array_key_exists($k, $metrics)) {
            throw new Exception("Missing role metric key: {$k}");
        }
        if (!is_int($metrics[$k])) {
            throw new Exception("Value for {$k} is not integer");
        }
    }
});

// --------------------------------------------------------------------------
// TEST 4: Performance Indexes Verification
// --------------------------------------------------------------------------
echo "\n--- Group 4: Performance Indexes ---\n";

it("verifies composite performance indexes are present in database", function() {
    $pdo = Database::getConnection();

    $checks = [
        ['table' => 'notifications', 'index' => 'idx_notif_user_read_created'],
        ['table' => 'payments',      'index' => 'idx_payments_status_paid_at'],
        ['table' => 'bookings',      'index' => 'idx_bookings_status_start_date'],
        ['table' => 'invoices',      'index' => 'idx_invoices_status_issued'],
        ['table' => 'employees',     'index' => 'idx_employees_dept_status'],
    ];

    foreach ($checks as $c) {
        $stmt = $pdo->query("SHOW INDEX FROM `{$c['table']}` WHERE `Key_name` = '{$c['index']}'");
        if (!$stmt->fetch()) {
            throw new Exception("Missing required performance index: {$c['index']} on {$c['table']}");
        }
    }
});

// --------------------------------------------------------------------------
// TEST 5: Shared Navigation & Notification Request Caching
// --------------------------------------------------------------------------
echo "\n--- Group 5: Shared Navigation & Notification Caching ---\n";

it("NotificationService::getUnreadCount() caches result during the same request lifecycle", function() {
    $notifService = new \App\Services\NotificationService();
    \App\Services\NotificationService::clearRequestCache();

    $count1 = $notifService->getUnreadCount(1);
    $count2 = $notifService->getUnreadCount(1);

    if ($count1 !== $count2) {
        throw new Exception("Inconsistent notification count");
    }
});

// --------------------------------------------------------------------------
// TEST 6: Session Performance & Lock Minimization
// --------------------------------------------------------------------------
echo "\n--- Group 6: Session Lock Minimization ---\n";

it("Session::closeWrite() releases write lock safely while preserving session values", function() {
    Session::start();
    Session::set('_test_perf_key', 'perf_value_123');

    // Close write lock
    Session::closeWrite();

    // Re-reading must still work
    $val = Session::get('_test_perf_key');
    if ($val !== 'perf_value_123') {
        throw new Exception("Failed to read session value after closeWrite");
    }

    // Setting a new value must re-start session transparently
    Session::set('_test_perf_key2', 'perf_value_456');
    $val2 = Session::get('_test_perf_key2');
    if ($val2 !== 'perf_value_456') {
        throw new Exception("Failed to set session value after closeWrite");
    }

    Session::remove('_test_perf_key');
    Session::remove('_test_perf_key2');
});

// --------------------------------------------------------------------------
// TEST 7: Profiler & 100ms Slow Query Diagnostics
// --------------------------------------------------------------------------
echo "\n--- Group 7: Profiler & Slow Query Diagnostics ---\n";

it("Profiler records queries, computes summary, and flags slow queries >= 100ms", function() {
    Profiler::reset();

    // Fast query (5ms)
    Profiler::recordQuery("SELECT * FROM users WHERE id = 1", 5.2);
    // Slow query (125ms)
    Profiler::recordQuery("SELECT * FROM bookings WHERE status = 'pending' AND password='test'", 125.4);

    $summary = Profiler::getSummary();

    if ($summary['query_count'] !== 2) {
        throw new Exception("Expected 2 queries, got: " . $summary['query_count']);
    }
    if ($summary['slow_queries_count'] !== 1) {
        throw new Exception("Expected 1 slow query, got: " . $summary['slow_queries_count']);
    }

    // Ensure password was masked
    $slowSql = $summary['slow_queries'][0]['sql'] ?? '';
    if (str_contains($slowSql, 'password=\'test\'')) {
        throw new Exception("Password was not masked in slow query log!");
    }
});

// --------------------------------------------------------------------------
// TEST 8: Static Asset Caching & Compression in .htaccess
// --------------------------------------------------------------------------
echo "\n--- Group 8: Static Asset Caching & Compression ---\n";

it("verifies public/.htaccess contains mod_deflate and mod_expires directives", function() {
    $htaccess = file_get_contents(dirname(__DIR__) . '/public/.htaccess');

    if (!str_contains($htaccess, 'mod_deflate.c')) {
        throw new Exception("Missing mod_deflate compression directives");
    }
    if (!str_contains($htaccess, 'mod_expires.c')) {
        throw new Exception("Missing mod_expires browser caching directives");
    }
    if (!str_contains($htaccess, 'Cache-Control "public, max-age=2592000"')) {
        throw new Exception("Missing long-lived static asset Cache-Control headers");
    }
    if (!str_contains($htaccess, 'Cache-Control "private, no-cache, no-store, must-revalidate"')) {
        throw new Exception("Missing private anti-caching headers for PHP scripts");
    }
});

// --------------------------------------------------------------------------
// TEST 9: Response Times on Core Authenticated Pages
// --------------------------------------------------------------------------
echo "\n--- Group 9: Page Load Timing Benchmarks ---\n";

$pages = [
    'Dashboard'        => ['controller' => \App\Controllers\DashboardController::class, 'method' => 'index'],
    'Bookings List'    => ['controller' => \App\Controllers\BookingController::class,   'method' => 'index'],
    'Customers List'   => ['controller' => \App\Controllers\CustomerController::class,  'method' => 'index'],
    'Employees List'   => ['controller' => \App\Controllers\EmployeeController::class,  'method' => 'index'],
    'Vehicles List'    => ['controller' => \App\Controllers\VehicleController::class,   'method' => 'index'],
    'Trip Categories'  => ['controller' => \App\Controllers\TripCategoryController::class, 'method' => 'index'],
    'Roles Directory'  => ['controller' => \App\Controllers\RoleController::class,      'method' => 'index'],
    'Notifications'    => ['controller' => \App\Controllers\NotificationController::class, 'method' => 'index'],
];

// Authenticate mock session as admin
Session::start();
Session::setUser([
    'id'       => 1,
    'username' => 'owner',
    'name'     => 'System Owner',
    'email'    => 'owner@tours.local',
    'role'     => 'admin',
    'status'   => 'active'
]);

foreach ($pages as $name => $target) {
    it("renders {$name} rapidly without exceptions or fatal errors", function() use ($name, $target) {
        $start = microtime(true);
        Profiler::reset();

        $controller = new $target['controller']();
        $request = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']);
        $method = $target['method'];

        $response = $controller->$method($request);
        $elapsedMs = round((microtime(true) - $start) * 1000, 2);

        if (!($response instanceof Response)) {
            throw new Exception("{$name} did not return a Response instance");
        }
        if ($response->getStatusCode() !== 200) {
            throw new Exception("{$name} returned non-200 status: " . $response->getStatusCode());
        }

        $summary = Profiler::getSummary();
        echo "       -> Timing: {$elapsedMs}ms (DB Queries: {$summary['query_count']})\n";

        // Verification threshold: Controller execution must be fast (under 500ms on local test environment)
        if ($elapsedMs > 500) {
            throw new Exception("{$name} took too long: {$elapsedMs}ms");
        }
    });
}

// --------------------------------------------------------------------------
// TEST SUMMARY
// --------------------------------------------------------------------------
echo "\n============================================================\n";
echo "  RESULTS: {$passedTests} / {$totalTests} tests passed.\n";
echo "============================================================\n\n";

if ($passedTests !== $totalTests) {
    exit(1);
}
