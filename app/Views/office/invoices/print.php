<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Tax Invoice - <?= e($invoice['invoice_number']) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.5; padding: 40px; margin: 0; background: #FFF; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; }
        .header-table { width: 100%; margin-bottom: 25px; border-bottom: 2px solid #0B192C; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #0B192C; }
        .invoice-title { font-size: 22px; font-weight: bold; color: #008DDA; text-align: right; }
        .meta-table { width: 100%; margin-bottom: 25px; font-size: 14px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #0B192C; color: #FFF; text-align: left; padding: 10px; font-size: 13px; text-transform: uppercase; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #0B192C; }
        .print-btn { background: #008DDA; color: #FFF; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } body { padding: 0; } .invoice-box { border: none; } }
    </style>
</head>
<body>
    <div style="text-align: right; max-width: 800px; margin: auto;">
        <button onclick="window.print()" class="print-btn">🖨️ Print / Save as PDF</button>
    </div>

    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-name">VoyageGo Tours & Travels</div>
                    <div style="font-size: 13px; color: #64748B;">
                        VoyageGo Operations Hub, Aero Mall Road<br>
                        Terminal 2, New Delhi 110037, India<br>
                        GSTIN: 07AAAAA0000A1Z5 | Support: +91 98765 43210
                    </div>
                </td>
                <td style="vertical-align: top;">
                    <div class="invoice-title">TAX INVOICE</div>
                    <div style="text-align: right; font-size: 13px; color: #64748B; margin-top: 5px;">
                        Invoice No: <strong><?= e($invoice['invoice_number']) ?></strong><br>
                        Date: <?= format_date($invoice['issued_at']) ?><br>
                        Status: <strong><?= strtoupper(e($invoice['payment_status'])) ?></strong>
                    </div>
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Billed To:</strong><br>
                    <?= e($invoice['customer_name']) ?><br>
                    <?= e($invoice['customer_phone'] ?? '') ?><br>
                    <?= e($invoice['customer_email']) ?>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong>Booking Details:</strong><br>
                    Ref: <strong><?= e($invoice['booking_code']) ?></strong><br>
                    Tour: <?= e($invoice['tour_title'] ?? 'Custom Fleet Chauffeur') ?><br>
                    Travel: <?= format_date($invoice['start_date']) ?> to <?= format_date($invoice['end_date']) ?>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: right;">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Fleet Chauffeur & Tour Transport Service</strong><br>
                        <span style="font-size: 12px; color: #64748B;"><?= e($invoice['pickup_address']) ?> → <?= e($invoice['drop_address']) ?></span>
                    </td>
                    <td style="text-align: right;"><?= format_currency((float)$invoice['subtotal']) ?></td>
                </tr>
                <?php if ((float)$invoice['discount_amount'] > 0): ?>
                <tr>
                    <td style="color: #16a34a;">Promotional Discount</td>
                    <td style="text-align: right; color: #16a34a;">- <?= format_currency((float)$invoice['discount_amount']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Goods & Services Tax (GST @ <?= e($invoice['tax_rate']) ?>%)</td>
                    <td style="text-align: right;"><?= format_currency((float)$invoice['tax_amount']) ?></td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL AMOUNT DUE</td>
                    <td style="text-align: right; color: #008DDA;"><?= format_currency((float)$invoice['total_amount']) ?></td>
                </tr>
                <tr>
                    <td style="color: #16a34a; font-weight: bold;">Amount Paid to Date</td>
                    <td style="text-align: right; color: #16a34a; font-weight: bold;"><?= format_currency((float)$invoice['paid_amount']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Balance Remaining</td>
                    <td style="text-align: right; font-weight: bold;"><?= format_currency(max(0.0, (float)$invoice['total_amount'] - (float)$invoice['paid_amount'])) ?></td>
                </tr>
            </tbody>
        </table>

        <div style="font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: space-between;">
            <div>Computer generated invoice. No physical signature required.</div>
            <div>Authorized Signatory: <strong>VoyageGo Billing Division</strong></div>
        </div>
    </div>
</body>
</html>
