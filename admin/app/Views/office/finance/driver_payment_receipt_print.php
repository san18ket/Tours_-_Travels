<?php
/**
 * TOURS & TRAVELS — OFFICIAL DRIVER TRIP PAYMENT VOUCHER & RECEIPT
 * Premium Printable & PDF-Ready A4 Document
 * Mirrors PMS print-receipt.php design with interactive themes and live QR code
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

// Payment and Trip Status Calculations
$paymentStatus = strtolower($receipt['status'] ?? 'completed');
$isReversed = ($paymentStatus === 'reversed');
$isPaymentCompleted = in_array($paymentStatus, ['completed', 'paid', 'success'], true);

$amountPaid = (float)($receipt['amount'] ?? 0);
$totalTripAmount = $receipt['trip_amount_snapshot'] !== null ? (float)$receipt['trip_amount_snapshot'] : (float)($receipt['driver_trip_amount'] ?? $amountPaid);
$previouslyPaid = (float)($receipt['previous_paid_amount'] ?? 0);
$totalPaidToDate = (float)($receipt['total_paid_after'] ?? ($previouslyPaid + $amountPaid));
$remainingDue = (float)($receipt['remaining_after'] ?? max(0.0, $totalTripAmount - $totalPaidToDate));
$isFullyPaid = (!$isReversed && $remainingDue <= 0.009);

$receiptNum = $receipt['receipt_number'] ?: ($receipt['payment_reference'] ?? ('DPR-' . date('Y') . '-' . str_pad((string)$receipt['id'], 5, '0', STR_PAD_LEFT)));
$amtInWords = convertAmountToWordsINR($amountPaid);
$paymentDate = !empty($receipt['payment_date']) ? date('d M Y', strtotime($receipt['payment_date'])) : date('d M Y');

// Calculate Installment / Voucher Number for this dispatch
$pdo = \App\Core\Database::getConnection();
$stmtInst = $pdo->prepare("SELECT COUNT(*) FROM `driver_trip_payments` WHERE `dispatch_id` = ? AND (`payment_date` < ? OR (`payment_date` = ? AND `id` <= ?))");
$stmtInst->execute([
    (int)$receipt['dispatch_id'],
    $receipt['payment_date'],
    $receipt['payment_date'],
    (int)$receipt['id']
]);
$voucherNumber = max(1, (int)$stmtInst->fetchColumn());

// Company Details
$companyName = company_display_name();
$tagline = company_tagline() ?: 'Premium Commercial Travel & Logistics';
$logoUrl = company_logo_url();
$address = company_full_address() ?: company_address();
$phone = company_phone();
$altPhone = company_alternate_phone();
$email = company_email();
$website = company_website();
$gstin = company_gst_number();
$pan = company_pan_number();

// Retrieve Existing Configured PAID Stamp
$paidStamp = company_paid_stamp_url();
$hasStamp = !empty($paidStamp) && has_company_paid_stamp();
$showStampParam = isset($show_stamp) ? (bool)$show_stamp : (($_GET['stamp'] ?? '1') !== '0');
$showPaidStamp = ($isPaymentCompleted && !$isReversed && $hasStamp && $showStampParam);

$methodLabels = [
    'cash'          => 'Cash',
    'bank_transfer' => 'Bank Transfer',
    'upi'           => 'UPI Transfer',
    'card'          => 'Credit/Debit Card',
    'cheque'        => 'Bank Cheque'
];
$methodLabel = $methodLabels[$receipt['payment_method']] ?? ucfirst(str_replace('_', ' ', $receipt['payment_method'] ?? 'Bank Transfer'));

// Resolve Digital Receipt Verification URL (works on localhost/LAN or production)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($host === 'localhost' || strpos($host, '127.') === 0 || $host === '::1'
            || preg_match('/^192\.168\./', $host) || preg_match('/^10\./', $host)
            || preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $host));
if ($isLocal) {
    $lanIp = '';
    $ipcfg = @shell_exec('ipconfig');
    if ($ipcfg) {
        preg_match_all('/IPv4[^:]*:\s*(\d+\.\d+\.\d+\.\d+)/', $ipcfg, $m);
        foreach (($m[1] ?? []) as $ip) {
            if (strpos($ip, '127.') !== 0) { $lanIp = $ip; break; }
        }
    }
    if (!$lanIp) {
        $resolved = gethostbyname(gethostname());
        if ($resolved && $resolved !== gethostname()) $lanIp = $resolved;
    }
    if ($lanIp) {
        $port = $_SERVER['SERVER_PORT'] ?? 80;
        $host = $lanIp . (($port == 80 || $port == 443) ? '' : ':' . $port);
    }
}
$receiptVerifyUrl = $scheme . '://' . $host . url('/receipt/verify/' . $receipt['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt #<?= e($receiptNum) ?> &bull; Driver Payment Voucher</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Plus Jakarta Sans',Arial,sans-serif; background:#f1f5f9; color:#1F2937; -webkit-print-color-adjust:exact!important; print-color-adjust:exact!important; }

  /* ── Interactive Toolbar (Mirrors PMS print-receipt.php) ── */
  .toolbar {
    background: linear-gradient(135deg, #1e3a5f, #0f243e);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  }
  .toolbar-main {
    display: contents; /* On desktop, children become direct flex items of .toolbar */
  }
  .toolbar-left {
    order: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }
  .toolbar-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 9px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #fff;
  }
  .toolbar-meta {
    min-width: 0;
  }
  .toolbar-title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.2px;
    line-height: 1.25;
  }
  .toolbar-sub {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.75);
    margin-top: 2px;
    line-height: 1.2;
  }
  .color-bar {
    order: 2;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
  }
  .color-label {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.7);
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
  }
  .swatches-track {
    display: flex;
    align-items: center;
    gap: 7px;
  }
  .swatch {
    width: 26px;
    height: 26px;
    min-width: 26px;
    border-radius: 50%;
    cursor: pointer;
    border: 2.5px solid transparent;
    transition: all .2s;
    box-shadow: 0 2px 5px rgba(0,0,0,.3);
  }
  .swatch:hover {
    transform: scale(1.2);
  }
  .swatch.active {
    border-color: #fff;
    transform: scale(1.15);
    box-shadow: 0 0 0 3px rgba(255,255,255,.3);
  }
  .toolbar-actions {
    order: 3;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
  }
  .tbtn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all .2s;
    white-space: nowrap;
    text-decoration: none;
  }
  .tbtn-close {
    background: rgba(255,255,255,.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,.2);
  }
  .tbtn-close:hover {
    background: rgba(255,255,255,.22);
    color: #fff;
  }
  .tbtn-print {
    background: #10B981;
    color: #fff;
  }
  .tbtn-print:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.3);
  }
  .tbtn-digital {
    background: rgba(255,255,255,.18);
    color: #fff;
    border: 1px solid rgba(255,255,255,.3);
  }
  .tbtn-digital:hover {
    background: rgba(255,255,255,.28);
    color: #fff;
  }

  /* ── Receipt paper (Mirrors PMS print-receipt.php) ── */
  .receipt-wrap {
    display: flex;
    justify-content: center;
    padding: 28px 16px 40px;
  }
  .receipt-paper {
    background: #fff;
    width: 100%;
    max-width: 860px;
    box-shadow: 0 8px 32px rgba(0,0,0,.12);
    border-radius: 10px;
    overflow: hidden;
    font-family: 'Plus Jakarta Sans', Arial, sans-serif;
    font-size: 13px;
    color: #111;
    position: relative;
  }

  /* ── Mobile Responsive Screens (@media screen and max-width: 768px) ── */
  @media screen and (max-width: 768px) {
    .toolbar {
      padding: 10px 14px;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    .toolbar-main {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      width: 100%;
    }
    .toolbar-left {
      order: unset;
      display: flex;
      align-items: center;
      gap: 9px;
      min-width: 0;
      flex: 1;
    }
    .toolbar-icon {
      width: 34px;
      height: 34px;
      min-width: 34px;
      font-size: 1rem;
      border-radius: 8px;
    }
    .toolbar-meta {
      min-width: 0;
      overflow: hidden;
    }
    .toolbar-title {
      font-size: 0.92rem;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1.25;
    }
    .toolbar-sub {
      font-size: 0.72rem;
      color: rgba(255, 255, 255, 0.75);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1.2;
      margin-top: 1px;
    }
    .toolbar-actions {
      order: unset;
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .toolbar-actions .tbtn {
      padding: 7px 11px;
      font-size: 0.8rem;
      border-radius: 7px;
      white-space: nowrap;
      gap: 5px;
    }
    .color-bar {
      order: unset;
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding-top: 7px;
      border-top: 1px solid rgba(255, 255, 255, 0.12);
      overflow-x: auto;
      overflow-y: hidden;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }
    .color-bar::-webkit-scrollbar {
      display: none;
    }
    .color-label {
      font-size: 0.72rem;
      color: rgba(255, 255, 255, 0.75);
      white-space: nowrap;
      flex-shrink: 0;
    }
    .swatches-track {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
    .swatch {
      width: 22px;
      height: 22px;
      min-width: 22px;
      flex-shrink: 0;
    }

    /* Receipt Paper Responsive Stacking */
    .receipt-wrap {
      padding: 12px 8px 30px;
    }
    .receipt-paper {
      border-radius: 8px;
      font-size: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .rc-header-wrap {
      padding: 14px 14px !important;
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 12px !important;
    }
    .rc-header-wrap img {
      max-height: 44px !important;
      width: auto !important;
    }
    .rc-header-right {
      text-align: left !important;
      width: 100% !important;
      border-top: 1px dashed rgba(0,0,0,0.08);
      padding-top: 10px;
    }
    .rc-details-wrap {
      padding: 14px 14px !important;
      flex-direction: column !important;
      gap: 16px !important;
    }
    .rc-details-right {
      min-width: 100% !important;
      text-align: left !important;
    }
    .rc-details-right table {
      margin-left: 0 !important;
      width: 100% !important;
    }
    .rc-summary-wrap {
      padding: 14px 14px 20px !important;
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 18px !important;
    }
    .rc-summary-left {
      min-width: 100% !important;
    }
    .rc-summary-mid, .rc-summary-right {
      align-self: center !important;
    }
    .rc-words-wrap {
      padding: 10px 14px !important;
    }
    .rc-notes-wrap {
      padding: 10px 14px !important;
    }
    .rc-footer-wrap {
      padding: 12px 14px !important;
      flex-direction: column !important;
      gap: 6px !important;
      text-align: center !important;
    }
  }

  /* ── Extra Small Screens (phones < 520px) ── */
  @media screen and (max-width: 520px) {
    .toolbar {
      padding: 8px 10px;
      gap: 6px;
    }
    .toolbar-actions .tbtn-digital .tbtn-text {
      display: none;
    }
    .toolbar-actions .tbtn-digital {
      padding: 6px 8px;
    }
    .toolbar-actions .tbtn {
      padding: 6px 9px;
      font-size: 0.75rem;
      gap: 4px;
    }
    .toolbar-icon {
      width: 30px;
      height: 30px;
      min-width: 30px;
      font-size: 0.9rem;
    }
    .toolbar-title {
      font-size: 0.86rem;
    }
    .toolbar-sub {
      font-size: 0.68rem;
    }
    .swatch {
      width: 20px;
      height: 20px;
      min-width: 20px;
    }
  }

  /* ── Ultra Narrow Phones (< 380px) ── */
  @media screen and (max-width: 380px) {
    .toolbar-actions .tbtn-close .tbtn-text {
      display: none;
    }
    .toolbar-actions .tbtn-close {
      padding: 6px 8px;
    }
  }

  /* ── Print styles ── */
  @media print {
    body { background:#fff !important; padding:0 !important; color:#000 !important; }
    .toolbar { display:none !important; }
    .receipt-wrap { padding:0 !important; }
    .receipt-paper { box-shadow:none !important; border-radius:0 !important; max-width:100% !important; margin:0 !important; }
    @page { size:A4 landscape; margin:10mm; }
  }
</style>
</head>
<body>

<!-- Toolbar with Theme Switcher -->
<div class="toolbar">
  <div class="toolbar-main">
    <div class="toolbar-left">
      <div class="toolbar-icon"><i class="fa-solid fa-receipt"></i></div>
      <div class="toolbar-meta">
        <div class="toolbar-title">Driver Payment Receipt</div>
        <div class="toolbar-sub"><?= e($receipt['driver_name']) ?> &mdash; <?= e($paymentDate) ?></div>
      </div>
    </div>
    <div class="toolbar-actions">
      <a href="<?= url('/receipt/verify/' . (int)$receipt['id']) ?>" class="tbtn tbtn-digital" title="View Mobile Digital Receipt"><i class="fa-solid fa-mobile-screen"></i> <span class="tbtn-text">Digital View</span></a>
      <button type="button" class="tbtn tbtn-close" onclick="window.history.length > 1 ? window.history.back() : window.close()" title="Close Receipt"><i class="fa-solid fa-xmark"></i> <span class="tbtn-text">Close</span></button>
      <button type="button" class="tbtn tbtn-print" id="printBtn" title="Print Receipt"><i class="fa-solid fa-print"></i> <span class="tbtn-text">Print</span></button>
    </div>
  </div>
  <div class="color-bar" id="colorBar">
    <span class="color-label"><i class="fa-solid fa-palette"></i> Theme:</span>
    <div class="swatches-track">
      <div class="swatch active" data-color="#10B981" style="background:#10B981;" title="Emerald"></div>
      <div class="swatch" data-color="#0EA5E9" style="background:#0EA5E9;" title="Sky Blue"></div>
      <div class="swatch" data-color="#6366F1" style="background:#6366F1;" title="Indigo"></div>
      <div class="swatch" data-color="#F59E0B" style="background:#F59E0B;" title="Amber"></div>
      <div class="swatch" data-color="#EF4444" style="background:#EF4444;" title="Red"></div>
      <div class="swatch" data-color="#8B5CF6" style="background:#8B5CF6;" title="Violet"></div>
      <div class="swatch" data-color="#1e3a5f" style="background:#1e3a5f;" title="Navy"></div>
      <div class="swatch" data-color="#064e3b" style="background:#064e3b;" title="Forest"></div>
    </div>
  </div>
</div>

<!-- Receipt Presentation Area -->
<div class="receipt-wrap">
  <div class="receipt-paper" id="receiptPaper">
    <!-- Initial server-rendered fallback so document is visible before JS runs -->
    <div class="rc-header-wrap" style="padding:22px 36px; background:#e6f9f3; display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #10B981;">
      <div style="display:flex; align-items:center; gap:14px;">
        <?php if (!empty($logoUrl)): ?>
          <img src="<?= e($logoUrl) ?>" style="height:56px; width:auto; object-fit:contain; background:rgba(255,255,255,.8); border-radius:8px; padding:4px 8px; border:1px solid #c2f0e0;" alt="Logo">
        <?php endif; ?>
        <div>
          <div style="font-size:22px; font-weight:800; color:#10B981; line-height:1.2;"><?= e($companyName) ?></div>
          <div style="font-size:11px; color:#555;"><?= e($tagline) ?></div>
        </div>
      </div>
      <div class="rc-header-right" style="text-align:right;">
        <div style="font-size:26px; font-weight:800; color:#10B981; letter-spacing:.5px; text-transform:uppercase;">Payment Receipt</div>
        <div style="font-size:11.5px; color:#666; margin-top:5px; font-weight:600;">Payment Date: <?= e($paymentDate) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden data for dynamic JS receipt builder (mirrors PMS print-receipt.php) -->
<div id="receiptData" style="display:none;"
  data-driver="<?= htmlspecialchars($receipt['driver_name'] ?? 'Driver', ENT_QUOTES) ?>"
  data-mobile="<?= htmlspecialchars($receipt['driver_phone'] ?? 'N/A', ENT_QUOTES) ?>"
  data-license="<?= htmlspecialchars($receipt['license_number'] ?? 'N/A', ENT_QUOTES) ?>"
  data-emp-code="<?= htmlspecialchars(!empty($receipt['employee_code']) ? $receipt['employee_code'] : ('DRV-' . str_pad((string)$receipt['driver_id'], 4, '0', STR_PAD_LEFT)), ENT_QUOTES) ?>"
  data-vehicle="<?= htmlspecialchars(($receipt['vehicle_number'] ?? 'Vehicle N/A') . (!empty($receipt['make_model']) ? ' (' . $receipt['make_model'] . ')' : ''), ENT_QUOTES) ?>"
  data-dispatch="<?= htmlspecialchars(($receipt['dispatch_code'] ?: ('#DSP-' . $receipt['dispatch_id'])) . ' &bull; ' . ($receipt['pickup_address'] ?? 'Start') . ' &rarr; ' . ($receipt['drop_address'] ?? 'End'), ENT_QUOTES) ?>"
  data-method="<?= htmlspecialchars($methodLabel, ENT_QUOTES) ?>"
  data-cur-amt="<?= number_format($amountPaid, 2) ?>"
  data-prev-amt="<?= number_format($previouslyPaid, 2) ?>"
  data-prev-num="<?= $previouslyPaid ?>"
  data-total-paid="<?= number_format($totalPaidToDate, 2) ?>"
  data-balance="<?= number_format($remainingDue, 2) ?>"
  data-balance-num="<?= $remainingDue ?>"
  data-date="<?= htmlspecialchars($paymentDate, ENT_QUOTES) ?>"
  data-rec-no="<?= htmlspecialchars($receiptNum, ENT_QUOTES) ?>"
  data-voucher-ref="<?= htmlspecialchars($receipt['payment_reference'], ENT_QUOTES) ?>"
  data-logo="<?= htmlspecialchars($logoUrl ?? '', ENT_QUOTES) ?>"
  data-stamp="<?= htmlspecialchars($showPaidStamp ? ($paidStamp ?? '') : '', ENT_QUOTES) ?>"
  data-co-name="<?= htmlspecialchars($companyName, ENT_QUOTES) ?>"
  data-co-address="<?= htmlspecialchars($address, ENT_QUOTES) ?>"
  data-co-phone="<?= htmlspecialchars($phone, ENT_QUOTES) ?>"
  data-co-alt-phone="<?= htmlspecialchars($altPhone ?? '', ENT_QUOTES) ?>"
  data-co-email="<?= htmlspecialchars($email, ENT_QUOTES) ?>"
  data-co-gst="<?= htmlspecialchars($gstin ?? '', ENT_QUOTES) ?>"
  data-co-pan="<?= htmlspecialchars($pan ?? '', ENT_QUOTES) ?>"
  data-install-no="<?= $voucherNumber ?>"
  data-show-stamp="<?= $showPaidStamp ? '1' : '0' ?>"
  data-is-reversed="<?= $isReversed ? '1' : '0' ?>"
  data-reversal-reason="<?= htmlspecialchars($receipt['reversal_reason'] ?? '', ENT_QUOTES) ?>"
  data-reversal-date="<?= htmlspecialchars(!empty($receipt['reversed_at']) ? date('d M Y, h:i A', strtotime($receipt['reversed_at'])) : '', ENT_QUOTES) ?>"
  data-total-trip-amt="<?= number_format($totalTripAmount, 2) ?>"
  data-account-name="<?= htmlspecialchars($receipt['account_name'] ?? 'Company Vault', ENT_QUOTES) ?>"
  data-txn-ref="<?= htmlspecialchars($receipt['transaction_ref'] ?? '', ENT_QUOTES) ?>"
  data-words="<?= htmlspecialchars($amtInWords, ENT_QUOTES) ?>"
  data-notes="<?= htmlspecialchars($receipt['notes'] ?? '', ENT_QUOTES) ?>"
  data-receipt-url="<?= htmlspecialchars($receiptVerifyUrl, ENT_QUOTES) ?>"
></div>

<!-- Hidden QR canvas container (off-screen, must not be display:none for canvas to render) -->
<div id="qrContainer" style="position:fixed;left:-9999px;top:-9999px;width:128px;height:128px;overflow:hidden;"></div>

<!-- Local QR Engine with CDN fallback -->
<script src="<?= asset('assets/js/qrcode.min.js') ?>"></script>
<script>
  if (typeof QRCode === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
  }
</script>

<script>
  let selectedColor = '#10B981'; // Emerald default (active)
  let qrDataUrl = '';

  const d = document.getElementById('receiptData').dataset;

  // Generate QR code as base64 data URL pointing to digital verification receipt
  function generateQR() {
    return new Promise(resolve => {
      const container = document.getElementById('qrContainer');
      container.innerHTML = '';
      if (typeof QRCode === 'undefined') {
        resolve('');
        return;
      }
      try {
        new QRCode(container, {
          text: d.receiptUrl,
          width: 128, height: 128,
          colorDark: '#0f172a', colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.M
        });
        let tries = 0;
        const poll = setInterval(() => {
          const cvs = container.querySelector('canvas');
          const img = container.querySelector('img');
          tries++;
          if (cvs) {
            clearInterval(poll);
            resolve(cvs.toDataURL('image/png'));
          } else if (img && img.complete && img.src && img.src.startsWith('data:')) {
            clearInterval(poll);
            resolve(img.src);
          } else if (tries > 40) {
            clearInterval(poll);
            resolve('');
          }
        }, 50);
      } catch (err) {
        resolve('');
      }
    });
  }

  function lightenHex(hex, ratio) {
    hex = hex.replace('#','');
    if (hex.length === 3) hex = hex.split('').map(x=>x+x).join('');
    const r = parseInt(hex.slice(0,2),16), g = parseInt(hex.slice(2,4),16), b = parseInt(hex.slice(4,6),16);
    const lr = Math.round(r+(255-r)*ratio), lg = Math.round(g+(255-g)*ratio), lb = Math.round(b+(255-b)*ratio);
    return '#'+[lr,lg,lb].map(v=>v.toString(16).padStart(2,'0')).join('');
  }

  // Build receipt HTML — light theme (pastel backgrounds, color as accent) matching PMS print-receipt.php
  function buildReceipt(c) {
    const headerBg = lightenHex(c, 0.86);
    const thBg     = lightenHex(c, 0.76);
    const footerBg = lightenHex(c, 0.92);

    const logoSrc   = d.logo;
    const stampSrc  = d.stamp;
    const curAmt    = d.curAmt;
    const prevAmt   = d.prevAmt;
    const prevNum   = parseFloat(d.prevNum) || 0;
    const balance   = d.balance;
    const balNum    = parseFloat(d.balanceNum) || 0;
    const rDate     = d.date;
    const recNo     = d.recNo;
    const driver    = d.driver;
    const mobile    = d.mobile;
    const license   = d.license;
    const empCode   = d.empCode;
    const vehicle   = d.vehicle;
    const dispatch  = d.dispatch;
    const method    = d.method;
    const totalPaid = d.totalPaid;
    const totalTrip = d.totalTripAmt;
    const account   = d.accountName;
    const txnRef    = d.txnRef;
    const words     = d.words;
    const notes     = d.notes;
    const isReversed= (d.isReversed === '1');
    const showStamp = (d.showStamp === '1' && !isReversed);

    const phones = [d.coPhone, d.coAltPhone].filter(Boolean).join(' / ');

    return `
      <!-- REVERSED WATERMARK -->
      ${isReversed ? `<div style="position:absolute;top:48%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:5.5rem;font-weight:900;color:rgba(220,38,38,0.08);letter-spacing:10px;text-transform:uppercase;pointer-events:none;z-index:1;white-space:nowrap;">REVERSED</div>` : ''}

      <!-- HEADER -->
      <div class="rc-header-wrap" style="background:${headerBg};padding:22px 36px;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid ${c};position:relative;z-index:2;">
        <div style="display:flex;align-items:center;gap:14px;">
          ${logoSrc ? `<img src="${logoSrc}" style="height:56px;width:auto;object-fit:contain;background:rgba(255,255,255,.85);border-radius:8px;padding:4px 8px;border:1px solid ${thBg};" alt="Logo">` : ''}
          <div>
            <div style="font-size:22px;font-weight:800;color:${c};letter-spacing:-.3px;line-height:1.2;">${d.coName}</div>
            <div style="font-size:11px;color:#555;margin-top:2px;">${d.coAddress} ${d.coGst ? '&bull; GSTIN: <strong>' + d.coGst + '</strong>' : ''}</div>
          </div>
        </div>
        <div class="rc-header-right" style="text-align:right;">
          <div style="font-size:26px;font-weight:800;color:${c};letter-spacing:.5px;text-transform:uppercase;">Payment Receipt</div>
          <div style="font-size:11.5px;color:#666;margin-top:4px;letter-spacing:.4px;font-weight:600;">Payment Date: <strong>${rDate}</strong></div>
          <div style="margin-top:4px;">
            ${isReversed 
              ? `<span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#FEE2E2;color:#B91C1C;">PAYMENT REVERSED</span>`
              : (balNum <= 0.009
                  ? `<span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#DCFCE7;color:#15803D;">TRIP FULLY SETTLED</span>`
                  : `<span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#FEF3C7;color:#B45309;">PARTIAL PAYMENT</span>`
                )
            }
          </div>
        </div>
      </div>

      <!-- ACCENT LINE -->
      <div style="height:4px;background:linear-gradient(90deg,${c},${thBg},${c});"></div>

      <!-- REVERSAL CALLOUT IF REVERSED -->
      ${isReversed ? `
        <div style="background:#FFF1F2;border:1.5px dashed #F87171;border-radius:8px;padding:10px 16px;margin:16px 36px 0;font-size:12px;color:#991B1B;position:relative;z-index:2;">
          <strong><i class="fa-solid fa-triangle-exclamation"></i> Transaction Cancelled &amp; Reversed</strong>
          ${d.reversalDate ? `on ${d.reversalDate}` : ''}. ${d.reversalReason ? `Reason: <em>${d.reversalReason}</em>.` : ''} Debited treasury balance and driver remaining payable have been completely restored.
        </div>
      ` : ''}

      <!-- DETAILS: DRIVER / DRIVER DETAILS + PAYMENT DETAILS -->
      <div class="rc-details-wrap" style="padding:22px 36px 18px;display:flex;justify-content:space-between;gap:30px;border-bottom:1px solid #e5e7eb;position:relative;z-index:2;">
        <!-- Left: Driver Details -->
        <div class="rc-details-left" style="flex:1;">
          <div style="font-size:10px;font-weight:700;color:${c};letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;border-bottom:2px solid ${thBg};padding-bottom:4px;">Driver Details</div>
          <table style="font-size:12px;border-collapse:collapse;width:100%;">
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;width:34%;">Driver Name</td><td style="color:#222;padding:2px 0;font-weight:700;font-size:13px;">: ${driver}</td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">Employee Code</td><td style="color:#444;padding:2px 0;">: ${empCode}</td></tr>
            ${mobile && mobile !== 'N/A' ? `<tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">Mobile</td><td style="color:#444;padding:2px 0;">: ${mobile}</td></tr>` : ''}
            ${license && license !== 'N/A' ? `<tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">License</td><td style="color:#444;padding:2px 0;font-family:monospace;">: ${license}</td></tr>` : ''}
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">Vehicle</td><td style="color:#444;padding:2px 0;font-weight:600;">: ${vehicle}</td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">Trip / Route</td><td style="color:#444;padding:2px 0;">: ${dispatch}</td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;white-space:nowrap;">Total Trip Fee</td><td style="color:#444;padding:2px 0;font-weight:700;">: &#8377;${totalTrip}</td></tr>
          </table>
        </div>

        <!-- Right: Payment Details -->
        <div class="rc-details-right" style="min-width:240px;text-align:right;">
          <div style="font-size:10px;font-weight:700;color:${c};letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;border-bottom:2px solid ${thBg};padding-bottom:4px;">Payment Details</div>
          <table style="margin-left:auto;font-size:12px;border-collapse:collapse;">
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Receipt No.</td><td style="color:#222;padding:2px 0;font-weight:700;font-family:monospace;">: ${recNo}</td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Voucher #</td><td style="color:#444;padding:2px 0;">: <span style="background:${thBg};color:${c};padding:2px 8px;border-radius:12px;font-weight:700;">#${d.installNo}</span></td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Payment Date</td><td style="color:#444;padding:2px 0;">: ${rDate}</td></tr>
            <tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Payment Mode</td><td style="color:#444;padding:2px 0;">: ${method}</td></tr>
            ${account ? `<tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Treasury</td><td style="color:#444;padding:2px 0;">: ${account}</td></tr>` : ''}
            ${txnRef ? `<tr><td style="color:#888;font-weight:600;padding:2px 10px 2px 0;text-align:left;white-space:nowrap;">Ref / UTR</td><td style="color:#444;padding:2px 0;font-family:monospace;">: ${txnRef}</td></tr>` : ''}
          </table>
        </div>
      </div>

      <!-- SUMMARY + QR CODE + STAMP (Mirrors PMS print-receipt.php) -->
      <div class="rc-summary-wrap" style="padding:16px 36px 24px;display:flex;justify-content:space-between;align-items:center;gap:24px;position:relative;z-index:2;">
        <!-- LEFT: Financial Summary Table -->
        <div class="rc-summary-left" style="flex:1;min-width:250px;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
              <td style="padding:8px 12px;color:#555;font-weight:600;">Current Payment</td>
              <td style="padding:8px 12px;text-align:right;font-weight:700;color:${isReversed ? '#DC2626' : '#15803D'};font-size:14px;font-family:'JetBrains Mono',monospace;">&#8377;${curAmt}</td>
            </tr>
            ${prevNum > 0 ? `
              <tr style="border-bottom:1px solid #e5e7eb;background:#f9fafb;">
                <td style="padding:8px 12px;color:#555;font-weight:500;">Previously Paid</td>
                <td style="padding:8px 12px;text-align:right;font-weight:600;color:#555;font-family:'JetBrains Mono',monospace;">&#8377;${prevAmt}</td>
              </tr>
            ` : ''}
            <tr style="border-bottom:1px solid #e5e7eb;">
              <td style="padding:8px 12px;color:#555;font-weight:500;">Total Paid to Date</td>
              <td style="padding:8px 12px;text-align:right;font-weight:700;color:#111;font-family:'JetBrains Mono',monospace;">&#8377;${totalPaid}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;font-weight:800;background:${thBg};color:${c};border-radius:4px 0 0 4px;border-top:2px solid ${c};">Still to Pay (Remaining Balance)</td>
              <td style="padding:10px 12px;font-weight:900;font-size:14px;background:${thBg};color:${c};text-align:right;border-radius:0 4px 4px 0;border-top:2px solid ${c};font-family:'JetBrains Mono',monospace;">&#8377;${balance}</td>
            </tr>
          </table>

          <!-- Amount In Words -->
          <div class="rc-words-wrap" style="margin-top:10px;font-size:11.5px;color:#555;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
            <span style="font-weight:700;color:${c};">Amount in Words:</span> <em>${words}</em>
          </div>

          ${notes ? `
            <div class="rc-notes-wrap" style="margin-top:6px;font-size:11px;color:#64748b;">
              <strong>Remarks:</strong> ${notes}
            </div>
          ` : ''}
        </div>

        <!-- MIDDLE: QR Code (Mirrors PMS print-receipt.php) -->
        <div class="rc-summary-mid" style="display:flex;flex-direction:column;align-items:center;gap:4px;flex-shrink:0;">
          ${qrDataUrl ? `
            <div style="background:#fff;border:2px solid ${thBg};border-radius:10px;padding:5px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
              <img src="${qrDataUrl}" style="width:90px;height:90px;display:block;" alt="QR Code">
            </div>
            <div style="font-size:8.5px;font-weight:600;color:#888;letter-spacing:.4px;text-align:center;line-height:1.3;margin-top:2px;">Scan for<br>Digital Receipt</div>
            <a href="${d.receiptUrl}" target="_blank" style="font-size:8.5px;font-weight:700;color:${c};text-decoration:none;letter-spacing:.3px;margin-top:1px;">View Details</a>
          ` : `
            <div style="width:90px;height:90px;background:#f8fafc;border:1px dashed ${thBg};border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#94a3b8;text-align:center;padding:8px;">
              Digital QR
            </div>
          `}
        </div>

        <!-- RIGHT: Existing PAID Stamp (No Signatures) -->
        <div class="rc-summary-right" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;min-width:130px;flex-shrink:0;">
          ${isReversed ? `
            <div style="border:2.5px dashed #DC2626;color:#DC2626;font-size:14px;font-weight:900;padding:12px 18px;border-radius:8px;letter-spacing:2px;text-transform:uppercase;transform:rotate(-8deg);background:#FEF2F2;">
              REVERSED
            </div>
            <div style="font-size:8px;font-weight:700;color:#DC2626;letter-spacing:0.5px;text-transform:uppercase;margin-top:4px;">Transaction Cancelled</div>
          ` : (showStamp && stampSrc ? `
            <div style="display:flex;align-items:center;justify-content:center;padding:4px;">
              <img src="${stampSrc}" style="max-width:130px;max-height:100px;width:auto;height:auto;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,.08));" alt="Official PAID Stamp">
            </div>
            <div style="font-size:8.5px;font-weight:700;color:${c};letter-spacing:.5px;text-transform:uppercase;margin-top:2px;">Official Payment Stamp</div>
          ` : `
            <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;border:1.5px solid #e2e8f0;padding:10px 14px;border-radius:8px;text-align:center;">
              Certified Payment
            </div>
          `)}
        </div>
      </div>

      <!-- FOOTER -->
      <div class="rc-footer-wrap" style="background:${footerBg};border-top:3px solid ${c};padding:12px 36px;display:flex;justify-content:space-between;align-items:center;font-size:10.5px;color:#555;position:relative;z-index:2;">
        <span>${d.coEmail}</span>
        <span style="font-weight:600;color:${c};">Official Driver Payment Voucher &bull; Certified Settlement</span>
        <span>${phones}</span>
      </div>
    `;
  }

  function renderReceipt(c) {
    document.getElementById('receiptPaper').innerHTML = buildReceipt(c);
  }

  // Generate QR once then do initial dynamic render
  generateQR().then(url => {
    qrDataUrl = url;
    renderReceipt(selectedColor);
  }).catch(() => {
    renderReceipt(selectedColor);
  });

  // Color swatches click handler (mirrors PMS print-receipt.php)
  document.querySelectorAll('.swatch').forEach(sw => {
    sw.addEventListener('click', function () {
      document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
      this.classList.add('active');
      selectedColor = this.dataset.color;
      generateQR().then(url => {
        qrDataUrl = url;
        renderReceipt(selectedColor);
      });
    });
  });

  // Iframe print trigger (exact same as PMS print-receipt.php)
  function triggerPrint() {
    const receiptHTML = buildReceipt(selectedColor);
    const printHTML = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Receipt</title><style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:\'Plus Jakarta Sans\',Arial,sans-serif;background:#fff;color:#111;}@page{size:A4 landscape;margin:10mm;}*{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important;}</style></head><body>' + receiptHTML + '</body></html>';
    const existing = document.getElementById('_printIframe');
    if (existing) existing.remove();
    const iframe = document.createElement('iframe');
    iframe.id = '_printIframe';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1123px;height:794px;border:none;';
    document.body.appendChild(iframe);
    const iDoc = iframe.contentWindow.document;
    iDoc.open(); iDoc.write(printHTML); iDoc.close();
    iframe.onload = function() {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
    };
  }

  // Print button click
  document.getElementById('printBtn').addEventListener('click', triggerPrint);

  // Intercept Ctrl+P — redirect to iframe-based print (mirrors PMS)
  window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
      e.preventDefault();
      triggerPrint();
    }
  });
</script>
</body>
</html>
