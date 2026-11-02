<!-- Trip Reminder Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #FDE68A;">
                ⏰ Departure Reminder
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Your Upcoming Journey Reminder
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($recipient['name'] ?? 'Valued Traveler') ?>, this is an operational reminder for your upcoming journey under reservation <strong><?= e($booking['booking_code'] ?? '') ?></strong>.
            </p>
        </td>
    </tr>

    <!-- Reminder Details Box -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #B45309; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        DEPARTURE SCHEDULE: <?= e($booking['booking_code'] ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Scheduled Reporting:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #B45309; font-weight: 800; border-bottom: 1px solid #E2E8F0;">
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
                <?php if (!empty($vehicle['make_model'])): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Assigned Fleet Vehicle:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($vehicle['make_model']) ?> (<?= e($vehicle['vehicle_number'] ?? '') ?>)
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($driver['name'])): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Chauffeur:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600;">
                        <?= e($driver['name']) ?> <?= !empty($driver['phone']) ? '• Ph: ' . e($driver['phone']) : '' ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>

    <!-- Travel Tips -->
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="background-color: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 8px; padding: 14px 18px; font-size: 12px; color: #475569; line-height: 1.5;">
                <strong>Travel Checklist:</strong> Please be ready at your designated pickup address 10 minutes prior to scheduled departure. Keep a valid government ID handy for verification.
            </div>
        </td>
    </tr>
</table>
