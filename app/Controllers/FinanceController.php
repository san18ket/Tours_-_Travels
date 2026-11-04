<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\BusinessExpense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryStructure;
use App\Models\EmployeeAdvance;
use App\Models\Refund;
use App\Models\Invoice;
use App\Models\Employee;
use Throwable;

class FinanceController extends Controller
{
    private FinanceAccount $accountModel;
    private FinanceTransaction $transactionModel;
    private BusinessExpense $expenseModel;
    private ExpenseCategory $categoryModel;
    private Payment $paymentModel;
    private Payroll $payrollModel;
    private PayrollItem $payrollItemModel;
    private EmployeeAdvance $advanceModel;
    private Refund $refundModel;
    private Invoice $invoiceModel;
    private Employee $employeeModel;

    public function __construct()
    {
        $this->accountModel = new FinanceAccount();
        $this->transactionModel = new FinanceTransaction();
        $this->expenseModel = new BusinessExpense();
        $this->categoryModel = new ExpenseCategory();
        $this->paymentModel = new Payment();
        $this->payrollModel = new Payroll();
        $this->payrollItemModel = new PayrollItem();
        $this->advanceModel = new EmployeeAdvance();
        $this->refundModel = new Refund();
        $this->invoiceModel = new Invoice();
        $this->employeeModel = new Employee();
    }

    /**
     * 1. Financial Command Dashboard
     */
    public function dashboard(Request $request): Response
    {
        $pdo = \App\Core\Database::getConnection();
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');

        // 1. Total Money Received (actual payments recorded)
        $totalReceived = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'")->fetchColumn() ?: 0);
        if ($totalReceived <= 0) {
            $totalReceived = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM finance_ledger WHERE transaction_type = 'credit' AND category NOT IN ('Internal Transfer') AND (reference_type IS NULL OR reference_type != 'finance_transfer')")->fetchColumn() ?: 0);
        }

        // 2. Total Business Expenses (expenses + paid salaries)
        $bizExp = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM business_expenses WHERE status != 'rejected'")->fetchColumn() ?: 0);
        $salariesPaid = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'paid'")->fetchColumn() ?: 0);
        $totalExpenses = $bizExp + $salariesPaid;

        // 3. Available Cash/Bank Balance (sum of active accounts)
        $accounts = $this->accountModel->getActiveAccounts();
        $availableBalance = 0.0;
        foreach ($accounts as $a) {
            $availableBalance += (float)$a['current_balance'];
        }

        // 4. Customer Pending Payments (invoices unpaid/partially_paid balance)
        $customerPendingPayments = (float)($pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE payment_status != 'paid' AND (total_amount > paid_amount)")->fetchColumn() ?: 0);

        // 5. Staff Salary Pending (unpaid payroll items)
        $staffSalaryPending = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'unpaid'")->fetchColumn() ?: 0);

        // Monthly Inflow vs Outflow (excluding internal transfers)
        $monthlyLedger = $this->transactionModel->getMonthlyIncomeExpense($currentYear);
        $incomeData = array_fill(1, 12, 0.0);
        $expenseData = array_fill(1, 12, 0.0);

        foreach ($monthlyLedger as $row) {
            $m = (int)$row['month'];
            $incomeData[$m] = (float)$row['total_income'];
            $expenseData[$m] = (float)$row['total_expense'];
        }

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        // Recent Activity Lists
        $recentPayments = $this->paymentModel->getPaymentsWithDetails([], 5, 0);
        $recentExpenses = $this->expenseModel->getExpensesWithDetails([], 5, 0);
        $upcomingSalaries = $this->payrollItemModel->getPayslipsWithDetails(['payment_status' => 'pending'], 5, 0);
        $recentRefunds = $this->refundModel->getRefundsWithDetails([], 5, 0);

        // Helper data for Quick Action Modals
        $pendingInvoices = $this->invoiceModel->getPendingInvoicesWithDetails([], 50, 0);
        $categories = $this->categoryModel->getActiveCategories();

        return $this->render('office/finance/dashboard', [
            'title'                   => 'Finance Overview & Treasury',
            'totalReceived'           => $totalReceived,
            'totalExpenses'           => $totalExpenses,
            'availableBalance'        => $availableBalance,
            'customerPendingPayments' => $customerPendingPayments,
            'staffSalaryPending'      => $staffSalaryPending,
            'accounts'                => $accounts,
            'incomeData'              => array_values($incomeData),
            'expenseData'             => array_values($expenseData),
            'monthLabels'             => array_values($monthNames),
            'recentPayments'          => $recentPayments,
            'recentExpenses'          => $recentExpenses,
            'upcomingSalaries'        => $upcomingSalaries,
            'recentRefunds'           => $recentRefunds,
            'pendingInvoices'         => $pendingInvoices,
            'categories'              => $categories,
            'currentYear'             => $currentYear,
            'currentMonth'            => $currentMonth,
        ], 'layouts/office');
    }

    /**
     * 2. Transaction Book & General Ledger
     */
    public function transactions(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $type = trim((string)$request->get('type', ''));
        $category = trim((string)$request->get('category', ''));
        $status = trim((string)$request->get('status', ''));
        $accountId = (int)$request->get('account_id', 0);
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'           => $search,
            'transaction_type' => $type,
            'category'         => $category,
            'status'           => $status,
            'account_id'       => $accountId,
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
        ]);

        $totalRecords = $this->transactionModel->countLedger($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $transactions = $this->transactionModel->getLedgerWithDetails($filters, $perPage, $offset);

        // Summary KPI figures
        $allTx = $this->transactionModel->all();
        $totalCredits = 0.0;
        $totalDebits = 0.0;
        foreach ($allTx as $tx) {
            if ($tx['transaction_type'] === 'credit') {
                $totalCredits += (float)$tx['amount'];
            } else {
                $totalDebits += (float)$tx['amount'];
            }
        }

        $accounts = $this->accountModel->getActiveAccounts();
        $dbCatMap = $this->categoryModel->getCategoryMap();
        $categories = !empty($dbCatMap) ? $dbCatMap : config('finance.expense_categories', []);

        return $this->render('office/finance/transactions', [
            'title'          => 'General Ledger & Transactions',
            'transactions'   => $transactions,
            'accounts'       => $accounts,
            'categories'     => $categories,
            'totalRecords'   => $totalRecords,
            'totalAll'       => count($allTx),
            'totalCredits'   => $totalCredits,
            'totalDebits'    => $totalDebits,
            'netBalance'     => ($totalCredits - $totalDebits),
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'search'         => $search,
            'selectedType'   => $type,
            'selectedCategory' => $category,
            'selectedStatus'   => $status,
            'selectedAccount'  => $accountId,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo,
        ], 'layouts/office');
    }

    /**
     * Record a manual credit or debit transaction
     */
    public function recordTransaction(Request $request): Response
    {
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/transactions');
        try {
            $accountId = (int)$request->post('account_id');
            $type = trim((string)$request->post('transaction_type', 'credit'));
            $amount = (float)$request->post('amount', 0);
            $category = trim((string)$request->post('category', 'General'));
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $ref = trim((string)$request->post('transaction_ref', ''));
            $notes = trim((string)$request->post('notes', ''));
            $transactedAt = trim((string)$request->post('transacted_at', date('Y-m-d H:i:s')));

            if ($accountId <= 0 || $amount <= 0) {
                $err = 'Please specify a valid account and an amount greater than zero.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $this->transactionModel->recordTransaction([
                'account_id'       => $accountId,
                'transaction_type' => $type,
                'amount'           => $amount,
                'category'         => $category,
                'payment_method'   => $paymentMethod,
                'transaction_ref'  => $ref,
                'notes'            => $notes,
                'created_by'       => Session::getUserId(),
                'transacted_at'    => $transactedAt ?: date('Y-m-d H:i:s'),
            ]);

            $msg = 'Transaction successfully committed to the immutable ledger.';
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => $redirectTo,
                ]);
            }
            return $this->redirect($redirectTo);
        } catch (Throwable $e) {
            $err = 'Failed to record transaction: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * View single transaction voucher details (Dedicated View Page matching office/customers/30)
     */
    public function showTransaction(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $tx = $this->transactionModel->findWithDetails($id);
        if (!$tx) {
            if ($request->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Transaction record not found.'], 404);
            }
            Session::setFlash('error', 'Transaction record not found.');
            return $this->redirect(url('/office/finance/transactions'));
        }

        if ($request->wantsJson()) {
            return $this->json(['success' => true, 'transaction' => $tx]);
        }

        $activeAccounts = $this->accountModel->getActiveAccounts();
        $account = $this->accountModel->find((int)$tx['account_id']);

        // Fetch contextual linked data if available
        $linkedData = null;
        if (!empty($tx['reference_type']) && !empty($tx['reference_id'])) {
            $pdo = \App\Core\Database::getConnection();
            if ($tx['reference_type'] === 'booking') {
                $stmt = $pdo->prepare("SELECT b.booking_code, b.total_amount, b.status as booking_status, c.name as customer_name, c.phone as customer_phone FROM `bookings` b LEFT JOIN `customers` c ON b.customer_profile_id = c.id WHERE b.id = ?");
                $stmt->execute([(int)$tx['reference_id']]);
                $linkedData = $stmt->fetch(\PDO::FETCH_ASSOC);
            } elseif ($tx['reference_type'] === 'business_expense') {
                $stmt = $pdo->prepare("SELECT expense_code, category, vendor_name, amount, status as expense_status FROM `business_expenses` WHERE id = ?");
                $stmt->execute([(int)$tx['reference_id']]);
                $linkedData = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        $refTitle = $tx['transaction_ref'] ?: ('TX-' . str_pad($tx['id'], 5, '0', STR_PAD_LEFT));

        return $this->render('office/finance/transaction_show', [
            'title'          => "{$refTitle} — Transaction Voucher Details",
            'transaction'    => $tx,
            'account'        => $account,
            'activeAccounts' => $activeAccounts,
            'linkedData'     => $linkedData
        ], 'layouts/office');
    }

    /**
     * Update transaction voucher details
     */
    public function updateTransaction(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $redirectTo = url('/office/finance/transactions');
        try {
            $category = trim((string)$request->post('category', ''));
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $ref = trim((string)$request->post('transaction_ref', ''));
            $notes = trim((string)$request->post('notes', ''));
            $status = trim((string)$request->post('status', 'completed'));
            $transactedAt = trim((string)$request->post('transacted_at', ''));

            $ok = $this->transactionModel->updateTransaction($id, [
                'category'        => $category,
                'payment_method'  => $paymentMethod,
                'transaction_ref' => $ref,
                'notes'           => $notes,
                'status'          => $status,
                'transacted_at'   => $transactedAt ?: date('Y-m-d H:i:s')
            ]);

            if (!$ok) {
                $err = 'Transaction record not found or could not be updated.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $msg = 'Transaction voucher successfully updated.';
            Session::setFlash('success', $msg);
            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (\Throwable $e) {
            $err = 'Failed to update transaction: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * Delete transaction voucher entry & reconcile balance
     */
    public function deleteTransaction(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/transactions');
        try {
            $ok = $this->transactionModel->deleteTransaction($id);
            if (!$ok) {
                $err = 'Transaction record not found or could not be deleted.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $msg = 'Transaction voucher deleted and account balance reconciled.';
            Session::setFlash('success', $msg);
            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (\Throwable $e) {
            $err = 'Failed to delete transaction: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * AJAX Toggle transaction status between completed and cancelled
     */
    public function toggleTransactionStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $targetStatus = trim((string)$request->post('status', ''));
            $res = $this->transactionModel->toggleStatus($id, $targetStatus ?: null);
            return $this->json($res);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Internal Account-to-Account Fund Transfer
     */
    public function transfer(Request $request): Response
    {
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/accounts');
        try {
            $fromAccountId = (int)$request->post('from_account_id');
            $toAccountId = (int)$request->post('to_account_id');
            $amount = (float)$request->post('amount', 0);
            $ref = trim((string)$request->post('transfer_ref', 'TRF-' . date('Ymd-His')));
            $notes = trim((string)$request->post('notes', 'Internal account transfer'));

            if ($fromAccountId <= 0 || $toAccountId <= 0 || $fromAccountId === $toAccountId) {
                $err = 'Please select two different accounts for the fund transfer.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            if ($amount <= 0) {
                $err = 'Transfer amount must be greater than zero.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $fromAcc = $this->accountModel->find($fromAccountId);
            $toAcc = $this->accountModel->find($toAccountId);

            if (!$fromAcc || !$toAcc) {
                $err = 'One or both selected accounts do not exist.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $transferRes = $this->transactionModel->transferFunds([
                'from_account_id' => $fromAccountId,
                'to_account_id'   => $toAccountId,
                'amount'          => $amount,
                'transfer_ref'    => $ref,
                'notes'           => $notes,
                'created_by'      => Session::getUserId(),
            ]);

            $msg = "Successfully transferred " . format_currency($amount) . " from {$transferRes['from_account']} to {$transferRes['to_account']}.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => $redirectTo,
                ]);
            }
            return $this->redirect($redirectTo);
        } catch (Throwable $e) {
            $err = 'Transfer failed: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * 3. Accounts & Banking
     */
    public function accounts(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $type = trim((string)$request->get('type', ''));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 15, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $filters = array_filter([
            'search' => $search,
            'type'   => $type,
            'status' => $status,
        ]);

        $totalRecords = $this->accountModel->countAccounts($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $accounts = $this->accountModel->getAccountsWithFilters($filters, $perPage, $offset);

        // Overall Treasury KPI stats across all active accounts
        $allAccounts = $this->accountModel->all();
        $totalBalance = 0.0;
        $bankBalance = 0.0;
        $cashBalance = 0.0;
        $gatewayBalance = 0.0;
        $bankCount = 0;
        $cashCount = 0;
        $gatewayCount = 0;

        foreach ($allAccounts as $acc) {
            $bal = (float)$acc['current_balance'];
            if ($acc['status'] === 'active') {
                $totalBalance += $bal;
            }
            if ($acc['account_type'] === 'bank') {
                if ($acc['status'] === 'active') $bankBalance += $bal;
                $bankCount++;
            } elseif ($acc['account_type'] === 'cash') {
                if ($acc['status'] === 'active') $cashBalance += $bal;
                $cashCount++;
            } elseif ($acc['account_type'] === 'gateway') {
                if ($acc['status'] === 'active') $gatewayBalance += $bal;
                $gatewayCount++;
            }
        }

        $activeAccounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/accounts', [
            'title'          => 'Bank Accounts & Cash Treasury',
            'accounts'       => $accounts,
            'activeAccounts' => $activeAccounts,
            'totalBalance'   => $totalBalance,
            'bankBalance'    => $bankBalance,
            'cashBalance'    => $cashBalance,
            'gatewayBalance' => $gatewayBalance,
            'bankCount'      => $bankCount,
            'cashCount'      => $cashCount,
            'gatewayCount'   => $gatewayCount,
            'accountCount'   => count($allAccounts),
            'totalRecords'   => $totalRecords,
            'totalAll'       => count($allAccounts),
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'search'         => $search,
            'selectedType'   => $type,
            'selectedStatus' => $status,
            'typesConfig'    => config('finance.account_types', []),
        ], 'layouts/office');
    }

    /**
     * Store new financial account
     */
    public function storeAccount(Request $request): Response
    {
        try {
            $name = trim((string)$request->post('account_name', ''));
            $type = trim((string)$request->post('account_type', 'bank'));
            $accNum = trim((string)$request->post('account_number', ''));
            $bankName = trim((string)$request->post('bank_name', ''));
            $ifsc = trim((string)$request->post('ifsc_code', ''));
            $openingBal = (float)$request->post('opening_balance', 0.0);

            if (empty($name)) {
                $err = 'Account Name is required.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/accounts'));
            }

            $this->accountModel->create([
                'account_name'    => $name,
                'account_type'    => in_array($type, ['cash', 'bank', 'gateway'], true) ? $type : 'bank',
                'account_number'  => $accNum ?: null,
                'bank_name'       => $bankName ?: null,
                'ifsc_code'       => $ifsc ? strtoupper($ifsc) : null,
                'opening_balance' => $openingBal,
                'current_balance' => $openingBal,
                'status'          => 'active',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $msg = "Finance Account '{$name}' created successfully with opening balance of " . format_currency($openingBal);
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/accounts'),
                ]);
            }
            return $this->redirect(url('/office/finance/accounts'));
        } catch (Throwable $e) {
            $err = 'Failed to create account: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/accounts'));
        }
    }

    /**
     * View single account details & passbook
     */
    public function accountShow(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $account = $this->accountModel->findByIdWithStats($id);

        if (!$account) {
            Session::setFlash('error', 'Treasury Account not found.');
            return $this->redirect(url('/office/finance/accounts'));
        }

        $activeAccounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/account_show', [
            'title'          => "{$account['account_name']} — Vault Details",
            'account'        => $account,
            'activeAccounts' => $activeAccounts,
        ], 'layouts/office');
    }

    /**
     * Update account details
     */
    public function updateAccount(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $redirectTo = url('/office/finance/accounts');

        try {
            $account = $this->accountModel->find($id);
            if (!$account) {
                $err = 'Treasury Account not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/accounts'));
            }

            $name = trim((string)$request->post('account_name', $account['account_name']));
            $type = trim((string)$request->post('account_type', $account['account_type']));
            $accNum = trim((string)$request->post('account_number', ''));
            $bankName = trim((string)$request->post('bank_name', ''));
            $ifsc = trim((string)$request->post('ifsc_code', ''));
            $status = trim((string)$request->post('status', $account['status']));

            if (empty($name)) {
                $err = 'Account Name is required.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $this->accountModel->update($id, [
                'account_name'   => $name,
                'account_type'   => in_array($type, ['cash', 'bank', 'gateway'], true) ? $type : $account['account_type'],
                'account_number' => $accNum ?: null,
                'bank_name'      => $bankName ?: null,
                'ifsc_code'      => $ifsc ? strtoupper($ifsc) : null,
                'status'         => in_array($status, ['active', 'inactive'], true) ? $status : $account['status'],
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            $msg = "Account '{$name}' updated successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => $redirectTo,
                ]);
            }
            return $this->redirect($redirectTo);
        } catch (Throwable $e) {
            $err = 'Failed to update account: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * AJAX Toggle Account Active / Inactive Status
     */
    public function toggleAccountStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $account = $this->accountModel->find($id);
            if (!$account) {
                return $this->json(['success' => false, 'message' => 'Account not found.'], 404);
            }

            $targetStatus = trim((string)$request->post('status', ''));
            if (!in_array($targetStatus, ['active', 'inactive'], true)) {
                $targetStatus = ($account['status'] === 'active') ? 'inactive' : 'active';
            }

            $this->accountModel->update($id, [
                'status'     => $targetStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $statusText = strtoupper($targetStatus);
            $label = ucfirst($targetStatus);
            return $this->json([
                'success'    => true,
                'status'     => $targetStatus,
                'status_txt' => $statusText,
                'label'      => $label,
                'message'    => "Account '{$account['account_name']}' is now {$label}."
            ]);
        } catch (Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Status toggle failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. Operating Expenses
     */
    public function expenses(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $category = trim((string)$request->get('category', ''));
        $accountId = (int)$request->get('account_id', 0);
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'     => $search,
            'status'     => $status,
            'category'   => $category,
            'account_id' => $accountId,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
        ]);

        $totalRecords = $this->expenseModel->countExpenses($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $expenses = $this->expenseModel->getExpensesWithDetails($filters, $perPage, $offset);

        // Expense totals
        $allExp = $this->expenseModel->all();
        $totalPaid = 0.0;
        $totalPending = 0.0;
        $fuelTotal = 0.0;
        $pendingCount = 0;

        foreach ($allExp as $e) {
            $amt = (float)$e['amount'];
            if ($e['status'] === 'paid') {
                $totalPaid += $amt;
            } elseif ($e['status'] === 'pending') {
                $totalPending += $amt;
                $pendingCount++;
            }
            if ($e['category'] === 'fuel_bulk') {
                $fuelTotal += $amt;
            }
        }

        $accounts = $this->accountModel->getActiveAccounts();
        $dbCatMap = $this->categoryModel->getCategoryMap();
        $categories = !empty($dbCatMap) ? $dbCatMap : config('finance.expense_categories', []);

        return $this->render('office/finance/expenses', [
            'title'            => 'Operating & Fleet Expenses',
            'expenses'         => $expenses,
            'accounts'         => $accounts,
            'categories'       => $categories,
            'totalRecords'     => $totalRecords,
            'totalAll'         => count($allExp),
            'totalPaid'        => $totalPaid,
            'totalPending'     => $totalPending,
            'fuelTotal'        => $fuelTotal,
            'pendingCount'     => $pendingCount,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'perPage'          => $perPage,
            'search'           => $search,
            'selectedStatus'   => $status,
            'selectedCategory' => $category,
            'selectedAccount'  => $accountId,
            'dateFrom'         => $dateFrom,
            'dateTo'           => $dateTo,
        ], 'layouts/office');
    }

    /**
     * Store new expense record
     */
    public function storeExpense(Request $request): Response
    {
        try {
            $category = trim((string)$request->post('category', 'misc'));
            $title = trim((string)$request->post('title', ''));
            $amount = (float)$request->post('amount', 0);
            $expenseDate = trim((string)$request->post('expense_date', date('Y-m-d')));
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $accountId = (int)$request->post('account_id', 0) ?: null;
            $vendor = trim((string)$request->post('vendor_name', ''));
            $notes = trim((string)$request->post('notes', ''));
            $status = trim((string)$request->post('status', 'paid'));

            if (empty($title) || $amount <= 0) {
                $err = 'Expense title and an amount greater than zero are required.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/expenses'));
            }

            $expenseCode = $this->expenseModel->generateExpenseCode();
            $userId = Session::getUserId();

            $expenseId = $this->expenseModel->create([
                'expense_code'   => $expenseCode,
                'category'       => $category,
                'title'          => $title,
                'amount'         => $amount,
                'expense_date'   => $expenseDate,
                'payment_method' => $paymentMethod,
                'account_id'     => $accountId,
                'vendor_name'    => $vendor ?: null,
                'status'         => in_array($status, ['pending', 'approved', 'paid', 'rejected'], true) ? $status : 'paid',
                'logged_by'      => $userId,
                'approved_by'    => ($status === 'paid' || $status === 'approved') ? $userId : null,
                'notes'          => $notes ?: null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            // If marked as paid with an active account, debit from ledger automatically
            if ($status === 'paid' && $accountId) {
                $this->transactionModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'debit',
                    'amount'           => $amount,
                    'category'         => 'Business Expense',
                    'reference_type'   => 'business_expense',
                    'reference_id'     => $expenseId,
                    'payment_method'   => $paymentMethod,
                    'transaction_ref'  => $expenseCode,
                    'notes'            => "Expense: {$title} (" . ($vendor ? "Vendor: {$vendor}" : 'General') . ")",
                    'created_by'       => $userId,
                    'transacted_at'    => $expenseDate . ' ' . date('H:i:s'),
                ]);
            }

            $msg = "Expense {$expenseCode} recorded successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/expenses'),
                ]);
            }
            return $this->redirect(url('/office/finance/expenses'));
        } catch (Throwable $e) {
            $err = 'Failed to save expense: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/expenses'));
        }
    }

    /**
     * Update Expense Approval / Payment Status
     */
    public function updateExpenseStatus(Request $request, array $params): Response
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $newStatus = trim((string)$request->post('status', ''));
            $accountId = (int)$request->post('account_id', 0) ?: null;

            $expense = $this->expenseModel->find($id);
            if (!$expense) {
                $err = 'Expense record not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/expenses'));
            }

            $currentStatus = $expense['status'];
            $userId = Session::getUserId();

            $updateData = [
                'status'     => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($newStatus === 'approved' || $newStatus === 'paid') {
                $updateData['approved_by'] = $userId;
            }

            if ($accountId) {
                $updateData['account_id'] = $accountId;
            }

            // If transitioning to paid from non-paid and account provided, debit account
            if ($newStatus === 'paid' && $currentStatus !== 'paid') {
                $targetAccount = $accountId ?: $expense['account_id'];
                if ($targetAccount) {
                    $this->transactionModel->recordTransaction([
                        'account_id'       => $targetAccount,
                        'transaction_type' => 'debit',
                        'amount'           => (float)$expense['amount'],
                        'category'         => 'Business Expense',
                        'reference_type'   => 'business_expense',
                        'reference_id'     => $id,
                        'payment_method'   => $expense['payment_method'],
                        'transaction_ref'  => $expense['expense_code'],
                        'notes'            => "Disbursed Expense: {$expense['title']}",
                        'created_by'       => $userId,
                        'transacted_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->expenseModel->update($id, $updateData);
            $msg = "Expense {$expense['expense_code']} updated to " . ucfirst($newStatus) . ".";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/expenses'),
                ]);
            }
            return $this->redirect(url('/office/finance/expenses'));
        } catch (Throwable $e) {
            $err = 'Status update failed: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/expenses'));
        }
    }

    /**
     * 5. Payroll & Salary Disbursals
     */
    public function payroll(Request $request): Response
    {
        $payrolls = $this->payrollModel->getPayrollsWithStats(30, 0);
        $employees = $this->employeeModel->where("status = 'active'", [], 'first_name ASC, last_name ASC');
        $accounts = $this->accountModel->getActiveAccounts();

        $totalDisbursed = 0.0;
        foreach ($payrolls as $p) {
            if ($p['status'] === 'paid') {
                $totalDisbursed += (float)$p['total_net'];
            }
        }

        return $this->render('office/finance/payroll', [
            'title'          => 'Payroll & Compensation Disbursals',
            'payrolls'       => $payrolls,
            'employees'      => $employees,
            'accounts'       => $accounts,
            'totalDisbursed' => $totalDisbursed,
            'batchCount'     => count($payrolls),
            'currentYear'    => (int)date('Y'),
            'currentMonth'   => (int)date('n'),
        ], 'layouts/office');
    }

    /**
     * Generate Monthly Payroll Run Batch
     */
    public function generatePayroll(Request $request): Response
    {
        try {
            $month = (int)$request->post('month', date('n'));
            $year = (int)$request->post('year', date('Y'));

            // Check if batch already exists
            $existing = $this->payrollModel->where("month = ? AND year = ?", [$month, $year]);
            if (!empty($existing)) {
                $err = "Payroll batch for " . date('F Y', mktime(0, 0, 0, $month, 10, $year)) . " already exists.";
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/payroll'));
            }

            $payrollCode = $this->payrollModel->generatePayrollCode($year, $month);
            $userId = Session::getUserId();

            // Create initial draft batch
            $payrollId = $this->payrollModel->create([
                'payroll_code'     => $payrollCode,
                'month'            => $month,
                'year'             => $year,
                'total_gross'      => 0.00,
                'total_deductions' => 0.00,
                'total_net'        => 0.00,
                'status'           => 'draft',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            // Query active employees and salary structures
            $employees = $this->employeeModel->where("status = 'active'", [], 'id ASC');
            $salaryStructModel = new SalaryStructure();

            $totalGross = 0.0;
            $totalDeductions = 0.0;
            $totalNet = 0.0;

            foreach ($employees as $emp) {
                $struct = $salaryStructModel->findByEmployeeId((int)$emp['id']);

                $basic = $struct ? (float)$struct['basic_salary'] : (float)($emp['salary'] ?? 18000.00);
                $allowances = $struct ? ((float)$struct['hra'] + (float)$struct['transport_allowance'] + (float)$struct['medical_allowance'] + (float)$struct['special_allowance']) : 0.00;
                $gross = $basic + $allowances;

                $statutoryDeductions = $struct ? ((float)$struct['pf_deduction'] + (float)$struct['esi_deduction'] + (float)$struct['professional_tax'] + (float)$struct['tds_deduction'] + (float)$struct['other_deductions']) : 0.00;

                // Check pending advances to deduct (up to 20% of net or actual remaining)
                $pendingAdvance = $this->advanceModel->getPendingAdvanceForEmployee((int)$emp['id']);
                $advanceToDeduct = min($pendingAdvance, round($gross * 0.15, 2));

                $totalEmpDeductions = $statutoryDeductions + $advanceToDeduct;
                $netSalary = max(0.0, $gross - $totalEmpDeductions);

                $payslipNumber = $this->payrollItemModel->generatePayslipNumber($year, $month, (int)$emp['id']);

                $this->payrollItemModel->create([
                    'payroll_id'       => $payrollId,
                    'employee_id'      => $emp['id'],
                    'payslip_number'   => $payslipNumber,
                    'basic_salary'     => $basic,
                    'allowances_total' => $allowances,
                    'gross_salary'     => $gross,
                    'deductions_total' => $statutoryDeductions,
                    'advance_deducted' => $advanceToDeduct,
                    'net_salary'       => $netSalary,
                    'payment_status'   => 'unpaid',
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);

                $totalGross += $gross;
                $totalDeductions += $totalEmpDeductions;
                $totalNet += $netSalary;
            }

            // Update master payroll sums
            $this->payrollModel->update($payrollId, [
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net'        => $totalNet,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            $msg = "Payroll batch {$payrollCode} drafted with " . count($employees) . " employee payslips.";
            Session::setFlash('success', $msg);
            $target = url('/office/finance/payroll');

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => $target,
                ]);
            }
            return $this->redirect($target);
        } catch (Throwable $e) {
            $err = 'Failed to generate payroll: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/payroll'));
        }
    }

    /**
     * View detailed payroll batch
     */
    public function payrollShow(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $payroll = $this->payrollModel->findWithItems($id);

        if (!$payroll) {
            Session::setFlash('error', 'Payroll batch not found.');
            return $this->redirect(url('/office/finance/payroll'));
        }

        // Auto-populate draft batch if it has 0 items but active employees exist
        if ($payroll['status'] === 'draft' && empty($payroll['items'])) {
            $this->syncDraftPayrollItems($id, (int)$payroll['year'], (int)$payroll['month']);
            $payroll = $this->payrollModel->findWithItems($id);
        }

        $accounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/payroll_show', [
            'title'    => "Payroll Batch: {$payroll['payroll_code']}",
            'payroll'  => $payroll,
            'accounts' => $accounts,
        ], 'layouts/office');
    }

    /**
     * Auto-sync active employees into an empty draft payroll batch
     */
    private function syncDraftPayrollItems(int $payrollId, int $year, int $month): void
    {
        $employees = $this->employeeModel->where("status = 'active'", [], 'id ASC');
        if (empty($employees)) {
            return;
        }

        $salaryStructModel = new SalaryStructure();
        $totalGross = 0.0;
        $totalDeductions = 0.0;
        $totalNet = 0.0;

        foreach ($employees as $emp) {
            $struct = $salaryStructModel->findByEmployeeId((int)$emp['id']);
            $basic = $struct ? (float)$struct['basic_salary'] : 18000.00;
            $allowances = $struct ? ((float)$struct['hra'] + (float)$struct['transport_allowance'] + (float)$struct['medical_allowance'] + (float)$struct['special_allowance']) : 0.00;
            $gross = $basic + $allowances;

            $statutoryDeductions = $struct ? ((float)$struct['pf_deduction'] + (float)$struct['esi_deduction'] + (float)$struct['professional_tax'] + (float)$struct['tds_deduction'] + (float)$struct['other_deductions']) : 0.00;

            $pendingAdvance = $this->advanceModel->getPendingAdvanceForEmployee((int)$emp['id']);
            $advanceToDeduct = min($pendingAdvance, round($gross * 0.15, 2));

            $totalEmpDeductions = $statutoryDeductions + $advanceToDeduct;
            $netSalary = max(0.0, $gross - $totalEmpDeductions);

            $payslipNumber = $this->payrollItemModel->generatePayslipNumber($year, $month, (int)$emp['id']);

            $this->payrollItemModel->create([
                'payroll_id'       => $payrollId,
                'employee_id'      => $emp['id'],
                'payslip_number'   => $payslipNumber,
                'basic_salary'     => $basic,
                'allowances_total' => $allowances,
                'gross_salary'     => $gross,
                'deductions_total' => $statutoryDeductions,
                'advance_deducted' => $advanceToDeduct,
                'net_salary'       => $netSalary,
                'payment_status'   => 'unpaid',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            $totalGross += $gross;
            $totalDeductions += $totalEmpDeductions;
            $totalNet += $netSalary;
        }

        $this->payrollModel->update($payrollId, [
            'total_gross'      => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'        => $totalNet,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark Entire Payroll Batch as Paid
     */
    public function markPayrollPaid(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $accountId = (int)$request->post('account_id', 0);
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $ref = trim((string)$request->post('payment_ref', 'PAYROLL-' . date('Ymd')));

            $payroll = $this->payrollModel->findWithItems($id);
            if (!$payroll) {
                $err = 'Payroll batch not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/payroll'));
            }

            if ($payroll['status'] === 'paid') {
                $err = 'Payroll batch is already marked as paid.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/payroll'));
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');
            $totalNet = (float)$payroll['total_net'];

            if (empty($payroll['items']) || $totalNet <= 0) {
                $err = 'Cannot disburse an empty payroll batch. Please ensure staff members are present in the batch.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/payroll'));
            }

            // 1. Debit total payroll amount from selected account
            if ($accountId > 0 && $totalNet > 0) {
                $this->transactionModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'debit',
                    'amount'           => $totalNet,
                    'category'         => 'Staff Salaries',
                    'reference_type'   => 'payroll_batch',
                    'reference_id'     => $id,
                    'payment_method'   => $paymentMethod,
                    'transaction_ref'  => $ref,
                    'notes'            => "Salary Disbursal for Batch {$payroll['payroll_code']} (" . count($payroll['items']) . " employees)",
                    'created_by'       => $userId,
                    'transacted_at'    => $now,
                ]);
            }

            // 2. Mark all items as paid
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("UPDATE `payroll_items` SET `payment_status` = 'paid', `payment_method` = ?, `payment_ref` = ?, `paid_at` = ?, `updated_at` = ? WHERE `payroll_id` = ?");
            $stmt->execute([$paymentMethod, $ref, $now, $now, $id]);

            // 3. Mark batch as paid
            $this->payrollModel->update($id, [
                'status'       => 'paid',
                'payment_date' => date('Y-m-d'),
                'approved_by'  => $userId,
                'paid_by'      => $userId,
                'updated_at'   => $now,
            ]);

            $msg = "Payroll {$payroll['payroll_code']} disbursed successfully. Total: " . format_currency($totalNet);
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/payroll'),
                ]);
            }
            return $this->redirect(url('/office/finance/payroll'));
        } catch (Throwable $e) {
            $err = 'Failed to disburse payroll: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/payroll'));
        }
    }

    /**
     * View Individual Employee Payslip
     */
    public function payslip(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $item = $this->payrollItemModel->findWithDetails($id);

        if (!$item) {
            Session::setFlash('error', 'Payslip record not found.');
            return $this->redirect(url('/office/finance/payroll'));
        }

        return $this->render('office/finance/payslip', [
            'title'   => "Payslip {$item['payslip_number']}",
            'payslip' => $item,
        ], 'layouts/office');
    }

    /**
     * 6. Employee Advances
     */
    public function advances(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search' => $search,
            'status' => $status,
        ]);

        $totalRecords = $this->advanceModel->countAdvances($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $advances = $this->advanceModel->getAdvancesWithEmployeeDetails($filters, $perPage, $offset);

        $allAdv = $this->advanceModel->all();
        $totalIssued = 0.0;
        $totalRecovered = 0.0;
        $pendingCount = 0;

        foreach ($allAdv as $a) {
            if ($a['status'] === 'approved' || $a['status'] === 'recovered') {
                $totalIssued += (float)$a['amount'];
                $totalRecovered += (float)$a['recovered_amount'];
            }
            if ($a['status'] === 'pending') {
                $pendingCount++;
            }
        }

        $employees = $this->employeeModel->where("status = 'active'", [], 'first_name ASC, last_name ASC');
        $accounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/advances', [
            'title'          => 'Employee Advance Loans & Recoveries',
            'advances'       => $advances,
            'employees'      => $employees,
            'accounts'       => $accounts,
            'totalRecords'   => $totalRecords,
            'totalAll'       => count($allAdv),
            'totalIssued'    => $totalIssued,
            'totalRecovered' => $totalRecovered,
            'pendingBalance' => max(0.0, $totalIssued - $totalRecovered),
            'pendingCount'   => $pendingCount,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'search'         => $search,
            'selectedStatus' => $status,
        ], 'layouts/office');
    }

    /**
     * Store new employee advance request
     */
    public function storeAdvance(Request $request): Response
    {
        try {
            $employeeId = (int)$request->post('employee_id');
            $amount = (float)$request->post('amount', 0);
            $reason = trim((string)$request->post('reason', ''));
            $reqDate = trim((string)$request->post('requested_date', date('Y-m-d')));

            if ($employeeId <= 0 || $amount <= 0 || empty($reason)) {
                $err = 'Employee, valid amount, and purpose reason are required.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/advances'));
            }

            $code = $this->advanceModel->generateAdvanceCode();
            $this->advanceModel->create([
                'advance_code'     => $code,
                'employee_id'      => $employeeId,
                'amount'           => $amount,
                'reason'           => $reason,
                'status'           => 'pending',
                'recovered_amount' => 0.00,
                'requested_date'   => $reqDate,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            $msg = "Advance request {$code} registered successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/advances'),
                ]);
            }
            return $this->redirect(url('/office/finance/advances'));
        } catch (Throwable $e) {
            $err = 'Failed to register advance: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/advances'));
        }
    }

    /**
     * Approve or Reject Employee Advance
     */
    public function updateAdvanceStatus(Request $request, array $params): Response
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $status = trim((string)$request->post('status', ''));
            $accountId = (int)$request->post('account_id', 0) ?: null;

            $advance = $this->advanceModel->find($id);
            if (!$advance) {
                $err = 'Advance record not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/advances'));
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');

            if ($status === 'approved' && $accountId) {
                // Debit advance payout from selected account
                $emp = $this->employeeModel->find((int)$advance['employee_id']);
                $empName = $emp ? trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) : "Employee #{$advance['employee_id']}";

                $this->transactionModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'debit',
                    'amount'           => (float)$advance['amount'],
                    'category'         => 'Staff Advance',
                    'reference_type'   => 'employee_advance',
                    'reference_id'     => $id,
                    'payment_method'   => 'cash',
                    'transaction_ref'  => $advance['advance_code'],
                    'notes'            => "Disbursed advance to {$empName}. Reason: {$advance['reason']}",
                    'created_by'       => $userId,
                    'transacted_at'    => $now,
                ]);
            }

            $this->advanceModel->update($id, [
                'status'      => $status,
                'approved_by' => ($status === 'approved') ? $userId : null,
                'approved_at' => ($status === 'approved') ? $now : null,
                'updated_at'  => $now,
            ]);

            $msg = "Advance {$advance['advance_code']} marked as " . ucfirst($status) . ".";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/advances'),
                ]);
            }
            return $this->redirect(url('/office/finance/advances'));
        } catch (Throwable $e) {
            $err = 'Action failed: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/advances'));
        }
    }

    /**
    /**
     * Show single employee advance profile / voucher view
     */
    public function showAdvance(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $adv = $this->advanceModel->findWithDetails($id);
        if (!$adv) {
            if ($request->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Staff advance record not found.'], 404);
            }
            Session::setFlash('error', 'Staff advance record not found.');
            return $this->redirect(url('/office/finance/advances'));
        }

        if ($request->wantsJson()) {
            return $this->json(['success' => true, 'advance' => $adv]);
        }

        $employee = $this->employeeModel->find((int)$adv['employee_id']);
        $accounts = $this->accountModel->getActiveAccounts();
        $employees = $this->employeeModel->where("status = 'active'", [], 'first_name ASC, last_name ASC');

        // Fetch linked journal disbursement transaction if any
        $linkedTransaction = null;
        try {
            $pdo = \App\Core\Database::getConnection();
            $txStmt = $pdo->prepare("SELECT t.*, a.account_name, a.bank_name, a.account_number, a.ifsc_code 
                                     FROM `finance_transactions` t 
                                     LEFT JOIN `finance_accounts` a ON t.account_id = a.id 
                                     WHERE t.reference_type = 'employee_advance' AND t.reference_id = ? 
                                     ORDER BY t.id DESC LIMIT 1");
            $txStmt->execute([$id]);
            $linkedTransaction = $txStmt->fetch() ?: null;
        } catch (\Throwable $e) {
            $linkedTransaction = null;
        }

        // Fetch payroll deduction history where advance was deducted
        $deductions = [];
        try {
            $pdo = \App\Core\Database::getConnection();
            $dedStmt = $pdo->prepare("SELECT pi.*, p.month, p.year, p.payroll_code, p.status as payroll_status 
                                     FROM `payroll_items` pi 
                                     JOIN `payrolls` p ON pi.payroll_id = p.id 
                                     WHERE pi.employee_id = ? AND pi.advance_deducted > 0 
                                     ORDER BY p.year DESC, p.month DESC");
            $dedStmt->execute([(int)$adv['employee_id']]);
            $deductions = $dedStmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $deductions = [];
        }

        return $this->render('office/finance/advance_show', [
            'title'             => "Advance Voucher #{$adv['advance_code']}",
            'advance'           => $adv,
            'employee'          => $employee,
            'accounts'          => $accounts,
            'employees'         => $employees,
            'linkedTransaction' => $linkedTransaction,
            'deductions'        => $deductions,
        ], 'layouts/office');
    }

    /**
     * Update employee advance record
     */
    public function updateAdvance(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $redirectTo = url('/office/finance/advances');
        try {
            $adv = $this->advanceModel->find($id);
            if (!$adv) {
                $err = 'Staff advance record not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $amount = (float)$request->post('amount', $adv['amount']);
            $reason = trim((string)$request->post('reason', $adv['reason']));
            $reqDate = trim((string)$request->post('requested_date', $adv['requested_date']));
            $notes = trim((string)$request->post('notes', $adv['notes'] ?? ''));
            $status = trim((string)$request->post('status', $adv['status']));
            $accountId = (int)$request->post('account_id', 0) ?: null;

            if ($amount <= 0 || empty($reason)) {
                $err = 'Valid advance amount and reason are required.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');

            $data = [
                'amount'         => $amount,
                'reason'         => $reason,
                'requested_date' => $reqDate,
                'notes'          => $notes,
                'status'         => $status,
                'updated_at'     => $now,
            ];

            if ($status === 'approved' && $adv['status'] !== 'approved') {
                $data['approved_by'] = $userId;
                $data['approved_at'] = $now;

                if ($accountId) {
                    $emp = $this->employeeModel->find((int)$adv['employee_id']);
                    $empName = $emp ? trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) : "Employee #{$adv['employee_id']}";
                    $this->transactionModel->recordTransaction([
                        'account_id'       => $accountId,
                        'transaction_type' => 'debit',
                        'amount'           => $amount,
                        'category'         => 'Staff Advance',
                        'reference_type'   => 'employee_advance',
                        'reference_id'     => $id,
                        'payment_method'   => 'cash',
                        'transaction_ref'  => $adv['advance_code'],
                        'notes'            => "Disbursed advance to {$empName}. Reason: {$reason}",
                        'created_by'       => $userId,
                        'transacted_at'    => $now,
                    ]);
                }
            }

            $this->advanceModel->update($id, $data);

            $msg = "Advance {$adv['advance_code']} successfully updated.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (\Throwable $e) {
            $err = 'Failed to update advance: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * Delete employee advance record
     */
    public function deleteAdvance(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/advances');
        try {
            $adv = $this->advanceModel->find($id);
            if (!$adv) {
                $err = 'Staff advance record not found or could not be deleted.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $code = $adv['advance_code'];
            $this->advanceModel->delete($id);

            $msg = "Advance record {$code} deleted successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (\Throwable $e) {
            $err = 'Failed to delete advance: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * AJAX Toggle advance status between approved, pending, recovered, rejected
     */
    public function toggleAdvanceStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $targetStatus = trim((string)$request->post('status', ''));
            $userId = Session::getUserId();
            $res = $this->advanceModel->toggleStatus($id, $targetStatus ?: null, $userId);
            return $this->json($res);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 7. Customer Refunds & Adjustments List
     */
    public function refunds(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search' => $search,
            'status' => $status,
        ]);

        $totalRecords = $this->refundModel->countRefunds($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $refunds = $this->refundModel->getRefundsWithDetails($filters, $perPage, $offset);

        $allRef = $this->refundModel->all();
        $totalProcessed = 0.0;
        $totalPending = 0.0;
        $pendingCount = 0;

        foreach ($allRef as $r) {
            $amt = (float)$r['amount'];
            if ($r['status'] === 'processed') {
                $totalProcessed += $amt;
            } elseif ($r['status'] === 'pending') {
                $totalPending += $amt;
                $pendingCount++;
            }
        }

        $accounts = $this->accountModel->getActiveAccounts();

        // Invoices with paid amounts eligible for refund
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->query("SELECT i.id, i.invoice_number, i.total_amount, i.paid_amount, i.booking_id, b.booking_code, c.name as customer_name FROM invoices i LEFT JOIN bookings b ON i.booking_id = b.id LEFT JOIN customers c ON b.customer_profile_id = c.id WHERE i.paid_amount > 0 ORDER BY i.id DESC LIMIT 100");
        $eligibleInvoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('office/finance/refunds', [
            'title'            => 'Customer Refunds & Adjustments',
            'refunds'          => $refunds,
            'accounts'         => $accounts,
            'eligibleInvoices' => $eligibleInvoices,
            'totalRecords'     => $totalRecords,
            'totalAll'         => count($allRef),
            'totalProcessed'   => $totalProcessed,
            'totalPending'     => $totalPending,
            'pendingCount'     => $pendingCount,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'perPage'          => $perPage,
            'search'           => $search,
            'selectedStatus'   => $status,
        ], 'layouts/office');
    }

    /**
     * Store new Customer Refund
     */
    public function storeRefund(Request $request): Response
    {
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/refunds');
        try {
            $invoiceId = (int)$request->post('invoice_id', 0) ?: null;
            $amount = (float)$request->post('amount', 0);
            $refundMethod = trim((string)$request->post('refund_method', 'bank_transfer'));
            $reason = trim((string)$request->post('reason', 'Customer cancellation / trip adjustment'));
            $accountId = (int)$request->post('account_id', 0) ?: null;
            $refundDate = trim((string)$request->post('refund_date', date('Y-m-d')));
            $autoProcess = (int)$request->post('auto_process', 1);

            if ($amount <= 0) {
                $err = 'Refund amount must be greater than zero.';
                if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 422);
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $bookingId = null;
            $invoice = null;
            if ($invoiceId) {
                $invoice = $this->invoiceModel->find($invoiceId);
                if (!$invoice) {
                    $err = 'Specified invoice not found.';
                    if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 404);
                    Session::setFlash('error', $err);
                    return $this->redirect($redirectTo);
                }
                $bookingId = $invoice['booking_id'] ?? null;

                // Check maximum allowable refund to prevent duplicate/excess refunds
                $pdo = \App\Core\Database::getConnection();
                $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE invoice_id = ? AND status = 'processed'");
                $stmt->execute([$invoiceId]);
                $alreadyRefunded = (float)$stmt->fetchColumn();
                $maxRefund = max(0.0, (float)$invoice['paid_amount'] - $alreadyRefunded);

                if ($amount > $maxRefund) {
                    $err = 'Refund amount cannot exceed remaining refundable paid amount (' . format_inr($maxRefund) . ').';
                    if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 422);
                    Session::setFlash('error', $err);
                    return $this->redirect($redirectTo);
                }
            }

            $now = date('Y-m-d H:i:s');
            $userId = Session::getUserId();
            $refundNumber = 'REF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $status = ($autoProcess && $accountId) ? 'processed' : 'pending';

            $refundId = $this->refundModel->create([
                'booking_id'    => $bookingId,
                'invoice_id'    => $invoiceId,
                'refund_number' => $refundNumber,
                'amount'        => $amount,
                'reason'        => $reason,
                'refund_method' => $refundMethod,
                'refund_date'   => $refundDate ?: date('Y-m-d'),
                'status'        => $status,
                'processed_by'  => ($status === 'processed') ? $userId : null,
                'processed_at'  => ($status === 'processed') ? $now : null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            if ($status === 'processed') {
                // Adjust invoice paid amount and payment status
                if ($invoice) {
                    $newPaid = max(0.0, (float)$invoice['paid_amount'] - $amount);
                    $totalAmt = (float)$invoice['total_amount'];
                    $newStatus = ($newPaid >= $totalAmt && $totalAmt > 0) ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
                    $this->invoiceModel->update((int)$invoice['id'], [
                        'paid_amount'    => $newPaid,
                        'payment_status' => $newStatus,
                        'updated_at'     => $now,
                    ]);
                }

                // Debit account
                if ($accountId) {
                    $this->transactionModel->recordTransaction([
                        'account_id'       => $accountId,
                        'transaction_type' => 'debit',
                        'amount'           => $amount,
                        'category'         => 'Booking Refund',
                        'reference_type'   => 'refund',
                        'reference_id'     => $refundId,
                        'payment_method'   => $refundMethod,
                        'transaction_ref'  => $refundNumber,
                        'notes'            => "Processed Refund: {$refundNumber}. Reason: {$reason}",
                        'created_by'       => $userId,
                        'transacted_at'    => $refundDate ? ($refundDate . ' 12:00:00') : $now,
                    ]);
                }
            }

            $msg = "Refund {$refundNumber} created successfully (" . ucfirst($status) . ").";
            Session::setFlash('success', $msg);
            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (Throwable $e) {
            $err = 'Failed to create refund: ' . $e->getMessage();
            if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 500);
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * Process or Update Refund Status
     */
    public function updateRefundStatus(Request $request, array $params): Response
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $status = trim((string)$request->post('status', ''));
            $accountId = (int)$request->post('account_id', 0) ?: null;

            $refund = $this->refundModel->find($id);
            if (!$refund) {
                $err = 'Refund record not found.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 404);
                }
                Session::setFlash('error', $err);
                return $this->redirect(url('/office/finance/refunds'));
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');

            if ($status === 'processed' && $refund['status'] !== 'processed') {
                // If invoice_id exists, adjust invoice paid_amount and payment_status
                if (!empty($refund['invoice_id'])) {
                    $invoice = $this->invoiceModel->find((int)$refund['invoice_id']);
                    if ($invoice) {
                        $newPaid = max(0.0, (float)$invoice['paid_amount'] - (float)$refund['amount']);
                        $totalAmt = (float)$invoice['total_amount'];
                        $newStatus = ($newPaid >= $totalAmt && $totalAmt > 0) ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
                        $this->invoiceModel->update((int)$invoice['id'], [
                            'paid_amount'    => $newPaid,
                            'payment_status' => $newStatus,
                            'updated_at'     => $now,
                        ]);
                    }
                }

                if ($accountId) {
                    $this->transactionModel->recordTransaction([
                        'account_id'       => $accountId,
                        'transaction_type' => 'debit',
                        'amount'           => (float)$refund['amount'],
                        'category'         => 'Booking Refund',
                        'reference_type'   => 'refund',
                        'reference_id'     => $id,
                        'payment_method'   => $refund['refund_method'],
                        'transaction_ref'  => $refund['refund_number'],
                        'notes'            => "Processed Refund: {$refund['refund_number']}. Reason: {$refund['reason']}",
                        'created_by'       => $userId,
                        'transacted_at'    => $now,
                    ]);
                }
            }

            $this->refundModel->update($id, [
                'status'       => $status,
                'processed_by' => ($status === 'processed') ? $userId : null,
                'processed_at' => ($status === 'processed') ? $now : null,
                'updated_at'   => $now,
            ]);

            $emailNotice = '';
            if ($status === 'processed') {
                try {
                    $refreshedRefund = $this->refundModel->find($id);
                    $emailService = new \App\Services\EmailService();
                    $emailRes = $emailService->sendRefundNotification($refreshedRefund);
                    if (!empty($emailRes['success'])) {
                        $emailNotice = ' Confirmation email sent.';
                    }
                } catch (\Throwable $ne) {
                    \App\Core\Logger::warning("Could not dispatch refund confirmation email: " . $ne->getMessage());
                }
            }

            $msg = "Refund {$refund['refund_number']} marked as " . ucfirst($status) . ".{$emailNotice}";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/finance/refunds'),
                ]);
            }
            return $this->redirect(url('/office/finance/refunds'));
        } catch (Throwable $e) {
            $err = 'Action failed: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/refunds'));
        }
    }

    /**
     * 8. Customer Payment History
     */
    public function payments(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $method = trim((string)$request->get('method', (string)$request->get('payment_method', '')));
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'         => $search,
            'payment_method' => $method,
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
        ]);

        $totalRecords = $this->paymentModel->countPayments($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $payments = $this->paymentModel->getPaymentsWithDetails($filters, $perPage, $offset);

        // Summary KPI stats
        $pdo = \App\Core\Database::getConnection();
        $totalCollected = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'")->fetchColumn() ?: 0);
        $cashCollected = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND LOWER(payment_method) = 'cash'")->fetchColumn() ?: 0);
        $upiCollected = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND LOWER(payment_method) = 'upi'")->fetchColumn() ?: 0);
        $bankCollected = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND LOWER(payment_method) IN ('net_banking', 'card')")->fetchColumn() ?: 0);

        $accounts = $this->accountModel->getActiveAccounts();
        $pendingInvoices = $this->invoiceModel->getPendingInvoicesWithDetails([], 50, 0);

        return $this->render('office/finance/payments', [
            'title'           => 'Customer Payment History',
            'payments'        => $payments,
            'accounts'        => $accounts,
            'pendingInvoices' => $pendingInvoices,
            'totalRecords'    => $totalRecords,
            'totalCollected'  => $totalCollected,
            'cashCollected'   => $cashCollected,
            'upiCollected'    => $upiCollected,
            'bankCollected'   => $bankCollected,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'perPage'         => $perPage,
            'search'          => $search,
            'selectedMethod'  => $method,
            'dateFrom'        => $dateFrom,
            'dateTo'          => $dateTo,
        ], 'layouts/office');
    }

    /**
     * 9. Customer Pending Payments & Receivables
     */
    public function pendingPayments(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'         => $search,
            'payment_status' => $status,
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
        ]);

        $totalRecords = $this->invoiceModel->countPendingInvoices($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $pendingInvoices = $this->invoiceModel->getPendingInvoicesWithDetails($filters, $perPage, $offset);

        // Summary KPIs
        $pdo = \App\Core\Database::getConnection();
        $totalDues = (float)($pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE payment_status != 'paid' AND total_amount > paid_amount")->fetchColumn() ?: 0);
        $overdueDues = (float)($pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE payment_status != 'paid' AND total_amount > paid_amount AND due_date IS NOT NULL AND due_date < CURDATE()")->fetchColumn() ?: 0);
        $unpaidCount = (int)($pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status = 'unpaid'")->fetchColumn() ?: 0);
        $partialCount = (int)($pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status = 'partially_paid'")->fetchColumn() ?: 0);

        $accounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/pending_payments', [
            'title'           => 'Customer Pending Payments & Receivables',
            'invoices'        => $pendingInvoices,
            'accounts'        => $accounts,
            'totalRecords'    => $totalRecords,
            'totalDues'       => $totalDues,
            'overdueDues'     => $overdueDues,
            'unpaidCount'     => $unpaidCount,
            'partialCount'    => $partialCount,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'perPage'         => $perPage,
            'search'          => $search,
            'selectedStatus'  => $status,
            'dateFrom'        => $dateFrom,
            'dateTo'          => $dateTo,
        ], 'layouts/office');
    }

    /**
     * 10. Business Expense Categories
     */
    public function expenseCategories(Request $request): Response
    {
        $categories = $this->categoryModel->all();
        $spendStats = $this->expenseModel->getCategorySpendStats();

        $statMap = [];
        foreach ($spendStats as $s) {
            $statMap[strtolower($s['category'])] = $s;
        }

        $totalCategorized = 0.0;
        $topCategory = '-';
        $maxSpend = 0.0;
        foreach ($categories as &$cat) {
            $slugKey = strtolower($cat['name']);
            $spent = (float)($statMap[$slugKey]['total_spent'] ?? 0);
            $cnt = (int)($statMap[$slugKey]['expense_count'] ?? 0);
            $cat['total_spent'] = $spent;
            $cat['expense_count'] = $cnt;
            $totalCategorized += $spent;
            if ($spent > $maxSpend) {
                $maxSpend = $spent;
                $topCategory = $cat['name'];
            }
        }
        unset($cat);

        return $this->render('office/finance/expense_categories', [
            'title'            => 'Business Expense Categories',
            'categories'       => $categories,
            'totalCategories'  => count($categories),
            'totalCategorized' => $totalCategorized,
            'topCategory'      => $topCategory,
        ], 'layouts/office');
    }

    /**
     * Store new Expense Category
     */
    public function storeExpenseCategory(Request $request): Response
    {
        try {
            $name = trim((string)$request->post('name', ''));
            $desc = trim((string)$request->post('description', ''));
            $isActive = (int)$request->post('is_active', 1);

            if (empty($name)) {
                Session::setFlash('error', 'Category name is required.');
                return $this->redirect(url('/office/finance/expense-categories'));
            }

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $existing = $this->categoryModel->findBySlug($slug);
            if ($existing) {
                Session::setFlash('error', 'A category with this name already exists.');
                return $this->redirect(url('/office/finance/expense-categories'));
            }

            $this->categoryModel->create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => $desc,
                'is_active'   => $isActive,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            Session::setFlash('success', "Category '{$name}' created successfully.");
            return $this->redirect(url('/office/finance/expense-categories'));
        } catch (Throwable $e) {
            Session::setFlash('error', 'Failed to create category: ' . $e->getMessage());
            return $this->redirect(url('/office/finance/expense-categories'));
        }
    }

    /**
     * Update Expense Category
     */
    public function updateExpenseCategory(Request $request, array $params): Response
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $name = trim((string)$request->post('name', ''));
            $desc = trim((string)$request->post('description', ''));
            $isActive = (int)$request->post('is_active', 1);

            if (empty($name)) {
                Session::setFlash('error', 'Category name is required.');
                return $this->redirect(url('/office/finance/expense-categories'));
            }

            $this->categoryModel->update($id, [
                'name'        => $name,
                'description' => $desc,
                'is_active'   => $isActive,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            Session::setFlash('success', "Category '{$name}' updated successfully.");
            return $this->redirect(url('/office/finance/expense-categories'));
        } catch (Throwable $e) {
            Session::setFlash('error', 'Failed to update category: ' . $e->getMessage());
            return $this->redirect(url('/office/finance/expense-categories'));
        }
    }

    /**
     * 11. Staff Salary & Payslips Directory
     */
    public function payslips(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $month = (int)$request->get('month', 0);
        $year = (int)$request->get('year', 0);
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'         => $search,
            'month'          => $month ?: null,
            'year'           => $year ?: null,
            'payment_status' => $status,
        ]);

        $totalRecords = $this->payrollItemModel->countPayslips($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $payslips = $this->payrollItemModel->getPayslipsWithDetails($filters, $perPage, $offset);

        // Summary KPI stats
        $pdo = \App\Core\Database::getConnection();
        $totalDisbursable = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items")->fetchColumn() ?: 0);
        $totalPaid = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'paid'")->fetchColumn() ?: 0);
        $totalPending = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status != 'paid'")->fetchColumn() ?: 0);
        $totalEmployees = (int)($pdo->query("SELECT COUNT(DISTINCT employee_id) FROM payroll_items")->fetchColumn() ?: 0);

        $accounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/payslips', [
            'title'            => 'Staff Salary & Payslips Directory',
            'payslips'         => $payslips,
            'accounts'         => $accounts,
            'totalRecords'     => $totalRecords,
            'totalDisbursable' => $totalDisbursable,
            'totalPaid'        => $totalPaid,
            'totalPending'     => $totalPending,
            'totalEmployees'   => $totalEmployees,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'perPage'          => $perPage,
            'search'           => $search,
            'selectedMonth'    => $month,
            'selectedYear'     => $year,
            'selectedStatus'   => $status,
        ], 'layouts/office');
    }

    /**
     * Mark single payslip as paid
     */
    public function markPayslipPaid(Request $request, array $params): Response
    {
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/payslips');
        try {
            $id = (int)($params['id'] ?? 0);
            $item = $this->payrollItemModel->find($id);
            if (!$item) {
                $err = 'Payslip record not found.';
                if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 404);
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            if ($item['payment_status'] === 'paid') {
                $err = 'This payslip has already been marked as paid.';
                if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 422);
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $accountId = (int)$request->post('account_id', 0) ?: null;
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $paymentDate = trim((string)$request->post('payment_date', date('Y-m-d')));
            $now = date('Y-m-d H:i:s');
            $userId = Session::getUserId();

            // Mark payslip as paid
            $this->payrollItemModel->update($id, [
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'paid_at'        => $paymentDate ? ($paymentDate . ' 12:00:00') : $now,
            ]);

            // If account selected, debit ledger
            if ($accountId) {
                $emp = $this->employeeModel->find((int)$item['employee_id']);
                $empName = $emp ? trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) : 'Employee #' . $item['employee_id'];
                $this->transactionModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'debit',
                    'amount'           => (float)$item['net_salary'],
                    'category'         => 'Salary Disbursal',
                    'reference_type'   => 'payroll_item',
                    'reference_id'     => $id,
                    'payment_method'   => $paymentMethod,
                    'transaction_ref'  => 'SAL-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT),
                    'notes'            => "Salary payment to {$empName}. Net: " . format_inr($item['net_salary']),
                    'created_by'       => $userId,
                    'transacted_at'    => $paymentDate ? ($paymentDate . ' 12:00:00') : $now,
                ]);
            }

            $msg = 'Payslip successfully marked as paid.';
            Session::setFlash('success', $msg);
            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => $redirectTo]);
            }
            return $this->redirect($redirectTo);
        } catch (Throwable $e) {
            $err = 'Action failed: ' . $e->getMessage();
            if ($request->isAjax()) return $this->json(['success' => false, 'message' => $err], 500);
            Session::setFlash('error', $err);
            return $this->redirect($redirectTo);
        }
    }

    /**
     * 12. Salary Payment History
     */
    public function salaryHistory(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $method = trim((string)$request->get('method', ''));
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'         => $search,
            'payment_method' => $method,
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
        ]);

        $totalRecords = $this->payrollItemModel->countSalaryHistory($filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $history = $this->payrollItemModel->getSalaryHistory($filters, $perPage, $offset);

        // Summary KPIs
        $pdo = \App\Core\Database::getConnection();
        $totalDisbursed = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'paid'")->fetchColumn() ?: 0);
        $thisMonthDisbursed = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'paid' AND MONTH(paid_at) = MONTH(CURRENT_DATE()) AND YEAR(paid_at) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0);
        $avgSalary = $totalRecords > 0 ? ($totalDisbursed / $totalRecords) : 0;

        return $this->render('office/finance/salary_history', [
            'title'              => 'Salary Payment History',
            'history'            => $history,
            'totalRecords'       => $totalRecords,
            'totalDisbursed'     => $totalDisbursed,
            'thisMonthDisbursed' => $thisMonthDisbursed,
            'avgSalary'          => $avgSalary,
            'currentPage'        => $page,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'search'             => $search,
            'selectedMethod'     => $method,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
        ], 'layouts/office');
    }

    /**
     * Cash & Bank helper redirects
     */
    public function accountDetailsDirect(Request $request): Response
    {
        $id = (int)$request->get('id', 0);
        if ($id > 0) {
            return $this->redirect(url('/office/finance/accounts/' . $id));
        }
        $acc = $this->accountModel->getActiveAccounts();
        if (!empty($acc)) {
            return $this->redirect(url('/office/finance/accounts/' . $acc[0]['id']));
        }
        return $this->redirect(url('/office/finance/accounts'));
    }

    public function transactionDetailsDirect(Request $request): Response
    {
        $id = (int)$request->get('id', 0);
        if ($id > 0) {
            return $this->redirect(url('/office/finance/transactions/' . $id));
        }
        $latest = $this->transactionModel->getLedgerWithDetails([], 1, 0);
        if (!empty($latest)) {
            return $this->redirect(url('/office/finance/transactions/' . $latest[0]['id']));
        }
        return $this->redirect(url('/office/finance/transactions'));
    }

    /**
     * 13. Financial Reports Overview Hub
     */
    public function reports(Request $request): Response
    {
        $pdo = \App\Core\Database::getConnection();
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');

        // Month metrics
        $monthIncome = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND MONTH(paid_at) = {$currentMonth} AND YEAR(paid_at) = {$currentYear}")->fetchColumn() ?: 0);
        $monthExpenses = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM business_expenses WHERE status != 'rejected' AND MONTH(expense_date) = {$currentMonth} AND YEAR(expense_date) = {$currentYear}")->fetchColumn() ?: 0);
        $monthSalaries = (float)($pdo->query("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE payment_status = 'paid' AND MONTH(paid_at) = {$currentMonth} AND YEAR(paid_at) = {$currentYear}")->fetchColumn() ?: 0);
        $monthTotalOutflow = $monthExpenses + $monthSalaries;
        $monthNetSurplus = $monthIncome - $monthTotalOutflow;

        $totalCustomerDues = (float)($pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE payment_status != 'paid' AND total_amount > paid_amount")->fetchColumn() ?: 0);

        return $this->render('office/finance/reports/index', [
            'title'             => 'Financial Intelligence & Reports',
            'monthIncome'       => $monthIncome,
            'monthTotalOutflow' => $monthTotalOutflow,
            'monthNetSurplus'   => $monthNetSurplus,
            'totalCustomerDues' => $totalCustomerDues,
            'currentMonth'      => date('F Y'),
        ], 'layouts/office');
    }

    /**
     * Income Report
     */
    public function incomeReport(Request $request): Response
    {
        $startDate = trim((string)$request->get('start_date', date('Y-m-01')));
        $endDate = trim((string)$request->get('end_date', date('Y-m-d')));
        $method = trim((string)$request->get('method', ''));

        $pdo = \App\Core\Database::getConnection();
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $sql = "SELECT p.*, DATE(p.paid_at) as payment_date, i.invoice_number, b.booking_code, 
                       COALESCE(c.name, u.name, 'Direct Guest') as customer_name, 
                       COALESCE(c.phone, u.phone, '') as customer_phone, 
                       a.account_name 
                FROM payments p 
                LEFT JOIN invoices i ON p.invoice_id = i.id 
                LEFT JOIN bookings b ON i.booking_id = b.id 
                LEFT JOIN customers c ON i.customer_profile_id = c.id 
                LEFT JOIN users u ON i.customer_id = u.id 
                LEFT JOIN finance_accounts a ON p.account_id = a.id 
                WHERE p.paid_at BETWEEN ? AND ? AND p.status = 'success'";
        if (!empty($method)) {
            $sql .= " AND LOWER(p.payment_method) = ?";
            $params[] = strtolower($method);
        }
        $sql .= " ORDER BY p.paid_at DESC, p.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalIncome = 0.0;
        $cashTotal = 0.0;
        $upiTotal = 0.0;
        $bankTotal = 0.0;
        foreach ($rows as $r) {
            $amt = (float)$r['amount'];
            $totalIncome += $amt;
            $m = strtolower($r['payment_method'] ?? '');
            if ($m === 'cash') $cashTotal += $amt;
            elseif ($m === 'upi') $upiTotal += $amt;
            else $bankTotal += $amt;
        }

        if ($request->get('export') === 'csv') {
            $headers = ['Payment Date', 'Payment Ref', 'Customer Name', 'Phone', 'Booking Code', 'Invoice #', 'Payment Method', 'Account', 'Amount (INR)'];
            $csvData = [];
            foreach ($rows as $r) {
                $csvData[] = [
                    $r['payment_date'],
                    $r['payment_reference'] ?: ('PAY-' . $r['id']),
                    $r['customer_name'] ?? 'Direct Walk-in',
                    $r['customer_phone'] ?? '',
                    $r['booking_code'] ?? '',
                    $r['invoice_number'] ?? '',
                    strtoupper($r['payment_method'] ?? 'CASH'),
                    $r['account_name'] ?? 'Default Treasury',
                    $r['amount'],
                ];
            }
            return $this->streamCsv('income_report_' . $startDate . '_to_' . $endDate . '.csv', $headers, $csvData);
        }

        return $this->render('office/finance/reports/income', [
            'title'       => 'Income & Revenue Report',
            'rows'        => $rows,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'method'      => $method,
            'totalIncome' => $totalIncome,
            'cashTotal'   => $cashTotal,
            'upiTotal'    => $upiTotal,
            'bankTotal'   => $bankTotal,
        ], 'layouts/office');
    }

    /**
     * Expense Report
     */
    public function expenseReport(Request $request): Response
    {
        $startDate = trim((string)$request->get('start_date', date('Y-m-01')));
        $endDate = trim((string)$request->get('end_date', date('Y-m-d')));
        $category = trim((string)$request->get('category', ''));

        $pdo = \App\Core\Database::getConnection();
        $params = [$startDate, $endDate];
        $sql = "SELECT e.*, a.account_name 
                FROM business_expenses e 
                LEFT JOIN finance_accounts a ON e.account_id = a.id 
                WHERE e.expense_date BETWEEN ? AND ? AND e.status != 'rejected'";
        if (!empty($category)) {
            $sql .= " AND LOWER(e.category) = ?";
            $params[] = strtolower($category);
        }
        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalExpenses = 0.0;
        $categoryBreakdown = [];
        foreach ($rows as $r) {
            $amt = (float)$r['amount'];
            $totalExpenses += $amt;
            $cat = $r['category'] ?: 'Other Expenses';
            $categoryBreakdown[$cat] = ($categoryBreakdown[$cat] ?? 0) + $amt;
        }

        if ($request->get('export') === 'csv') {
            $headers = ['Expense Date', 'Expense Code', 'Title', 'Category', 'Vendor Name', 'Payment Method', 'Account', 'Amount (INR)', 'Status'];
            $csvData = [];
            foreach ($rows as $r) {
                $csvData[] = [
                    $r['expense_date'],
                    $r['expense_code'] ?: ('EXP-' . $r['id']),
                    $r['title'],
                    $r['category'],
                    $r['vendor_name'] ?? '',
                    strtoupper($r['payment_method'] ?? 'CASH'),
                    $r['account_name'] ?? 'Treasury',
                    $r['amount'],
                    ucfirst($r['status'] ?? 'Approved'),
                ];
            }
            return $this->streamCsv('expense_report_' . $startDate . '_to_' . $endDate . '.csv', $headers, $csvData);
        }

        $allCategories = $this->categoryModel->getActiveCategories();

        return $this->render('office/finance/reports/expenses', [
            'title'             => 'Business Expense Report',
            'rows'              => $rows,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'category'          => $category,
            'allCategories'     => $allCategories,
            'totalExpenses'     => $totalExpenses,
            'categoryBreakdown' => $categoryBreakdown,
        ], 'layouts/office');
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request): Response
    {
        $startDate = trim((string)$request->get('start_date', date('Y-01-01')));
        $endDate = trim((string)$request->get('end_date', date('Y-m-d')));

        $pdo = \App\Core\Database::getConnection();

        // 1. Accrual Invoiced Revenue
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $invoicedRevenue = (float)$stmt->fetchColumn();

        // 2. Realized Cash Collected
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paid_at BETWEEN ? AND ? AND status = 'success'");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $realizedRevenue = (float)$stmt->fetchColumn();

        // 3. Operational Business Expenses (excluding internal transfers)
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM business_expenses WHERE expense_date BETWEEN ? AND ? AND status != 'rejected'");
        $stmt->execute([$startDate, $endDate]);
        $operatingExpenses = (float)$stmt->fetchColumn();

        // 4. Staff Salary Disbursed
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(net_salary), 0) FROM payroll_items WHERE paid_at BETWEEN ? AND ? AND payment_status = 'paid'");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $salaryExpenses = (float)$stmt->fetchColumn();

        // 5. Refunds Processed
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE (refund_date BETWEEN ? AND ? OR processed_at BETWEEN ? AND ?) AND status = 'processed'");
        $stmt->execute([$startDate, $endDate, $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $refundsPaid = (float)$stmt->fetchColumn();

        // Totals
        $totalCosts = $operatingExpenses + $salaryExpenses + $refundsPaid;
        $netAccrualProfit = $invoicedRevenue - $totalCosts;
        $netCashSurplus = $realizedRevenue - $totalCosts;

        $accrualMargin = $invoicedRevenue > 0 ? (($netAccrualProfit / $invoicedRevenue) * 100) : 0.0;
        $cashMargin = $realizedRevenue > 0 ? (($netCashSurplus / $realizedRevenue) * 100) : 0.0;

        if ($request->get('export') === 'csv') {
            $headers = ['Metric / Line Item', 'Amount (INR)', 'Notes / Explanations'];
            $csvData = [
                ['Total Invoiced Revenue (Accrual)', $invoicedRevenue, 'Total amount billed to customers on trips within this date range'],
                ['Total Realized Collections (Cash)', $realizedRevenue, 'Actual money received in cash/bank from customers'],
                ['Operational Business Expenses', $operatingExpenses, 'Office rent, fuel, vehicle maintenance, marketing, supplies'],
                ['Staff Salary Disbursed', $salaryExpenses, 'Net salaries paid to employees during this period'],
                ['Customer Refunds Issued', $refundsPaid, 'Money returned to customers for trip cancellations/adjustments'],
                ['Total Business Costs', $totalCosts, 'Sum of operating expenses, salaries, and refunds'],
                ['Net Accounting Profit (Accrual)', $netAccrualProfit, 'Invoiced Revenue minus Total Costs'],
                ['Net Cash Surplus (Cash in Hand)', $netCashSurplus, 'Realized Collections minus Total Costs'],
                ['Accrual Profit Margin (%)', round($accrualMargin, 2) . '%', 'Accounting profitability'],
                ['Cash Surplus Margin (%)', round($cashMargin, 2) . '%', 'Pure cash generation percentage'],
            ];
            return $this->streamCsv('profit_loss_' . $startDate . '_to_' . $endDate . '.csv', $headers, $csvData);
        }

        return $this->render('office/finance/reports/profit_loss', [
            'title'              => 'Profit & Loss Statement (P&L)',
            'startDate'          => $startDate,
            'endDate'            => $endDate,
            'invoicedRevenue'    => $invoicedRevenue,
            'realizedRevenue'    => $realizedRevenue,
            'operatingExpenses'  => $operatingExpenses,
            'salaryExpenses'     => $salaryExpenses,
            'refundsPaid'        => $refundsPaid,
            'totalCosts'         => $totalCosts,
            'netAccrualProfit'   => $netAccrualProfit,
            'netCashSurplus'     => $netCashSurplus,
            'accrualMargin'      => $accrualMargin,
            'cashMargin'         => $cashMargin,
        ], 'layouts/office');
    }

    /**
     * Customer Pending Dues & Aging Report
     */
    public function customerDuesReport(Request $request): Response
    {
        $pdo = \App\Core\Database::getConnection();

        $sql = "SELECT i.*, DATE(COALESCE(i.issued_at, i.created_at)) as invoice_date, b.booking_code, 
                       COALESCE(c.name, u.name, 'Direct Guest') as customer_name, 
                       COALESCE(c.phone, u.phone, '') as customer_phone, 
                       COALESCE(c.email, u.email, '') as customer_email,
                       DATEDIFF(CURDATE(), COALESCE(i.due_date, DATE(i.created_at))) as days_overdue,
                       (i.total_amount - i.paid_amount) as remaining_balance 
                FROM invoices i 
                LEFT JOIN bookings b ON i.booking_id = b.id 
                LEFT JOIN customers c ON i.customer_profile_id = c.id 
                LEFT JOIN users u ON i.customer_id = u.id 
                WHERE (i.total_amount - i.paid_amount) > 0.01
                ORDER BY days_overdue DESC, remaining_balance DESC";

        $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $totalDues = 0.0;
        $bucketCurrent = 0.0;   // 0-15 days
        $bucket16to30 = 0.0;    // 16-30 days
        $bucket31to60 = 0.0;    // 31-60 days
        $bucketOver60 = 0.0;    // 60+ days

        foreach ($rows as $r) {
            $bal = (float)$r['remaining_balance'];
            $days = (int)$r['days_overdue'];
            $totalDues += $bal;

            if ($days <= 15) {
                $bucketCurrent += $bal;
            } elseif ($days <= 30) {
                $bucket16to30 += $bal;
            } elseif ($days <= 60) {
                $bucket31to60 += $bal;
            } else {
                $bucketOver60 += $bal;
            }
        }

        if ($request->get('export') === 'csv') {
            $headers = ['Invoice #', 'Customer Name', 'Phone', 'Booking Code', 'Invoice Date', 'Due Date', 'Total Amount', 'Paid Amount', 'Pending Due (INR)', 'Days Overdue', 'Aging Bucket'];
            $csvData = [];
            foreach ($rows as $r) {
                $days = (int)$r['days_overdue'];
                $bucket = ($days <= 15) ? '0-15 Days' : (($days <= 30) ? '16-30 Days' : (($days <= 60) ? '31-60 Days' : '60+ Days Overdue'));
                $csvData[] = [
                    $r['invoice_number'],
                    $r['customer_name'] ?? 'Direct Customer',
                    $r['customer_phone'] ?? '',
                    $r['booking_code'] ?? '',
                    $r['invoice_date'],
                    $r['due_date'] ?? 'On Arrival',
                    $r['total_amount'],
                    $r['paid_amount'],
                    $r['remaining_balance'],
                    $days,
                    $bucket,
                ];
            }
            return $this->streamCsv('customer_dues_aging_report.csv', $headers, $csvData);
        }

        return $this->render('office/finance/reports/customer_dues', [
            'title'          => 'Customer Pending Dues & Aging Report',
            'rows'           => $rows,
            'totalDues'      => $totalDues,
            'bucketCurrent'  => $bucketCurrent,
            'bucket16to30'   => $bucket16to30,
            'bucket31to60'   => $bucket31to60,
            'bucketOver60'   => $bucketOver60,
        ], 'layouts/office');
    }

    /**
     * Staff Salary Report
     */
    public function salaryReport(Request $request): Response
    {
        $year = (int)$request->get('year', (int)date('Y'));
        $month = (int)$request->get('month', 0);

        $pdo = \App\Core\Database::getConnection();
        $params = [$year];
        $sql = "SELECT pi.*, pi.allowances_total as allowances, pi.deductions_total as deductions,
                       DATE(pi.paid_at) as payment_date,
                       p.month as payroll_month, p.year as payroll_year, p.payroll_code, 
                       CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code, e.department as department_name, 
                       a.account_name 
                FROM payroll_items pi 
                JOIN payrolls p ON pi.payroll_id = p.id 
                JOIN employees e ON pi.employee_id = e.id 
                LEFT JOIN finance_accounts a ON pi.payment_ref = a.id 
                WHERE p.year = ?";
        if ($month > 0) {
            $sql .= " AND p.month = ?";
            $params[] = $month;
        }
        $sql .= " ORDER BY p.year DESC, p.month DESC, e.first_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalBasic = 0.0;
        $totalAllowances = 0.0;
        $totalDeductions = 0.0;
        $totalNetSalary = 0.0;
        $totalPaid = 0.0;
        $totalPending = 0.0;

        foreach ($rows as $r) {
            $totalBasic += (float)$r['basic_salary'];
            $totalAllowances += (float)$r['allowances'];
            $totalDeductions += (float)$r['deductions'];
            $net = (float)$r['net_salary'];
            $totalNetSalary += $net;
            if ($r['payment_status'] === 'paid') {
                $totalPaid += $net;
            } else {
                $totalPending += $net;
            }
        }

        if ($request->get('export') === 'csv') {
            $headers = ['Month/Year', 'Employee Code', 'Employee Name', 'Department', 'Basic Salary', 'Allowances', 'Deductions', 'Net Salary (INR)', 'Payment Status', 'Payment Date', 'Account'];
            $csvData = [];
            foreach ($rows as $r) {
                $mName = date('M', mktime(0, 0, 0, (int)$r['payroll_month'], 10));
                $csvData[] = [
                    $mName . ' ' . $r['payroll_year'],
                    $r['employee_code'],
                    $r['employee_name'],
                    $r['department_name'] ?? 'Operations',
                    $r['basic_salary'],
                    $r['allowances'],
                    $r['deductions'],
                    $r['net_salary'],
                    ucfirst($r['payment_status']),
                    $r['payment_date'] ?? 'Unpaid',
                    $r['account_name'] ?? '',
                ];
            }
            return $this->streamCsv('salary_report_' . $year . '.csv', $headers, $csvData);
        }

        return $this->render('office/finance/reports/salary', [
            'title'           => 'Staff Salary & Payroll Report',
            'rows'            => $rows,
            'year'            => $year,
            'month'           => $month,
            'totalBasic'      => $totalBasic,
            'totalAllowances' => $totalAllowances,
            'totalDeductions' => $totalDeductions,
            'totalNetSalary'  => $totalNetSalary,
            'totalPaid'       => $totalPaid,
            'totalPending'    => $totalPending,
        ], 'layouts/office');
    }

    /**
     * Expense Category Spend Report
     */
    public function expenseCategoryReport(Request $request): Response
    {
        $startDate = trim((string)$request->get('start_date', date('Y-01-01')));
        $endDate = trim((string)$request->get('end_date', date('Y-m-d')));

        $pdo = \App\Core\Database::getConnection();

        $stmt = $pdo->prepare("SELECT category, COUNT(*) as voucher_count, COALESCE(SUM(amount), 0) as total_amount 
                               FROM business_expenses 
                               WHERE expense_date BETWEEN ? AND ? AND status != 'rejected' 
                               GROUP BY category 
                               ORDER BY total_amount DESC");
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grandTotal = 0.0;
        foreach ($rows as $r) {
            $grandTotal += (float)$r['total_amount'];
        }

        foreach ($rows as &$r) {
            $amt = (float)$r['total_amount'];
            $r['percentage'] = $grandTotal > 0 ? round(($amt / $grandTotal) * 100, 1) : 0;
        }
        unset($r);

        if ($request->get('export') === 'csv') {
            $headers = ['Category', 'Voucher Count', 'Total Spent (INR)', 'Percentage of Total (%)'];
            $csvData = [];
            foreach ($rows as $r) {
                $csvData[] = [
                    $r['category'],
                    $r['voucher_count'],
                    $r['total_amount'],
                    $r['percentage'] . '%',
                ];
            }
            return $this->streamCsv('expense_category_report.csv', $headers, $csvData);
        }

        return $this->render('office/finance/reports/expense_categories', [
            'title'      => 'Expense Category Spend Breakdown',
            'rows'       => $rows,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'grandTotal' => $grandTotal,
        ], 'layouts/office');
    }

    /**
     * Stream CSV download
     */
    private function streamCsv(string $filename, array $headers, array $rows): Response
    {
        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM for Microsoft Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
