<?php
/**
 * Tours & Travels — Official Public Digital Receipt Verification View
 * Master Brand Theme: Forest Teal (#176B5B) + Deep Forest (#16443B) + Champagne Gold (#D6B77A) + Warm Ivory (#F7F8F4)
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

$isReversed = ($receipt['status'] ?? '') === 'reversed';
$amountPaid = (float)($receipt['amount'] ?? 0);
$totalTripAmount = $receipt['trip_amount_snapshot'] !== null ? (float)$receipt['trip_amount_snapshot'] : (float)($receipt['driver_trip_amount'] ?? $amountPaid);
$previouslyPaid = (float)($receipt['previous_paid_amount'] ?? 0);
$totalPaidToDate = (float)($receipt['total_paid_after'] ?? ($previouslyPaid + $amountPaid));
$remainingDue = (float)($receipt['remaining_after'] ?? max(0.0, $totalTripAmount - $totalPaidToDate));
$isFullyPaid = (!$isReversed && $remainingDue <= 0.009);

$receiptNum = $receipt['receipt_number'] ?: ($receipt['payment_reference'] ?? ('DPR-' . date('Y') . '-' . str_pad((string)$receipt['id'], 5, '0', STR_PAD_LEFT)));
$paymentDate = !empty($receipt['payment_date']) ? date('d M Y', strtotime($receipt['payment_date'])) : date('d M Y');
$amtInWords = convertAmountToWordsINR($amountPaid);

$companyName = company_display_name();
$tagline = company_tagline() ?: 'Premium Commercial Travel & Logistics';
$logoUrl = company_logo_url();
$address = company_full_address() ?: company_address();
$phone = company_phone();
$altPhone = company_alternate_phone();
$email = company_email();
$gstin = company_gst_number();
$pan = company_pan_number();
$paidStamp = company_paid_stamp_url();

$methodLabels = [
    'cash'          => 'Cash Payment',
    'bank_transfer' => 'Bank Transfer (IMPS/NEFT)',
    'neft'          => 'NEFT / RTGS Transfer',
    'upi'           => 'UPI Instant Transfer',
    'card'          => 'Credit / Debit Card',
    'cheque'        => 'Bank Cheque'
];
$methodIcons = [
    'cash'          => 'fa-money-bill-wave',
    'bank_transfer' => 'fa-building-columns',
    'neft'          => 'fa-building-columns',
    'upi'           => 'fa-mobile-screen-button',
    'card'          => 'fa-credit-card',
    'cheque'        => 'fa-money-check'
];
$methodLabel = $methodLabels[$receipt['payment_method']] ?? ucfirst(str_replace('_', ' ', $receipt['payment_method'] ?? 'Bank Transfer'));
$methodIcon = $methodIcons[$receipt['payment_method']] ?? 'fa-wallet';

// Payment Progress Calculation
$progress = $totalTripAmount > 0 ? min(100, round(($totalPaidToDate / $totalTripAmount) * 100)) : 100;
if ($isReversed) {
    $statusLabel = 'Payment Reversed';
    $statusColor = '#D95757';
    $statusBg = '#FEE2E2';
    $statusTextColor = '#F87171';
    $statusBorderColor = 'rgba(217, 87, 87, 0.45)';
    $statusBgColor = 'rgba(217, 87, 87, 0.2)';
} elseif ($isFullyPaid) {
    $statusLabel = 'Trip Fully Settled';
    $statusColor = '#218765';
    $statusBg = '#EAF2ED';
    $statusTextColor = '#D6B77A';
    $statusBorderColor = 'rgba(214, 183, 122, 0.5)';
    $statusBgColor = 'rgba(33, 135, 101, 0.25)';
} else {
    $statusLabel = 'Partial Payment';
    $statusColor = '#C78A35';
    $statusBg = '#FEF3C7';
    $statusTextColor = '#FCD34D';
    $statusBorderColor = 'rgba(199, 138, 53, 0.45)';
    $statusBgColor = 'rgba(199, 138, 53, 0.2)';
}

$printUrl = url('/receipt/verify/' . (int)$receipt['id'] . '/print');
$allVouchersList = !empty($allVouchers) ? $allVouchers : [$receipt];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Receipt &bull; <?= e($receiptNum) ?> &bull; <?= e($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            /* Tours & Travels Master Brand Tokens */
            --brand-primary: #176B5B;
            --brand-primary-hover: #125748;
            --brand-secondary: #16443B;
            --brand-dark: #0D2B25;
            --brand-accent: #D6B77A;
            --brand-accent-light: #FBF7EE;
            --accent: #176B5B;
            --accent2: #16443B;
            --light: #EAF2ED;
            --mid: #C8DDD3;
            --dark: #0D2B25;
            --green: #218765;
            --amber: #C78A35;
            --red: #D95757;
            --text: #1A2E28;
            --muted: #53685F;
            --border: #E2E8F0;
            --white: #FFFFFF;
            --bg: #F7F8F4;
            --gold: #D6B77A;
            --gold-border: rgba(214, 183, 122, 0.45);
            --shadow-sm: 0 2px 10px rgba(22, 68, 59, 0.04);
            --shadow-card: 0 12px 36px rgba(22, 68, 59, 0.08);
            --shadow-lg: 0 20px 50px rgba(22, 68, 59, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F7F8F4;
            background-image: 
                radial-gradient(at 0% 0%, rgba(23, 107, 91, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(214, 183, 122, 0.06) 0px, transparent 50%);
            min-height: 100vh;
            padding: 0 0 70px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── HERO BANNER (Executive Brand Mesh Glow) ── */
        .hero {
            background: linear-gradient(145deg, #0D2B25 0%, #16443B 38%, #176B5B 80%, #125748 100%);
            padding: 42px 20px 84px;
            text-align: center;
            position: relative;
            overflow: hidden;
            color: #FFFFFF;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 85% 15%, rgba(214, 183, 122, 0.28) 0%, transparent 45%),
                radial-gradient(circle at 15% 65%, rgba(33, 135, 101, 0.35) 0%, transparent 50%),
                radial-gradient(circle at 50% 100%, rgba(13, 43, 37, 0.6) 0%, transparent 60%);
            pointer-events: none;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
            opacity: 0.65;
        }

        /* Hero Pill Row */
        .hero-top-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 22px;
            position: relative;
            z-index: 2;
            flex-wrap: wrap;
        }
        .hero-verified {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(214, 183, 122, 0.45);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 8px 20px;
            font-size: 11.5px;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }
        .hero-verified i {
            font-size: 14px;
            color: #D6B77A; /* Champagne Gold checkmark */
        }
        .hero-secure-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(23, 107, 91, 0.35);
            border: 1px solid rgba(214, 183, 122, 0.35);
            border-radius: 30px;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #EAF2ED;
            letter-spacing: 0.5px;
        }
        .hero-secure-pill i {
            font-size: 12px;
            color: #D6B77A;
        }

        /* Amount Block */
        .hero-amount-wrap {
            position: relative;
            z-index: 2;
            margin-bottom: 6px;
        }
        .hero-currency-sup {
            font-size: 30px;
            font-weight: 700;
            color: #D6B77A; /* Champagne Gold Symbol */
            vertical-align: super;
            line-height: 0;
            margin-right: 4px;
        }
        .hero-amount {
            font-size: 60px;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: -2px;
            line-height: 1;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: inline;
            font-family: 'JetBrains Mono', monospace;
        }
        .hero-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.82);
            margin-top: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            position: relative;
            z-index: 2;
            font-weight: 600;
        }

        /* Divider Dots */
        .hero-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 18px 0;
            position: relative;
            z-index: 2;
        }
        .hero-divider span {
            display: block;
            height: 1px;
            width: 48px;
            background: linear-gradient(90deg, transparent, rgba(214, 183, 122, 0.5), transparent);
        }
        .hero-divider i {
            font-size: 8px;
            color: #D6B77A;
        }

        /* Meta Pills Row */
        .hero-meta-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }
        .hero-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #FFFFFF;
        }
        .hero-meta-pill i {
            font-size: 12px;
            color: #D6B77A;
        }
        .hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 12px;
            font-weight: 700;
            border: 1.5px solid;
            backdrop-filter: blur(8px);
        }
        .hero-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse-dot 1.8s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.4); }
        }

        /* ── CARD CONTAINER & STRUCTURE ── */
        .card-container {
            max-width: 620px;
            margin: -36px auto 0;
            padding: 0 16px;
            position: relative;
            z-index: 10;
        }
        .verify-sheet {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(22, 68, 59, 0.14);
        }

        /* ── COMPANY LETTERHEAD (Full Width Header) ── */
        .co-header {
            background: linear-gradient(135deg, #FBF7EE 0%, #EAF2ED 100%);
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            border-bottom: 2px solid var(--mid);
            flex-wrap: wrap;
        }
        .co-brand-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 280px;
        }
        .co-logo {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            object-fit: contain;
            background: #FFFFFF;
            border: 1.5px solid var(--mid);
            padding: 4px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(22, 68, 59, 0.08);
        }
        .co-logo-placeholder {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #FFFFFF;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(22, 68, 59, 0.15);
        }
        .co-details {
            min-width: 0;
            flex: 1;
        }
        .co-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--brand-secondary);
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .co-sub {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 3px;
            line-height: 1.4;
        }
        .co-meta-tax {
            display: flex;
            gap: 12px;
            font-size: 10.5px;
            color: var(--muted);
            margin-top: 4px;
            flex-wrap: wrap;
        }
        .co-meta-tax strong {
            color: var(--brand-secondary);
        }
        .rec-badge {
            text-align: right;
            flex-shrink: 0;
        }
        .rec-badge-title {
            font-size: 9.5px;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .rec-badge-no {
            font-size: 13px;
            font-weight: 800;
            color: var(--brand-secondary);
            background: #FFFFFF;
            border: 1.5px solid var(--mid);
            border-radius: 8px;
            padding: 5px 12px;
            display: inline-block;
            letter-spacing: 0.5px;
            font-family: 'JetBrains Mono', monospace;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .rec-badge-date {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
        }
        .rec-badge-date strong {
            color: var(--text);
        }

        /* Accent Line */
        .accent-line {
            height: 3.5px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent), var(--brand-secondary));
        }

        /* ── SECTION COMMON STYLING ── */
        .verify-section {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
        }
        .verify-section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--brand-secondary);
            letter-spacing: 1.8px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
        }
        .section-title i {
            color: var(--brand-primary);
            font-size: 13px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: var(--mid);
        }

        /* ── 4-BOX STAT KPI GRID (Full Width, Zero Crushing) ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .stat-card {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 16px 18px;
            border: 1px solid var(--border);
            border-left: 4.5px solid var(--accent);
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(22, 68, 59, 0.08);
        }
        .stat-card.emerald { border-left-color: var(--brand-primary); }
        .stat-card.green   { border-left-color: var(--green); }
        .stat-card.amber   { border-left-color: var(--amber); }
        .stat-card.red     { border-left-color: var(--red); }
        
        .stat-label {
            font-size: 10.5px;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.1;
            font-family: 'JetBrains Mono', monospace;
            word-break: break-all;
        }
        .stat-value.emerald { color: var(--brand-primary); }
        .stat-value.green   { color: var(--green); }
        .stat-value.amber   { color: var(--amber); }
        .stat-value.red     { color: var(--red); }
        .stat-sub {
            font-size: 10px;
            color: var(--muted);
            margin-top: 5px;
            font-weight: 600;
        }

        /* Progress Bar */
        .progress-wrap {
            margin-top: 18px;
            background: #FAFBF9;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        .progress-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 700;
        }
        .progress-pct {
            color: var(--brand-primary);
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
        }
        .progress-bar-bg {
            height: 9px;
            background: #E2E8F0;
            border-radius: 99px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent));
            transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ── PANELS & GRIDS ── */
        .d-grid-2, .d-grid-breakdown-stamp {
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 0 24px 24px;
        }
        .verify-panel {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
        }
        .timeline-panel {
            margin: 0 24px 24px;
        }

        /* ── INFO ROWS (High Contrast, No Text Clipping) ── */
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
        }
        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            background: var(--light);
            border: 1px solid var(--mid);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: var(--brand-primary);
            flex-shrink: 0;
        }
        .info-content {
            flex: 1;
            min-width: 0;
        }
        .info-key {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 3px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .info-val {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.4;
            word-break: break-word;
        }
        .info-val.val-mono {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.3px;
        }
        .info-val.text-brand {
            color: var(--brand-primary);
        }
        .info-sub-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--light);
            color: var(--brand-secondary);
            font-size: 11.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            margin-top: 4px;
            border: 1px solid var(--mid);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        /* ── PAYMENT TABLE & WORDS ── */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .pay-table thead tr {
            background: var(--light);
            border-bottom: 1.5px solid var(--mid);
        }
        .pay-table th {
            padding: 12px 16px;
            color: var(--brand-secondary);
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .pay-table th:last-child { text-align: right; }
        .pay-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            font-size: 13.5px;
        }
        .pay-table td:last-child {
            text-align: right;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
        }
        .pay-table tr:last-child td { border-bottom: none; }
        .pay-table tr.alt td { background: #FAFBF9; }
        
        .pay-item-title {
            font-weight: 800;
            color: var(--text);
            font-size: 14px;
        }
        .pay-item-meta {
            display: flex;
            gap: 12px;
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 4px;
            flex-wrap: wrap;
        }
        .pay-item-val {
            font-size: 16px;
        }
        .text-green { color: var(--green); }
        .text-red   { color: var(--red); }

        /* Amount in Words Callout */
        .amt-words-box {
            background: #FBF7EE;
            border: 1px dashed var(--gold);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .amt-words-label {
            font-size: 10px;
            font-weight: 800;
            color: var(--amber);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .amt-words-text {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--brand-secondary);
            font-style: italic;
            line-height: 1.4;
        }

        /* Total Box */
        .total-box {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            border-radius: 14px;
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            color: #FFFFFF;
            box-shadow: 0 8px 24px rgba(22, 68, 59, 0.16);
            border: 1px solid rgba(214, 183, 122, 0.35);
        }
        .total-box-label {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 3px;
        }
        .total-box-val {
            font-size: 24px;
            font-weight: 900;
            color: #FFFFFF;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: -0.5px;
        }
        .total-box-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.72);
            margin-top: 3px;
        }
        .total-box-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #D6B77A; /* Champagne Gold Icon */
            flex-shrink: 0;
        }

        /* ── OFFICIAL STAMP & CERTIFICATION BOX ── */
        .stamp-panel {
            justify-content: space-between;
        }
        .stamp-box-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 18px;
            background: #FBF7EE;
            border-radius: 14px;
            border: 1.5px dashed var(--gold);
            text-align: center;
            box-shadow: 0 4px 16px rgba(214, 183, 122, 0.08);
            position: relative;
            flex: 1;
            margin-bottom: 16px;
        }
        .stamp-img {
            max-width: 155px;
            max-height: 110px;
            object-fit: contain;
            filter: drop-shadow(0 3px 8px rgba(0,0,0,0.1));
            transition: transform 0.2s ease;
        }
        .stamp-img:hover {
            transform: scale(1.03);
        }
        .stamp-verified-label {
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-secondary);
            text-transform: uppercase;
            margin-top: 10px;
            letter-spacing: 0.6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .stamp-verified-label i {
            color: var(--brand-primary);
        }
        .stamp-verified-meta {
            font-size: 9.5px;
            color: var(--muted);
            margin-top: 3px;
        }
        .reversed-seal {
            border: 2px dashed #DC2626;
            color: #DC2626;
            font-size: 13.5px;
            font-weight: 900;
            padding: 9px 18px;
            border-radius: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: #FEF2F2;
            transform: rotate(-3deg);
            box-shadow: 0 2px 8px rgba(220,38,38,0.1);
        }
        .reversed-sub {
            font-size: 10px;
            font-weight: 700;
            color: #DC2626;
            text-transform: uppercase;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }
        .generic-cert-badge {
            font-size: 11px;
            font-weight: 800;
            color: var(--brand-primary);
            border: 1.5px solid var(--mid);
            padding: 9px 16px;
            border-radius: 8px;
            text-transform: uppercase;
            background: #FFFFFF;
        }

        /* Desktop Action Buttons Inside Stamp Panel */
        .desktop-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ── TIMELINE ── */
        .timeline {
            position: relative;
            padding-left: 28px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--mid);
        }
        .tl-item {
            position: relative;
            margin-bottom: 14px;
        }
        .tl-item:last-child { margin-bottom: 0; }
        .tl-dot {
            position: absolute;
            left: -23px;
            top: 12px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border);
            border: 2px solid var(--mid);
        }
        .tl-dot.current {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.25);
        }
        .tl-dot.done {
            background: var(--brand-secondary);
            border-color: var(--brand-secondary);
        }
        .tl-dot.reversed {
            background: var(--red);
            border-color: var(--red);
        }
        .tl-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--border);
            transition: all 0.15s ease;
        }
        .tl-card.current {
            background: var(--light);
            border-left-color: var(--brand-primary);
            border-color: var(--mid);
            box-shadow: 0 3px 12px rgba(23, 107, 91, 0.12);
        }
        .tl-card.done {
            border-left-color: var(--brand-secondary);
        }
        .tl-card.reversed {
            background: #FFF1F2;
            border-left-color: var(--red);
            opacity: 0.9;
        }
        .tl-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .tl-header-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .tl-inst {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .tl-amt {
            font-size: 16px;
            font-weight: 800;
            color: var(--text);
            font-family: 'JetBrains Mono', monospace;
        }
        .tl-amt.current { color: var(--brand-primary); }
        .tl-amt.done { color: var(--brand-secondary); }
        .tl-amt.reversed { color: var(--red); text-decoration: line-through; }
        .tl-meta {
            font-size: 12px;
            color: var(--muted);
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        .tl-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tl-badge.current {
            background: var(--brand-primary);
            color: #FFFFFF;
        }
        .tl-badge.reversed {
            background: #FEE2E2;
            color: #B91C1C;
        }

        /* ── FOOTER ── */
        .rec-footer {
            background: linear-gradient(135deg, #FBF7EE 0%, #EAF2ED 100%);
            border-top: 2px solid var(--mid);
            padding: 22px 28px;
            text-align: center;
        }
        .rec-footer-thank {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--brand-secondary);
            margin-bottom: 5px;
        }
        .rec-footer-sub {
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.55;
        }
        .rec-footer-date {
            font-size: 11px;
            color: var(--muted);
            margin-top: 8px;
            font-weight: 600;
        }
        .rec-footer-date i {
            color: var(--brand-primary);
        }

        /* ── BUTTONS ── */
        .mbtn {
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .mbtn-primary {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(22, 68, 59, 0.2);
        }
        .mbtn-primary:hover {
            background: linear-gradient(135deg, var(--brand-primary-hover) 0%, var(--brand-dark) 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(22, 68, 59, 0.25);
            color: #FFFFFF;
        }
        .mbtn-outline {
            background: #FFFFFF;
            color: var(--brand-secondary);
            border: 1.5px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .mbtn-outline:hover {
            background: var(--light);
            border-color: var(--mid);
            color: var(--brand-primary);
        }

        /* ── MOBILE STICKY ACTION BAR ── */
        .mobile-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
            box-shadow: 0 -4px 24px rgba(22, 68, 59, 0.12);
            display: flex;
            gap: 10px;
            z-index: 100;
            border-top: 1px solid var(--border);
        }
        .mobile-action-bar .mbtn-primary {
            flex: 1;
        }
        .mobile-action-bar .mbtn-outline {
            max-width: 125px;
        }

        /* ── LARGE SCREEN DESKTOP SPLIT LAYOUT (@media min-width: 1024px) ── */
        @media (min-width: 1024px) {
            body { padding-bottom: 80px; }
            .hero { padding: 54px 40px 110px; }
            .hero-amount { font-size: 70px; }
            .hero-currency-sup { font-size: 34px; }
            .card-container {
                max-width: 1160px;
                margin: -58px auto 0;
                padding: 0 24px;
            }
            .verify-sheet {
                border-radius: 24px;
            }
            .stat-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }
            .stat-card {
                padding: 18px 20px;
            }
            .stat-value {
                font-size: 22px;
            }
            .d-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 24px;
                padding: 0 28px 28px;
            }
            .d-grid-breakdown-stamp {
                display: grid;
                grid-template-columns: 1.15fr 0.85fr;
                gap: 24px;
                padding: 0 28px 28px;
                align-items: stretch;
            }
            .timeline-panel {
                margin: 0 28px 28px;
                padding: 28px;
            }
            .verify-section {
                padding: 28px;
            }
            .mobile-action-bar {
                display: none;
            }
        }

        /* ── EXTRA SMALL MOBILE DEVICES (@media max-width: 420px) ── */
        @media (max-width: 420px) {
            .hero { padding: 30px 14px 64px; }
            .hero-amount { font-size: 38px; }
            .hero-currency-sup { font-size: 20px; }
            .hero-top-row { gap: 6px; }
            .hero-verified, .hero-secure-pill { font-size: 10px; padding: 6px 12px; }
            .hero-meta-pill, .hero-status-pill { font-size: 10.5px; padding: 5px 10px; }
            .stat-grid { grid-template-columns: 1fr; }
            .co-header { flex-direction: column; align-items: flex-start; padding: 18px; }
            .rec-badge { text-align: left; width: 100%; margin-top: 10px; }
            .verify-section, .verify-panel, .timeline-panel { padding: 18px 16px; }
            .d-grid-2, .d-grid-breakdown-stamp { padding: 0 16px 18px; }
            .total-box { flex-direction: column; align-items: flex-start; gap: 12px; }
            .total-box-icon { align-self: flex-end; margin-top: -34px; }
            .info-row { gap: 10px; }
            .info-icon { width: 34px; height: 34px; font-size: 13px; }
        }
    </style>
</head>
<body>

    <!-- HERO BAND (Matches Tours & Travels Master Brand) -->
    <div class="hero">
        <!-- Top Pills -->
        <div class="hero-top-row">
            <div class="hero-verified">
                <i class="fa-solid fa-circle-check"></i> Official Digital Receipt Verified
            </div>
            <div class="hero-secure-pill">
                <i class="fa-solid fa-shield-halved"></i> Secure &amp; Authentic
            </div>
        </div>

        <!-- Amount -->
        <div class="hero-amount-wrap">
            <span class="hero-currency-sup">₹</span><span class="hero-amount"><?= number_format($amountPaid, 2) ?></span>
        </div>
        <div class="hero-label">Remuneration Disbursed &nbsp;&bull;&nbsp; <?= e($paymentDate) ?></div>

        <!-- Divider -->
        <div class="hero-divider">
            <span></span><i class="fa-solid fa-circle"></i><span></span>
        </div>

        <!-- Meta Pills Row -->
        <div class="hero-meta-row">
            <div class="hero-meta-pill">
                <i class="fa-solid fa-receipt"></i> <?= e($receiptNum) ?>
            </div>
            <div class="hero-meta-pill">
                <i class="fa-regular fa-calendar"></i> <?= e($paymentDate) ?>
            </div>
            <div class="hero-status-pill" style="background: <?= $statusBgColor ?>; border-color: <?= $statusBorderColor ?>; color: <?= $statusTextColor ?>;">
                <span class="hero-status-dot" style="background: <?= $statusColor ?>;"></span>
                <?= e($statusLabel) ?>
            </div>
        </div>
    </div>

    <!-- MAIN CARD CONTAINER (Desktop 2-Col Balanced Layout, Mobile Single Stream) -->
    <div class="card-container">
        <div class="verify-sheet">
            
            <!-- 1. OFFICIAL COMPANY LETTERHEAD (Full Width Header) -->
            <div class="co-header">
                <div class="co-brand-wrap">
                    <?php if (!empty($logoUrl)): ?>
                        <img src="<?= e($logoUrl) ?>" class="co-logo" alt="Logo">
                    <?php else: ?>
                        <div class="co-logo-placeholder"><i class="fa-solid fa-van-shuttle"></i></div>
                    <?php endif; ?>
                    <div class="co-details">
                        <div class="co-name"><?= e($companyName) ?></div>
                        <div class="co-sub"><?= e($tagline) ?></div>
                        <div class="co-meta-tax">
                            <?php if (!empty($gstin)): ?><span>GSTIN: <strong><?= e($gstin) ?></strong></span><?php endif; ?>
                            <?php if (!empty($pan)): ?><span>PAN: <strong><?= e($pan) ?></strong></span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="rec-badge">
                    <div class="rec-badge-title">OFFICIAL PAYMENT VOUCHER</div>
                    <div class="rec-badge-no"><?= e($receiptNum) ?></div>
                    <div class="rec-badge-date">Date: <strong><?= e($paymentDate) ?></strong></div>
                </div>
            </div>

            <div class="accent-line"></div>

            <!-- 2. FINANCIAL SETTLEMENT KPI OVERVIEW (Full Width 4-Box Grid) -->
            <div class="verify-section">
                <div class="section-title"><i class="fa-solid fa-chart-pie"></i> Financial Settlement Overview</div>
                <div class="stat-grid">
                    <div class="stat-card emerald">
                        <div class="stat-label">Trip Agreed Fee</div>
                        <div class="stat-value emerald">₹<?= number_format($totalTripAmount, 2) ?></div>
                        <div class="stat-sub">Remuneration</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-label">Total Disbursed</div>
                        <div class="stat-value green">₹<?= number_format($totalPaidToDate, 2) ?></div>
                        <div class="stat-sub">Cumulative Paid</div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-label">This Voucher</div>
                        <div class="stat-value amber">₹<?= number_format($amountPaid, 2) ?></div>
                        <div class="stat-sub"><?= e($paymentDate) ?></div>
                    </div>
                    <div class="stat-card <?= $remainingDue <= 0.009 ? 'green' : 'red' ?>">
                        <div class="stat-label">Remaining Balance</div>
                        <div class="stat-value <?= $remainingDue <= 0.009 ? 'green' : 'red' ?>">₹<?= number_format($remainingDue, 2) ?></div>
                        <div class="stat-sub"><?= $remainingDue <= 0.009 ? 'Fully Settled' : 'Still to Pay' ?></div>
                    </div>
                </div>

                <!-- Settlement Progress Bar -->
                <div class="progress-wrap">
                    <div class="progress-meta">
                        <span><i class="fa-solid fa-percent"></i> Settlement Progress</span>
                        <span class="progress-pct"><?= $progress ?>% Settled</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $progress ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- 3. SIDE-BY-SIDE DOSSIERS: DRIVER & TRIP | RECEIPT & TREASURY -->
            <div class="d-grid-2">
                
                <!-- Driver & Trip Dossier -->
                <div class="verify-panel">
                    <div class="section-title"><i class="fa-solid fa-car-side"></i> Driver &amp; Trip Details</div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="info-content">
                            <div class="info-key">Driver Name</div>
                            <div class="info-val"><?= e($receipt['driver_name']) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-id-badge"></i></div>
                        <div class="info-content">
                            <div class="info-key">Employee / Driver Code</div>
                            <div class="info-val"><?= !empty($receipt['employee_code']) ? e($receipt['employee_code']) : ('DRV-' . str_pad((string)$receipt['driver_id'], 4, '0', STR_PAD_LEFT)) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-id-card"></i></div>
                        <div class="info-content">
                            <div class="info-key">Driving License Number</div>
                            <div class="info-val val-mono"><?= e($receipt['license_number'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-van-shuttle"></i></div>
                        <div class="info-content">
                            <div class="info-key">Assigned Commercial Vehicle</div>
                            <div class="info-val"><?= e($receipt['vehicle_number'] ?? 'Vehicle N/A') ?> <?= !empty($receipt['make_model']) ? '(' . e($receipt['make_model']) . ')' : '' ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-route"></i></div>
                        <div class="info-content">
                            <div class="info-key">Trip Route &amp; Dispatch Reference</div>
                            <div class="info-val">
                                <?= e($receipt['pickup_address'] ?? 'Start') ?> &rarr; <?= e($receipt['drop_address'] ?? 'End') ?>
                                <div><span class="info-sub-badge"><i class="fa-solid fa-ticket"></i> <?= e($receipt['dispatch_code'] ?: ('#DSP-' . $receipt['dispatch_id'])) ?></span></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($receipt['booking_code'])): ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-bookmark"></i></div>
                        <div class="info-content">
                            <div class="info-key">Booking Reference</div>
                            <div class="info-val val-mono"><?= e($receipt['booking_code']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Receipt & Treasury Details -->
                <div class="verify-panel">
                    <div class="section-title"><i class="fa-solid fa-file-invoice-dollar"></i> Receipt &amp; Treasury Details</div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-hashtag"></i></div>
                        <div class="info-content">
                            <div class="info-key">Receipt Reference Number</div>
                            <div class="info-val val-mono text-brand"><?= e($receiptNum) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-regular fa-calendar-check"></i></div>
                        <div class="info-content">
                            <div class="info-key">Disbursement Date &amp; Time</div>
                            <div class="info-val"><?= e($paymentDate) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid <?= $methodIcon ?>"></i></div>
                        <div class="info-content">
                            <div class="info-key">Payment Channel / Mode</div>
                            <div class="info-val"><?= e($methodLabel) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($receipt['account_name'])): ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-vault"></i></div>
                        <div class="info-content">
                            <div class="info-key">Disbursed From Treasury Vault</div>
                            <div class="info-val"><?= e($receipt['account_name']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($receipt['transaction_ref'])): ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-fingerprint"></i></div>
                        <div class="info-content">
                            <div class="info-key">Bank Reference / UTR Number</div>
                            <div class="info-val val-mono"><?= e($receipt['transaction_ref']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-solid fa-shield-halved" style="color: <?= $statusColor ?>;"></i></div>
                        <div class="info-content">
                            <div class="info-key">Settlement Status</div>
                            <div class="info-val">
                                <span class="status-badge" style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusBorderColor ?>;">
                                    <i class="fa-solid fa-circle-dot"></i> <?= e($statusLabel) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 4. SIDE-BY-SIDE: BREAKDOWN & WORDS | OFFICIAL COMPANY PAID STAMP -->
            <div class="d-grid-breakdown-stamp">
                
                <!-- Left: Payment Breakdown & Words -->
                <div class="verify-panel">
                    <div class="section-title"><i class="fa-solid fa-list-check"></i> Payment Breakdown &amp; Certification</div>
                    <table class="pay-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Disbursement Item</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="pay-item-title">Current Voucher Disbursement</div>
                                    <div class="pay-item-meta">
                                        <span><i class="fa-regular fa-calendar"></i> <?= e($paymentDate) ?></span>
                                        <span><i class="fa-solid <?= $methodIcon ?>"></i> <?= e($methodLabel) ?></span>
                                    </div>
                                </td>
                                <td class="pay-item-val <?= $isReversed ? 'text-red' : 'text-green' ?>">
                                    ₹<?= number_format($amountPaid, 2) ?>
                                </td>
                            </tr>
                            <tr class="alt">
                                <td colspan="2">
                                    <div class="amt-words-box">
                                        <div class="amt-words-label"><i class="fa-solid fa-spell-check"></i> Amount in Words (Indian Currency)</div>
                                        <div class="amt-words-text"><?= e($amtInWords) ?></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Total Box -->
                    <div class="total-box">
                        <div>
                            <div class="total-box-label">Remaining Balance ("Still to Pay")</div>
                            <div class="total-box-val">₹<?= number_format($remainingDue, 2) ?></div>
                            <div class="total-box-sub">Total disbursed: ₹<?= number_format($totalPaidToDate, 2) ?> of ₹<?= number_format($totalTripAmount, 2) ?></div>
                        </div>
                        <div class="total-box-icon">
                            <?php if ($remainingDue <= 0.009): ?>
                                <i class="fa-solid fa-circle-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-hourglass-half"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Official Company Certification & PAID Stamp -->
                <div class="verify-panel stamp-panel">
                    <div class="section-title"><i class="fa-solid fa-certificate"></i> Certification &amp; Official Seal</div>
                    
                    <div class="stamp-box-container">
                        <?php if ($isReversed): ?>
                            <div class="reversed-seal">
                                <i class="fa-solid fa-ban"></i> TRANSACTION REVERSED
                            </div>
                            <div class="reversed-sub">
                                Debited Treasury Balance Restored &bull; Reversal Verified
                            </div>
                        <?php elseif (!empty($paidStamp)): ?>
                            <img src="<?= e($paidStamp) ?>" class="stamp-img" alt="Official Company PAID Stamp">
                            <div class="stamp-verified-label">
                                <i class="fa-solid fa-circle-check"></i> Official Company Payment Stamp
                            </div>
                            <div class="stamp-verified-meta">
                                Verified Commercial Disbursement &bull; Tours &amp; Travels
                            </div>
                        <?php else: ?>
                            <div class="generic-cert-badge">
                                <i class="fa-solid fa-stamp"></i> Certified Commercial Settlement
                            </div>
                            <div class="stamp-verified-meta">
                                Authorized Payment Voucher Recorded
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Desktop Actions Inside Panel -->
                    <div class="desktop-actions" id="desktopActionSection">
                        <a href="<?= $printUrl ?>" target="_blank" class="mbtn mbtn-primary">
                            <i class="fa-solid fa-print"></i> Print Official A4 Voucher
                        </a>
                        <button type="button" class="mbtn mbtn-outline" onclick="copyReceiptLink()">
                            <i class="fa-regular fa-copy"></i> <span class="deskCopyBtnText">Copy Digital Verification Link</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- 5. PAYMENT HISTORY TIMELINE (Full Width) -->
            <div class="verify-panel timeline-panel">
                <div class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Payment History Timeline (All Vouchers for this Dispatch)</div>
                <div class="timeline">
                    <?php
                    $revAll = array_reverse($allVouchersList);
                    foreach ($revAll as $idx => $v):
                        $isCur = ((int)$v['id'] === (int)$receipt['id']);
                        $vRev = (($v['status'] ?? '') === 'reversed');
                        $vDate = !empty($v['payment_date']) ? date('d M Y', strtotime($v['payment_date'])) : 'N/A';
                        $vAmt = number_format((float)$v['amount'], 2);
                        $vMethod = $methodLabels[$v['payment_method']] ?? ucfirst(str_replace('_', ' ', $v['payment_method'] ?? 'Bank Transfer'));
                        $vRef = $v['receipt_number'] ?: ($v['payment_reference'] ?? ('#VCH-' . $v['id']));
                        
                        $dotCls = $vRev ? 'reversed' : ($isCur ? 'current' : 'done');
                        $cardCls = $vRev ? 'reversed' : ($isCur ? 'current' : 'done');
                        $amtCls = $vRev ? 'reversed' : ($isCur ? 'current' : 'done');
                    ?>
                    <div class="tl-item">
                        <div class="tl-dot <?= $dotCls ?>"></div>
                        <div class="tl-card <?= $cardCls ?>">
                            <div class="tl-top">
                                <div class="tl-header-left">
                                    <span class="tl-inst">Voucher #<?= $idx + 1 ?> &bull; <?= e($vRef) ?></span>
                                    <?php if ($isCur): ?>
                                        <span class="tl-badge current"><i class="fa-solid fa-check"></i> This Receipt</span>
                                    <?php endif; ?>
                                    <?php if ($vRev): ?>
                                        <span class="tl-badge reversed"><i class="fa-solid fa-rotate-left"></i> Reversed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="tl-amt <?= $amtCls ?>">₹<?= $vAmt ?></div>
                            </div>
                            <div class="tl-meta">
                                <span><i class="fa-regular fa-calendar"></i> <?= $vDate ?></span>
                                <span><i class="fa-solid fa-wallet"></i> <?= e($vMethod) ?></span>
                                <?php if (!empty($v['account_name'])): ?>
                                    <span><i class="fa-solid fa-vault"></i> <?= e($v['account_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 6. OFFICIAL FOOTER -->
            <div class="rec-footer">
                <div class="rec-footer-thank">🙏 Thank you for your professional service and dedication!</div>
                <div class="rec-footer-sub">
                    <?= e($companyName) ?> &bull; <?= e($address) ?>
                    <?php if (!empty($phone)): ?> &bull; Phone: <?= e($phone) ?><?php endif; ?>
                    <?php if (!empty($email)): ?> &bull; <?= e($email) ?><?php endif; ?>
                </div>
                <div class="rec-footer-date">
                    <i class="fa-solid fa-qrcode"></i> Official Digital Receipt &bull; Authenticated Online at <?= date('d M Y, h:i A') ?> IST
                </div>
            </div>

        </div>
    </div>

    <!-- MOBILE STICKY ACTION BAR (Visible on mobile screens) -->
    <div class="mobile-action-bar">
        <a href="<?= $printUrl ?>" target="_blank" class="mbtn mbtn-primary">
            <i class="fa-solid fa-print" style="color: var(--brand-accent);"></i> Print A4 Receipt
        </a>
        <button type="button" class="mbtn mbtn-outline" onclick="copyReceiptLink()" title="Copy Link">
            <i class="fa-regular fa-copy"></i> <span id="copyBtnText">Copy</span>
        </button>
    </div>

    <script>
        function copyReceiptLink() {
            var url = window.location.href;
            var handleSuccess = function() {
                var mobBtn = document.getElementById('copyBtnText');
                if (mobBtn) mobBtn.textContent = 'Copied!';
                document.querySelectorAll('.deskCopyBtnText').forEach(function(el) {
                    el.textContent = 'Copied to Clipboard!';
                });
                setTimeout(function() {
                    if (mobBtn) mobBtn.textContent = 'Copy';
                    document.querySelectorAll('.deskCopyBtnText').forEach(function(el) {
                        el.textContent = 'Copy Digital Verification Link';
                    });
                }, 2200);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(handleSuccess);
            } else {
                var t = document.createElement('input');
                t.value = url;
                document.body.appendChild(t);
                t.select();
                document.execCommand('copy');
                document.body.removeChild(t);
                handleSuccess();
            }
        }
    </script>
</body>
</html>
