<?php
/**
 * Test Suite: Finance Sidebar Multi-Level Nested Dropdown UI & Active States
 */

require 'app/bootstrap.php';

use App\Core\Session;
use App\Core\Database;

Session::set('user_id', 1);
Session::set('user_role', 'admin');
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$content = '<p>Test Page Body</p>';

$routesToTest = [
    '/office/finance' => [
        'subcat' => null, // Top-level direct link
        'label'  => 'Finance Dashboard'
    ],
    '/office/invoices' => [
        'subcat' => 'subcat-finance-payments',
        'label'  => 'Invoices'
    ],
    '/office/finance/refunds' => [
        'subcat' => 'subcat-finance-payments',
        'label'  => 'Refunds'
    ],
    '/office/finance/payments' => [
        'subcat' => 'subcat-finance-payments',
        'label'  => 'Payment History'
    ],
    '/office/finance/pending-payments' => [
        'subcat' => 'subcat-finance-payments',
        'label'  => 'Pending Payments'
    ],
    '/office/finance/expenses' => [
        'subcat' => 'subcat-finance-expenses',
        'label'  => 'Expenses'
    ],
    '/office/finance/expense-categories' => [
        'subcat' => 'subcat-finance-expenses',
        'label'  => 'Expense Categories'
    ],
    '/office/finance/payroll' => [
        'subcat' => 'subcat-finance-salary',
        'label'  => 'Payroll'
    ],
    '/office/finance/advances' => [
        'subcat' => 'subcat-finance-salary',
        'label'  => 'Salary Advances'
    ],
    '/office/finance/payslips' => [
        'subcat' => 'subcat-finance-salary',
        'label'  => 'Payslips'
    ],
    '/office/finance/salary-history' => [
        'subcat' => 'subcat-finance-salary',
        'label'  => 'Salary History'
    ],
    '/office/finance/accounts' => [
        'subcat' => 'subcat-finance-cashbank',
        'label'  => 'Accounts'
    ],
    '/office/finance/transactions' => [
        'subcat' => 'subcat-finance-cashbank',
        'label'  => 'Transactions'
    ],
    '/office/finance/reports' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Reports Overview'
    ],
    '/office/finance/reports/income' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Income Report'
    ],
    '/office/finance/reports/expenses' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Expense Report'
    ],
    '/office/finance/reports/profit-loss' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Profit & Loss Report'
    ],
    '/office/finance/reports/customer-dues' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Customer Dues Report'
    ],
    '/office/finance/reports/salary' => [
        'subcat' => 'subcat-finance-reports',
        'label'  => 'Salary Report'
    ],
];

$allSubcategories = [
    'subcat-finance-payments',
    'subcat-finance-expenses',
    'subcat-finance-salary',
    'subcat-finance-cashbank',
    'subcat-finance-reports'
];

echo "======================================================================\n";
echo "   FINANCE SIDEBAR MULTI-LEVEL NESTED DROPDOWN TEST SUITE\n";
echo "======================================================================\n";

$allPassed = true;

foreach ($routesToTest as $uri => $testInfo) {
    $_SERVER['REQUEST_URI'] = $uri;
    ob_start();
    include 'app/Views/layouts/office.php';
    $html = ob_get_clean();

    $expectedSubcat = $testInfo['subcat'];
    $expectedLabel  = $testInfo['label'];

    // 1. Verify Level 1 Finance dropdown is marked has-active and expanded
    $isFinanceHeaderOpen = str_contains($html, 'class="category-header has-active open" id="cat-finance-header"') && str_contains($html, 'aria-expanded="true"');
    $isFinanceItemsExpanded = str_contains($html, 'class="sidebar-category-items expanded" id="cat-finance-items"');

    // 2. Verify Subcategory expansion
    $subcatCorrect = true;
    $subcatErrorMsg = '';

    foreach ($allSubcategories as $subcatId) {
        $headerExpectedExpanded = ($subcatId === $expectedSubcat);
        $headerRegex = '/<button[^>]*id="' . preg_quote($subcatId, '/') . '-header"[^>]*aria-expanded="([^"]+)"|<button[^>]*aria-expanded="([^"]+)"[^>]*id="' . preg_quote($subcatId, '/') . '-header"/i';
        $itemsRegex  = '/<div[^>]*id="' . preg_quote($subcatId, '/') . '-items"[^>]*class="([^"]+)"|<div[^>]*class="([^"]+)"[^>]*id="' . preg_quote($subcatId, '/') . '-items"/i';

        preg_match($headerRegex, $html, $hMatches);
        preg_match($itemsRegex, $html, $iMatches);

        $ariaExpandedVal = !empty($hMatches[1]) ? $hMatches[1] : (!empty($hMatches[2]) ? $hMatches[2] : '');
        $classVal        = !empty($iMatches[1]) ? $iMatches[1] : (!empty($iMatches[2]) ? $iMatches[2] : '');

        $isExpandedAttr   = ($ariaExpandedVal === 'true');
        $hasExpandedClass = str_contains($classVal, 'expanded');

        if ($headerExpectedExpanded) {
            if (!$isExpandedAttr || !$hasExpandedClass) {
                $subcatCorrect = false;
                $subcatErrorMsg .= "Expected subcategory '{$subcatId}' to be expanded (got aria-expanded='{$ariaExpandedVal}', class='{$classVal}'). ";
            }
        } else {
            if ($isExpandedAttr || $hasExpandedClass) {
                $subcatCorrect = false;
                $subcatErrorMsg .= "Subcategory '{$subcatId}' should be collapsed (got aria-expanded='{$ariaExpandedVal}', class='{$classVal}'). ";
            }
        }
    }

    // 3. Verify the expected link has 'active' class
    $expectedLinkPattern = '/class="nav-link\s+active"[^>]*>[\s\S]*?<span class="nav-text">' . preg_quote($expectedLabel, '/') . '<\/span>/i';
    $hasActiveLink = (bool)preg_match($expectedLinkPattern, $html);

    if ($isFinanceHeaderOpen && $isFinanceItemsExpanded && $subcatCorrect && $hasActiveLink) {
        $subcatStr = $expectedSubcat ? "Subcat [{$expectedSubcat}]" : "Direct Dashboard";
        echo " [PASS] Route: {$uri} -> Finance open, {$subcatStr} open, '{$expectedLabel}' active\n";
    } else {
        echo " [FAIL] Route: {$uri}\n";
        if (!$isFinanceHeaderOpen) echo "   - Finance header not open\n";
        if (!$isFinanceItemsExpanded) echo "   - Finance items not expanded\n";
        if (!$subcatCorrect) echo "   - Subcategory state error: {$subcatErrorMsg}\n";
        if (!$hasActiveLink) echo "   - Did not find active link for '{$expectedLabel}'\n";
        $allPassed = false;
    }
}

// 4. Test when on a non-finance route (e.g. /office/bookings), Finance and all subcats are NOT expanded
$_SERVER['REQUEST_URI'] = '/office/bookings';
ob_start();
include 'app/Views/layouts/office.php';
$html = ob_get_clean();

$financeNotExpanded = !str_contains($html, 'cat-finance-items" class="sidebar-category-items expanded"') &&
                      !str_contains($html, 'cat-finance-header" class="category-header has-active open"');

$allSubcatsClosed = true;
foreach ($allSubcategories as $subcatId) {
    if (str_contains($html, $subcatId . '-items" class="sidebar-subcategory-items expanded"')) {
        $allSubcatsClosed = false;
    }
}

if ($financeNotExpanded && $allSubcatsClosed) {
    echo " [PASS] Route: /office/bookings -> Finance dropdown & all nested subcategories are closed by default\n";
} else {
    echo " [FAIL] Route: /office/bookings -> Finance or subcategory was unexpectedly open!\n";
    $allPassed = false;
}

echo "======================================================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL NESTED DROPDOWN CONSISTENCY & ACCORDION TESTS PASSED!\n";
    exit(0);
} else {
    echo "ERROR: SOME TESTS FAILED!\n";
    exit(1);
}
