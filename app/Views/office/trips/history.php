<!-- Header Ribbon -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0 0 0.25rem 0;">Trip Operations History</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Comprehensive audit log of commercial dispatches, chauffeur duty states, and odometer verifications.</p>
    </div>
    <div>
        <a href="<?= url('/office/trips/assign') ?>" class="btn btn-navy" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Assign Trip to Driver</span>
        </a>
    </div>
</div>

<!-- KPI Metrics Ribbon -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon info" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--vg-blue-500); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Total Historical Dispatches</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalRecords) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon purple" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8B5CF6; display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Active Deployments</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalActive) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon success" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--vg-success); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Completed Trips</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalCompleted) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon danger" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: var(--vg-danger); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Cancelled Trips</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalCancelled) ?></div>
        </div>
    </div>
</div>

<!-- Search & Filters Ribbon -->
<div class="vg-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="<?= url('/office/trips/history') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 220px; position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Search dispatch code, booking code, plate, chauffeur..." value="<?= e($search) ?>" style="padding-left: 2.25rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="status" class="form-select" style="flex: 1; min-width: 150px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="assigned" <?= $selectedStatus === 'assigned' ? 'selected' : '' ?>>Assigned (Pending Driver)</option>
                <option value="driver_accepted" <?= $selectedStatus === 'driver_accepted' ? 'selected' : '' ?>>Driver Accepted</option>
                <option value="trip_started" <?= $selectedStatus === 'trip_started' ? 'selected' : '' ?>>In Transit / Started</option>
                <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <input type="date" name="from_date" class="form-control" value="<?= e($fromDate) ?>" title="From Departure Date" style="width: 145px;" onchange="this.form.submit()">
            <input type="date" name="to_date" class="form-control" value="<?= e($toDate) ?>" title="To Departure Date" style="width: 145px;" onchange="this.form.submit()">
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select name="per_page" class="form-select" style="width: 110px;" onchange="this.form.submit()">
                <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
            </select>

            <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($selectedStatus) || !empty($fromDate) || !empty($toDate)): ?>
                <a href="<?= url('/office/trips/history') ?>" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- History Ledger -->
<div class="vg-card">
    <?php if (empty($trips)): ?>
        <div style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🗺️</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.5rem;">No Trip Operations Recorded</h3>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem auto;">
                <?= (!empty($search) || !empty($selectedStatus) || !empty($fromDate) || !empty($toDate)) ? 'No trip records match the current filter query. Try resetting your search filters.' : 'Deploy your first reservation assignment to begin tracking trip lifecycles.' ?>
            </p>
            <a href="<?= url('/office/trips/assign') ?>" class="btn btn-navy">
                + Deploy First Trip
            </a>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Dispatch Code</th>
                        <th>Reservation Manifest</th>
                        <th>Assigned Chauffeur</th>
                        <th>Fleet Vehicle</th>
                        <th>Odometer Readings</th>
                        <th>Trip Lifecycle</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-family: monospace; font-size: 0.95rem;"><?= e($t['dispatch_code']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    Assigned by <?= e($t['assigner_name'] ?? 'Operations') ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <a href="<?= url('/office/bookings/' . $t['booking_id']) ?>" style="font-weight: 700; color: var(--vg-blue-500); text-decoration: none;">
                                        <?= e($t['booking_code']) ?>
                                    </a>
                                    <span style="font-size: 0.78rem; color: var(--vg-text-muted); margin-left: 0.35rem;">(<?= (int)$t['passenger_count'] ?> Guests)</span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--vg-text-secondary); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($t['pickup_address']) ?> → <?= e($t['drop_address']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-900);"><?= e($t['driver_name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted);"><?= e($t['driver_phone']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--vg-navy-900);"><?= e($t['make_model']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--vg-blue-500); font-family: monospace; font-weight: 700;"><?= e($t['vehicle_number']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                    <?= $t['start_odometer'] ? number_format((int)$t['start_odometer']) . ' km' : '—' ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    End: <?= $t['end_odometer'] ? number_format((int)$t['end_odometer']) . ' km' : ($t['status'] === 'trip_started' ? '<span style="color:#0284C7;">In Transit</span>' : '—') ?>
                                </div>
                            </td>
                            <td>
                                <?= render_status_badge($t['status']) ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                    <a href="<?= url('/office/bookings/' . $t['booking_id']) ?>" class="btn btn-outline btn-sm" title="Inspect Booking">Inspect</a>
                                    <?php if (!in_array($t['status'], ['completed', 'cancelled'], true) && auth_role() === 'admin'): ?>
                                        <form action="<?= url('/office/trips/' . $t['id'] . '/cancel') ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Cancel this trip deployment? Vehicle and chauffeur will be returned to available roster.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="reason" value="Cancelled via operations console">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Cancel Trip">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Record Cards View -->
        <div class="vg-mobile-cards" style="padding: 1rem;">
            <?php foreach ($trips as $t): ?>
                <div class="vg-record-card">
                    <div class="vg-card-row-header">
                        <div>
                            <span class="badge badge-navy" style="font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                                <?= e($t['dispatch_code']) ?>
                            </span>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 0.25rem;">
                                Booking: <a href="<?= url('/office/bookings/' . $t['booking_id']) ?>" style="color: var(--vg-blue-500); font-weight: 700;"><?= e($t['booking_code']) ?></a>
                            </div>
                        </div>
                        <div>
                            <?= render_status_badge($t['status']) ?>
                        </div>
                    </div>
                    <div class="vg-card-grid">
                        <div class="vg-card-field">
                            <span class="vg-card-label">Chauffeur</span>
                            <span class="vg-card-value"><?= e($t['driver_name']) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Vehicle</span>
                            <span class="vg-card-value" style="font-family: monospace;"><?= e($t['vehicle_number']) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Start Odo</span>
                            <span class="vg-card-value"><?= $t['start_odometer'] ? number_format((int)$t['start_odometer']) . ' km' : '—' ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">End Odo</span>
                            <span class="vg-card-value"><?= $t['end_odometer'] ? number_format((int)$t['end_odometer']) . ' km' : 'In transit' ?></span>
                        </div>
                    </div>
                    <div class="vg-card-actions">
                        <a href="<?= url('/office/bookings/' . $t['booking_id']) ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">View Booking</a>
                        <?php if (!in_array($t['status'], ['completed', 'cancelled'], true) && auth_role() === 'admin'): ?>
                            <form action="<?= url('/office/trips/' . $t['id'] . '/cancel') ?>" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('Cancel trip?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-block btn-sm">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Server-side Pagination Ribbon -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-top: 1px solid var(--vg-pearl-200); flex-wrap: wrap; gap: 0.75rem;">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalRecords) ?> total dispatches)
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('/office/trips/history?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="btn btn-outline btn-sm">‹ Previous</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="<?= url('/office/trips/history?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn <?= $p === $currentPage ? 'btn-navy' : 'btn-outline' ?> btn-sm"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('/office/trips/history?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="btn btn-outline btn-sm">Next ›</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
