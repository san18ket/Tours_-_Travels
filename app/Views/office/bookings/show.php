<?php
$bookingActions = '<div style="display:flex;gap:0.65rem;flex-wrap:wrap;align-items:center;">'
    . '<a href="' . url('/office/bookings') . '" class="btn btn-outline btn-sm">Back to List</a>';
if ($booking['invoice_id']) {
    $bookingActions .= '<a href="' . url('/office/invoices/' . $booking['invoice_id']) . '" class="btn btn-primary btn-sm">'
        . 'View Tax Invoice ' . e($booking['invoice_number']) . '</a>';
} elseif (in_array(auth_role(), ['admin', 'dispatcher'], true)) {
    $bookingActions .= '<form action="' . url('/office/invoices/generate/' . $booking['id']) . '" method="POST" style="margin:0;">'
        . csrf_field() . '<button type="submit" class="btn btn-navy btn-sm">Generate Tax Invoice</button></form>';
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
    'image'       => 'header-bookings.jpg',
    'badge'       => ucfirst(str_replace('_', ' ', $booking['status'])),
]) ?>

<div class="vg-detail-grid">
    <!-- Left Column: Itinerary & Dispatch Assignment -->
    <div>
        <!-- Route & Guest Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Itinerary & Route Details</span>
                <span class="badge badge-info"><?= (int)$booking['passenger_count'] ?> Guests</span>
            </div>
            <div class="card-body">
                <div class="vg-split-2" style="margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-text-muted); text-transform: uppercase;">Guest Client</div>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['customer_name']) ?></div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><?= e($booking['customer_phone'] ?? '—') ?> • <?= e($booking['customer_email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--vg-text-muted); text-transform: uppercase;">Travel Schedule</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--vg-navy-900);">
                            <?= format_date($booking['start_date']) ?> &rarr; <?= format_date($booking['end_date']) ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">Pickup at <?= e($booking['pickup_time']) ?></div>
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
                        <strong style="font-size: 0.82rem; color: var(--vg-navy-900);">Special Guest Notes & Preferences:</strong>
                        <p style="margin: 0.35rem 0 0; font-size: 0.88rem; color: var(--vg-text-secondary);"><?= nl2br(e($booking['special_requests'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fleet Dispatch Assignment Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Chauffeur & Fleet Assignment</span>
                <?php if ($booking['dispatch_id']): ?>
                    <?= render_status_badge('assigned') ?>
                <?php else: ?>
                    <span class="badge badge-warning">Unassigned</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($booking['dispatch_id']): ?>
                    <div class="vg-split-2" style="align-items: center;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div class="stat-icon info" style="width: 44px; height: 44px;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Assigned Vehicle</div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['make_model']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--vg-blue-500); font-weight: 600;"><code><?= e($booking['vehicle_number']) ?></code></div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div class="stat-icon success" style="width: 44px; height: 44px;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Designated Chauffeur</div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($booking['driver_name']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--vg-text-muted);"><a href="tel:<?= e($booking['driver_phone']) ?>"><?= e($booking['driver_phone']) ?></a></div>
                            </div>
                        </div>
                    </div>
                <?php elseif (in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
                    <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin-bottom: 1.25rem;">
                        This reservation has not been dispatched yet. Select an available chauffeur and vehicle to deploy.
                    </p>

                    <form action="<?= url('/office/dispatch/assign') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="vehicle_id">Select Ready Vehicle *</label>
                                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                                    <option value="">Choose Available Vehicle</option>
                                    <?php foreach ($availableVehicles as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= e($v['make_model']) ?> (<?= e($v['vehicle_number']) ?> - <?= (int)$v['seating_capacity'] ?> seats)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="driver_id">Select Ready Chauffeur *</label>
                                <select id="driver_id" name="driver_id" class="form-select" required>
                                    <option value="">Choose On-Duty Chauffeur</option>
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
                        Chauffeur assignment is currently being processed by dispatch control.
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
                <span class="card-title">Fare Breakdown</span>
                <?= render_status_badge($booking['payment_status'] ?? 'unpaid') ?>
            </div>
            <div class="card-body" style="font-size: 0.92rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Base Tour Subtotal:</span>
                    <span><?= format_currency((float)$booking['subtotal']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Discount Applied:</span>
                    <span style="color: var(--vg-success);">- <?= format_currency((float)$booking['discount_amount']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Goods & Services Tax (5% GST):</span>
                    <span><?= format_currency((float)$booking['tax_amount']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid var(--vg-pearl-200); font-size: 1.15rem; font-weight: 800; color: var(--vg-navy-900); margin-top: 0.5rem;">
                    <span>Total Fare:</span>
                    <span style="color: var(--vg-blue-500);"><?= format_currency((float)$booking['total_amount']) ?></span>
                </div>

                <?php if ($booking['invoice_id']): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--vg-pearl-200); display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: var(--vg-text-muted);">Amount Collected:</span>
                        <strong style="color: var(--vg-success);"><?= format_currency((float)$booking['paid_amount']) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Lifecycle State Actions (Staff Only) -->
        <?php if (in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Operational Controls</span>
            </div>
            <div class="card-body">
                <form action="<?= url('/office/bookings/' . $booking['id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="status">Update Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="assigned" <?= $booking['status'] === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                            <option value="in_progress" <?= $booking['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reason">Status Change Reason</label>
                        <input type="text" id="reason" name="reason" class="form-control" placeholder="Optional audit memo">
                    </div>

                    <button type="submit" class="btn btn-outline btn-block btn-sm">
                        Apply Status Update
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
