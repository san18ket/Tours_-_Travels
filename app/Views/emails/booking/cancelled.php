<!-- Booking Cancelled Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #FEE2E2; color: #991B1B; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #FECACA;">
                ✕ Booking Cancelled
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Notice of Booking Cancellation
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Customer') ?>, this email confirms that booking reservation <strong><?= e($booking['booking_code']) ?></strong> has been cancelled.
            </p>
        </td>
    </tr>

    <!-- Details Box -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #991B1B; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        CANCELLED RESERVATION: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Route:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['pickup_address']) ?> &rarr; <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Scheduled Date:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['start_date']) ?>
                    </td>
                </tr>
                <?php if (!empty($reason)): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Reason for Cancellation:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #DC2626; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($reason) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Total Booking Value:</td>
                    <td style="padding: 12px 18px; font-size: 14px; color: #0F172A; font-weight: 700;">
                        <?= format_currency((float)($booking['total_amount'] ?? 0)) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Refund & Support notice -->
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="background-color: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #92400E; line-height: 1.5;">
                <strong>Refund &amp; Settlement:</strong> If you have already made advance or partial payment towards this booking, our accounts team will initiate your refund or credit note in accordance with <?= e(\App\Services\CompanySettingsService::getCompanyName()) ?> terms. Please contact our support team if you require assistance or wish to reschedule.
            </div>
        </td>
    </tr>
</table>
