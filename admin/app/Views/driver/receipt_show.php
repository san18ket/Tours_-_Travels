<?php
/**
 * TOURS & TRAVELS — DRIVER PORTAL PAYMENT RECEIPT VIEW
 * Scoped to authenticated driver only
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
$printUrl = url('/driver/payments/receipt/' . (int)$receipt['id'] . '/print');
?>

<div class="driver-container" style="max-width: 820px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <a href="<?= url('/driver/payments') ?>" class="btn btn-outline" style="font-size: 0.82rem;">
            <i class="fa-solid fa-arrow-left"></i> <span>Back to Payments</span>
        </a>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= $printUrl ?>" target="_blank" class="btn btn-primary" style="font-size: 0.82rem;">
                <i class="fa-solid fa-print"></i> <span>Print Receipt</span>
            </a>
            <a href="<?= $printUrl ?>" target="_blank" class="btn btn-outline" style="font-size: 0.82rem;">
                <i class="fa-solid fa-download"></i> <span>Save as PDF</span>
            </a>
        </div>
    </div>

    <!-- Official Document Card -->
    <div class="vg-card" style="padding: 2rem 2.25rem; background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 16px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05); position: relative; overflow: hidden;">
        
        <?php if ($isReversed): ?>
            <div style="position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 4.5rem; font-weight: 900; color: rgba(220, 38, 38, 0.08); letter-spacing: 10px; text-transform: uppercase; pointer-events: none; user-select: none;">
                REVERSED
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.25rem; border-bottom: 2px solid #0F172A; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1rem; max-width: 60%;">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($companyName) ?>" style="max-height: 48px; max-width: 120px; object-fit: contain;" onerror="this.style.display='none'">
                <?php endif; ?>
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;"><?= e($companyName) ?></h2>
                    <div style="font-size: 0.74rem; font-weight: 700; color: #B45309; text-transform: uppercase; margin-top: 2px;"><?= e($tagline) ?></div>
                    <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">Phone: <?= e($phone) ?></div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 1.05rem; font-weight: 800; color: #0F172A; text-transform: uppercase;">Payment Voucher</div>
                <div style="display: inline-block; margin-top: 4px; padding: 3px 8px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; font-family: monospace; font-size: 0.85rem; font-weight: 700; color: #0F172A;">
                    <?= e($receiptNum) ?>
                </div>
                <div style="margin-top: 4px;">
                    <?php if ($isReversed): ?>
                        <span style="display: inline-block; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background: #FEE2E2; color: #B91C1C;">
                            Reversed
                        </span>
                    <?php elseif ($isFullyPaid): ?>
                        <span style="display: inline-block; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background: #DCFCE7; color: #166534;">
                            Fully Settled
                        </span>
                    <?php else: ?>
                        <span style="display: inline-block; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background: #FEF3C7; color: #B45309;">
                            Partial Payment
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Driver & Trip Meta -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.8rem;">
                <div style="font-weight: 800; color: #334155; text-transform: uppercase; font-size: 0.72rem; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-user-tie" style="color: #B45309;"></i> Driver Details
                </div>
                <div><strong><?= e($receipt['driver_name'] ?? 'Driver') ?></strong></div>
                <div style="color: #64748B; font-size: 0.75rem;">Code: <?= e($receipt['employee_code'] ?: ('DRV-' . $receipt['driver_id'])) ?></div>
                <div style="color: #64748B; font-size: 0.75rem;">Phone: <?= e($receipt['driver_phone'] ?? 'N/A') ?></div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.8rem;">
                <div style="font-weight: 800; color: #334155; text-transform: uppercase; font-size: 0.72rem; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-route" style="color: #B45309;"></i> Trip Reference
                </div>
                <div>Dispatch: <strong style="color: #B45309; font-family: monospace;"><?= e($receipt['dispatch_code'] ?: ('#DSP-' . $receipt['dispatch_id'])) ?></strong></div>
                <div style="color: #64748B; font-size: 0.75rem;">Date: <?= !empty($receipt['trip_date']) ? date('d M Y', strtotime($receipt['trip_date'])) : 'N/A' ?></div>
                <div style="color: #64748B; font-size: 0.75rem;">Vehicle: <?= e($receipt['vehicle_number'] ?? 'Vehicle N/A') ?></div>
            </div>
        </div>

        <!-- Route -->
        <div style="background: #F1F5F9; border-radius: 6px; padding: 0.5rem 0.75rem; font-size: 0.78rem; margin-bottom: 1.25rem; color: #334155;">
            <strong>Route:</strong> <?= e($receipt['pickup_address'] ?? 'Start') ?> &rarr; <?= e($receipt['drop_address'] ?? 'End') ?>
        </div>

        <!-- Financial Breakdown -->
        <div style="margin-bottom: 1.25rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.84rem; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr style="background: #0F172A; color: #FFFFFF; font-size: 0.74rem;">
                        <th style="padding: 8px 12px; text-align: left; text-transform: uppercase;"><?= e(__('table.payment_breakdown', 'Payment Breakdown')) ?></th>
                        <th style="padding: 8px 12px; text-align: right; width: 35%; text-transform: uppercase;"><?= e(__('table.amount', 'Amount')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 7px 12px; color: #334155;">1. Total Agreed Trip Earnings</td>
                        <td style="padding: 7px 12px; text-align: right; font-family: monospace; font-weight: 700;"><?= format_inr($totalTripAmount) ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 7px 12px; color: #64748B;">2. Previously Received</td>
                        <td style="padding: 7px 12px; text-align: right; font-family: monospace; color: #64748B;"><?= format_inr($previouslyPaid) ?></td>
                    </tr>
                    <tr style="background: rgba(217, 119, 6, 0.08); border-top: 1.5px solid #D97706; border-bottom: 1.5px solid #D97706;">
                        <td style="padding: 10px 12px;">
                            <strong style="color: #0F172A; font-size: 0.92rem;">3. THIS PAYMENT AMOUNT</strong>
                            <div style="font-size: 0.74rem; color: #B45309; margin-top: 2px;">
                                Method: <?= ucwords(str_replace('_', ' ', $receipt['payment_method'] ?? 'Bank Transfer')) ?>
                                <?php if (!empty($receipt['transaction_ref'])): ?>
                                    &bull; Ref/UTR: <?= e($receipt['transaction_ref']) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding: 10px 12px; text-align: right; font-family: monospace; font-weight: 800; font-size: 1.15rem; color: <?= $isReversed ? '#DC2626' : '#166534' ?>;">
                            <?= format_inr($amountPaid) ?>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 7px 12px; color: #334155;">4. Total Received to Date</td>
                        <td style="padding: 7px 12px; text-align: right; font-family: monospace; font-weight: 700;"><?= format_inr($totalPaidToDate) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 7px 12px; font-weight: 700; color: #0F172A;">5. Still to Receive (Remaining Due)</td>
                        <td style="padding: 7px 12px; text-align: right; font-family: monospace; font-weight: 800; color: <?= $remainingDue > 0 ? '#B45309' : '#166534' ?>;">
                            <?= format_inr($remainingDue) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.78rem; margin-bottom: 1.25rem;">
            <strong>Amount in Words:</strong> <span style="font-style: italic;"><?= e($amtInWords) ?></span>
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #E2E8F0; padding-top: 0.75rem; display: flex; justify-content: space-between; font-size: 0.68rem; color: #64748B;">
            <div>&bull; Official Driver Payment Voucher &bull; <?= e($companyName) ?></div>
            <div>Date: <?= date('d M Y', strtotime($receipt['payment_date'])) ?></div>
        </div>
    </div>
</div>

