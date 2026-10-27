<?php
/**
 * My Assigned Fleet Vehicle View — Dedicated Driver Portal
 */
?>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-950); margin: 0 0 0.25rem; letter-spacing: -0.02em;">
            My Assigned Vehicle
        </h1>
        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
            Fleet vehicle specifications and operational details linked to your chauffeur assignments.
        </div>
    </div>
    <div>
        <?php if (!empty($vehicle)): ?>
            <span class="badge <?= ($vehicle['is_active_assignment'] ?? false) ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.8rem; padding: 0.45rem 0.85rem;">
                <span class="pulse-indicator"></span> <?= ($vehicle['is_active_assignment'] ?? false) ? 'Active Duty Assignment' : 'Last Assigned Fleet' ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($vehicle)): ?>
    <!-- Professional Empty State -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm); border: 1px dashed var(--vg-pearl-300); text-align: center; padding: 4rem 1.5rem;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--vg-pearl-100); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.25rem;">
            <i class="fa-solid fa-car"></i>
        </div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--vg-navy-950); margin: 0 0 0.5rem;">
            No vehicle assigned yet
        </h2>
        <p style="font-size: 0.9rem; color: var(--vg-text-muted); max-width: 460px; margin: 0 auto 1.5rem;">
            You currently have no fleet vehicle assigned to your profile. When office operations dispatches a trip to you, the assigned luxury vehicle and its telemetry specs will appear here.
        </p>
        <div>
            <a href="<?= url('/driver/dashboard') ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-gauge" style="margin-right: 4px;"></i> Return to Dashboard
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Vehicle Profile Card -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem; overflow: hidden;">
        <!-- Vehicle Hero Header -->
        <div style="background: linear-gradient(135deg, var(--vg-navy-950) 0%, var(--vg-navy-900) 100%); color: #FFF; padding: 1.75rem; border-bottom: 2px solid var(--vg-gold-500);">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 64px; height: 64px; border-radius: var(--vg-radius-md); background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); border: 1.5px solid var(--vg-gold-500); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #FFF; box-shadow: var(--vg-shadow-gold);">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--vg-gold-400); font-weight: 700;">
                            VoyageGo Luxury Fleet
                        </div>
                        <h2 style="font-size: 1.5rem; font-weight: 800; color: #FFF; margin: 0.2rem 0; letter-spacing: -0.01em;">
                            <?= e($vehicle['vehicle_number']) ?>
                        </h2>
                        <div style="font-size: 0.9rem; color: #CBD5E1;">
                            <?= e($vehicle['make_model']) ?> • <span class="badge badge-navy" style="font-size: 0.72rem;"><?= strtoupper($vehicle['vehicle_type']) ?></span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                    <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                        <span class="pulse-indicator"></span> Fleet Status: <?= strtoupper($vehicle['status'] ?? 'Available') ?>
                    </span>
                    <div style="font-size: 0.75rem; color: #94A3B8;">
                        Assigned on: <?= e($vehicle['booking_code'] ?? 'Active Dispatch') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Specifications Grid -->
        <div class="card-body" style="padding: 1.5rem;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 1rem; letter-spacing: 0.05em;">
                Vehicle Technical Specifications
            </div>

            <div class="vg-detail-grid" style="margin-bottom: 1.5rem;">
                <!-- Plate Number -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Registration Plate</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e($vehicle['vehicle_number']) ?>
                    </div>
                </div>

                <!-- Make & Model -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Make &amp; Model</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e($vehicle['make_model']) ?>
                    </div>
                </div>

                <!-- Seating Capacity -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Seating Capacity</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= (int)($vehicle['seating_capacity'] ?? 4) ?> Guests + Chauffeur
                    </div>
                </div>

                <!-- Fuel Type -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Powertrain / Fuel</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e(ucfirst($vehicle['fuel_type'] ?? 'Diesel')) ?>
                    </div>
                </div>

                <!-- Current Odometer -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Current Odometer Reading</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= number_format((int)($vehicle['odometer_km'] ?? 0)) ?> km
                    </div>
                </div>

                <!-- Fleet Category -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Vehicle Class</div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e(ucfirst($vehicle['vehicle_type'] ?? 'Standard')) ?>
                    </div>
                </div>
            </div>

            <!-- Compliance & Verification Dates -->
            <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 1.25rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.75rem;">
                    Safety &amp; Compliance Records
                </div>
                <div class="vg-split-2">
                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--vg-text-muted); display: block;">Registration Expiry</span>
                            <strong style="color: var(--vg-navy-950); font-size: 0.95rem;"><?= format_date($vehicle['registration_expiry']) ?></strong>
                        </div>
                        <i class="fa-solid fa-shield-halved" style="color: var(--vg-success); font-size: 1.25rem;"></i>
                    </div>

                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--vg-text-muted); display: block;">Commercial Insurance Expiry</span>
                            <strong style="color: var(--vg-navy-950); font-size: 0.95rem;"><?= format_date($vehicle['insurance_expiry']) ?></strong>
                        </div>
                        <i class="fa-solid fa-file-contract" style="color: var(--vg-blue-500); font-size: 1.25rem;"></i>
                    </div>
                </div>
            </div>

            <!-- Operational Notice -->
            <div style="margin-top: 1.25rem; background: var(--vg-pearl-100); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem; font-size: 0.82rem; color: var(--vg-text-secondary); display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-circle-info" style="color: var(--vg-blue-500); font-size: 1rem;"></i>
                <span>Vehicle assignment is managed by VoyageGo fleet dispatch operations. Any mechanical defects or maintenance requests should be reported directly to office operations.</span>
            </div>
        </div>
    </div>
<?php endif; ?>
