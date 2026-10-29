<?php
/**
 * MANASWI Tours & Travels — Finance Module Configuration
 */

return [
    'currency' => [
        'code'   => 'INR',
        'symbol' => '₹',
        'locale' => 'en_IN',
    ],

    'account_types' => [
        'bank'    => 'Commercial Bank Account',
        'cash'    => 'Petty Cash / Counter Cash',
        'gateway' => 'Online Payment Gateway (Razorpay/UPI)',
    ],

    'expense_categories' => [
        'fuel_bulk'         => 'Fleet Fuel (Diesel/Petrol)',
        'maintenance'       => 'Vehicle Maintenance & Parts',
        'driver_allowance'  => 'Driver Daily Bata / Allowances',
        'office_rent'       => 'Office & Garage Rent',
        'utilities'         => 'Electricity, Water & Internet',
        'software_saas'     => 'Software, GPS & Telematics',
        'marketing'         => 'Advertising & Promotion',
        'office_supplies'   => 'Stationery & Operations',
        'legal_accounting'  => 'Taxation, Legal & Audit Fees',
        'misc'              => 'Miscellaneous Expenses',
    ],

    'payment_methods' => [
        'bank_transfer' => 'NEFT / RTGS / IMPS',
        'upi'           => 'UPI Transfer (GPay / PhonePe / Paytm)',
        'cash'          => 'Cash in Hand',
        'card'          => 'Credit / Debit Card',
        'cheque'        => 'Bank Cheque / Draft',
    ],

    'payroll' => [
        'payslip_prefix'  => 'MAN-PAY-',
        'payroll_prefix'  => 'PR-',
        'expense_prefix'  => 'EXP-',
        'advance_prefix'  => 'ADV-',
        'refund_prefix'   => 'REF-',
    ],

    'gateways' => [
        'razorpay' => [
            'enabled' => false,
            'key_id' => getenv('RAZORPAY_KEY_ID') ?: '',
            'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        ],
    ],
];

