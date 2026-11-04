<?php
/**
 * Booking Confirmed Email Template
 * Dynamically styled with company website theme colors.
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$accent = $themeColors['accent'] ?? '#D6B77A';
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
                        ✓ Reservation Confirmed
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
    Dear <strong><?= e($customerName) ?></strong>, we are delighted to confirm your travel reservation with <?= e($companyName) ?>. A fleet vehicle and chauffeur are being scheduled for your itinerary.
</p>

<!-- ITINERARY DETAILS CARD -->
<?php
$cardTitle = 'CONFIRMED ITINERARY: ' . $booking['booking_code'];
$pickupTimeFormatted = !empty($booking['pickup_time']) ? date('h:i A', strtotime($booking['pickup_time'])) : 'Scheduled';
$cardRows = [
    ['label' => 'Booking Reference', 'value' => $booking['booking_code']],
    ['label' => 'Pickup Location', 'value' => $booking['pickup_address'] ?? ($booking['pickup_location'] ?? 'Scheduled Location')],
    ['label' => 'Destination', 'value' => $booking['drop_address'] ?? ($booking['dropoff_location'] ?? ($booking['drop_location'] ?? 'Scheduled Destination'))],
    ['label' => 'Travel Date', 'value' => format_date($booking['start_date'] ?? ($booking['pickup_date'] ?? date('Y-m-d'))) . ' at ' . $pickupTimeFormatted],
    ['label' => 'Passengers', 'value' => ((int)($booking['passenger_count'] ?? 1)) . ' Guest(s)'],
    ['label' => 'Confirmed Total Fare', 'html' => '<span style="font-size: 16px; font-weight: 800; color: #168653;">' . format_currency((float)($booking['total_amount'] ?? 0)) . '</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- NEXT STEPS -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; margin: 16px 0;">
    <tr>
        <td style="padding: 14px 18px; font-size: 13px; color: #1E40AF; line-height: 1.5;">
            <strong>Next Steps:</strong> You will receive a notification with your designated chauffeur's contact number and vehicle registration details prior to your pickup.
        </td>
    </tr>
</table>

<!-- CTA BUTTON -->
<?php
$btnText = 'View Booking Details';
$btnUrl = url('/office/bookings/' . $booking['id']);
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
