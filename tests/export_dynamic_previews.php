<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\EmailService;
use App\Services\CompanySettingsService;

$emailService = new EmailService();

$sampleBooking = [
    'id' => 125,
    'booking_code' => 'VG-2026-00125',
    'pickup_address' => 'Nagpur Railway Station',
    'drop_address' => 'Pune',
    'start_date' => '2026-11-10',
    'end_date' => '2026-11-12',
    'pickup_time' => '08:30:00',
    'passenger_count' => 2,
    'total_amount' => 12500.00,
    'paid_amount' => 12500.00,
];
$sampleCustomer = ['id' => 1, 'name' => 'Sanket Sidurkar', 'email' => 'sanketsidurkar@gmail.com', 'phone' => '+91 87998 45236', 'city' => 'Nagpur', 'customer_code' => 'VG-CUST-0001'];
$sampleVehicle = ['id' => 1, 'make_model' => 'Toyota Innova Crysta', 'registration_number' => 'MH 31 TR 8899', 'type' => 'SUV'];
$sampleDriver = ['id' => 1, 'name' => 'Rajesh Sharma', 'phone' => '+91 98220 11111', 'license_number' => 'MH31-2018-9999'];
$sampleDispatch = ['id' => 10, 'dispatch_code' => 'DSP-2611-0010', 'status' => 'assigned'];
$samplePayment = ['id' => 99, 'payment_reference' => 'PAY-2611-0099', 'amount' => 12500.00, 'payment_method' => 'upi', 'paid_at' => '2026-11-02 12:00:00'];
$sampleInvoice = ['id' => 88, 'invoice_number' => 'INV-2026-0088', 'total_amount' => 12500.00, 'paid_amount' => 12500.00];

$templates = [
    'booking/created' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer],
    'booking/confirmed' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle],
    'booking/cancelled' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'reason' => 'Schedule changed by guest'],
    'booking/driver-assigned' => ['booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'dispatch' => $sampleDispatch],
    'driver/trip-assigned' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'customer' => $sampleCustomer, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver],
    'payment/successful' => ['payment' => $samplePayment, 'invoice' => $sampleInvoice, 'booking' => $sampleBooking, 'customer' => $sampleCustomer],
    'trip/reminder' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'recipient' => $sampleCustomer],
    'trip/started' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'customer' => $sampleCustomer],
    'trip/completed' => ['dispatch' => $sampleDispatch, 'booking' => $sampleBooking, 'vehicle' => $sampleVehicle, 'driver' => $sampleDriver, 'customer' => $sampleCustomer],
    'customer/welcome' => ['customer' => $sampleCustomer],
    'employee/welcome' => ['employee' => ['id' => 20, 'employee_code' => 'EMP-00020', 'first_name' => 'Amit', 'last_name' => 'Deshmukh', 'department' => 'fleet_chauffeur', 'designation' => 'Executive Chauffeur', 'email' => 'amit@example.com', 'phone' => '+91 98333 44444']],
    'account/password-reset' => ['user' => ['id' => 5, 'username' => 'sanket_s', 'name' => 'Sanket Sidurkar', 'email' => 'sanketsidurkar@gmail.com'], 'temporaryPassword' => 'SecurePass#2026'],
    'test' => ['smtpHost' => 'smtp.gmail.com', 'smtpPort' => 465, 'sender' => CompanySettingsService::getCompanyEmail(), 'recipientEmail' => 'sanketsidurkar@gmail.com']
];

$outDir = dirname(__DIR__) . '/public/email_previews';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

foreach ($templates as $tmpl => $data) {
    $html = $emailService->renderTemplate($tmpl, $data);
    $filename = str_replace('/', '_', $tmpl) . '.html';
    file_put_contents($outDir . '/' . $filename, $html);
    echo "Exported preview: {$filename}\n";
}
echo "All previews successfully regenerated with dynamic company branding.\n";

