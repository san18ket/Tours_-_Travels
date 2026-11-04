<?php
/**
 * Refund Processed Confirmation Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Customer';
$refundNum = $refund['refund_number'] ?? ('RF-' . ($refund['id'] ?? ''));
$amountFormatted = format_currency((float)($refund['amount'] ?? 0));
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ Refund Processed Successfully
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Ref: <?= e($refundNum) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, this email confirms that your refund request for <strong style="color: #168653;"><?= $amountFormatted ?></strong> has been successfully processed by <?= e($companyName) ?>.
</p>

<!-- REFUND DETAILS CARD -->
<?php
$cardTitle = 'REFUND CREDIT DETAILS: ' . $refundNum;
$cardRows = [
    ['label' => 'Refund Reference', 'value' => $refundNum],
    ['label' => 'Refund Amount', 'html' => '<span style="font-size: 18px; font-weight: 800; color: #168653;">' . $amountFormatted . '</span>'],
    ['label' => 'Refund Method', 'value' => ucwords(str_replace(['_', '-'], ' ', (string)($refund['refund_method'] ?? 'Bank Transfer')))],
    ['label' => 'Invoice Ref', 'value' => $invoice['invoice_number'] ?? 'Direct Booking Adjustment'],
    ['label' => 'Reason / Purpose', 'value' => $refund['reason'] ?? 'Schedule adjustment or cancellation'],
    ['label' => 'Processed Date', 'value' => format_datetime($refund['processed_at'] ?? ($refund['created_at'] ?? date('Y-m-d H:i:s')))],
];
require __DIR__ . '/../components/info_card.php';
?>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 8px; margin: 16px 0;">
    <tr>
        <td style="padding: 14px 18px; font-size: 12.5px; color: #64748B; line-height: 1.5;">
            <strong>Settlement Timeline:</strong> Depending on your payment institution or bank processing schedules, electronic funds typically reflect in your account within 3 to 7 business days.
        </td>
    </tr>
</table>

