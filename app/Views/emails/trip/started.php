<?php
/**
 * Trip Started / Live Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$customerName = $customer['name'] ?? 'Valued Guest';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #E0F2FE; border: 1px solid #BAE6FD; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #0369A1;">
                        🚗 Trip Underway
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
    Dear <strong><?= e($customerName) ?></strong>, your chauffeur has commenced the journey. Sit back and enjoy a smooth, pleasant travel experience.
</p>

<!-- TRIP DETAILS CARD -->
<?php
$cardTitle = 'TRIP STATUS: ' . ($booking['booking_code'] ?? '');
$cardRows = [
    ['label' => 'Trip Code', 'value' => $booking['booking_code'] ?? ''],
    ['label' => 'Destination', 'value' => $booking['drop_address'] ?? ''],
    ['label' => 'Chauffeur', 'value' => ($driver['name'] ?? 'Driver') . (!empty($driver['phone']) ? ' (' . $driver['phone'] . ')' : '')],
    ['label' => 'Vehicle', 'value' => ($vehicle['make_model'] ?? 'Vehicle') . ' (' . ($vehicle['vehicle_number'] ?? '') . ')'],
    ['label' => 'Started At', 'value' => format_datetime(date('Y-m-d H:i:s'))],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Open Trip Itinerary';
$btnUrl = url('/office/bookings/' . ($booking['id'] ?? 0));
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
