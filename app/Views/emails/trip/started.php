<!-- Trip Started Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #E0F2FE; color: #0369A1; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #BAE6FD;">
                🚀 Trip Commenced
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Your Journey is Underway
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Passenger') ?>, your journey for reservation <strong><?= e($booking['booking_code']) ?></strong> has officially commenced. Wishing you a safe, smooth, and relaxing trip!
            </p>
        </td>
    </tr>

    <!-- En Route Details -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #0369A1; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        LIVE TRIP STATUS: EN ROUTE
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Departure Timestamp:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_datetime(date('Y-m-d H:i:s')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Destination:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Chauffeur:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($driver['name'] ?? 'Assigned Chauffeur') ?> <?= !empty($driver['phone']) ? '• Ph: ' . e($driver['phone']) : '' ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Fleet Vehicle:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600;">
                        <?= e($vehicle['make_model'] ?? 'Fleet Vehicle') ?> (<?= e($vehicle['vehicle_number'] ?? 'N/A') ?>)
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Safety / Live Support Banner -->
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #166534; line-height: 1.5;">
                <strong>Passenger Safety:</strong> Our operations command center actively monitors vehicle telemetry throughout your trip. In case of emergency or route adjustments, our 24/7 hotline is +91 98220 12345.
            </div>
        </td>
    </tr>
</table>
