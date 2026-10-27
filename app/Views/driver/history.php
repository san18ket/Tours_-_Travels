<?php
/**
 * Driver Trip History View — Dedicated Driver Portal
 */
?>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-950); margin: 0 0 0.25rem; letter-spacing: -0.02em;">
            Chauffeur Trip History
        </h1>
        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
            Comprehensive verified archive of all completed and past journeys executed under your chauffeur profile.
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.45rem 0.85rem;">
            <?= (int)$total ?> Archived Journey<?= $total === 1 ? '' : 's' ?>
        </span>
    </div>
</div>

<!-- Filters Toolbar -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.15rem 1.25rem;">
        <form action="<?= url('/driver/history') ?>" method="GET" style="margin: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
            <!-- Keyword Search -->
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Search by booking code, route, vehicle..." value="<?= e($search ?? '') ?>" style="font-size: 0.85rem;">
            </div>

            <!-- Status Filter -->
            <div style="flex: 1; min-width: 140px;">
                <select name="status" class="form-control" style="font-size: 0.85rem;">
                    <option value="">All Past Statuses</option>
                    <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <!-- Date Range Filters -->
            <div style="flex: 1; min-width: 130px;">
                <input type="date" name="from_date" class="form-control" value="<?= e($fromDate ?? '') ?>" placeholder="From Date" style="font-size: 0.85rem;" title="From Date">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <input type="date" name="to_date" class="form-control" value="<?= e($toDate ?? '') ?>" placeholder="To Date" style="font-size: 0.85rem;" title="To Date">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem;">
                    <i class="fa-solid fa-filter" style="margin-right: 4px;"></i> Filter
                </button>
                <?php if (!empty($search) || !empty($status) || !empty($fromDate) || !empty($toDate)): ?>
                    <a href="<?= url('/driver/history') ?>" class="btn btn-outline" style="padding: 0.55rem 0.85rem; font-size: 0.85rem;" title="Reset Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- History Listing Card -->
<div class="card" style="box-shadow: var(--vg-shadow-sm);">
    <?php if (empty($history)): ?>
        <div class="card-body" style="padding: 3.5rem 1.5rem; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--vg-pearl-100); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--vg-navy-950); margin: 0 0 0.35rem;">
                No trip history records found
            </h3>
            <p style="font-size: 0.9rem; color: var(--vg-text-muted); max-width: 440px; margin: 0 auto 1.25rem;">
                <?= (!empty($search) || !empty($status) || !empty($fromDate) || !empty($toDate))
                    ? 'No completed journeys match your filter parameters.'
                    : 'Once you accept and complete assigned trips, detailed logs and telemetry summaries will be archived here.' ?>
            </p>
            <?php if (!empty($search) || !empty($status) || !empty($fromDate) || !empty($toDate)): ?>
                <a href="<?= url('/driver/history') ?>" class="btn btn-outline btn-sm">
                    Clear Filters
                </a>
            <?php else: ?>
                <a href="<?= url('/driver/trips') ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-calendar-check" style="margin-right: 4px;"></i> View Current Assignments
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive mobile-card-view">
            <table class="data-table" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th>Trip Reference</th>
                        <th>Pickup &amp; Destination</th>
                        <th>Passenger</th>
                        <th>Fleet Vehicle</th>
                        <th>Journey Date</th>
                        <th>Distance Travelled</th>
                        <th>Final Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/driver/trips/' . $h['id']) ?>" style="font-weight: 800; color: var(--vg-blue-600); text-decoration: none; font-size: 0.95rem;">
                                    <?= e($h['booking_code']) ?>
                                </a>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted); font-family: monospace;"><?= e($h['dispatch_code']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($h['pickup_address']) ?>">
                                    <span style="color:#10B981;">●</span> <?= e($h['pickup_address']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($h['drop_address']) ?>">
                                    <span style="color:#EF4444;">●</span> <?= e($h['drop_address']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-950); font-size: 0.88rem;">
                                    <?= e($h['customer_name'] ?? 'VIP Guest') ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-950); font-size: 0.85rem;">
                                    <?= e($h['vehicle_number']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    <?= e($h['make_model']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem;">
                                    <?= format_date($h['start_date']) ?>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted);">
                                    <?= e($h['pickup_time'] ? date('h:i A', strtotime($h['pickup_time'])) : '') ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $startOdo = (int)($h['start_odometer'] ?? 0);
                                    $endOdo   = (int)($h['end_odometer'] ?? 0);
                                    $diff     = ($endOdo >= $startOdo && $startOdo > 0) ? ($endOdo - $startOdo) : 0;
                                ?>
                                <?php if ($diff > 0): ?>
                                    <strong style="color: var(--vg-navy-950);"><?= number_format($diff) ?> km</strong>
                                    <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= number_format($startOdo) ?> &rarr; <?= number_format($endOdo) ?></div>
                                <?php else: ?>
                                    <span style="color: var(--vg-text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $h['status'] === 'completed' ? 'badge-success' : 'badge-danger' ?>" style="font-size: 0.72rem;">
                                    <?= strtoupper(str_replace('_', ' ', $h['status'])) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= url('/driver/trips/' . $h['id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                    View Log
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Mobile Responsive Card View -->
            <div class="vg-mobile-cards" style="display: none; flex-direction: column; gap: 1rem; padding: 1rem;">
                <?php foreach ($history as $h): ?>
                    <div class="card" style="border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-sm); border-radius: var(--vg-radius-md);">
                        <div class="card-body" style="padding: 1.15rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div>
                                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--vg-blue-600);"><?= e($h['booking_code']) ?></div>
                                    <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= e($h['dispatch_code']) ?></div>
                                </div>
                                <span class="badge <?= $h['status'] === 'completed' ? 'badge-success' : 'badge-danger' ?>" style="font-size: 0.72rem;">
                                    <?= strtoupper(str_replace('_', ' ', $h['status'])) ?>
                                </span>
                            </div>

                            <div style="font-size: 0.85rem; margin-bottom: 0.75rem; background: var(--vg-pearl-50); padding: 0.75rem; border-radius: var(--vg-radius-sm);">
                                <div style="margin-bottom: 0.35rem;">
                                    <span style="color:#10B981; font-weight:bold;">From:</span> <?= e($h['pickup_address']) ?>
                                </div>
                                <div>
                                    <span style="color:#EF4444; font-weight:bold;">To:</span> <?= e($h['drop_address']) ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 0.85rem;">
                                <div>
                                    <span style="color: var(--vg-text-muted);">Vehicle:</span> <strong><?= e($h['vehicle_number']) ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Date:</span> <strong><?= format_date($h['start_date']) ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Guest:</span> <strong><?= e($h['customer_name'] ?? 'VIP Guest') ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--vg-text-muted);">Distance:</span>
                                    <strong><?= ((int)($h['end_odometer'] ?? 0) > (int)($h['start_odometer'] ?? 0)) ? number_format((int)$h['end_odometer'] - (int)$h['start_odometer']) . ' km' : '—' ?></strong>
                                </div>
                            </div>

                            <a href="<?= url('/driver/trips/' . $h['id']) ?>" class="btn btn-outline btn-sm" style="display: block; text-align: center;">View Detailed Itinerary</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper" style="padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--vg-pearl-200);">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= $total ?> total)
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/driver/history?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($fromDate) ? '&from_date=' . urlencode($fromDate) : '') . (!empty($toDate) ? '&to_date=' . urlencode($toDate) : '')) ?>" class="btn btn-outline btn-sm">
                            &laquo; Prev
                        </a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= url('/driver/history?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($fromDate) ? '&from_date=' . urlencode($fromDate) : '') . (!empty($toDate) ? '&to_date=' . urlencode($toDate) : '')) ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/driver/history?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($fromDate) ? '&from_date=' . urlencode($fromDate) : '') . (!empty($toDate) ? '&to_date=' . urlencode($toDate) : '')) ?>" class="btn btn-outline btn-sm">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
