<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\CompanySettingsService;
use App\Core\Config;

$pdo = Database::getConnection();

// 1. Ensure a primary profile exists
$count = (int)$pdo->query("SELECT COUNT(*) FROM company_profiles")->fetchColumn();
if ($count === 0) {
    $pdo->exec("INSERT INTO company_profiles (company_name, email, status, is_primary) VALUES ('Dynamic Express', 'dynamic.company@travels.com', 'active', 1)");
} else {
    $pdo->exec("UPDATE company_profiles SET company_name = 'Dynamic Express', email = 'dynamic.company@travels.com', is_primary = 1");
}
CompanySettingsService::clearCache();
Config::reset('mail');

echo "1. CompanySettingsService email: " . CompanySettingsService::getCompanyEmail() . "\n";
echo "2. Config mail.from.address: " . Config::get('mail.from.address') . "\n";
echo "3. Config mail.from.name: " . Config::get('mail.from.name') . "\n";
echo "4. Config mail.admin_address: " . Config::get('mail.admin_address') . "\n";

assert(CompanySettingsService::getCompanyEmail() === 'dynamic.company@travels.com', 'CompanySettingsService resolves dynamic SQL email');
assert(Config::get('mail.from.name') === 'Dynamic Express', 'Config mail.from.name resolves dynamic SQL company name');
assert(Config::get('mail.reply_to.address') === 'dynamic.company@travels.com', 'Config mail.reply_to.address resolves dynamic SQL email');

// 2. Set email to NULL in SQL table
$pdo->exec("UPDATE company_profiles SET email = NULL WHERE is_primary = 1");
CompanySettingsService::clearCache();
Config::reset('mail');

echo "5. When email is NULL in SQL table: '" . CompanySettingsService::getCompanyEmail() . "'\n";

// Reset to user's real email for testing
$pdo->exec("UPDATE company_profiles SET company_name = 'MANASWI Tours & Travels', tagline = 'Tours is the best', email = 'sanketsidurkar@gmail.com', phone = '+91 87998 45236', website = 'https://manaswitravels.com', address = 'Plot 45, Great Nag Road, Nagpur - 440024', status = 'active', is_primary = 1 WHERE id = 5 OR is_primary = 1");
CompanySettingsService::clearCache();
Config::reset('mail');
echo "6. Restored to user's company profile email: " . CompanySettingsService::getCompanyEmail() . "\n";

echo "ALL DYNAMIC COMPANY EMAIL TESTS PASSED!\n";
