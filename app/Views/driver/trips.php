<?php
/**
 * My Assigned Trips View — Dedicated Driver Portal
 */
?>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-950); margin: 0 0 0.25rem; letter-spacing: -0.02em;">
            <?= e(__('sidebar.assigned_trips')) ?>
        </h1>
        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
            <?= e(__('driver.current_assignment')) ?>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.45rem 0.85rem;">
            <?= (int)$total ?> <?= e(__('status.active')) ?>
        </span>
    </div>
</div>

<!-- Search & Filters Toolbar -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.15rem 1.25rem;">
        <form action="<?= url('/driver/trips') ?>" method="GET" style="margin: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
            <!-- Keyword Search -->
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="<?= e(__('table.booking_ref')) ?>, <?= e(__('table.customer')) ?>..." value="<?= e($search ?? '') ?>" style="font-size: 0.85rem;">
            </div>

            <!-- Status Filter -->
            <div style="flex: 1; min-width: 150px;">
                <select name="status" class="form-control" style="font-size: 0.85rem;">
                    <option value=""><?= e(__('table.status')) ?> (All)</option>
                    <option value="assigned" <?= ($status ?? '') === 'assigned' ? 'selected' : '' ?>><?= e(__('status.assigned')) ?></option>
                    <option value="driver_accepted" <?= ($status ?? '') === 'driver_accepted' ? 'selected' : '' ?>><?= e(__('status.driver_accepted')) ?></option>
                    <option value="trip_started" <?= ($status ?? '') === 'trip_started' ? 'selected' : '' ?>><?= e(__('status.trip_started')) ?></option>
                </select>
            </div>

            <!-- Date Filter -->
            <div style="flex: 1; min-width: 140px;">
                <input type="date" name="date" class="form-control" value="<?= e($date ?? '') ?>" style="font-size: 0.85rem;">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem;">
                    <i class="fa-solid fa-filter" style="margin-right: 4px;"></i> <?= e(__('btn.filter')) ?>
                </button>
                <?php if (!empty($search) || !empty($status) || !empty($date)): ?>
                    <a href="<?= url('/driver/trips') ?>" class="btn btn-outline" style="padding: 0.55rem 0.85rem; font-size: 0.85rem;" title="<?= e(__('btn.reset')) ?>">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Assignments Listing -->
<div class="card" style="box-shadow: var(--vg-shadow-sm);">
    <?php if (empty($trips)): ?>
        <div class="card-body" style="padding: 3.5rem 1.5rem; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--vg-pearl-100); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--vg-navy-950); margin: 0 0 0.35rem;">
                <?= e(__('driver.no_trips_assigned')) ?>
            </h3>
            <p style="font-size: 0.9rem; color: var(--vg-text-muted); max-width: 440px; margin: 0 auto 1.25rem;">
                <?= (!empty($search) || !empty($status) || !empty($date))
                    ? e(__('table.no_records'))
                    : e(__('driver.waiting_dispatch')) ?>
            </p>
            <?php if (!empty($search) || !empty($status) || !empty($date)): ?>
                <a href="<?= url('/driver/trips') ?>" class="btn btn-outline btn-sm">
                    <?= e(__('btn.reset')) ?>
                </a>
            <?php else: ?>
                <a href="<?= url('/driver/dashboard') ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-gauge" style="margin-right: 4px;"></i> <?= e(__('sidebar.dashboard')) ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive mobile-card-view">
            <table class="data-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th>Trip &amp; Booking Code</th>
                        <th>Passenger</th>
                        <th>Pickup &amp; Destination</th>
                        <th>Pickup Date &amp; Time</th>
                        <th>Vehicle Assigned</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/driver/trips/' . $t['id']) ?>" style="font-weight: 800; color: var(--vg-blue-600); text-decoration: none; font-size: 0.95rem;">
                                    <?= e($t['booking_code']) ?>
                                </a>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); font-family: monospace;"><?= e($t['dispatch_code']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-950); font-size: 0.88rem;">
                                    <?= e($t['customer_name'] ?? 'VIP Guest') ?>
                                </div>
                                <?php if (!empty($t['customer_phone'])): ?>
                                    <a href="tel:<?= e($t['customer_phone']) ?>" style="font-size: 0.75rem; color: var(--vg-blue-600); text-decoration: none;">
                                        <i class="fa-solid fa-phone" style="font-size: 0.7rem; margin-right: 2px;"></i> <?= e($t['customer_phone']) ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($t['pickup_address']) ?>">
                                    <span style="color: #10B981; margin-right: 3px;">●</span> <?= e($t['pickup_address']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($t['drop_address']) ?>">
                                    <span style="color: #EF4444; margin-right: 3px;">●</span> <?= e($t['drop_address']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--vg-navy-950);">
                                    <?= format_date($t['start_date']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    <?= e($t['pickup_time'] ? date('h:i A', strtotime($t['pickup_time'])) : 'Scheduled') ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-950); font-size: 0.85rem;">
                                    <?= e($t['vehicle_number']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    <?= e($t['make_model']) ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $badge = match($t['status']) {
                                        'assigned'         => 'badge-warning',
                                        'driver_accepted'  => 'badge-info',
                                        'trip_started'     => 'badge-primary',
                                        'completed'        => 'badge-success',
                                        default            => 'badge-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badge ?>" style="font-size: 0.72rem;">
                                    <?= strtoupper(str_replace('_', ' ', $t['status'])) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                                    <?php if ($t['status'] === 'assigned'): ?>
                                        <form action="<?= url('/driver/trips/' . $t['id'] . '/accept') ?>" method="POST" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 0.35rem 0.7rem;" title="Accept Trip">
                                                Accept
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.35rem 0.7rem;">
                                        View Details
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Mobile Responsive Card View -->
            <div class="vg-mobile-cards" style="display: none; flex-direction: column; gap: 1rem; padding: 1rem;">
                <?php foreach ($trips as $t): ?>
                    <div class="card" style="border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-sm); border-radius: var(--vg-radius-md);">
                        <div class="card-body" style="padding: 1.15rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div>
                                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--vg-blue-600);"><?= e($t['booking_code']) ?></div>
                                    <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= e($t['dispatch_code']) ?></div>
                                </div>
                                <span class="badge badge-info" style="font-size: 0.72rem;">
                                    <?= strtoupper(str_replace('_', ' ', $t['status'])) ?>
                                </span>
                            </div>

                            <div style="font-size: 0.85rem; margin-bottom: 0.75rem; background: var(--vg-pearl-50); padding: 0.75rem; border-radius: var(--vg-radius-sm);">
                                <div style="margin-bottom: 0.35rem;">
                                    <span style="color:#10B981; font-weight:bold;">Pickup:</span> <?= e($t['pickup_address']) ?>
                                </div>
                                <div>
                                    <span style="color:#EF4444; font-weight:bold;">Drop:</span> <?= e($t['drop_address']) ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 0.85rem;">
                                <div>
                                    <span style="color: var(--vg-text-muted);">Guest:</span> <strong><?= e($t['customer_name'] ?? 'VIP Guest') ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Vehicle:</span> <strong><?= e($t['vehicle_number']) ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Date:</span> <strong><?= format_date($t['start_date']) ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Time:</span> <strong><?= e($t['pickup_time'] ? date('h:i A', strtotime($t['pickup_time'])) : 'Scheduled') ?></strong>
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <?php if ($t['status'] === 'assigned'): ?>
                                    <form action="<?= url('/driver/trips/' . $t['id'] . '/accept') ?>" method="POST" style="margin:0; flex:1;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-success btn-sm" style="width: 100%;">Accept</button>
                                    </form>
                                <?php endif; ?>
                                <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper" style="padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--vg-pearl-200);">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= $total ?> assignments total)
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/driver/trips?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($date) ? '&date=' . urlencode($date) : '')) ?>" class="btn btn-outline btn-sm">
                            &laquo; Prev
                        </a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= url('/driver/trips?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($date) ? '&date=' . urlencode($date) : '')) ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/driver/trips?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($date) ? '&date=' . urlencode($date) : '')) ?>" class="btn btn-outline btn-sm">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
