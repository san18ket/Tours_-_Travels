<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Fleet Dispatch Operations</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Live deployment control, chauffeur trip progress, odometer verifications, and route completions.</p>
    </div>
    <div>
        <a href="<?= url('/office/bookings') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Assign From Reservations</span>
        </a>
    </div>
</div>

<div class="vg-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="pulse-indicator"></span>
            <span class="card-title">Fleet Dispatches (<?= count($dispatches) ?>)</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= url('/office/dispatch') ?>" class="btn btn-outline btn-sm">All Dispatches</a>
            <a href="<?= url('/office/dispatch?status=trip_started') ?>" class="btn btn-outline btn-sm">In Transit</a>
            <a href="<?= url('/office/dispatch?status=completed') ?>" class="btn btn-outline btn-sm">Completed</a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($dispatches)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <div class="empty-state-title">No Dispatches Created</div>
                <p class="empty-state-desc">Assign pending reservations to available chauffeurs and vehicles to initiate dispatch.</p>
                <a href="<?= url('/office/bookings') ?>" class="btn btn-primary btn-sm">Go to Bookings</a>
            </div>
        <?php else: ?>
            <!-- Desktop Data Grid -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Dispatch Code</th>
                            <th>Reservation & Client</th>
                            <th>Chauffeur & Phone</th>
                            <th>Vehicle Details</th>
                            <th>Odometer (Start / End)</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dispatches as $d): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($d['dispatch_code']) ?></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">Assigned by <?= e($d['assigner_name']) ?></div>
                                </td>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><a href="<?= url('/office/bookings/' . $d['booking_id']) ?>"><?= e($d['booking_code']) ?></a></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($d['customer_name']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= e($d['driver_name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($d['driver_phone']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= e($d['make_model']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-blue-500);"><code><?= e($d['vehicle_number']) ?></code></div>
                                </td>
                                <td>
                                    <div><?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' km' : '—' ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">
                                        End: <?= $d['end_odometer'] ? number_format((int)$d['end_odometer']) . ' km' : 'In transit' ?>
                                    </div>
                                </td>
                                <td>
                                    <?= render_status_badge($d['status']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="btn btn-outline btn-sm">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($dispatches as $d): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($d['dispatch_code']) ?></span>
                                <div style="font-size:0.72rem; color:var(--vg-text-muted);">Bk: <?= e($d['booking_code']) ?></div>
                            </div>
                            <div>
                                <?= render_status_badge($d['status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Chauffeur</span>
                                <span class="vg-card-value"><?= e($d['driver_name']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Vehicle</span>
                                <span class="vg-card-value"><code><?= e($d['vehicle_number']) ?></code></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Start Odo</span>
                                <span class="vg-card-value"><?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' km' : '—' ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Client</span>
                                <span class="vg-card-value"><?= e($d['customer_name']) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="btn btn-outline btn-sm">View Booking</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
