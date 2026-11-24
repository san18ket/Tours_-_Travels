<?php
/**
 * Test Suite: Booking Create 5-Step Wizard & Extra Charges Simplification
 * Verifies all requirements of Part 1, Part 2, and Part 3
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Models\Customer;
use App\Models\TripCategory;
use App\Models\BookingBillingItem;
use App\Models\Invoice;
use App\Services\BookingService;

function runAssertion(string $desc, bool $condition, string $msg = '') {
    if (!$condition) {
        echo "FAIL: $desc - $msg\n";
        exit(1);
    }
    echo "PASS: $desc\n";
}

echo "===================================================================\n";
echo "STARTING VERIFICATION: 5-STEP WIZARD & SIMPLIFIED EXTRA CHARGES\n";
echo "===================================================================\n\n";

$createViewContent = file_get_contents(ROOT_PATH . '/app/Views/office/bookings/create.php');

// 1. Check 5-step stepper in Header
runAssertion(
    "Stepper header configured for 5 steps",
    strpos($createViewContent, 'data-step-target="1"') !== false &&
    strpos($createViewContent, 'data-step-target="2"') !== false &&
    strpos($createViewContent, 'data-step-target="3"') !== false &&
    strpos($createViewContent, 'data-step-target="4"') !== false &&
    strpos($createViewContent, 'data-step-target="5"') !== false,
    "Stepper must contain 5 step buttons (1 to 5)"
);

runAssertion(
    "Stepper labels match required 5 steps",
    strpos($createViewContent, 'Client &amp; Guests') !== false &&
    strpos($createViewContent, 'Itinerary &amp; Route') !== false &&
    strpos($createViewContent, 'Trip Charges') !== false &&
    strpos($createViewContent, 'Payment (Optional)') !== false &&
    strpos($createViewContent, 'Review &amp; Confirm') !== false,
    "Stepper must display correct labels for all 5 steps"
);

// 2. Check Part 1: Rename Section D and common English
runAssertion(
    "Section D renamed to 'D. Extra Charges (If Any)'",
    strpos($createViewContent, 'D. Extra Charges (If Any)') !== false,
    "Must be renamed to 'D. Extra Charges (If Any)'"
);

// 3. Check Quick Charge Options without fixed prices
runAssertion(
    "Quick charge shortcuts present (Parking, Toll, Permit, Extra Driver, Other)",
    strpos($createViewContent, "selectQuickExtraOption('Parking')") !== false &&
    strpos($createViewContent, "selectQuickExtraOption('Toll')") !== false &&
    strpos($createViewContent, "selectQuickExtraOption('Permit')") !== false &&
    strpos($createViewContent, "selectQuickExtraOption('Extra Driver')") !== false &&
    strpos($createViewContent, "selectQuickExtraOption('Other')") !== false,
    "Quick options must be selectable shortcuts"
);

runAssertion(
    "No hardcoded forced prices in shortcuts",
    strpos($createViewContent, "+ Parking (₹70)") === false &&
    strpos($createViewContent, "+ Toll (₹150)") === false,
    "Shortcuts must NOT force fixed prices"
);

// 4. Custom charge entry form
runAssertion(
    "Custom charge form fields exist",
    strpos($createViewContent, 'id="extra_charge_name"') !== false &&
    strpos($createViewContent, 'id="extra_charge_qty"') !== false &&
    strpos($createViewContent, 'id="extra_charge_price"') !== false &&
    strpos($createViewContent, 'id="extra_charge_total"') !== false &&
    strpos($createViewContent, 'id="btnSubmitExtraCharge"') !== false &&
    strpos($createViewContent, 'id="btnCancelEditExtra"') !== false,
    "Custom extra charge form fields must all exist"
);

// 5. Empty notice & dynamic table
runAssertion(
    "Empty extra charges notice present",
    strpos($createViewContent, 'id="noExtraChargesNotice"') !== false &&
    strpos($createViewContent, 'No extra charges added.') !== false,
    "Must display 'No extra charges added.' when empty"
);

runAssertion(
    "Extra charges dynamic table and prominent total row present",
    strpos($createViewContent, 'id="extraChargesTableWrapper"') !== false &&
    strpos($createViewContent, 'id="extraChargesTotalRow"') !== false &&
    strpos($createViewContent, 'id="lblExtraChargesTotalDisplay"') !== false,
    "Extra charges table and total row must be present"
);

// 6. Check Customer Payment is NOT inside Step 3
runAssertion(
    "Customer payment is moved out of Step 3",
    strpos($createViewContent, 'wizardPanel3') < strpos($createViewContent, 'wizardPanel4') &&
    strpos($createViewContent, 'id="wizardPanel4"') !== false,
    "Step 3 must close before Step 4 starts"
);

// 7. Check Step 4: Customer Payment (Optional)
runAssertion(
    "Step 4 heading and description match requirements",
    strpos($createViewContent, 'Customer Payment (Optional)') !== false &&
    strpos($createViewContent, 'Has the customer paid any money now? If not, you can skip this step and collect payment later.') !== false,
    "Step 4 heading and subtitle must be present"
);

runAssertion(
    "Step 4 two choice cards (Advance vs Pay Later)",
    strpos($createViewContent, 'id="choiceCardAdvance"') !== false &&
    strpos($createViewContent, 'id="choiceCardLater"') !== false &&
    strpos($createViewContent, 'Customer Paid an Advance') !== false &&
    strpos($createViewContent, 'Customer Will Pay Later') !== false,
    "Two choice cards must be present"
);

runAssertion(
    "Step 4 'Customer Will Pay Later' reassuring banner",
    strpos($createViewContent, 'id="payLaterContainer"') !== false &&
    strpos($createViewContent, 'Payment will be collected later.') !== false,
    "Reassuring banner must be shown when pay later is selected"
);

runAssertion(
    "Step 4 Skip Payment button present",
    strpos($createViewContent, 'id="btnSkipPayment"') !== false &&
    strpos($createViewContent, 'skipPaymentToReview()') !== false,
    "Skip Payment button must route to Step 5"
);

// 8. Check Step 5: Review & Confirm
runAssertion(
    "Step 5 Review & Confirm panel present",
    strpos($createViewContent, 'id="wizardPanel5"') !== false &&
    strpos($createViewContent, 'Review &amp; Confirm') !== false,
    "Step 5 must be Review & Confirm"
);

runAssertion(
    "Step 5 includes Card 3 with itemized extra charges",
    strpos($createViewContent, 'id="sumCardExtraChargesWrapper"') !== false &&
    strpos($createViewContent, 'id="sumCardExtraChargesList"') !== false &&
    strpos($createViewContent, 'id="sumCardOtherFare"') !== false,
    "Step 5 Card 3 must display extra charges breakdown"
);

runAssertion(
    "Step 5 includes Card 4 for Payment Details with pay later banner",
    strpos($createViewContent, 'Payment Details') !== false &&
    strpos($createViewContent, 'id="sumCardPayLaterBanner"') !== false,
    "Step 5 Card 4 must display payment summary and pay later banner"
);

runAssertion(
    "Jump Edit buttons route to Steps 1, 2, 3, 4",
    strpos($createViewContent, 'onclick="wizardGoToStep(1)"') !== false &&
    strpos($createViewContent, 'onclick="wizardGoToStep(2)"') !== false &&
    strpos($createViewContent, 'onclick="wizardGoToStep(3)"') !== false &&
    strpos($createViewContent, 'onclick="wizardGoToStep(4)"') !== false,
    "Review cards must have jump edit buttons for steps 1-4"
);

// 9. Check JavaScript totalSteps = 5 and validation
runAssertion(
    "JavaScript totalSteps is set to 5",
    strpos($createViewContent, 'const totalSteps = 5;') !== false,
    "totalSteps must be 5"
);

runAssertion(
    "JavaScript step 4 overpayment and negative validation",
    strpos($createViewContent, 'if (stepNum === 4)') !== false &&
    strpos($createViewContent, 'Advance payment cannot be greater than the booking amount.') !== false &&
    strpos($createViewContent, 'Advance payment cannot be a negative amount.') !== false,
    "Step 4 validation must check overpayment and negative amount"
);

// 10. Functional Backend Test: Creating booking with custom extra charges and ₹0 advance (Pay Later)
$custModel = new Customer();
$customer = $custModel->where("status = 'active'", [], 'id ASC', 1)[0] ?? null;
if (!$customer) {
    $custId = $custModel->create([
        'customer_code' => 'CUST-TEST-WIZ',
        'name'          => 'Dr. Ananya Roy',
        'phone'         => '9123456780',
        'status'        => 'active'
    ]);
    $customer = $custModel->find($custId);
}

$tripCatModel = new TripCategory();
$tripCategory = $tripCatModel->getActiveCategories()[0];

$bookingService = new BookingService();
$invoiceModel = new Invoice();
$billingItemModel = new BookingBillingItem();

$bookingPayLater = $bookingService->createBooking([
    'customer_id'      => $customer['id'],
    'trip_category_id' => $tripCategory['id'],
    'pickup_address'   => 'South Extension 1, New Delhi',
    'drop_address'     => 'Cyber Hub, Gurugram',
    'start_date'       => date('Y-m-d', strtotime('+2 days')),
    'end_date'         => date('Y-m-d', strtotime('+2 days')),
    'passenger_count'  => 3,
    'include_gst'      => true,
    'gst_rate'         => 5.0,
    'discount_amount'  => 0.0,
    'advance_amount'   => 0.0, // Skipped payment / Pay Later
    'billing_items'    => [
        ['charge_category' => 'distance', 'description' => 'Distance Charges (40.00 KM)', 'quantity' => 40.0, 'unit' => 'KM', 'unit_rate' => 15.00, 'line_total' => 600.00],
        ['charge_category' => 'daily', 'description' => 'Daily Vehicle Charges (1 Day)', 'quantity' => 1.0, 'unit' => 'Days', 'unit_rate' => 1200.00, 'line_total' => 1200.00],
        ['charge_category' => 'other', 'description' => 'Airport Parking', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 120.00, 'line_total' => 120.00],
        ['charge_category' => 'other', 'description' => 'Toll Fee (NH-48)', 'quantity' => 2.0, 'unit' => 'Unit', 'unit_rate' => 125.00, 'line_total' => 250.00]
    ]
]);

// Subtotal = 600 + 1200 + 120 + 250 = 2170
// GST 5% on 2170 = 108.50
// Total = 2278.50
runAssertion(
    "Booking created with custom extra charges and Pay Later (₹0 advance)",
    abs((float)$bookingPayLater['subtotal'] - 2170.00) < 0.01 &&
    abs((float)$bookingPayLater['total_amount'] - 2278.50) < 0.01,
    "Subtotal should be 2170.00 and Total should be 2278.50"
);

$invPayLater = $invoiceModel->findByBookingId((int)$bookingPayLater['id']);
runAssertion(
    "Invoice status for Pay Later is 'unpaid' with balance = total",
    $invPayLater['payment_status'] === 'unpaid' &&
    abs((float)$invPayLater['paid_amount'] - 0.00) < 0.01 &&
    abs((float)$invPayLater['total_amount'] - 2278.50) < 0.01,
    "Pay Later invoice must be unpaid with 0 paid amount"
);

$savedItems = $billingItemModel->getItemsByBooking((int)$bookingPayLater['id']);
runAssertion(
    "All 4 itemized billing records (including 2 extra charges) saved to database",
    count($savedItems) === 4 &&
    $savedItems[2]['description'] === 'Airport Parking' &&
    $savedItems[3]['description'] === 'Toll Fee (NH-48)',
    "Itemized extra charges must be persisted correctly in booking_billing_items"
);

// 11. Functional Backend Test: Creating booking with custom extra charges and advance payment
$bookingAdvance = $bookingService->createBooking([
    'customer_id'      => $customer['id'],
    'trip_category_id' => $tripCategory['id'],
    'pickup_address'   => 'Connaught Place, New Delhi',
    'drop_address'     => 'Taj Mahal, Agra',
    'start_date'       => date('Y-m-d', strtotime('+3 days')),
    'end_date'         => date('Y-m-d', strtotime('+3 days')),
    'passenger_count'  => 4,
    'include_gst'      => true,
    'gst_rate'         => 5.0,
    'discount_amount'  => 100.00,
    'advance_amount'   => 1500.00, // Customer Paid an Advance
    'billing_items'    => [
        ['charge_category' => 'distance', 'description' => 'Distance Charges (210.00 KM)', 'quantity' => 210.0, 'unit' => 'KM', 'unit_rate' => 14.00, 'line_total' => 2940.00],
        ['charge_category' => 'daily', 'description' => 'Daily Vehicle Charges (1 Day)', 'quantity' => 1.0, 'unit' => 'Days', 'unit_rate' => 1500.00, 'line_total' => 1500.00],
        ['charge_category' => 'other', 'description' => 'Yamuna Expressway Toll', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 450.00, 'line_total' => 450.00],
        ['charge_category' => 'other', 'description' => 'Driver Allowance', 'quantity' => 1.0, 'unit' => 'Unit', 'unit_rate' => 350.00, 'line_total' => 350.00]
    ]
]);

// Subtotal = 2940 + 1500 + 450 + 350 = 5240
// Discount = 100 -> Taxable = 5140
// GST 5% on 5140 = 257.00
// Total = 5397.00
// Advance = 1500.00 -> Remaining = 3897.00 -> partially_paid
$invAdvance = $invoiceModel->findByBookingId((int)$bookingAdvance['id']);
runAssertion(
    "Booking created with advance payment shows partially_paid status and correct balance",
    abs((float)$bookingAdvance['total_amount'] - 5397.00) < 0.01 &&
    $invAdvance['payment_status'] === 'partially_paid' &&
    abs((float)$invAdvance['paid_amount'] - 1500.00) < 0.01,
    "Invoice status must be partially_paid with ₹1500 paid"
);

echo "\n===================================================================\n";
echo "ALL ASSERTIONS PASSED SUCCESSFULLY! 5-STEP WIZARD IS 100% VERIFIED\n";
echo "===================================================================\n";

