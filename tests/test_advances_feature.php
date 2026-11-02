<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Controllers\FinanceController;

Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/finance/advances';

$fc = new FinanceController();
$req = new Request();

echo "=== 1. Testing Advances Index ===\n";
try {
    $res = $fc->advances($req);
    echo "Status code: " . $res->getStatusCode() . "\n";
    $body = $res->getBody();
    echo "Body length: " . strlen($body) . " bytes\n";
    
    // Check for Sr No (#) column
    if (strpos($body, '<th class="text-center" style="width: 50px;">#</th>') !== false) {
        echo "SUCCESS: Found '#'' for Serial No in table header.\n";
    } else {
        echo "FAIL: '#' header not found!\n";
    }

    // Check Advance # is removed from table header
    if (strpos($body, '<th style="width: 130px;">Advance #</th>') === false) {
        echo "SUCCESS: 'Advance #' header removed from table.\n";
    } else {
        echo "FAIL: 'Advance #' header still present!\n";
    }

    // Check status interactive toggle and pills like transactions
    if (strpos($body, 'cust-status-toggle') !== false && strpos($body, 'cust-toggle-pill') !== false && strpos($body, 'tx-status-dropdown-menu') !== false) {
        echo "SUCCESS: Interactive status toggle and dropdown pills found matching office/finance/transactions.\n";
    } else {
        echo "FAIL: Status toggle/dropdown not found!\n";
    }

    // Check Action buttons popup has view, edit, delete only
    if (strpos($body, 'action-btn-table view') !== false && strpos($body, 'action-btn-table edit') !== false && strpos($body, 'action-btn-table delete') !== false) {
        echo "SUCCESS: View, Edit, and Delete action buttons found in popup.\n";
    } else {
        echo "FAIL: View, Edit, Delete buttons missing from action popup!\n";
    }

} catch (Throwable $e) {
    echo "Advances Index Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== 2. Testing Advance Show View (/office/finance/advances/1) ===\n";
try {
    $_SERVER['REQUEST_URI'] = '/office/finance/advances/1';
    $res2 = $fc->showAdvance($req, ['id' => 1]);
    echo "Status code: " . $res2->getStatusCode() . "\n";
    $body2 = $res2->getBody();
    echo "Body length: " . strlen($body2) . " bytes\n";

    if (strpos($body2, 'advance-view-root') !== false && strpos($body2, 'view-hero-card') !== false && strpos($body2, 'view-kpi-grid') !== false) {
        echo "SUCCESS: Advance show page rendered with hero card and KPI grid matching office/finance/transactions/1.\n";
    } else {
        echo "FAIL: Elements missing in advance show view!\n";
    }

    if (strpos($body2, 'ADV-202611-0001') !== false) {
        echo "SUCCESS: Advance code found on view page.\n";
    } else {
        echo "FAIL: Advance code missing!\n";
    }

    if (strpos($body2, 'Loan Overview') !== false && strpos($body2, 'Staff Profile') !== false && strpos($body2, 'Payroll &amp; Audit Trail') !== false) {
        echo "SUCCESS: All 3 navigation tabs found matching transactions/1.\n";
    } else {
        echo "FAIL: Tabs missing in advance show view!\n";
    }

} catch (Throwable $e) {
    echo "Advance Show Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
