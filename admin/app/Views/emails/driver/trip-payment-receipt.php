<?php
/**
 * Driver Trip Payment Receipt Template
 * Driver-specific trip payment voucher email. Contains NO customer billing data.
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$driverName = $driver['name'] ?? 'Driver';
$amountPaidFormatted = format_currency((float)($payment['amount'] ?? 0));
$earnedFormatted = format_currency((float)($dispatch['driver_trip_amount'] ?? 0));
$totalPaidFormatted = format_currency((float)($payment['new_total_paid'] ?? ($dispatch['driver_paid_amount'] ?? $payment['amount'])));
$remainingFormatted = format_currency((float)($payment['remaining'] ?? max(0.0, (float)($dispatch['driver_trip_amount'] ?? 0) - (float)($dispatch['driver_paid_amount'] ?? 0))));
$voucherRef = $payment['payment_reference'] ?? ('DTR-' . ($payment['id'] ?? ''));
$isFullyPaid = (($payment['payment_status'] ?? '') === 'paid' || (float)($payment['remaining'] ?? 1) <= 0);
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: <?= $isFullyPaid ? '#F0FDF4' : '#EFF6FF' ?>; border: 1px solid <?= $isFullyPaid ? '#BBF7D0' : '#BFDBFE' ?>; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: <?= $isFullyPaid ? '#168653' : '#1D4ED8' ?>;">
                        <?= $isFullyPaid ? '✓ Trip Payment Settled' : '⏱ Partial Trip Disbursement' ?>
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Voucher: <?= e($voucherRef) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($driverName) ?></strong>, a trip payment disbursement of <strong style="color: #168653;"><?= $amountPaidFormatted ?></strong> has been processed for your completed trip with <?= e($companyName) ?>.
</p>

<!-- PAYMENT VOUCHER DETAILS CARD -->
<?php
$cardTitle = 'DRIVER PAYMENT VOUCHER: ' . $voucherRef;
$cardRows = [
    ['label' => 'Paid Amount', 'html' => '<span style="font-size: 18px; font-weight: 800; color: #168653;">' . $amountPaidFormatted . '</span>'],
    ['label' => 'Trip Dispatch Code', 'value' => $dispatch['dispatch_code'] ?? 'N/A'],
    ['label' => 'Total Trip Earnings', 'value' => $earnedFormatted],
    ['label' => 'Total Paid to Date', 'value' => $totalPaidFormatted],
    ['label' => 'Remaining Balance Due', 'html' => $isFullyPaid ? '<span style="color: #168653; font-weight: 700;">₹0.00 (Fully Settled)</span>' : '<span style="color: #D97706; font-weight: 700;">' . $remainingFormatted . '</span>'],
    ['label' => 'Payment Status', 'html' => $isFullyPaid ? '<span style="color: #168653; font-weight: 700;">Fully Paid</span>' : '<span style="color: #1D4ED8; font-weight: 700;">Partially Paid</span>'],
    ['label' => 'Disbursement Date', 'value' => format_date($payment['payment_date'] ?? date('Y-m-d'))],
    ['label' => 'Payment Method', 'value' => ucwords(str_replace('_', ' ', $payment['payment_method'] ?? 'Bank Transfer'))],
    ['label' => 'Transaction Reference', 'html' => '<span style="font-family: monospace; font-size: 13px; font-weight: 700; color: #475569;">' . e($payment['transaction_ref'] ?? 'Direct Transfer') . '</span>'],
];
if (!empty($payment['notes'])) {
    $cardRows[] = ['label' => 'Office Notes', 'value' => $payment['notes']];
}
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Open Driver Portal';
$btnUrl = url('/driver/earnings');
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>

