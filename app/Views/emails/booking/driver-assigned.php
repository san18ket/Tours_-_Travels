<!-- Customer: Driver Assigned Notification -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #CCFBF1; color: #0F766E; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #99F6E4;">
                🚍 Chauffeur &amp; Vehicle Assigned
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Your Chauffeur &amp; Fleet Details
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Customer') ?>, a dedicated executive vehicle and professional chauffeur have been assigned to your booking <strong><?= e($booking['booking_code']) ?></strong>.
            </p>
        </td>
    </tr>

    <!-- Chauffeur & Vehicle Card -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #0F766E; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        ASSIGNED CHAUFFEUR &amp; VEHICLE
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Chauffeur Name:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #0F172A; font-weight: 700; border-bottom: 1px solid #E2E8F0;">
                        <?= e($driver['name'] ?? 'Assigned Chauffeur') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Chauffeur Contact:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #0284C7; font-weight: 700; border-bottom: 1px solid #E2E8F0;">
                        <?= e($driver['phone'] ?? 'Provided prior to departure') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Vehicle Model:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($vehicle['make_model'] ?? 'Fleet Vehicle') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Vehicle Registration No:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #0F766E; font-weight: 700; font-family: monospace; border-bottom: 1px solid #E2E8F0;">
                        <?= e($vehicle['vehicle_number'] ?? 'N/A') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Scheduled Pickup:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['start_date']) ?> at <?= date('h:i A', strtotime($booking['pickup_time'] ?? '09:00:00')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Pickup Location:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600;">
                        <?= e($booking['pickup_address']) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- CTA Button -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #0F766E;">
                        <a href="<?= e(url('/office/bookings/' . $booking['id'])) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            View Booking &amp; Live Tracking &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
