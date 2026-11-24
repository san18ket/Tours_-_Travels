<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Models\Invoice;
use App\Models\BookingBillingItem;

$invModel = new Invoice();
$inv = $invModel->findWithFullDetails(82);

if (!$inv) {
    echo "Invoice 82 NOT FOUND! Finding latest invoices:\n";
    $latest = $invModel->where('1=1', [], 'id DESC', 5);
    print_r($latest);
    exit;
}

echo "ALL INVOICE 82 DATA:\n";
print_r($inv);
$rf = new ReflectionFunction('url');
echo "url function is at: " . $rf->getFileName() . ':' . $rf->getStartLine() . "\n";
echo "url('/invoice/verify/82') = " . url('/invoice/verify/82') . "\n";
