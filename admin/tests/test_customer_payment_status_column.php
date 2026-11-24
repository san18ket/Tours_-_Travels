<?php
/**
 * Test Suite: Customer Directory Payment Status Column
 *
 * Verifies:
 * 1. Replacement of Address column with Payment Status column in admin/office/customers
 * 2. Proper calculation of payment status:
 *    - 'paid' when all bookings/invoices are fully paid
 *    - 'partially_paid' when partial balance remains
 *    - 'unpaid' when balance is due
 *    - 'no_bookings' when customer has 0 bookings
 * 3. Professional display badges and due amounts
 * 4. Desktop table and mobile card integration
 * 5. Full Controller render check
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Customer;
use App\Controllers\CustomerController;
use App\Core\Request;

$pdo = Database::getConnection();
$customerModel = new Customer();

$passed = 0;
$failed = 0;

function it(string $description, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo "[PASS] " . $description . PHP_EOL;
        $passed++;
    } catch (\Throwable $e) {
        echo "[FAIL] " . $description . " - ERROR: " . $e->getMessage() . PHP_EOL;
        $failed++;
    }
}

echo "========================================================" . PHP_EOL;
echo "  CUSTOMER PAYMENT STATUS COLUMN VERIFICATION SUITE     " . PHP_EOL;
echo "========================================================" . PHP_EOL;

// 1. Verify Customer::getPaginated populates payment status fields
it("Customer::getPaginated populates payment status summary fields", function() use ($customerModel) {
    $customers = $customerModel->getPaginated(null, null, 1, 20);
    if (empty($customers)) {
        throw new \Exception("Expected customers in database, got 0");
    }

    foreach ($customers as $c) {
        if (!isset($c['payment_status'])) {
            throw new \Exception("Missing 'payment_status' key in customer ID {$c['id']}");
        }
        if (!isset($c['payment_status_label'])) {
            throw new \Exception("Missing 'payment_status_label' key in customer ID {$c['id']}");
        }
        if (!isset($c['total_billed']) || !isset($c['total_paid']) || !isset($c['total_due'])) {
            throw new \Exception("Missing financial amount keys in customer ID {$c['id']}");
        }
        if (!in_array($c['payment_status'], ['paid', 'partially_paid', 'unpaid', 'no_bookings'], true)) {
            throw new \Exception("Invalid payment_status '{$c['payment_status']}' in customer ID {$c['id']}");
        }
    }
});

// 2. Verify accurate payment status derivation across scenarios
it("Customer payment status calculation accurately handles all states", function() use ($pdo, $customerModel) {
    $rand = bin2hex(random_bytes(4));

    // A. No bookings customer
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Test Cust NoBookings', '9666600001', 'nobook_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-NB-{$rand}"]);
    $cIdNoBook = (int)$pdo->lastInsertId();

    // B. Paid customer
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Test Cust Paid', '9666600002', 'paid_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-PD-{$rand}"]);
    $cIdPaid = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 750, 'completed', NOW(), NOW())")
        ->execute(["BK-PD-{$rand}", $cIdPaid]);
    $bIdPaid = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 750, 750, 750, 'paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-PD-{$rand}", $bIdPaid, $cIdPaid]);
    $invIdPaid = (int)$pdo->lastInsertId();

    // C. Partially paid customer
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Test Cust Partial', '9666600003', 'part_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-PT-{$rand}"]);
    $cIdPartial = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 1000, 'completed', NOW(), NOW())")
        ->execute(["BK-PT-{$rand}", $cIdPartial]);
    $bIdPartial = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 1000, 1000, 400, 'partially_paid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-PT-{$rand}", $bIdPartial, $cIdPartial]);
    $invIdPartial = (int)$pdo->lastInsertId();

    // D. Unpaid / Due customer
    $pdo->prepare("INSERT INTO `customers` (`customer_code`, `name`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES (?, 'Test Cust Unpaid', '9666600004', 'unpd_{$rand}@test.com', 'active', NOW(), NOW())")
        ->execute(["CUST-UN-{$rand}"]);
    $cIdUnpaid = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `bookings` (`booking_code`, `customer_profile_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES (?, ?, 'A', 'B', CURDATE(), CURDATE(), 500, 'completed', NOW(), NOW())")
        ->execute(["BK-UN-{$rand}", $cIdUnpaid]);
    $bIdUnpaid = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_profile_id`, `subtotal`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `issued_at`, `created_at`) VALUES (?, ?, ?, 500, 500, 0, 'unpaid', CURDATE(), NOW(), NOW())")
        ->execute(["INV-UN-{$rand}", $bIdUnpaid, $cIdUnpaid]);
    $invIdUnpaid = (int)$pdo->lastInsertId();

    // Fetch and evaluate
    $testCustomers = $customerModel->getPaginated("{$rand}", null, 1, 10);
    $indexed = [];
    foreach ($testCustomers as $tc) {
        $indexed[(int)$tc['id']] = $tc;
    }

    // Verify No Bookings
    if (($indexed[$cIdNoBook]['payment_status'] ?? '') !== 'no_bookings') {
        throw new \Exception("Expected 'no_bookings', got " . var_export($indexed[$cIdNoBook]['payment_status'] ?? null, true));
    }
    if (($indexed[$cIdNoBook]['payment_status_label'] ?? '') !== 'No Bookings') {
        throw new \Exception("Expected label 'No Bookings', got {$indexed[$cIdNoBook]['payment_status_label']}");
    }

    // Verify Paid
    if (($indexed[$cIdPaid]['payment_status'] ?? '') !== 'paid') {
        throw new \Exception("Expected 'paid', got " . var_export($indexed[$cIdPaid]['payment_status'] ?? null, true));
    }
    if ((float)($indexed[$cIdPaid]['total_due'] ?? -1) > 0.009) {
        throw new \Exception("Expected total_due 0 for paid customer, got {$indexed[$cIdPaid]['total_due']}");
    }

    // Verify Partially Paid
    if (($indexed[$cIdPartial]['payment_status'] ?? '') !== 'partially_paid') {
        throw new \Exception("Expected 'partially_paid', got " . var_export($indexed[$cIdPartial]['payment_status'] ?? null, true));
    }
    if (abs((float)$indexed[$cIdPartial]['total_due'] - 600.0) > 0.01) {
        throw new \Exception("Expected total_due 600, got {$indexed[$cIdPartial]['total_due']}");
    }

    // Verify Unpaid
    if (($indexed[$cIdUnpaid]['payment_status'] ?? '') !== 'unpaid') {
        throw new \Exception("Expected 'unpaid', got " . var_export($indexed[$cIdUnpaid]['payment_status'] ?? null, true));
    }
    if (abs((float)$indexed[$cIdUnpaid]['total_due'] - 500.0) > 0.01) {
        throw new \Exception("Expected total_due 500, got {$indexed[$cIdUnpaid]['total_due']}");
    }

    // Clean up
    $pdo->prepare("DELETE FROM `invoices` WHERE `id` IN (?, ?, ?)")->execute([$invIdPaid, $invIdPartial, $invIdUnpaid]);
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` IN (?, ?, ?)")->execute([$bIdPaid, $bIdPartial, $bIdUnpaid]);
    $pdo->prepare("DELETE FROM `customers` WHERE `id` IN (?, ?, ?, ?)")->execute([$cIdNoBook, $cIdPaid, $cIdPartial, $cIdUnpaid]);
});

// 3. Verify customer index view template markup & CSS
it("Customer index view has Payment Status column header and removes Address column header", function() {
    $viewFile = __DIR__ . '/../app/Views/office/customers/index.php';
    $content = file_get_contents($viewFile);

    // 1. Verify <th> contains Payment Status
    if (!str_contains($content, 'table-col-payment') || !str_contains($content, 'table.payment_status')) {
        throw new \Exception("Missing table-col-payment or table.payment_status in <th>");
    }

    // 2. Verify <th> does NOT have Address
    if (preg_match('/<th[^>]*>\s*<\?=\s*e\(__\([\'"]table\.address[\'"]/i', $content)) {
        throw new \Exception("Table header still contains Address column!");
    }

    // 3. Verify <td> contains cust-payment-cell and cust-pay-badge
    if (!str_contains($content, 'cust-payment-cell') || !str_contains($content, 'cust-pay-badge')) {
        throw new \Exception("Table body missing cust-payment-cell or cust-pay-badge");
    }

    // 4. Verify CSS contains .cust-pay-badge and state classes
    if (!str_contains($content, '.cust-pay-badge.pay-paid') ||
        !str_contains($content, '.cust-pay-badge.pay-partially_paid') ||
        !str_contains($content, '.cust-pay-badge.pay-unpaid') ||
        !str_contains($content, '.cust-pay-badge.pay-no_bookings')) {
        throw new \Exception("CSS missing .cust-pay-badge styling definitions");
    }

    // 5. Verify mobile view card contains mv-pay-row
    if (!str_contains($content, 'mv-pay-row')) {
        throw new \Exception("Mobile view missing mv-pay-row payment badge");
    }
});

// 4. Verify CustomerController::index renders HTTP 200 with payment status
it("CustomerController::index renders successfully with Payment Status column", function() {
    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'Payment Status')) {
        throw new \Exception("Rendered HTML does not contain 'Payment Status' header");
    }
    if (!str_contains($html, 'cust-payment-cell')) {
        throw new \Exception("Rendered HTML does not contain 'cust-payment-cell'");
    }
    if (!str_contains($html, 'cust-pay-badge')) {
        throw new \Exception("Rendered HTML does not contain 'cust-pay-badge'");
    }
});

echo "========================================================" . PHP_EOL;
echo "  SUMMARY: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "========================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
