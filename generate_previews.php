<?php
require_once __DIR__ . '/app/bootstrap.php';

use App\Services\EmailService;

$emailService = new EmailService();

$previewsDir = __DIR__ . '/public/email_previews';
if (!is_dir($previewsDir)) {
    mkdir($previewsDir, 0777, true);
}

$templates = [
    'customer/welcome' => [
        'customer' => [
            'name' => 'Sanket Sidurkar',
            'customer_code' => 'VG-CUST-0001',
            'email' => 'sanketsidurkar9@gmail.com',
            'phone' => '8799845236',
            'city' => 'Nagpur',
            'membership_tier' => 'Executive Club Member'
        ]
    ],
    'employee/welcome' => [
        'employee' => [
            'name' => 'Vikram Patil',
            'employee_code' => 'EMP-0042',
            'designation' => 'Senior Chauffeur',
            'department' => 'Operations Fleet',
            'email' => 'vikram.patil@example.com',
            'phone' => '+91 98221 55443'
        ]
    ],
    'booking/created' => [
        'customerName' => 'Sanket Sidurkar',
        'booking' => [
            'id' => 1024,
            'booking_number' => 'BK-2026-1024',
            'vehicle_name' => 'Mercedes-Benz E-Class',
            'service_type' => 'Outstation Round Trip',
            'pickup_location' => 'Ramdaspeth, Nagpur',
            'dropoff_location' => 'Tadoba National Park, Chandrapur',
            'pickup_time' => '05 Nov 2026, 06:30 AM',
            'total_amount' => 12500,
            'status' => 'Pending Review'
        ]
    ],
    'booking/confirmed' => [
        'customerName' => 'Sanket Sidurkar',
        'booking' => [
            'id' => 1024,
            'booking_number' => 'BK-2026-1024',
            'vehicle_name' => 'Mercedes-Benz E-Class',
            'service_type' => 'Outstation Round Trip',
            'pickup_location' => 'Ramdaspeth, Nagpur',
            'dropoff_location' => 'Tadoba National Park, Chandrapur',
            'pickup_time' => '05 Nov 2026, 06:30 AM',
            'total_amount' => 12500,
            'status' => 'Confirmed'
        ]
    ],
    'booking/cancelled' => [
        'customerName' => 'Sanket Sidurkar',
        'booking' => [
            'booking_number' => 'BK-2026-1024',
            'vehicle_name' => 'Mercedes-Benz E-Class',
            'cancelled_at' => '02 Nov 2026, 05:00 PM',
            'cancellation_reason' => 'Client schedule adjustment',
            'refund_amount' => 12500,
            'refund_status' => 'Initiated to original payment method'
        ]
    ],
    'booking/driver-assigned' => [
        'customerName' => 'Sanket Sidurkar',
        'booking' => [
            'booking_number' => 'BK-2026-1024',
            'driver_name' => 'Vikram Patil',
            'driver_phone' => '+91 98221 55443',
            'vehicle_name' => 'Mercedes-Benz E-Class (MH-31-EQ-9988)',
            'pickup_time' => '05 Nov 2026, 06:30 AM'
        ]
    ],
    'driver/trip-assigned' => [
        'driverName' => 'Vikram Patil',
        'booking' => [
            'booking_number' => 'BK-2026-1024',
            'customer_name' => 'Sanket Sidurkar',
            'customer_phone' => '8799845236',
            'pickup_location' => 'Ramdaspeth, Nagpur',
            'dropoff_location' => 'Tadoba National Park, Chandrapur',
            'reporting_time' => '05 Nov 2026, 06:00 AM'
        ]
    ],
    'payment/successful' => [
        'customerName' => 'Sanket Sidurkar',
        'payment' => [
            'transaction_id' => 'TXN-98471203',
            'booking_number' => 'BK-2026-1024',
            'amount_paid' => 12500,
            'payment_method' => 'UPI / NetBanking',
            'paid_at' => '02 Nov 2026, 05:15 PM'
        ]
    ],
    'trip/started' => [
        'customerName' => 'Sanket Sidurkar',
        'trip' => [
            'booking_number' => 'BK-2026-1024',
            'driver_name' => 'Vikram Patil',
            'vehicle_name' => 'Mercedes-Benz E-Class',
            'start_time' => '05 Nov 2026, 06:35 AM',
            'destination' => 'Tadoba National Park'
        ]
    ],
    'trip/completed' => [
        'customerName' => 'Sanket Sidurkar',
        'trip' => [
            'booking_number' => 'BK-2026-1024',
            'driver_name' => 'Vikram Patil',
            'completion_time' => '06 Nov 2026, 08:45 PM',
            'total_distance' => '340 km',
            'final_amount' => 12500
        ]
    ],
    'trip/reminder' => [
        'customerName' => 'Sanket Sidurkar',
        'trip' => [
            'booking_number' => 'BK-2026-1024',
            'pickup_time' => 'Tomorrow, 05 Nov 2026 at 06:30 AM',
            'pickup_location' => 'Ramdaspeth, Nagpur',
            'driver_name' => 'Vikram Patil (+91 98221 55443)'
        ]
    ],
    'account/password-reset' => [
        'userName' => 'Sanket Sidurkar',
        'userEmail' => 'sanketsidurkar9@gmail.com',
        'resetUrl' => 'https://example.com/reset-password?token=sample_token',
        'expiresIn' => '60 minutes'
    ],
    'test' => [
        'testName' => 'Diagnostic Email Verification',
        'serverTime' => date('d M Y, h:i A')
    ]
];

foreach ($templates as $tmpl => $data) {
    // Generate self-contained standalone preview with embedded Base64 data URIs
    $html = $emailService->renderTemplate($tmpl, $data, true);
    $filename = str_replace('/', '_', $tmpl) . '.html';
    file_put_contents($previewsDir . '/' . $filename, $html);
    
    if ($tmpl === 'customer/welcome') {
        file_put_contents(__DIR__ . '/public/preview_welcome.html', $html);
    }
    echo "Generated preview: {$filename}\n";
}

echo "All 13 previews generated successfully!\n";
