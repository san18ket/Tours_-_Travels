<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Reservations & Bookings</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Track guest itineraries, chauffeur dispatches, and billing status.</p>
    </div>
    <div>
        <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New Reservation</span>
        </a>
    </div>
</div>

<div class="vg-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="<?= url('/office/bookings') ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%;">
            <div class="filter-input-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" class="form-control" placeholder="Search reference code, guest name, phone..." value="<?= e($search ?? '') ?>">
            </div>
            <select name="status" class="form-select" style="max-width: 170px;">
                <option value="">All Statuses</option>
                <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= ($selectedStatus ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="assigned" <?= ($selectedStatus ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="in_progress" <?= ($selectedStatus ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <select name="per_page" class="form-select" style="max-width: 120px;" onchange="this.form.submit()">
                <option value="10" <?= ($perPage ?? 10) == 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="25" <?= ($perPage ?? 10) == 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= ($perPage ?? 10) == 50 ? 'selected' : '' ?>>50 / page</option>
            </select>
            <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/bookings') ?>" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div class="empty-state-title">No Bookings Found</div>
                <p class="empty-state-desc">There are no reservation records matching your selected query filters.</p>
                <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary btn-sm">Create Reservation</a>
            </div>
        <?php else: ?>
            <!-- Desktop Data Grid -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Guest Client</th>
                            <th>Tour / Route</th>
                            <th>Travel Itinerary</th>
                            <th>Dispatch Status</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($b['booking_code']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);"><?= format_datetime($b['created_at'], 'd M Y, H:i') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--vg-navy-900);"><?= e($b['customer_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);"><?= e($b['customer_phone'] ?? '—') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--vg-navy-900);"><?= e($b['tour_title'] ?? 'Custom Route') ?></div>
                                    <div style="font-size: 0.75rem; color: var(--vg-text-muted); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($b['pickup_address']) ?> → <?= e($b['drop_address']) ?>">
                                        <?= e($b['pickup_address']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div><?= format_date($b['start_date']) ?> &rarr; <?= format_date($b['end_date']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);"><?= (int)$b['passenger_count'] ?> Guests</div>
                                </td>
                                <td>
                                    <?php if ($b['dispatch_id']): ?>
                                        <?= render_status_badge('assigned') ?>
                                        <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 2px;"><?= e($b['driver_name']) ?></div>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--vg-navy-900);"><?= format_currency((float)$b['total_amount']) ?></div>
                                    <div style="margin-top: 2px;">
                                        <?= render_status_badge($b['payment_status'] ?? 'unpaid') ?>
                                    </div>
                                </td>
                                <td>
                                    <?= render_status_badge($b['status']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="btn btn-outline btn-sm">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View (Phones & Tablets) -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($bookings as $b): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($b['booking_code']) ?></span>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= format_datetime($b['created_at'], 'd M Y, H:i') ?></div>
                            </div>
                            <div>
                                <?= render_status_badge($b['status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Client</span>
                                <span class="vg-card-value"><?= e($b['customer_name']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Total Amount</span>
                                <span class="vg-card-value" style="color:var(--vg-navy-900);"><?= format_currency((float)$b['total_amount']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Tour / Route</span>
                                <span class="vg-card-value"><?= e($b['tour_title'] ?? 'Custom Route') ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Travel Date</span>
                                <span class="vg-card-value"><?= format_date($b['start_date']) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <?= render_status_badge($b['payment_status'] ?? 'unpaid') ?>
                            <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="btn btn-outline btn-sm">Inspect</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Server-Side Pagination Controls -->
            <?php
                $queryParams = [];
                if (!empty($search)) $queryParams['q'] = $search;
                if (!empty($selectedStatus)) $queryParams['status'] = $selectedStatus;
                echo render_pagination($currentPage ?? 1, $totalPages ?? 1, $totalRecords ?? count($bookings), $perPage ?? 10, url('/office/bookings'), $queryParams);
            ?>
        <?php endif; ?>
    </div>
</div>
