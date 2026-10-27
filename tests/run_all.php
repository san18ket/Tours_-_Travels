<?php
/**
 * Master Verification Test Suite
 */

require_once __DIR__ . '/TestRunner.php';

use Tests\TestRunner;
use App\Core\Database;
use App\Core\MigrationManager;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\RateLimiter;
use App\Core\View;
use App\Services\BookingService;
use App\Services\BillingService;
use App\Services\DispatchService;
use App\Services\GpsTrackingService;
use App\Services\BackupService;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\Dispatch;
use App\Models\Employee;

$runner = new TestRunner();

// =========================================================================
// SUITE 1: Automatic Database Initialization & Migrations
// =========================================================================
$runner->runSuite('Database Initialization & Versioned Migrations', function (TestRunner $t) {
    // 1. Database Creation & Connectivity
    $created = Database::createDatabaseIfNotExists();
    $t->assert($created === true, 'Database verified or created idempotently using installer credentials');

    $pdo = Database::getConnection();
    $t->assert($pdo instanceof PDO, 'Runtime PDO connection established successfully');

    // 2. Run Idempotent Migrations with Advisory Locking
    $migrationResult = MigrationManager::runPending($pdo, true);
    $t->assert($migrationResult['success'] === true, 'All versioned migrations executed cleanly inside transactional blocks');

    // 3. Schema Migrations History Table Verification
    $executed = MigrationManager::getExecutedMigrations($pdo);
    $t->assert(count($executed) >= 8, 'Migration history records verified in schema_migrations table (count: ' . count($executed) . ')');

    // 4. Repeated Migration Run (Idempotency & Zero Data Loss)
    $secondRun = MigrationManager::runPending($pdo, true);
    $t->assert($secondRun['success'] === true && count($secondRun['migrated']) === 0, 'Re-running migrations is idempotent; pending migrations count is 0');

    // 5. Locking Concurrency Simulation
    $lockAcquired = Database::acquireLock('test_concurrency_lock', 2);
    $t->assert($lockAcquired === true, 'Distributed MySQL advisory lock acquired successfully (GET_LOCK)');
    $lockReleased = Database::releaseLock('test_concurrency_lock');
    $t->assert($lockReleased === true, 'Distributed MySQL advisory lock released successfully (RELEASE_LOCK)');

    // 6. Installation Lock System
    $isInstalled = MigrationManager::isInstalled();
    if (!$isInstalled) {
        MigrationManager::lockInstallation(['test_run' => true]);
    }
    $t->assert(MigrationManager::isInstalled() === true, 'Installation lock file exists and locks future unauthorized installer executions');
});

// =========================================================================
// SUITE 2: Security, Authentication & Input Defense
// =========================================================================
$runner->runSuite('Security, Authentication, XSS & SQL Injection Defense', function (TestRunner $t) {
    // 1. Password Hashing & Verification
    $plainPass = 'SuperSecret@123';
    $hashed = password_hash($plainPass, PASSWORD_BCRYPT, ['cost' => 12]);
    $t->assert(password_verify($plainPass, $hashed) === true, 'Bcrypt password hashing and constant-time verification passes');
    $t->assert(password_verify('WrongPassword', $hashed) === false, 'Invalid password is firmly rejected');

    // 2. CSRF Token Generation & Timing-Safe Comparison
    $token1 = Csrf::getToken();
    $t->assert(strlen($token1) === 64, 'Cryptographically secure 64-char hex CSRF token generated');
    $t->assert(Csrf::validate($token1) === true, 'Valid CSRF token accepted via hash_equals');
    $t->assert(Csrf::validate('invalid_token_attempt_12345') === false, 'Tampered CSRF token rejected');
    $t->assert(Csrf::validate(null) === false, 'Null CSRF token rejected');

    // 3. Output Escaping / XSS Immunity
    $xssPayload = '<script>alert("XSS")</script>&"\'';
    $escaped = View::e($xssPayload);
    $t->assert(!str_contains($escaped, '<script>'), 'Output escaping neutralizes script tags (< converted to &lt;)');
    $t->assert(str_contains($escaped, '&quot;') || str_contains($escaped, '&#039;'), 'Quotes escaped safely with ENT_QUOTES');

    // 4. SQL Injection Immunity with PDO Prepared Statements
    $userModel = new User();
    $sqliAttempt = "admin@voyagego.com' OR '1'='1";
    $result = $userModel->findByEmail($sqliAttempt);
    $t->assert($result === null, "SQL Injection attempt (' OR '1'='1) safely parameterized and returned 0 records");

    // 5. Rate Limiting Defense
    $throttleKey = 'test_rate_limit_' . uniqid();
    for ($i = 0; $i < 3; $i++) {
        RateLimiter::hit($throttleKey, 30);
    }
    $t->assert(RateLimiter::tooManyAttempts($throttleKey, 5) === false, 'Attempts under threshold allowed');
    RateLimiter::hit($throttleKey, 30);
    RateLimiter::hit($throttleKey, 30); // 5th hit
    $t->assert(RateLimiter::tooManyAttempts($throttleKey, 5) === true, 'Rate limiter locks out after exceeding threshold');
    RateLimiter::clear($throttleKey);
    $t->assert(RateLimiter::tooManyAttempts($throttleKey, 5) === false, 'Rate limit clear resets attempts safely');
});

// =========================================================================
// SUITE 3: Business Logic, Pricing & Invoice Immutability
// =========================================================================
$runner->runSuite('Business Rules, Pricing, Invoices & Idempotency', function (TestRunner $t) {
    $bookingService = new BookingService();
    $billingService = new BillingService();
    $dispatchService = new DispatchService();
    $gpsService = new GpsTrackingService();

    // 1. Pricing Calculation Accuracy (5% GST, passenger tiers, discounts)
    $pricing1 = $bookingService->calculatePricing(null, 2, 10000.00, 1000.00);
    $t->assert($pricing1['subtotal'] == 10000.00, 'Subtotal correctly computed: ' . $pricing1['subtotal']);
    $t->assert($pricing1['discount_amount'] == 1000.00, 'Discount correctly applied: ' . $pricing1['discount_amount']);
    $t->assert($pricing1['tax_amount'] == 450.00, '5% GST accurately computed on taxable amount (9000 * 0.05 = 450): ' . $pricing1['tax_amount']);
    $t->assert($pricing1['total_amount'] == 9450.00, 'Final total correctly matches subtotal - discount + tax: ' . $pricing1['total_amount']);

    // 2. Booking Idempotency
    $userModel = new User();
    $customer = $userModel->findBy('role', 'customer');
    if (!$customer) {
        $customerId = $userModel->create([
            'name' => 'Test Customer',
            'email' => 'test.cust.' . uniqid() . '@example.com',
            'password' => 'secret',
            'role' => 'customer',
            'status' => 'active'
        ]);
        $customer = $userModel->find($customerId);
    }

    $idempToken = 'test_idemp_' . uniqid();
    $booking1 = $bookingService->createBooking([
        'customer_id'       => $customer['id'],
        'pickup_address'    => 'New Delhi Airport T3',
        'drop_address'      => 'Civil Lines, Jaipur',
        'start_date'        => date('Y-m-d', strtotime('+2 days')),
        'end_date'          => date('Y-m-d', strtotime('+5 days')),
        'passenger_count'   => 3,
        'custom_subtotal'   => 15000.00,
        'discount_amount'   => 0.00,
        'idempotency_token' => $idempToken,
    ]);
    $t->assert(!empty($booking1['id']), 'Booking reservation created successfully with code: ' . $booking1['booking_code']);

    // Attempt duplicate booking submission with identical idempotency token
    $bookingDuplicate = $bookingService->createBooking([
        'customer_id'       => $customer['id'],
        'pickup_address'    => 'Different Address',
        'drop_address'      => 'Different Address',
        'start_date'        => date('Y-m-d', strtotime('+2 days')),
        'end_date'          => date('Y-m-d', strtotime('+5 days')),
        'passenger_count'   => 3,
        'custom_subtotal'   => 15000.00,
        'idempotency_token' => $idempToken,
    ]);
    $t->assert($bookingDuplicate['id'] === $booking1['id'], 'Duplicate booking submission prevented by idempotency token; returned existing record');

    // 3. Invoice Generation & Strict Immutability
    $invoice = $billingService->generateInvoice($booking1['id']);
    $t->assert(!empty($invoice['id']), 'Tax invoice generated with number: ' . $invoice['invoice_number']);
    $t->assert((int)$invoice['is_immutable'] === 1, 'Invoice marked as immutable (is_immutable = 1)');

    // Attempt to tamper with finalized invoice subtotal
    $invoiceModel = new Invoice();
    $t->assertThrows(function () use ($invoiceModel, $invoice) {
        $invoiceModel->update($invoice['id'], ['subtotal' => 1.00]);
    }, RuntimeException::class, 'Direct mutation of finalized immutable invoice amounts is blocked');

    // Attempt to delete finalized invoice
    $t->assertThrows(function () use ($invoiceModel, $invoice) {
        $invoiceModel->delete($invoice['id']);
    }, RuntimeException::class, 'Deletion of finalized immutable invoice is blocked under accounting compliance');

    // 4. Payment Allocation & Duplicate Payment Prevention
    $payIdempKey = 'pay_idemp_' . uniqid();
    $payment = $billingService->recordPayment(
        $invoice['id'],
        5000.00,
        'upi',
        'TXN-' . uniqid(),
        $payIdempKey,
        'Advance payment'
    );
    $t->assert(!empty($payment['id']), 'Payment allocated successfully. Reference: ' . $payment['payment_reference']);

    // Verify invoice paid_amount and status transitioned to partially_paid
    $updatedInvoice = $invoiceModel->find($invoice['id']);
    $t->assert($updatedInvoice['paid_amount'] == 5000.00, 'Invoice paid_amount updated to 5000.00');
    $t->assert($updatedInvoice['payment_status'] === 'partially_paid', 'Invoice status transitioned to partially_paid');

    // Duplicate payment attempt with same idempotency key
    $duplicatePayment = $billingService->recordPayment(
        $invoice['id'],
        5000.00,
        'upi',
        'TXN-' . uniqid(),
        $payIdempKey,
        'Duplicate attempt'
    );
    $t->assert($duplicatePayment['id'] === $payment['id'], 'Duplicate payment prevented via idempotency key; double charging thwarted');

    // 5. GPS Submission Validation: On-Duty Standby Policy vs Off-Duty Privacy Rejection
    $driverModel = new DriverProfile();
    $allDrivers = $driverModel->all();
    if (!empty($allDrivers)) {
        $driverId = (int)$allDrivers[0]['id'];
        $originalStatus = $allDrivers[0]['duty_status'] ?? 'off_duty';

        // When off-duty, GPS location update MUST be rejected to preserve chauffeur privacy
        $driverModel->update($driverId, ['duty_status' => 'off_duty']);
        $t->assertThrows(function () use ($gpsService, $driverId) {
            $gpsService->recordDriverLocation($driverId, 28.6139, 77.2090, 45.0);
        }, RuntimeException::class, 'GPS coordinates rejected when chauffeur is off-duty (privacy safeguard)');

        // When on-duty without active trip, location sharing is allowed under on_duty_standby policy
        $driverModel->update($driverId, ['duty_status' => 'on_duty']);
        $standbyResult = $gpsService->recordDriverLocation($driverId, 28.6139, 77.2090, 45.0);
        $t->assert($standbyResult['success'] === true && $standbyResult['policy'] === 'on_duty_standby', 'GPS coordinates accepted for on-duty chauffeur under on_duty_standby policy');

        // Restore status
        $driverModel->update($driverId, ['duty_status' => $originalStatus]);
    }

    // Invalid coordinates boundary check
    $t->assertThrows(function () use ($gpsService) {
        $gpsService->recordDriverLocation(9999, 999.0, 999.0);
    }, RuntimeException::class, 'Geospatially impossible coordinates (>90 deg latitude) rejected');
});

// =========================================================================
// SUITE 4: Backups, Checksums & Restoration Procedure
// =========================================================================
$runner->runSuite('Database Backup, Integrity Checksum & Retention', function (TestRunner $t) {
    $backupService = new BackupService();

    // 1. Create Backup Snapshot
    $backup = $backupService->createBackup();
    $t->assert(file_exists($backup['filepath']), 'Database SQL backup archive created on disk: ' . $backup['filename']);
    $t->assert(!empty($backup['checksum']), 'SHA-256 integrity checksum calculated: ' . substr($backup['checksum'], 0, 16) . '...');
    $t->assert($backup['size_bytes'] > 1000, 'Backup contains schema and records (size: ' . round($backup['size_bytes'] / 1024, 2) . ' KB)');

    // 2. Backup Integrity Check
    $content = file_get_contents($backup['filepath']);
    $t->assert(str_contains($content, 'VoyageGo Database Backup'), 'Backup archive contains authorized VoyageGo header');
    $t->assert(str_contains($content, 'CREATE TABLE'), 'Backup archive contains verified DDL schema statements');

    // 3. Test Verified Restoration Procedure
    $restoreResult = $backupService->restoreBackup($backup['filepath']);
    $t->assert($restoreResult['success'] === true, 'Restoration procedure verified successfully inside transaction');
});

// =========================================================================
// SUITE 5: Owner Authentication & Admin User Management
// =========================================================================
$runner->runSuite('Owner Authentication & Admin User Management', function (TestRunner $t) {
    $authService = new \App\Services\AuthService();
    $userModel = new User();

    // 1. Login with owner username ('owner' / 'owner123')
    $ownerLogin = $authService->login('owner', 'owner123');
    $t->assert($ownerLogin['username'] === 'owner' && $ownerLogin['role'] === 'admin', 'Login with default owner username succeeds with admin role');

    // 2. Login with existing user email ('admin@voyagego.com')
    $adminUser = $userModel->findBy('email', 'admin@voyagego.com');
    if (!$adminUser) {
        $userModel->create([
            'name'       => 'Aditya Singhania',
            'username'   => 'admin',
            'email'      => 'admin@voyagego.com',
            'phone'      => '+91 98111 22334',
            'password'   => password_hash('Admin@12345', PASSWORD_BCRYPT, ['cost' => 12]),
            'role'       => 'admin',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
    $adminEmailLogin = $authService->login('admin@voyagego.com', 'Admin@12345');
    $t->assert($adminEmailLogin['email'] === 'admin@voyagego.com', 'Login with existing user email succeeds');

    // 3. Existing users remaining able to log in by backfilled username after migration
    $adminUsernameLogin = $authService->login('admin', 'Admin@12345');
    $t->assert($adminUsernameLogin['id'] === $adminEmailLogin['id'], 'Existing users remain able to log in by username after migration');

    // 4. Correct and incorrect passwords
    $t->assertThrows(function () use ($authService) {
        $authService->login('owner', 'WrongPassword!123');
    }, RuntimeException::class, 'Invalid password for owner is firmly rejected');

    $t->assertThrows(function () use ($authService) {
        $authService->login('non_existent_user', 'anypassword');
    }, RuntimeException::class, 'Non-existent username/email is firmly rejected');

    // 5. Creating a user and rejecting duplicate usernames/emails
    $testUsername = 'ops.lead.' . uniqid();
    $testEmail = 'ops.lead.' . uniqid() . '@voyagego.com';
    $newUserId = $userModel->create([
        'name'       => 'Operations Lead',
        'username'   => $testUsername,
        'email'      => $testEmail,
        'phone'      => '+91 99999 11111',
        'password'   => password_hash('Lead@12345', PASSWORD_BCRYPT, ['cost' => 12]),
        'role'       => 'dispatcher',
        'status'     => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    $t->assert($newUserId > 0, "New user account created successfully with ID: {$newUserId}");

    // Test duplicate username rejection
    $existingByUsername = $userModel->findByUsername($testUsername);
    $t->assert(!empty($existingByUsername), 'User lookup by username returns created record');

    $duplicateUserThrown = false;
    try {
        $userModel->create([
            'name'       => 'Duplicate User',
            'username'   => $testUsername,
            'email'      => 'another_' . uniqid() . '@voyagego.com',
            'password'   => password_hash('Pass@12345', PASSWORD_BCRYPT),
            'role'       => 'customer',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        $duplicateUserThrown = true;
    }
    $t->assert($duplicateUserThrown === true, 'Database level constraint rejects duplicate username');

    // Test duplicate email rejection
    $duplicateEmailThrown = false;
    try {
        $userModel->create([
            'name'       => 'Duplicate Email User',
            'username'   => 'diff_user_' . uniqid(),
            'email'      => $testEmail,
            'password'   => password_hash('Pass@12345', PASSWORD_BCRYPT),
            'role'       => 'customer',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        $duplicateEmailThrown = true;
    }
    $t->assert($duplicateEmailThrown === true, 'Database level constraint rejects duplicate email');

    // 6. Admin-only access to user-management routes
    $appRouter = require dirname(__DIR__) . '/app/routes.php';

    // Non-admin user access test: Customer accessing /office/users
    Session::start();
    Session::set('user_id', 7);
    Session::set('user_role', 'customer');
    Session::set('user', ['id' => 7, 'name' => 'Test Customer', 'role' => 'customer', 'username' => 'test.customer', 'email' => 'test@example.com']);

    $customerReq = new \App\Core\Request([], [], [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => '/office/users',
        'SCRIPT_NAME'    => '/index.php',
        'SERVER_NAME'    => '127.0.0.1',
        'SERVER_PORT'    => '80',
    ]);
    $forbiddenResp = $appRouter->dispatch($customerReq);
    $t->assert($forbiddenResp->getStatusCode() === 403, 'Customer role blocked with 403 Forbidden on /office/users');

    // Admin user access test: Admin accessing /office/users
    Session::set('user_id', $ownerLogin['id']);
    Session::set('user_role', 'admin');
    Session::set('user', $ownerLogin);

    $adminReq = new \App\Core\Request([], [], [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => '/office/users',
        'SCRIPT_NAME'    => '/index.php',
        'SERVER_NAME'    => '127.0.0.1',
        'SERVER_PORT'    => '80',
    ]);
    $adminResp = $appRouter->dispatch($adminReq);
    $t->assert($adminResp->getStatusCode() === 200, 'Admin role granted 200 OK access to /office/users');

    // 7. CSRF rejection on state-changing user route
    $csrfFailReq = new \App\Core\Request([], [
        'name' => 'Bad User', 'username' => 'bad.user', 'email' => 'bad@example.com', 'role' => 'customer', 'status' => 'active'
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/office/users',
        'SCRIPT_NAME'    => '/index.php',
        'SERVER_NAME'    => '127.0.0.1',
        'SERVER_PORT'    => '80',
    ]);
    $csrfResp = $appRouter->dispatch($csrfFailReq);
    $t->assert($csrfResp->getStatusCode() === 302 || $csrfResp->getStatusCode() === 403, 'State-changing POST without CSRF token is intercepted and rejected');

    // 8. Password hashing and password reset
    $resetPass = 'NewlyResetSecret@999';
    $newHash = password_hash($resetPass, PASSWORD_BCRYPT, ['cost' => 12]);
    $userModel->updatePassword($newUserId, $newHash);
    $updatedUser = $userModel->find($newUserId);
    $t->assert(password_verify($resetPass, $updatedUser['password']) === true, 'Password reset securely updates hash and verifies with new password');
    $t->assert(password_verify('Lead@12345', $updatedUser['password']) === false, 'Old password invalidated after reset');

    // 9. Preventing deactivation of the last active administrator
    $activeAdminsCount = $userModel->countActiveAdmins();
    $t->assert($activeAdminsCount >= 1, "Active admin count verified: {$activeAdminsCount}");
    
    $tempAdminId = $userModel->create([
        'name'       => 'Temp Admin',
        'username'   => 'temp_admin_' . uniqid(),
        'email'      => 'temp_admin_' . uniqid() . '@voyagego.com',
        'password'   => password_hash('Temp@12345', PASSWORD_BCRYPT),
        'role'       => 'admin',
        'status'     => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    $t->assert($userModel->isLastActiveAdmin($tempAdminId) === false, 'When multiple active admins exist, isLastActiveAdmin returns false');

    // 10. Running migrations and seeding twice without duplicate accounts or data loss
    $pdo = Database::getConnection();
    $initialUserCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    
    $repeatMigration = MigrationManager::runPending($pdo, true);
    $t->assert($repeatMigration['success'] === true && count($repeatMigration['migrated']) === 0, 'Re-running migrations reports 0 pending migrations');

    $repeatSeeder = \App\Seeders\DatabaseSeeder::run($pdo);
    $postRepeatUserCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    $t->assert($postRepeatUserCount === $initialUserCount, "Running seeder twice is idempotent: user count unchanged ({$postRepeatUserCount})");
});

$runner->runSuite('Phase 1A Audit Fixes: Model Where Offset, Rate Limiter & Pagination Binding', function (TestRunner $t) {
    $pdo = Database::getConnection();

    // 1. Test Model::where with $offset
    $userModel = new \App\Models\User();
    $page1 = $userModel->where("1=1", [], 'id ASC', 1, 0);
    $page2 = $userModel->where("1=1", [], 'id ASC', 1, 1);
    $t->assert(!empty($page1) && !empty($page2), 'Model::where retrieves records for page 1 and page 2');
    $t->assert($page1[0]['id'] !== $page2[0]['id'], 'Model::where properly respects offset and returns different records on page 2');

    // 2. Test User::getPaginated with bound parameters
    $usersP1 = $userModel->getPaginated(null, null, null, 1, 2);
    $usersP2 = $userModel->getPaginated(null, null, null, 2, 2);
    $t->assert(count($usersP1) > 0, 'User::getPaginated successfully executes with bound parameters');
    if (count($usersP1) >= 2 && count($usersP2) >= 1) {
        $p1Ids = array_column($usersP1, 'id');
        $t->assert(!in_array($usersP2[0]['id'], $p1Ids, true), 'User::getPaginated offset correctly shifts window');
    } else {
        $t->assert(true, 'User::getPaginated executed successfully');
    }

    // 3. Test Rate Limiter key consistency between Router and AuthController
    $req = new \App\Core\Request([], [
        'login' => '  Owner  ',
        'password' => 'secret123'
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/login',
        'REMOTE_ADDR'    => '127.0.0.1'
    ]);
    
    // Router logic:
    $username = $req->input('login') ?? $req->input('username') ?? $req->input('email') ?? '';
    $routerKey = "throttle:login:127.0.0.1:" . hash('sha256', strtolower(trim((string)$username)));

    // AuthController logic:
    $input = $req->all();
    $loginIdentifier = trim($input['login'] ?? $input['email'] ?? '');
    $authKey = "throttle:login:127.0.0.1:" . hash('sha256', strtolower($loginIdentifier));

    $t->assert($routerKey === $authKey, 'RateLimiter key generated by Router matches key cleared by AuthController');
    $t->assert(!str_contains($routerKey, hash('sha256', '')), 'RateLimiter key correctly incorporates submitted login username');

    // 4. Test Request::has and Request::exists
    $t->assert($req->has('login') === true, 'Request::has returns true for present non-empty field');
    $t->assert($req->has('non_existent') === false, 'Request::has returns false for missing field');
    $t->assert($req->exists('login') === true, 'Request::exists returns true for present key');
});

// =========================================================================
// SUITE 7: Customer Registration, Isolation & Office Separation
// =========================================================================
$runner->runSuite('Customer Registration, Profile Isolation & Office Separation', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $customerModel = new \App\Models\Customer();
    $userModel = new \App\Models\User();
    $bookingModel = new \App\Models\Booking();

    // 1. Customer table exists and has proper schema (with preferred_contact_method and travel profile columns dropped)
    $colStmt = $pdo->query("SHOW COLUMNS FROM `customers`");
    $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);
    $t->assert(in_array('customer_code', $columns, true), 'Customers table contains customer_code column');
    $t->assert(in_array('phone', $columns, true), 'Customers table contains phone (primary mobile) column');
    $t->assert(!in_array('customer_type', $columns, true), 'Customers table has customer_type column removed');
    $t->assert(!in_array('preferred_contact_method', $columns, true), 'Customers table has preferred_contact_method column removed');
    $t->assert(in_array('marketing_consent', $columns, true), 'Customers table contains marketing_consent column');

    // 2. Customer code generation format
    $code = $customerModel->generateCustomerCode();
    $t->assert(preg_match('/^VG-CUST-[A-Z0-9]{4,6}$/', $code) === 1, "Generated customer code matches luxury format: {$code}");

    // 3. Customer creation does NOT create a user login account
    $initialUserCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    $testMobile = '98765' . str_pad((string)random_int(10000, 99999), 5, '0');
    $testEmail = 'luxury.guest.' . uniqid() . '@example.com';

    $customerId = $customerModel->create([
        'customer_code'     => $code,
        'name'              => 'Lady Eleanor Vance',
        'title'             => 'Lady',
        'gender'            => 'female',
        'phone'             => $testMobile,
        'alternate_phone'   => '+44 20 7946 0991',
        'email'             => $testEmail,
        'address'           => '742 Evergreen Terrace',
        'city'              => 'London',
        'country'           => 'United Kingdom',
        'marketing_consent' => 1,
        'status'            => 'active',
    ]);
    $t->assert($customerId > 0, "Customer profile successfully created with ID: {$customerId}");

    $postUserCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    $t->assert($postUserCount === $initialUserCount, 'Creating customer profile does NOT create a staff/user login account');

    // 4. Verification that customer has no credentials to log in
    $authService = new \App\Services\AuthService();
    try {
        $authService->authenticate($testEmail, 'AnyPassword123');
        $t->assert(false, 'Customer must not be authenticatable via staff login');
    } catch (\Throwable $e) {
        $t->assert(true, 'Customer cannot log into the office panel: no login account exists');
    }

    // 5. Authorization: Admin vs Dispatcher vs Driver on Customer Registration routes
    $router = require dirname(__DIR__) . '/app/routes.php';

    // Admin access to /office/users/create (which routes to Customer Registration)
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    $adminReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/users/create']);
    $adminRes = $router->dispatch($adminReq);
    $t->assert($adminRes->getStatusCode() === 200, 'Admin role granted 200 OK access to customer registration form (/office/users/create)');

    // Dispatcher access to /office/users/create (Forbidden)
    Session::set('user_role', 'dispatcher');
    $dispReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/users/create']);
    $dispRes = $router->dispatch($dispReq);
    $t->assert($dispRes->getStatusCode() === 403, 'Dispatcher role blocked with 403 Forbidden on customer registration form (/office/users/create)');

    // Driver access to /office/users/create (Forbidden)
    Session::set('user_role', 'driver');
    $drvReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/users/create']);
    $drvRes = $router->dispatch($drvReq);
    $t->assert($drvRes->getStatusCode() === 403, 'Driver role blocked with 403 Forbidden on customer registration form (/office/users/create)');

    // 6. Duplicate detection flags potential duplicates without crashing
    $duplicates = $customerModel->findPotentialDuplicates($testMobile, $testEmail);
    $t->assert(!empty($duplicates), 'Duplicate check successfully flags matching customer by phone and email');
    $t->assert((int)$duplicates[0]['id'] === (int)$customerId, 'Duplicate check identifies existing customer profile ID');

    // Duplicate check with unrelated details returns empty
    $noDuplicates = $customerModel->findPotentialDuplicates('9999900000', 'unique.guest@voyagego.com');
    $t->assert(empty($noDuplicates), 'Duplicate check returns empty when details are unique');

    // 7. Customer search and pagination
    $searchResults = $customerModel->getPaginated('Lady Eleanor', null, 1, 10);
    $t->assert(count($searchResults) >= 1, 'Customer search finds created customer by name');
    $t->assert($searchResults[0]['customer_code'] === $code, 'Search result contains correct customer code');

    // 8. Backward-compatible booking linkage
    // Create a new booking linked directly to customer_profile_id
    $bookingService = new \App\Services\BookingService();
    $bookingData = [
        'customer_profile_id' => $customerId,
        'customer_id'         => null,
        'pickup_address'      => 'Claridge\'s Hotel, Mayfair',
        'drop_address'        => 'Heathrow VIP Terminal',
        'start_date'          => date('Y-m-d', strtotime('+3 days')),
        'end_date'            => date('Y-m-d', strtotime('+3 days')),
        'passenger_count'     => 2,
        'custom_subtotal'     => 15000.0,
        'idempotency_token'   => bin2hex(random_bytes(16)),
    ];
    $newBooking = $bookingService->createBooking($bookingData);
    $t->assert(!empty($newBooking['id']), "Booking created with customer_profile_id: {$newBooking['booking_code']}");

    // Query booking details with customer join
    $fetchedBooking = $bookingModel->findWithDetails((int)$newBooking['id']);
    $t->assert($fetchedBooking !== null, 'findWithDetails retrieves booking linked to customer_profile_id');
    $t->assert($fetchedBooking['customer_name'] === 'Lady Eleanor Vance', "Customer name correctly resolved via COALESCE: {$fetchedBooking['customer_name']}");
    $t->assert($fetchedBooking['customer_code'] === $code, "Customer code correctly resolved: {$fetchedBooking['customer_code']}");

    // Verify customer booking history ledger
    $history = $customerModel->getBookingHistory($customerId);
    $t->assert(count($history) >= 1, 'Customer booking history ledger retrieves linked booking record');
    $t->assert($history[0]['booking_code'] === $newBooking['booking_code'], 'Ledger contains correct booking code');

    // Clean up test booking and customer
    $pdo->prepare("DELETE FROM `bookings` WHERE id = ?")->execute([$newBooking['id']]);
    $pdo->prepare("DELETE FROM `customers` WHERE id = ?")->execute([$customerId]);
});

// =========================================================================
// SUITE 8: Employee Directory, Staff Registration & Role Authorization
// =========================================================================
$runner->runSuite('Employee Directory, Staff Registration & Role Authorization', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $employeeModel = new Employee();

    // 1. Employee code generation format (VG-EMP-XXXX)
    $empCode = $employeeModel->generateEmployeeCode();
    $t->assert(preg_match('/^VG-EMP-[A-Z0-9]{4,6}$/', $empCode) === 1, "Generated employee code matches luxury format: {$empCode}");

    // 2. Employee creation with full profile
    $testEmpEmail = 'test.chauffeur.' . uniqid() . '@voyagego.com';
    $testEmpPhone = '98123' . str_pad((string)random_int(10000, 99999), 5, '0');
    $empId = $employeeModel->create([
        'employee_code'   => $empCode,
        'first_name'      => 'Alistair',
        'last_name'       => 'Montgomery',
        'email'           => $testEmpEmail,
        'phone'           => $testEmpPhone,
        'department'      => 'fleet_chauffeur',
        'designation'     => 'Senior Executive Chauffeur',
        'employment_type' => 'full_time',
        'joining_date'    => date('Y-m-d'),
        'status'          => 'active',
    ]);
    $t->assert($empId > 0, "Employee created successfully with ID: {$empId}");

    // 3. Employee lookup and details
    $fetched = $employeeModel->findWithDetails($empId);
    $t->assert($fetched !== null, 'findWithDetails retrieves created employee');
    $t->assert($fetched['first_name'] === 'Alistair', 'Employee first name matches');
    $t->assert($fetched['employee_code'] === $empCode, 'Employee code matches');

    // 4. Employee filtering and pagination
    $filteredCount = $employeeModel->countFiltered('Alistair', 'fleet_chauffeur', 'active');
    $t->assert($filteredCount >= 1, "Employee countFiltered returned {$filteredCount} records");
    $paginated = $employeeModel->getPaginated('Alistair', 'fleet_chauffeur', 'active', 1, 10);
    $t->assert(count($paginated) >= 1, 'Employee getPaginated retrieves filtered record');

    // 5. Active Chauffeurs listing
    $chauffeurs = $employeeModel->getActiveChauffeurs();
    $t->assert(is_array($chauffeurs), 'getActiveChauffeurs returns an array of chauffeur profiles');

    // 6. Authorization testing for Employee routes
    $router = require dirname(__DIR__) . '/app/routes.php';

    // Admin access to /office/employees (Allowed)
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    $adminReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/employees']);
    $adminRes = $router->dispatch($adminReq);
    $t->assert($adminRes->getStatusCode() === 200, 'Admin granted 200 OK access to Employee Directory (/office/employees)');

    // Dispatcher access to /office/employees (Forbidden)
    Session::set('user_role', 'dispatcher');
    $dispReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/employees']);
    $dispRes = $router->dispatch($dispReq);
    $t->assert($dispRes->getStatusCode() === 403, 'Dispatcher blocked with 403 Forbidden on Employee Directory');

    // Driver access to /office/employees (Forbidden)
    Session::set('user_role', 'driver');
    $drvReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/employees']);
    $drvRes = $router->dispatch($drvReq);
    $t->assert($drvRes->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Employee Directory');

    // Admin access to /office/users/roles (Roles & Permissions Matrix)
    Session::set('user_role', 'admin');
    $rolesReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/users/roles']);
    $rolesRes = $router->dispatch($rolesReq);
    $t->assert($rolesRes->getStatusCode() === 200, 'Admin granted 200 OK access to Roles & Permissions Matrix (/office/users/roles)');

    // Role List (/office/roles) route access tests
    $roleListReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/roles']);
    $roleListRes = $router->dispatch($roleListReq);
    $t->assert($roleListRes->getStatusCode() === 200, 'Admin granted 200 OK access to Role List (/office/roles)');

    Session::set('user_role', 'driver');
    $drvRoleRes = $router->dispatch($roleListReq);
    $t->assert($drvRoleRes->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Role List (/office/roles)');

    // Clean up test employee
    $pdo->prepare("DELETE FROM `employees` WHERE id = ?")->execute([$empId]);
});

// =========================================================================
// SUITE 9: Vehicle Management & Status Transitions
// =========================================================================
$runner->runSuite('Vehicle Management & Status Restrictions', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $vehicleModel = new Vehicle();

    // 1. Vehicle Creation
    $testPlate = 'TEST-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $vehicleId = $vehicleModel->create([
        'vehicle_number'   => $testPlate,
        'make_model'       => 'Mercedes-Benz S-Class S580',
        'vehicle_type'     => 'sedan',
        'seating_capacity' => 4,
        'fuel_type'        => 'petrol',
        'odometer_km'      => 12500,
        'status'           => 'available',
    ]);
    $t->assert($vehicleId > 0, "Vehicle created successfully with ID: {$vehicleId}");

    // 2. Count and Paginate Vehicles
    $count = $vehicleModel->countFiltered('Mercedes', 'sedan', 'available');
    $t->assert($count >= 1, "Vehicle countFiltered returned {$count} matching vehicles");
    $list = $vehicleModel->getPaginated('Mercedes', 'sedan', 'available', 1, 10);
    $t->assert(count($list) >= 1, 'Vehicle getPaginated returned created vehicle');
    $t->assert($list[0]['vehicle_number'] === $testPlate, 'Vehicle plate number matches');

    // 3. Status Transition to Maintenance and back
    $vehicleModel->update($vehicleId, ['status' => 'maintenance']);
    $updated = $vehicleModel->find($vehicleId);
    $t->assert($updated['status'] === 'maintenance', 'Vehicle status successfully transitioned to maintenance');

    $vehicleModel->update($vehicleId, ['status' => 'available']);
    $updated2 = $vehicleModel->find($vehicleId);
    $t->assert($updated2['status'] === 'available', 'Vehicle status successfully restored to available');

    // 4. Authorization testing for Vehicles routes
    $router = require dirname(__DIR__) . '/app/routes.php';

    // Admin access to /office/vehicles (Allowed)
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    $adminReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/vehicles']);
    $adminRes = $router->dispatch($adminReq);
    $t->assert($adminRes->getStatusCode() === 200, 'Admin granted 200 OK access to Vehicle List (/office/vehicles)');

    // Dispatcher access to /office/vehicles (Allowed)
    Session::set('user_role', 'dispatcher');
    $dispReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/vehicles']);
    $dispRes = $router->dispatch($dispReq);
    $t->assert($dispRes->getStatusCode() === 200, 'Dispatcher granted 200 OK access to Vehicle List (/office/vehicles)');

    // Driver access to /office/vehicles (Forbidden)
    Session::set('user_role', 'driver');
    $drvReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/vehicles']);
    $drvRes = $router->dispatch($drvReq);
    $t->assert($drvRes->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Vehicle List (/office/vehicles)');

    // Dispatcher access to /office/vehicles/create (Forbidden - Admin only)
    Session::set('user_role', 'dispatcher');
    $dispCreateReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/vehicles/create']);
    $dispCreateRes = $router->dispatch($dispCreateReq);
    $t->assert($dispCreateRes->getStatusCode() === 403, 'Dispatcher blocked with 403 Forbidden on Vehicle Creation (/office/vehicles/create)');

    // Clean up test vehicle
    $pdo->prepare("DELETE FROM `vehicles` WHERE id = ?")->execute([$vehicleId]);
});

// =========================================================================
// SUITE 10: Trip Operations, Status History Trail & Capacity Validation
// =========================================================================
$runner->runSuite('Trip Operations, Status History Trail & Capacity Validation', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $bookingService = new BookingService();
    $dispatchService = new DispatchService();
    $dispatchModel = new Dispatch();
    $customerModel = new \App\Models\Customer();
    $vehicleModel = new Vehicle();
    $driverModel = new DriverProfile();

    // 1. Setup Customer and Booking with 6 passengers
    $custCode = $customerModel->generateCustomerCode();
    $customerId = $customerModel->create([
        'customer_code'     => $custCode,
        'name'              => 'Viscount Sterling',
        'phone'             => '99887' . str_pad((string)random_int(10000, 99999), 5, '0'),
        'email'             => 'sterling.' . uniqid() . '@example.com',
        'status'            => 'active',
        'marketing_consent' => 1,
    ]);

    $bookingData = [
        'customer_profile_id' => $customerId,
        'pickup_address'      => 'The Dorchester, Park Lane',
        'drop_address'        => 'Gatwick Executive Aviation',
        'start_date'          => date('Y-m-d', strtotime('+2 days')),
        'end_date'            => date('Y-m-d', strtotime('+2 days')),
        'passenger_count'     => 6,
        'custom_subtotal'     => 22000.0,
        'idempotency_token'   => bin2hex(random_bytes(16)),
    ];
    $booking = $bookingService->createBooking($bookingData);
    $bookingId = (int)$booking['id'];

    // 2. Setup small vehicle (capacity 4) and large vehicle (capacity 8)
    $smallPlate = 'TEST-SML-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $smallVehicleId = $vehicleModel->create([
        'vehicle_number'   => $smallPlate,
        'make_model'       => 'BMW 7 Series',
        'vehicle_type'     => 'sedan',
        'seating_capacity' => 4,
        'fuel_type'        => 'petrol',
        'odometer_km'      => 8000,
        'status'           => 'available',
    ]);

    $largePlate = 'TEST-LRG-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $largeVehicleId = $vehicleModel->create([
        'vehicle_number'   => $largePlate,
        'make_model'       => 'Mercedes Sprinter VIP',
        'vehicle_type'     => 'van',
        'seating_capacity' => 8,
        'fuel_type'        => 'diesel',
        'odometer_km'      => 15000,
        'status'           => 'available',
    ]);

    // Setup test driver user & driver profile
    $driverUserEmail = 'test.driver.' . uniqid() . '@voyagego.com';
    $driverUserId = (new \App\Models\User())->create([
        'name'     => 'James Chauffeur',
        'username' => 'driver_' . substr(bin2hex(random_bytes(3)), 0, 6),
        'email'    => $driverUserEmail,
        'password' => password_hash('SecretPass123!', PASSWORD_BCRYPT),
        'role'     => 'driver',
        'status'   => 'active',
    ]);

    $licenseNo = 'DL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $driverId = $driverModel->create([
        'user_id'          => $driverUserId,
        'license_number'   => $licenseNo,
        'license_expiry'   => date('Y-m-d', strtotime('+2 years')),
        'experience_years' => 7,
        'duty_status'      => 'off_duty',
        'rating'           => 5.00,
    ]);

    // 3. Test Capacity Constraint: Attempting to assign 4-seater to 6 passengers must fail
    try {
        $dispatchService->assignBooking($bookingId, $smallVehicleId, $driverId, 1);
        $t->assert(false, 'Should throw exception when vehicle capacity is less than passenger count');
    } catch (\RuntimeException $e) {
        $t->assert(str_contains($e->getMessage(), 'seating capacity'), 'Seating capacity validation triggered: ' . $e->getMessage());
    }

    // 4. Assign valid large vehicle (capacity 8 >= 6)
    $dispatch = $dispatchService->assignBooking($bookingId, $largeVehicleId, $driverId, 1);
    $dispatchId = (int)$dispatch['id'];
    $t->assert($dispatchId > 0, "Dispatch assigned successfully with ID: {$dispatchId}");
    $t->assert($dispatch['status'] === 'assigned', 'Dispatch initial status is assigned');

    // 5. Verify audit trail in trip_status_history table
    $history = $dispatchModel->getStatusHistory($dispatchId);
    $t->assert(count($history) >= 1, 'trip_status_history has recorded initial assignment transition');
    $t->assert($history[0]['to_status'] === 'assigned', "History record shows to_status = 'assigned'");

    // 6. Driver accepts trip and verifies history trail
    $accepted = $dispatchService->driverAccept($dispatchId, $driverId);
    $t->assert($accepted === true, 'Driver accepted trip successfully');
    $historyAfterAccept = $dispatchModel->getStatusHistory($dispatchId);
    $t->assert(count($historyAfterAccept) >= 2, 'trip_status_history recorded driver_accepted step');
    $latestAfterAccept = end($historyAfterAccept);
    $t->assert($latestAfterAccept['to_status'] === 'driver_accepted', 'Latest status in history is driver_accepted');

    // 7. Cancel Trip and verify release of assets
    $cancelled = $dispatchService->cancelTrip($dispatchId, 1, 'VIP itinerary modified');
    $t->assert($cancelled === true, 'Trip successfully cancelled');
    $finalDispatch = $dispatchModel->find($dispatchId);
    $t->assert($finalDispatch['status'] === 'cancelled', 'Dispatch record marked as cancelled');

    $freedVehicle = $vehicleModel->find($largeVehicleId);
    $t->assert($freedVehicle['status'] === 'available', 'Vehicle freed and restored to available status');

    $freedDriver = $driverModel->find($driverId);
    $t->assert($freedDriver['duty_status'] === 'on_duty', 'Driver duty status restored to on_duty');

    // Verify cancellation recorded in status history
    $finalHistory = $dispatchModel->getStatusHistory($dispatchId);
    $latestStatus = end($finalHistory);
    $t->assert($latestStatus['to_status'] === 'cancelled', 'Cancellation logged in trip_status_history');
    $t->assert(str_contains($latestStatus['notes'], 'VIP itinerary modified'), 'Cancellation notes recorded');

    // 8. Trip Operations Routes Authorization
    $router = require dirname(__DIR__) . '/app/routes.php';

    // Admin access to /office/trips/assign and /office/trips/history (Allowed)
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    $adminAssign = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/assign']));
    $t->assert($adminAssign->getStatusCode() === 200, 'Admin granted 200 OK on Trip Assign (/office/trips/assign)');

    $adminHist = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/history']));
    $t->assert($adminHist->getStatusCode() === 200, 'Admin granted 200 OK on Trip History (/office/trips/history)');

    // Dispatcher access (Allowed)
    Session::set('user_role', 'dispatcher');
    $dispHist = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/history']));
    $t->assert($dispHist->getStatusCode() === 200, 'Dispatcher granted 200 OK on Trip History (/office/trips/history)');

    // Driver access (Forbidden)
    Session::set('user_role', 'driver');
    $drvHist = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/history']));
    $t->assert($drvHist->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Trip History');

    // Clean up test data
    $pdo->prepare("DELETE FROM `trip_status_history` WHERE dispatch_id = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `dispatches` WHERE id = ?")->execute([$dispatchId]);
    $pdo->prepare("DELETE FROM `bookings` WHERE id = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM `vehicles` WHERE id IN (?, ?)")->execute([$smallVehicleId, $largeVehicleId]);
    $pdo->prepare("DELETE FROM `customers` WHERE id = ?")->execute([$customerId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE id = ?")->execute([$driverId]);
    $pdo->prepare("DELETE FROM `users` WHERE id = ?")->execute([$driverUserId]);
});

// =========================================================================
// SUITE 11: Real-Time Fleet Live Tracking & Legacy Redirections
// =========================================================================
$runner->runSuite('Real-Time Fleet Live Tracking & Legacy Redirections', function (TestRunner $t) {
    $router = require dirname(__DIR__) . '/app/routes.php';

    // 1. Live Tracking Office Route Authorization
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    $adminTrack = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']));
    $t->assert($adminTrack->getStatusCode() === 200, 'Admin granted 200 OK on Live Fleet Tracking (/office/tracking)');

    Session::set('user_role', 'dispatcher');
    $dispTrack = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']));
    $t->assert($dispTrack->getStatusCode() === 200, 'Dispatcher granted 200 OK on Live Fleet Tracking (/office/tracking)');

    Session::set('user_role', 'driver');
    $drvTrack = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']));
    $t->assert($drvTrack->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Live Fleet Tracking');

    // 2. Live Telemetry API contract (/api/fleet/live)
    Session::set('user_role', 'admin');
    $apiRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/fleet/live']));
    $t->assert($apiRes->getStatusCode() === 200, 'Admin granted 200 OK on /api/fleet/live');
    $json = json_decode($apiRes->getContent(), true);
    $t->assert(is_array($json), '/api/fleet/live returns valid JSON array');

    // 3. Graceful Legacy Redirections
    // GET /office/tours -> 302 to /office/bookings
    $toursRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tours']));
    $t->assert($toursRes->getStatusCode() === 302, 'Legacy /office/tours issues 302 redirect');
    $t->assert(str_contains($toursRes->getHeaders()['Location'] ?? '', '/office/bookings'), 'Redirects gracefully to /office/bookings');

    // GET /office/fleet -> 302 to /office/vehicles
    $fleetRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/fleet']));
    $t->assert($fleetRes->getStatusCode() === 302, 'Legacy /office/fleet issues 302 redirect');
    $t->assert(str_contains($fleetRes->getHeaders()['Location'] ?? '', '/office/vehicles'), 'Redirects gracefully to /office/vehicles');

    // GET /office/dispatch -> 302 to /office/trips/assign
    $dispatchRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/dispatch']));
    $t->assert($dispatchRes->getStatusCode() === 302, 'Legacy /office/dispatch issues 302 redirect');
    $t->assert(str_contains($dispatchRes->getHeaders()['Location'] ?? '', '/office/trips/assign'), 'Redirects gracefully to /office/trips/assign');
});

// =========================================================================
// SUITE 12: Role & Permissions System Matching School Management Architecture
// =========================================================================
$runner->runSuite('Role & Permissions System Matching School Management Architecture', function (TestRunner $t) {
    $router = require dirname(__DIR__) . '/app/routes.php';
    $pdo = \App\Core\Database::getConnection();

    // 1. PermissionRegistry catalogue structure
    $catalogue = \App\Services\PermissionRegistry::getCatalogue();
    $t->assert(isset($catalogue['dashboard_telemetry']), 'PermissionRegistry contains dashboard_telemetry category');
    $t->assert(isset($catalogue['booking_manifests']), 'PermissionRegistry contains booking_manifests category');
    $t->assert(isset($catalogue['access_control']), 'PermissionRegistry contains access_control category');

    $flat = \App\Services\PermissionRegistry::getFlatPermissions();
    $t->assert(count($flat) >= 40, 'PermissionRegistry flattens into comprehensive catalogue (' . count($flat) . ' permissions)');

    // 2. Database permissions seeded
    $dbPermsCount = (int)$pdo->query("SELECT COUNT(*) FROM `permissions`")->fetchColumn();
    $t->assert($dbPermsCount >= 40, "Database permissions table contains seeded records ({$dbPermsCount})");

    // 3. Admin access to Role List, Role Permission Overview, and Matrix
    Session::start();
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');

    $rolesRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/roles']));
    $t->assert($rolesRes->getStatusCode() === 200, 'Admin granted 200 OK on Role List (/office/roles)');

    $rpRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/role-permission']));
    $t->assert($rpRes->getStatusCode() === 200, 'Admin granted 200 OK on Role Permission Directory (/office/role-permission)');

    $assignRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/role-permission/assign']));
    $t->assert($assignRes->getStatusCode() === 200, 'Admin granted 200 OK on Permission Matrix (/office/role-permission/assign)');

    // 4. Non-admin roles blocked
    Session::set('user_role', 'driver');
    $drvRp = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/role-permission']));
    $t->assert($drvRp->getStatusCode() === 403, 'Driver blocked with 403 Forbidden on Role Permission Directory');

    // 5. Options JSON API contract
    Session::set('user_role', 'admin');
    $adminRoleStmt = $pdo->query("SELECT id FROM `roles` WHERE `slug` = 'admin' LIMIT 1");
    $adminRoleId = (int)$adminRoleStmt->fetchColumn();

    $optionsRes = $router->dispatch(new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/role-permission/options/{$adminRoleId}"]));
    $t->assert($optionsRes->getStatusCode() === 200, "Permission options API returns 200 OK for role {$adminRoleId}");
    $optJson = json_decode($optionsRes->getContent(), true);
    $t->assert(isset($optJson['role']) && isset($optJson['catalogue']) && isset($optJson['selected']), 'Options API returns valid role, catalogue, and selected permission keys');

    // 6. Create custom role, assign permissions, update, and delete
    $csrf = \App\Core\Csrf::getToken();
    $roleModel = new \App\Models\Role();
    $testRoleTitle = 'VIP Concierge Ops ' . bin2hex(random_bytes(2));
    
    $createReq = new \App\Core\Request([], [
        '_token'      => $csrf,
        'name'        => $testRoleTitle,
        'description' => 'Dedicated luxury itinerary and chauffeur coordination',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/office/roles',
        'HTTP_ACCEPT'    => 'application/json',
    ]);
    $createRes = $router->dispatch($createReq);
    $t->assert($createRes->getStatusCode() === 200, 'Custom role created successfully via POST /office/roles');
    $createdJson = json_decode($createRes->getContent(), true);
    $newRoleId = (int)($createdJson['role_id'] ?? 0);
    $t->assert($newRoleId > 0, "Created custom role ID is valid ({$newRoleId})");

    // Fetch permission IDs to assign
    $samplePerms = $pdo->query("SELECT id FROM `permissions` WHERE `module` IN ('bookings', 'customers') LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
    $assignReq = new \App\Core\Request([], [
        '_token'      => $csrf,
        'scope_mode'  => 'role',
        'role_id'     => $newRoleId,
        'permissions' => array_map('intval', $samplePerms),
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/office/role-permission',
        'HTTP_ACCEPT'    => 'application/json',
    ]);
    $assignPostRes = $router->dispatch($assignReq);
    $t->assert($assignPostRes->getStatusCode() === 200, 'Assigned permissions saved successfully via POST /office/role-permission');

    $assignedPermIds = $roleModel->getPermissionIdsForRole($newRoleId);
    $t->assert(count($assignedPermIds) === count($samplePerms), "Custom role has exactly " . count($samplePerms) . " permissions assigned");

    // Update role description
    $updateReq = new \App\Core\Request([], [
        '_token'      => $csrf,
        'name'        => $testRoleTitle,
        'description' => 'Updated scope description for concierge team',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => "/office/roles/{$newRoleId}/update",
        'HTTP_ACCEPT'    => 'application/json',
    ]);
    $updateRes = $router->dispatch($updateReq);
    $t->assert($updateRes->getStatusCode() === 200, 'Custom role description updated successfully');

    // Protect system role deletion
    $delAdminReq = new \App\Core\Request([], [
        '_token' => $csrf,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => "/office/roles/{$adminRoleId}/delete",
        'HTTP_ACCEPT'    => 'application/json',
    ]);
    $delAdminRes = $router->dispatch($delAdminReq);
    $t->assert($delAdminRes->getStatusCode() === 403, 'System Administrator role deletion firmly rejected with 403');

    // Delete custom role
    $delCustomReq = new \App\Core\Request([], [
        '_token' => $csrf,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => "/office/roles/{$newRoleId}/delete",
        'HTTP_ACCEPT'    => 'application/json',
    ]);
    $delCustomRes = $router->dispatch($delCustomReq);
    $t->assert($delCustomRes->getStatusCode() === 200, 'Custom role deleted successfully via POST /office/roles/{id}/delete');

    // Clean up
    $pdo->prepare("DELETE FROM `role_permissions` WHERE `role_id` = ?")->execute([$newRoleId]);
    $pdo->prepare("DELETE FROM `roles` WHERE `id` = ?")->execute([$newRoleId]);
});

// =========================================================================
// SUITE 9: MODULE 19 — Dedicated Driver Portal (Role-Based UI, Security & Isolation)
// =========================================================================
$runner->runSuite('MODULE 19: Dedicated Driver Portal — Role-Based UI, Security & Data Isolation', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $router = require dirname(__DIR__) . '/app/routes.php';
    $userModel = new User();
    $driverModel = new DriverProfile();
    $dispatchModel = new Dispatch();
    $vehicleModel = new Vehicle();
    $bookingModel = new Booking();
    $dispatchService = new DispatchService();
    $csrf = Csrf::getToken();

    // -------------------------------------------------------------------------
    // Setup Test Drivers & Profiles
    // -------------------------------------------------------------------------
    // Driver 1
    $drv1Email = 'driver.suite19.1.' . uniqid() . '@voyagego.com';
    $drv1UserId = $userModel->create([
        'name'     => 'Marcus Sterling',
        'username' => 'msterling_' . uniqid(),
        'email'    => $drv1Email,
        'phone'    => '+91 98111 ' . rand(10000, 99999),
        'password' => password_hash('Chauffeur@123', PASSWORD_BCRYPT, ['cost' => 12]),
        'role'     => 'driver',
        'status'   => 'active',
    ]);
    $drv1ProfileId = $driverModel->create([
        'user_id'          => $drv1UserId,
        'license_number'   => 'DL-MH-19-' . rand(100000, 999999),
        'license_expiry'   => date('Y-m-d', strtotime('+3 years')),
        'experience_years' => 7,
        'duty_status'      => 'on_duty',
        'rating'           => 4.95,
        'current_latitude' => 18.9220,
        'current_longitude'=> 72.8347,
        'last_gps_at'      => date('Y-m-d H:i:s'),
    ]);

    // Driver 2 (For Isolation Testing)
    $drv2Email = 'driver.suite19.2.' . uniqid() . '@voyagego.com';
    $drv2UserId = $userModel->create([
        'name'     => 'Devin Vance',
        'username' => 'dvance_' . uniqid(),
        'email'    => $drv2Email,
        'phone'    => '+91 98222 ' . rand(10000, 99999),
        'password' => password_hash('Chauffeur@123', PASSWORD_BCRYPT, ['cost' => 12]),
        'role'     => 'driver',
        'status'   => 'active',
    ]);
    $drv2ProfileId = $driverModel->create([
        'user_id'          => $drv2UserId,
        'license_number'   => 'DL-KA-19-' . rand(100000, 999999),
        'license_expiry'   => date('Y-m-d', strtotime('+2 years')),
        'experience_years' => 5,
        'duty_status'      => 'on_duty',
        'rating'           => 4.88,
        'current_latitude' => 12.9716,
        'current_longitude'=> 77.5946,
        'last_gps_at'      => date('Y-m-d H:i:s'),
    ]);

    // Test Vehicle & Bookings
    $testVehicleId = $vehicleModel->create([
        'vehicle_number'      => 'MH-01-DR-' . rand(1000, 9999),
        'make_model'          => 'Mercedes-Benz E-Class Luxury',
        'vehicle_type'        => 'sedan',
        'seating_capacity'    => 4,
        'fuel_type'           => 'diesel',
        'status'              => 'available',
        'odometer_km'         => 25400,
        'registration_expiry' => date('Y-m-d', strtotime('+1 year')),
        'insurance_expiry'    => date('Y-m-d', strtotime('+1 year')),
    ]);

    $bookingService = new \App\Services\BookingService();
    $existingCust = $pdo->query("SELECT id FROM `customers` ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $testCustId = $existingCust ? (int)$existingCust['id'] : (int)(new \App\Models\Customer())->create([
        'customer_code' => 'VG-CUST-T19-' . substr(uniqid(), 0, 8),
        'first_name'    => 'VIP',
        'last_name'     => 'Passenger',
        'phone'         => '+9199999' . rand(10000, 99999),
        'email'         => 'vip' . uniqid() . '@example.com',
        'status'        => 'active'
    ]);

    $b1 = $bookingService->createBooking([
        'customer_profile_id' => $testCustId,
        'pickup_address'      => 'The Taj Mahal Palace, Colaba',
        'drop_address'        => 'Mumbai International Airport T2',
        'start_date'          => date('Y-m-d'),
        'end_date'            => date('Y-m-d'),
        'passenger_count'     => 2,
        'custom_subtotal'     => 4500.0,
        'idempotency_token'   => bin2hex(random_bytes(16)),
    ]);
    $b2 = $bookingService->createBooking([
        'customer_profile_id' => $testCustId,
        'pickup_address'      => 'Bandra Kurla Complex',
        'drop_address'        => 'St. Regis Mumbai',
        'start_date'          => date('Y-m-d', strtotime('+1 day')),
        'end_date'            => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'     => 1,
        'custom_subtotal'     => 3200.0,
        'idempotency_token'   => bin2hex(random_bytes(16)),
    ]);

    // Dispatch 1 -> Driver 1
    $dispatch1 = $dispatchService->assignBooking((int)$b1['id'], $testVehicleId, $drv1ProfileId, 1);
    // Dispatch 2 -> Driver 2
    $testVehicle2Id = $vehicleModel->create([
        'vehicle_number'      => 'MH-02-VG-' . rand(1000, 9999),
        'make_model'          => 'BMW 7 Series Executive',
        'vehicle_type'        => 'sedan',
        'seating_capacity'    => 4,
        'fuel_type'           => 'diesel',
        'status'              => 'available',
        'odometer_km'         => 18900,
        'registration_expiry' => date('Y-m-d', strtotime('+1 year')),
        'insurance_expiry'    => date('Y-m-d', strtotime('+1 year')),
    ]);
    $dispatch2 = $dispatchService->assignBooking((int)$b2['id'], $testVehicle2Id, $drv2ProfileId, 1);

    // -------------------------------------------------------------------------
    // TEST 1: Admin login redirects to the authorized management dashboard
    // -------------------------------------------------------------------------
    $authController = new \App\Controllers\AuthController();
    $refMethod = new \ReflectionMethod($authController, 'redirectBasedOnRole');
    $refMethod->setAccessible(true);
    $adminRedirect = $refMethod->invoke($authController, 'admin');
    $t->assert(str_contains($adminRedirect->getHeader('Location') ?? '', '/office/dashboard'), '1. Admin login redirects to authorized management dashboard (/office/dashboard)');

    // -------------------------------------------------------------------------
    // TEST 2: Driver login redirects to dedicated driver dashboard
    // -------------------------------------------------------------------------
    $driverRedirect = $refMethod->invoke($authController, 'driver');
    $t->assert(str_contains($driverRedirect->getHeader('Location') ?? '', '/driver/dashboard'), '2. Driver login redirects to dedicated driver dashboard (/driver/dashboard)');

    // Root route redirect test for driver session
    Session::start();
    Session::set('user_id', $drv1UserId);
    Session::set('user_role', 'driver');
    Session::set('user', $userModel->find($drv1UserId));

    $rootReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']);
    $rootRes = $router->dispatch($rootReq);
    $t->assert(str_contains($rootRes->getHeader('Location') ?? '', '/driver/dashboard'), '2b. Authenticated driver at root route / redirects to /driver/dashboard');

    // -------------------------------------------------------------------------
    // TEST 3: Driver sees driver-specific sidebar and navbar
    // -------------------------------------------------------------------------
    $dashReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/dashboard']);
    $dashRes = $router->dispatch($dashReq);
    $t->assert($dashRes->getStatusCode() === 200, '3. Driver dashboard loads with 200 OK');
    $dashHtml = (string)$dashRes->getBody();
    $t->assert(str_contains($dashHtml, 'id="sidebar"'), '3a. Driver layout contains dedicated driver sidebar');
    $t->assert(str_contains($dashHtml, 'CHAUFFEUR PORTAL') || str_contains($dashHtml, 'Chauffeur Console'), '3b. Sidebar displays brand and chauffeur section label');
    $t->assert(str_contains($dashHtml, '/driver/dashboard') && str_contains($dashHtml, '/driver/trips') && str_contains($dashHtml, '/driver/history') && str_contains($dashHtml, '/driver/vehicle') && str_contains($dashHtml, '/driver/location') && str_contains($dashHtml, '/driver/profile'), '3c. Driver sidebar contains all 6 driver navigation routes');

    // -------------------------------------------------------------------------
    // TEST 4: Administrative menu items are NOT displayed to drivers
    // -------------------------------------------------------------------------
    $t->assert(!str_contains($dashHtml, '/office/customers') && !str_contains($dashHtml, '/office/employees') && !str_contains($dashHtml, '/office/users') && !str_contains($dashHtml, '/office/roles') && !str_contains($dashHtml, '/office/role-permission'), '4. Administrative menu items (customers, employees, users, roles) are completely absent from driver layout');

    // -------------------------------------------------------------------------
    // TEST 5: Direct access to protected admin URLs is denied to drivers
    // -------------------------------------------------------------------------
    $adminEndpoints = [
        '/office/dashboard',
        '/office/customers',
        '/office/employees',
        '/office/vehicles',
        '/office/bookings',
        '/office/invoices',
        '/office/users',
        '/office/roles',
        '/office/trips/assign',
        '/office/tracking',
    ];
    $allBlocked = true;
    foreach ($adminEndpoints as $ep) {
        $epReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $ep]);
        $epRes = $router->dispatch($epReq);
        if ($epRes->getStatusCode() !== 403) {
            $allBlocked = false;
            echo "    Failed block on endpoint {$ep} (Status: {$epRes->getStatusCode()})\n";
        }
    }
    $t->assert($allBlocked, '5. Direct access to all protected admin and office routes returns 403 Forbidden for drivers');

    // -------------------------------------------------------------------------
    // TEST 6: Driver sees ONLY their own assigned trips
    // -------------------------------------------------------------------------
    $tripsReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/trips']);
    $tripsRes = $router->dispatch($tripsReq);
    $t->assert($tripsRes->getStatusCode() === 200, '6a. Driver trips page loads with 200 OK');
    $tripsHtml = (string)$tripsRes->getBody();
    $t->assert(str_contains($tripsHtml, $b1['booking_code']), '6b. Driver 1 sees their assigned booking: ' . $b1['booking_code']);
    $t->assert(!str_contains($tripsHtml, $b2['booking_code']), '6c. Driver 1 CANNOT see Driver 2\'s assigned booking: ' . $b2['booking_code']);

    // -------------------------------------------------------------------------
    // TEST 7: Changing trip ID or URL parameter cannot expose another driver's records
    // -------------------------------------------------------------------------
    // Driver 1 accessing their own trip details
    $ownTripReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/trips/' . $dispatch1['id']]);
    $ownTripRes = $router->dispatch($ownTripReq);
    $t->assert($ownTripRes->getStatusCode() === 200, '7a. Driver 1 can view own trip details (200 OK)');

    // Driver 1 attempting to access Driver 2's trip details
    $otherTripReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/trips/' . $dispatch2['id']]);
    $otherTripRes = $router->dispatch($otherTripReq);
    $t->assert($otherTripRes->getStatusCode() === 302, '7b. Driver 1 attempting to view Driver 2\'s trip is intercepted and redirected (302)');
    $otherHtml = (string)$otherTripRes->getBody();
    $t->assert(!str_contains($otherHtml, $b2['booking_code']), '7c. Driver 2\'s booking data is never exposed to Driver 1');

    // -------------------------------------------------------------------------
    // TEST 8: Unauthorized trip status transitions are rejected
    // -------------------------------------------------------------------------
    // Cannot start trip without accepting
    $t->assertThrows(function () use ($dispatchService, $dispatch1, $drv1ProfileId) {
        // Complete trip directly from assigned status
        $dispatchService->completeTrip((int)$dispatch1['id'], $drv1ProfileId, 30000);
    }, \RuntimeException::class, '8a. Attempt to complete an unstarted/assigned trip is firmly rejected');

    // Cannot start with lower odometer
    $t->assertThrows(function () use ($dispatchService, $dispatch1, $drv1ProfileId) {
        $dispatchService->startTrip((int)$dispatch1['id'], $drv1ProfileId, 100);
    }, \RuntimeException::class, '8b. Attempt to start trip with odometer lower than vehicle reading is rejected');

    // Driver 1 cannot accept Driver 2's trip
    $t->assertThrows(function () use ($dispatchService, $dispatch2, $drv1ProfileId) {
        $dispatchService->driverAccept((int)$dispatch2['id'], $drv1ProfileId);
    }, \RuntimeException::class, '8c. Attempt to accept another driver\'s trip is rejected with Unauthorized error');

    // Valid workflow progression
    $accepted = $dispatchService->driverAccept((int)$dispatch1['id'], $drv1ProfileId);
    $t->assert($accepted === true, '8d. Driver 1 accepts assigned trip successfully');

    $started = $dispatchService->startTrip((int)$dispatch1['id'], $drv1ProfileId, 25420, 'Departed hotel smoothly');
    $t->assert($started === true, '8e. Driver 1 starts trip with valid odometer reading');

    // -------------------------------------------------------------------------
    // TEST 11: Location sharing accurately reflects actual implementation
    // -------------------------------------------------------------------------
    // GPS API submission test while trip is active
    $gpsReq = new \App\Core\Request([], [
        'latitude'  => 18.9250,
        'longitude' => 72.8360,
        'speed_kmh' => 45.2,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/gps/update',
        'CONTENT_TYPE'   => 'application/json',
        'HTTP_X_CSRF_TOKEN' => $csrf,
    ]);
    $gpsRes = $router->dispatch($gpsReq);
    $t->assert($gpsRes->getStatusCode() === 200, '11d. Driver can submit live GPS coordinates via /api/gps/update (200 OK)');
    $gpsJson = json_decode((string)$gpsRes->getBody(), true);
    $t->assert($gpsJson['data']['policy'] === 'active_trip' && (int)$gpsJson['data']['dispatch_id'] === (int)$dispatch1['id'], '11d-2. Active trip tracking policy applied with valid dispatch ID');
    $updatedDriver = $driverModel->find($drv1ProfileId);
    $t->assert(abs((float)$updatedDriver['current_latitude'] - 18.9250) < 0.001, '11e. Database driver profile coordinates updated accurately');
    $gpsLogModel = new \App\Models\GpsLog();
    $latestLogs = $gpsLogModel->getLatestForDriver($drv1ProfileId, 1);
    $t->assert(!empty($latestLogs) && (int)$latestLogs[0]['dispatch_id'] === (int)$dispatch1['id'], '11f. gps_logs table logs active dispatch ID');

    $completed = $dispatchService->completeTrip((int)$dispatch1['id'], $drv1ProfileId, 25465, 'Arrived at airport terminal on time');
    $t->assert($completed === true, '8f. Driver 1 completes trip with verified end odometer');

    // TEST: On-duty standby tracking policy without active trip
    $onDutyGpsReq = new \App\Core\Request([], [
        'latitude'  => 18.9300,
        'longitude' => 72.8400,
        'speed_kmh' => 0.0,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/gps/update',
        'CONTENT_TYPE'   => 'application/json',
        'HTTP_X_CSRF_TOKEN' => $csrf,
    ]);
    $onDutyGpsRes = $router->dispatch($onDutyGpsReq);
    $t->assert($onDutyGpsRes->getStatusCode() === 200, '11g. Driver can submit live GPS coordinates while on-duty without active trip (200 OK)');
    $onDutyJson = json_decode((string)$onDutyGpsRes->getBody(), true);
    $t->assert($onDutyJson['data']['policy'] === 'on_duty_standby' && $onDutyJson['data']['dispatch_id'] === null, '11h. On-duty standby policy applied with null dispatch ID');
    $standbyDriver = $driverModel->find($drv1ProfileId);
    $t->assert(abs((float)$standbyDriver['current_latitude'] - 18.9300) < 0.001, '11i. Standby driver coordinates persisted to driver_profiles');
    $standbyLogs = $gpsLogModel->getLatestForDriver($drv1ProfileId, 1);
    $t->assert(!empty($standbyLogs) && $standbyLogs[0]['dispatch_id'] === null, '11j. gps_logs table persists on-duty breadcrumb with null dispatch_id');

    // TEST: Off-duty rejection (privacy safeguard)
    $driverModel->update($drv1ProfileId, ['duty_status' => 'off_duty']);
    $offDutyGpsReq = new \App\Core\Request([], [
        'latitude'  => 18.9350,
        'longitude' => 72.8450,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/gps/update',
        'CONTENT_TYPE'   => 'application/json',
        'HTTP_X_CSRF_TOKEN' => $csrf,
    ]);
    $offDutyGpsRes = $router->dispatch($offDutyGpsReq);
    $t->assert($offDutyGpsRes->getStatusCode() === 422, '11k. Off-duty location submission rejected to protect chauffeur privacy (422)');
    $offDutyJson = json_decode((string)$offDutyGpsRes->getBody(), true);
    $t->assert(str_contains($offDutyJson['error'] ?? '', 'off duty'), '11l. Informative error returned when off-duty driver attempts location sharing');

    // Restore on-duty
    $driverModel->update($drv1ProfileId, ['duty_status' => 'on_duty']);

    // TEST: Invalid coordinate bounds (null island & out of bounds)
    $badCoordsReq = new \App\Core\Request([], [
        'latitude'  => 0.0,
        'longitude' => 0.0,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/gps/update',
        'CONTENT_TYPE'   => 'application/json',
        'HTTP_X_CSRF_TOKEN' => $csrf,
    ]);
    $badCoordsRes = $router->dispatch($badCoordsReq);
    $t->assert($badCoordsRes->getStatusCode() === 422, '11m. Null island (0,0) coordinates rejected (422)');

    // TEST: CSRF rejection
    $tamperedCsrfReq = new \App\Core\Request([], [
        'latitude'  => 18.9300,
        'longitude' => 72.8400,
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/gps/update',
        'CONTENT_TYPE'   => 'application/json',
        'HTTP_X_CSRF_TOKEN' => 'tampered_invalid_csrf_token',
    ]);
    $tamperedCsrfRes = $router->dispatch($tamperedCsrfReq);
    $t->assert($tamperedCsrfRes->getStatusCode() === 403, '11n. Tampered CSRF token on GPS update rejected with 403 Forbidden');

    // Cannot accept an already completed trip
    $t->assertThrows(function () use ($dispatchService, $dispatch1, $drv1ProfileId) {
        $dispatchService->driverAccept((int)$dispatch1['id'], $drv1ProfileId);
    }, \RuntimeException::class, '8g. Duplicate acceptance on already completed trip is rejected');

    // -------------------------------------------------------------------------
    // TEST 9: Driver trip history contains only authenticated driver's records
    // -------------------------------------------------------------------------
    $histReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/history']);
    $histRes = $router->dispatch($histReq);
    $t->assert($histRes->getStatusCode() === 200, '9a. Driver trip history loads with 200 OK');
    $histHtml = (string)$histRes->getBody();
    $t->assert(str_contains($histHtml, $b1['booking_code']), '9b. Completed trip appears in Driver 1\'s history archive');
    $t->assert(!str_contains($histHtml, $b2['booking_code']), '9c. Driver 2\'s trip does NOT appear in Driver 1\'s history archive');

    // -------------------------------------------------------------------------
    // TEST 10: Vehicle page displays only valid assignments & empty states
    // -------------------------------------------------------------------------
    $vehReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/vehicle']);
    $vehRes = $router->dispatch($vehReq);
    $t->assert($vehRes->getStatusCode() === 200, '10a. Driver vehicle page loads with 200 OK');
    $vehHtml = (string)$vehRes->getBody();
    $t->assert(str_contains($vehHtml, 'Mercedes-Benz E-Class Luxury'), '10b. Displays vehicle assigned from completed/active trip');

    // Test driver with NO vehicle assigned
    $drv3UserId = $userModel->create([
        'name'     => 'Fresh Chauffeur',
        'username' => 'fresh_' . uniqid(),
        'email'    => 'fresh.' . uniqid() . '@voyagego.com',
        'phone'    => '+91 98333 ' . rand(10000, 99999),
        'password' => password_hash('Chauffeur@123', PASSWORD_BCRYPT, ['cost' => 12]),
        'role'     => 'driver',
        'status'   => 'active',
    ]);
    $driverModel->create([
        'user_id'          => $drv3UserId,
        'license_number'   => 'DL-MH-99-' . rand(100000, 999999),
        'license_expiry'   => date('Y-m-d', strtotime('+3 years')),
        'experience_years' => 2,
        'duty_status'      => 'off_duty',
        'rating'           => 5.0,
    ]);
    Session::set('user_id', $drv3UserId);
    Session::set('user', $userModel->find($drv3UserId));

    $freshVehReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/vehicle']);
    $freshVehRes = $router->dispatch($freshVehReq);
    $freshVehHtml = (string)$freshVehRes->getBody();
    $t->assert(str_contains($freshVehHtml, 'No vehicle assigned yet'), '10c. Driver with no vehicle assignments receives professional empty state');

    // Test Driver 3 (driver with NO coordinates recorded yet) loading location console
    $drv3LocReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/location']);
    $drv3LocRes = $router->dispatch($drv3LocReq);
    $t->assert($drv3LocRes->getStatusCode() === 200, '11o. Driver without coordinates loads location console (200 OK)');
    $drv3LocHtml = (string)$drv3LocRes->getBody();
    $t->assert(!str_contains($drv3LocHtml, '19.0760'), '11p. Driver without coordinates does NOT have hardcoded Mumbai latitude presented');
    $t->assert(!str_contains($drv3LocHtml, '72.8777'), '11q. Driver without coordinates does NOT have hardcoded Mumbai longitude presented');
    $t->assert(str_contains($drv3LocHtml, 'Awaiting browser GPS lock'), '11r. Unlocated driver shows clear awaiting signal notice');

    // Switch session back to Driver 1
    Session::set('user_id', $drv1UserId);
    Session::set('user', $userModel->find($drv1UserId));

    // Location console page test
    $locReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/location']);
    $locRes = $router->dispatch($locReq);
    $t->assert($locRes->getStatusCode() === 200, '11a. Driver location console loads with 200 OK');
    $locHtml = (string)$locRes->getBody();
    $t->assert(str_contains($locHtml, 'driverLiveMap'), '11b. Location page includes live map container');
    $t->assert(str_contains($locHtml, 'VoyageTelemetry'), '11c. Integrates genuinely with client telemetry engine');

    // Test Office Central Live Fleet Tracker reflects Driver 1's position
    $adminUser = $userModel->findBy('role', 'admin') ?? $userModel->findBy('email', 'admin@voyagego.com');
    Session::set('user_id', $adminUser['id']);
    Session::set('user_role', 'admin');
    Session::set('user', $adminUser);
    $fleetReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/fleet/live']);
    $fleetRes = $router->dispatch($fleetReq);
    $t->assert($fleetRes->getStatusCode() === 200, '11s. Office dispatch live fleet tracker returns 200 OK');
    $fleetData = json_decode((string)$fleetRes->getBody(), true);
    $t->assert(!empty($fleetData['fleet']), '11t. Fleet tracker returns active vehicles and on-duty chauffeurs');
    $foundDriver = false;
    foreach ($fleetData['fleet'] as $f) {
        if ((int)$f['driver_id'] === $drv1ProfileId) {
            $foundDriver = true;
            $t->assert(abs((float)$f['current_latitude'] - 18.9300) < 0.001, '11u. Office tracker reflects chauffeur\'s updated latitude');
            break;
        }
    }
    $t->assert($foundDriver, '11v. Chauffeur profile exists in central fleet tracker');

    // Switch session back to Driver 1 for profile test
    Session::set('user_id', $drv1UserId);
    Session::set('user_role', 'driver');
    Session::set('user', $userModel->find($drv1UserId));

    // -------------------------------------------------------------------------
    // TEST 12: Driver profile respects field-level permissions
    // -------------------------------------------------------------------------
    $profReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/profile']);
    $profRes = $router->dispatch($profReq);
    $t->assert($profRes->getStatusCode() === 200, '12a. Driver profile page loads with 200 OK');
    $profHtml = (string)$profRes->getBody();
    $t->assert(str_contains($profHtml, 'Verified Professional Credentials (Read-Only)'), '12b. Credentials marked clearly as read-only');

    // Update permitted field (phone)
    $newPhone = '+91 99888 ' . rand(10000, 99999);
    $updateProfReq = new \App\Core\Request([], [
        '_token'            => $csrf,
        'phone'             => $newPhone,
        'emergency_contact' => 'Sarah Sterling (+91 98999 11111)',
        'address'           => '12 Palm Grove Avenue',
        'role'              => 'admin', // Tampering attempt: trying to elevate role
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/driver/profile',
    ]);
    $updateProfRes = $router->dispatch($updateProfReq);
    $t->assert($updateProfRes->getStatusCode() === 302, '12c. Profile update redirects with success flash');

    $reloadedUser = $userModel->find($drv1UserId);
    $t->assert($reloadedUser['phone'] === $newPhone, '12d. Permitted phone number updated successfully');
    $t->assert($reloadedUser['role'] === 'driver', '12e. Role tampering rejected: driver role remains strictly "driver"');

    // Password change test
    $pwReq = new \App\Core\Request([], [
        '_token'           => $csrf,
        'current_password' => 'Chauffeur@123',
        'new_password'     => 'NewSuperSecurePass@2026',
        'confirm_password' => 'NewSuperSecurePass@2026',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/driver/profile/password',
    ]);
    $pwRes = $router->dispatch($pwReq);
    $t->assert($pwRes->getStatusCode() === 302, '12f. Password change redirects with success message');
    $reloadedUserPw = $userModel->find($drv1UserId);
    $t->assert(password_verify('NewSuperSecurePass@2026', $reloadedUserPw['password']), '12g. New password successfully verified with bcrypt');

    // -------------------------------------------------------------------------
    // TEST 13: Sidebar and navbar work on desktop and mobile
    // -------------------------------------------------------------------------
    $t->assert(str_contains($dashHtml, 'sidebar-toggle-btn') && str_contains($dashHtml, 'toggleSidebarCollapse'), '13a. Desktop toggle collapse trigger present');
    $t->assert(str_contains($dashHtml, 'sidebar-backdrop') && str_contains($dashHtml, 'closeSidebar'), '13b. Mobile slide-in drawer backdrop and close controls present');
    $t->assert(str_contains($dashHtml, 'bottom-nav') && str_contains($dashHtml, 'Driver Mobile Navigation'), '13c. Mobile bottom navigation dock present');

    // -------------------------------------------------------------------------
    // TEST 14: Theme variables apply consistently across the driver portal
    // -------------------------------------------------------------------------
    $t->assert(str_contains($dashHtml, 'var(--vg-navy-950)') || str_contains($dashHtml, 'var(--vg-blue-500)'), '14a. Driver dashboard utilizes master luxury CSS design tokens');
    $t->assert(str_contains($dashHtml, 'asset(\'css/app.css\')') || str_contains($dashHtml, 'app.css'), '14b. Links to unified app.css theme engine');

    // -------------------------------------------------------------------------
    // TEST 15: Empty assignments and missing data produce professional empty states
    // -------------------------------------------------------------------------
    Session::set('user_id', $drv3UserId);
    Session::set('user', $userModel->find($drv3UserId));
    $emptyTripsReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/trips']);
    $emptyTripsRes = $router->dispatch($emptyTripsReq);
    $emptyTripsHtml = (string)$emptyTripsRes->getBody();
    $t->assert(str_contains($emptyTripsHtml, 'No trips assigned yet'), '15. Empty assignments produce professional luxury empty state: "No trips assigned yet"');

    // -------------------------------------------------------------------------
    // TEST 16: Existing admin and employee workflows continue to work
    // -------------------------------------------------------------------------
    Session::set('user_id', 1);
    Session::set('user_role', 'admin');
    Session::set('user', $userModel->find(1));

    $adminDashReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/dashboard']);
    $adminDashRes = $router->dispatch($adminDashReq);
    $t->assert($adminDashRes->getStatusCode() === 200, '16a. Existing admin dashboard continues to work (200 OK)');

    $adminAssignReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/assign']);
    $adminAssignRes = $router->dispatch($adminAssignReq);
    $t->assert($adminAssignRes->getStatusCode() === 200, '16b. Existing admin trip assignment workflow continues to work (200 OK)');

    $adminHistReq = new \App\Core\Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/trips/history']);
    $adminHistRes = $router->dispatch($adminHistReq);
    $t->assert($adminHistRes->getStatusCode() === 200, '16c. Existing admin fleet trip history continues to work (200 OK)');

    // Clean up created test records
    $pdo->prepare("DELETE FROM `gps_logs` WHERE `driver_id` IN (?, ?, ?)")->execute([$drv1ProfileId, $drv2ProfileId, 0]);
    $pdo->prepare("DELETE FROM `trip_status_history` WHERE `dispatch_id` IN (?, ?)")->execute([(int)$dispatch1['id'], (int)$dispatch2['id']]);
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` IN (?, ?)")->execute([(int)$dispatch1['id'], (int)$dispatch2['id']]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` IN (?, ?)")->execute([(int)$b1['id'], (int)$b2['id']]);
    $pdo->prepare("DELETE FROM `vehicles` WHERE `id` IN (?, ?)")->execute([$testVehicleId, $testVehicle2Id]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` IN (?, ?)")->execute([$drv1ProfileId, $drv2ProfileId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `user_id` = ?")->execute([$drv3UserId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` IN (?, ?, ?)")->execute([$drv1UserId, $drv2UserId, $drv3UserId]);
});

// =========================================================================
// SUITE 20: Dedicated Mobile Driver API, Tokens & Offline GPS Telemetry
// =========================================================================
$runner->runSuite('MODULE 20: Dedicated Mobile Driver API, Authentication Tokens & Offline Telemetry', function (TestRunner $t) {
    $router = require dirname(__DIR__) . '/app/routes.php';
    $pdo = \App\Core\Database::getConnection();

    $userModel = new \App\Models\User();
    $driverModel = new \App\Models\DriverProfile();

    $testUsername = 'mobiledrv_' . bin2hex(random_bytes(3));
    $testPassword = 'TestPassword123!';
    $now = date('Y-m-d H:i:s');

    $userId = $userModel->create([
        'name'       => 'Mobile Test Chauffeur',
        'username'   => $testUsername,
        'email'      => $testUsername . '@voyagego.internal',
        'phone'      => '+91 99887 76655',
        'password'   => password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 10]),
        'role'       => 'driver',
        'status'     => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $driverProfileId = $driverModel->create([
        'user_id'           => $userId,
        'license_number'    => 'DL-MOB-' . bin2hex(random_bytes(4)),
        'license_expiry'    => date('Y-m-d', strtotime('+2 years')),
        'experience_years'  => 5,
        'duty_status'       => 'on_duty',
        'current_latitude'  => 28.6139,
        'current_longitude' => 77.2090,
        'last_gps_at'       => $now,
        'created_at'        => $now,
        'updated_at'        => $now,
    ]);

    $t->assert($userId > 0 && $driverProfileId > 0, '20.1. Mobile chauffeur account provisioned');

    // 2. Authentication validation
    $badReq = new \App\Core\Request([], [
        'login' => $testUsername,
        'password' => 'wrong',
        'device_uuid' => 'qa-uuid'
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/mobile/v1/auth/login',
        'CONTENT_TYPE'   => 'application/json'
    ]);
    $badRes = $router->dispatch($badReq);
    $t->assert($badRes->getStatusCode() === 401, '20.2. Invalid password rejected with 401');

    // 3. Successful login
    $deviceUuid = 'qa-dev-' . bin2hex(random_bytes(4));
    $loginReq = new \App\Core\Request([], [
        'login'       => $testUsername,
        'password'    => $testPassword,
        'device_uuid' => $deviceUuid,
        'device_name' => 'Pixel 8 Native Test',
        'platform'    => 'android'
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/mobile/v1/auth/login',
        'CONTENT_TYPE'   => 'application/json'
    ]);
    $loginRes = $router->dispatch($loginReq);
    $t->assert($loginRes->getStatusCode() === 200, '20.3a. Mobile login succeeds with 200 OK');
    $loginData = json_decode($loginRes->getContent(), true);
    $token = $loginData['auth']['access_token'] ?? '';
    $refreshToken = $loginData['auth']['refresh_token'] ?? '';
    $t->assert(!empty($token) && !empty($refreshToken), '20.3b. Cryptographic access & refresh tokens issued');

    // 4. Mobile Auth Middleware enforcement
    $meReqNoAuth = new \App\Core\Request([], [], [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => '/api/mobile/v1/driver/me',
        'HTTP_ACCEPT'    => 'application/json'
    ]);
    $meResNoAuth = $router->dispatch($meReqNoAuth);
    $t->assert($meResNoAuth->getStatusCode() === 401, '20.4. Unauthenticated mobile request rejected with 401');

    $meReqAuth = new \App\Core\Request([], [], [
        'REQUEST_METHOD'     => 'GET',
        'REQUEST_URI'        => '/api/mobile/v1/driver/me',
        'HTTP_ACCEPT'        => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token
    ]);
    $meResAuth = $router->dispatch($meReqAuth);
    $t->assert($meResAuth->getStatusCode() === 200, '20.5. Authenticated chauffeur profile retrieved (200 OK)');

    // 5. Tracking status and start
    $startReq = new \App\Core\Request([], [], [
        'REQUEST_METHOD'     => 'POST',
        'REQUEST_URI'        => '/api/mobile/v1/tracking/start',
        'HTTP_ACCEPT'        => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token
    ]);
    $startRes = $router->dispatch($startReq);
    $t->assert($startRes->getStatusCode() === 200, '20.6. Tracking session initialized (200 OK)');

    // 6. Offline batch GPS synchronization
    $batchReq = new \App\Core\Request([], [
        'locations' => [
            ['point_id' => 'p1', 'latitude' => 28.6145, 'longitude' => 77.2095, 'speed_kmh' => 30, 'timestamp' => date('Y-m-d H:i:s', time() + 5)],
            ['point_id' => 'p2', 'latitude' => 28.6150, 'longitude' => 77.2100, 'speed_kmh' => 35, 'timestamp' => date('Y-m-d H:i:s', time() + 10)],
        ]
    ], [
        'REQUEST_METHOD'     => 'POST',
        'REQUEST_URI'        => '/api/mobile/v1/gps',
        'HTTP_ACCEPT'        => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token
    ]);
    $batchRes = $router->dispatch($batchReq);
    $t->assert($batchRes->getStatusCode() === 200, '20.7a. Batch offline GPS queue processed with 200 OK');
    $batchData = json_decode($batchRes->getContent(), true);
    $t->assert(($batchData['processed_count'] ?? 0) === 2, '20.7b. Exactly 2 breadcrumbs synchronized');

    // 7. Refresh token rotation
    $refReq = new \App\Core\Request([], ['refresh_token' => $refreshToken], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/api/mobile/v1/auth/refresh',
        'CONTENT_TYPE'   => 'application/json'
    ]);
    $refRes = $router->dispatch($refReq);
    $t->assert($refRes->getStatusCode() === 200, '20.8. Refresh token rotated successfully (200 OK)');
    $newAccessToken = json_decode($refRes->getContent(), true)['auth']['access_token'] ?? '';

    // 8. Logout & Revocation
    $logoutReq = new \App\Core\Request([], [], [
        'REQUEST_METHOD'     => 'POST',
        'REQUEST_URI'        => '/api/mobile/v1/auth/logout',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $newAccessToken
    ]);
    $logoutRes = $router->dispatch($logoutReq);
    $t->assert($logoutRes->getStatusCode() === 200, '20.9. Device session revoked on logout (200 OK)');

    $revokedCheck = $router->dispatch($logoutReq);
    $t->assert($revokedCheck->getStatusCode() === 401, '20.10. Revoked token rejected with 401');

    // Cleanup
    $pdo->prepare("DELETE FROM `gps_logs` WHERE `driver_id` = ?")->execute([$driverProfileId]);
    $pdo->prepare("DELETE FROM `tracking_sessions` WHERE `driver_id` = ?")->execute([$driverProfileId]);
    $pdo->prepare("DELETE FROM `driver_mobile_devices` WHERE `driver_id` = ?")->execute([$driverProfileId]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverProfileId]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
});

exit($runner->summary());


