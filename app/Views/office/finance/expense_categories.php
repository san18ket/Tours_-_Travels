<?php
/**
 * MANASWI TOURS & TRAVELS — BUSINESS EXPENSE CATEGORIES
 * Matching Reference UI System
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/expenses') . '" class="btn btn-outline"><i class="fa-solid fa-receipt"></i> <span>All Expenses</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openAddCategoryModal()"><i class="fa-solid fa-plus"></i> <span>Add Category</span></button>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="expenseCategoriesPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Expense Categories',
            'description' => 'Manage operational cost categories, cost tracking buckets, vehicle overhead classifications, and audit groupings.',
            'image'       => 'header-invoices.jpg',
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
            <div class="booking-kpi-val"><?= number_format($totalCategories) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Configured expense heads</span>
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

    <!-- 3. Modern Table of Categories -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-tags" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Expense Head Catalog
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Categories:</span>
                <span class="total-records-badge"><?= number_format($totalCategories) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Category Name</th>
                        <th style="min-width: 140px;">System Slug</th>
                        <th style="min-width: 250px;">Description &amp; Operational Scope</th>
                        <th style="text-align: right; width: 140px;">Total Spent</th>
                        <th class="text-center" style="width: 100px;">Vouchers</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): 
                        $isActive = !empty($cat['is_active']);
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="cust-serial-num"><?= $i + 1 ?></div>
                        </td>
                        <td>
                            <strong style="color: var(--vg-navy-900); font-size: 0.92rem;"><?= e($cat['name']) ?></strong>
                        </td>
                        <td>
                            <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 6px; border-radius: 4px;"><?= e($cat['slug']) ?></code>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-secondary);">
                            <?= e($cat['description'] ?? 'Standard operational expense bucket') ?>
                        </td>
                        <td style="text-align: right;">
                            <strong style="font-size: 0.92rem; color: <?= !empty($cat['total_spent']) ? '#D95757' : 'var(--text-muted)' ?>;">
                                <?= format_inr($cat['total_spent'] ?? 0) ?>
                            </strong>
                        </td>
                        <td class="text-center" style="font-weight: 600; font-size: 0.86rem; color: var(--vg-navy-900);">
                            <?= number_format($cat['expense_count'] ?? 0) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $isActive ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                <?= $isActive ? 'Active' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline btn-sm" style="padding: 3px 8px;" onclick="openEditCategoryModal(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', '<?= addslashes(e($cat['description'] ?? '')) ?>', <?= $isActive ? 1 : 0 ?>)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
        <form action="<?= url('/office/finance/expense-categories') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="add_cat_name">Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="add_cat_name" class="form-control" placeholder="e.g. Fuel & Travel / Vehicle Maintenance" required>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="add_cat_desc">Description</label>
                    <textarea name="description" id="add_cat_desc" class="form-control" rows="2" placeholder="Explain what expenses belong in this category..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Active for recording business expenses</span>
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
                    <label class="form-label" for="edit_cat_desc">Description</label>
                    <textarea name="description" id="edit_cat_desc" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="edit_cat_active" value="1">
                        <span>Active for recording business expenses</span>
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

<script>
    function openAddCategoryModal() {
        document.getElementById('addCategoryModalBackdrop').style.display = 'flex';
    }
    function closeAddCategoryModal() {
        document.getElementById('addCategoryModalBackdrop').style.display = 'none';
    }
    function openEditCategoryModal(id, name, desc, isActive) {
        document.getElementById('editCategoryForm').action = '<?= url('/office/finance/expense-categories') ?>/' + id;
        document.getElementById('edit_cat_name').value = name;
        document.getElementById('edit_cat_desc').value = desc;
        document.getElementById('edit_cat_active').checked = isActive === 1;
        document.getElementById('editCategoryModalBackdrop').style.display = 'flex';
    }
    function closeEditCategoryModal() {
        document.getElementById('editCategoryModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

<style>
.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 35, 30, 0.45);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}
.modal-dialog-custom {
    background: #ffffff;
    border-radius: var(--vg-radius-lg, 18px);
    box-shadow: 0 20px 45px rgba(22, 68, 59, 0.22);
    border: 1px solid rgba(23, 107, 91, 0.15);
    width: 100%;
    max-width: 520px;
    overflow: hidden;
    animation: modalSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(18px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header-custom {
    padding: 1.15rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(22, 68, 59, 0.08);
}
.modal-close-custom {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
}
.modal-body-custom {
    padding: 1.25rem 1.35rem;
}
.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: var(--page-background);
}
</style>
