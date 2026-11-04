<?php
/**
 * Driver Duty Assignment Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$driverName = $driver['name'] ?? ($driver['first_name'] ?? 'Driver');
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #92400E;">
                        📋 Duty Assignment Notice
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Trip: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Hello <strong><?= e($driverName) ?></strong>, a new passenger trip has been assigned to you by dispatch. Please inspect your assigned vehicle and report at least 30 minutes prior to scheduled departure.
</p>

<!-- TRIP DISPATCH DETAILS CARD -->
<?php
$cardTitle = 'DISPATCH & PASSENGER DETAILS';
$pickupTimeFormatted = !empty($booking['pickup_time']) ? date('h:i A', strtotime($booking['pickup_time'])) : 'Scheduled';
$cardRows = [
    ['label' => 'Trip / Booking Code', 'value' => $booking['booking_code']],
    ['label' => 'Scheduled Departure', 'value' => format_date($booking['start_date'] ?? ($booking['pickup_date'] ?? date('Y-m-d'))) . ' at ' . $pickupTimeFormatted],
    ['label' => 'Pickup Location', 'value' => $booking['pickup_address'] ?? ($booking['pickup_location'] ?? 'Scheduled Location')],
    ['label' => 'Drop Destination', 'value' => $booking['drop_address'] ?? ($booking['dropoff_location'] ?? ($booking['drop_location'] ?? 'Scheduled Destination'))],
    ['label' => 'Passenger Name', 'value' => $customer['name'] ?? ($booking['customer_name'] ?? 'Guest')],
    ['label' => 'Passenger Mobile', 'html' => '<a href="tel:' . e($customer['phone'] ?? ($booking['customer_phone'] ?? '')) . '" style="color: ' . e($primary) . '; font-weight: 700; text-decoration: none;">📞 ' . e($customer['phone'] ?? ($booking['customer_phone'] ?? 'On File')) . '</a>'],
    ['label' => 'Assigned Vehicle', 'value' => ($vehicle['make_model'] ?? 'Vehicle') . ' (' . ($vehicle['vehicle_number'] ?? 'N/A') . ')'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Open Driver Portal';
$btnUrl = url('/driver/trips');
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>
