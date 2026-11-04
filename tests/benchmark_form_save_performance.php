<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BillingService;
use App\Services\DispatchService;
use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;
use App\Controllers\BookingController;
use App\Controllers\FinanceController;
use App\Controllers\DispatchController;
use App\Controllers\CompanyController;

$pdo = Database::getConnection();
Session::start();
$adminUser = [
    'id' => 1,
    'name' => 'Benchmark Administrator',
    'email' => 'admin@voyagego.com',
    'role' => 'admin'
];
Session::setUser($adminUser);
$_SESSION['user'] = $adminUser;

echo "====================================================================\n";
echo " FORM SAVE PERFORMANCE BENCHMARK (INITIAL MEASUREMENTS)\n";
echo "====================================================================\n\n";

$results = [];

try {
    // 1. Benchmark Customer Save
    $custRun = time() . '_' . rand(100, 999);
$custEmail = "bench_cust_{$custRun}@example.com";
// Pre-seed verified OTP in DB
$otpHash = password_hash('1234', PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(32));
$pdo->prepare("INSERT INTO email_verifications (email, flow, otp_hash, attempts, max_attempts, expires_at, status, verified_at, verification_token, created_at, updated_at) VALUES (?, 'customer', ?, 0, 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 'verified', NOW(), ?, NOW(), NOW())")
    ->execute([$custEmail, $otpHash, $token]);

$customerController = new CustomerController();
$custReq = new Request([], [
    'title'                    => 'Mr',
    'name'                     => "Benchmark Customer {$custRun}",
    'email'                    => $custEmail,
    'phone'                    => '98' . rand(10000000, 99999999),
    'city'                     => 'Mumbai',
    'state'                    => 'Maharashtra',
    'status'                   => 'active',
    'email_verification_token' => $token,
], [], [], ['REQUEST_METHOD' => 'POST']);

$t0 = microtime(true);
$custResp = $customerController->store($custReq);
$tCust = round((microtime(true) - $t0) * 1000, 2);
$results['Customer Create'] = $tCust;
echo "1. Customer Create Save Time: {$tCust} ms (HTTP Status: {$custResp->getStatusCode()})\n";

// 2. Benchmark Customer Update
$createdCust = (new Customer())->findBy('email', $custEmail);
$custEditReq = new Request([], [
    'name'   => "Benchmark Customer Updated {$custRun}",
    'phone'  => '98' . rand(10000000, 99999999),
    'city'   => 'Pune',
    'status' => 'active',
], [], [], ['REQUEST_METHOD' => 'POST']);
$t0 = microtime(true);
$custEditResp = $customerController->update($custEditReq, ['id' => (int)$createdCust['id']]);
$tCustEdit = round((microtime(true) - $t0) * 1000, 2);
$results['Customer Edit'] = $tCustEdit;
echo "2. Customer Edit Save Time: {$tCustEdit} ms\n";

// 3. Benchmark Employee Save
$empRun = time() . '_' . rand(100, 999);
$empEmail = "bench_emp_{$empRun}@example.com";
$tokenEmp = bin2hex(random_bytes(32));
$pdo->prepare("INSERT INTO email_verifications (email, flow, otp_hash, attempts, max_attempts, expires_at, status, verified_at, verification_token, created_at, updated_at) VALUES (?, 'employee', ?, 0, 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 'verified', NOW(), ?, NOW(), NOW())")
    ->execute([$empEmail, $otpHash, $tokenEmp]);

$employeeController = new EmployeeController();
$empReq = new Request([], [
    'first_name'               => "BenchFirst{$empRun}",
    'last_name'                => "BenchLast",
    'email'                    => $empEmail,
    'phone'                    => '97' . rand(10000000, 99999999),
    'role'                     => 'dispatcher',
    'employment_type'          => 'full_time',
    'joining_date'             => date('Y-m-d'),
    'status'                   => 'active',
    'email_verification_token' => $tokenEmp,
], [], [], ['REQUEST_METHOD' => 'POST']);

$t0 = microtime(true);
$empResp = $employeeController->store($empReq);
$tEmp = round((microtime(true) - $t0) * 1000, 2);
$results['Employee Create'] = $tEmp;
echo "3. Employee Create Save Time: {$tEmp} ms (HTTP Status: {$empResp->getStatusCode()})\n";

// 4. Benchmark Booking Save (with advance payment)
$bookingController = new BookingController();
$bookReq = new Request([], [
    'customer_id'       => (int)$createdCust['id'],
    'pickup_address'    => 'Airport Terminal 2, Mumbai',
    'drop_address'      => 'The Taj Mahal Palace, Colaba',
    'start_date'        => date('Y-m-d', strtotime('+3 days')),
    'end_date'          => date('Y-m-d', strtotime('+3 days')),
    'pickup_time'       => '11:00:00',
    'passenger_count'   => 2,
    'custom_subtotal'   => 4500.00,
    'advance_amount'    => 1500.00,
    'payment_method'    => 'upi',
    'idempotency_token' => 'bench_bk_' . time() . rand(1000, 9999),
], [], [], ['REQUEST_METHOD' => 'POST']);

$t0 = microtime(true);
$bookResp = $bookingController->store($bookReq);
$tBook = round((microtime(true) - $t0) * 1000, 2);
$results['Booking Create (with Advance)'] = $tBook;
echo "4. Booking Create Save Time: {$tBook} ms\n";

// 5. Benchmark Standalone Payment Save
$bookingModel = new Booking();
$lastBooking = $pdo->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$invoiceModel = new Invoice();
$invoice = $invoiceModel->findByBookingId((int)$lastBooking['id']);

$billingService = new BillingService();
$t0 = microtime(true);
$payment = $billingService->recordPayment(
    (int)$invoice['id'],
    500.00,
    'cash',
    'TXN-BENCH-' . time(),
    'idemp_pay_' . time() . rand(100, 999),
    'Benchmark payment test'
);
$tPay = round((microtime(true) - $t0) * 1000, 2);
$results['Payment Record'] = $tPay;
echo "5. Payment Save Time: {$tPay} ms\n";

// 6. Benchmark Expense Save
$financeController = new FinanceController();
$expReq = new Request([], [
    'title'          => 'Benchmark Office Supplies ' . time(),
    'amount'         => 750.00,
    'category'       => 'office_supplies',
    'payment_method' => 'cash',
    'expense_date'   => date('Y-m-d'),
    'status'         => 'paid',
], [], [], ['REQUEST_METHOD' => 'POST']);
$t0 = microtime(true);
$expResp = $financeController->storeExpense($expReq);
$tExp = round((microtime(true) - $t0) * 1000, 2);
$results['Expense Create'] = $tExp;
echo "6. Expense Save Time: {$tExp} ms\n";

// 7. Benchmark Driver Assignment Save
// Ensure an available vehicle and chauffeur exist
$vehicleId = (int)$pdo->query("SELECT id FROM vehicles WHERE status = 'available' LIMIT 1")->fetchColumn();
if (!$vehicleId) {
    $vModel = new Vehicle();
    $vehicleId = $vModel->create([
        'vehicle_number' => 'MH01-BENCH-' . rand(100, 999),
        'make_model'     => 'Toyota Innova',
        'vehicle_type'   => 'suv',
        'status'         => 'available',
        'created_at'     => date('Y-m-d H:i:s'),
        'updated_at'     => date('Y-m-d H:i:s'),
    ]);
}
$driverProfileId = (int)$pdo->query("SELECT id FROM driver_profiles WHERE duty_status = 'available' LIMIT 1")->fetchColumn();
if (!$driverProfileId) {
    $driverProfileId = (int)$pdo->query("SELECT id FROM driver_profiles LIMIT 1")->fetchColumn();
}
$dispatchController = new DispatchController();
$dispReq = new Request([], [
    'booking_id' => (int)$lastBooking['id'],
    'vehicle_id' => $vehicleId,
    'driver_id'  => $driverProfileId,
], [], [], ['REQUEST_METHOD' => 'POST']);
$t0 = microtime(true);
$dispResp = $dispatchController->assign($dispReq);
$tDisp = round((microtime(true) - $t0) * 1000, 2);
$results['Driver Assignment'] = $tDisp;
echo "7. Driver Assignment Save Time: {$tDisp} ms\n";

// 8. Benchmark Company Profile Save
$companyController = new CompanyController();
$compReq = new Request([], [
    'company_name' => 'MANASWI Tours & Travels',
    'owner_name'   => 'System Administrator',
    'tagline'      => 'Luxury Chauffeur & Tour Experiences',
    'email'        => 'contact@manaswitours.com',
    'phone'        => '+91 9876543210',
    'city'         => 'Jaipur',
    'state'        => 'Rajasthan',
    'country'      => 'India',
], [], [], ['REQUEST_METHOD' => 'POST']);
$t0 = microtime(true);
$compResp = $companyController->save($compReq);
$tComp = round((microtime(true) - $t0) * 1000, 2);
$results['Company Profile Save'] = $tComp;
echo "8. Company Profile Save Time: {$tComp} ms\n";

    echo "\n====================================================================\n";
    echo " BENCHMARK SUMMARY\n";
    echo "====================================================================\n";
    foreach ($results as $operation => $duration) {
        echo sprintf(" %-30s : %8.2f ms\n", $operation, $duration);
    }
    echo "====================================================================\n";
} catch (\Throwable $e) {
    echo "\n[BENCHMARK EXCEPTION]: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
