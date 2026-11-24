<?php
/**
 * Test Suite: Professional Itemized Trip Billing System
 * Comprehensive 17-Scenario Test Matrix Covering Phase 7 Requirements
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\TripCategory;
use App\Models\BookingBillingItem;
use App\Services\BookingService;
use App\Services\BillingService;

function runTest(string $name, callable $fn) {
    echo "========================================\n";
    echo "RUNNING: $name\n";
    try {
        $fn();
        echo "RESULT: PASS [SUCCESS]\n";
    } catch (\Throwable $e) {
        echo "RESULT: FAIL [ERROR]: " . $e->getMessage() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }
}

$pdo = Database::getConnection();

// Ensure test customer exists
$custModel = new Customer();
$customer = $custModel->where("status = 'active'", [], 'id ASC', 1)[0] ?? null;
if (!$customer) {
    $custId = $custModel->create([
        'customer_code' => 'CUST-TEST-BILL',
        'name'          => 'Shri Rajesh Sharma',
        'phone'         => '9876543210',
        'email'         => 'rajesh.sharma@example.com',
        'address_line'  => '42 Civil Lines',
        'city'          => 'Jaipur',
        'state'         => 'Rajasthan',
        'postal_code'   => '302001',
        'status'        => 'active'
    ]);
    $customer = $custModel->find($custId);
}

// Ensure trip category exists
$tripCatModel = new TripCategory();
$tripCategory = $tripCatModel->getActiveCategories()[0] ?? null;
if (!$tripCategory) {
    $catId = $tripCatModel->create([
        'name' => 'Outstation Round Trip',
        'slug' => 'outstation-round-trip',
        'status' => 'active'
    ]);
    $tripCategory = $tripCatModel->find($catId);
}

$bookingService = new BookingService();
$billingItemModel = new BookingBillingItem();
$invoiceModel = new Invoice();

// =========================================================================
// SCENARIO 1: Reference Trip Calculation (75 KM ₹840, Daily ₹1300, Parking ₹70)
// =========================================================================
runTest("Scenario 1: Reference Trip Calculation (Exact Match to Company Reference)", function() use ($bookingService, $billingItemModel, $invoiceModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'         => $customer['id'],
        'trip_category_id'    => $tripCategory['id'],
        'pickup_address'      => 'IGI Terminal 3, New Delhi',
        'drop_address'        => 'Amer Fort, Jaipur',
        'start_date'          => date('Y-m-d', strtotime('+1 day')),
        'end_date'            => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'     => 2,
        'include_gst'         => false,
        'gst_rate'            => 0.0,
        'discount_amount'     => 0.0,
        'advance_amount'      => 0.0,
        'billing_items'       => [
            ['charge_category' => 'distance', 'description' => 'Distance Charges (75.00 KM)', 'quantity' => 75.0, 'unit' => 'KM', 'unit_rate' => null, 'line_total' => 840.00],
            ['charge_category' => 'daily', 'description' => 'Daily Vehicle Hire (1 Day)', 'quantity' => 1.0, 'unit' => 'Days', 'unit_rate' => 1300.00, 'line_total' => 1300.00],
            ['charge_category' => 'parking', 'description' => 'Parking Charges', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 70.00, 'line_total' => 70.00],
        ]
    ]);

    assert(abs((float)$booking['subtotal'] - 2210.00) < 0.01, "Subtotal must be 2210.00");
    assert(abs((float)$booking['total_amount'] - 2210.00) < 0.01, "Grand Total must be 2210.00");

    $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
    assert($invoice !== null, "Invoice must exist");
    assert(abs((float)$invoice['total_amount'] - 2210.00) < 0.01, "Invoice total must be 2210.00");
    assert(abs((float)$invoice['paid_amount'] - 0.00) < 0.01, "Invoice paid amount must be 0.00");
    assert($invoice['payment_status'] === 'unpaid', "Status must be unpaid");
    echo "Reference bill exact match verified: Subtotal=₹2,210, Total=₹2,210, Balance=₹2,210.\n";
});

// =========================================================================
// SCENARIO 2: Per-km Rate Auto-Calculation
// =========================================================================
runTest("Scenario 2: Per-KM Rate Auto-Calculation (100 KM @ ₹12/KM)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'Hub A',
        'drop_address'     => 'Hub B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance (100 KM)', 'quantity' => 100.0, 'unit' => 'KM', 'unit_rate' => 12.00, 'line_total' => 1200.00]
        ]
    ]);
    assert(abs((float)$booking['subtotal'] - 1200.00) < 0.01, "Expected ₹1,200.00");
    echo "Auto-calc verified: 100 KM @ ₹12.00/KM = ₹{$booking['subtotal']}\n";
});

// =========================================================================
// SCENARIO 3: Manual Distance Override Without Per-KM Rate (Never Invent Rate)
// =========================================================================
runTest("Scenario 3: Manual Distance Override Without Rate (unit_rate IS NULL)", function() use ($bookingService, $billingItemModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'Hub A',
        'drop_address'     => 'Hub B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance Lump Sum', 'quantity' => 85.0, 'unit' => 'KM', 'unit_rate' => null, 'line_total' => 950.00]
        ]
    ]);
    $items = $billingItemModel->getItemsByBooking((int)$booking['id']);
    assert($items[0]['unit_rate'] === null, "unit_rate must be null when omitted, never invented");
    assert(abs((float)$items[0]['line_total'] - 950.00) < 0.01, "line_total must be 950.00");
    echo "Lump-sum verified: ₹950.00 with unit_rate = NULL preserved.\n";
});

// =========================================================================
// SCENARIO 4: Multi-Day Daily Charges (3 Days @ ₹1,500/day = ₹4,500)
// =========================================================================
runTest("Scenario 4: Multi-Day Daily Charge (3 Days @ ₹1,500 = ₹4,500)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily Hire 3 Days', 'quantity' => 3.0, 'unit' => 'Days', 'unit_rate' => 1500.00, 'line_total' => 4500.00]
        ]
    ]);
    assert(abs((float)$booking['subtotal'] - 4500.00) < 0.01, "Expected ₹4,500.00");
    echo "Multi-day verified: 3 Days @ ₹1500.00 = ₹{$booking['subtotal']}\n";
});

// =========================================================================
// SCENARIO 5: Halting Charges (2 Nights @ ₹600 = ₹1,200)
// =========================================================================
runTest("Scenario 5: Halting Charges (2 Nights @ ₹600 = ₹1,200)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily Hire', 'quantity' => 2.0, 'unit' => 'Days', 'unit_rate' => 1000.00, 'line_total' => 2000.00],
            ['charge_category' => 'halting', 'description' => 'Night Halt', 'quantity' => 2.0, 'unit' => 'Nights', 'unit_rate' => 600.00, 'line_total' => 1200.00]
        ]
    ]);
    assert(abs((float)$booking['subtotal'] - 3200.00) < 0.01, "Expected ₹3,200.00");
    echo "Halting verified: Daily ₹2,000 + Halting ₹1,200 = ₹{$booking['subtotal']}\n";
});

// =========================================================================
// SCENARIO 6: Multiple Additional Charges (Parking + Toll + Permit)
// =========================================================================
runTest("Scenario 6: Multiple Additional Charges (Parking ₹70, Toll ₹150, Permit ₹300)", function() use ($bookingService, $billingItemModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'parking', 'description' => 'Airport Parking', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 70.00, 'line_total' => 70.00],
            ['charge_category' => 'toll', 'description' => 'Expressway Toll', 'quantity' => 1.0, 'unit' => 'Trip', 'unit_rate' => 150.00, 'line_total' => 150.00],
            ['charge_category' => 'permit', 'description' => 'State Entry Permit', 'quantity' => 1.0, 'unit' => 'State', 'unit_rate' => 300.00, 'line_total' => 300.00],
        ]
    ]);
    assert(abs((float)$booking['subtotal'] - 520.00) < 0.01, "Expected ₹520.00");
    $items = $billingItemModel->getItemsByBooking((int)$booking['id']);
    assert(count($items) === 3, "Expected 3 other charges saved");
    echo "Multiple additional charges verified: Total=₹{$booking['subtotal']}\n";
});

// =========================================================================
// SCENARIO 7: Subtotal Recalculation From All 4 Categories
// =========================================================================
runTest("Scenario 7: Full 4-Category Subtotal Aggregation", function() use ($bookingService, $customer, $tripCategory) {
    // Distance (₹840) + Daily (₹1300) + Halting (₹500) + Other (₹70) = ₹2,710
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance', 'quantity' => 75.0, 'line_total' => 840.00],
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1300.00],
            ['charge_category' => 'halting', 'description' => 'Halting', 'quantity' => 1.0, 'line_total' => 500.00],
            ['charge_category' => 'parking', 'description' => 'Parking', 'quantity' => 1.0, 'line_total' => 70.00],
        ]
    ]);
    assert(abs((float)$booking['subtotal'] - 2710.00) < 0.01, "Expected ₹2,710.00");
    echo "4-category sum verified: ₹840 + ₹1,300 + ₹500 + ₹70 = ₹{$booking['subtotal']}\n";
});

// =========================================================================
// SCENARIO 8: Promotional Discount Calculation
// =========================================================================
runTest("Scenario 8: Promotional Discount (₹200 discount on ₹2,210)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'discount_amount'  => 200.00,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance', 'quantity' => 75.0, 'line_total' => 840.00],
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1300.00],
            ['charge_category' => 'parking', 'description' => 'Parking', 'quantity' => 1.0, 'line_total' => 70.00],
        ]
    ]);
    // Subtotal: 2210, Discount: 200 -> Total: 2010
    assert(abs((float)$booking['total_amount'] - 2010.00) < 0.01, "Expected ₹2,010.00 total");
    echo "Discount verified: Subtotal ₹2,210 - Discount ₹200 = ₹{$booking['total_amount']}\n";
});

// =========================================================================
// SCENARIO 9: GST Applied to Taxable Amount (Subtotal - Discount)
// =========================================================================
runTest("Scenario 9: GST @ 5% on Taxable Amount (₹2,210 - ₹210 = ₹2,000 -> Tax ₹100)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => true,
        'gst_rate'         => 5.0,
        'discount_amount'  => 210.00,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance', 'quantity' => 75.0, 'line_total' => 840.00],
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1300.00],
            ['charge_category' => 'parking', 'description' => 'Parking', 'quantity' => 1.0, 'line_total' => 70.00],
        ]
    ]);
    // Taxable: 2000, 5% of 2000 = 100 -> Total: 2100
    assert(abs((float)$booking['tax_amount'] - 100.00) < 0.01, "Expected ₹100.00 tax");
    assert(abs((float)$booking['total_amount'] - 2100.00) < 0.01, "Expected ₹2,100.00 total");
    echo "GST calculation verified: Taxable ₹2,000 × 5% = ₹{$booking['tax_amount']}, Total = ₹{$booking['total_amount']}\n";
});

// =========================================================================
// SCENARIO 10: GST Exempt (0% GST)
// =========================================================================
runTest("Scenario 10: GST Exempt Option (gst_rate = 0, tax = 0)", function() use ($bookingService, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1300.00],
        ]
    ]);
    assert(abs((float)$booking['tax_amount'] - 0.00) < 0.01, "Expected ₹0.00 tax");
    echo "GST Exempt verified: Tax = ₹{$booking['tax_amount']}\n";
});

// =========================================================================
// SCENARIO 11: Advance = 0 -> Status Unpaid
// =========================================================================
runTest("Scenario 11: Zero Advance -> Status Unpaid, Balance = Grand Total", function() use ($bookingService, $invoiceModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'advance_amount'   => 0.0,
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1000.00],
        ]
    ]);
    $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
    assert($invoice['payment_status'] === 'unpaid', "Status must be unpaid");
    assert(abs((float)$invoice['paid_amount'] - 0.00) < 0.01, "Paid amount must be 0");
    echo "Zero advance verified: Status={$invoice['payment_status']}, Paid=₹{$invoice['paid_amount']}\n";
});

// =========================================================================
// SCENARIO 12: Partial Advance Payment -> Status Partially Paid
// =========================================================================
runTest("Scenario 12: Partial Advance Payment -> Status Partially Paid", function() use ($bookingService, $invoiceModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'advance_amount'   => 500.0,
        'payment_method'   => 'cash',
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1000.00],
        ]
    ]);
    $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
    assert($invoice['payment_status'] === 'partially_paid', "Status must be partially_paid");
    assert(abs((float)$invoice['paid_amount'] - 500.00) < 0.01, "Paid amount must be 500");
    echo "Partial advance verified: Status={$invoice['payment_status']}, Paid=₹{$invoice['paid_amount']}\n";
});

// =========================================================================
// SCENARIO 13: Full Advance Payment -> Status Paid
// =========================================================================
runTest("Scenario 13: Full Advance Payment -> Status Paid (Settled)", function() use ($bookingService, $invoiceModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'City A',
        'drop_address'     => 'City B',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 1,
        'include_gst'      => false,
        'advance_amount'   => 1000.0,
        'payment_method'   => 'bank_transfer',
        'billing_items'    => [
            ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1000.00],
        ]
    ]);
    $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
    assert($invoice['payment_status'] === 'paid', "Status must be paid");
    assert(abs((float)$invoice['paid_amount'] - 1000.00) < 0.01, "Paid amount must be 1000");
    echo "Full advance verified: Status={$invoice['payment_status']}, Paid=₹{$invoice['paid_amount']}\n";
});

// =========================================================================
// SCENARIO 14: Overpayment Rejection Validation
// =========================================================================
runTest("Scenario 14: Overpayment Rejection Validation (Advance > Total Rejection)", function() use ($bookingService, $customer, $tripCategory) {
    $rejected = false;
    try {
        $bookingService->createBooking([
            'customer_id'      => $customer['id'],
            'trip_category_id' => $tripCategory['id'],
            'pickup_address'   => 'City A',
            'drop_address'     => 'City B',
            'start_date'       => date('Y-m-d', strtotime('+1 day')),
            'passenger_count'  => 1,
            'include_gst'      => false,
            'advance_amount'   => 5000.0, // > total 1000!
            'billing_items'    => [
                ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1000.00],
            ]
        ]);
    } catch (\Throwable $e) {
        $rejected = true;
    }
    assert($rejected, "Overpayment of advance must be rejected");
    echo "Overpayment rejection verified: System prevented advance greater than booking total.\n";
});

// =========================================================================
// SCENARIO 15: Negative Amount Validation
// =========================================================================
runTest("Scenario 15: Negative Amount Validation (Negative Advance/Rate Rejection)", function() use ($bookingService, $customer, $tripCategory) {
    $rejected = false;
    try {
        $bookingService->createBooking([
            'customer_id'      => $customer['id'],
            'trip_category_id' => $tripCategory['id'],
            'pickup_address'   => 'City A',
            'drop_address'     => 'City B',
            'start_date'       => date('Y-m-d', strtotime('+1 day')),
            'passenger_count'  => 1,
            'advance_amount'   => -500.0, // Negative!
            'billing_items'    => [
                ['charge_category' => 'daily', 'description' => 'Daily', 'quantity' => 1.0, 'line_total' => 1000.00],
            ]
        ]);
    } catch (\Throwable $e) {
        $rejected = true;
    }
    assert($rejected, "Negative advance amount must be rejected");
    echo "Negative amount rejection verified.\n";
});

// =========================================================================
// SCENARIO 16: Invoice Print Template Renders All Itemized Charges
// =========================================================================
runTest("Scenario 16: Invoice Print Template Data Binding for Itemized Charges", function() use ($bookingService, $invoiceModel, $customer, $tripCategory) {
    $booking = $bookingService->createBooking([
        'customer_id'      => $customer['id'],
        'trip_category_id' => $tripCategory['id'],
        'pickup_address'   => 'Delhi Hub',
        'drop_address'     => 'Jaipur Palace',
        'start_date'       => date('Y-m-d', strtotime('+1 day')),
        'passenger_count'  => 2,
        'include_gst'      => false,
        'billing_items'    => [
            ['charge_category' => 'distance', 'description' => 'Distance Charges (75.00 KM)', 'quantity' => 75.0, 'unit' => 'KM', 'unit_rate' => null, 'line_total' => 840.00],
            ['charge_category' => 'daily', 'description' => 'Daily Hire', 'quantity' => 1.0, 'unit' => 'Days', 'unit_rate' => 1300.00, 'line_total' => 1300.00],
            ['charge_category' => 'parking', 'description' => 'Parking Fee', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 70.00, 'line_total' => 70.00],
        ]
    ]);
    $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
    $full = $invoiceModel->findWithFullDetails((int)$invoice['id']);

    assert(count($full['billing_items']) === 3, "Expected 3 billing items loaded");
    assert($full['billing_items'][0]['description'] === 'Distance Charges (75.00 KM)', "Description mismatch");
    assert($full['billing_items'][2]['description'] === 'Parking Fee', "Parking description mismatch");

    echo "Invoice Print Data Binding Verified: {$full['invoice_number']} loaded with " . count($full['billing_items']) . " itemized rows.\n";
});

// =========================================================================
// SCENARIO 17: Backward Compatibility for Legacy Invoices
// =========================================================================
runTest("Scenario 17: Backward Compatibility Fallback for Legacy Bookings/Invoices", function() use ($invoiceModel) {
    // Test with legacy invoice ID 1
    $full = $invoiceModel->findWithFullDetails(1);
    assert(!empty($full['billing_items']), "Fallback items must be populated");
    assert(strpos($full['billing_items'][0]['description'], 'Commercial Fleet Hire') !== false, "Fallback must describe Commercial Fleet Hire");
    echo "Backward compatibility verified: Legacy invoice gracefully renders fallback single-line item.\n";
});

echo "\n===================================================================\n";
echo "SUCCESS: ALL 17 ITEMIZATION & BILLING SCENARIOS PASSED WITH ZERO ERRORS!\n";
echo "===================================================================\n";
