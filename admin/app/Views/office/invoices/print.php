<?php
/**
 * TOURS & TRAVELS — OFFICIAL CUSTOMER TAX INVOICE & BILL RECEIPT
 * Premium Printable & PDF-Ready A4 Document
 * Matching Theme System with Interactive Swatches and Live Dynamic QR Code
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

// Financials & Payment Status
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

// Company Branding
$companyName = company_display_name();
$tagline = company_tagline() ?: 'Commercial Fleet Logistics & Premium Tours';
$logoUrl = company_logo_url();
$address = company_full_address() ?: company_address();
$phone = company_phone();
$altPhone = company_alternate_phone();
$email = company_email();
$website = company_website();
$gstin = company_gst_number();
$pan = company_pan_number();

// Company Official Stamps & Signatures
$paidStamp = company_paid_stamp_url();
$hasStamp = !empty($paidStamp) && has_company_paid_stamp();
$showStampParam = isset($show_stamp) ? (bool)$show_stamp : (($_GET['stamp'] ?? '1') !== '0');
$showPaidStamp = ($isFullyPaid && $hasStamp && $showStampParam);

$authSigUrl = company_signature_url();
$sigStampUrl = company_signature_stamp_url();

// Resolve Digital Invoice Verification URL
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
$invoiceVerifyUrl = $scheme . '://' . $host . url('/invoice/verify/' . $invoice['id']);

// Default Primary Bank Account
$bankAccountName = $bankAccount['bank_name'] ?? 'Primary Business Bank';
$bankAccNumber = $bankAccount['account_number'] ?? '';
$bankIfsc = $bankAccount['ifsc_code'] ?? '';
$bankBeneficiary = $bankAccount['account_name'] ?? $companyName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tax Invoice #<?= e($invoiceNum) ?> &bull; <?= e($companyName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    background: #f1f5f9;
    color: #1F2937;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* ── Interactive Toolbar Matching Driver Payment Slip ── */
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
    display: contents;
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
  .tbtn-share {
    background: rgba(255,255,255,.18);
    color: #fff;
    border: 1px solid rgba(255,255,255,.25);
  }
  .tbtn-share:hover {
    background: rgba(255,255,255,.28);
    color: #fff;
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
  .tbtn-print {
    background: #10B981;
    color: #fff;
  }
  .tbtn-print:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.3);
  }

  /* ── Paper Presentation Area ── */
  .receipt-wrap {
    display: flex;
    justify-content: center;
    padding: 28px 16px 40px;
  }
  .receipt-paper {
    background: #fff;
    width: 100%;
    max-width: 880px;
    box-shadow: 0 8px 32px rgba(0,0,0,.12);
    border-radius: 12px;
    overflow: hidden;
    font-size: 13px;
    color: #111;
    position: relative;
  }

  /* ── Mobile Responsive Screen Rules ── */
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
    }
    .toolbar-sub {
      font-size: 0.72rem;
      color: rgba(255, 255, 255, 0.75);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
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

    /* Stacking on mobile */
    .receipt-wrap {
      padding: 12px 8px 30px;
    }
    .receipt-paper {
      border-radius: 8px;
      font-size: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .rc-header-wrap {
      padding: 16px 14px !important;
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
    .rc-summary-wrap {
      padding: 14px 14px 20px !important;
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 18px !important;
    }
    .rc-summary-left {
      min-width: 100% !important;
    }
    .rc-summary-right {
      align-self: center !important;
    }
    .rc-signatures-wrap {
      flex-direction: column !important;
      gap: 24px !important;
      padding: 16px 14px !important;
    }
    .rc-footer-wrap {
      padding: 12px 14px !important;
      flex-direction: column !important;
      gap: 6px !important;
      text-align: center !important;
    }
  }

  @media screen and (max-width: 520px) {
    .toolbar-actions .tbtn-share .tbtn-text,
    .toolbar-actions .tbtn-digital .tbtn-text {
      display: none;
    }
    .toolbar-actions .tbtn-share,
    .toolbar-actions .tbtn-digital {
      padding: 6px 8px;
    }
  }

  @media screen and (max-width: 380px) {
    .toolbar-actions .tbtn-close .tbtn-text {
      display: none;
    }
    .toolbar-actions .tbtn-close {
      padding: 6px 8px;
    }
  }

  /* ── Dedicated Print Stylesheet ── */
  @media print {
    body {
      background: #fff !important;
      padding: 0 !important;
      color: #000 !important;
    }
    .toolbar {
      display: none !important;
    }
    .receipt-wrap {
      padding: 0 !important;
    }
    .receipt-paper {
      box-shadow: none !important;
      border-radius: 0 !important;
      max-width: 100% !important;
      margin: 0 !important;
    }
    @page {
      size: A4 portrait;
      margin: 10mm;
    }
  }
</style>
</head>
<body>

<!-- Toolbar with Theme Switcher (Matching Driver Payment Slip) -->
<div class="toolbar">
  <div class="toolbar-main">
    <div class="toolbar-left">
      <div class="toolbar-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
      <div class="toolbar-meta">
        <div class="toolbar-title">Customer Tax Invoice &amp; Bill Receipt</div>
        <div class="toolbar-sub"><?= e($invoice['customer_name'] ?? 'Customer') ?> &mdash; <?= e($invoiceNum) ?></div>
      </div>
    </div>
    <div class="toolbar-actions">
      <a href="<?= url('/invoice/verify/' . (int)$invoice['id']) ?>" target="_blank" class="tbtn tbtn-digital" title="<?= e(__('tooltip.view_digital_invoice_verificat', 'View Digital Invoice Verification')) ?>"><i class="fa-solid fa-mobile-screen"></i> <span class="tbtn-text">Digital View</span></a>
      <button type="button" class="tbtn tbtn-share" onclick="shareInvoice()" title="<?= e(__('tooltip.share_invoice', 'Share Invoice')) ?>"><i class="fa-solid fa-share-nodes"></i> <span class="tbtn-text">Share</span></button>
      <button type="button" class="tbtn tbtn-close" onclick="window.history.length > 1 ? window.history.back() : window.close()" title="<?= e(__('tooltip.close_receipt', 'Close Receipt')) ?>"><i class="fa-solid fa-xmark"></i> <span class="tbtn-text">Close</span></button>
      <button type="button" class="tbtn tbtn-print" id="printBtn" title="<?= e(__('tooltip.print_invoice', 'Print Invoice')) ?>"><i class="fa-solid fa-print"></i> <span class="tbtn-text">Print</span></button>
    </div>
  </div>
  <div class="color-bar" id="colorBar">
    <span class="color-label"><i class="fa-solid fa-palette"></i> Theme:</span>
    <div class="swatches-track">
      <div class="swatch active" data-color="#10B981" style="background:#10B981;" title="<?= e(__('tooltip.emerald', 'Emerald')) ?>"></div>
      <div class="swatch" data-color="#0EA5E9" style="background:#0EA5E9;" title="<?= e(__('tooltip.sky_blue', 'Sky Blue')) ?>"></div>
      <div class="swatch" data-color="#6366F1" style="background:#6366F1;" title="<?= e(__('tooltip.indigo', 'Indigo')) ?>"></div>
      <div class="swatch" data-color="#F59E0B" style="background:#F59E0B;" title="<?= e(__('tooltip.amber', 'Amber')) ?>"></div>
      <div class="swatch" data-color="#EF4444" style="background:#EF4444;" title="Red"></div>
      <div class="swatch" data-color="#8B5CF6" style="background:#8B5CF6;" title="<?= e(__('tooltip.violet', 'Violet')) ?>"></div>
      <div class="swatch" data-color="#1e3a5f" style="background:#1e3a5f;" title="<?= e(__('tooltip.navy', 'Navy')) ?>"></div>
      <div class="swatch" data-color="#064e3b" style="background:#064e3b;" title="<?= e(__('tooltip.forest', 'Forest')) ?>"></div>
    </div>
  </div>
</div>

<!-- Receipt Presentation Area -->
<div class="receipt-wrap">
  <div class="receipt-paper" id="receiptPaper">
    <!-- Server-rendered fallback content -->
    <div class="rc-header-wrap" style="padding:22px 36px; background:#e6f9f3; display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #10B981;">
      <div style="display:flex; align-items:center; gap:14px;">
        <?php if (!empty($logoUrl)): ?>
          <img src="<?= e($logoUrl) ?>" style="height:54px; width:auto; object-fit:contain; background:rgba(255,255,255,.9); border-radius:8px; padding:4px 8px; border:1px solid #c2f0e0;" alt="Logo">
        <?php endif; ?>
        <div>
          <div style="font-size:22px; font-weight:800; color:#10B981; line-height:1.2;"><?= e($companyName) ?></div>
          <div style="font-size:11px; color:#555;"><?= e($tagline) ?></div>
        </div>
      </div>
      <div class="rc-header-right" style="text-align:right;">
        <div style="font-size:24px; font-weight:800; color:#10B981; letter-spacing:.5px; text-transform:uppercase;">Tax Invoice</div>
        <div style="font-size:11.5px; color:#666; margin-top:4px; font-weight:600;">Bill No: <?= e($invoiceNum) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden serialized data for client-side theme re-rendering -->
<div id="invoiceData" style="display:none;"
  data-inv-no="<?= htmlspecialchars($invoiceNum, ENT_QUOTES) ?>"
  data-inv-id="<?= (int)$invoice['id'] ?>"
  data-date="<?= htmlspecialchars($issuedDate, ENT_QUOTES) ?>"
  data-due-date="<?= htmlspecialchars($dueDate, ENT_QUOTES) ?>"
  data-customer-name="<?= htmlspecialchars($invoice['customer_name'] ?? 'Valued Customer', ENT_QUOTES) ?>"
  data-customer-code="<?= htmlspecialchars($invoice['customer_code'] ?? 'CUS-001', ENT_QUOTES) ?>"
  data-customer-phone="<?= htmlspecialchars($invoice['customer_phone'] ?? '—', ENT_QUOTES) ?>"
  data-customer-email="<?= htmlspecialchars($invoice['customer_email'] ?? '—', ENT_QUOTES) ?>"
  data-customer-address="<?= htmlspecialchars($invoice['customer_address'] ?? 'Billing Address on Record', ENT_QUOTES) ?>"
  data-booking-code="<?= htmlspecialchars($invoice['booking_code'] ?? '—', ENT_QUOTES) ?>"
  data-trip-category="<?= htmlspecialchars($invoice['trip_category_name'] ?? ($invoice['tour_title'] ?: 'Commercial Travel Journey'), ENT_QUOTES) ?>"
  data-pickup="<?= htmlspecialchars($invoice['pickup_address'] ?? 'Pickup Point', ENT_QUOTES) ?>"
  data-drop="<?= htmlspecialchars($invoice['drop_address'] ?? 'Drop Location', ENT_QUOTES) ?>"
  data-start-date="<?= !empty($invoice['start_date']) ? date('d M Y', strtotime($invoice['start_date'])) : '—' ?>"
  data-end-date="<?= !empty($invoice['end_date']) ? date('d M Y', strtotime($invoice['end_date'])) : '—' ?>"
  data-pickup-time="<?= htmlspecialchars($invoice['pickup_time'] ?? '', ENT_QUOTES) ?>"
  data-passengers="<?= (int)($invoice['passenger_count'] ?? 1) ?>"
  data-vehicle="<?= htmlspecialchars(!empty($invoice['vehicle_number']) ? ($invoice['vehicle_number'] . ' (' . ($invoice['vehicle_model'] ?: 'Fleet Vehicle') . ')') : 'Assigned Luxury Fleet', ENT_QUOTES) ?>"
  data-driver="<?= htmlspecialchars(!empty($invoice['driver_name']) ? ($invoice['driver_name'] . (!empty($invoice['driver_phone']) ? ' &bull; ' . $invoice['driver_phone'] : '')) : 'Professional Driver', ENT_QUOTES) ?>"
  data-subtotal="<?= number_format((float)($invoice['subtotal'] ?? $totalAmt), 2) ?>"
  data-tax-rate="<?= number_format((float)($invoice['tax_rate'] ?? 0), 2) ?>"
  data-tax-amt="<?= number_format((float)($invoice['tax_amount'] ?? 0), 2) ?>"
  data-tax-amt-num="<?= (float)($invoice['tax_amount'] ?? 0) ?>"
  data-discount="<?= number_format((float)($invoice['discount_amount'] ?? 0), 2) ?>"
  data-discount-num="<?= (float)($invoice['discount_amount'] ?? 0) ?>"
  data-total-amt="<?= number_format($totalAmt, 2) ?>"
  data-paid-amt="<?= number_format($paidAmt, 2) ?>"
  data-balance-amt="<?= number_format($balanceAmt, 2) ?>"
  data-balance-num="<?= $balanceAmt ?>"
  data-words="<?= htmlspecialchars($amtInWords, ENT_QUOTES) ?>"
  data-is-paid="<?= $isFullyPaid ? '1' : '0' ?>"
  data-is-partial="<?= $isPartial ? '1' : '0' ?>"
  data-logo="<?= htmlspecialchars($logoUrl ?? '', ENT_QUOTES) ?>"
  data-stamp="<?= htmlspecialchars($showPaidStamp ? ($paidStamp ?? '') : '', ENT_QUOTES) ?>"
  data-sig="<?= htmlspecialchars($authSigUrl ?? '', ENT_QUOTES) ?>"
  data-sig-stamp="<?= htmlspecialchars($sigStampUrl ?? '', ENT_QUOTES) ?>"
  data-co-name="<?= htmlspecialchars($companyName, ENT_QUOTES) ?>"
  data-co-tagline="<?= htmlspecialchars($tagline, ENT_QUOTES) ?>"
  data-co-address="<?= htmlspecialchars($address, ENT_QUOTES) ?>"
  data-co-phone="<?= htmlspecialchars($phone, ENT_QUOTES) ?>"
  data-co-alt-phone="<?= htmlspecialchars($altPhone ?? '', ENT_QUOTES) ?>"
  data-co-email="<?= htmlspecialchars($email, ENT_QUOTES) ?>"
  data-co-website="<?= htmlspecialchars($website ?? '', ENT_QUOTES) ?>"
  data-co-gst="<?= htmlspecialchars($gstin ?? '', ENT_QUOTES) ?>"
  data-co-pan="<?= htmlspecialchars($pan ?? '', ENT_QUOTES) ?>"
  data-bank-name="<?= htmlspecialchars($bankAccountName, ENT_QUOTES) ?>"
  data-bank-acc="<?= htmlspecialchars($bankAccNumber, ENT_QUOTES) ?>"
  data-bank-ifsc="<?= htmlspecialchars($bankIfsc, ENT_QUOTES) ?>"
  data-bank-ben="<?= htmlspecialchars($bankBeneficiary, ENT_QUOTES) ?>"
  data-verify-url="<?= htmlspecialchars($invoiceVerifyUrl, ENT_QUOTES) ?>"
  data-verify-link="<?= htmlspecialchars(url('/invoice/verify/' . (int)$invoice['id']), ENT_QUOTES) ?>"
></div>

<!-- Hidden JSON for Payments History List -->
<script id="paymentsDataJson" type="application/json">
<?= json_encode($payments ?? []) ?>
</script>

<!-- Hidden JSON for Itemized Billing Items List -->
<script id="billingItemsDataJson" type="application/json">
<?= json_encode($invoice['billing_items'] ?? []) ?>
</script>

<!-- Hidden QR container (off-screen for canvas rendering) -->
<div id="qrContainer" style="position:fixed;left:-9999px;top:-9999px;width:128px;height:128px;overflow:hidden;"></div>

<!-- Local QR Code library with CDN fallback -->
<script src="<?= asset('assets/js/qrcode.min.js') ?>"></script>
<script>
  if (typeof QRCode === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
  }
</script>

<script>
  let selectedColor = '#10B981'; // Emerald default (Tours & Travels Master Brand matching)
  let qrDataUrl = '';

  const d = document.getElementById('invoiceData').dataset;
  let paymentsList = [];
  try {
    paymentsList = JSON.parse(document.getElementById('paymentsDataJson').textContent || '[]');
  } catch(e) {
    paymentsList = [];
  }

  let billingItemsList = [];
  try {
    billingItemsList = JSON.parse(document.getElementById('billingItemsDataJson').textContent || '[]');
  } catch(e) {
    billingItemsList = [];
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
    });
  }

  // Generate QR code pointing to public digital invoice verification
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
          text: d.verifyUrl,
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

  // Dynamic Builder Matching PMS print-receipt.php
  function buildInvoice(c) {
    const headerBg = lightenHex(c, 0.88);
    const thBg     = lightenHex(c, 0.78);
    const boxBg    = lightenHex(c, 0.94);
    const footerBg = lightenHex(c, 0.92);

    const logoSrc   = d.logo;
    const stampSrc  = d.stamp;
    const sigSrc    = d.sigStamp || d.sig;
    const isPaid    = (d.isPaid === '1');
    const isPartial = (d.isPartial === '1');
    const balNum    = parseFloat(d.balanceNum) || 0;
    const phones    = [d.coPhone, d.coAltPhone].filter(Boolean).join(' / ');

    // Build Payments Received rows if any
    let paymentsRowsHtml = '';
    if (paymentsList && paymentsList.length > 0) {
      paymentsRowsHtml = paymentsList.map((p, idx) => {
        const pDate = p.paid_at ? new Date(p.paid_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const pAmt = parseFloat(p.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const pMethod = (p.payment_method || 'Payment').replace(/_/g, ' ').toUpperCase();
        return `
          <tr style="border-bottom:1px solid #e2e8f0; font-size:11.5px;">
            <td style="padding:6px 12px; font-weight:600; color:#1e293b;">${p.payment_reference || ('PAY-' + (idx+1))}</td>
            <td style="padding:6px 12px; color:#475569;">${pDate}</td>
            <td style="padding:6px 12px; color:#475569;"><span style="display:inline-block;padding:2px 6px;border-radius:4px;background:#f1f5f9;font-weight:700;font-size:10.5px;">${pMethod}</span> ${p.transaction_id ? '&bull; Ref: ' + p.transaction_id : ''}</td>
            <td style="padding:6px 12px; text-align:right; font-weight:700; color:#10B981;">₹${pAmt}</td>
          </tr>
        `;
      }).join('');
    }

    // Build Itemized Charges rows matching Reference Bill
    let itemsRowsHtml = '';
    if (billingItemsList && billingItemsList.length > 0) {
      itemsRowsHtml = billingItemsList.map((item) => {
        const desc = item.description || 'Trip Service Charge';
        const qtyNum = parseFloat(item.quantity || 1);
        const unit = (item.unit || '').trim();
        const qtyDisplay = (qtyNum > 0) ? ((Number.isInteger(qtyNum) ? qtyNum : qtyNum.toFixed(2)) + (unit ? ' ' + unit : '')) : '—';
        const rateVal = (item.unit_rate !== null && item.unit_rate !== '' && !isNaN(parseFloat(item.unit_rate)))
          ? ('₹' + parseFloat(item.unit_rate).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
          : '—';
        const lineAmt = parseFloat(item.line_total || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        let categoryBadge = '';
        const cat = (item.charge_category || '').toLowerCase();
        if (cat === 'distance') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#E8F4F0;color:#176B5B;font-size:10px;font-weight:700;margin-left:6px;border:1px solid rgba(23,107,91,0.2);">Distance</span>`;
        } else if (cat === 'daily') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EFF6FF;color:#1E40AF;font-size:10px;font-weight:700;margin-left:6px;border:1px solid rgba(30,64,175,0.2);">Daily Fleet</span>`;
        } else if (cat === 'halting') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#F1F5F9;color:#475569;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">Halting</span>`;
        } else if (cat === 'parking') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#FEF3C7;color:#92400E;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #fde68a;">Parking</span>`;
        } else if (cat === 'toll') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#FEF2F2;color:#991B1B;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #fecaca;">Toll Fee</span>`;
        } else if (cat === 'permit') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#F3E8FF;color:#6B21A8;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #e9d5ff;">Permit</span>`;
        } else if (cat === 'extra_driver' || cat === 'driver') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#ECFEFF;color:#0E7490;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cffafe;">Extra Driver</span>`;
        } else if (cat === 'other' || cat === 'extra') {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EEF2F6;color:#334155;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">Extra Charge</span>`;
        } else if (cat) {
          categoryBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#EEF2F6;color:#334155;font-size:10px;font-weight:700;margin-left:6px;border:1px solid #cbd5e1;">${escapeHtml(cat.charAt(0).toUpperCase() + cat.slice(1))}</span>`;
        }

        return `
          <tr style="border-bottom:1px solid #e2e8f0;">
            <td style="padding:10px 12px;">
              <div style="font-weight:700;color:#0f172a;font-size:12.5px;">
                ${escapeHtml(desc)} ${categoryBadge}
              </div>
            </td>
            <td style="padding:10px 12px;text-align:center;color:#475569;font-weight:600;">${qtyDisplay}</td>
            <td style="padding:10px 12px;text-align:right;color:#475569;font-weight:600;">${rateVal}</td>
            <td style="padding:10px 12px;text-align:right;font-weight:700;color:#0f172a;font-size:12.5px;">₹${lineAmt}</td>
          </tr>
        `;
      }).join('');
    } else {
      // Backward compatibility fallback for legacy invoices
      itemsRowsHtml = `
        <tr style="border-bottom:1px solid #e2e8f0;">
          <td style="padding:12px 12px;">
            <div style="font-weight:700;color:#0f172a;font-size:13px;">Commercial Fleet Hire &bull; ${d.tripCategory}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">
              Complete itinerary from ${d.pickup} to ${d.drop} &bull; Inclusive of vehicle fuel, driver allowance &amp; standard permits
            </div>
          </td>
          <td style="padding:12px 12px;text-align:center;color:#475569;font-weight:600;">1 Trip</td>
          <td style="padding:12px 12px;text-align:right;color:#475569;font-weight:600;">₹${d.subtotal}</td>
          <td style="padding:12px 12px;text-align:right;font-weight:700;color:#0f172a;font-size:13px;">₹${d.subtotal}</td>
        </tr>
      `;
    }

    return `
      <!-- TOP HEADER BRANDING -->
      <div class="rc-header-wrap" style="background:${headerBg};padding:22px 36px;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid ${c};position:relative;z-index:2;">
        <div style="display:flex;align-items:center;gap:14px;">
          ${logoSrc ? `<img src="${logoSrc}" style="height:56px;width:auto;object-fit:contain;background:rgba(255,255,255,.9);border-radius:8px;padding:4px 8px;border:1px solid ${thBg};" alt="Logo">` : ''}
          <div>
            <div style="font-size:22px;font-weight:800;color:${c};letter-spacing:-.3px;line-height:1.2;">${d.coName}</div>
            <div style="font-size:11px;color:#555;margin-top:2px;">${d.coAddress} ${d.coGst ? '&bull; GSTIN: <strong>' + d.coGst + '</strong>' : ''}</div>
          </div>
        </div>
        <div class="rc-header-right" style="text-align:right;">
          <div style="font-size:24px;font-weight:800;color:${c};letter-spacing:.5px;text-transform:uppercase;">Tax Invoice</div>
          <div style="font-size:11.5px;color:#666;margin-top:4px;letter-spacing:.4px;font-weight:600;">Bill No: <strong>${d.invNo}</strong> &bull; Date: <strong>${d.date}</strong></div>
          <div style="margin-top:4px;">
            ${isPaid 
              ? `<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;background:#DCFCE7;color:#15803D;letter-spacing:.3px;">FULL PAYMENT RECEIVED</span>`
              : (isPartial
                  ? `<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;background:#FEF3C7;color:#B45309;letter-spacing:.3px;">PARTIALLY PAID</span>`
                  : `<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;background:#FEE2E2;color:#B91C1C;letter-spacing:.3px;">PAYMENT DUE</span>`
                )
            }
          </div>
        </div>
      </div>

      <!-- ACCENT LINE -->
      <div style="height:4px;background:linear-gradient(90deg,${c},${thBg},${c});"></div>

      <!-- 2-COLUMN INFO GRID: CUSTOMER (BILLED TO) + TRIP / JOURNEY PARTICULAR -->
      <div class="rc-details-wrap" style="padding:20px 36px 16px;display:flex;justify-content:space-between;gap:24px;border-bottom:1px solid #e5e7eb;position:relative;z-index:2;">
        <!-- Left: Customer Details -->
        <div style="flex:1;min-width:0;">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:${c};margin-bottom:8px;display:flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-user-tag"></i> Billed To (Customer)
          </div>
          <div style="font-size:14px;font-weight:700;color:#0f172a;line-height:1.3;">${d.customerName}</div>
          <div style="font-size:11.5px;color:#64748b;margin-top:3px;">Client ID: <strong>${d.customerCode}</strong></div>
          <div style="font-size:11.5px;color:#475569;margin-top:2px;"><i class="fa-solid fa-phone" style="width:14px;color:#94a3b8;"></i> ${d.customerPhone}</div>
          <div style="font-size:11.5px;color:#475569;margin-top:2px;"><i class="fa-solid fa-envelope" style="width:14px;color:#94a3b8;"></i> ${d.customerEmail}</div>
          <div style="font-size:11px;color:#64748b;margin-top:4px;line-height:1.4;">${d.customerAddress}</div>
        </div>

        <!-- Right: Booking & Journey Details -->
        <div style="flex:1;min-width:0;background:${boxBg};border:1px solid ${thBg};border-radius:8px;padding:12px 16px;">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:${c};margin-bottom:8px;display:flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-route"></i> Booking &amp; Fleet Particulars
          </div>
          <table style="width:100%;font-size:11.5px;border-collapse:collapse;">
            <tr>
              <td style="color:#64748b;padding:3px 0;width:95px;font-weight:600;">Booking Ref:</td>
              <td style="color:#0f172a;font-weight:700;font-family:'JetBrains Mono',monospace;">${d.bookingCode}</td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Trip Category:</td>
              <td style="color:#0f172a;font-weight:700;"><span style="display:inline-block;padding:1px 7px;border-radius:4px;background:#e0e7ff;color:#3730a3;font-size:10.5px;">${d.tripCategory}</span></td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Route:</td>
              <td style="color:#0f172a;font-weight:600;">${d.pickup} &rarr; ${d.drop}</td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Travel Dates:</td>
              <td style="color:#0f172a;">${d.startDate} &mdash; ${d.endDate} ${d.pickupTime ? '(' + d.pickupTime + ')' : ''}</td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Passengers:</td>
              <td style="color:#0f172a;"><strong>${d.passengers}</strong> Person(s)</td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Vehicle:</td>
              <td style="color:#0f172a;font-weight:600;">${d.vehicle}</td>
            </tr>
            <tr>
              <td style="color:#64748b;padding:3px 0;font-weight:600;">Driver:</td>
              <td style="color:#0f172a;">${d.driver}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- ITEMIZED CHARGES TABLE -->
      <div style="padding:16px 36px 10px;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
          <thead>
            <tr style="background:${thBg};color:#0f172a;">
              <th style="padding:8px 12px;text-align:left;font-weight:700;border-radius:6px 0 0 6px;">Particulars &amp; Service Description</th>
              <th style="padding:8px 12px;text-align:center;font-weight:700;width:105px;"><?= e(__('booking.quantity', 'Quantity')) ?></th>
              <th style="padding:8px 12px;text-align:right;font-weight:700;width:115px;">Rate (₹)</th>
              <th style="padding:8px 12px;text-align:right;font-weight:700;width:130px;border-radius:0 6px 6px 0;">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            ${itemsRowsHtml}
            ${parseFloat(d.taxAmtNum) > 0 ? `
              <tr style="border-bottom:1px solid #e2e8f0;background:#fafafa;">
                <td style="padding:8px 12px;color:#475569;" colspan="3">
                  <strong>Goods &amp; Services Tax (GST @ ${d.taxRate}%)</strong>
                  <span style="font-size:10.5px;color:#94a3b8;margin-left:6px;">Standard Commercial Transportation GST</span>
                </td>
                <td style="padding:8px 12px;text-align:right;font-weight:600;color:#475569;">₹${d.taxAmt}</td>
              </tr>
            ` : ''}
            ${parseFloat(d.discountNum) > 0 ? `
              <tr style="border-bottom:1px solid #e2e8f0;background:#fafafa;">
                <td style="padding:8px 12px;color:#15803D;" colspan="3">
                  <strong>Special Promotional Discount</strong>
                </td>
                <td style="padding:8px 12px;text-align:right;font-weight:700;color:#15803D;">- ₹${d.discount}</td>
              </tr>
            ` : ''}
          </tbody>
        </table>
      </div>

      <!-- SUMMARY STRIP: WORDS + BANK REMITTANCE + TOTALS + PAID STAMP -->
      <div class="rc-summary-wrap" style="padding:16px 36px 20px;display:flex;justify-content:space-between;align-items:flex-start;gap:24px;position:relative;z-index:2;">
        <!-- Left: Amount in Words + Bank Remittance + Live QR -->
        <div class="rc-summary-left" style="flex:1;min-width:0;">
          <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:10px 14px;margin-bottom:12px;">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:3px;">
              Invoice Amount in Words:
            </div>
            <div style="font-size:12px;font-weight:700;color:#0f172a;font-style:italic;">
              ${d.words}
            </div>
          </div>

          <!-- Bank & QR Remittance Box -->
          <div style="display:flex;gap:14px;align-items:center;background:${boxBg};border:1px solid ${thBg};border-radius:8px;padding:12px 14px;">
            <a href="${d.verifyLink || d.verifyUrl}" target="_blank" rel="noopener" title="<?= e(__('tooltip.click_or_scan_with_mobile_to_o', 'Click or Scan with mobile to Open Digital Slip')) ?>" style="text-decoration:none;display:flex;flex-direction:column;align-items:center;cursor:pointer;flex-shrink:0;">
              <div style="width:78px;height:78px;min-width:78px;background:#fff;border-radius:6px;border:1px solid #cbd5e1;display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);transition:all .18s ease;" onmouseover="this.style.transform='scale(1.05)';this.style.boxShadow='0 3px 10px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 1px 3px rgba(0,0,0,0.08)';">
                ${qrDataUrl ? `<img src="${qrDataUrl}" style="width:70px;height:70px;object-fit:contain;" alt="Digital Verification QR Code">` : `<div style="font-size:9px;color:#94a3b8;text-align:center;"><i class="fa-solid fa-qrcode" style="font-size:18px;display:block;margin-bottom:2px;"></i>Scan / Click</div>`}
              </div>
              <span style="font-size:9.5px;font-weight:700;color:${c};margin-top:4px;text-align:center;display:flex;align-items:center;gap:3px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open Slip</span>
            </a>
            <div style="font-size:11px;color:#475569;line-height:1.45;flex:1;min-width:0;">
              <div style="font-weight:800;color:${c};font-size:11.5px;margin-bottom:2px;"><i class="fa-solid fa-building-columns"></i> Bank Remittance Details</div>
              <div>Bank: <strong>${d.bankName}</strong></div>
              <div>A/C No: <strong style="font-family:'JetBrains Mono',monospace;">${d.bankAcc || 'On File'}</strong> &bull; IFSC: <strong style="font-family:'JetBrains Mono',monospace;">${d.bankIfsc || 'On File'}</strong></div>
              <div style="font-size:10px;color:#64748b;margin-top:3px;">
                Scan QR or <a href="${d.verifyLink || d.verifyUrl}" target="_blank" rel="noopener" style="color:${c};font-weight:700;text-decoration:underline;">Click Here to Open Digital Slip</a> with complete trip, customer &amp; payment particulars.
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Totals Box + PAID Stamp -->
        <div class="rc-summary-right" style="display:flex;flex-direction:column;gap:12px;min-width:260px;">
          <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
            <tr>
              <td style="padding:4px 0;color:#64748b;font-weight:600;">Subtotal:</td>
              <td style="padding:4px 0;text-align:right;font-weight:700;color:#0f172a;">₹${d.subtotal}</td>
            </tr>
            ${parseFloat(d.taxAmtNum) > 0 ? `
              <tr>
                <td style="padding:4px 0;color:#64748b;font-weight:600;">Taxes (GST ${d.taxRate}%):</td>
                <td style="padding:4px 0;text-align:right;font-weight:600;color:#475569;">₹${d.taxAmt}</td>
              </tr>
            ` : ''}
            <tr style="border-top:2px solid ${c};border-bottom:2px solid ${c};">
              <td style="padding:8px 0;color:${c};font-weight:800;font-size:14px;">TOTAL AMOUNT:</td>
              <td style="padding:8px 0;text-align:right;font-weight:800;color:${c};font-size:15px;">₹${d.totalAmt}</td>
            </tr>
            <tr>
              <td style="padding:6px 0;color:#10B981;font-weight:700;">Collections Received:</td>
              <td style="padding:6px 0;text-align:right;font-weight:700;color:#10B981;">₹${d.paidAmt}</td>
            </tr>
            <tr style="border-top:1px dashed #cbd5e1;">
              <td style="padding:6px 0;font-weight:800;color:${balNum <= 0.009 ? '#10B981' : '#D95757'};">
                BALANCE DUE:
                ${(parseFloat(d.paidAmt) <= 0.009 && balNum > 0.009) ? `<div style="font-size:9.5px;color:#94a3b8;font-weight:600;margin-top:1px;">(Advance skipped &bull; Pay later)</div>` : ''}
              </td>
              <td style="padding:6px 0;text-align:right;font-weight:800;font-size:13.5px;color:${balNum <= 0.009 ? '#10B981' : '#D95757'};">
                ${balNum <= 0.009 ? '₹0.00 (Settled)' : '₹' + d.balanceAmt}
              </td>
            </tr>
          </table>

          <!-- Official PAID Stamp or Status Stamp Block -->
          <div style="text-align:center;margin-top:4px;">
            ${isPaid && stampSrc ? `
              <div style="display:inline-block;padding:2px;">
                <img src="${stampSrc}" style="max-width:140px;max-height:85px;width:auto;height:auto;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,.1));" alt="Official PAID Stamp">
                <div style="font-size:8.5px;font-weight:700;color:${c};letter-spacing:.5px;text-transform:uppercase;margin-top:2px;">Official Payment Stamp</div>
              </div>
            ` : (isPaid ? `
              <div style="border:2px solid #10B981;color:#10B981;font-size:12px;font-weight:800;padding:6px 14px;border-radius:6px;display:inline-block;letter-spacing:1px;text-transform:uppercase;transform:rotate(-4deg);background:#ecfdf5;">
                PAID IN FULL
              </div>
            ` : (isPartial ? `
              <div style="border:2px dashed #F59E0B;color:#B45309;font-size:11px;font-weight:800;padding:5px 12px;border-radius:6px;display:inline-block;letter-spacing:.5px;text-transform:uppercase;background:#FEF3C7;">
                PARTIALLY PAID &bull; ₹${d.balanceAmt} DUE
              </div>
            ` : `
              <div style="border:2px dashed #EF4444;color:#B91C1C;font-size:11px;font-weight:800;padding:5px 12px;border-radius:6px;display:inline-block;letter-spacing:.5px;text-transform:uppercase;background:#FEE2E2;">
                PAYMENT OUTSTANDING
              </div>
            `))}
          </div>
        </div>
      </div>

      <!-- PAYMENTS HISTORY LEDGER TABLE (IF ANY PAYMENTS RECORDED) -->
      ${paymentsRowsHtml ? `
        <div style="padding:0 36px 16px;">
          <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-receipt"></i> Recorded Collections &amp; Payment Receipts
          </div>
          <table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">
            <thead>
              <tr style="background:#e2e8f0;color:#334155;font-size:11px;">
                <th style="padding:6px 12px;text-align:left;">Voucher / Receipt #</th>
                <th style="padding:6px 12px;text-align:left;"><?= e(__('table.date', 'Date')) ?></th>
                <th style="padding:6px 12px;text-align:left;">Payment Mode &amp; Ref</th>
                <th style="padding:6px 12px;text-align:right;">Amount (₹)</th>
              </tr>
            </thead>
            <tbody>
              ${paymentsRowsHtml}
            </tbody>
          </table>
        </div>
      ` : ''}

      <!-- SIGNATURES BLOCK -->
      <div class="rc-signatures-wrap" style="padding:14px 36px 20px;display:flex;justify-content:space-between;align-items:flex-end;gap:30px;border-top:1px solid #e5e7eb;position:relative;z-index:2;">
        <div style="width:200px;text-align:center;">
          <div style="height:44px;"></div>
          <div style="border-top:1.5px solid #cbd5e1;padding-top:6px;font-size:11.5px;font-weight:700;color:#0f172a;">Customer Signature</div>
          <div style="font-size:10px;color:#94a3b8;margin-top:1px;">Accepted &bull; Received in good order</div>
        </div>
        <div style="width:220px;text-align:center;">
          <div style="height:44px;display:flex;align-items:flex-end;justify-content:center;">
            ${sigSrc ? `<img src="${sigSrc}" style="max-height:42px;max-width:180px;object-fit:contain;" alt="Signature">` : ''}
          </div>
          <div style="border-top:1.5px solid #cbd5e1;padding-top:6px;font-size:11.5px;font-weight:700;color:#0f172a;">For ${d.coName}</div>
          <div style="font-size:10px;color:#94a3b8;margin-top:1px;">Authorized Signatory / Cashier</div>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="rc-footer-wrap" style="background:${footerBg};border-top:3px solid ${c};padding:12px 36px;display:flex;justify-content:space-between;align-items:center;font-size:10.5px;color:#555;position:relative;z-index:2;">
        <span>${d.coEmail}</span>
        <span style="font-weight:600;color:${c};">Computer Generated Tax Invoice &bull; Valid Without Physical Seal</span>
        <span>${phones}</span>
      </div>
    `;
  }

  function renderInvoice(c) {
    document.getElementById('receiptPaper').innerHTML = buildInvoice(c);
  }

  // Render immediately for instant zero-flash display
  renderInvoice(selectedColor);

  // Generate QR code and update dynamic render
  generateQR().then(url => {
    qrDataUrl = url;
    renderInvoice(selectedColor);
  }).catch(() => {
    renderInvoice(selectedColor);
  });

  // Color Swatches Interactive Handler
  document.querySelectorAll('.swatch').forEach(sw => {
    sw.addEventListener('click', function () {
      document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
      this.classList.add('active');
      selectedColor = this.dataset.color;
      generateQR().then(url => {
        qrDataUrl = url;
        renderInvoice(selectedColor);
      });
    });
  });

  // Iframe print trigger (exact same as PMS print-receipt.php)
  function triggerPrint() {
    const invoiceHTML = buildInvoice(selectedColor);
    const printHTML = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Tax Invoice</title><style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:\'Plus Jakarta Sans\',Arial,sans-serif;background:#fff;color:#111;}@page{size:A4 portrait;margin:10mm;}*{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important;}</style></head><body>' + invoiceHTML + '</body></html>';
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

  document.getElementById('printBtn').addEventListener('click', triggerPrint);

  // Shortcut Ctrl+P
  window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
      e.preventDefault();
      triggerPrint();
    }
  });

  function shareInvoice() {
    const shareUrl = d.verifyUrl || window.location.href;
    if (navigator.share) {
      navigator.share({
        title: d.coName + ' - Invoice ' + d.invNo,
        text: 'Official Tax Invoice from ' + d.coName + ' for ' + d.customerName,
        url: shareUrl
      }).catch(() => {});
    } else {
      navigator.clipboard.writeText(shareUrl).then(() => {
        alert('Invoice digital link copied to clipboard!');
      });
    }
  }
</script>
</body>
</html>
