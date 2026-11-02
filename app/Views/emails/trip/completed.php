<!-- Trip Completed Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #D1FAE5; color: #065F46; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #A7F3D0;">
                ✓ Journey Concluded
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Thank You for Traveling with <?= e(\App\Services\CompanySettingsService::getCompanyName()) ?>
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Passenger') ?>, your journey under reservation <strong><?= e($booking['booking_code']) ?></strong> has been successfully completed. We hope you enjoyed exceptional service and comfort.
            </p>
        </td>
    </tr>

    <!-- Completion Recap Card -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #065F46; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        JOURNEY RECAP MANIFEST
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Completed At:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_datetime($dispatch['end_time'] ?? date('Y-m-d H:i:s')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Route:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['pickup_address']) ?> &rarr; <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <?php 
                $startKm = (int)($dispatch['start_odometer'] ?? 0);
                $endKm = (int)($dispatch['end_odometer'] ?? 0);
                $travelled = max(0, $endKm - $startKm);
                if ($travelled > 0): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Total Distance Travelled:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #065F46; font-weight: 700; border-bottom: 1px solid #E2E8F0;">
                        <?= number_format($travelled) ?> KM
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Fleet Vehicle:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($vehicle['make_model'] ?? 'Fleet Vehicle') ?> (<?= e($vehicle['vehicle_number'] ?? 'N/A') ?>)
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Executive Chauffeur:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600;">
                        <?= e($driver['name'] ?? 'Executive Chauffeur') ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Billing Recap & CTA -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #065F46;">
                        <a href="<?= e(url('/office/bookings/' . $booking['id'])) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            View Booking &amp; Download Invoices &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
