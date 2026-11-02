<!-- Booking Confirmed Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #D1FAE5; color: #065F46; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #A7F3D0;">
                ✓ Booking Confirmed
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Your Journey is Officially Confirmed
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Guest') ?>, we are pleased to inform you that your travel booking has been verified and confirmed. A fleet vehicle and chauffeur are being prepared for your itinerary.
            </p>
        </td>
    </tr>

    <!-- Booking Confirmation Details -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #065F46; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        CONFIRMED ITINERARY: <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Pickup Address:</td>
                    <td style="padding: 14px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['pickup_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Drop Destination:</td>
                    <td style="padding: 14px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['drop_address']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Scheduled Departure:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_date($booking['start_date']) ?> at <?= date('h:i A', strtotime($booking['pickup_time'] ?? '09:00:00')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Passengers:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= (int)($booking['passenger_count'] ?? 1) ?> Guest(s)
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 18px; font-size: 14px; color: #0F172A; font-weight: 700;">Total Confirmed Fare:</td>
                    <td style="padding: 14px 18px; font-size: 16px; color: #059669; font-weight: 800;">
                        <?= format_currency((float)($booking['total_amount'] ?? 0)) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Next Steps Card -->
    <tr>
        <td style="padding-bottom: 22px;">
            <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #1E40AF; line-height: 1.5;">
                <strong>What's next:</strong> You will receive a separate notification with your designated chauffeur's contact number and vehicle registration details once assigned to your trip.
            </div>
        </td>
    </tr>

    <!-- CTA Button -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #065F46;">
                        <a href="<?= e(url('/office/bookings/' . $booking['id'])) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            Access Your Reservation &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
