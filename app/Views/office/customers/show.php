<?php
/**
 * VoyageGo — Customer Profile & Travel Ledger View
 */
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-container" aria-label="Breadcrumb" style="margin-bottom: 1rem;">
    <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--vg-text-muted);">
        <li>
            <a href="<?= url('/office/dashboard') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Dashboard</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li>
            <a href="<?= url('/office/customers') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Customers</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li style="color: var(--vg-navy-900); font-weight: 600;" aria-current="page"><?= e($customer['customer_code']) ?></li>
    </ol>
</nav>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); margin: 0;">
                <?= !empty($customer['title']) ? e($customer['title']) . '. ' : '' ?><?= e($customer['name']) ?>
            </h1>
            <?= render_status_badge($customer['status']) ?>
        </div>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0.25rem 0 0 0;">
            Customer Reference: <code><?= e($customer['customer_code']) ?></code> • Registered on <?= format_date($customer['created_at']) ?>
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-navy btn-sm">Edit Profile</a>
        <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn btn-sm" style="background: #10B981; color: #FFF; font-weight: 600;">+ New Booking</a>
        <a href="<?= url('/office/customers') ?>" class="btn btn-outline btn-sm">Back to Directory</a>
    </div>
</div>

<!-- Profile Grid -->
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;" class="vg-split-2">
    <!-- Left Column: Customer Profile Info -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Contact Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Contact Information</span>
            </div>
            <div class="card-body" style="font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Primary Mobile</span>
                    <strong style="color: var(--vg-navy-900); font-size: 1rem;"><a href="tel:<?= e($customer['phone']) ?>" style="color: inherit; text-decoration: none;"><?= e($customer['phone']) ?></a></strong>
                </div>

                <?php if (!empty($customer['alternate_phone'])): ?>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Alternate Phone</span>
                    <span><a href="tel:<?= e($customer['alternate_phone']) ?>" style="color: inherit; text-decoration: none;"><?= e($customer['alternate_phone']) ?></a></span>
                </div>
                <?php endif; ?>

                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Email Address</span>
                    <span><?= !empty($customer['email']) ? '<a href="mailto:' . e($customer['email']) . '">' . e($customer['email']) . '</a>' : '<em style="color:var(--vg-text-muted);">Not provided</em>' ?></span>
                </div>

                <?php if (!empty($customer['gender']) || !empty($customer['date_of_birth'])): ?>
                <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <?php if (!empty($customer['gender'])): ?>
                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Gender</span>
                        <span style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $customer['gender'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($customer['date_of_birth'])): ?>
                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Birth Date</span>
                        <span><?= format_date($customer['date_of_birth']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Address Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Billing &amp; Registered Address</span>
            </div>
            <div class="card-body" style="font-size: 0.9rem;">
                <?php if (!empty($customer['address_line']) || !empty($customer['city'])): ?>
                    <p style="margin: 0; line-height: 1.5; color: var(--vg-navy-900);">
                        <?= !empty($customer['address_line']) ? e($customer['address_line']) . '<br>' : '' ?>
                        <?= !empty($customer['city']) ? e($customer['city']) : '' ?><?= !empty($customer['state']) ? ', ' . e($customer['state']) : '' ?><?= !empty($customer['postal_code']) ? ' - ' . e($customer['postal_code']) : '' ?><br>
                        <strong><?= e($customer['country']) ?></strong>
                    </p>
                <?php else: ?>
                    <p style="margin: 0; color: var(--vg-text-muted); font-style: italic;">No physical address recorded on file.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Booking History Ledger -->
    <div class="vg-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="card-title">Travel Bookings &amp; Reservations Ledger</span>
                <span style="font-size: 0.82rem; color: var(--vg-text-muted); display: block;">Total lifetime reservations: <?= count($bookings) ?></span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($bookings)): ?>
                <div style="text-align: center; padding: 3rem 1.5rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🧳</div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.35rem;">No Bookings Recorded Yet</h4>
                    <p style="color: var(--vg-text-secondary); font-size: 0.85rem; margin-bottom: 1.25rem;">This customer profile does not have any active or past travel bookings.</p>
                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn btn-navy btn-sm">+ Create First Booking</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Tour / Route</th>
                                <th>Dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/office/bookings/' . $b['id']) ?>" style="font-weight: 700; color: var(--vg-blue-500); text-decoration: none;">
                                            <code><?= e($b['booking_code']) ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--vg-navy-900);">
                                            <?= !empty($b['tour_title']) ? e($b['tour_title']) : 'Custom Chauffeur Fleet Service' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                            <?= format_date($b['start_date']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= (int)$b['passenger_count'] ?>
                                    </td>
                                    <td>
                                        <strong style="color: var(--vg-navy-900);"><?= format_currency((float)$b['total_amount']) ?></strong>
                                    </td>
                                    <td>
                                        <?= render_status_badge($b['status']) ?>
                                    </td>
                                    <td>
                                        <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="btn btn-outline btn-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
