<?php
/**
 * Booking Cancelled Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerName = $customer['name'] ?? 'Valued Guest';
$reasonText = !empty($reason) ? $reason : 'Client or operational schedule change.';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEE2E2; border: 1px solid #FECACA; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #991B1B;">
                        ✕ Booking Cancelled
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #991B1B;">
                        Code: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px 0; font-size: 14px; color: #20352F; line-height: 1.5;">
    Dear <strong><?= e($customerName) ?></strong>, this notification confirms that reservation #<strong><?= e($booking['booking_code']) ?></strong> has been cancelled.
</p>

<!-- ITINERARY DETAILS CARD -->
<?php
$cardTitle = 'CANCELLED RESERVATION: ' . $booking['booking_code'];
$cardRows = [
    ['label' => 'Booking Reference', 'value' => $booking['booking_code']],
    ['label' => 'Pickup Address', 'value' => $booking['pickup_address'] ?? ($booking['pickup_location'] ?? 'Scheduled Location')],
    ['label' => 'Destination', 'value' => $booking['drop_address'] ?? ($booking['dropoff_location'] ?? ($booking['drop_location'] ?? 'Scheduled Destination'))],
    ['label' => 'Original Travel Date', 'value' => format_date($booking['start_date'] ?? ($booking['pickup_date'] ?? date('Y-m-d')))],
    ['label' => 'Cancellation Reason', 'value' => $reasonText],
    ['label' => 'Refund Eligibility', 'html' => '<span style="color: #64748B;">Subject to booking cancellation policy</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 8px; margin: 16px 0;">
    <tr>
        <td style="padding: 14px 18px; font-size: 13px; color: #64748B; line-height: 1.5;">
            If you have questions regarding eligible refunds or need to reschedule your travel, please reach out to our team with reference #<strong><?= e($booking['booking_code']) ?></strong>.
        </td>
    </tr>
</table>
