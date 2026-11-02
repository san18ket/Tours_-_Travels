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
        $accountId = (int)$request->get('account_id', 0);
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 15;

        $filters = array_filter([
            'search'           => $search,
            'transaction_type' => $type,
            'category'         => $category,
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
                Session::flash('error', 'Please specify a valid account and an amount greater than zero.');
                return $this->redirect('/office/finance/transactions');
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

            Session::flash('success', 'Transaction successfully committed to the immutable ledger.');
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to record transaction: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/transactions');
    }

    /**
     * Internal Account-to-Account Fund Transfer
     */
    public function transfer(Request $request): Response
    {
        try {
            $fromAccountId = (int)$request->post('from_account_id');
            $toAccountId = (int)$request->post('to_account_id');
            $amount = (float)$request->post('amount', 0);
            $ref = trim((string)$request->post('transfer_ref', 'TRF-' . date('Ymd-His')));
            $notes = trim((string)$request->post('notes', 'Internal account transfer'));

            if ($fromAccountId <= 0 || $toAccountId <= 0 || $fromAccountId === $toAccountId) {
                Session::flash('error', 'Please select two different accounts for the fund transfer.');
                return $this->redirect('/office/finance/accounts');
            }

            if ($amount <= 0) {
                Session::flash('error', 'Transfer amount must be greater than zero.');
                return $this->redirect('/office/finance/accounts');
            }

            $fromAcc = $this->accountModel->find($fromAccountId);
            $toAcc = $this->accountModel->find($toAccountId);

            if (!$fromAcc || !$toAcc) {
                Session::flash('error', 'One or both selected accounts do not exist.');
                return $this->redirect('/office/finance/accounts');
            }

            if ((float)$fromAcc['current_balance'] < $amount) {
                Session::flash('error', "Insufficient balance in {$fromAcc['account_name']}. Available: " . format_currency((float)$fromAcc['current_balance']));
                return $this->redirect('/office/finance/accounts');
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

            Session::flash('success', "Successfully transferred " . format_currency($amount) . " from {$fromAcc['account_name']} to {$toAcc['account_name']}.");
        } catch (Throwable $e) {
            Session::flash('error', 'Transfer failed: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/accounts');
    }

    /**
     * 3. Accounts & Banking
     */
    public function accounts(Request $request): Response
    {
        $accounts = $this->accountModel->all('account_type ASC, account_name ASC');
        $totalBalance = 0.0;
        $bankBalance = 0.0;
        $cashBalance = 0.0;

        foreach ($accounts as $acc) {
            $bal = (float)$acc['current_balance'];
            $totalBalance += $bal;
            if ($acc['account_type'] === 'bank') $bankBalance += $bal;
            elseif ($acc['account_type'] === 'cash') $cashBalance += $bal;
        }

        return $this->render('office/finance/accounts', [
            'title'        => 'Bank Accounts & Cash Treasury',
            'accounts'     => $accounts,
            'totalBalance' => $totalBalance,
            'bankBalance'  => $bankBalance,
            'cashBalance'  => $cashBalance,
            'accountCount' => count($accounts),
            'typesConfig'  => config('finance.account_types', []),
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
                Session::flash('error', 'Account Name is required.');
                return $this->redirect('/office/finance/accounts');
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

            Session::flash('success', "Finance Account '{$name}' created successfully with opening balance of " . format_currency($openingBal));
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to create account: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/accounts');
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
                Session::flash('error', 'Expense title and an amount greater than zero are required.');
                return $this->redirect('/office/finance/expenses');
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

            Session::flash('success', "Expense {$expenseCode} recorded successfully.");
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to save expense: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/expenses');
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
                Session::flash('error', 'Expense record not found.');
                return $this->redirect('/office/finance/expenses');
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
            Session::flash('success', "Expense {$expense['expense_code']} updated to " . ucfirst($newStatus) . ".");
        } catch (Throwable $e) {
            Session::flash('error', 'Status update failed: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/expenses');
    }

    /**
     * 5. Payroll & Salary Disbursals
     */
    public function payroll(Request $request): Response
    {
        $payrolls = $this->payrollModel->getPayrollsWithStats(30, 0);
        $employees = $this->employeeModel->where("status = 'active'", [], 'name ASC');
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
                Session::flash('error', "Payroll batch for " . date('F Y', mktime(0, 0, 0, $month, 10, $year)) . " already exists.");
                return $this->redirect('/office/finance/payroll');
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

            Session::flash('success', "Payroll batch {$payrollCode} drafted with " . count($employees) . " employee payslips.");
            return $this->redirect('/office/finance/payroll/' . $payrollId);
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to generate payroll: ' . $e->getMessage());
            return $this->redirect('/office/finance/payroll');
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
            Session::flash('error', 'Payroll batch not found.');
            return $this->redirect('/office/finance/payroll');
        }

        $accounts = $this->accountModel->getActiveAccounts();

        return $this->render('office/finance/payroll_show', [
            'title'    => "Payroll Batch: {$payroll['payroll_code']}",
            'payroll'  => $payroll,
            'accounts' => $accounts,
        ], 'layouts/office');
    }

    /**
     * Mark Entire Payroll Batch as Paid
     */
    public function markPayrollPaid(Request $request, array $params): Response
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $accountId = (int)$request->post('account_id', 0);
            $paymentMethod = trim((string)$request->post('payment_method', 'bank_transfer'));
            $ref = trim((string)$request->post('payment_ref', 'PAYROLL-' . date('Ymd')));

            $payroll = $this->payrollModel->findWithItems($id);
            if (!$payroll) {
                Session::flash('error', 'Payroll batch not found.');
                return $this->redirect('/office/finance/payroll');
            }

            if ($payroll['status'] === 'paid') {
                Session::flash('error', 'Payroll batch is already marked as paid.');
                return $this->redirect('/office/finance/payroll/' . $id);
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');
            $totalNet = (float)$payroll['total_net'];

            // 1. Debit total payroll amount from selected account
            if ($accountId > 0) {
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
            $pdo = $this->payrollModel->getPdo();
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

            Session::flash('success', "Payroll {$payroll['payroll_code']} disbursed successfully. Total: " . format_currency($totalNet));
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to disburse payroll: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/payroll/' . $id);
    }

    /**
     * View Individual Employee Payslip
     */
    public function payslip(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $item = $this->payrollItemModel->findWithDetails($id);

        if (!$item) {
            Session::flash('error', 'Payslip record not found.');
            return $this->redirect('/office/finance/payroll');
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

        $employees = $this->employeeModel->where("status = 'active'", [], 'name ASC');
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
                Session::flash('error', 'Employee, valid amount, and purpose reason are required.');
                return $this->redirect('/office/finance/advances');
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

            Session::flash('success', "Advance request {$code} registered successfully.");
        } catch (Throwable $e) {
            Session::flash('error', 'Failed to register advance: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/advances');
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
                Session::flash('error', 'Advance record not found.');
                return $this->redirect('/office/finance/advances');
            }

            $userId = Session::getUserId();
            $now = date('Y-m-d H:i:s');

            if ($status === 'approved' && $accountId) {
                // Debit advance payout from selected account
                $emp = $this->employeeModel->find((int)$advance['employee_id']);
                $empName = $emp ? $emp['name'] : "Employee #{$advance['employee_id']}";

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

            Session::flash('success', "Advance {$advance['advance_code']} marked as " . ucfirst($status) . ".");
        } catch (Throwable $e) {
            Session::flash('error', 'Action failed: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/advances');
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
                Session::flash('error', 'Refund record not found.');
                return $this->redirect('/office/finance/refunds');
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

            Session::flash('success', "Refund {$refund['refund_number']} marked as " . ucfirst($status) . ".");
        } catch (Throwable $e) {
            Session::flash('error', 'Action failed: ' . $e->getMessage());
        }

        return $this->redirect('/office/finance/refunds');
    }
}
