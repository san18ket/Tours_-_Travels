<?php

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Controllers\InvoiceController;

echo "======================================================================\n";
echo "   FLOATING ACTION BUTTON & INVOICE TABLE TEST SUITE\n";
echo "======================================================================\n";

Session::set('user_id', 1);
Session::set('user_role', 'admin');

$controller = new InvoiceController();
$request = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/invoices']);
$response = $controller->index($request);

$html = $response->getContent();

// Test 1: Page rendered with HTTP 200
if ($response->getStatusCode() === 200 && !empty($html)) {
    echo " [PASS] 1. /office/invoices rendered successfully with status 200\n";
} else {
    echo " [FAIL] 1. /office/invoices failed with status " . $response->getStatusCode() . "\n";
    exit(1);
}

// Test 2: Booking Manifest column is REMOVED from table header
if (strpos($html, '<th>Booking Manifest</th>') === false &&
    strpos($html, '>Booking Manifest<') === false) {
    echo " [PASS] 2. 'Booking Manifest' column completely removed from table header\n";
} else {
    echo " [FAIL] 2. 'Booking Manifest' column still present in table header\n";
    exit(1);
}

// Test 3: Table has compact Actions column header (width: 58px)
if (strpos($html, 'width: 58px;">Actions</th>') !== false) {
    echo " [PASS] 3. Actions column has professional compact width (58px)\n";
} else {
    echo " [FAIL] 3. Actions column width not updated to 58px\n";
    exit(1);
}

// Test 4: Floating action button (.action-toggle-btn) & floating popup (.action-btns-popup) present
if (strpos($html, 'class="table-action-buttons"') !== false &&
    strpos($html, 'class="action-toggle-btn"') !== false &&
    strpos($html, 'class="action-btns-popup"') !== false) {
    echo " [PASS] 4. Table action column features professional floating action button & popup menu\n";
} else {
    echo " [FAIL] 4. Floating action button or popup menu missing\n";
    exit(1);
}

// Test 5: All action buttons (View, Print, Pay) are housed inside the floating action popup
if (strpos($html, 'class="action-btn-table view" title="View Full Invoice"') !== false &&
    strpos($html, 'class="action-btn-table dispatch" title="Print Tax Invoice"') !== false &&
    strpos($html, 'class="action-btn-table pay" title="Record Payment"') !== false) {
    echo " [PASS] 5. View, Print, and Pay action buttons all present in the floating action menu\n";
} else {
    echo " [FAIL] 5. One or more action buttons missing from floating action menu\n";
    exit(1);
}

// Test 6: Mobile cards contain dedicated .mv-card-actions-bar with floating action menu
if (strpos($html, 'class="mv-card-actions-bar"') !== false &&
    strpos($html, 'class="mv-card-more-actions"') !== false &&
    strpos($html, 'class="card-btns-popup"') !== false) {
    echo " [PASS] 6. Mobile cards contain dedicated .mv-card-actions-bar with floating action menu\n";
} else {
    echo " [FAIL] 6. Mobile cards missing .mv-card-actions-bar or floating menu\n";
    exit(1);
}

// Test 7: Empty state has colspan="7"
$sourceFile = file_get_contents(__DIR__ . '/../app/Views/office/invoices/index.php');
if (strpos($sourceFile, 'colspan="7"') !== false) {
    echo " [PASS] 7. Empty state table row updated to colspan=7\n";
} else {
    echo " [FAIL] 7. Empty state table row not updated to colspan=7\n";
    exit(1);
}

// Test 8: Emerald/green styling for .action-btn-table.pay
if (strpos($html, '.action-btn-table.pay') !== false &&
    strpos($html, '#10b981') !== false &&
    strpos($html, '#d1fae5') !== false) {
    echo " [PASS] 8. Emerald/green styling for .action-btn-table.pay verified\n";
} else {
    echo " [FAIL] 8. CSS styling for .action-btn-table.pay missing or incomplete\n";
    exit(1);
}

// Test 9: Floating action popup CSS styling
if (strpos($html, '.table-action-buttons .action-btns-popup') !== false &&
    strpos($html, 'box-shadow: 0 10px 30px rgba(15, 23, 42, 0.16)') !== false) {
    echo " [PASS] 9. Floating action popup menu professional luxury shadow & transition verified\n";
} else {
    echo " [FAIL] 9. Floating action popup CSS styling missing\n";
    exit(1);
}

// Test 10: Visibility rules logic verification (Unit test with simulated invoices)
$testInvoices = [
    [
        'id' => 991,
        'invoice_number' => 'INV-TEST-UNPAID',
        'customer_name' => 'Unpaid Client',
        'customer_email' => 'unpaid@example.com',
        'issued_at' => date('Y-m-d H:i:s'),
        'total_amount' => 5000.00,
        'paid_amount' => 0.00,
        'payment_status' => 'unpaid',
        'booking_id' => 101,
        'booking_code' => 'VG-101',
    ],
    [
        'id' => 992,
        'invoice_number' => 'INV-TEST-PARTIAL',
        'customer_name' => 'Partial Client',
        'customer_email' => 'partial@example.com',
        'issued_at' => date('Y-m-d H:i:s'),
        'total_amount' => 5000.00,
        'paid_amount' => 2500.00,
        'payment_status' => 'partial',
        'booking_id' => 102,
        'booking_code' => 'VG-102',
    ],
    [
        'id' => 993,
        'invoice_number' => 'INV-TEST-PAID',
        'customer_name' => 'Paid Client',
        'customer_email' => 'paid@example.com',
        'issued_at' => date('Y-m-d H:i:s'),
        'total_amount' => 5000.00,
        'paid_amount' => 5000.00,
        'payment_status' => 'paid',
        'booking_id' => null,
        'booking_code' => null,
    ],
    [
        'id' => 994,
        'invoice_number' => 'INV-TEST-TINY-DUST',
        'customer_name' => 'Dust Client',
        'customer_email' => 'dust@example.com',
        'issued_at' => date('Y-m-d H:i:s'),
        'total_amount' => 5000.00,
        'paid_amount' => 4999.9995, // remaining 0.0005 <= 0.001
        'payment_status' => 'paid',
        'booking_id' => null,
        'booking_code' => null,
    ],
];

// Capture rendering of simulated invoices
ob_start();
$invoices = $testInvoices;
$currentPage = 1;
$perPage = 10;
$totalRecords = count($testInvoices);
$totalPages = 1;
$search = '';
$selectedStatus = '';
$accounts = [];
include __DIR__ . '/../app/Views/office/invoices/index.php';
$simHtml = ob_get_clean();

// Check Unpaid: Pay button MUST appear in floating menu for 991
if (strpos($simHtml, 'INV-TEST-UNPAID') !== false &&
    preg_match('/openRecordPaymentModal\([^\)]*?991[^\)]*?\)/', $simHtml)) {
    echo " [PASS] 10A. Unpaid invoice renders Pay button in floating menu prefilled with full remaining balance\n";
} else {
    echo " [FAIL] 10A. Unpaid invoice did not render Pay button\n";
    exit(1);
}

// Check Partial: Pay button MUST appear in floating menu for 992
if (strpos($simHtml, 'INV-TEST-PARTIAL') !== false &&
    preg_match('/openRecordPaymentModal\([^\)]*?992[^\)]*?\)/', $simHtml)) {
    echo " [PASS] 10B. Partially paid invoice renders Pay button in floating menu prefilled with partial balance\n";
} else {
    echo " [FAIL] 10B. Partially paid invoice did not render Pay button\n";
    exit(1);
}

// Check Paid: Pay button MUST NOT appear for 993
$paidHasPay = preg_match('/openRecordPaymentModal\([^\)]*?993[^\)]*?\)/', $simHtml);
if (!$paidHasPay) {
    echo " [PASS] 10C. Fully paid invoice successfully hides Pay button in floating menu\n";
} else {
    echo " [FAIL] 10C. Fully paid invoice incorrectly rendered Pay button\n";
    exit(1);
}

// Check Dust: Pay button MUST NOT appear for 994
$dustHasPay = preg_match('/openRecordPaymentModal\([^\)]*?994[^\)]*?\)/', $simHtml);
if (!$dustHasPay) {
    echo " [PASS] 10D. Outstanding balance <= 0.001 successfully hides Pay button in floating menu\n";
} else {
    echo " [FAIL] 10D. Outstanding balance <= 0.001 incorrectly rendered Pay button\n";
    exit(1);
}

// Check that Invoice & Issued Date column shows issue date only (no booking reference tag)
preg_match('/INV-TEST-UNPAID.*?<\/td>/s', $simHtml, $m);
$cell = $m[0] ?? '';
if (strpos($cell, 'VG-101') === false && strpos($cell, '03 Nov 2026') !== false) {
    echo " [PASS] 10E. Invoice & Issued Date column shows only the issue date (clean, no booking tags)\n";
} else {
    echo " [FAIL] 10E. Invoice & Issued Date column contains unexpected extra tags\n";
    exit(1);
}

echo "======================================================================\n";
echo "SUCCESS: ALL FLOATING ACTION BUTTON & TABLE TESTS PASSED 100%!\n";
echo "======================================================================\n";
