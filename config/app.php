<?php
/**
 * Application Configuration
 */

return [
    'name' => 'VoyageGo Tours & Travels',
    'tagline' => 'Enterprise Fleet Dispatch & Tour Operations',
    'version' => '1.0.0',
    'env' => 'production', // 'development' or 'production'
    'debug' => false,
    'timezone' => 'Asia/Kolkata',
    'currency' => 'INR',
    'currency_symbol' => '₹',
    
    // Roles supported across the system
    'roles' => [
        'admin' => 'Administrator',
        'dispatcher' => 'Dispatch Officer',
        'driver' => 'Fleet Driver',
        'customer' => 'Customer / Client'
    ],

    // Default contact / branding
    'support_email' => 'support@voyagego.internal',
    'support_phone' => '+91 98765 43210',
    'company_address' => 'VoyageGo Hub, Aero Mall Road, Terminal 2, New Delhi 110037',
    
    // Key directories
    'paths' => [
        'root' => dirname(__DIR__),
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'backups' => dirname(__DIR__) . '/storage/backups',
        'private_uploads' => dirname(__DIR__) . '/storage/private_uploads',
        'public' => dirname(__DIR__) . '/public',
    ]
];
