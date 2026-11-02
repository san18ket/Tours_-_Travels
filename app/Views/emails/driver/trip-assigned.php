<!-- Driver Trip Assigned Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #CCFBF1; color: #0F766E; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #99F6E4;">
                🚍 Chauffeur Duty Assignment
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                New Trip Assignment: <?= e($dispatch['dispatch_code'] ?? 'DSP-DUTY') ?>
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Hello <strong><?= e($driver['name'] ?? 'Chauffeur') ?></strong>, you have been deployed for operational duty for reservation <strong><?= e($booking['booking_code'] ?? '') ?></strong>. Please review your trip itinerary below.
            </p>
        </td>
    </tr>

    <!-- Duty Card -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #0F766E; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        OPERATIONAL DISPATCH MANIFEST
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 38%;">Assigned Fleet Vehicle:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 700; border-bottom: 1px solid #E2E8F0;">
                        <?= e($vehicle['make_model'] ?? 'Fleet Vehicle') ?> 
                        <span style="color: #0F766E;">(<?= e($vehicle['vehicle_number'] ?? 'N/A') ?>)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Reporting Time:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 700; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['start_date']) ?> at <?= date('h:i A', strtotime($booking['pickup_time'] ?? '09:00:00')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Pickup Location:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['pickup_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Drop Destination:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Guest Passenger:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['customer_name'] ?? $customer['name'] ?? 'Passenger') ?> (<?= (int)($booking['passenger_count'] ?? 1) ?> Guests)
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Passenger Contact:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0284C7; font-weight: 600;">
                        <?= e($booking['customer_phone'] ?? $customer['phone'] ?? 'N/A') ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- CTA Button to Driver Portal -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #0F766E;">
                        <a href="<?= e(url('/driver/trips/' . ($dispatch['id'] ?? ''))) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            Access Chauffeur Portal to Accept &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
