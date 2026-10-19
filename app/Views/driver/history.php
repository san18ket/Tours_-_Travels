<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
    <div>
        <h1 style="font-size: 1.3rem; font-weight: 800; color: var(--navy-900);">My Completed Trips</h1>
        <p style="color: var(--text-secondary); font-size: 0.85rem;">History of completed itineraries and odometer records.</p>
    </div>
    <a href="<?= url('/driver/duty') ?>" class="btn btn-outline btn-sm">Back to Duty</a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🚗</div>
                <div class="empty-state-title">No Trip History</div>
                <p class="empty-state-desc">Your completed trips and mileage logs will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Trip & Client</th>
                            <th>Route</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <strong><?= e($h['dispatch_code']) ?></strong>
                                    <div style="font-size:0.75rem; color:#64748B;">Guest: <?= e($h['customer_name']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:0.82rem; color:var(--navy-900); font-weight:600;"><?= e($h['pickup_address']) ?></div>
                                    <div style="font-size:0.75rem; color:#64748B;">to <?= e($h['drop_address']) ?></div>
                                </td>
                                <td>
                                    <div><?= e($h['make_model']) ?></div>
                                    <div style="font-size:0.75rem; color:#008DDA;"><code><?= e($h['vehicle_number']) ?></code></div>
                                </td>
                                <td>
                                    <span class="badge <?= $h['status'] === 'completed' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= e(ucfirst(str_replace('_', ' ', $h['status']))) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
