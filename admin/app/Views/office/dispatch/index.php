<?= render_page_header([
    'title'       => __('dispatch.title', 'Fleet Dispatch Operations'),
    'description' => __('dispatch.subtitle', 'Live deployment control, driver trip progress, odometer verifications, and route completions.'),
    'badge'       => 'Fleet Dispatch Active',
    'breadcrumbs' => [
        ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
        ['label' => __('nav.dispatch', 'Dispatch'), 'url' => ''],
    ],
    'actions'     => '<a href="' . url('/office/bookings') . '" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.assign_reservations', 'Assign From Reservations')) . '</span></a>',
]) ?>

<div class="vg-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="pulse-indicator"></span>
            <span class="card-title"><?= e(__('dispatch.fleet_dispatches', 'Fleet Dispatches')) ?> (<?= count($dispatches) ?>)</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= url('/office/dispatch') ?>" class="btn btn-outline btn-sm"><?= e(__('btn.all_dispatches', 'All Dispatches')) ?></a>
            <a href="<?= url('/office/dispatch?status=trip_started') ?>" class="btn btn-outline btn-sm"><?= e(__('btn.in_transit', 'In Transit')) ?></a>
            <a href="<?= url('/office/dispatch?status=completed') ?>" class="btn btn-outline btn-sm"><?= e(__('btn.completed', 'Completed')) ?></a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($dispatches)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <div class="empty-state-title"><?= e(__('card.no_dispatches_created', 'No Dispatches Created')) ?></div>
                <p class="empty-state-desc"><?= e(__('dispatch.empty_desc', 'Assign pending reservations to available drivers and vehicles to initiate dispatch.')) ?></p>
                <a href="<?= url('/office/bookings') ?>" class="btn btn-primary btn-sm"><?= e(__('dispatch.go_to_bookings', 'Go to Bookings')) ?></a>
            </div>
        <?php else: ?>
            <!-- Desktop Data Grid -->
            <div class="table-responsive modern-table-container">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th class="col-priority-1 table-col-code"><?= e(__('table.dispatch_code', 'Dispatch')) ?></th>
                            <th class="col-priority-1 table-col-name"><?= e(__('table.reservation_client', 'Booking / Passenger')) ?></th>
                            <th class="col-priority-1 table-col-driver"><?= e(__('table.driver_phone', 'Driver')) ?></th>
                            <th class="col-priority-2 table-col-vehicle" data-column-priority="secondary"><?= e(__('table.vehicle_details', 'Vehicle')) ?></th>
                            <th class="col-priority-3 col-optional table-col-odometer" data-column-priority="optional"><?= e(__('table.odometer_start_end', 'Odometer')) ?></th>
                            <th class="col-priority-1 table-col-status"><?= e(__('table.status', 'Status')) ?></th>
                            <th class="col-priority-1 table-col-actions"><?= e(__('table.action', 'Actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dispatches as $d): ?>
                            <tr>
                                <td class="col-priority-1 table-col-code">
                                    <div class="table-cell-group">
                                        <span class="table-cell-primary" style="font-family: ui-monospace, monospace;">
                                            <?= e($d['dispatch_code']) ?>
                                        </span>
                                        <span class="table-cell-secondary">
                                            <?= e(__('dispatch.assigned_by', 'By')) ?> <?= e($d['assigner_name']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="col-priority-1 table-col-name">
                                    <div class="table-cell-group">
                                        <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="table-cell-primary" style="text-decoration:none;">
                                            <?= e($d['booking_code']) ?>
                                        </a>
                                        <span class="table-cell-secondary"><?= e($d['customer_name']) ?></span>
                                    </div>
                                </td>
                                <td class="col-priority-1 table-col-person">
                                    <div class="table-cell-group">
                                        <span class="table-cell-primary"><?= e($d['driver_name']) ?></span>
                                        <span class="table-cell-secondary"><?= e($d['driver_phone']) ?></span>
                                    </div>
                                </td>
                                <td class="col-priority-2 table-col-vehicle" data-column-priority="secondary">
                                    <div class="table-cell-group">
                                        <span class="table-cell-primary"><?= e($d['make_model']) ?></span>
                                        <span class="table-cell-secondary" style="font-family: ui-monospace, monospace; color: var(--brand-primary, #176B5B); font-weight: 600;">
                                            <?= e($d['vehicle_number']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="col-priority-3 col-optional table-col-odometer" data-column-priority="optional">
                                    <div class="table-cell-group">
                                        <span class="table-cell-primary" style="font-size: 0.82rem; font-weight: 600;">
                                            Start: <?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' ' . e(__('common.km', 'km')) : '—' ?>
                                        </span>
                                        <span class="table-cell-secondary">
                                            End: <?= $d['end_odometer'] ? number_format((int)$d['end_odometer']) . ' ' . e(__('common.km', 'km')) : e(__('common.in_transit', 'In transit')) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="col-priority-1 table-col-status">
                                    <?= render_status_badge($d['status']) ?>
                                </td>
                                <td class="col-priority-1 table-col-actions" style="white-space: nowrap;">
                                    <div class="action-dropdown-wrap">
                                        <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="action-dropdown-item view">
                                                <i class="fa-solid fa-eye"></i> <span><?= e(__('booking.view_booking', 'View Booking')) ?></span>
                                            </a>
                                            <a href="<?= url('/office/trips/assign?booking_id=' . $d['booking_id']) ?>" class="action-dropdown-item dispatch">
                                                <i class="fa-solid fa-car-side"></i> <span><?= e(__('btn.reassign_trip', 'Reassign Trip')) ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards cards-grid" style="padding: 1rem;">
                <?php foreach ($dispatches as $d): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($d['dispatch_code']) ?></span>
                                <div style="font-size:0.72rem; color:var(--vg-text-muted);"><?= e(__('common.booking_short', 'Bk:')) ?> <?= e($d['booking_code']) ?></div>
                            </div>
                            <div>
                                <?= render_status_badge($d['status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label"><?= e(__('table.driver', 'Driver')) ?></span>
                                <span class="vg-card-value"><?= e($d['driver_name']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label"><?= e(__('table.vehicle', 'Vehicle')) ?></span>
                                <span class="vg-card-value"><code><?= e($d['vehicle_number']) ?></code></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label"><?= e(__('table.start_odo', 'Start Odo')) ?></span>
                                <span class="vg-card-value"><?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' ' . e(__('common.km', 'km')) : '—' ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label"><?= e(__('table.client', 'Client')) ?></span>
                                <span class="vg-card-value"><?= e($d['customer_name']) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions mv-card-actions">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="action-dropdown-item view">
                                        <i class="fa-solid fa-eye"></i> <span><?= e(__('booking.view_booking', 'View Booking')) ?></span>
                                    </a>
                                    <a href="<?= url('/office/trips/assign?booking_id=' . $d['booking_id']) ?>" class="action-dropdown-item dispatch">
                                        <i class="fa-solid fa-car-side"></i> <span><?= e(__('btn.reassign_trip', 'Reassign Trip')) ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
