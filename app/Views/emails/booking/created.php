<?php
/**
 * Booking Created (Pending Confirmation) Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Guest';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #92400E;">
                        ⏳ Reservation Received (Under Review)
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
    Dear <strong><?= e($customerName) ?></strong>, we have received your travel booking request for <strong><?= e($companyName) ?></strong>. Our dispatch team is reviewing vehicle and chauffeur availability.
</p>

<!-- ITINERARY DETAILS CARD -->
<?php
$cardTitle = 'RESERVATION REQUEST: ' . $booking['booking_code'];
$pickupTimeFormatted = !empty($booking['pickup_time']) ? date('h:i A', strtotime($booking['pickup_time'])) : 'Scheduled';
$cardRows = [
    ['label' => 'Booking Reference', 'value' => $booking['booking_code']],
    ['label' => 'Pickup Location', 'value' => $booking['pickup_address']],
    ['label' => 'Destination', 'value' => $booking['drop_address']],
    ['label' => 'Requested Date', 'value' => format_date($booking['start_date']) . ' at ' . $pickupTimeFormatted],
    ['label' => 'Passengers', 'value' => ((int)($booking['passenger_count'] ?? 1)) . ' Guest(s)'],
    ['label' => 'Estimated Total', 'html' => '<span style="font-size: 16px; font-weight: 800; color: ' . e($secondary) . ';">' . format_currency((float)($booking['total_amount'] ?? 0)) . '</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Check Reservation Status';
$btnUrl = url('/office/bookings/' . $booking['id']);
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
