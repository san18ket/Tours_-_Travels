<?php
$bookingActions = '<div style="display:flex;gap:0.65rem;flex-wrap:wrap;align-items:center;">'
    . '<a href="' . url('/office/bookings') . '" class="btn btn-outline btn-sm">' . e(__('btn.back', 'Back to List')) . '</a>';
if (in_array(auth_role(), ['admin', 'dispatcher'], true)) {
    $bookingActions .= '<a href="' . url('/office/bookings/' . $booking['id'] . '/edit') . '" class="btn btn-outline btn-sm">'
        . '<i class="fa-solid fa-pen-to-square" style="margin-right:4px;"></i> Edit Booking</a>';
}
if ($booking['invoice_id']) {
    $bookingActions .= '<a href="' . url('/office/invoices/' . $booking['invoice_id']) . '" class="btn btn-primary btn-sm">'
        . '<i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> View Invoice ' . e($booking['invoice_number']) . '</a>';
    $bookingActions .= '<a href="' . url('/office/invoices/' . $booking['invoice_id'] . '/print') . '" target="_blank" class="btn btn-outline btn-sm">'
        . '<i class="fa-solid fa-print" style="margin-right:4px;"></i> Print Invoice</a>';
} elseif (in_array(auth_role(), ['admin', 'dispatcher'], true)) {
    $bookingActions .= '<form action="' . url('/office/invoices/generate/' . $booking['id']) . '" method="POST" style="margin:0;">'
        . csrf_field() . '<button type="submit" class="btn btn-navy btn-sm"><i class="fa-solid fa-file-invoice" style="margin-right:4px;"></i> Generate Invoice</button></form>';
}
$bookingActions .= '</div>';
?>
<?= render_page_header([
    'title'       => 'Reservation ' . e($booking['booking_code']),
    'subtitle'    => 'Created on ' . format_datetime($booking['created_at']) . ' • Guest: ' . e($booking['customer_name']),
    'breadcrumbs' => [
        ['label' => 'Bookings', 'url' => url('/office/bookings')],
        ['label' => e($booking['booking_code']), 'url' => ''],
    ],
    'actions'     => $bookingActions,
    'badge'       => ucfirst(str_replace('_', ' ', $booking['status'])),
]) ?>

<div class="vg-detail-grid">
    <!-- Left Column: Itinerary & Dispatch Assignment -->
    <div>
        <!-- Route & Guest Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title"><?= e(__('booking.itinerary_route_details', 'Itinerary & Route Details')) ?></span>
                <span class="badge badge-info"><?= (int)$booking['passenger_count'] ?> <?= e(__('table.guests', 'Guests')) ?></span>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--vg-pearl-200);">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--vg-text-muted);text-transform:uppercase;letter-spacing:0.04em;"><?= e(__('booking.trip_category', 'Trip Category')) ?>:</span>
                        <span class="badge" style="background:rgba(30,58,138,0.08);color:#1e3a8a;border:1px solid rgba(30,58,138,0.22);font-weight:700;font-size:0.85rem;padding:0.35rem 0.75rem;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                            <i class="fa-solid fa-tag" style="font-size:0.78rem;"></i>
                            <?= e($booking['trip_category_name'] ?? 'Not specified') ?>
                        </span>
                    </div>
                    <?php if (!empty($booking['tour_title'])): ?>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--vg-text-muted);text-transform:uppercase;">Package / Tour:</span>
                        <span style="font-size:0.88rem;font-weight:600;color:var(--vg-navy-900);"><?= e($booking['tour_title']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="vg-split-2" style="margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-text-muted); text-transform: uppercase;"><?= e(__('booking.guest_client', 'Guest Client')) ?></div>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['customer_name']) ?></div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><?= e($booking['customer_phone'] ?? '—') ?> • <?= e($booking['customer_email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-text-muted); text-transform: uppercase;"><?= e(__('booking.journey_timetable', 'Travel Schedule')) ?></div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--vg-navy-900);">
                            <?= format_date($booking['start_date']) ?> &rarr; <?= format_date($booking['end_date']) ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><?= e(__('booking.pickup_time', 'Pickup at')) ?> <?= e($booking['pickup_time']) ?></div>
                    </div>
                </div>

                <div class="location-timeline">
                    <div class="location-point">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-blue-500); text-transform: uppercase;">Origin / Pickup Address</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($booking['pickup_address']) ?></div>
                    </div>
                    <div class="location-point destination">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-success); text-transform: uppercase;">Destination / Drop-off</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($booking['drop_address']) ?></div>
                    </div>
                </div>

                <?php if (!empty($booking['special_requests'])): ?>
                    <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.95rem; margin-top: 1.25rem;">
                        <strong style="font-size: 0.82rem; color: var(--vg-navy-900);"><?= e(__('booking.special_notes_preferences', 'Special Guest Notes & Preferences:')) ?></strong>
                        <p style="margin: 0.35rem 0 0; font-size: 0.88rem; color: var(--vg-text-secondary);"><?= nl2br(e($booking['special_requests'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fleet Dispatch Assignment Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title"><?= e(__('booking.fleet_assignment', 'Driver & Fleet Assignment')) ?></span>
                <?php if ($booking['dispatch_id']): ?>
                    <?= render_status_badge($booking['dispatch_status'] ?? 'assigned', 'dispatch') ?>
                <?php else: ?>
                    <span class="badge badge-warning"><?= e(__('booking.unassigned', 'Unassigned')) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($booking['dispatch_id']): ?>
                    <div class="vg-split-3" style="align-items: center; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div class="stat-icon info" style="width: 44px; height: 44px;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;"><?= e(__('booking.assigned_vehicle', 'Assigned Vehicle')) ?></div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['make_model']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--vg-blue-500); font-weight: 600;"><code><?= e($booking['vehicle_number']) ?></code></div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div class="stat-icon success" style="width: 44px; height: 44px;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;"><?= e(__('booking.designated_driver', 'Designated Driver')) ?></div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['driver_name']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><a href="tel:<?= e($booking['driver_phone']) ?>"><?= e($booking['driver_phone']) ?></a></div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div class="stat-icon warning" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                                <i class="fa-solid fa-indian-rupee-sign" style="font-size: 1.15rem; color: #D97706;"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;"><?= e(__('booking.driver_trip_amount', 'Driver Trip Amount')) ?></div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);">
                                    <?= ($booking['driver_trip_amount'] !== null && $booking['driver_trip_amount'] !== '') ? format_currency((float)$booking['driver_trip_amount']) : 'Not specified' ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><?= e(__('booking.operational_payout', 'Operational Payout')) ?></div>
                            </div>
                        </div>
                    </div>
                <?php elseif (in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
                    <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin-bottom: 1.25rem;">
                        This reservation has not been dispatched yet. Select an available driver and vehicle to deploy.
                    </p>

                    <form action="<?= url('/office/dispatch/assign') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="vehicle_id">Select Ready Vehicle *</label>
                                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                                    <option value=""><?= e(__('booking.choose_available_vehicle', 'Choose Available Vehicle')) ?></option>
                                    <?php foreach ($availableVehicles as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= e($v['make_model']) ?> (<?= e($v['vehicle_number']) ?> - <?= (int)$v['seating_capacity'] ?> seats)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="driver_id">Select Ready Driver *</label>
                                <select id="driver_id" name="driver_id" class="form-select" required>
                                    <option value=""><?= e(__('booking.choose_onduty_driver', 'Choose On-Duty Driver')) ?></option>
                                    <?php foreach ($availableDrivers as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= e($d['name']) ?> (<?= e($d['duty_status']) ?> - Rating: <?= e($d['rating']) ?>★)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-navy btn-sm" style="margin-top: 0.5rem;">
                            Deploy &amp; Dispatch Trip
                        </button>
                    </form>
                <?php else: ?>
                    <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">
                        Driver assignment is currently being processed by dispatch control.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Billing & Status Actions -->
    <div>
        <!-- Financial Breakdown Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title"><?= e(__('booking.fare_breakdown', 'Fare Breakdown')) ?></span>
                <?= render_status_badge($booking['payment_status'] ?? 'unpaid') ?>
            </div>
            <div class="card-body" style="font-size: 0.92rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);"><?= e(__('booking.base_tour_subtotal', 'Base Tour Subtotal:')) ?></span>
                    <span><?= format_currency((float)$booking['subtotal']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);"><?= e(__('booking.discount_applied', 'Discount Applied:')) ?></span>
                    <span style="color: var(--vg-success);">- <?= format_currency((float)$booking['discount_amount']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Goods & Services Tax (5% GST):</span>
                    <span><?= format_currency((float)$booking['tax_amount']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid var(--vg-pearl-200); font-size: 1.15rem; font-weight: 800; color: var(--vg-navy-900); margin-top: 0.5rem;">
                    <span><?= e(__('booking.base_fare', 'Booking Amount:')) ?></span>
                    <span style="color: var(--vg-blue-500);"><?= format_currency((float)$booking['total_amount']) ?></span>
                </div>

                <?php 
                    $totFare = (float)$booking['total_amount'];
                    $recFare = (float)($booking['paid_amount'] ?? 0);
                    $remFare = max(0.0, $totFare - $recFare);
                ?>
                <div style="display: flex; justify-content: space-between; margin-top: 0.65rem; font-size: 0.92rem;">
                    <span style="color: var(--vg-text-muted);"><?= e(__('booking.money_received', 'Money Received:')) ?></span>
                    <strong style="color: var(--vg-success);"><?= format_currency($recFare) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.65rem; font-size: 0.92rem;">
                    <span style="color: var(--vg-text-muted);"><?= e(__('booking.remaining_amount', 'Remaining Amount:')) ?></span>
                    <strong style="color: <?= $remFare > 0.001 ? '#dc2626' : 'var(--vg-success)' ?>;"><?= format_currency($remFare) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.65rem; font-size: 0.92rem;">
                    <span style="color: var(--vg-text-muted);"><?= e(__('status.payment_status', 'Payment Status:')) ?></span>
                    <?= render_status_badge($booking['payment_status'] ?? 'unpaid') ?>
                </div>

                <?php if ($booking['invoice_id'] && $remFare > 0.001 && in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
                    <div style="margin-top: 1.1rem; padding-top: 0.85rem; border-top: 1px dashed var(--vg-pearl-200);">
                        <a href="<?= url('/office/invoices/' . $booking['invoice_id']) ?>" class="btn btn-primary btn-sm btn-block" style="text-align:center; display:block;">
                            <i class="fa-solid fa-money-bill-wave" style="margin-right:5px;"></i> Record Remaining Payment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Lifecycle State Actions (Staff Only) -->
        <?php if (in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title"><?= e(__('booking.operational_controls', 'Operational Controls')) ?></span>
            </div>
            <div class="card-body">
                <form action="<?= url('/office/bookings/' . $booking['id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="status"><?= e(__('booking.update_status', 'Update Status')) ?></label>
                        <select id="status" name="status" class="form-select">
                            <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>><?= e(__('status.pending', 'Pending')) ?></option>
                            <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>><?= e(__('status.confirmed', 'Confirmed')) ?></option>
                            <option value="assigned" <?= $booking['status'] === 'assigned' ? 'selected' : '' ?>><?= e(__('status.assigned', 'Assigned')) ?></option>
                            <option value="in_progress" <?= $booking['status'] === 'in_progress' ? 'selected' : '' ?>><?= e(__('status.in_progress', 'In Progress')) ?></option>
                            <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>><?= e(__('status.completed', 'Completed')) ?></option>
                            <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>><?= e(__('status.cancelled', 'Cancelled')) ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reason"><?= e(__('booking.status_reason', 'Status Change Reason')) ?></label>
                        <input type="text" id="reason" name="reason" class="form-control" placeholder="<?= e(__('placeholder.optional_audit_memo', 'Optional audit memo')) ?>">
                    </div>

                    <button type="submit" class="btn btn-outline btn-block btn-sm">
                        <?= e(__('booking.update_status', 'Apply Status Update')) ?>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
