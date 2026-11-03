<?php
/**
 * Comprehensive Automated Verification Suite for Global Save & Redirect Standard
 * Tests all CREATE and EDIT/UPDATE operations across modules.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;
use App\Controllers\VehicleController;
use App\Controllers\TourController;
use App\Controllers\BookingController;
use App\Controllers\InvoiceController;
use App\Controllers\FinanceController;
use App\Controllers\CompanyController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\DispatchController;
use App\Services\EmailOtpService;

class GlobalSaveRedirectTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "====================================================================\n";
        echo "    GLOBAL SAVE & REDIRECT STANDARD VERIFICATION SUITE\n";
        echo "====================================================================\n\n";

        // Setup session as authenticated admin
        $_SESSION = [
            'user' => [
                'id'       => 1,
                'name'     => 'System Admin',
                'username' => 'admin',
                'role'     => 'admin',
                'email'    => 'admin@example.com'
            ]
        ];

        $this->testCustomerRedirects();
        $this->testEmployeeRedirects();
        $this->testVehicleRedirects();
        $this->testTourRedirects();
        $this->testBookingRedirects();
        $this->testInvoiceRedirects();
        $this->testFinanceAccountRedirects();
        $this->testFinanceTransactionRedirects();
        $this->testFinanceAdvanceRedirects();
        $this->testFinancePayrollRedirects();
        $this->testFinanceRefundRedirects();
        $this->testFinanceExpenseCategoryRedirects();
        $this->testCompanyRedirects();
        $this->testUserRedirects();
        $this->testRoleRedirects();
        $this->testViewDetailsPagesStillAccessible();
        $this->testEmailQueuingPerformance();

        echo "\n--------------------------------------------------------------------\n";
        echo "Summary: Total Passed: {$this->passed} | Total Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            echo "\nFailed Assertions:\n";
            foreach ($this->errors as $err) {
                echo " [FAIL] {$err}\n";
            }
            exit(1);
        } else {
            echo "ALL VERIFICATION CHECKS PASSED PERFECTLY!\n";
            echo "====================================================================\n";
        }
    }

    private function assert(bool $condition, string $testName, string $errorDetails = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo " [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $msg = "{$testName}" . ($errorDetails ? " - {$errorDetails}" : "");
            $this->errors[] = $msg;
            echo " [FAIL] {$msg}\n";
        }
    }

    private function getRedirectLocation(Response $response): string
    {
        return $response->getHeader('Location') ?? '';
    }

    private function testCustomerRedirects(): void
    {
        echo "\n--- 1. CUSTOMERS MODULE ---\n";
        $ctrl = new CustomerController();
        $pdo = Database::getConnection();
        $otpService = new EmailOtpService();

        // 1.1 Customer Create standard redirect
        $uniquePhone = '9' . substr(strval(time()), -9);
        $uniqueEmail = 'cust_' . time() . '_' . rand(100, 999) . '@example.com';
        $custToken = bin2hex(random_bytes(32));
        $rawOtp = '4321';
        $hash = password_hash($rawOtp, PASSWORD_DEFAULT);

        $pdo->prepare("INSERT INTO `email_verifications` 
            (`email`, `otp_hash`, `flow`, `status`, `expires_at`, `resend_available_at`, `verification_token`, `created_at`) 
            VALUES (?, ?, 'customer', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW(), ?, NOW())")
            ->execute([$uniqueEmail, $hash, $custToken]);

        $otpService->verifyOtp($uniqueEmail, EmailOtpService::FLOW_CUSTOMER, $rawOtp);

        $req = new Request([], [
            'title'                    => 'Mr',
            'name'                     => 'Test Customer Save Standard',
            'email'                    => $uniqueEmail,
            'phone'                    => $uniquePhone,
            'status'                   => 'active',
            'email_verification_token' => $custToken
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/customers'), "Customer Create -> Standard Redirect to /office/customers (Got: {$loc})");

        // 1.2 Customer Edit/Update standard redirect
        $custStmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        $custStmt->execute([$uniqueEmail]);
        $custId = (int)$custStmt->fetchColumn();

        $updateReq = new Request([], [
            'title'  => 'Mr',
            'name'   => 'Test Customer Updated Name',
            'email'  => $uniqueEmail,
            'phone'  => $uniquePhone,
            'status' => 'active'
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->update($updateReq, ['id' => $custId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/customers'), "Customer Edit/Update -> Standard Redirect to /office/customers (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/customers/{$custId}"), "Customer Edit/Update -> Does NOT redirect to View/Details page");

        // 1.3 Customer Edit/Update AJAX response
        $ajaxUpdateReq = new Request([], [
            'title'  => 'Mr',
            'name'   => 'Test Customer Updated AJAX',
            'email'  => $uniqueEmail,
            'phone'  => $uniquePhone,
            'status' => 'active'
        ], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $ajaxUpdateRes = $ctrl->update($ajaxUpdateReq, ['id' => $custId]);
        $json = json_decode((string)$ajaxUpdateRes->getContent(), true);
        $this->assert(!empty($json['success']) && str_ends_with($json['redirect'] ?? '', '/office/customers'), "Customer Edit/Update AJAX -> Returns redirect to /office/customers");

        // Cleanup
        $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$custId]);
    }

    private function testEmployeeRedirects(): void
    {
        echo "\n--- 2. EMPLOYEES MODULE ---\n";
        $ctrl = new EmployeeController();
        $pdo = Database::getConnection();
        $otpService = new EmailOtpService();

        $uniqueEmail = 'emp_' . time() . '_' . rand(100, 999) . '@example.com';
        $uniquePhone = '8' . substr(strval(time()), -9);
        $empToken = bin2hex(random_bytes(32));
        $rawOtp = '4321';
        $hash = password_hash($rawOtp, PASSWORD_DEFAULT);

        $pdo->prepare("INSERT INTO `email_verifications` 
            (`email`, `otp_hash`, `flow`, `status`, `expires_at`, `resend_available_at`, `verification_token`, `created_at`) 
            VALUES (?, ?, 'employee', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW(), ?, NOW())")
            ->execute([$uniqueEmail, $hash, $empToken]);

        $otpService->verifyOtp($uniqueEmail, EmailOtpService::FLOW_EMPLOYEE, $rawOtp);

        // 2.1 Employee Create redirect
        $req = new Request([], [
            'first_name'               => 'John',
            'last_name'                => 'Doe',
            'email'                    => $uniqueEmail,
            'phone'                    => $uniquePhone,
            'role'                     => 'driver',
            'employment_type'          => 'full_time',
            'joining_date'             => date('Y-m-d'),
            'status'                   => 'active',
            'email_verification_token' => $empToken
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/employees'), "Employee Create -> Redirect to /office/employees (Got: {$loc})");

        // 2.2 Employee Edit/Update standard redirect
        $empStmt = $pdo->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
        $empStmt->execute([$uniqueEmail]);
        $empId = (int)$empStmt->fetchColumn();

        $updateReq = new Request([], [
            'first_name'      => 'John Updated',
            'last_name'       => 'Doe',
            'email'           => $uniqueEmail,
            'phone'           => $uniquePhone,
            'role'            => 'driver',
            'employment_type' => 'full_time',
            'joining_date'    => date('Y-m-d'),
            'status'          => 'active'
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->update($updateReq, ['id' => $empId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/employees'), "Employee Edit/Update -> Redirect to /office/employees (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/employees/{$empId}"), "Employee Edit/Update -> Does NOT redirect to View/Details page");

        // 2.3 Employee Edit/Update AJAX response
        $ajaxUpdateReq = new Request([], [
            'first_name'      => 'John AJAX',
            'last_name'       => 'Doe',
            'email'           => $uniqueEmail,
            'phone'           => $uniquePhone,
            'role'            => 'driver',
            'employment_type' => 'full_time',
            'joining_date'    => date('Y-m-d'),
            'status'          => 'active'
        ], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $ajaxUpdateRes = $ctrl->update($ajaxUpdateReq, ['id' => $empId]);
        $json = json_decode((string)$ajaxUpdateRes->getContent(), true);
        $this->assert(!empty($json['success']) && str_ends_with($json['redirect'] ?? '', '/office/employees'), "Employee Edit/Update AJAX -> Returns redirect to /office/employees");

        // Cleanup
        $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$empId]);
    }

    private function testVehicleRedirects(): void
    {
        echo "\n--- 3. VEHICLES MODULE ---\n";
        $ctrl = new VehicleController();
        $pdo = Database::getConnection();

        $vNum = 'MH-' . rand(10, 99) . '-AB-' . rand(1000, 9999);
        $req = new Request([], [
            'vehicle_number'   => $vNum,
            'make_model'       => 'Toyota Innova Crysta',
            'seating_capacity' => 7,
            'fuel_type'        => 'diesel',
            'odometer_km'      => 12000,
            'status'           => 'available'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/vehicles'), "Vehicle Create -> Redirect to /office/vehicles (Got: {$loc})");

        $vStmt = $pdo->prepare("SELECT id FROM vehicles WHERE vehicle_number = ? LIMIT 1");
        $vStmt->execute([$vNum]);
        $vId = (int)$vStmt->fetchColumn();

        $statusReq = new Request([], ['status' => 'maintenance'], ['REQUEST_METHOD' => 'POST']);
        $statusRes = $ctrl->updateStatus($statusReq, ['id' => $vId]);
        $statusLoc = $this->getRedirectLocation($statusRes);
        $this->assert(str_ends_with($statusLoc, '/office/vehicles'), "Vehicle Update Status -> Redirect to /office/vehicles (Got: {$statusLoc})");

        // Cleanup
        $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$vId]);
    }

    private function testTourRedirects(): void
    {
        echo "\n--- 4. TOURS MODULE ---\n";
        $ctrl = new TourController();
        $pdo = Database::getConnection();

        $tourTitle = 'Test Luxury Goa Tour ' . rand(100, 999);
        $req = new Request([], [
            'title'            => $tourTitle,
            'category'         => 'beach',
            'duration_days'    => 4,
            'duration_nights'  => 3,
            'pickup_city'      => 'Mumbai',
            'destination_city' => 'Goa',
            'base_price'       => 15000,
            'max_passengers'   => 6,
            'status'           => 'published'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/tours'), "Tour Create -> Redirect to /office/tours (Got: {$loc})");

        $tStmt = $pdo->prepare("SELECT id FROM tours WHERE title = ? LIMIT 1");
        $tStmt->execute([$tourTitle]);
        $tId = (int)$tStmt->fetchColumn();

        $updateReq = new Request([], [
            'title'            => $tourTitle . ' Updated',
            'category'         => 'beach',
            'duration_days'    => 5,
            'duration_nights'  => 4,
            'pickup_city'      => 'Mumbai',
            'destination_city' => 'Goa',
            'base_price'       => 18000,
            'max_passengers'   => 6,
            'status'           => 'published'
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->update($updateReq, ['id' => $tId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/tours'), "Tour Edit/Update -> Redirect to /office/tours (Got: {$updateLoc})");

        // Cleanup
        $pdo->prepare("DELETE FROM tours WHERE id = ?")->execute([$tId]);
    }

    private function testBookingRedirects(): void
    {
        echo "\n--- 5. BOOKINGS MODULE ---\n";
        $ctrl = new BookingController();
        $pdo = Database::getConnection();

        // Ensure customer exists
        $cStmt = $pdo->query("SELECT id FROM customers LIMIT 1");
        $cId = (int)$cStmt->fetchColumn();
        if (!$cId) {
            $pdo->exec("INSERT INTO customers (customer_code, name, phone, email, status) VALUES ('CUST-T1', 'Booking Tester', '9888888888', 'btester@example.com', 'active')");
            $cId = (int)$pdo->lastInsertId();
        }

        $req = new Request([], [
            'customer_id'     => $cId,
            'pickup_address'  => 'Mumbai Terminal 2 Airport',
            'drop_address'    => 'Taj Mahal Palace, Colaba',
            'start_date'      => date('Y-m-d', strtotime('+2 days')),
            'end_date'        => date('Y-m-d', strtotime('+3 days')),
            'passenger_count' => 2,
            'custom_subtotal' => 5000,
            'advance_amount'  => 1000,
            'payment_method'  => 'cash'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/bookings'), "Booking Create -> Redirect to /office/bookings (Got: {$loc})");
        $this->assert(!preg_match('#/office/bookings/\d+#', $loc), "Booking Create -> Does NOT redirect to View/Details page");

        // Get created booking
        $bStmt = $pdo->query("SELECT id FROM bookings ORDER BY id DESC LIMIT 1");
        $bId = (int)$bStmt->fetchColumn();

        // Update status redirect
        $statusReq = new Request([], ['status' => 'confirmed'], ['REQUEST_METHOD' => 'POST']);
        $statusRes = $ctrl->updateStatus($statusReq, ['id' => $bId]);
        $statusLoc = $this->getRedirectLocation($statusRes);
        $this->assert(str_ends_with($statusLoc, '/office/bookings'), "Booking Update Status -> Redirect to /office/bookings (Got: {$statusLoc})");
        $this->assert(!str_contains($statusLoc, "/office/bookings/{$bId}"), "Booking Update Status -> Does NOT redirect to View/Details page");

        // Safe Cleanup
        $invStmt = $pdo->prepare("SELECT id FROM invoices WHERE booking_id = ?");
        $invStmt->execute([$bId]);
        $invIds = $invStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($invIds as $invId) {
            $pdo->prepare("DELETE FROM payments WHERE invoice_id = ?")->execute([$invId]);
            $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$invId]);
        }
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bId]);
    }

    private function testInvoiceRedirects(): void
    {
        echo "\n--- 6. INVOICES MODULE ---\n";
        $ctrl = new InvoiceController();
        $pdo = Database::getConnection();

        // Get admin user ID for valid booking foreign key
        $uStmt = $pdo->query("SELECT id FROM users LIMIT 1");
        $uId = (int)$uStmt->fetchColumn();

        $cStmt = $pdo->query("SELECT id FROM customers LIMIT 1");
        $cProfileId = (int)$cStmt->fetchColumn();

        $bCode = 'BK-INV-TEST-' . rand(100, 999);
        $pdo->prepare("INSERT INTO bookings (booking_code, customer_id, customer_profile_id, pickup_address, drop_address, start_date, end_date, passenger_count, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$bCode, $uId, $cProfileId, 'Pickup A', 'Drop B', date('Y-m-d'), date('Y-m-d'), 1, 3000, 'confirmed']);
        $bId = (int)$pdo->lastInsertId();

        $genRes = $ctrl->generate(new Request(), ['booking_id' => $bId]);
        $genLoc = $this->getRedirectLocation($genRes);
        $this->assert(str_ends_with($genLoc, '/office/invoices'), "Invoice Generate/Create -> Redirect to /office/invoices (Got: {$genLoc})");
        $this->assert(!preg_match('#/office/invoices/\d+#', $genLoc), "Invoice Generate/Create -> Does NOT redirect to View/Details page");

        $invStmt = $pdo->prepare("SELECT id FROM invoices WHERE booking_id = ? LIMIT 1");
        $invStmt->execute([$bId]);
        $invId = (int)$invStmt->fetchColumn();

        // Test payment recording redirect
        $payReq = new Request([], [
            'amount'         => 1000,
            'payment_method' => 'cash'
        ], ['REQUEST_METHOD' => 'POST']);

        $payRes = $ctrl->recordPayment($payReq, ['id' => $invId]);
        $payLoc = $this->getRedirectLocation($payRes);
        $this->assert(str_ends_with($payLoc, '/office/invoices'), "Invoice Record Payment -> Redirect to /office/invoices (Got: {$payLoc})");

        // Safe Cleanup
        $pdo->prepare("DELETE FROM payments WHERE invoice_id = ?")->execute([$invId]);
        $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$invId]);
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bId]);
    }

    private function testFinanceAccountRedirects(): void
    {
        echo "\n--- 7. FINANCE ACCOUNTS MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        $accName = 'Test Treasury Vault ' . rand(100, 999);
        $req = new Request([], [
            'account_name' => $accName,
            'account_type' => 'cash',
            'status'       => 'active'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->storeAccount($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/finance/accounts'), "Account Create -> Redirect to /office/finance/accounts (Got: {$loc})");

        $aStmt = $pdo->prepare("SELECT id FROM finance_accounts WHERE account_name = ? LIMIT 1");
        $aStmt->execute([$accName]);
        $accId = (int)$aStmt->fetchColumn();

        $updateReq = new Request([], [
            'account_name' => $accName . ' Updated',
            'account_type' => 'cash',
            'status'       => 'active',
            'redirect_to'  => url('/office/finance/accounts/' . $accId) // Deliberately passing old details url to test override
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->updateAccount($updateReq, ['id' => $accId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/finance/accounts'), "Account Update -> Redirect to /office/finance/accounts (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/finance/accounts/{$accId}"), "Account Update -> Does NOT redirect to View/Details page");

        // Cleanup
        $pdo->prepare("DELETE FROM finance_accounts WHERE id = ?")->execute([$accId]);
    }

    private function testFinanceTransactionRedirects(): void
    {
        echo "\n--- 8. FINANCE TRANSACTIONS MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        // Get active account
        $accStmt = $pdo->query("SELECT id FROM finance_accounts WHERE status = 'active' LIMIT 1");
        $accId = (int)$accStmt->fetchColumn();

        $req = new Request([], [
            'account_id'       => $accId,
            'transaction_type' => 'credit',
            'amount'           => 2500,
            'category'         => 'Client Deposit',
            'payment_method'   => 'bank_transfer',
            'notes'            => 'Test transaction create'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->recordTransaction($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/finance/transactions'), "Transaction Create -> Redirect to /office/finance/transactions (Got: {$loc})");

        $txStmt = $pdo->query("SELECT id FROM finance_ledger ORDER BY id DESC LIMIT 1");
        $txId = (int)$txStmt->fetchColumn();

        $updateReq = new Request([], [
            'category'       => 'Client Deposit (Verified)',
            'payment_method' => 'bank_transfer',
            'notes'          => 'Updated transaction notes',
            'redirect_to'    => url('/office/finance/transactions/' . $txId)
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->updateTransaction($updateReq, ['id' => $txId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/finance/transactions'), "Transaction Update -> Redirect to /office/finance/transactions (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/finance/transactions/{$txId}"), "Transaction Update -> Does NOT redirect to View/Details page");

        // Cleanup
        $pdo->prepare("DELETE FROM finance_ledger WHERE id = ?")->execute([$txId]);
    }

    private function testFinanceAdvanceRedirects(): void
    {
        echo "\n--- 9. EMPLOYEE ADVANCES MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        // Ensure employee exists
        $empStmt = $pdo->query("SELECT id FROM employees LIMIT 1");
        $empId = (int)$empStmt->fetchColumn();

        $req = new Request([], [
            'employee_id'    => $empId,
            'amount'         => 3000,
            'reason'         => 'Emergency Medical Expense',
            'requested_date' => date('Y-m-d')
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->storeAdvance($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/finance/advances'), "Advance Create -> Redirect to /office/finance/advances (Got: {$loc})");

        $advStmt = $pdo->query("SELECT id FROM employee_advances ORDER BY id DESC LIMIT 1");
        $advId = (int)$advStmt->fetchColumn();

        $updateReq = new Request([], [
            'amount'         => 3500,
            'reason'         => 'Emergency Medical Expense (Revised)',
            'requested_date' => date('Y-m-d'),
            'status'         => 'pending',
            'redirect_to'    => url('/office/finance/advances/' . $advId)
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->updateAdvance($updateReq, ['id' => $advId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/finance/advances'), "Advance Update -> Redirect to /office/finance/advances (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/finance/advances/{$advId}"), "Advance Update -> Does NOT redirect to View/Details page");

        // Cleanup
        $pdo->prepare("DELETE FROM employee_advances WHERE id = ?")->execute([$advId]);
    }

    private function testFinancePayrollRedirects(): void
    {
        echo "\n--- 10. PAYROLL MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        $month = (int)date('m');
        $year = (int)date('Y');

        // Delete any existing batch for this month/year to prevent collision
        $existingPayrolls = $pdo->query("SELECT id FROM payrolls WHERE month = {$month} AND year = {$year}")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($existingPayrolls as $epId) {
            $pdo->prepare("DELETE FROM payroll_items WHERE payroll_id = ?")->execute([$epId]);
            $pdo->prepare("DELETE FROM payrolls WHERE id = ?")->execute([$epId]);
        }

        $genReq = new Request([], [
            'month' => $month,
            'year'  => $year
        ], ['REQUEST_METHOD' => 'POST']);

        $genRes = $ctrl->generatePayroll($genReq);
        $genLoc = $this->getRedirectLocation($genRes);
        $this->assert(str_ends_with($genLoc, '/office/finance/payroll'), "Payroll Generate -> Redirect to /office/finance/payroll (Got: {$genLoc})");
        $this->assert(!preg_match('#/office/finance/payroll/\d+#', $genLoc), "Payroll Generate -> Does NOT redirect to View/Details page");

        $pStmt = $pdo->query("SELECT id FROM payrolls ORDER BY id DESC LIMIT 1");
        $pId = (int)$pStmt->fetchColumn();

        if ($pId) {
            // Test mark paid
            $accStmt = $pdo->query("SELECT id FROM finance_accounts WHERE status = 'active' LIMIT 1");
            $accId = (int)$accStmt->fetchColumn();

            $payReq = new Request([], [
                'account_id'     => $accId,
                'payment_method' => 'bank_transfer',
                'payment_ref'    => 'NEFT-SAL-BATCH'
            ], ['REQUEST_METHOD' => 'POST']);

            $payRes = $ctrl->markPayrollPaid($payReq, ['id' => $pId]);
            $payLoc = $this->getRedirectLocation($payRes);
            $this->assert(str_ends_with($payLoc, '/office/finance/payroll'), "Payroll Mark Paid -> Redirect to /office/finance/payroll (Got: {$payLoc})");
            $this->assert(!str_contains($payLoc, "/office/finance/payroll/{$pId}"), "Payroll Mark Paid -> Does NOT redirect to View/Details page");

            // Cleanup
            $pdo->prepare("DELETE FROM payroll_items WHERE payroll_id = ?")->execute([$pId]);
            $pdo->prepare("DELETE FROM payrolls WHERE id = ?")->execute([$pId]);
        }
    }

    private function testFinanceRefundRedirects(): void
    {
        echo "\n--- 11. REFUNDS MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        $req = new Request([], [
            'amount'        => 500,
            'refund_method' => 'cash',
            'reason'        => 'Customer trip route adjustment',
            'auto_process'  => 0
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->storeRefund($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/finance/refunds'), "Refund Create -> Redirect to /office/finance/refunds (Got: {$loc})");

        $rStmt = $pdo->query("SELECT id FROM refunds ORDER BY id DESC LIMIT 1");
        $rId = (int)$rStmt->fetchColumn();

        // Cleanup
        $pdo->prepare("DELETE FROM refunds WHERE id = ?")->execute([$rId]);
    }

    private function testFinanceExpenseCategoryRedirects(): void
    {
        echo "\n--- 12. EXPENSE CATEGORIES MODULE ---\n";
        $ctrl = new FinanceController();
        $pdo = Database::getConnection();

        $catName = 'Vehicle Tolls ' . rand(100, 999);
        $req = new Request([], [
            'name'        => $catName,
            'description' => 'Highway toll charges and bridge fees',
            'is_active'   => 1
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->storeExpenseCategory($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/finance/expense-categories'), "Expense Category Create -> Redirect to /office/finance/expense-categories (Got: {$loc})");

        $cStmt = $pdo->prepare("SELECT id FROM expense_categories WHERE name = ? LIMIT 1");
        $cStmt->execute([$catName]);
        $cId = (int)$cStmt->fetchColumn();

        $updateReq = new Request([], [
            'name'        => $catName . ' Updated',
            'description' => 'Updated toll charges description',
            'is_active'   => 1
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->updateExpenseCategory($updateReq, ['id' => $cId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/finance/expense-categories'), "Expense Category Update -> Redirect to /office/finance/expense-categories (Got: {$updateLoc})");

        // Cleanup
        $pdo->prepare("DELETE FROM expense_categories WHERE id = ?")->execute([$cId]);
    }

    private function testCompanyRedirects(): void
    {
        echo "\n--- 13. COMPANY PROFILE MODULE ---\n";
        $ctrl = new CompanyController();

        $req = new Request([], [
            'company_name'   => 'VoyageGo Luxury Fleet',
            'official_email' => 'contact@voyagego.internal',
            'contact_number' => '+91 99999 88888',
            'city'           => 'Mumbai',
            'state'          => 'Maharashtra',
            'country'        => 'India'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->save($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/company'), "Company Save -> Redirect to /office/company (Got: {$loc})");
    }

    private function testUserRedirects(): void
    {
        echo "\n--- 14. USERS MODULE ---\n";
        $ctrl = new UserController();
        $pdo = Database::getConnection();

        $username = 'usr_' . time() . '_' . rand(10, 99);
        $email = $username . '@example.com';

        $req = new Request([], [
            'name'                  => 'Operations Staff',
            'username'              => $username,
            'email'                 => $email,
            'password'              => 'P@ssw0rd123!',
            'password_confirmation' => 'P@ssw0rd123!',
            'role'                  => 'dispatcher',
            'status'                => 'active'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/users'), "User Create -> Redirect to /office/users (Got: {$loc})");

        $uStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $uStmt->execute([$username]);
        $uId = (int)$uStmt->fetchColumn();

        $updateReq = new Request([], [
            'name'     => 'Operations Staff Updated',
            'username' => $username,
            'email'    => $email,
            'role'     => 'dispatcher',
            'status'   => 'active'
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->update($updateReq, ['id' => $uId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/users'), "User Update -> Redirect to /office/users (Got: {$updateLoc})");
        $this->assert(!str_contains($updateLoc, "/office/users/{$uId}/edit"), "User Update -> Does NOT redirect to Edit or Details");

        // Cleanup
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uId]);
    }

    private function testRoleRedirects(): void
    {
        echo "\n--- 15. ROLES & PERMISSIONS MODULE ---\n";
        $ctrl = new RoleController();
        $pdo = Database::getConnection();

        $roleName = 'Fleet Auditor ' . rand(100, 999);
        $req = new Request([], [
            'name'        => $roleName,
            'description' => 'Audits vehicle safety logs'
        ], ['REQUEST_METHOD' => 'POST']);

        $res = $ctrl->store($req);
        $loc = $this->getRedirectLocation($res);
        $this->assert(str_ends_with($loc, '/office/roles'), "Role Create -> Redirect to /office/roles (Got: {$loc})");

        $rStmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
        $rStmt->execute([$roleName]);
        $rId = (int)$rStmt->fetchColumn();

        $updateReq = new Request([], [
            'name'        => $roleName . ' (Updated)',
            'description' => 'Updated auditor description'
        ], ['REQUEST_METHOD' => 'POST']);

        $updateRes = $ctrl->update($updateReq, ['id' => $rId]);
        $updateLoc = $this->getRedirectLocation($updateRes);
        $this->assert(str_ends_with($updateLoc, '/office/roles'), "Role Update -> Redirect to /office/roles (Got: {$updateLoc})");

        // Cleanup
        $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$rId]);
    }

    private function testViewDetailsPagesStillAccessible(): void
    {
        echo "\n--- 16. VIEW/DETAILS MANUAL NAVIGATION VERIFICATION ---\n";
        $pdo = Database::getConnection();

        // 16.1 Customer View
        $custStmt = $pdo->query("SELECT id FROM customers LIMIT 1");
        $cId = (int)$custStmt->fetchColumn();
        if ($cId) {
            $ctrl = new CustomerController();
            $res = $ctrl->show(new Request(), ['id' => $cId]);
            $this->assert($res->getStatusCode() === 200, "Customer View/Details page manually accessible (HTTP 200)");
        }

        // 16.2 Employee View
        $empStmt = $pdo->query("SELECT id FROM employees LIMIT 1");
        $eId = (int)$empStmt->fetchColumn();
        if ($eId) {
            $ctrl = new EmployeeController();
            $res = $ctrl->show(new Request(), ['id' => $eId]);
            $this->assert($res->getStatusCode() === 200, "Employee View/Details page manually accessible (HTTP 200)");
        }

        // 16.3 Booking View
        $bStmt = $pdo->query("SELECT id FROM bookings LIMIT 1");
        $bId = (int)$bStmt->fetchColumn();
        if ($bId) {
            $ctrl = new BookingController();
            $res = $ctrl->show(new Request(), ['id' => $bId]);
            $this->assert($res->getStatusCode() === 200, "Booking View/Details page manually accessible (HTTP 200)");
        }

        // 16.4 Invoice View
        $invStmt = $pdo->query("SELECT id FROM invoices LIMIT 1");
        $invId = (int)$invStmt->fetchColumn();
        if ($invId) {
            $ctrl = new InvoiceController();
            $res = $ctrl->show(new Request(), ['id' => $invId]);
            $this->assert($res->getStatusCode() === 200, "Invoice View/Details page manually accessible (HTTP 200)");
        }

        // 16.5 Account View
        $accStmt = $pdo->query("SELECT id FROM finance_accounts LIMIT 1");
        $accId = (int)$accStmt->fetchColumn();
        if ($accId) {
            $ctrl = new FinanceController();
            $res = $ctrl->accountShow(new Request(), ['id' => $accId]);
            $this->assert($res->getStatusCode() === 200, "Finance Account View/Details page manually accessible (HTTP 200)");
        }
    }

    private function testEmailQueuingPerformance(): void
    {
        echo "\n--- 17. SAVE PERFORMANCE & ASYNC EMAIL QUEUE VERIFICATION ---\n";
        $emailService = new \App\Services\EmailService();
        $pdo = Database::getConnection();

        $startTime = microtime(true);
        $logId = $emailService->queue(
            'speedtest_' . time() . '@example.com',
            'Speed Tester',
            'Queue Performance Test',
            '<p>Test body</p>',
            'Test body',
            'performance_test',
            'test',
            999
        );
        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $this->assert($logId !== null, "Email queue operation generated pending email log entry (ID: {$logId})");
        $this->assert($elapsedMs < 100, "Email queue finished in under 100ms without blocking (Actual: " . round($elapsedMs, 2) . "ms)");

        if ($logId) {
            $stmt = $pdo->prepare("SELECT status FROM email_logs WHERE id = ?");
            $stmt->execute([$logId]);
            $status = $stmt->fetchColumn();
            $this->assert($status === 'pending', "Email log pre-recorded with status 'pending' (Got: '{$status}')");

            // Clean up test log
            $pdo->prepare("DELETE FROM email_logs WHERE id = ?")->execute([$logId]);
        }
    }
}

// Run the test suite
$test = new GlobalSaveRedirectTest();
$test->run();
