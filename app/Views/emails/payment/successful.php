<!-- Payment Successful Template -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <div style="display: inline-block; background-color: #D1FAE5; color: #065F46; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px; border: 1px solid #A7F3D0;">
                ✓ Payment Received
            </div>
            <h1 style="margin: 12px 0 6px 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                Payment Receipt Confirmation
            </h1>
            <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.5;">
                Dear <?= e($customer['name'] ?? 'Valued Customer') ?>, we have successfully received your payment of <strong><?= format_currency((float)($payment['amount'] ?? 0)) ?></strong>. Your official digital receipt is detailed below.
            </p>
        </td>
    </tr>

    <!-- Payment Receipt Card -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td colspan="2" style="background-color: #0F172A; color: #FFFFFF; padding: 12px 18px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                        RECEIPT REF: <?= e($payment['payment_reference'] ?? 'PAY-OFFICIAL') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0; width: 40%;">Amount Credited:</td>
                    <td style="padding: 14px 18px; font-size: 18px; color: #059669; font-weight: 800; border-bottom: 1px solid #E2E8F0;">
                        <?= format_currency((float)($payment['amount'] ?? 0)) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Invoice Number:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($invoice['invoice_number'] ?? 'N/A') ?>
                    </td>
                </tr>
                <?php if (!empty($booking['booking_code'])): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Associated Booking:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= e($booking['booking_code']) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Payment Method:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #E2E8F0;">
                        <?= e($payment['payment_method'] ?? 'Online') ?>
                    </td>
                </tr>
                <?php if (!empty($payment['transaction_id'])): ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Bank / Gateway Ref:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-family: monospace; border-bottom: 1px solid #E2E8F0;">
                        <?= e($payment['transaction_id']) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B; border-bottom: 1px solid #E2E8F0;">Date &amp; Time:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0F172A; font-weight: 600; border-bottom: 1px solid #E2E8F0;">
                        <?= format_datetime($payment['paid_at'] ?? date('Y-m-d H:i:s')) ?>
                    </td>
                </tr>
                <?php 
                $invoiceTotal = (float)($invoice['total_amount'] ?? $booking['total_amount'] ?? 0);
                $paidSoFar = (float)($invoice['paid_amount'] ?? $payment['amount'] ?? 0);
                $balance = max(0.0, $invoiceTotal - $paidSoFar);
                ?>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748B;">Balance Remaining:</td>
                    <td style="padding: 12px 18px; font-size: 13px; color: <?= $balance <= 0 ? '#059669' : '#D97706' ?>; font-weight: 700;">
                        <?= $balance <= 0 ? 'Full Balance Cleared (₹ 0.00)' : format_currency($balance) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- CTA Button -->
    <?php if (!empty($invoice['id'])): ?>
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-radius: 8px; background-color: #0F172A;">
                        <a href="<?= e(url('/office/invoices/' . $invoice['id'])) ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; letter-spacing: 0.3px;">
                            View Official Tax Invoice &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php endif; ?>
</table>
