<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\BusinessExpense;
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
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');

        // Liquidity across accounts
        $accounts = $this->accountModel->getActiveAccounts();
        $totalLiquid = 0.0;
        foreach ($accounts as $a) {
            $totalLiquid += (float)$a['current_balance'];
        }

        // Monthly Inflow vs Outflow
        $monthlyLedger = $this->transactionModel->getMonthlyIncomeExpense($currentYear);
        $chartMonths = [];
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

        $currentMonthIncome = $incomeData[$currentMonth] ?? 0.0;
        $currentMonthExpense = $expenseData[$currentMonth] ?? 0.0;
        $netCashFlow = $currentMonthIncome - $currentMonthExpense;

        // Invoices Receivables summary
        $invoices = $this->invoiceModel->all();
        $totalInvoiced = 0.0;
        $totalCollected = 0.0;
        foreach ($invoices as $inv) {
            $totalInvoiced += (float)($inv['total_amount'] ?? 0);
            $totalCollected += (float)($inv['paid_amount'] ?? 0);
        }
        $receivables = max(0.0, $totalInvoiced - $totalCollected);

        // Expense by Category breakdown
        $expenseBreakdown = $this->expenseModel->getTotalExpensesByCategory($currentYear, $currentMonth);

        // Latest transactions
        $recentTransactions = $this->transactionModel->getLedgerWithDetails([], 10, 0);

        return $this->render('office/finance/dashboard', [
            'title'               => 'Finance & Treasury Command',
            'accounts'            => $accounts,
            'totalLiquid'         => $totalLiquid,
            'currentMonthIncome'  => $currentMonthIncome,
            'currentMonthExpense' => $currentMonthExpense,
            'netCashFlow'         => $netCashFlow,
            'receivables'         => $receivables,
            'incomeData'          => array_values($incomeData),
            'expenseData'         => array_values($expenseData),
            'monthLabels'         => array_values($monthNames),
            'expenseBreakdown'    => $expenseBreakdown,
            'recentTransactions'  => $recentTransactions,
            'currentYear'         => $currentYear,
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
        $categories = config('finance.expense_categories', []);

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
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/transactions');
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
                return $this->json(['success' => true, 'message' => $msg]);
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
                return $this->json(['success' => true, 'message' => $msg]);
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

            if ((float)$fromAcc['current_balance'] < $amount) {
                $err = "Insufficient balance in {$fromAcc['account_name']}. Available: " . format_currency((float)$fromAcc['current_balance']);
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('error', $err);
                return $this->redirect($redirectTo);
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');

            // 1. Debit from source account
            $this->transactionModel->recordTransaction([
                'account_id'       => $fromAccountId,
                'transaction_type' => 'debit',
                'amount'           => $amount,
                'category'         => 'Internal Transfer',
                'reference_type'   => 'finance_transfer',
                'reference_id'     => $toAccountId,
                'payment_method'   => 'bank_transfer',
                'transaction_ref'  => $ref,
                'notes'            => "Transfer to {$toAcc['account_name']}. " . $notes,
                'created_by'       => $userId,
                'transacted_at'    => $now,
            ]);

            // 2. Credit to destination account
            $this->transactionModel->recordTransaction([
                'account_id'       => $toAccountId,
                'transaction_type' => 'credit',
                'amount'           => $amount,
                'category'         => 'Internal Transfer',
                'reference_type'   => 'finance_transfer',
                'reference_id'     => $fromAccountId,
                'payment_method'   => 'bank_transfer',
                'transaction_ref'  => $ref,
                'notes'            => "Transfer received from {$fromAcc['account_name']}. " . $notes,
                'created_by'       => $userId,
                'transacted_at'    => $now,
            ]);

            $msg = "Successfully transferred " . format_currency($amount) . " from {$fromAcc['account_name']} to {$toAcc['account_name']}.";
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
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/accounts/' . $id);

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
        $categories = config('finance.expense_categories', []);

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
            $target = url('/office/finance/payroll/' . $payrollId);

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
                return $this->redirect(url('/office/finance/payroll/' . $id));
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
                return $this->redirect(url('/office/finance/payroll/' . $id));
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
                    'redirect' => url('/office/finance/payroll/' . $id),
                ]);
            }
            return $this->redirect(url('/office/finance/payroll/' . $id));
        } catch (Throwable $e) {
            $err = 'Failed to disburse payroll: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('error', $err);
            return $this->redirect(url('/office/finance/payroll/' . $id));
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
        $redirectTo = $request->post('redirect_to') ?: url('/office/finance/advances');
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
                return $this->json(['success' => true, 'message' => $msg]);
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
                return $this->json(['success' => true, 'message' => $msg]);
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
     * 7. Refunds & Returns
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

        return $this->render('office/finance/refunds', [
            'title'          => 'Customer Refunds & Adjustments',
            'refunds'        => $refunds,
            'accounts'       => $accounts,
            'totalRecords'   => $totalRecords,
            'totalAll'       => count($allRef),
            'totalProcessed' => $totalProcessed,
            'totalPending'   => $totalPending,
            'pendingCount'   => $pendingCount,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'search'         => $search,
            'selectedStatus' => $status,
        ], 'layouts/office');
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

            if ($status === 'processed' && $refund['status'] !== 'processed' && $accountId) {
                // Debit refund amount from account
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

            $this->refundModel->update($id, [
                'status'       => $status,
                'processed_by' => ($status === 'processed') ? $userId : null,
                'processed_at' => ($status === 'processed') ? $now : null,
                'updated_at'   => $now,
            ]);

            $msg = "Refund {$refund['refund_number']} marked as " . ucfirst($status) . ".";
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
}
