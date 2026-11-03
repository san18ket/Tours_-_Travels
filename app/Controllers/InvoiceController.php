<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\FinanceAccount;
use App\Services\BillingService;
use Throwable;

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private BillingService $billingService;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->billingService = new BillingService();
    }

    public function index(Request $request): Response
    {
        $allInvoices = $this->invoiceModel->getAllWithDetails();

        // Customer authorization: only see own invoices
        if (Session::getUserRole() === 'customer') {
            $allInvoices = array_values(array_filter($allInvoices, fn($inv) => (int)$inv['customer_id'] === Session::getUserId()));
        }

        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $dateFrom = trim((string)$request->get('date_from', ''));
        $dateTo = trim((string)$request->get('date_to', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [5, 10, 15, 20, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $filtered = array_filter($allInvoices, function($inv) use ($search, $status, $dateFrom, $dateTo) {
            if ($status !== '' && strtolower($inv['payment_status'] ?? '') !== strtolower($status)) {
                return false;
            }
            if ($dateFrom !== '') {
                $invDate = substr((string)($inv['invoice_date'] ?? ''), 0, 10);
                if ($invDate !== '' && $invDate < $dateFrom) {
                    return false;
                }
            }
            if ($dateTo !== '') {
                $invDate = substr((string)($inv['invoice_date'] ?? ''), 0, 10);
                if ($invDate !== '' && $invDate > $dateTo) {
                    return false;
                }
            }
            if ($search !== '') {
                $needle = strtolower($search);
                $haystack = strtolower(
                    ($inv['invoice_number'] ?? '') . ' ' .
                    ($inv['booking_code'] ?? '') . ' ' .
                    ($inv['customer_name'] ?? '') . ' ' .
                    ($inv['customer_email'] ?? '')
                );
                if (!str_contains($haystack, $needle)) {
                    return false;
                }
            }
            return true;
        });

        $filtered = array_values($filtered);
        $totalRecords = count($filtered);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $paginatedInvoices = array_slice($filtered, $offset, $perPage);

        // Overall financial sums across entire ledger
        $totalInvoiced = 0.0;
        $totalPaid = 0.0;
        $paidCount = 0;
        $unpaidCount = 0;
        $partialCount = 0;
        foreach ($allInvoices as $inv) {
            $totalInvoiced += (float)($inv['total_amount'] ?? 0);
            $totalPaid += (float)($inv['paid_amount'] ?? 0);
            $st = strtolower($inv['payment_status'] ?? 'unpaid');
            if ($st === 'paid') $paidCount++;
            elseif ($st === 'partial') $partialCount++;
            else $unpaidCount++;
        }
        $totalOutstanding = max(0.0, $totalInvoiced - $totalPaid);

        $accounts = (new FinanceAccount())->getActiveAccounts();

        return $this->render('office/invoices/index', [
            'invoices'         => $paginatedInvoices,
            'accounts'         => $accounts,
            'totalRecords'     => $totalRecords,
            'totalAll'         => count($allInvoices),
            'totalInvoiced'    => $totalInvoiced,
            'totalPaid'        => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'paidCount'        => $paidCount,
            'unpaidCount'      => $unpaidCount,
            'partialCount'     => $partialCount,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'perPage'          => $perPage,
            'search'           => $search,
            'selectedStatus'   => $status,
            'dateFrom'         => $dateFrom,
            'dateTo'           => $dateTo,
            'title'            => 'Invoices & Billing Ledger',
        ], 'layouts/office');
    }

    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $invoice = $this->invoiceModel->findWithFullDetails($id);

        if (!$invoice) {
            Session::setFlash('danger', 'Invoice not found.');
            return $this->redirect(url('/office/invoices'));
        }

        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$invoice['customer_id']);
        }

        $payments = $this->paymentModel->getPaymentsByInvoice($id);
        $accounts = (new FinanceAccount())->getActiveAccounts();

        return $this->render('office/invoices/show', [
            'invoice'  => $invoice,
            'payments' => $payments,
            'accounts' => $accounts,
            'title'    => "Tax Invoice {$invoice['invoice_number']}",
        ], 'layouts/office');
    }

    public function generate(Request $request, array $params): Response
    {
        $bookingId = (int)($params['booking_id'] ?? 0);
        try {
            $invoice = $this->billingService->generateInvoice($bookingId);
            Session::setFlash('success', "Invoice {$invoice['invoice_number']} generated successfully.");
            return $this->redirect(url("/office/invoices/{$invoice['id']}"));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url("/office/bookings/{$bookingId}"));
        }
    }

    public function recordPayment(Request $request, array $params): Response
    {
        $invoiceId = (int)($params['id'] ?? $request->input('invoice_id', 0));
        $redirectTo = $request->input('redirect_to') ?: url("/office/invoices/{$invoiceId}");
        try {
            $data = $this->validate($request->all(), [
                'amount'         => 'required|numeric',
                'payment_method' => 'required|in:upi,net_banking,card,cash,bank_transfer',
            ]);

            $idempotencyKey = $request->input('idempotency_key') ?: bin2hex(random_bytes(16));
            $transactionId = $request->input('transaction_id') ?: ('TXN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));
            $accountId = (int)$request->input('account_id', 0) ?: null;

            $receivedDate = $request->input('received_date') ?: $request->input('paid_at');
            $paidAt = null;
            if ($receivedDate) {
                $paidAt = strlen($receivedDate) === 10 ? $receivedDate . ' ' . date('H:i:s') : $receivedDate;
            }

            $payment = $this->billingService->recordPayment(
                $invoiceId,
                (float)$data['amount'],
                $data['payment_method'],
                $transactionId,
                $idempotencyKey,
                $request->input('notes'),
                $accountId,
                $paidAt
            );

            $updatedInvoice = $this->invoiceModel->find($invoiceId);
            $remaining = max(0.0, (float)($updatedInvoice['total_amount'] ?? 0) - (float)($updatedInvoice['paid_amount'] ?? 0));
            $statusText = $remaining <= 0.001 ? "Invoice is now fully paid." : "Remaining amount: " . format_currency($remaining) . ".";
            $msg = "Payment of " . format_currency((float)$data['amount']) . " recorded successfully. {$statusText}";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => $redirectTo,
                    'payment'  => $payment,
                ]);
            }
        } catch (Throwable $e) {
            $err = $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 422);
            }
            Session::setFlash('danger', $err);
        }

        return $this->redirect($redirectTo);
    }

    public function printInvoice(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $invoice = $this->invoiceModel->findWithFullDetails($id);

        if (!$invoice) {
            return Response::make("Invoice not found.", 404);
        }

        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$invoice['customer_id']);
        }

        $payments = $this->paymentModel->getPaymentsByInvoice($id);

        return $this->render('office/invoices/print', [
            'invoice'  => $invoice,
            'payments' => $payments,
            'title'    => "Print Invoice - {$invoice['invoice_number']}",
        ], null); // Render standalone printable document
    }
}
