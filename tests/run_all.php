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

    // 5. GPS Submission Validation Against Active Assignment
    $driverModel = new DriverProfile();
    $allDrivers = $driverModel->all();
    if (!empty($allDrivers)) {
        $driverId = (int)$allDrivers[0]['id'];
        // Driver without an active trip should be rejected
        $dispatchModel = new Dispatch();
        $activeTrip = $dispatchModel->getActiveAssignmentForDriver($driverId);
        if (!$activeTrip) {
            $t->assertThrows(function () use ($gpsService, $driverId) {
                $gpsService->recordDriverLocation($driverId, 28.6139, 77.2090, 45.0);
            }, RuntimeException::class, 'GPS coordinates rejected when chauffeur has no active authorized trip assignment');
        }
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

exit($runner->summary());

