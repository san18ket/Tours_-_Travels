<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.55rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em;">Command Operations Dashboard</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.92rem;">Real-time overview of active tours, vehicle dispatches, and financial ledger.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New Reservation</span>
        </a>
        <a href="<?= url('/office/dispatch') ?>" class="btn btn-navy">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Dispatch Fleet</span>
        </a>
    </div>
</div>

<!-- 4 Top Luxury KPI Cards -->
<div class="grid-cols-4">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Trips</div>
            <div class="stat-value">
                <?= $activeTrips ?>
                <?php if ($activeTrips > 0): ?>
                    <span class="pulse-indicator" style="vertical-align: middle; margin-left: 6px;"></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Reservations</div>
            <div class="stat-value"><?= $totalBookings ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Fleet Readiness</div>
            <div class="stat-value"><?= $availableFleet ?> <span style="font-size:0.82rem; font-weight:600; color:var(--vg-gold-500);">/ <?= $driversOnDuty ?> On Duty</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value" style="font-size: 1.35rem;"><?= format_currency($totalRevenue) ?></div>
        </div>
    </div>
</div>

<!-- Active Trips & Dispatch Status -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Active Dispatches Card -->
    <div class="vg-card" style="margin-bottom:0;">
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <span class="pulse-indicator"></span>
                <span class="card-title">Live Dispatch Radar</span>
            </div>
            <a href="<?= url('/office/dispatch') ?>" class="btn btn-outline btn-sm">Dispatch Board</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($activeDispatches)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </div>
                    <div class="empty-state-title">No Active Dispatches</div>
                    <p class="empty-state-desc">Assign pending reservations to available chauffeurs and vehicles.</p>
                    <a href="<?= url('/office/bookings') ?>" class="btn btn-primary btn-sm">Go to Bookings</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Trip Code</th>
                                <th>Vehicle & Chauffeur</th>
                                <th>Route</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeDispatches as $disp): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--vg-navy-900);"><?= e($disp['dispatch_code']) ?></strong>
                                        <div style="font-size:0.75rem; color:var(--vg-text-muted);">Ref: <?= e($disp['booking_code']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:var(--vg-navy-900);"><?= e($disp['make_model']) ?></div>
                                        <div style="font-size:0.75rem; color:var(--vg-text-muted);"><code><?= e($disp['vehicle_number']) ?></code> • <?= e($disp['driver_name']) ?></div>
                                    </td>
                                    <td>
                                        <div style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:0.85rem;" title="<?= e($disp['pickup_address']) ?> → <?= e($disp['drop_address']) ?>">
                                            <?= e($disp['pickup_address']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= render_status_badge($disp['status']) ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= url('/office/bookings/' . $disp['booking_id']) ?>" class="btn btn-outline btn-sm">Inspect</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Security & Audit Log Stream -->
    <div class="vg-card" style="margin-bottom:0;">
        <div class="card-header">
            <span class="card-title">Security Stream</span>
            <span class="badge badge-navy">Audit Log</span>
        </div>
        <div class="card-body" style="padding:1.25rem;">
            <?php if (empty($recentAudits)): ?>
                <div style="text-align:center; padding:2rem 1rem; color:var(--vg-text-muted); font-size:0.85rem;">No security events recorded yet.</div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php foreach ($recentAudits as $audit): ?>
                        <div style="padding:0.65rem 0.85rem; background:#FFFFFF; border-radius:var(--vg-radius-sm); border:1px solid var(--vg-pearl-200); font-size:0.82rem; box-shadow:var(--vg-shadow-sm);">
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                                <strong style="color:var(--vg-navy-900);"><?= e(strtoupper(str_replace('_', ' ', $audit['action']))) ?></strong>
                                <span style="color:var(--vg-text-muted); font-size:0.75rem;"><?= format_datetime($audit['created_at'], 'H:i:s') ?></span>
                            </div>
                            <div style="color:var(--vg-text-muted); font-size:0.78rem;">
                                Operator: <?= e($audit['user_name'] ?? 'System') ?> • <?= e($audit['entity_type']) ?> #<?= (int)$audit['entity_id'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Reservations -->
<div class="vg-card">
    <div class="card-header">
        <span class="card-title">Recent Tour Reservations</span>
        <a href="<?= url('/office/bookings') ?>" class="btn btn-outline btn-sm">View All Bookings</a>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Client</th>
                        <th>Dates</th>
                        <th>Guests</th>
                        <th>Total Amount</th>
                        <th>Reservation Status</th>
                        <th>Billing</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr><td colspan="8" style="text-align:center; padding:2.5rem; color:var(--vg-text-muted);">No bookings registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($b['booking_code']) ?></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($b['tour_title'] ?? 'Custom Route') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= e($b['customer_name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($b['customer_phone'] ?? '—') ?></div>
                                </td>
                                <td>
                                    <?= format_date($b['start_date']) ?>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">to <?= format_date($b['end_date']) ?></div>
                                </td>
                                <td><?= (int)$b['passenger_count'] ?> Guests</td>
                                <td><strong style="color:var(--vg-navy-900);"><?= format_currency((float)$b['total_amount']) ?></strong></td>
                                <td>
                                    <?= render_status_badge($b['status']) ?>
                                </td>
                                <td>
                                    <?= render_status_badge($b['payment_status'] ?? 'unpaid') ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="btn btn-outline btn-sm">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
