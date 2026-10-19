<?php
/**
 * VoyageGo — Customer Directory
 * Responsive luxury customer listing with search, filtering, KPI metrics, and mobile cards.
 */
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0 0 0.25rem 0;">Customer Directory</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Comprehensive guest profiles, corporate travel accounts, and reservation ledgers.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/customers/create') ?>" class="btn btn-navy" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Customer</span>
        </a>
    </div>
</div>

<!-- KPI Metrics Ribbon -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon info" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--vg-blue-500); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Total Clients</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalCustomers) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon success" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--vg-success); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Active Profiles</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalActive) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon warning" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(100, 116, 139, 0.1); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Inactive Accounts</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalInactive ?? 0) ?></div>
        </div>
    </div>
</div>

<!-- Filter Toolbar Card -->
<div class="vg-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="<?= url('/office/customers') ?>" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;" class="vg-split-2">
        <!-- Search Field -->
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="search" style="font-size: 0.82rem; font-weight: 600;">Search Customers</label>
            <input type="text" id="search" name="search" class="form-control" placeholder="Search by ID, name, mobile, email, city..." value="<?= e($search) ?>">
        </div>

        <!-- Status Filter -->
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="status" style="font-size: 0.82rem; font-weight: 600;">Profile Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
            </select>
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-navy" style="white-space: nowrap;">Filter</button>
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/customers') ?>" class="btn btn-outline" title="Reset Filters">&#x2715;</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Customers Listing Table & Cards -->
<div class="vg-card">
    <?php if (empty($customers)): ?>
        <div style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.5rem;">No Customer Profiles Found</h3>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem auto;">
                <?= (!empty($search) || !empty($selectedType) || !empty($selectedStatus)) ? 'No customers match the current filter query. Try resetting your filters.' : 'Your customer directory is empty. Begin by adding your first guest profile.' ?>
            </p>
            <a href="<?= url('/office/customers/create') ?>" class="btn btn-navy">
                + Register First Customer
            </a>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client Details</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/office/customers/' . $c['id']) ?>" style="font-weight: 700; color: var(--vg-blue-500); text-decoration: none;">
                                    <code><?= e($c['customer_code']) ?></code>
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-900);">
                                    <?= !empty($c['title']) ? e($c['title']) . '. ' : '' ?><?= e($c['name']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                    <a href="tel:<?= e($c['phone']) ?>" style="color: inherit; text-decoration: none;"><?= e($c['phone']) ?></a>
                                </div>
                                <?php if (!empty($c['email'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--vg-text-muted);"><?= e($c['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                    <?= !empty($c['city']) ? e($c['city']) : '—' ?><?= !empty($c['state']) ? ', ' . e($c['state']) : '' ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem;">
                                    <?= (int)$c['total_bookings'] ?>
                                </span>
                            </td>
                            <td>
                                <?= render_status_badge($c['status']) ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                    <a href="<?= url('/office/customers/' . $c['id']) ?>" class="btn btn-outline btn-sm" title="View Profile &amp; Bookings">
                                        View
                                    </a>
                                    <a href="<?= url('/office/customers/' . $c['id'] . '/edit') ?>" class="btn btn-outline btn-sm" title="Edit Profile">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper" style="padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--vg-pearl-200); flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalCustomers) ?> total customers)
                </div>
                <div style="display: flex; gap: 0.4rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('/office/customers?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-outline btn-sm">&larr; Previous</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="<?= url('/office/customers?page=' . $p . '&search=' . urlencode($search) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-sm <?= $p === $currentPage ? 'btn-navy' : 'btn-outline' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('/office/customers?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-outline btn-sm">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
