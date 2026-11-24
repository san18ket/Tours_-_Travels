<?php
/**
 * Tours & Travels — Official Public Digital Invoice Verification View
 * Master Brand Theme: Forest Teal (#176B5B) + Deep Forest (#16443B) + Champagne Gold (#D6B77A) + Warm Ivory (#F7F8F4)
 * Complete Information: Customer Details, Trip Particulars, Trip Charges & Fare Calculation, Payment Settlement.
 * Professionally arranged for both Large (lg >= 1024px) and Small (sm < 1024px) viewports.
 */

if (!function_exists('convertAmountToWordsINR')) {
    function convertAmountToWordsINR(float $number): string {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen((string)$no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
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
        $d = array_reverse($str);
        $res = implode('', $d);
        $points = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return empty(trim($res)) ? 'Zero Rupees Only' : trim($res) . " Rupees" . $points . " Only";
    }
}

$totalAmt = (float)($invoice['total_amount'] ?? 0);
$paidAmt = (float)($invoice['paid_amount'] ?? 0);
$balanceAmt = max(0.0, $totalAmt - $paidAmt);
$amtInWords = convertAmountToWordsINR($totalAmt);

$paymentStatus = strtolower(trim((string)($invoice['payment_status'] ?? 'unpaid')));
$isFullyPaid = ($paymentStatus === 'paid' || $balanceAmt <= 0.009);
$isPartial = (!$isFullyPaid && $paidAmt > 0.009);

$invoiceNum = $invoice['invoice_number'] ?: ('INV-' . date('Y') . '-' . str_pad((string)$invoice['id'], 5, '0', STR_PAD_LEFT));
$issuedDate = !empty($invoice['issued_at']) ? date('d M Y', strtotime($invoice['issued_at'])) : date('d M Y');
$dueDate = !empty($invoice['due_date']) ? date('d M Y', strtotime($invoice['due_date'])) : 'On Receipt';

$companyName = company_display_name();
$tagline = company_tagline() ?: 'Commercial Fleet Logistics & Premium Tours';
$logoUrl = company_logo_url();
$address = company_full_address() ?: company_address();
$phone = company_phone();
$altPhone = company_alternate_phone();
$email = company_email();
$gstin = company_gst_number();
$pan = company_pan_number();
$paidStamp = company_paid_stamp_url();

$printUrl = url('/invoice/verify/' . (int)$invoice['id'] . '/print');
$adminPrintUrl = url('/office/invoices/' . (int)$invoice['id'] . '/print');

$bankAccountName = $bankAccount['bank_name'] ?? 'Primary Business Bank';
$bankAccNumber = $bankAccount['account_number'] ?? '';
$bankIfsc = $bankAccount['ifsc_code'] ?? '';
$bankBeneficiary = $bankAccount['account_name'] ?? $companyName;

// Trip logistics details
$startDate = !empty($invoice['start_date']) ? $invoice['start_date'] : null;
$endDate   = !empty($invoice['end_date']) ? $invoice['end_date'] : null;
$startDateFormatted = $startDate ? date('d M Y', strtotime($startDate)) : '—';
$endDateFormatted   = $endDate ? date('d M Y', strtotime($endDate)) : '—';
$tripDuration = '';
if ($startDate && $endDate) {
    try {
        $d1 = new DateTime($startDate);
        $d2 = new DateTime($endDate);
        $diffDays = $d1->diff($d2)->days + 1;
        $tripDuration = $diffDays . ' ' . ($diffDays === 1 ? 'Day' : 'Days');
    } catch (\Throwable $e) {
        $tripDuration = 'Multi-Day Trip';
    }
}

$pickupTime = !empty($invoice['pickup_time']) ? date('h:i A', strtotime($invoice['pickup_time'])) : '';
$passengers = (int)($invoice['passenger_count'] ?? 1);
$tripCat = !empty($invoice['trip_category_name']) ? $invoice['trip_category_name'] : (!empty($invoice['tour_title']) ? $invoice['tour_title'] : 'Custom Travel');

$phoneClean = preg_replace('/\D/', '', (string)($invoice['customer_phone'] ?? ''));
$waPhone = (strlen($phoneClean) === 10) ? ('91' . $phoneClean) : $phoneClean;

if ($isFullyPaid) {
    $statusLabel = 'Full Payment Received';
    $statusColor = '#218765';
    $statusBg = '#DCFCE7';
    $statusTextColor = '#15803D';
    $statusBorderColor = 'rgba(33, 135, 101, 0.45)';
} elseif ($isPartial) {
    $statusLabel = 'Partially Paid';
    $statusColor = '#C78A35';
    $statusBg = '#FEF3C7';
    $statusTextColor = '#B45309';
    $statusBorderColor = 'rgba(199, 138, 53, 0.45)';
} else {
    $statusLabel = 'Payment Pending (Pay Later)';
    $statusColor = '#DC2626';
    $statusBg = '#FEE2E2';
    $statusTextColor = '#B91C1C';
    $statusBorderColor = 'rgba(220, 38, 38, 0.45)';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Invoice &bull; <?= e($invoiceNum) ?> &bull; <?= e($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --brand-primary: #176B5B;
            --brand-primary-hover: #125748;
            --brand-secondary: #16443B;
            --brand-dark: #0D2B25;
            --brand-accent: #D6B77A;
            --brand-accent-hover: #c4a362;
            --bg-canvas: #F7F8F4;
            --surface-card: #FFFFFF;
            --surface-elevated: #F0F4F1;
            --border-light: rgba(23, 107, 91, 0.12);
            --border-medium: rgba(23, 107, 91, 0.22);
            --border-accent: rgba(214, 183, 122, 0.35);
            --text-heading: #0D2B25;
            --text-body: #20352F;
            --text-muted: #53685F;
            --text-light: #72827A;
            --shadow-subtle: 0 4px 18px rgba(13, 43, 37, 0.05);
            --shadow-card: 0 8px 24px rgba(13, 43, 37, 0.08);
            --shadow-lg: 0 16px 40px rgba(13, 43, 37, 0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            background: var(--bg-canvas);
            color: var(--text-body);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .m-topbar {
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-secondary) 100%);
            border-bottom: 2px solid var(--brand-accent);
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(13, 43, 37, 0.25);
        }
        .m-topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .m-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            min-width: 0;
        }
        .m-brand-logo {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: #FFFFFF;
            padding: 3px;
            object-fit: contain;
            border: 1px solid var(--border-accent);
            flex-shrink: 0;
        }
        .m-brand-text {
            min-width: 0;
        }
        .m-brand-name {
            font-size: 1rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.2px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .m-brand-badge {
            font-size: 0.7rem;
            color: var(--brand-accent);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 1px;
        }
        .m-topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .mbtn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .mbtn-primary {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-hover) 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(23, 107, 91, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .mbtn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(23, 107, 91, 0.45);
            color: #FFFFFF;
        }
        .mbtn-outline {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .mbtn-outline:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
        }

        .m-main-layout {
            max-width: 1200px;
            margin: 24px auto 40px;
            padding: 0 16px;
            display: grid;
            grid-template-columns: minmax(0, 750px) 390px;
            gap: 24px;
            align-items: start;
        }
        @media screen and (max-width: 1024px) {
            .m-main-layout {
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 16px;
            }
        }

        .m-card {
            background: var(--surface-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-light);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .m-card-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(23, 107, 91, 0.05), rgba(22, 68, 59, 0.02));
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .m-card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .m-card-body {
            padding: 20px;
        }

        .m-hero-card {
            background: linear-gradient(135deg, var(--brand-secondary) 0%, var(--brand-dark) 100%);
            border-radius: var(--radius-xl);
            color: #FFFFFF;
            padding: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-accent);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .m-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }
        .m-hero-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--brand-accent);
            font-weight: 800;
        }
        .m-hero-amount {
            font-size: 2.2rem;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: -0.5px;
            margin-top: 4px;
            font-family: 'JetBrains Mono', monospace;
        }
        .m-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .info-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media screen and (max-width: 640px) {
            .info-grid-2 { grid-template-columns: 1fr; }
        }
        .info-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: var(--radius-md);
            padding: 14px;
        }
        .info-box-title {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--brand-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .m-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        .m-table th {
            background: #F1F5F9;
            color: #475569;
            padding: 9px 14px;
            text-align: left;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .m-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
            vertical-align: middle;
        }

        /* Route Banner Component */
        .route-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #ECFDF5 0%, #EFF6FF 100%);
            border: 1px solid #C7D2FE;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
        }
        .route-hub {
            display: flex;
            flex-direction: column;
        }
        .route-hub-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748B;
            letter-spacing: 0.5px;
        }
        .route-hub-name {
            font-size: 1rem;
            font-weight: 800;
            color: #0F172A;
            text-transform: capitalize;
        }
        .route-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: var(--brand-primary);
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        /* Settlement Reassurance Notice */
        .pay-notice-box {
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .pay-notice-unpaid {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .pay-notice-partial {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            color: #92400E;
        }
        .pay-notice-paid {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }
        .pay-notice-icon {
            font-size: 1.25rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
        .pay-notice-title {
            font-size: 0.85rem;
            font-weight: 800;
            margin-bottom: 3px;
        }
        .pay-notice-desc {
            font-size: 0.78rem;
            line-height: 1.45;
            opacity: 0.95;
        }

        /* Summary breakdown box */
        .summary-metric-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        @media screen and (max-width: 580px) {
            .summary-metric-grid {
                grid-template-columns: 1fr;
            }
        }
        .summary-metric-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: var(--radius-md);
            padding: 12px 14px;
            text-align: center;
        }
        .summary-metric-label {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748B;
            letter-spacing: 0.5px;
        }
        .summary-metric-value {
            font-size: 1.15rem;
            font-weight: 800;
            margin-top: 3px;
            font-family: 'JetBrains Mono', monospace;
        }

        .m-qr-box {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: var(--radius-md);
        }
        .qr-canvas-holder {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
        }
        .qr-canvas-holder canvas, .qr-canvas-holder img {
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            padding: 4px;
            background: #FFFFFF;
        }
    </style>
</head>
<body>

<!-- Topbar Navigation -->
<header class="m-topbar">
    <div class="m-topbar-inner">
        <a href="<?= url('/office/invoices') ?>" class="m-brand" title="<?= e(__('tooltip.back_to_invoices', 'Back to Invoices')) ?>">
            <?php if (!empty($logoUrl)): ?>
                <img src="<?= e($logoUrl) ?>" class="m-brand-logo" alt="Logo">
            <?php endif; ?>
            <div class="m-brand-text">
                <div class="m-brand-name"><?= e($companyName) ?></div>
                <div class="m-brand-badge"><i class="fa-solid fa-certificate"></i> Digital Tax Invoice Verification</div>
            </div>
        </a>
        <div class="m-topbar-actions">
            <a href="<?= $printUrl ?>" target="_blank" class="mbtn mbtn-primary" title="<?= e(__('tooltip.print_official_a4_invoice_slip', 'Print Official A4 Invoice Slip')) ?>">
                <i class="fa-solid fa-print"></i> <span class="mbtn-text">Print A4 Invoice</span>
            </a>
            <button type="button" class="mbtn mbtn-outline" onclick="shareSlip()" title="<?= e(__('tooltip.share_digital_slip', 'Share Digital Slip')) ?>">
                <i class="fa-solid fa-share-nodes"></i>
            </button>
        </div>
    </div>
</header>

<!-- Main Layout Container -->
<main class="m-main-layout">
    <!-- Left Column: Primary Verified Record -->
    <div class="m-col-main">
        <!-- Hero Card -->
        <div class="m-hero-card">
            <div class="m-hero-top">
                <div>
                    <div class="m-hero-label">Verified Tax Invoice &bull; <?= e($invoiceNum) ?></div>
                    <div class="m-hero-amount">₹<?= number_format($totalAmt, 2) ?></div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.85); margin-top: 4px; font-style: italic;">
                        <?= e($amtInWords) ?>
                    </div>
                </div>
                <div>
                    <span class="m-hero-badge" style="background:<?= $statusBg ?>; color:<?= $statusTextColor ?>; border:1px solid <?= $statusBorderColor ?>;">
                        <i class="fa-solid fa-circle-check"></i> <?= e($statusLabel) ?>
                    </span>
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:16px; padding-top:12px; border-top:1px solid rgba(255,255,255,0.15); font-size:0.8rem;">
                <div>Collections Received: <strong style="color:#34D399; font-family:'JetBrains Mono',monospace;">₹<?= number_format($paidAmt, 2) ?></strong></div>
                <div>Balance Outstanding: <strong style="color:<?= $balanceAmt <= 0.009 ? '#34D399' : '#FCA5A5' ?>; font-family:'JetBrains Mono',monospace;">₹<?= number_format($balanceAmt, 2) ?></strong></div>
                <div>Issued: <strong><?= e($issuedDate) ?></strong> &bull; Due: <strong><?= e($dueDate) ?></strong></div>
            </div>
        </div>

        <!-- Section 1: Customer Details (Billed To) -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title">
                    <i class="fa-solid fa-user-shield" style="color:var(--brand-primary);"></i>
                    Customer Details (Billed To)
                </div>
                <span style="font-size:0.75rem; font-weight:700; background:#E8F4F0; color:#176B5B; padding:3px 8px; border-radius:4px;">
                    <i class="fa-solid fa-id-card"></i> <?= e($invoice['customer_code'] ?? 'CUS-001') ?>
                </span>
            </div>
            <div class="m-card-body">
                <div class="info-grid-2">
                    <div class="info-box">
                        <div class="info-box-title"><i class="fa-solid fa-user"></i> Primary Account Contact</div>
                        <div style="font-size:1.05rem; font-weight:800; color:#0F172A;"><?= e($invoice['customer_name'] ?? 'Valued Customer') ?></div>
                        <div style="font-size:0.78rem; color:#64748B; margin-top:3px;">
                            Account Code: <strong style="font-family:'JetBrains Mono',monospace; color:var(--brand-primary);"><?= e($invoice['customer_code'] ?? 'CUS-001') ?></strong>
                        </div>
                        <div style="font-size:0.82rem; color:#334155; margin-top:8px; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-phone" style="color:#94a3b8; width:16px;"></i>
                            <?php if (!empty($invoice['customer_phone'])): ?>
                                <a href="tel:<?= e($invoice['customer_phone']) ?>" style="color:#0F172A; font-weight:700; text-decoration:none;">
                                    <?= e($invoice['customer_phone']) ?>
                                </a>
                                <?php if ($waPhone): ?>
                                    <a href="https://wa.me/<?= e($waPhone) ?>" target="_blank" rel="noopener" style="font-size:0.7rem; background:#DCFCE7; color:#15803D; padding:2px 6px; border-radius:4px; text-decoration:none; font-weight:700;">
                                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#94A3B8;">No Phone on File</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:0.82rem; color:#334155; margin-top:4px; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-envelope" style="color:#94a3b8; width:16px;"></i>
                            <?php if (!empty($invoice['customer_email'])): ?>
                                <a href="mailto:<?= e($invoice['customer_email']) ?>" style="color:var(--brand-primary); font-weight:600; text-decoration:none;">
                                    <?= e($invoice['customer_email']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#94A3B8;">No Email on File</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-title"><i class="fa-solid fa-location-dot"></i> Billing Address</div>
                        <div style="font-size:0.85rem; color:#334155; line-height:1.5;">
                            <?= nl2br(e($invoice['customer_address'] ?? 'Official Billing Address on Record with Travel Office')) ?>
                        </div>
                        <div style="font-size:0.75rem; color:#64748B; margin-top:8px; padding-top:6px; border-top:1px dashed #CBD5E1;">
                            <i class="fa-solid fa-shield-halved" style="color:#10B981;"></i> Verified Registered Account &bull; Identity Compliant
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Trip Details & Fleet Logistics -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title">
                    <i class="fa-solid fa-route" style="color:var(--brand-primary);"></i>
                    Trip Itinerary &amp; Fleet Logistics
                </div>
                <span style="font-size:0.75rem; font-weight:700; background:#E0E7FF; color:#3730A3; padding:3px 9px; border-radius:4px;">
                    <i class="fa-solid fa-bookmark"></i> <?= e($tripCat) ?>
                </span>
            </div>
            <div class="m-card-body">
                <!-- Route banner -->
                <div class="route-banner">
                    <div class="route-hub">
                        <span class="route-hub-label"><i class="fa-solid fa-circle-dot" style="color:#10B981;"></i> Pickup Hub</span>
                        <span class="route-hub-name"><?= e($invoice['pickup_address'] ?? 'Origin') ?></span>
                    </div>
                    <div class="route-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                    <div class="route-hub">
                        <span class="route-hub-label"><i class="fa-solid fa-location-pin" style="color:#EF4444;"></i> Drop Hub</span>
                        <span class="route-hub-name"><?= e($invoice['drop_address'] ?? 'Destination') ?></span>
                    </div>
                </div>

                <div class="info-grid-2">
                    <div class="info-box">
                        <div class="info-box-title"><i class="fa-solid fa-calendar-days"></i> Timetable &amp; Schedule</div>
                        <div style="font-size:0.82rem; margin-bottom:4px;">
                            Booking Ref: <strong style="font-family:'JetBrains Mono',monospace; color:var(--brand-primary);"><?= e($invoice['booking_code'] ?? '—') ?></strong>
                        </div>
                        <div style="font-size:0.82rem; margin-bottom:4px;">
                            Travel Dates: <strong><?= e($startDateFormatted) ?></strong> &mdash; <strong><?= e($endDateFormatted) ?></strong>
                            <?php if ($tripDuration): ?>
                                <span style="font-size:0.75rem; font-weight:700; color:#475569; background:#E2E8F0; padding:1px 6px; border-radius:4px; margin-left:4px;"><?= e($tripDuration) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:0.82rem; margin-bottom:4px;">
                            Pickup Time: <strong><?= e($pickupTime ?: 'As Agreed / On Call') ?></strong>
                        </div>
                        <div style="font-size:0.82rem;">
                            Passengers: <strong><?= $passengers ?></strong> Headcount / Guest(s)
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-title"><i class="fa-solid fa-van-shuttle"></i> Assigned Fleet &amp; Driver</div>
                        <div style="font-size:0.82rem; margin-bottom:4px;">
                            Vehicle: <strong><?= e(!empty($invoice['vehicle_number']) ? ($invoice['vehicle_number'] . ' (' . ($invoice['vehicle_model'] ?: 'Fleet Vehicle') . ')') : 'Assigned Luxury Commercial Fleet (Allocated prior to departure)') ?></strong>
                        </div>
                        <div style="font-size:0.82rem; margin-bottom:4px;">
                            Driver: <strong><?= e(!empty($invoice['driver_name']) ? ($invoice['driver_name'] . (!empty($invoice['driver_phone']) ? ' (' . $invoice['driver_phone'] . ')' : '')) : 'Professional Uniformed Driver (Allocated upon fleet dispatch)') ?></strong>
                        </div>
                        <div style="font-size:0.82rem; color:#64748B; margin-top:6px; font-size:0.75rem;">
                            <i class="fa-solid fa-circle-info"></i> GPS tracking &amp; verified driver documents active on this itinerary.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Trip Charges & Fare Calculation -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title">
                    <i class="fa-solid fa-list-check" style="color:var(--brand-primary);"></i>
                    Trip Charges &amp; Fare Calculation
                </div>
                <span style="font-size:0.75rem; font-weight:700; color:#64748B;">
                    <?= !empty($invoice['billing_items']) ? count($invoice['billing_items']) . ' Itemized Charge(s)' : 'Standard Tariff' ?>
                </span>
            </div>
            <div class="m-card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table class="m-table">
                        <thead>
                            <tr>
                                <th>Particulars &amp; Service Description</th>
                                <th style="text-align:center; width:110px;"><?= e(__('booking.quantity', 'Quantity')) ?></th>
                                <th style="text-align:right; width:120px;">Rate (₹)</th>
                                <th style="text-align:right; width:135px;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($invoice['billing_items'])): ?>
                                <?php foreach ($invoice['billing_items'] as $bItem): ?>
                                    <?php
                                        $cat = strtolower(trim((string)($bItem['charge_category'] ?? 'other')));
                                        $catBadge = '';
                                        if ($cat === 'distance') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#E8F4F0;color:#176B5B;font-size:10px;font-weight:700;margin-left:6px;border:1px solid rgba(23,107,91,0.2);">Distance</span>';
                                        } elseif ($cat === 'daily') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EFF6FF;color:#1E40AF;font-size:10px;font-weight:700;margin-left:6px;border:1px solid rgba(30,64,175,0.2);">Daily Fleet</span>';
                                        } elseif ($cat === 'halting') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#F1F5F9;color:#475569;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">Halting</span>';
                                        } elseif ($cat === 'parking') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#FEF3C7;color:#92400E;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #fde68a;">Parking</span>';
                                        } elseif ($cat === 'toll') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#FEF2F2;color:#991B1B;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #fecaca;">Toll Fee</span>';
                                        } elseif ($cat === 'permit') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#F3E8FF;color:#6B21A8;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #e9d5ff;">Permit</span>';
                                        } elseif ($cat === 'extra_driver' || $cat === 'driver') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#ECFEFF;color:#0E7490;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cffafe;">Extra Driver</span>';
                                        } elseif ($cat === 'other' || $cat === 'extra') {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EEF2F6;color:#334155;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">Extra Charge</span>';
                                        } elseif ($cat) {
                                            $catBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EEF2F6;color:#334155;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">' . e(ucfirst($cat)) . '</span>';
                                        }
                                        $qNum = (float)($bItem['quantity'] ?? 1);
                                        $unitStr = trim((string)($bItem['unit'] ?? ''));
                                        $qtyLabel = ($qNum > 0) ? ((floor($qNum) == $qNum ? (int)$qNum : number_format($qNum, 2)) . ($unitStr ? ' ' . $unitStr : '')) : '—';
                                        $rateLabel = (isset($bItem['unit_rate']) && $bItem['unit_rate'] !== null && $bItem['unit_rate'] !== '') ? ('₹' . number_format((float)$bItem['unit_rate'], 2)) : '—';
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight:700; color:#0F172A; font-size:0.88rem;">
                                                <?= e($bItem['description'] ?? 'Trip Service Charge') ?> <?= $catBadge ?>
                                            </div>
                                        </td>
                                        <td style="text-align:center; color:#475569; font-weight:600;"><?= e($qtyLabel) ?></td>
                                        <td style="text-align:right; color:#475569; font-weight:600; font-family:'JetBrains Mono',monospace;"><?= e($rateLabel) ?></td>
                                        <td style="text-align:right; font-weight:700; color:#0F172A; font-size:0.9rem; font-family:'JetBrains Mono',monospace;">₹<?= number_format((float)($bItem['line_total'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#0F172A; font-size:0.88rem;">Commercial Fleet Tour Hire &bull; <?= e($tripCat) ?></div>
                                        <div style="font-size:0.75rem; color:#64748B; margin-top:2px;">
                                            Itinerary from <?= e($invoice['pickup_address'] ?? 'Origin') ?> to <?= e($invoice['drop_address'] ?? 'Destination') ?> &bull; Inclusive of vehicle fuel &amp; driver allowance
                                        </div>
                                    </td>
                                    <td style="text-align:center; color:#475569; font-weight:600;">1 Trip</td>
                                    <td style="text-align:right; color:#475569; font-weight:600; font-family:'JetBrains Mono',monospace;">₹<?= number_format((float)($invoice['subtotal'] ?? $totalAmt), 2) ?></td>
                                    <td style="text-align:right; font-weight:700; color:#0F172A; font-family:'JetBrains Mono',monospace;">₹<?= number_format((float)($invoice['subtotal'] ?? $totalAmt), 2) ?></td>
                                </tr>
                            <?php endif; ?>

                            <!-- Subtotal Row -->
                            <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                                <td colspan="3" style="font-weight:700; color:#334155; text-align:right;">Trip Charges Subtotal:</td>
                                <td style="text-align:right; font-weight:700; color:#0F172A; font-family:'JetBrains Mono',monospace;">
                                    ₹<?= number_format((float)($invoice['subtotal'] ?? $totalAmt), 2) ?>
                                </td>
                            </tr>

                            <!-- Taxes / GST Row -->
                            <?php if ((float)($invoice['tax_amount'] ?? 0) > 0): ?>
                            <tr style="background:#F8FAFC;">
                                <td colspan="3" style="text-align:right; color:#475569;">
                                    <strong>Goods &amp; Services Tax (GST @ <?= number_format((float)($invoice['tax_rate'] ?? 0), 2) ?>%):</strong>
                                </td>
                                <td style="text-align:right; font-weight:600; color:#475569; font-family:'JetBrains Mono',monospace;">
                                    ₹<?= number_format((float)$invoice['tax_amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <!-- Promotional Discount Row -->
                            <?php if ((float)($invoice['discount_amount'] ?? 0) > 0): ?>
                            <tr style="background:#F8FAFC;">
                                <td colspan="3" style="text-align:right; color:#15803D;">
                                    <strong>Special Promotional Discount:</strong>
                                </td>
                                <td style="text-align:right; font-weight:700; color:#15803D; font-family:'JetBrains Mono',monospace;">
                                    - ₹<?= number_format((float)$invoice['discount_amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <!-- Grand Total Row -->
                            <tr style="background:linear-gradient(135deg, rgba(23,107,91,0.08), rgba(22,68,59,0.04)); border-top:2px solid var(--brand-primary); border-bottom:2px solid var(--brand-primary);">
                                <td colspan="3" style="font-size:0.95rem; font-weight:800; color:var(--brand-primary); text-align:right; text-transform:uppercase;">
                                    Grand Total (Total Bill Amount):
                                </td>
                                <td style="text-align:right; font-size:1.15rem; font-weight:900; color:var(--brand-primary); font-family:'JetBrains Mono',monospace;">
                                    ₹<?= number_format($totalAmt, 2) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 4: Payment Details & Settlement Terms -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title">
                    <i class="fa-solid fa-credit-card" style="color:var(--brand-primary);"></i>
                    Payment Details &amp; Settlement Terms
                </div>
                <span style="font-size:0.75rem; font-weight:800; background:<?= $statusBg ?>; color:<?= $statusTextColor ?>; padding:3px 10px; border-radius:12px; border:1px solid <?= $statusBorderColor ?>;">
                    <?= e($statusLabel) ?>
                </span>
            </div>
            <div class="m-card-body">
                <!-- Summary 3-metric tiles -->
                <div class="summary-metric-grid">
                    <div class="summary-metric-card">
                        <div class="summary-metric-label">Total Bill</div>
                        <div class="summary-metric-value" style="color:var(--brand-primary);">₹<?= number_format($totalAmt, 2) ?></div>
                    </div>
                    <div class="summary-metric-card">
                        <div class="summary-metric-label">Collections Received</div>
                        <div class="summary-metric-value" style="color:#10B981;">₹<?= number_format($paidAmt, 2) ?></div>
                    </div>
                    <div class="summary-metric-card">
                        <div class="summary-metric-label">Remaining Balance Due</div>
                        <div class="summary-metric-value" style="color:<?= $balanceAmt <= 0.009 ? '#10B981' : '#DC2626' ?>;">
                            <?= $balanceAmt <= 0.009 ? '₹0.00 (Settled)' : '₹' . number_format($balanceAmt, 2) ?>
                        </div>
                    </div>
                </div>

                <!-- Reassurance / Payment Status Explanation -->
                <?php if ($balanceAmt <= 0.009 || $isFullyPaid): ?>
                    <div class="pay-notice-box pay-notice-paid">
                        <div class="pay-notice-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div>
                            <div class="pay-notice-title">Invoice Settled in Full</div>
                            <div class="pay-notice-desc">All charges amounting to ₹<?= number_format($totalAmt, 2) ?> have been fully paid and reconciled. No balance outstanding. Thank you for choosing <?= e($companyName) ?>!</div>
                        </div>
                    </div>
                <?php elseif ($isPartial): ?>
                    <div class="pay-notice-box pay-notice-partial">
                        <div class="pay-notice-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div>
                            <div class="pay-notice-title">Partially Paid &mdash; Balance Due: ₹<?= number_format($balanceAmt, 2) ?></div>
                            <div class="pay-notice-desc">Advance collection of ₹<?= number_format($paidAmt, 2) ?> has been verified. The remaining balance of ₹<?= number_format($balanceAmt, 2) ?> is payable to the driver or office upon arrival or trip conclusion.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="pay-notice-box pay-notice-unpaid">
                        <div class="pay-notice-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div>
                            <div class="pay-notice-title">Advance Payment Skipped &mdash; Payment To Be Collected Later</div>
                            <div class="pay-notice-desc">
                                Zero advance was collected during booking creation. The customer will pay the full balance of <strong>₹<?= number_format($balanceAmt, 2) ?></strong> via Cash, UPI, Card, or Bank Transfer during or at the conclusion of the journey.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Payments Received Ledger (if any payments exist) -->
                <?php if (!empty($payments)): ?>
                    <div style="margin-top:14px;">
                        <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#475569; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-receipt"></i> Recorded Collections &amp; Payment Receipts (<?= count($payments) ?>)
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="m-table">
                                <thead>
                                    <tr>
                                        <th><?= e(__('table.payment_ref', 'Payment Ref')) ?></th>
                                        <th><?= e(__('table.date', 'Date')) ?></th>
                                        <th>Payment Mode &amp; Reference</th>
                                        <th style="text-align:right;">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td style="font-weight:700; font-family:'JetBrains Mono',monospace; color:#0F172A;"><?= e($p['payment_reference']) ?></td>
                                        <td style="color:#475569;"><?= !empty($p['paid_at']) ? date('d M Y, h:i A', strtotime($p['paid_at'])) : '—' ?></td>
                                        <td>
                                            <span style="display:inline-block; padding:2px 7px; border-radius:4px; background:#F1F5F9; font-weight:700; font-size:0.75rem; color:#334155;">
                                                <?= strtoupper(str_replace('_', ' ', $p['payment_method'] ?? 'cash')) ?>
                                            </span>
                                            <?php if (!empty($p['transaction_id'])): ?>
                                                <span style="font-size:0.75rem; color:#64748B; margin-left:4px;">&bull; Ref: <?= e($p['transaction_id']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right; font-weight:700; color:#10B981; font-family:'JetBrains Mono',monospace;">
                                            ₹<?= number_format((float)$p['amount'], 2) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Verification, Remittance & Official Actions -->
    <div class="m-col-side">
        <!-- Print & Share Actions Card -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title"><i class="fa-solid fa-print" style="color:var(--brand-primary);"></i> Official Documents</div>
            </div>
            <div class="m-card-body">
                <a href="<?= $printUrl ?>" target="_blank" class="mbtn mbtn-primary" style="width:100%; justify-content:center; padding:11px; margin-bottom:9px;">
                    <i class="fa-solid fa-file-arrow-down"></i> Print Official A4 Invoice
                </a>
                <button type="button" class="mbtn mbtn-outline" style="width:100%; justify-content:center; padding:9px; color:var(--text-heading); border-color:#CBD5E1; margin-bottom:8px;" onclick="copySlipLink()">
                    <i class="fa-solid fa-copy"></i> <span id="copyBtnText">Copy Verification Link</span>
                </button>
                <div style="font-size:0.72rem; color:#64748B; text-align:center; margin-top:4px;">
                    Official computerized tax invoice &bull; Standard corporate layout
                </div>
            </div>
        </div>

        <!-- Digital QR Verification Card -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title"><i class="fa-solid fa-qrcode" style="color:var(--brand-primary);"></i> Authenticity QR Code</div>
            </div>
            <div class="m-card-body">
                <div class="m-qr-box">
                    <div class="qr-canvas-holder" id="verifyQrCanvas"></div>
                    <div style="font-size:0.85rem; font-weight:800; color:#0F172A;">Certified Authentic Slip</div>
                    <div style="font-size:0.75rem; color:#64748B; margin-top:2px;">Scan with smartphone camera to view digital live status</div>
                    <div style="margin-top:8px;">
                        <a href="<?= $printUrl ?>" target="_blank" style="font-size:0.75rem; font-weight:700; color:var(--brand-primary); text-decoration:none;">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Printable A4 Slip
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company / Billing Issuer Card -->
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title"><i class="fa-solid fa-building" style="color:var(--brand-primary);"></i> Billing Issuer</div>
            </div>
            <div class="m-card-body" style="font-size:0.82rem; line-height:1.5;">
                <div style="font-weight:800; color:#0F172A; font-size:0.95rem;"><?= e($companyName) ?></div>
                <div style="color:#64748B; font-size:0.75rem;"><?= e($tagline) ?></div>
                <div style="margin-top:8px; color:#475569;"><?= e($address) ?></div>
                <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #CBD5E1;">
                    <div>GSTIN: <strong style="font-family:'JetBrains Mono',monospace;"><?= e($gstin ?: 'On File') ?></strong></div>
                    <div>PAN: <strong style="font-family:'JetBrains Mono',monospace;"><?= e($pan ?: 'On File') ?></strong></div>
                    <div>Phone: <a href="tel:<?= e($phone) ?>" style="color:#0F172A; text-decoration:none; font-weight:600;"><?= e($phone) ?></a></div>
                    <div>Email: <a href="mailto:<?= e($email) ?>" style="color:var(--brand-primary); text-decoration:none; font-weight:600;"><?= e($email) ?></a></div>
                </div>
            </div>
        </div>

        <!-- Bank Remittance Details Card -->
        <?php if (!empty($bankAccount)): ?>
        <div class="m-card">
            <div class="m-card-header">
                <div class="m-card-title"><i class="fa-solid fa-building-columns" style="color:var(--brand-primary);"></i> Bank Remittance</div>
            </div>
            <div class="m-card-body" style="font-size:0.82rem; line-height:1.55;">
                <div style="color:#64748B; font-size:0.75rem; margin-bottom:6px;">Official corporate bank details for RTGS / NEFT / IMPS transfers:</div>
                <div>Bank Name: <strong><?= e($bankAccountName) ?></strong></div>
                <div>A/C Number: <strong style="font-family:'JetBrains Mono',monospace; color:var(--brand-primary); font-size:0.9rem;"><?= e($bankAccNumber) ?></strong></div>
                <div>IFSC Code: <strong style="font-family:'JetBrains Mono',monospace;"><?= e($bankIfsc) ?></strong></div>
                <div>Beneficiary: <strong><?= e($bankBeneficiary) ?></strong></div>
                <div style="font-size:0.72rem; color:#64748B; margin-top:8px; padding-top:6px; border-top:1px dashed #CBD5E1;">
                    Please reference Bill No <strong><?= e($invoiceNum) ?></strong> in your bank remittance remarks.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="<?= asset('assets/js/qrcode.min.js') ?>"></script>
<script>
    if (typeof QRCode === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrEl = document.getElementById('verifyQrCanvas');
        if (typeof QRCode !== 'undefined' && qrEl) {
            new QRCode(qrEl, {
                text: window.location.href,
                width: 120,
                height: 120,
                colorDark: '#0D2B25',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });

    function copySlipLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            const btn = document.getElementById('copyBtnText');
            if (btn) {
                const orig = btn.textContent;
                btn.textContent = 'Link Copied!';
                setTimeout(() => { btn.textContent = orig; }, 2000);
            }
        }).catch(() => {
            alert('Verification link: ' + window.location.href);
        });
    }

    function shareSlip() {
        if (navigator.share) {
            navigator.share({
                title: 'Official Tax Invoice - <?= e($invoiceNum) ?>',
                text: 'Digital Tax Invoice for <?= e($invoice['customer_name'] ?? 'Customer') ?> from <?= e($companyName) ?>',
                url: window.location.href
            }).catch(() => {});
        } else {
            copySlipLink();
        }
    }
</script>
</body>
</html>
