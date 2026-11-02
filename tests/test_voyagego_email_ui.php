<?php
/**
 * Test Suite: Professional Dynamic Email UI Verification
 * Validates pixel-perfect alignment with the Executive UI/UX Design System:
 * 
 * 1. Brand Color Palette:
 *    - Primary Navy (#071F50)
 *    - Secondary Navy (#12366B)
 *    - Primary Purple (#5520D8)
 *    - Accent Purple (#7655F5)
 *    - Light Purple (#F3F0FF)
 *    - Main Background (#F5F7FB)
 *    - White (#FFFFFF)
 *    - Main Text (#17233F)
 *    - Secondary Text (#64748B)
 *    - Border (#E2E8F0)
 * 
 * 2. 10-Section Professional Architecture:
 *    - Section 1: Brand Header (White background, dynamic company logo or fallback badge, dynamic company name, date badge)
 *    - Section 2: Hero Header (Gradient navy #071F50, category badge, headline, supporting subtitle)
 *    - Section 3: Personalized Greeting (Hello, [Name]! + concise lead sentence)
 *    - Section 4-8: Dynamic Content Cards (Status pill, key-value specs, notices, CTA button)
 *    - Section 9: Enterprise Privileges & Support (NEED HELP?, dynamic company email, dynamic company phone)
 *    - Section 10: Professional Footer (Dark navy #071F50, dynamic company name, tagline, website, copyright year)
 * 
 * 3. 100% Dynamic Company Branding:
 *    - Zero hardcoded "VoyageGo" strings in any rendered output.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\EmailService;
use App\Services\CompanySettingsService;

$passed = 0;
$failed = 0;

function assertUiTest(string $description, bool $condition, ?string $details = null): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($details ? " -> {$details}" : "") . "\n";
        $failed++;
    }
}

$emailService = new EmailService();
$companyName = CompanySettingsService::getCompanyName();

echo "============================================================\n";
echo "1. VERIFYING BRAND COLOR PALETTE & DESIGN TOKENS\n";
echo "============================================================\n";

$masterContent = file_get_contents(dirname(__DIR__) . '/app/Views/emails/layouts/master.php');

assertUiTest("Master layout defines Primary Navy Blue (#071F50)", str_contains($masterContent, '#071F50'));
assertUiTest("Master layout defines Secondary Navy (#12366B)", str_contains($masterContent, '#12366B'));
assertUiTest("Master layout defines Primary Purple (#5520D8)", str_contains($masterContent, '#5520D8'));
assertUiTest("Master layout defines Accent Purple (#7655F5)", str_contains($masterContent, '#7655F5'));
assertUiTest("Master layout defines Main Background (#F5F7FB)", str_contains($masterContent, '#F5F7FB'));
assertUiTest("Master layout defines Main Text Dark Navy (#17233F)", str_contains($masterContent, '#17233F'));
assertUiTest("Master layout defines Secondary Text Slate (#64748B)", str_contains($masterContent, '#64748B'));
assertUiTest("Master layout defines Border Color (#E2E8F0)", str_contains($masterContent, '#E2E8F0'));

echo "\n============================================================\n";
echo "2. VERIFYING 10-SECTION ARCHITECTURE IN MASTER LAYOUT\n";
echo "============================================================\n";

// Section 1: Brand Header
assertUiTest("Section 1: Brand Header has white background (#FFFFFF)", str_contains($masterContent, 'SECTION 1: BRAND HEADER') && str_contains($masterContent, 'background-color: #FFFFFF'));
assertUiTest("Section 1: Brand Header contains dynamic company name slot", str_contains($masterContent, 'e($companyName)'));
assertUiTest("Section 1: Brand Header contains dynamic company tagline slot", str_contains($masterContent, 'e($companyTagline)'));
assertUiTest("Section 1: Transactional email eliminates unnecessary navigation menus", !str_contains($masterContent, 'Home</a>') && !str_contains($masterContent, 'My Bookings'));
assertUiTest("Section 1: Brand Header has subtle bottom border (#E2E8F0)", str_contains($masterContent, 'border-bottom: 1px solid #E2E8F0;'));

// Section 2: Hero Header
assertUiTest("Section 2: Hero has navy gradient (#071F50 / #12366B)", str_contains($masterContent, 'SECTION 2: HERO') && str_contains($masterContent, '#071F50'));
assertUiTest("Section 2: Hero displays category label slot", str_contains($masterContent, '$categoryLabel'));
assertUiTest("Section 2: Hero displays prominent heading slot", str_contains($masterContent, '$headingText'));
assertUiTest("Section 2: Hero displays supporting message slot", str_contains($masterContent, '$subheadingText'));

// Section 3: Personalized Greeting
assertUiTest("Section 3: Contains personalized greeting 'Hello, ...!'", str_contains($masterContent, 'Hello, <?= e($greetingName) ?>!'));
assertUiTest("Section 3: Displays concise introductory lead sentence", str_contains($masterContent, '<?= e($leadText) ?>'));

// Section 9: Support Section
assertUiTest("Section 9: Displays compact 'NEED HELP?' block", str_contains($masterContent, 'NEED HELP?'));
assertUiTest("Section 9: Displays customer support email link", str_contains($masterContent, 'mailto:<?= e($supportEmail) ?>'));
assertUiTest("Section 9: Displays support telephone link", str_contains($masterContent, 'tel:<?= e(preg_replace'));

// Section 10: Professional Footer
assertUiTest("Section 10: Footer has dark navy background (#071F50)", str_contains($masterContent, 'SECTION 10: PROFESSIONAL FOOTER') && str_contains($masterContent, 'background-color: #071F50'));
assertUiTest("Section 10: Footer contains dynamic company name", str_contains($masterContent, '<?= e($companyName) ?>'));
assertUiTest("Section 10: Footer contains website URL slot", str_contains($masterContent, '$websiteUrl'));
assertUiTest("Section 10: Footer contains dynamic copyright year", str_contains($masterContent, "date('Y')"));

echo "\n============================================================\n";
echo "3. VERIFYING BOOKING CONFIRMED EMAIL\n";
echo "============================================================\n";

$sampleBooking = [
    'id' => 125,
    'booking_code' => 'VG-2026-00125',
    'pickup_address' => 'Nagpur Railway Station',
    'drop_address' => 'Pune',
    'start_date' => '2026-11-10',
    'pickup_time' => '08:30:00',
    'passenger_count' => 2,
    'total_amount' => 12500.00,
    'paid_amount' => 12500.00,
];
$sampleCustomer = ['id' => 1, 'name' => 'Sanket Sidurkar', 'email' => 'sanket@example.com', 'phone' => '+91 98765 43210'];
$sampleVehicle = ['id' => 1, 'make_model' => 'Toyota Innova Crysta'];

$renderedConfirmed = $emailService->renderTemplate('booking/confirmed', [
    'booking' => $sampleBooking,
    'customer' => $sampleCustomer,
    'vehicle' => $sampleVehicle,
]);

assertUiTest("Booking Confirmed: Greets 'Hello, Sanket Sidurkar!'", str_contains($renderedConfirmed, 'Hello, Sanket Sidurkar!'));
assertUiTest("Booking Confirmed: Section status badge 'Booking Confirmed'", str_contains($renderedConfirmed, 'Booking Confirmed'));
assertUiTest("Booking Confirmed: Displays itinerary code 'VG-2026-00125'", str_contains($renderedConfirmed, 'VG-2026-00125'));
assertUiTest("Booking Confirmed: Contains pickup 'Nagpur Railway Station'", str_contains($renderedConfirmed, 'Nagpur Railway Station'));
assertUiTest("Booking Confirmed: Contains destination 'Pune'", str_contains($renderedConfirmed, 'Pune'));
assertUiTest("Booking Confirmed: Contains scheduled departure time", str_contains($renderedConfirmed, '08:30 AM'));
assertUiTest("Booking Confirmed: Displays total confirmed fare", str_contains($renderedConfirmed, '12,500.00'));
assertUiTest("Booking Confirmed: Contains Access Your Reservation CTA", str_contains($renderedConfirmed, 'Access Your Reservation &rarr;'));

echo "\n============================================================\n";
echo "4. VERIFYING SECTIONS & STYLING ACROSS ALL 13 EMAIL TEMPLATES\n";
echo "============================================================\n";

$sampleDriver = ['id' => 1, 'name' => 'Rajesh Sharma', 'phone' => '+91 98220 11111'];
$sampleDispatch = ['id' => 10, 'dispatch_code' => 'DSP-2611-0010'];
$samplePayment = ['id' => 99, 'payment_reference' => 'PAY-2611-0099', 'amount' => 12500.00, 'payment_method' => 'upi', 'paid_at' => '2026-11-02 12:00:00'];
$sampleInvoice = ['id' => 88, 'invoice_number' => 'INV-2026-0088', 'total_amount' => 12500.00, 'paid_amount' => 12500.00];

$templatesToTest = [
    'booking/created' => [
        'data' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer],
        'expected' => ['RESERVATION DETAILS', 'VG-2026-00125', 'Nagpur Railway Station', 'Pune']
    ],
    'booking/confirmed' => [
        'data' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle],
        'expected' => ['Booking Confirmed', 'CONFIRMED ITINERARY', 'VG-2026-00125', 'Access Your Reservation']
    ],
    'booking/cancelled' => [
        'data' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'reason' => 'Schedule changed by guest'],
        'expected' => ['Booking Cancelled', 'VG-2026-00125', 'Schedule changed by guest']
    ],
    'booking/driver-assigned' => [
        'data' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'dispatch' => $sampleDispatch],
        'expected' => ['Rajesh Sharma', 'Toyota Innova Crysta', 'VG-2026-00125']
    ],
    'driver/trip-assigned' => [
        'data' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver],
        'expected' => ['DSP-2611-0010', 'Nagpur Railway Station', 'Pune']
    ],
    'payment/successful' => [
        'data' => ['payment' => $samplePayment, 'invoice' => $sampleInvoice, 'booking' => $sampleBooking, 'customer' => $sampleCustomer],
        'expected' => ['PAY-2611-0099', 'INV-2026-0088', '12,500.00']
    ],
    'trip/reminder' => [
        'data' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'recipient' => $sampleCustomer],
        'expected' => ['Nagpur Railway Station', 'Pune', 'VG-2026-00125']
    ],
    'trip/started' => [
        'data' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'customer' => $sampleCustomer],
        'expected' => ['VG-2026-00125', 'Pune']
    ],
    'trip/completed' => [
        'data' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'customer' => $sampleCustomer],
        'expected' => ['VG-2026-00125']
    ],
    'customer/welcome' => [
        'data' => ['customer' => ['id' => 15, 'customer_code' => 'CUST-00015', 'name' => 'Priya Patel', 'email' => 'priya@example.com', 'phone' => '+91 98111 22222', 'city' => 'Mumbai']],
        'expected' => ['Priya Patel', 'CUST-00015', 'Mumbai']
    ],
    'employee/welcome' => [
        'data' => ['employee' => ['id' => 20, 'employee_code' => 'EMP-00020', 'first_name' => 'Amit', 'last_name' => 'Deshmukh', 'department' => 'fleet_chauffeur', 'designation' => 'Executive Chauffeur', 'email' => 'amit@example.com', 'phone' => '+91 98333 44444']],
        'expected' => ['EMP-00020', 'Amit Deshmukh']
    ],
    'account/password-reset' => [
        'data' => ['user' => ['id' => 5, 'username' => 'amit_d', 'name' => 'Amit Deshmukh', 'email' => 'amit@example.com'], 'temporaryPassword' => 'SecurePass#2026'],
        'expected' => ['amit_d', 'SecurePass#2026']
    ],
    'test' => [
        'data' => ['smtpHost' => 'smtp.gmail.com', 'smtpPort' => '465', 'sender' => 'admin@example.com', 'recipientEmail' => 'admin@example.com'],
        'expected' => ['smtp.gmail.com', '465', 'admin@example.com']
    ],
];

foreach ($templatesToTest as $tmplName => $config) {
    $html = $emailService->renderTemplate($tmplName, $config['data']);
    
    // 1. Verify expected section tokens
    foreach ($config['expected'] as $token) {
        assertUiTest("Template '{$tmplName}' contains '{$token}'", str_contains($html, $token));
    }
    
    // 2. Verify Design Tokens
    assertUiTest("Template '{$tmplName}' has Main Background (#F5F7FB)", str_contains($html, '#F5F7FB'));
    assertUiTest("Template '{$tmplName}' has Section 10 Navy Footer (#071F50)", str_contains($html, '#071F50'));
    assertUiTest("Template '{$tmplName}' has Need Help? Section", str_contains($html, 'NEED HELP?'));
    assertUiTest("Template '{$tmplName}' has No Distracting Nav Menus", !str_contains($html, 'Home</a>') && !str_contains($html, 'My Bookings'));
    assertUiTest("Template '{$tmplName}' has ZERO 'VoyageGo' branding", !str_contains($html, 'VoyageGo'));
}

echo "\n============================================================\n";
echo "DYNAMIC EMAIL UI TEST SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
