<?php
// Function to convert amount to Indian Rupee Words
if (!function_exists('convertAmountToWordsINR')) {
    function convertAmountToWordsINR(float $number): string {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
            50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty',
            90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $d = 0;
        $res = implode('', array_reverse($str));
        $points = ($decimal) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return empty(trim($res)) ? 'Zero Rupees Only' : trim($res) . " Rupees" . $points . " Only";
    }
}

$totalAmt = (float)$invoice['total_amount'];
$paidAmt = (float)$invoice['paid_amount'];
$balanceAmt = max(0.0, $totalAmt - $paidAmt);
$amtInWords = convertAmountToWordsINR($totalAmt);

$paymentStatus = strtolower(trim((string)($invoice['payment_status'] ?? 'unpaid')));
$isFullyPaid = ($paymentStatus === 'paid' && $balanceAmt <= 0.01 && $totalAmt > 0);

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$companyTagline = \App\Services\CompanySettingsService::getTagline();
$companyLogoUrl = \App\Services\CompanySettingsService::getLogoUrl();
$paidStampUrl = \App\Services\CompanySettingsService::getPaidStampUrl();
$authSigUrl = \App\Services\CompanySettingsService::getSignatureUrl();
$sigStampUrl = \App\Services\CompanySettingsService::getSignatureStampUrl();
$companyAddress = \App\Services\CompanySettingsService::getFullAddress() ?: \App\Services\CompanySettingsService::getAddress();
$companyPhone = \App\Services\CompanySettingsService::getPhone();
$companyAltPhone = \App\Services\CompanySettingsService::getAlternatePhone();
$companyEmail = \App\Services\CompanySettingsService::getCompanyEmail();
$companyGst = \App\Services\CompanySettingsService::getGstNumber();
$companyPan = \App\Services\CompanySettingsService::getPanNumber();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Receipt - <?= e($invoice['invoice_number']) ?> - <?= e($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
            color: #20352F;
            background: #F7F8F4;
            padding: 30px 20px;
            line-height: 1.45;
        }

        .action-bar {
            max-width: 860px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-print {
            background: linear-gradient(135deg, #1E7A68 0%, #176B5B 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(23, 107, 91, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(23, 107, 91, 0.4);
        }

        .btn-light {
            background: #FFFFFF;
            color: #176B5B;
            border: 1px solid rgba(23, 107, 91, 0.25);
            box-shadow: 0 2px 8px rgba(22, 68, 59, 0.06);
        }

        .btn-light:hover {
            background: #EAF2ED;
            border-color: #176B5B;
        }

        /* Printable Receipt Box */
        .receipt-card {
            max-width: 860px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(22, 68, 59, 0.10);
            border: 2px solid #176B5B;
            padding: 36px 40px;
            position: relative;
        }

        /* Header Branding */
        .receipt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px dashed #CBDDF5;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }

        .receipt-brand-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .receipt-logo {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1E7A68, #176B5B);
            padding: 8px;
            box-shadow: 0 6px 16px rgba(23, 107, 91, 0.25);
        }

        .company-title {
            font-size: 26px;
            font-weight: 800;
            color: #16443B;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .company-subtitle {
            font-size: 16px;
            font-weight: 700;
            color: #176B5B;
            margin-top: 2px;
        }

        .company-tagline {
            font-size: 12px;
            font-weight: 600;
            color: #72827A;
            margin-top: 4px;
        }

        .company-contact {
            font-size: 11px;
            color: #72827A;
            margin-top: 4px;
            line-height: 1.4;
        }

        .receipt-bill-badge {
            text-align: right;
        }

        .bill-badge-title {
            display: inline-block;
            background: #176B5B;
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 800;
            padding: 6px 18px;
            border-radius: 8px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .bill-meta-row {
            margin-top: 8px;
            font-size: 13px;
            color: #53685F;
        }

        /* 2-Column Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #F7F8F4;
            border: 1px solid #EAF2ED;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            width: 140px;
            color: #72827A;
            font-weight: 600;
            flex-shrink: 0;
        }

        .info-val {
            color: #16443B;
            font-weight: 700;
            flex-grow: 1;
        }

        /* Rates and Charges Grid */
        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .bill-table th {
            background: #16443B;
            color: #FFFFFF;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }

        .bill-table th:last-child {
            text-align: right;
        }

        .bill-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #EAF2ED;
            font-size: 13px;
            color: #53685F;
        }

        .bill-table td:last-child {
            text-align: right;
            font-weight: 700;
            color: #16443B;
        }

        .bill-table tbody tr:nth-child(even) td {
            background: #F7F8F4;
        }

        /* Financial Summary Box */
        .financial-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background: #F7F8F4;
            border-radius: 14px;
            border: 1px solid #EAF2ED;
        }

        .words-section {
            flex: 1;
        }

        .words-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #72827A;
            margin-bottom: 6px;
        }

        .words-val {
            font-size: 14px;
            font-weight: 700;
            color: #16443B;
            font-style: italic;
            background: #FFFFFF;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px dashed rgba(23, 107, 91, 0.3);
        }

        .summary-totals {
            width: 280px;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }

        .total-item.grand-total {
            border-top: 2px solid #176B5B;
            border-bottom: 2px solid #176B5B;
            padding: 10px 0;
            margin: 6px 0;
            font-size: 17px;
            font-weight: 800;
            color: #176B5B;
        }

        .total-item.advance-paid {
            color: #218765;
            font-weight: 700;
        }

        .total-item.balance-due {
            color: #D95757;
            font-weight: 800;
            font-size: 15px;
        }

        /* Signatures Footer */
        .receipt-signatures {
            display: flex;
            justify-content: space-between;
            padding-top: 40px;
            border-top: 1px solid #EAF2ED;
        }

        .sig-block {
            text-align: center;
            width: 220px;
        }

        .sig-line {
            border-top: 1.5px solid rgba(22, 68, 59, 0.25);
            margin-bottom: 8px;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #16443B;
        }

        .sig-sub {
            font-size: 11px;
            color: #72827A;
        }

        /* Print Specific Styling */
        @media print {
            body { background: #FFFFFF; padding: 0; }
            .action-bar { display: none !important; }
            .sig-unconfigured-notice { display: none !important; }
            .receipt-card {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar with Signature Selection Controls -->
    <div class="action-bar">
        <div>
            <a href="<?= url('/office/invoices/' . $invoice['id']) ?>" class="btn-action btn-light">
                &larr; Back to Invoice
            </a>
        </div>

        <!-- Selectable Signature Mode for Invoice Printing -->
        <div style="display: flex; align-items: center; gap: 8px; background: #FFFFFF; padding: 6px 14px; border-radius: 12px; border: 1px solid rgba(23, 107, 91, 0.25); box-shadow: 0 2px 8px rgba(22, 68, 59, 0.06);">
            <span style="font-size: 13px; font-weight: 700; color: #16443B; display: flex; align-items: center; gap: 6px;">
                ✍️ Signature Print:
            </span>
            <div style="display: flex; gap: 12px; font-size: 13px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #20352F; font-weight: 500;">
                    <input type="radio" name="sigOption" value="none" checked onchange="handleSigOptionChange('none')">
                    <span>No Signature</span>
                </label>
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #20352F; font-weight: 500;">
                    <input type="radio" name="sigOption" value="auth" onchange="handleSigOptionChange('auth')">
                    <span>Authorized Signature</span>
                </label>
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #20352F; font-weight: 500;">
                    <input type="radio" name="sigOption" value="stamp" onchange="handleSigOptionChange('stamp')">
                    <span>Signature + Stamp</span>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="shareBill()" class="btn-action btn-light">
                🔗 Share Bill
            </button>
            <button onclick="window.print()" class="btn-action btn-print">
                🖨️ Print Receipt / Download PDF
            </button>
        </div>
    </div>

    <!-- Official Printable Manaswi Tours & Travels Receipt -->
    <div class="receipt-card">

        <!-- Fully Paid Stamp (Rendered ONLY when Invoice is Fully Paid AND stamp is uploaded) -->
        <?php if ($isFullyPaid && !empty($paidStampUrl)): ?>
            <div class="paid-stamp-watermark" style="position: absolute; right: 40px; top: 110px; z-index: 10; pointer-events: none; transform: rotate(-14deg); opacity: 0.90;" title="Invoice Fully Paid">
                <img src="<?= e($paidStampUrl) ?>" alt="PAID" style="max-width: 170px; max-height: 105px; object-fit: contain; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.12));">
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-brand-left">
                <?php if (!empty($companyLogoUrl)): ?>
                    <img src="<?= e($companyLogoUrl) ?>" alt="Logo" class="receipt-logo" style="object-fit: contain; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 4px;">
                <?php endif; ?>
                <div>
                    <div class="company-title"><?= e($companyName) ?></div>
                    <?php if (!empty($companyTagline)): ?>
                        <div class="company-tagline"><?= e($companyTagline) ?></div>
                    <?php endif; ?>
                    <div class="company-contact">
                        <?= e($companyAddress ?: 'Registered Office Address') ?><br>
                        Phone: <?= e($companyPhone ?: '—') ?>
                        <?php if (!empty($companyAltPhone)): ?> | Alt: <?= e($companyAltPhone) ?><?php endif; ?>
                        <?php if (!empty($companyEmail)): ?> | Email: <?= e($companyEmail) ?><?php endif; ?><br>
                        <?php if (!empty($companyGst)): ?>GSTIN: <strong><?= e($companyGst) ?></strong><?php endif; ?>
                        <?php if (!empty($companyPan)): ?> | PAN: <strong><?= e($companyPan) ?></strong><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="receipt-bill-badge">
                <div class="bill-badge-title">OFFICIAL RECEIPT</div>
                <div class="bill-meta-row">
                    Bill No: <strong><?= e($invoice['invoice_number']) ?></strong>
                </div>
                <div class="bill-meta-row">
                    Date: <strong><?= format_date($invoice['issued_at']) ?></strong>
                </div>
                <div class="bill-meta-row">
                    Status: <strong style="color:<?= $isFullyPaid ? '#10B981' : ($paymentStatus === 'partial' ? '#2563EB' : '#DC2626') ?>; text-transform:uppercase;"><?= e($invoice['payment_status']) ?></strong>
                </div>
            </div>
        </div>

        <!-- 2-Column Info Grid (Customer, Vehicle, Journey) -->
        <div class="info-grid">
            <div>
                <div class="info-row">
                    <span class="info-label">Customer Name:</span>
                    <span class="info-val"><?= e($invoice['customer_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Phone:</span>
                    <span class="info-val"><?= e($invoice['customer_phone'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle Number:</span>
                    <span class="info-val"><?= e($invoice['registration_number'] ?? 'MH-12-TR-8822') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle Type:</span>
                    <span class="info-val"><?= e($invoice['vehicle_model'] ?? 'Tempo Traveller (17 Seater AC)') ?></span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">Booking Ref:</span>
                    <span class="info-val"><?= e($invoice['booking_code'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Journey From:</span>
                    <span class="info-val"><?= e($invoice['pickup_address'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Journey To:</span>
                    <span class="info-val"><?= e($invoice['drop_address'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Travel Dates:</span>
                    <span class="info-val"><?= !empty($invoice['start_date']) ? format_date($invoice['start_date']) . ' to ' . format_date($invoice['end_date']) : '—' ?></span>
                </div>
            </div>
        </div>

        <!-- Billing Rate Breakdown Table -->
        <table class="bill-table">
            <thead>
                <tr>
                    <th>Particulars &amp; Service Description</th>
                    <th style="text-align: center;">Units / Metric</th>
                    <th style="text-align: right;">Rate (₹)</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Charges Per KM &bull; Tour Distance</strong><br>
                        <span style="font-size: 11px; color: #7890AA;">Outstation round-trip mileage covered</span>
                    </td>
                    <td style="text-align: center;">450 KM</td>
                    <td style="text-align: right;">₹24.00 / KM</td>
                    <td>₹10,800.00</td>
                </tr>
                <tr>
                    <td>
                        <strong>Charges Per Day &bull; Fleet Hire</strong><br>
                        <span style="font-size: 11px; color: #7890AA;">Full day air-conditioned luxury coach</span>
                    </td>
                    <td style="text-align: center;">2 Days</td>
                    <td style="text-align: right;">₹2,000.00 / Day</td>
                    <td>₹4,000.00</td>
                </tr>
                <tr>
                    <td>
                        <strong>Haulting &bull; Driver Night Allowances</strong><br>
                        <span style="font-size: 11px; color: #7890AA;">Night halt accommodation &amp; driver allowance</span>
                    </td>
                    <td style="text-align: center;">1 Night</td>
                    <td style="text-align: right;">₹500.00 / Night</td>
                    <td>₹500.00</td>
                </tr>
                <tr>
                    <td>
                        <strong>Other Charges / Toll &amp; Parking</strong><br>
                        <span style="font-size: 11px; color: #7890AA;">National highway tollway &amp; state permits</span>
                    </td>
                    <td style="text-align: center;">State Tax &amp; Toll</td>
                    <td style="text-align: right;">Actuals</td>
                    <td>₹450.00</td>
                </tr>
                <?php if ((float)$invoice['tax_amount'] > 0): ?>
                <tr>
                    <td>Goods &amp; Services Tax (GST @ <?= e($invoice['tax_rate']) ?>%)</td>
                    <td style="text-align: center;">Standard Tax</td>
                    <td style="text-align: right;"><?= e($invoice['tax_rate']) ?>%</td>
                    <td><?= format_currency((float)$invoice['tax_amount']) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Summary Totals & Amount in Words -->
        <div class="financial-summary">
            <div class="words-section">
                <div class="words-label">Amount in Words:</div>
                <div class="words-val"><?= e($amtInWords) ?></div>
                <div style="font-size: 11px; color: #7890AA; margin-top: 12px; line-height: 1.5;">
                    &bull; Payment subject to clearance of Cheque / UPI / Bank transfer.<br>
                    &bull; All disputes subject to local jurisdiction.<br>
                    &bull; Thank you for choosing Manaswi Tours &amp; Travels!
                </div>
            </div>
            <div class="summary-totals">
                <div class="total-item">
                    <span>Subtotal:</span>
                    <span><?= format_currency((float)$invoice['subtotal']) ?></span>
                </div>
                <?php if ((float)$invoice['discount_amount'] > 0): ?>
                <div class="total-item" style="color: #20C997;">
                    <span>Discount:</span>
                    <span>- <?= format_currency((float)$invoice['discount_amount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="total-item grand-total">
                    <span>TOTAL AMOUNT:</span>
                    <span><?= format_currency($totalAmt) ?></span>
                </div>
                <div class="total-item advance-paid">
                    <span>ADVANCE PAID:</span>
                    <span><?= format_currency($paidAmt) ?></span>
                </div>
                <div class="total-item balance-due">
                    <span>BALANCE DUE:</span>
                    <span><?= format_currency($balanceAmt) ?></span>
                </div>
            </div>
        </div>

        <!-- Official Signatures -->
        <div class="receipt-signatures">
            <div class="sig-block">
                <div style="height: 55px;"></div>
                <div class="sig-line">Customer Signature</div>
                <div class="sig-sub">Accepted &bull; Received vehicle in good order</div>
            </div>
            <div class="sig-block">
                <div id="companySigContainer" style="min-height: 55px; display: flex; align-items: flex-end; justify-content: center; margin-bottom: 6px;">
                    <!-- Authorized Signature Container -->
                    <div id="authSigBox" style="display: none;">
                        <?php if (!empty($authSigUrl)): ?>
                            <img src="<?= e($authSigUrl) ?>" alt="Authorized Signature" style="max-height: 52px; max-width: 190px; object-fit: contain;">
                        <?php else: ?>
                            <span class="sig-unconfigured-notice" style="font-size: 11px; color: #94A3B8; font-style: italic; background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                (Authorized signature not uploaded in Company Profile)
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Signature + Company Stamp Container -->
                    <div id="stampSigBox" style="display: none;">
                        <?php if (!empty($sigStampUrl)): ?>
                            <img src="<?= e($sigStampUrl) ?>" alt="Signature &amp; Stamp" style="max-height: 60px; max-width: 190px; object-fit: contain;">
                        <?php else: ?>
                            <span class="sig-unconfigured-notice" style="font-size: 11px; color: #94A3B8; font-style: italic; background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                (Signature + Stamp not uploaded in Company Profile)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sig-line">For <?= e($companyName) ?></div>
                <div class="sig-sub">Authorized Signatory / Cashier</div>
            </div>
        </div>
    </div>

    <script>
        function handleSigOptionChange(opt) {
            var authBox = document.getElementById('authSigBox');
            var stampBox = document.getElementById('stampSigBox');
            if (!authBox || !stampBox) return;

            if (opt === 'auth') {
                authBox.style.display = 'block';
                stampBox.style.display = 'none';
            } else if (opt === 'stamp') {
                authBox.style.display = 'none';
                stampBox.style.display = 'block';
            } else {
                authBox.style.display = 'none';
                stampBox.style.display = 'none';
            }
        }

        function shareBill() {
            var url = window.location.href;
            if (navigator.share) {
                navigator.share({
                    title: '<?= e($companyName) ?> Receipt <?= e($invoice['invoice_number']) ?>',
                    text: 'Official Bill Receipt from <?= e($companyName) ?>',
                    url: url
                }).catch(function(err){});
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Receipt link copied to clipboard!');
                });
            }
        }
    </script>
</body>
</html>
