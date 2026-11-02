<!-- Booking Created Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #FDE68A;">
                Booking Created • Pending Confirmation
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Your Reservation is Confirmed in System
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Guest') ?>, thank you for booking with <?= e(\App\Services\CompanySettingsService::getCompanyName()) ?>. We have received your travel itinerary and our operations team is preparing your dispatch.
            </p>
        </td>
    </tr>

    <!-- Booking Summary Card -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #0F172A; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        RESERVATION DETAILS: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Route / Itinerary:</td>
                    <td style="padding: 14px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['pickup_address']) ?> <br>
                        <span style="color: #10B981;">&darr; to</span><br>
                        <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Reporting Time:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['start_date']) ?> at <?= date('h:i A', strtotime($booking['pickup_time'] ?? '09:00:00')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">End / Return Date:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['end_date'] ?? $booking['start_date']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Passengers:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= (int)($booking['passenger_count'] ?? 1) ?> Guest(s)
                    </td>
                </tr>
                <?php if (!empty($booking['special_requests'])): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Special Requests:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; border-bottom: 1px solid #E2E8F0;">
                        <?= nl2br(e($booking['special_requests'])) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 14px 18px; font-size: 14px; color: #0F172A; font-weight: 700;">Total Amount:</td>
                    <td style="padding: 14px 18px; font-size: 16px; color: #10B981; font-weight: 800;">
                        <?= format_currency((float)($booking['total_amount'] ?? 0)) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Call to action button -->
    <tr>
        <td align="center" style="padding-top: 10px; padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #10B981;">
                        <a href="<?= e(url('/office/bookings/' . $booking['id'])) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            View Reservation Details &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
