<?php
$empFullName = e($employee['first_name'] . ' ' . $employee['last_name']);
$headerActions = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;">'
    . '<a href="' . url('/office/employees') . '" class="btn btn-outline btn-sm">All Staff</a>';
if (auth_role() === 'admin') {
    $headerActions .= '<a href="' . url('/office/employees/' . $employee['id'] . '/edit') . '" class="btn btn-navy btn-sm">Edit Profile</a>';
}
$headerActions .= '</div>';
?>
<?= render_page_header([
    'title'       => $empFullName,
    'subtitle'    => e($employee['employee_code']) . ' • ' . e($employee['designation']) . ' (' . e(ucfirst(str_replace('_', ' ', $employee['department']))) . ')',
    'breadcrumbs' => [
        ['label' => 'Staff Directory', 'url' => url('/office/employees')],
        ['label' => $empFullName, 'url' => ''],
    ],
    'actions'     => $headerActions,
    'image'       => 'header-employees.jpg',
    'badge'       => ucfirst($employee['status'] ?? 'active'),
]) ?>

<div style="max-width: 900px; margin: 0 auto;">

    <!-- Overview Card -->
    <div class="vg-card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <span class="card-title">Employee Credentials &amp; Position</span>
            <?= render_status_badge($employee['status']) ?>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                <div class="vg-card-field">
                    <span class="vg-card-label">Employee Code</span>
                    <span class="vg-card-value" style="font-family: monospace; font-size: 1.05rem; font-weight: 700; color: var(--vg-blue-500);">
                        <?= e($employee['employee_code']) ?>
                    </span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Primary Mobile Phone</span>
                    <span class="vg-card-value" style="font-weight: 700;">
                        <a href="tel:<?= e($employee['phone']) ?>" style="color: var(--vg-navy-900); text-decoration: none;"><?= e($employee['phone']) ?></a>
                    </span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Alternate Phone</span>
                    <span class="vg-card-value"><?= e($employee['alternate_phone'] ?? '—') ?></span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Corporate Email</span>
                    <span class="vg-card-value">
                        <?php if (!empty($employee['email'])): ?>
                            <a href="mailto:<?= e($employee['email']) ?>" style="color: var(--vg-blue-500);"><?= e($employee['email']) ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Department</span>
                    <span class="vg-card-value" style="text-transform: capitalize; font-weight: 600;">
                        <?= e(str_replace('_', ' ', $employee['department'])) ?>
                    </span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Employment Contract</span>
                    <span class="vg-card-value" style="text-transform: capitalize;">
                        <?= e(str_replace('_', ' ', $employee['employment_type'])) ?>
                    </span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Date of Joining</span>
                    <span class="vg-card-value"><?= format_date($employee['joining_date']) ?></span>
                </div>

                <div class="vg-card-field">
                    <span class="vg-card-label">Emergency Contact</span>
                    <span class="vg-card-value"><?= e($employee['emergency_contact'] ?? '—') ?></span>
                </div>
            </div>

            <?php if (!empty($employee['address'])): ?>
                <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--vg-pearl-200);">
                    <span class="vg-card-label">Residential Address</span>
                    <div style="font-size: 0.92rem; color: var(--vg-text-primary); margin-top: 0.25rem;"><?= e($employee['address']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chauffeur / Duty Link if Department is Chauffeur -->
    <?php if ($employee['department'] === 'fleet_chauffeur'): ?>
        <div class="vg-card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <span class="card-title">Commercial Chauffeur Compliance</span>
                <?php if (!empty($employee['duty_status'])): ?>
                    <span class="badge <?= $employee['duty_status'] === 'on_duty' ? 'badge-success' : ($employee['duty_status'] === 'on_trip' ? 'badge-info' : 'badge-neutral') ?>">
                        <?= strtoupper(e(str_replace('_', ' ', $employee['duty_status']))) ?>
                    </span>
                <?php else: ?>
                    <span class="badge badge-warning">Profile Incomplete</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                    <div class="vg-card-field">
                        <span class="vg-card-label">Driving License Number</span>
                        <span class="vg-card-value" style="font-family: monospace; font-weight: 700; color: var(--vg-navy-900);">
                            <?= e($employee['license_number'] ?? 'Not Recorded') ?>
                        </span>
                    </div>

                    <div class="vg-card-field">
                        <span class="vg-card-label">License Expiry</span>
                        <span class="vg-card-value"><?= !empty($employee['license_expiry']) ? format_date($employee['license_expiry']) : '—' ?></span>
                    </div>

                    <div class="vg-card-field">
                        <span class="vg-card-label">Driving Experience</span>
                        <span class="vg-card-value"><?= !empty($employee['experience_years']) ? (int)$employee['experience_years'] . ' Years' : '—' ?></span>
                    </div>

                    <div class="vg-card-field">
                        <span class="vg-card-label">Customer Rating</span>
                        <span class="vg-card-value" style="color: #F59E0B; font-weight: 700;">★ <?= number_format((float)($employee['rating'] ?? 5.0), 1) ?> / 5.0</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- System Login Account Link -->
    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Attached System Login Account</span>
        </div>
        <div class="card-body">
            <?php if (!empty($employee['linked_username'])): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900);">
                            @<?= e($employee['linked_username']) ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                            Role: <span class="badge badge-navy"><?= e($employee['linked_role']) ?></span>
                            Status: <?= render_status_badge($employee['linked_user_status']) ?>
                        </div>
                    </div>
                    <?php if (auth_role() === 'admin'): ?>
                    <div>
                        <a href="<?= url('/office/users/' . $employee['user_id'] . '/edit') ?>" class="btn btn-outline btn-sm">Manage User Account</a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="color: var(--vg-text-secondary); font-size: 0.9rem;">
                    This employee does not currently have a system login account. They are registered solely for administrative HR and payroll roster purposes.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
