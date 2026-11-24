<?php
/**
 * Tours & Travels — Official Driver Payment Receipt View (Office Panel)
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
$amtInWords = convertAmountToWordsINR($amountPaid);

$companyName = company_display_name();
$tagline = company_tagline() ?: 'Premium Commercial Travel & Logistics';
$logoUrl = company_logo_url();
$address = company_full_address() ?: company_address();
$phone = company_phone();
$email = company_email();
$website = company_website();
$gstin = company_gst_number();
$signature = company_signature_url();
$paidStamp = company_paid_stamp_url();

$printUrl = url('/office/finance/driver-payments/receipt/' . (int)$receipt['id'] . '/print');

$actionsHtml = '<div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">'
    . '<a href="' . url('/office/finance/driver-payments') . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> <span>Back to Driver Payments</span></a>'
    . '<a href="' . $printUrl . '" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-print"></i> <span>Print Receipt (A4)</span></a>'
    . '<a href="' . $printUrl . '" target="_blank" class="btn btn-outline btn-sm"><i class="fa-solid fa-download"></i> <span>Save as PDF</span></a>';

if (auth_role() === 'admin' && !$isReversed) {
    $actionsHtml .= '<button type="button" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FCA5A5;" onclick="openReverseModal()"><i class="fa-solid fa-rotate-left"></i> <span>Reverse Payment</span></button>';
}
$actionsHtml .= '</div>';
?>

<div class="bookings-page-view finance-page-view" style="max-width: 920px; margin: 0 auto; padding-bottom: 2rem;">
    <!-- Page Header with Breadcrumbs & Actions -->
    <?= render_page_header([
        'title'       => 'Payment Receipt ' . e($receiptNum),
        'description' => 'Official trip compensation voucher issued to driver ' . e($receipt['driver_name'] ?? 'Driver') . ' for Dispatch #' . e($receipt['dispatch_code'] ?: $receipt['dispatch_id']),
        'breadcrumbs' => [
            ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
            ['label' => 'Finance Overview', 'url' => url('/office/finance/dashboard')],
            ['label' => 'Driver Payments', 'url' => url('/office/finance/driver-payments')],
            ['label' => e($receiptNum), 'url' => ''],
        ],
        'actions'     => $actionsHtml,
        'badge'       => $isReversed ? 'Reversed' : ($isFullyPaid ? 'Fully Paid' : 'Partially Paid'),
    ]) ?>

    <!-- Status Banner -->
    <?php if ($isReversed): ?>
        <div style="background: #FFF1F2; border: 1.5px solid #FECDD3; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; color: #991B1B;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.35rem; margin-top: 2px;"></i>
            <div>
                <strong style="font-size: 0.95rem;">This Payment Voucher has been Officially Reversed</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #7F1D1D;">
                    Reversed on <strong><?= !empty($receipt['reversed_at']) ? date('d M Y, h:i A', strtotime($receipt['reversed_at'])) : 'N/A' ?></strong>
                    by <strong><?= e($receipt['reversed_by_name'] ?? 'Administrator') ?></strong>.
                    <?php if (!empty($receipt['reversal_reason'])): ?>
                        <br>Reason: <em><?= e($receipt['reversal_reason']) ?></em>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <div style="background: rgba(33, 135, 101, 0.08); border: 1.5px solid rgba(33, 135, 101, 0.25); border-radius: 12px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; font-size: 0.86rem; color: #166534;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-shield-check" style="font-size: 1.2rem; color: #218765;"></i>
                <span><strong>Verified Driver Payment Record:</strong> Fully balanced against company treasury accounts with an immutable audit trail.</span>
            </div>
            <div style="font-family: monospace; font-weight: 700; color: #0F172A;">
                Voucher Ref: <?= e($receipt['payment_reference']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Official Corporate Document Card -->
    <div class="vg-card" style="padding: 2.5rem 2.75rem; background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06); position: relative; overflow: hidden;">
        
        <?php if ($isReversed): ?>
            <div style="position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; font-weight: 900; color: rgba(220, 38, 38, 0.08); letter-spacing: 12px; text-transform: uppercase; pointer-events: none; user-select: none;">
                REVERSED
            </div>
        <?php endif; ?>

        <!-- Letterhead Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 2px solid #0F172A; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.15rem; max-width: 60%;">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($companyName) ?>" style="max-height: 56px; max-width: 140px; object-fit: contain;" onerror="this.style.display='none'">
                <?php endif; ?>
                <div>
                    <h1 style="font-size: 1.35rem; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2; letter-spacing: -0.4px;"><?= e($companyName) ?></h1>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #B45309; text-transform: uppercase; margin-top: 2px;"><?= e($tagline) ?></div>
                    <div style="font-size: 0.75rem; color: #475569; margin-top: 3px; line-height: 1.35;">
                        <?= e($address) ?><br>
                        Phone: <strong><?= e($phone) ?></strong> &bull; Email: <?= e($email) ?>
                        <?php if (!empty($gstin)): ?> &bull; GSTIN: <strong><?= e($gstin) ?></strong><?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px;">Driver Payment Voucher</div>
                <div style="font-size: 0.76rem; font-weight: 600; color: #B45309; text-transform: uppercase; margin-top: 2px;">Internal Settlement Receipt</div>
                <div style="display: inline-block; margin-top: 6px; padding: 4px 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; font-family: monospace; font-size: 0.9rem; font-weight: 700; color: #0F172A;">
                    <?= e($receiptNum) ?>
                </div>
                <div style="margin-top: 6px;">
                    <?php if ($isReversed): ?>
                        <span style="display: inline-block; font-size: 0.72rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; background: #FEE2E2; color: #B91C1C; text-transform: uppercase;">
                            <i class="fa-solid fa-ban"></i> Reversed
                        </span>
                    <?php elseif ($isFullyPaid): ?>
                        <span style="display: inline-block; font-size: 0.72rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; background: #DCFCE7; color: #166534; text-transform: uppercase;">
                            <i class="fa-solid fa-circle-check"></i> Fully Settled
                        </span>
                    <?php else: ?>
                        <span style="display: inline-block; font-size: 0.72rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; background: #FEF3C7; color: #B45309; text-transform: uppercase;">
                            <i class="fa-solid fa-clock"></i> Partial Payment
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 2-Column Information: Driver & Trip Reference -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
            <!-- Driver Box -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.74rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 0.5rem; padding-bottom: 0.4rem; border-bottom: 1px solid #E2E8F0;">
                    <i class="fa-solid fa-user-tie" style="color: #B45309; margin-right: 4px;"></i> Driver Details
                </div>
                <table style="width: 100%; font-size: 0.82rem; border-collapse: collapse;">
                    <tr>
                        <td style="color: #64748B; width: 40%; padding: 3px 0;">Driver Name:</td>
                        <td style="font-weight: 700; color: #0F172A; padding: 3px 0;"><?= e($receipt['driver_name'] ?? 'Driver') ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">Employee Code:</td>
                        <td style="font-weight: 700; color: #0F172A; padding: 3px 0;"><?= !empty($receipt['employee_code']) ? e($receipt['employee_code']) : ('DRV-' . str_pad((string)$receipt['driver_id'], 4, '0', STR_PAD_LEFT)) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">Contact Phone:</td>
                        <td style="font-weight: 600; color: #0F172A; padding: 3px 0;"><?= e($receipt['driver_phone'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">License Number:</td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #0F172A; padding: 3px 0;"><?= e($receipt['license_number'] ?? 'N/A') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Trip Box -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.74rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 0.5rem; padding-bottom: 0.4rem; border-bottom: 1px solid #E2E8F0;">
                    <i class="fa-solid fa-route" style="color: #B45309; margin-right: 4px;"></i> Trip &amp; Vehicle Reference
                </div>
                <table style="width: 100%; font-size: 0.82rem; border-collapse: collapse;">
                    <tr>
                        <td style="color: #64748B; width: 40%; padding: 3px 0;">Dispatch Code:</td>
                        <td style="font-family: monospace; font-weight: 700; color: #B45309; padding: 3px 0;"><?= e($receipt['dispatch_code'] ?: ('#DSP-' . $receipt['dispatch_id'])) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">Booking Reference:</td>
                        <td style="font-weight: 600; color: #0F172A; padding: 3px 0;"><?= !empty($receipt['booking_code']) ? e($receipt['booking_code']) : 'Direct Dispatch' ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">Trip Date:</td>
                        <td style="font-weight: 600; color: #0F172A; padding: 3px 0;"><?= !empty($receipt['trip_date']) ? date('d M Y', strtotime($receipt['trip_date'])) : (!empty($receipt['start_time']) ? date('d M Y', strtotime($receipt['start_time'])) : 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748B; padding: 3px 0;">Vehicle:</td>
                        <td style="font-weight: 700; color: #0F172A; padding: 3px 0;">
                            <?= e($receipt['vehicle_number'] ?? 'Vehicle N/A') ?>
                            <?= !empty($receipt['make_model']) ? '<span style="font-weight: normal; color: #64748B;">(' . e($receipt['make_model']) . ')</span>' : '' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Route Bar -->
        <div style="background: #F1F5F9; border-radius: 8px; padding: 0.6rem 0.95rem; font-size: 0.82rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; color: #334155;">
            <i class="fa-solid fa-location-dot" style="color: #218765;"></i>
            <strong>Route:</strong>
            <span><?= e($receipt['pickup_address'] ?? 'Start') ?></span>
            <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem; color: #94A3B8;"></i>
            <span><?= e($receipt['drop_address'] ?? 'End') ?></span>
        </div>

        <!-- Financial Breakdown Table -->
        <div style="margin-bottom: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
                <thead>
                    <tr style="background: #0F172A; color: #FFFFFF;">
                        <th style="padding: 9px 14px; text-align: left; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Financial Settlement Breakdown</th>
                        <th style="padding: 9px 14px; text-align: right; width: 32%; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 8px 14px; color: #334155;">
                            <strong>1. Total Agreed Driver Trip Earnings</strong>
                            <div style="font-size: 0.74rem; color: #64748B;">Approved compensation for Dispatch <?= e($receipt['dispatch_code']) ?></div>
                        </td>
                        <td style="padding: 8px 14px; text-align: right; font-family: monospace; font-weight: 700; color: #0F172A;">
                            <?= format_inr($totalTripAmount) ?>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 8px 14px; color: #334155;">
                            <span>2. Previously Paid Amount</span>
                            <div style="font-size: 0.74rem; color: #64748B;">Disbursements made prior to this voucher</div>
                        </td>
                        <td style="padding: 8px 14px; text-align: right; font-family: monospace; font-weight: 600; color: #64748B;">
                            <?= format_inr($previouslyPaid) ?>
                        </td>
                    </tr>
                    <tr style="background: rgba(217, 119, 6, 0.08); border-top: 1.5px solid #D97706; border-bottom: 1.5px solid #D97706;">
                        <td style="padding: 11px 14px;">
                            <span style="font-weight: 800; color: #0F172A; font-size: 0.95rem;">3. CURRENT PAYMENT AMOUNT</span>
                            <span style="display: inline-block; background: #D97706; color: #FFFFFF; font-size: 0.68rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">Voucher Payout</span>
                            <div style="font-size: 0.76rem; color: #B45309; font-weight: 600; margin-top: 2px;">
                                Paid via <?= ucwords(str_replace('_', ' ', $receipt['payment_method'] ?? 'Bank Transfer')) ?>
                                &bull; Account: <?= e($receipt['account_name'] ?? 'Company Vault') ?>
                                <?php if (!empty($receipt['transaction_ref'])): ?>
                                    &bull; Ref/UTR: <span style="font-family: monospace;"><?= e($receipt['transaction_ref']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding: 11px 14px; text-align: right; font-family: monospace; font-weight: 800; font-size: 1.2rem; color: <?= $isReversed ? '#DC2626' : '#166534' ?>;">
                            <?= format_inr($amountPaid) ?>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 8px 14px; color: #334155;">
                            <span>4. Total Paid to Date</span>
                            <div style="font-size: 0.74rem; color: #64748B;">Cumulative amount received including this voucher</div>
                        </td>
                        <td style="padding: 8px 14px; text-align: right; font-family: monospace; font-weight: 700; color: #0F172A;">
                            <?= format_inr($totalPaidToDate) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 14px; color: #334155;">
                            <strong style="color: #0F172A;">5. Still to Pay (Remaining Balance Due)</strong>
                            <div style="font-size: 0.74rem; color: #64748B;">Unpaid balance awaiting settlement</div>
                        </td>
                        <td style="padding: 8px 14px; text-align: right; font-family: monospace; font-weight: 800; font-size: 0.95rem; color: <?= $remainingDue > 0 ? '#B45309' : '#166534' ?>;">
                            <?= format_inr($remainingDue) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- In Words Box -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.65rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: baseline; gap: 0.6rem; font-size: 0.82rem;">
            <span style="font-weight: 700; color: #475569; font-size: 0.74rem; text-transform: uppercase;">Amount in Words:</span>
            <span style="font-weight: 700; color: #0F172A; font-style: italic;"><?= e($amtInWords) ?></span>
        </div>

        <?php if (!empty($receipt['notes'])): ?>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.65rem 1rem; font-size: 0.8rem; color: #334155; margin-bottom: 1.5rem;">
                <strong>Voucher Memo / Remarks:</strong> <?= e($receipt['notes']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($receipt['attachment_file'])): ?>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.65rem 1rem; font-size: 0.8rem; color: #334155; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <i class="fa-solid fa-paperclip" style="color: #2563EB; margin-right: 4px;"></i>
                    <strong>Payment Proof Attachment:</strong>
                    <span style="color: #64748B;"><?= basename($receipt['attachment_file']) ?></span>
                </div>
                <a href="<?= url('/storage/' . $receipt['attachment_file']) ?>" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>View Attachment</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Signatures & Authorization -->
        <div style="margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid #E2E8F0; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; text-align: center;">
            <div>
                <div style="height: 48px; display: flex; align-items: flex-end; justify-content: center; font-size: 0.78rem; color: #475569; font-weight: 600;">
                    <?= e($receipt['created_by_name'] ?? 'Office Staff') ?>
                </div>
                <div style="border-top: 1.5px dashed #CBD5E1; padding-top: 4px; font-size: 0.74rem; font-weight: 700; color: #0F172A; text-transform: uppercase;">
                    Recorded By
                </div>
                <div style="font-size: 0.68rem; color: #64748B; margin-top: 2px;">
                    <?= date('d M Y, h:i A', strtotime($receipt['created_at'])) ?>
                </div>
            </div>

            <div>
                <div style="height: 48px; display: flex; align-items: flex-end; justify-content: center;">
                    <?php if (!empty($signature)): ?>
                        <img src="<?= e($signature) ?>" alt="Authorized Signatory" style="max-height: 44px; max-width: 120px; object-fit: contain;">
                    <?php elseif (!empty($paidStamp)): ?>
                        <img src="<?= e($paidStamp) ?>" alt="Paid Stamp" style="max-height: 44px; max-width: 120px; object-fit: contain;">
                    <?php else: ?>
                        <span style="font-size: 0.72rem; color: #94A3B8;">— Certified Payout —</span>
                    <?php endif; ?>
                </div>
                <div style="border-top: 1.5px dashed #CBD5E1; padding-top: 4px; font-size: 0.74rem; font-weight: 700; color: #0F172A; text-transform: uppercase;">
                    Authorized Signatory
                </div>
                <div style="font-size: 0.68rem; color: #64748B; margin-top: 2px;">
                    For <?= e($companyName) ?>
                </div>
            </div>

            <div>
                <div style="height: 48px; display: flex; align-items: flex-end; justify-content: center; font-size: 0.74rem; color: #94A3B8; font-style: italic;">
                    Driver Signature
                </div>
                <div style="border-top: 1.5px dashed #CBD5E1; padding-top: 4px; font-size: 0.74rem; font-weight: 700; color: #0F172A; text-transform: uppercase;">
                    Driver Acknowledgement
                </div>
                <div style="font-size: 0.68rem; color: #64748B; margin-top: 2px;">
                    Received with thanks
                </div>
            </div>
        </div>

        <!-- Document Footer -->
        <div style="margin-top: 1.75rem; padding-top: 0.85rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; color: #64748B;">
            <div>&bull; Official Driver Payment Voucher &bull; Internal accounting record &bull; Not a customer tax invoice.</div>
            <div>Computer Generated Document &bull; Voucher Ref: <span style="font-family: monospace; font-weight: 700;"><?= e($receipt['payment_reference']) ?></span></div>
        </div>
    </div>
</div>

<!-- Reversal Confirmation Modal (Admin only) -->
<?php if (auth_role() === 'admin' && !$isReversed): ?>
<div class="modal-backdrop-custom" id="reversePaymentModal" style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #FFF1F2; border-bottom: 1px solid #FECDD3;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: #991B1B;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #991B1B;">Reverse Driver Payment</h3>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeReverseModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/driver-payments/reverse/' . (int)$receipt['id']) ?>" method="POST" onsubmit="return validateReverseForm()">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e(url('/office/finance/driver-payments/receipt/' . (int)$receipt['id'])) ?>">
            <div class="modal-body-custom" style="padding: 1.25rem 1.35rem;">
                <p style="font-size: 0.84rem; color: #334155; line-height: 1.45; margin-bottom: 1rem;">
                    You are reversing payment voucher <strong><?= e($receiptNum) ?></strong> of <strong><?= format_inr($amountPaid) ?></strong>.
                    <br><br>
                    This will credit <strong><?= format_inr($amountPaid) ?></strong> back to <strong><?= e($receipt['account_name'] ?? 'Company Vault') ?></strong> and restore the driver's remaining balance on Dispatch <strong><?= e($receipt['dispatch_code']) ?></strong>.
                </p>
                <div class="form-group">
                    <label class="form-label" for="reversal_reason" style="font-weight: 700; color: #0F172A;">
                        Reversal Reason <span class="req">*</span>
                    </label>
                    <textarea name="reason" id="reversal_reason" class="form-control" rows="3" placeholder="State reason for reversing this payment (e.g. entered wrong account, duplicate entry, incorrect amount)..." required></textarea>
                </div>
            </div>
            <div class="modal-footer-custom" style="padding: 1rem 1.35rem; display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #E2E8F0; background: #F8FAFC;">
                <button type="button" class="btn btn-outline" onclick="closeReverseModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" style="background: #DC2626; border-color: #DC2626; color: #FFFFFF;">
                    <i class="fa-solid fa-rotate-left"></i> <span>Confirm Reversal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReverseModal() {
        document.getElementById('reversePaymentModal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeReverseModal() {
        document.getElementById('reversePaymentModal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    function validateReverseForm() {
        var reason = document.getElementById('reversal_reason').value.trim();
        if (!reason) {
            alert('Please provide a reason for reversing this payment.');
            return false;
        }
        return confirm('Are you sure you want to reverse this payment voucher? This action will restore company funds and update financial records.');
    }
</script>
<?php endif; ?>

