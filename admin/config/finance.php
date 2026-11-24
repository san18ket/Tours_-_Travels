<?php
/**
 * Finance Module Configuration
 * Default settings, gateway configurations, and accounting policies
 */

return [
    'currency'        => 'INR',
    'currency_symbol' => '₹',

    // Razorpay Payment Gateway (Test Sandbox Mode Default)
    'razorpay' => [
        'enabled'        => true,
        'mode'           => getenv('RAZORPAY_MODE') ?: 'test', // 'test' or 'live'
        'key_id'         => getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_ToursTravelsTest2026',
        'key_secret'     => getenv('RAZORPAY_KEY_SECRET') ?: 'ToursTravelsSecretKey2026',
        'webhook_secret' => getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'ToursTravelsWebhookSecret2026',
        'company_name'   => getenv('APP_NAME') ?: 'Tours & Travels',
        'theme_color'    => '#176B5B',
    ],

    // Default Statutory Payroll Parameters (India FY 2026-27 Compliant)
    'payroll' => [
        'standard_work_days'     => 30,
        'pf_employee_percent'    => 12.00,
        'esi_employee_percent'   => 0.75,
        'default_hra_percent'    => 40.00,
        'professional_tax_slab'  => 200.00,
    ],

    // Accounts Receivable Overdue Slabs (in days)
    'aging_slabs' => [
        'current' => 30,
        'medium'  => 60,
        'high'    => 90,
    ]
];
