<?php
/**
 * Application Configuration
 */

return [
    'name' => 'Tours & Travels Management System',
    'tagline' => 'Safe Journey • Happy Memories',
    'version' => '2.0.0',
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

    // Default contact / branding (Neutral system fallbacks; database company_profiles is source of truth)
    'support_email' => '',
    'support_phone' => '',
    'company_address' => '',
    
    // Key directories
    'paths' => [
        'root' => dirname(__DIR__),
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'backups' => dirname(__DIR__) . '/storage/backups',
        'uploads' => dirname(__DIR__) . '/public/storage',
        'private_uploads' => dirname(__DIR__) . '/public/storage',
        'public' => dirname(__DIR__) . '/public',
    ]
];
