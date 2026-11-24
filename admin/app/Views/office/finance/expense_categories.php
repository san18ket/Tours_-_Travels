<?php
/**
 * BUSINESS EXPENSE CATEGORIES
 * Canonical Office Management & Relational Accounting System
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/expenses') . '" class="btn btn-outline"><i class="fa-solid fa-receipt"></i> <span>All Expenses</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openAddCategoryModal()"><i class="fa-solid fa-plus"></i> <span>Add Category</span></button>'
    . '</div>';

$activeCategories = $activeCategories ?? count(array_filter($categories, fn($c) => ($c['status'] ?? 'active') === 'active'));
?>

<div class="bookings-page-view finance-page-view" id="expenseCategoriesPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Expense Categories',
            'description' => 'Manage operational cost categories, cost tracking buckets, vehicle overhead classifications, and audit groupings.',
            'badge'       => 'Category Setup',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Expenses', 'url' => url('/office/finance/expenses')],
                ['label' => 'Categories', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="catKpiGrid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Categories</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" id="kpiActiveCats"><?= number_format($activeCategories) ?> <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">/ <?= number_format($totalCategories) ?></span></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Ready for expense recording</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/expense-categories') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Recorded Spend</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($totalCategorized) ?></div>
            <div class="booking-kpi-sub">
                <span>Cumulative expense amount</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Top Expense Category</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35; font-size: 1.15rem;"><?= e($topCategory) ?></div>
            <div class="booking-kpi-sub">
                <span>Highest operational outflow</span>
            </div>
        </div>
    </div>

    <!-- 3. Modern Table of Categories (Desktop) -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-tags" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Expense Head Catalog
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Categories:</span>
                <span class="total-records-badge" id="catalogTotalBadge"><?= number_format($totalCategories) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table" id="expenseCategoriesTable">
                <thead>
                    <tr>
                        <th class="table-col-index text-center col-priority-1">#</th>
                        <th class="table-col-name col-priority-1">Category Name</th>
                        <th class="table-col-code col-priority-2" data-column-priority="secondary">System Slug</th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional">Description &amp; Operational Scope</th>
                        <th class="table-col-amount col-priority-1 text-end">Total Spent</th>
                        <th class="table-col-index text-center col-priority-2" data-column-priority="secondary">Vouchers</th>
                        <th class="table-col-status text-center col-priority-1">Status</th>
                        <th class="table-col-actions text-end col-priority-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-solid fa-tags" style="color: var(--brand-primary);"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No Expense Categories Defined</h4>
                                <p class="text-muted" style="margin:0;">Create your first category head to start classifying business expenditures.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $i => $cat): 
                        $status = strtolower($cat['status'] ?? 'active');
                        $isActive = ($status === 'active');
                        $isDefault = !empty($cat['is_default']);
                        $spent = (float)($cat['total_spent'] ?? 0);
                        $vouchers = (int)($cat['voucher_count'] ?? $cat['expense_count'] ?? 0);
                    ?>
                    <tr id="categoryRow-<?= (int)$cat['id'] ?>">
                        <!-- 1. Serial -->
                        <td class="table-col-index text-center col-priority-1">
                            <div class="cust-serial-num"><?= $i + 1 ?></div>
                        </td>

                        <!-- 2. Category Name -->
                        <td class="table-col-name col-priority-1">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <strong style="color: var(--vg-navy-900); font-size: 0.92rem;"><?= e($cat['name']) ?></strong>
                                <?php if ($isDefault): ?>
                                    <span class="badge" style="font-size: 0.68rem; font-weight: 600; background: rgba(23, 107, 91, 0.1); color: var(--brand-primary); padding: 2px 6px; border-radius: 4px;">Default</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. System Slug (Relational Key) -->
                        <td class="table-col-code col-priority-2" data-column-priority="secondary">
                            <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 7px; border-radius: 4px; font-weight: 600; color: #475569;"><?= e($cat['slug']) ?></code>
                        </td>

                        <!-- 4. Description -->
                        <td class="col-priority-3 col-optional" data-column-priority="optional" style="font-size: 0.85rem; color: var(--text-secondary);">
                            <?= e($cat['description'] ?? 'Standard operational expense bucket') ?>
                        </td>

                        <!-- 5. Total Spent -->
                        <td class="table-col-amount col-priority-1 text-end">
                            <strong style="font-size: 0.92rem; color: <?= $spent > 0 ? '#D95757' : 'var(--text-muted)' ?>;">
                                <?= format_inr($spent) ?>
                            </strong>
                        </td>

                        <!-- 6. Vouchers Count -->
                        <td class="table-col-index text-center col-priority-2" data-column-priority="secondary" style="font-weight: 600; font-size: 0.86rem; color: var(--vg-navy-900);">
                            <?= number_format($vouchers) ?>
                        </td>

                        <!-- 7. Status (Professional Status Pill) -->
                        <td class="table-col-status text-center col-priority-1">
                            <div class="cust-toggle-cell" id="catToggleWrap-<?= (int)$cat['id'] ?>" style="justify-content: center;">
                                <span class="cust-toggle-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                    <span class="toggle-pulse-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"></span>
                                    <span class="toggle-label-text"><?= ucfirst($status) ?></span>
                                </span>
                            </div>
                        </td>

                        <!-- 8. Actions (One Single Professional Actions Control) -->
                        <td class="table-col-actions text-end col-priority-1" style="white-space: nowrap;">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleActionDropdown(this, event)" title="Actions" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <button type="button" class="action-dropdown-item edit" onclick="openEditCategoryModal(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', '<?= addslashes(e($cat['slug'])) ?>', '<?= addslashes(e($cat['description'] ?? '')) ?>', '<?= $status ?>', <?= $isDefault ? 1 : 0 ?>)">
                                        <i class="fa-solid fa-pen-to-square"></i> <span>Edit Category</span>
                                    </button>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger" onclick="confirmDeleteCategory(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', <?= $vouchers ?>, <?= $spent ?>, <?= $isDefault ? 1 : 0 ?>, '<?= $status ?>')">
                                        <i class="fa-solid fa-trash-can"></i> <span>Delete Category</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens (Zero Horizontal Scroll) -->
        <div class="cards-grid" id="expenseCategoriesMobileCards">
            <?php foreach ($categories as $cat): 
                $status = strtolower($cat['status'] ?? 'active');
                $isActive = ($status === 'active');
                $isDefault = !empty($cat['is_default']);
                $spent = (float)($cat['total_spent'] ?? 0);
                $vouchers = (int)($cat['voucher_count'] ?? $cat['expense_count'] ?? 0);
            ?>
            <div class="mv-booking-card-wrapper" id="mvCategoryCard-<?= (int)$cat['id'] ?>">
                <div class="mv-booking-card" style="padding: 1rem; border-radius: 12px; background: #ffffff; border: 1px solid rgba(22, 68, 59, 0.08); margin-bottom: 0.75rem;">
                    <!-- Top row: Name, Default badge, and Live Mobile Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                <strong style="font-size: 0.96rem; color: var(--vg-navy-900);"><?= e($cat['name']) ?></strong>
                                <?php if ($isDefault): ?>
                                    <span class="badge" style="font-size: 0.65rem; font-weight: 600; background: rgba(23, 107, 91, 0.1); color: var(--brand-primary); padding: 1px 5px; border-radius: 4px;">Default</span>
                                <?php endif; ?>
                            </div>
                            <div style="margin-top: 2px;">
                                <code style="font-size: 0.74rem; background: var(--page-background-secondary); padding: 1px 5px; border-radius: 4px; color: #475569;"><?= e($cat['slug']) ?></code>
                            </div>
                        </div>

                        <!-- Mobile Status Pill -->
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.2rem;">
                            <span class="cust-toggle-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 999px;">
                                <span class="toggle-pulse-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"></span>
                                <span class="toggle-label-text"><?= ucfirst($status) ?></span>
                            </span>
                            <?php if ($isDefault): ?>
                                <span style="font-size: 10px; color: #64748B;">System default</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($cat['description'])): ?>
                    <p style="font-size: 0.82rem; color: var(--text-secondary); margin: 0 0 0.75rem 0; line-height: 1.4;">
                        <?= e($cat['description']) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Bottom row: Total Spent & Vouchers -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(0, 0, 0, 0.05); padding-top: 0.65rem;">
                        <div>
                            <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">Total Spend</span>
                            <strong style="font-size: 1rem; color: <?= $spent > 0 ? '#D95757' : 'var(--text-muted)' ?>;">
                                <?= format_inr($spent) ?>
                            </strong>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">Vouchers</span>
                            <span style="font-size: 0.86rem; font-weight: 700; color: var(--vg-navy-900);"><?= $vouchers ?></span>
                        </div>
                    </div>

                    <!-- Single Horizontal Actions Row -->
                    <div class="mv-card-actions">
                        <div class="action-dropdown-wrap">
                            <button type="button" class="action-toggle-btn" onclick="toggleActionDropdown(this, event)" title="Actions" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                            </button>
                            <div class="action-dropdown-menu">
                                <button type="button" class="action-dropdown-item edit" onclick="openEditCategoryModal(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', '<?= addslashes(e($cat['slug'])) ?>', '<?= addslashes(e($cat['description'] ?? '')) ?>', '<?= $status ?>', <?= $isDefault ? 1 : 0 ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i> <span>Edit Category</span>
                                </button>
                                <?php if (!$isDefault): ?>
                                <button type="button" class="action-dropdown-item danger" onclick="confirmDeleteCategory(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', <?= $vouchers ?>, <?= $spent ?>, <?= $isDefault ? 1 : 0 ?>, '<?= $status ?>')">
                                    <i class="fa-solid fa-trash-can"></i> <span>Delete Category</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Add Expense Category                                               -->
<!-- ========================================================================= -->
<div id="addCategoryModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'addCategoryModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-tags" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Create Expense Category
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeAddCategoryModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/expense-categories') ?>" method="POST" id="addCategoryForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="add_cat_name">Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="add_cat_name" class="form-control" placeholder="e.g. Office Equipment / Software Tools" required>
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; display: block;">A unique relational system slug will be generated automatically.</span>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="add_cat_desc">Description &amp; Scope</label>
                    <textarea name="description" id="add_cat_desc" class="form-control" rows="2" placeholder="Explain what company operational disbursements belong here..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; color: var(--vg-navy-900);">
                        <input type="checkbox" name="status" value="active" checked style="width: 17px; height: 17px; accent-color: var(--brand-primary);">
                        <span>Active for recording new business expenses</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeAddCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Category</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Edit Expense Category                                              -->
<!-- ========================================================================= -->
<div id="editCategoryModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editCategoryModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Edit Expense Category
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditCategoryModal()">&times;</button>
        </div>
        <form id="editCategoryForm" action="" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="edit_cat_name">Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label">System Slug (Relational Key)</label>
                    <input type="text" id="edit_cat_slug_display" class="form-control" readonly style="background: var(--page-background-secondary); font-family: monospace; font-size: 0.85rem; color: #475569;" title="Relational slug is immutable to preserve historical ledger links.">
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="edit_cat_desc">Description &amp; Scope</label>
                    <textarea name="description" id="edit_cat_desc" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; color: var(--vg-navy-900);">
                        <input type="checkbox" name="status" id="edit_cat_status" value="active" style="width: 17px; height: 17px; accent-color: var(--brand-primary);">
                        <span>Active for recording new business expenses</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Update Category</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Safe Delete / Deactivation Confirmation                            -->
<!-- ========================================================================= -->
<div id="deleteCategoryModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'deleteCategoryModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="border-bottom: 1px solid rgba(0,0,0,0.06);">
            <h3 id="delModalTitle" style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #D95757;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.45rem;"></i>
                Manage Expense Category
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body-custom" style="padding: 1.25rem 1.5rem;">
            <!-- Category Banner -->
            <div style="background: rgba(22, 68, 59, 0.05); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <strong id="delCatName" style="color: var(--vg-navy-900); font-size: 1rem; display: block;"></strong>
                <span id="delCatMeta" style="font-size: 0.82rem; color: var(--text-muted);"></span>
            </div>

            <!-- Notice / Explanation Box -->
            <div id="delNoticeBox" style="font-size: 0.88rem; line-height: 1.5; margin-bottom: 1rem;"></div>

            <!-- Dynamic Action Choices -->
            <div id="delActionChoices" style="display: flex; flex-direction: column; gap: 0.65rem;"></div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Close</button>
        </div>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Category Status Toggle (Tactile AJAX Feedback)                            */
    /* ========================================================================= */
    function toggleCategoryStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'inactive' : 'active';
        const catName = btn.getAttribute('data-name') || 'Category';

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/finance/expense-categories/') ?>' + id + '/toggle-status';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                '_csrf_token': csrfToken,
                'csrf_token': csrfToken,
                'status': targetStatus
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data && data.success) {
                const isNowActive = (data.status === 'active');

                // 1. Update all matching toggle buttons (both desktop table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowActive) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle status (currently Active)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle status (currently Inactive)';
                    }
                });

                // 2. Update desktop table status pill
                const tableWrap = document.getElementById('catToggleWrap-' + id);
                if (tableWrap) {
                    const pill = tableWrap.querySelector('.cust-toggle-pill');
                    const dot = tableWrap.querySelector('.toggle-pulse-dot');
                    const text = tableWrap.querySelector('.toggle-label-text');
                    if (pill) {
                        pill.className = 'cust-toggle-pill ' + (isNowActive ? 'status-active' : 'status-inactive');
                    }
                    if (dot) {
                        dot.className = 'toggle-pulse-dot ' + (isNowActive ? 'status-active' : 'status-inactive');
                    }
                    if (text) {
                        text.textContent = isNowActive ? 'Active' : 'Inactive';
                    }
                }

                // 3. Update mobile status label
                const cardWrap = document.getElementById('mvCategoryCard-' + id);
                if (cardWrap) {
                    const mvLabel = cardWrap.querySelector('.mv-toggle-label-text');
                    if (mvLabel) {
                        mvLabel.className = 'mv-toggle-label-text ' + (isNowActive ? 'status-active' : 'status-inactive');
                        mvLabel.textContent = isNowActive ? 'Active' : 'Inactive';
                    }
                }

                // 4. Update KPI Active count
                updateKpiActiveCount();

                // 5. Toast notification
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message, 'success');
                }
            } else {
                throw new Error((data && data.message) || 'Status update failed.');
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error updating status. Please try again.', 'error');
            } else {
                console.error(err);
            }
        });
    }

    function updateKpiActiveCount() {
        const activeToggles = document.querySelectorAll('#expenseCategoriesTable .cust-status-toggle.is-active');
        const kpiVal = document.getElementById('kpiActiveCats');
        const totalRows = document.querySelectorAll('#expenseCategoriesTable tbody tr[id^="categoryRow-"]').length;
        if (kpiVal) {
            kpiVal.innerHTML = activeToggles.length + ' <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">/ ' + totalRows + '</span>';
        }
    }

    /* ========================================================================= */
    /* 3-Dot Action Dropdown Toggle (Universal Across Desktop Table & Mobile)    */
    /* ========================================================================= */
    function toggleActionDropdown(btn, e) {
        if (window.OfficeActionMenu && typeof window.OfficeActionMenu.toggle === 'function') {
            window.OfficeActionMenu.toggle(btn, e);
            return;
        }
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const wrap = btn.closest('.action-dropdown-wrap');
        const menu = btn.nextElementSibling;
        const isOpen = menu && (menu.classList.contains('open') || menu.classList.contains('is-open'));
        document.querySelectorAll('.action-dropdown-wrap.is-open').forEach(w => w.classList.remove('is-open'));
        document.querySelectorAll('.action-dropdown-menu.open, .action-dropdown-menu.is-open').forEach(m => m.classList.remove('open', 'is-open'));
        if (wrap && !isOpen) wrap.classList.add('is-open');
        if (menu && !isOpen) menu.classList.add('open', 'is-open');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown-wrap')) {
            document.querySelectorAll('.action-dropdown-wrap.is-open').forEach(w => w.classList.remove('is-open'));
            document.querySelectorAll('.action-dropdown-menu.open, .action-dropdown-menu.is-open').forEach(m => m.classList.remove('open', 'is-open'));
        }
    });

    /* ========================================================================= */
    /* Modal Handlers: Add & Edit                                                */
    /* ========================================================================= */
    function openAddCategoryModal() {
        document.getElementById('add_cat_name').value = '';
        document.getElementById('add_cat_desc').value = '';
        document.getElementById('addCategoryModalBackdrop').style.display = 'flex';
    }

    function closeAddCategoryModal() {
        document.getElementById('addCategoryModalBackdrop').style.display = 'none';
    }

    function openEditCategoryModal(id, name, slug, desc, status, isDefault) {
        document.getElementById('editCategoryForm').action = '<?= url('/office/finance/expense-categories') ?>/' + id;
        document.getElementById('edit_cat_name').value = name;
        document.getElementById('edit_cat_slug_display').value = slug;
        document.getElementById('edit_cat_desc').value = desc;
        document.getElementById('edit_cat_status').checked = (status === 'active');
        document.getElementById('editCategoryModalBackdrop').style.display = 'flex';
        document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
    }

    function closeEditCategoryModal() {
        document.getElementById('editCategoryModalBackdrop').style.display = 'none';
    }

    /* ========================================================================= */
    /* Safe Delete / Deactivation Confirmation                                    */
    /* ========================================================================= */
    function confirmDeleteCategory(id, name, vouchers, totalSpent, isDefault, currentStatus) {
        document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
        
        const titleEl = document.getElementById('delModalTitle');
        const nameEl = document.getElementById('delCatName');
        const metaEl = document.getElementById('delCatMeta');
        const noticeEl = document.getElementById('delNoticeBox');
        const actionsEl = document.getElementById('delActionChoices');

        nameEl.textContent = name;
        metaEl.textContent = vouchers + ' voucher(s) recorded • Total spent: ₹ ' + totalSpent.toLocaleString('en-IN', {minimumFractionDigits: 2});

        // Case A: Has historical expense vouchers linked
        if (vouchers > 0) {
            titleEl.innerHTML = '<i class="fa-solid fa-shield-halved" style="color: #C78A35; margin-right: 0.45rem;"></i> Financial Accounting Protection';
            noticeEl.innerHTML = `
                <div style="background: rgba(199, 138, 53, 0.08); border-left: 3px solid #C78A35; padding: 0.75rem 1rem; border-radius: 4px; color: #855314;">
                    <strong>Historical Vouchers Detected:</strong><br>
                    This category is currently linked to <strong>${vouchers}</strong> expense voucher(s) totaling <strong>₹ ${totalSpent.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>.
                    To preserve tax compliance, ledger balances, and audit records, permanent deletion is prevented.
                </div>
                <p style="margin-top: 0.75rem; color: var(--text-secondary);">
                    You can <strong>deactivate</strong> this category instead. Deactivation prevents any new expense records from using this category while keeping all historical financial reports 100% accurate.
                </p>
            `;

            actionsEl.innerHTML = `
                ${currentStatus === 'active' ? `
                    <button type="button" class="btn btn-primary" style="background: #C78A35; border-color: #C78A35; width: 100%; justify-content: center;" onclick="executeCategoryDeactivation(${id})">
                        <i class="fa-solid fa-power-off"></i> <span>Deactivate Category Now</span>
                    </button>
                ` : `
                    <div style="font-size: 0.85rem; color: #218765; font-weight: 600; text-align: center; padding: 0.5rem;">
                        <i class="fa-solid fa-circle-check"></i> Category is already deactivated for new entries.
                    </div>
                `}
            `;
        }
        // Case B: System default category
        else if (isDefault) {
            titleEl.innerHTML = '<i class="fa-solid fa-lock" style="color: var(--brand-primary); margin-right: 0.45rem;"></i> System Default Category';
            noticeEl.innerHTML = `
                <div style="background: rgba(23, 107, 91, 0.08); border-left: 3px solid var(--brand-primary); padding: 0.75rem 1rem; border-radius: 4px; color: var(--brand-primary);">
                    <strong>Protected System Head:</strong><br>
                    This is a core operational default category. Core categories cannot be removed permanently to ensure fleet workflows remain intact.
                </div>
                <p style="margin-top: 0.75rem; color: var(--text-secondary);">
                    You can toggle its active status off to hide it from day-to-day recording forms.
                </p>
            `;

            actionsEl.innerHTML = `
                ${currentStatus === 'active' ? `
                    <button type="button" class="btn btn-primary" style="background: var(--brand-primary); width: 100%; justify-content: center;" onclick="executeCategoryDeactivation(${id})">
                        <i class="fa-solid fa-power-off"></i> <span>Deactivate Category</span>
                    </button>
                ` : `
                    <div style="font-size: 0.85rem; color: #218765; font-weight: 600; text-align: center; padding: 0.5rem;">
                        <i class="fa-solid fa-circle-check"></i> Category is currently inactive.
                    </div>
                `}
            `;
        }
        // Case C: Clean category with 0 vouchers and not default
        else {
            titleEl.innerHTML = '<i class="fa-solid fa-trash-can" style="color: #D95757; margin-right: 0.45rem;"></i> Delete Expense Category';
            noticeEl.innerHTML = `
                <p style="color: var(--text-secondary); margin: 0;">
                    Are you sure you want to permanently delete <strong>${name}</strong>? There are no expense vouchers linked to this category. This action cannot be undone.
                </p>
            `;

            actionsEl.innerHTML = `
                <button type="button" class="btn btn-primary" style="background: #D95757; border-color: #D95757; width: 100%; justify-content: center;" onclick="executeCategoryDeletion(${id})">
                    <i class="fa-solid fa-trash-can"></i> <span>Confirm Permanent Delete</span>
                </button>
            `;
        }

        document.getElementById('deleteCategoryModalBackdrop').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteCategoryModalBackdrop').style.display = 'none';
    }

    function executeCategoryDeactivation(id) {
        const dummyBtn = document.querySelector(`.cust-status-toggle[data-id="${id}"]`) || document.createElement('button');
        dummyBtn.classList.add('is-active'); // Force toggle to inactive
        toggleCategoryStatus(id, dummyBtn);
        closeDeleteModal();
    }

    function executeCategoryDeletion(id) {
        const url = '<?= url('/office/finance/expense-categories/') ?>' + id + '/delete';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                '_csrf_token': csrfToken,
                'csrf_token': csrfToken
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            closeDeleteModal();
            if (data && data.success) {
                // Remove table row
                const row = document.getElementById('categoryRow-' + id);
                if (row) row.remove();

                // Remove mobile card
                const card = document.getElementById('mvCategoryCard-' + id);
                if (card) card.remove();

                // Update counts
                updateKpiActiveCount();
                const totalBadge = document.getElementById('catalogTotalBadge');
                if (totalBadge) {
                    const currentTotal = parseInt(totalBadge.textContent) || 1;
                    totalBadge.textContent = Math.max(0, currentTotal - 1);
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Category deleted successfully.', 'success');
                }
            } else {
                throw new Error((data && data.message) || 'Failed to delete category.');
            }
        })
        .catch(err => {
            closeDeleteModal();
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Could not delete category.', 'error');
            } else {
                console.error(err);
            }
        });
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>
