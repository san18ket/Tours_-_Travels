<?php
/**
 * Payment Receipt / Successful Confirmation Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Customer';
$amountFormatted = format_currency((float)($payment['amount'] ?? 0));
$payRef = $payment['payment_code'] ?? ($payment['payment_reference'] ?? ('PAY-' . ($payment['id'] ?? '')));
$invoiceTotal = (float)($invoice['total_amount'] ?? $booking['total_amount'] ?? 0);
$paidSoFar = (float)($invoice['paid_amount'] ?? $payment['amount'] ?? 0);
$balance = max(0.0, $invoiceTotal - $paidSoFar);
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ Payment Received
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Ref: <?= e($payRef) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, we have received your payment of <strong style="color: #168653;"><?= $amountFormatted ?></strong> for your travel services with <?= e($companyName) ?>.
</p>

<!-- RECEIPT DETAILS CARD -->
<?php
$cardTitle = 'PAYMENT RECEIPT: ' . $payRef;
$cardRows = [
    ['label' => 'Amount Credited', 'html' => '<span style="font-size: 18px; font-weight: 800; color: #168653;">' . $amountFormatted . '</span>'],
    ['label' => 'Invoice Number', 'value' => $invoice['invoice_number'] ?? 'N/A'],
    ['label' => 'Associated Booking', 'value' => $booking['booking_code'] ?? 'Direct Billing'],
    ['label' => 'Payment Mode', 'value' => strtoupper($payment['payment_method'] ?? 'Online')],
    ['label' => 'Transaction Ref', 'html' => '<span style="font-family: monospace; font-size: 13px; font-weight: 700; color: #475569;">' . e($payment['transaction_id'] ?? 'Verified') . '</span>'],
    ['label' => 'Payment Date', 'value' => format_datetime($payment['paid_at'] ?? ($payment['created_at'] ?? date('Y-m-d H:i:s')))],
    ['label' => 'Remaining Balance', 'html' => $balance <= 0 ? '<span style="color: #168653; font-weight: 700;">Nil / Fully Settled</span>' : '<span style="color: #D97706; font-weight: 700;">' . format_currency($balance) . '</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php if (!empty($invoice['id'])): ?>
    <?php
    $btnText = 'View Official Tax Invoice';
    $btnUrl = url('/office/invoices/' . $invoice['id']);
    $btnColor = $primary;
    require __DIR__ . '/../components/button.php';
    ?>
<?php endif; ?>
