<?php
/**
 * Trip Completed Summary Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Guest';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ Trip Completed
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Trip: <?= e($booking['booking_code'] ?? '') ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, thank you for choosing <strong><?= e($companyName) ?></strong>. Your trip under reservation #<strong><?= e($booking['booking_code'] ?? '') ?></strong> has reached its destination safely.
</p>

<!-- TRIP SUMMARY CARD -->
<?php
$cardTitle = 'TRIP COMPLETION SUMMARY';
$cardRows = [
    ['label' => 'Trip Code', 'value' => $booking['booking_code'] ?? ''],
    ['label' => 'Drop Location', 'value' => $booking['drop_address'] ?? ''],
    ['label' => 'Chauffeur', 'value' => $driver['name'] ?? 'Assigned Chauffeur'],
    ['label' => 'Vehicle', 'value' => ($vehicle['make_model'] ?? 'Vehicle') . ' (' . ($vehicle['vehicle_number'] ?? '') . ')'],
    ['label' => 'Completed At', 'value' => format_datetime(date('Y-m-d H:i:s'))],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Book Your Next Journey';
$btnUrl = url('/');
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
