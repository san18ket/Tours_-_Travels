<?php
/**
 * Upcoming Trip Reminder Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$customerName = $customer['name'] ?? 'Valued Traveler';
$pickupTimeFormatted = !empty($booking['pickup_time']) ? date('h:i A', strtotime($booking['pickup_time'])) : 'Scheduled';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #92400E;">
                        ⏰ Upcoming Trip Reminder
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
    Dear <strong><?= e($customerName) ?></strong>, this is a friendly reminder for your upcoming journey under reservation #<strong><?= e($booking['booking_code'] ?? '') ?></strong>.
</p>

<!-- TRIP DETAILS CARD -->
<?php
$cardTitle = 'DEPARTURE SCHEDULE: ' . ($booking['booking_code'] ?? '');
$cardRows = [
    ['label' => 'Scheduled Departure', 'html' => '<strong style="color: #92400E;">' . format_date($booking['start_date']) . ' at ' . $pickupTimeFormatted . '</strong>'],
    ['label' => 'Pickup Address', 'value' => $booking['pickup_address']],
    ['label' => 'Destination', 'value' => $booking['drop_address']],
    ['label' => 'Assigned Fleet Vehicle', 'value' => ($vehicle['make_model'] ?? 'Chauffeur Vehicle') . ' (' . ($vehicle['vehicle_number'] ?? 'Assigned') . ')'],
    ['label' => 'Assigned Chauffeur', 'value' => ($driver['name'] ?? 'Chauffeur on Duty') . (!empty($driver['phone']) ? ' • Ph: ' . $driver['phone'] : '')],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'View Live Reservation';
$btnUrl = url('/office/bookings/' . ($booking['id'] ?? 0));
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
