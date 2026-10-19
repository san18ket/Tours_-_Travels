<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Invoice;
use App\Models\Payment;
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
        $invoices = $this->invoiceModel->getAllWithDetails();

        // Customer authorization: only see own invoices
        if (Session::getUserRole() === 'customer') {
            $invoices = array_filter($invoices, fn($inv) => (int)$inv['customer_id'] === Session::getUserId());
        }

        return $this->render('office/invoices/index', [
            'invoices' => $invoices,
            'title'    => 'Invoices & Billing Ledger',
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

        return $this->render('office/invoices/show', [
            'invoice'  => $invoice,
            'payments' => $payments,
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
        $invoiceId = (int)($params['id'] ?? 0);
        try {
            $data = $this->validate($request->all(), [
                'amount'         => 'required|numeric',
                'payment_method' => 'required|in:upi,net_banking,card,cash',
            ]);

            $idempotencyKey = $request->input('idempotency_key') ?: bin2hex(random_bytes(16));
            $transactionId = $request->input('transaction_id') ?: ('TXN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));

            $payment = $this->billingService->recordPayment(
                $invoiceId,
                (float)$data['amount'],
                $data['payment_method'],
                $transactionId,
                $idempotencyKey,
                $request->input('notes')
            );

            Session::setFlash('success', "Payment {$payment['payment_reference']} of " . format_currency((float)$data['amount']) . " recorded successfully.");
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url("/office/invoices/{$invoiceId}"));
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
