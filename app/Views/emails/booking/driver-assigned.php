<?php
/**
 * Customer Notification: Chauffeur & Vehicle Assigned Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$accent = $themeColors['accent'] ?? '#D6B77A';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Customer';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #E0F2FE; border: 1px solid #BAE6FD; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #0369A1;">
                        🚍 Chauffeur &amp; Vehicle Assigned
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Code: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, a dedicated fleet vehicle and professional chauffeur have been assigned to your booking #<strong><?= e($booking['booking_code']) ?></strong> with <?= e($companyName) ?>.
</p>

<!-- CHAUFFEUR & FLEET DETAILS CARD -->
<?php
$cardTitle = 'ASSIGNED CHAUFFEUR & FLEET DETAILS';
$pickupTimeFormatted = !empty($booking['pickup_time']) ? date('h:i A', strtotime($booking['pickup_time'])) : 'Scheduled';
$cardRows = [
    ['label' => 'Chauffeur Name', 'value' => $driver['name'] ?? 'Assigned Chauffeur'],
    ['label' => 'Chauffeur Mobile', 'html' => '<a href="tel:' . e($driver['phone'] ?? '') . '" style="color: ' . e($primary) . '; font-weight: 700; text-decoration: none;">📞 ' . e($driver['phone'] ?? 'Provided prior to departure') . '</a>'],
    ['label' => 'Vehicle Model', 'value' => $vehicle['make_model'] ?? 'Fleet Vehicle'],
    ['label' => 'Registration Plate', 'html' => '<span style="font-family: monospace; font-size: 14px; font-weight: 800; color: ' . e($secondary) . '; background: #EAF2ED; padding: 2px 8px; border-radius: 4px;">' . e($vehicle['vehicle_number'] ?? 'N/A') . '</span>'],
    ['label' => 'Pickup Date & Time', 'value' => format_date($booking['start_date'] ?? ($booking['pickup_date'] ?? date('Y-m-d'))) . ' at ' . $pickupTimeFormatted],
    ['label' => 'Pickup Address', 'value' => $booking['pickup_address'] ?? ($booking['pickup_location'] ?? 'Scheduled Location')],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Track Your Reservation';
$btnUrl = url('/office/bookings/' . $booking['id']);
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
