<?php
/**
 * Tax Invoice Notification Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Customer';
$invoiceNum = $invoice['invoice_number'] ?? ('INV-' . ($invoice['id'] ?? ''));
$totalFormatted = format_currency((float)($invoice['total_amount'] ?? 0));
$paidFormatted = format_currency((float)($invoice['paid_amount'] ?? 0));
$balanceDue = max(0.0, (float)($invoice['total_amount'] ?? 0) - (float)($invoice['paid_amount'] ?? 0));
$isPaid = ($balanceDue <= 0.001);
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: <?= $isPaid ? '#F0FDF4' : '#FEF3C7' ?>; border: 1px solid <?= $isPaid ? '#BBF7D0' : '#FDE68A' ?>; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: <?= $isPaid ? '#168653' : '#92400E' ?>;">
                        <?= $isPaid ? '✓ Invoice Paid in Full' : '📄 Invoice Issued' ?>
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Invoice: <?= e($invoiceNum) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, please find your itemized tax invoice from <strong><?= e($companyName) ?></strong> detailed below.
</p>

<!-- INVOICE DETAILS CARD -->
<?php
$cardTitle = 'TAX INVOICE DETAILS: ' . $invoiceNum;
$cardRows = [
    ['label' => 'Invoice Number', 'value' => $invoiceNum],
    ['label' => 'Invoice Date', 'value' => format_date($invoice['invoice_date'] ?? date('Y-m-d'))],
    ['label' => 'Associated Booking', 'value' => $booking['booking_code'] ?? 'Direct Service'],
    ['label' => 'Total Amount', 'html' => '<span style="font-size: 16px; font-weight: 800; color: ' . e($secondary) . ';">' . $totalFormatted . '</span>'],
    ['label' => 'Amount Paid', 'html' => '<span style="font-weight: 700; color: #168653;">' . $paidFormatted . '</span>'],
    ['label' => 'Balance Due', 'html' => $isPaid ? '<span style="color: #168653; font-weight: 700;">Nil (Fully Settled)</span>' : '<span style="color: #DC2626; font-weight: 800;">' . format_currency($balanceDue) . '</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'View & Print Tax Invoice';
$btnUrl = url('/office/invoices/' . ($invoice['id'] ?? 0));
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>

