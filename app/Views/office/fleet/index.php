<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Fleet & Chauffeur Roster</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Maintain commercial fleet readiness, vehicle compliance cycles, and chauffeur duty rosters.</p>
    </div>
    <?php if (auth_role() === 'admin'): ?>
    <div>
        <a href="<?= url('/office/fleet/vehicles/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Register New Vehicle</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php
    $currentTab = $_GET['tab'] ?? 'vehicles';
?>

<!-- Tab Navigation Bar -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--vg-pearl-200); padding-bottom: 0.5rem;">
    <a href="<?= url('/office/fleet?tab=vehicles') ?>" class="btn <?= $currentTab === 'vehicles' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        Commercial Vehicles (<?= count($vehicles) ?>)
    </a>
    <a href="<?= url('/office/fleet?tab=drivers') ?>" class="btn <?= $currentTab === 'drivers' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        Chauffeur Duty Roster (<?= count($drivers) ?>)
    </a>
    <a href="<?= url('/office/fleet?tab=documents') ?>" class="btn <?= $currentTab === 'documents' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        Document Compliance
    </a>
</div>

<?php if ($currentTab === 'vehicles'): ?>
    <!-- Commercial Vehicles Section -->
    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Commercial Vehicles</span>
            <span class="badge badge-navy"><?= count($vehicles) ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <!-- Desktop Data Grid -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plate & Model</th>
                            <th>Type & Capacity</th>
                            <th>Fuel Type</th>
                            <th>Odometer</th>
                            <th>Readiness Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($v['make_model']) ?></strong>
                                    <div style="font-size:0.8rem; color:var(--vg-blue-500);"><code><?= e($v['vehicle_number']) ?></code></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= (int)$v['seating_capacity'] ?> Passenger Seats</div>
                                </td>
                                <td><span class="badge badge-neutral"><?= strtoupper(e($v['fuel_type'])) ?></span></td>
                                <td><strong style="color:var(--vg-navy-900);"><?= number_format((int)$v['odometer_km']) ?> km</strong></td>
                                <td>
                                    <?= render_status_badge($v['status']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($v['status'] !== 'on_trip'): ?>
                                        <form action="<?= url('/office/fleet/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="display:inline-block; margin:0;">
                                            <?= csrf_field() ?>
                                            <?php if ($v['status'] === 'available'): ?>
                                                <input type="hidden" name="status" value="maintenance">
                                                <button type="submit" class="btn btn-outline btn-sm" title="Mark for Maintenance">Service</button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="available">
                                                <button type="submit" class="btn btn-success btn-sm">Set Ready</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size:0.78rem; color:var(--vg-text-muted); font-weight:600;">On Active Trip</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($vehicles as $v): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($v['make_model']) ?></span>
                                <div style="font-size: 0.75rem; color: var(--vg-blue-500);"><code><?= e($v['vehicle_number']) ?></code></div>
                            </div>
                            <div>
                                <?= render_status_badge($v['status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Type</span>
                                <span class="vg-card-value"><?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Capacity</span>
                                <span class="vg-card-value"><?= (int)$v['seating_capacity'] ?> Seats</span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Odometer</span>
                                <span class="vg-card-value"><?= number_format((int)$v['odometer_km']) ?> km</span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Fuel</span>
                                <span class="vg-card-value"><?= strtoupper(e($v['fuel_type'])) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <?php if ($v['status'] !== 'on_trip'): ?>
                                <form action="<?= url('/office/fleet/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <?php if ($v['status'] === 'available'): ?>
                                        <input type="hidden" name="status" value="maintenance">
                                        <button type="submit" class="btn btn-outline btn-sm">Service</button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="btn btn-success btn-sm">Set Ready</button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <span style="font-size:0.78rem; color:var(--vg-text-muted);">Executing Trip</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php elseif ($currentTab === 'drivers'): ?>
    <!-- Driver Profiles Section -->
    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Chauffeur Duty Roster</span>
            <span class="badge badge-navy"><?= count($drivers) ?> Chauffeurs</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <!-- Desktop Data Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Chauffeur Name</th>
                            <th>License & Expiry</th>
                            <th>Experience & Rating</th>
                            <th>Duty State</th>
                            <th>Last Telemetry Sync</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $d): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($d['name']) ?></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($d['phone']) ?> • <?= e($d['email']) ?></div>
                                </td>
                                <td>
                                    <div><code><?= e($d['license_number']) ?></code></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">Exp: <?= format_date($d['license_expiry']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= (int)$d['experience_years'] ?> Yrs Exp</div>
                                    <div style="color:var(--vg-gold-500); font-size:0.85rem; font-weight:700;">★ <?= e($d['rating']) ?></div>
                                </td>
                                <td>
                                    <?= render_status_badge($d['duty_status']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($d['last_gps_at'])): ?>
                                        <span style="font-size:0.85rem; font-weight:600;"><?= format_datetime($d['last_gps_at'], 'H:i:s, d M') ?></span>
                                        <div style="font-size:0.72rem; color:var(--vg-success);">📡 Synced (<?= round((float)$d['current_latitude'], 3) ?>, <?= round((float)$d['current_longitude'], 3) ?>)</div>
                                    <?php else: ?>
                                        <span style="color:var(--vg-text-muted); font-size:0.85rem;">Standby</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($d['duty_status'] !== 'on_trip'): ?>
                                        <form action="<?= url('/office/fleet/drivers/' . $d['id'] . '/duty') ?>" method="POST" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <?= $d['duty_status'] === 'on_duty' ? 'Set Off-Duty' : 'Set On-Duty' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size:0.78rem; color:var(--vg-blue-500); font-weight:600;">Trip Executing</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($drivers as $d): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($d['name']) ?></span>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= e($d['phone']) ?></div>
                            </div>
                            <div>
                                <?= render_status_badge($d['duty_status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">License</span>
                                <span class="vg-card-value"><code><?= e($d['license_number']) ?></code></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">License Expiry</span>
                                <span class="vg-card-value"><?= format_date($d['license_expiry']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Experience</span>
                                <span class="vg-card-value"><?= (int)$d['experience_years'] ?> Years</span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Rating</span>
                                <span class="vg-card-value" style="color:var(--vg-gold-500);">★ <?= e($d['rating']) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <?php if ($d['duty_status'] !== 'on_trip'): ?>
                                <form action="<?= url('/office/fleet/drivers/' . $d['id'] . '/duty') ?>" method="POST" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?= $d['duty_status'] === 'on_duty' ? 'Set Off-Duty' : 'Set On-Duty' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:0.78rem; color:var(--vg-blue-500); font-weight:600;">Trip Executing</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php elseif ($currentTab === 'documents'): ?>
    <!-- Document Compliance Overview -->
    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Commercial Document Compliance</span>
            <span class="badge badge-gold">Active Monitoring</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vehicle Number</th>
                            <th>Make & Model</th>
                            <th>Registration Expiry</th>
                            <th>Insurance Expiry</th>
                            <th>Compliance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <?php
                                $now = time();
                                $regTime = !empty($v['registration_expiry']) ? strtotime($v['registration_expiry']) : null;
                                $insTime = !empty($v['insurance_expiry']) ? strtotime($v['insurance_expiry']) : null;
                                $isExpiringSoon = ($regTime && ($regTime - $now < 30 * 86400)) || ($insTime && ($insTime - $now < 30 * 86400));
                            ?>
                            <tr>
                                <td><code><?= e($v['vehicle_number']) ?></code></td>
                                <td><strong><?= e($v['make_model']) ?></strong></td>
                                <td><?= format_date($v['registration_expiry']) ?></td>
                                <td><?= format_date($v['insurance_expiry']) ?></td>
                                <td>
                                    <?php if ($isExpiringSoon): ?>
                                        <span class="badge badge-warning"><span class="badge-dot"></span>Renewal Due Soon</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><span class="badge-dot"></span>Compliant</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
