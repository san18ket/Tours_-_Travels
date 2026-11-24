<?php
/**
 * Automated Verification Test Suite for Invoice 82 Print and Digital Verification
 * Tests:
 * 1. Invoice 82 model details & billing items
 * 2. /office/invoices/82/print rendering (QR link to /invoice/verify/82, Trip Charges & Fare Calculation)
 * 3. /invoice/verify/82 rendering (Customer Details, Trip Details, Trip Charges & Fare Calculation, Payment Details)
 * 4. Invoices with extra charges (Parking, Toll, Permit, etc.)
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Invoice;
use App\Models\BookingBillingItem;

echo "============================================================\n";
echo "TEST SUITE: INVOICE 82 PRINT & DIGITAL VERIFICATION SLIP\n";
echo "============================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $description, ?string $extra = null): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] " . $description . "\n";
        $passCount++;
    } else {
        echo " [FAIL] " . $description . ($extra ? " -- " . $extra : "") . "\n";
        $failCount++;
    }
}

$invoiceModel = new Invoice();
$invoice = $invoiceModel->findWithFullDetails(82);

assertTest(!empty($invoice), "Invoice 82 must exist in database");

if (!$invoice) {
    echo "Aborting remaining tests because Invoice 82 does not exist.\n";
    exit(1);
}

// Check basic details of invoice 82
assertTest($invoice['id'] == 82, "Invoice ID is 82");
assertTest(!empty($invoice['booking_id']), "Invoice 82 has booking_id (ID: {$invoice['booking_id']})");
assertTest($invoice['customer_name'] === 'Sanket Sidurkar', "Customer name is Sanket Sidurkar");
assertTest(!empty($invoice['customer_code']), "Customer code is present: " . ($invoice['customer_code'] ?? ''));
assertTest(!empty($invoice['customer_phone']), "Customer phone is present: " . ($invoice['customer_phone'] ?? ''));
assertTest(!empty($invoice['customer_address']), "Customer address is present");
assertTest($invoice['pickup_address'] === 'nagpur', "Pickup address is nagpur");
assertTest($invoice['drop_address'] === 'mumbai', "Drop address is mumbai");
assertTest(!empty($invoice['billing_items']), "Invoice 82 has itemized billing items");
assertTest(count($invoice['billing_items']) >= 2, "Invoice 82 has at least 2 billing items (Distance + Daily)");

// Check billing items specifics
$hasDistance = false;
$hasDaily = false;
foreach ($invoice['billing_items'] as $it) {
    if ($it['charge_category'] === 'distance') $hasDistance = true;
    if ($it['charge_category'] === 'daily') $hasDaily = true;
}
assertTest($hasDistance, "Invoice 82 has distance charge item");
assertTest($hasDaily, "Invoice 82 has daily charge item");

// Check financial totals
assertTest((float)$invoice['subtotal'] == 26900.0, "Subtotal is 26900.00");
assertTest((float)$invoice['tax_amount'] == 1345.0, "GST is 1345.00 (5%)");
assertTest((float)$invoice['total_amount'] == 28245.0, "Grand total is 28245.00");
assertTest((float)$invoice['paid_amount'] == 0.0, "Paid amount is 0.00 (Unpaid / Advance Skipped)");

// Fetch payments and bank account
$paymentModel = new \App\Models\Payment();
$payments = $paymentModel->getPaymentsByInvoice(82);
$bankAccounts = (new \App\Models\FinanceAccount())->where("`status` = 'active' AND `account_type` = 'bank'", [], 'id ASC', 1);
$bankAccount = $bankAccounts[0] ?? null;

// TEST 1: Render /office/invoices/82/print
echo "\n--- TESTING /office/invoices/82/print VIEW RENDERING ---\n";
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$invoiceNum = $invoice['invoice_number'];
$issuedDate = date('d M Y', strtotime($invoice['issued_at']));
$dueDate = date('d M Y', strtotime($invoice['due_date']));
$show_stamp = true;
include __DIR__ . '/../app/Views/office/invoices/print.php';
$printOutput = ob_get_clean();

assertTest(!empty($printOutput), "Print view renders successfully without throwing errors");
assertTest(strpos($printOutput, 'INV-2026-00245') !== false, "Print view contains invoice number INV-2026-00245");
assertTest(strpos($printOutput, 'Sanket Sidurkar') !== false, "Print view contains customer name Sanket Sidurkar");
assertTest(strpos($printOutput, 'data-verify-link=') !== false, "Print view contains data-verify-link attribute");
assertTest(strpos($printOutput, '/invoice/verify/82') !== false, "Print view data-verify-link points to /invoice/verify/82");
assertTest(strpos($printOutput, 'Open Slip') !== false, "Print view contains clickable 'Open Slip' text below QR");
assertTest(strpos($printOutput, 'Click Here to Open Digital Slip') !== false, "Print view contains descriptive 'Click Here to Open Digital Slip' link");
assertTest(strpos($printOutput, 'tbtn-digital') !== false, "Print view toolbar contains digital view button");
assertTest(strpos($printOutput, 'Distance') !== false, "Print view JavaScript template handles Distance charge badge");
assertTest(strpos($printOutput, 'Daily Fleet') !== false, "Print view JavaScript template handles Daily Fleet badge");
assertTest(strpos($printOutput, 'Permit') !== false, "Print view JavaScript template handles Permit badge");
assertTest(strpos($printOutput, 'Extra Driver') !== false, "Print view JavaScript template handles Extra Driver badge");
assertTest(strpos($printOutput, 'Toll Fee') !== false, "Print view JavaScript template handles Toll Fee badge");
assertTest(strpos($printOutput, 'Parking') !== false, "Print view JavaScript template handles Parking badge");
assertTest(strpos($printOutput, 'Advance skipped') !== false, "Print view handles advance skipped notice for unpaid balance");

// TEST 2: Render /invoice/verify/82
echo "\n--- TESTING /invoice/verify/82 DIGITAL VERIFICATION VIEW RENDERING ---\n";
ob_start();
include __DIR__ . '/../app/Views/office/invoices/verify.php';
$verifyOutput = ob_get_clean();

assertTest(!empty($verifyOutput), "Verify view renders successfully without throwing errors");
assertTest(strpos($verifyOutput, 'INV-2026-00245') !== false, "Verify view contains invoice number INV-2026-00245");

// Customer Details assertions
assertTest(strpos($verifyOutput, 'Customer Details (Billed To)') !== false, "Verify view has Customer Details section");
assertTest(strpos($verifyOutput, 'Sanket Sidurkar') !== false, "Verify view shows customer name Sanket Sidurkar");
assertTest(strpos($verifyOutput, 'CUS-0001') !== false, "Verify view shows customer code CUS-0001");
assertTest(strpos($verifyOutput, '8799845236') !== false, "Verify view shows customer phone 8799845236");
assertTest(strpos($verifyOutput, 'tel:8799845236') !== false, "Verify view has click-to-call link for customer phone");
assertTest(strpos($verifyOutput, 'sanketsidurkar9@gmail.com') !== false, "Verify view shows customer email");
assertTest(strpos($verifyOutput, 'mailto:sanketsidurkar9@gmail.com') !== false, "Verify view has click-to-email link");
assertTest(strpos($verifyOutput, 'Khaperkheda') !== false, "Verify view shows customer address");

// Trip Details assertions
assertTest(strpos($verifyOutput, 'Trip Itinerary &amp; Fleet Logistics') !== false, "Verify view has Trip Itinerary & Fleet Logistics section");
assertTest(strpos($verifyOutput, 'nagpur') !== false, "Verify view shows pickup hub nagpur");
assertTest(strpos($verifyOutput, 'mumbai') !== false, "Verify view shows drop hub mumbai");
assertTest(strpos($verifyOutput, 'BK-2026-0DA216') !== false, "Verify view shows booking code BK-2026-0DA216");
assertTest(strpos($verifyOutput, 'Wedding') !== false, "Verify view shows trip category Wedding");
assertTest(strpos($verifyOutput, '20') !== false, "Verify view shows passenger count (20)");
assertTest(strpos($verifyOutput, '09:00 AM') !== false, "Verify view shows formatted pickup time 09:00 AM");
assertTest(strpos($verifyOutput, '4 Days') !== false, "Verify view shows trip duration 4 Days");

// Trip Charges & Fare Calculation assertions
assertTest(strpos($verifyOutput, 'Trip Charges &amp; Fare Calculation') !== false, "Verify view has Trip Charges & Fare Calculation section");
assertTest(strpos($verifyOutput, 'Distance Charges (75.00 KM)') !== false, "Verify view shows distance charge description");
assertTest(strpos($verifyOutput, '75.00 KM') !== false, "Verify view shows quantity 75.00 KM");
assertTest(strpos($verifyOutput, '₹12.00') !== false, "Verify view shows rate ₹12.00");
assertTest(strpos($verifyOutput, '900.00') !== false, "Verify view shows distance amount 900.00");
assertTest(strpos($verifyOutput, 'Daily Vehicle Charges (20 Days)') !== false, "Verify view shows daily vehicle charge description");
assertTest(strpos($verifyOutput, '20 Days') !== false || strpos($verifyOutput, '20.00 Days') !== false, "Verify view shows quantity 20 Days");
assertTest(strpos($verifyOutput, '1,300.00') !== false, "Verify view shows daily rate 1,300.00");
assertTest(strpos($verifyOutput, '26,000.00') !== false, "Verify view shows daily amount 26,000.00");
assertTest(strpos($verifyOutput, '26,900.00') !== false, "Verify view shows Subtotal ₹26,900.00");
assertTest(strpos($verifyOutput, '1,345.00') !== false, "Verify view shows GST ₹1,345.00 (5%)");
assertTest(strpos($verifyOutput, '28,245.00') !== false, "Verify view shows Grand Total ₹28,245.00");

// Payment Details assertions
assertTest(strpos($verifyOutput, 'Payment Details &amp; Settlement Terms') !== false, "Verify view has Payment Details & Settlement Terms section");
assertTest(strpos($verifyOutput, 'Advance Payment Skipped &mdash; Payment To Be Collected Later') !== false, "Verify view has advance skipped reassurance notice");
assertTest(strpos($verifyOutput, 'Zero advance was collected during booking creation') !== false, "Verify view explains zero advance collected");
assertTest(strpos($verifyOutput, 'Total Bill') !== false, "Verify view has Total Bill summary card");
assertTest(strpos($verifyOutput, 'Remaining Balance Due') !== false, "Verify view has Remaining Balance Due card");

// Official Documents & Actions assertions
assertTest(strpos($verifyOutput, 'Print Official A4 Invoice') !== false, "Verify view has Print Official A4 Invoice button");
assertTest(strpos($verifyOutput, '/invoice/verify/82/print') !== false, "Print button points to /invoice/verify/82/print");
assertTest(strpos($verifyOutput, 'verifyQrCanvas') !== false, "Verify view has authenticity QR code container");
assertTest(strpos($verifyOutput, 'copySlipLink') !== false, "Verify view has copy slip link action");

// TEST 3: Test with an invoice that has extra charges (e.g. Parking or Permit)
echo "\n--- TESTING INVOICE WITH EXTRA CHARGES (PARKING / TOLL / PERMIT) ---\n";
$extraItemInvoice = (new Invoice())->findWithFullDetails(81);
if ($extraItemInvoice) {
    ob_start();
    $invoice = $extraItemInvoice;
    include __DIR__ . '/../app/Views/office/invoices/verify.php';
    $extraOutput = ob_get_clean();
    assertTest(!empty($extraOutput), "Invoice 81 renders successfully");
    assertTest(strpos($extraOutput, 'Parking') !== false, "Invoice 81 renders Parking badge and charge");
} else {
    echo " [INFO] Invoice 81 not present, skipped extra items test.\n";
}

echo "\n============================================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "============================================================\n";

if ($failCount > 0) {
    exit(1);
}
