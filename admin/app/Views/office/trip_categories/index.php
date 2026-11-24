<div class="bookings-page-view">
    <!-- 1. Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('trip_category.title', 'Trip Categories'),
            'description' => __('trip_category.description', 'Manage simple operational trip categories for travel reservations, billing manifests, and fleet dispatching.'),
            'badge'       => __('trip_category.badge', 'Master Classification'),
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.bookings', 'Bookings'), 'url' => url('/office/bookings')],
                ['label' => __('trip_category.title', 'Trip Categories'), 'url' => ''],
            ],
            'actions'     => '<button type="button" class="btn btn-primary" onclick="openAddCategoryModal()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('trip_category.add_category', 'Add Category')) . '</span></button>',
        ]) ?>
    </div>

    <!-- 2. KPI Summary Cards -->
    <div class="booking-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
        <div class="booking-kpi-card is-selected">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('trip_category.total_categories', 'Total Categories')) ?></span>
                <span class="booking-kpi-icon icon-teal"><i class="fa-solid fa-tags"></i></span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalCount ?? 0) ?></div>
            <div class="booking-kpi-sub"><?= e(__('trip_category.authoritative_master', 'Authoritative master data')) ?></div>
        </div>
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('trip_category.active_categories', 'Active Categories')) ?></span>
                <span class="booking-kpi-icon icon-emerald"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="booking-kpi-val"><?= number_format($activeCount ?? 0) ?></div>
            <div class="booking-kpi-sub"><?= e(__('trip_category.selectable_new', 'Selectable for new bookings')) ?></div>
        </div>
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('trip_category.inactive_categories', 'Inactive Categories')) ?></span>
                <span class="booking-kpi-icon icon-amber"><i class="fa-solid fa-ban"></i></span>
            </div>
            <div class="booking-kpi-val"><?= number_format($inactiveCount ?? 0) ?></div>
            <div class="booking-kpi-sub"><?= e(__('trip_category.preserved_history', 'Preserved for historical bookings')) ?></div>
        </div>
    </div>

    <!-- 3. Desktop Table & Mobile Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title"><?= e(__('trip_category.list_title', 'Trip Categories List')) ?></h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('trip_category.categories_label', 'Categories:')) ?></span>
                <span class="total-records-badge"><?= number_format($totalCount ?? 0) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="categoriesTable">
                <thead>
                    <tr>
                        <th class="table-col-index text-center col-priority-1"><?= e(__('common.order', 'Order')) ?></th>
                        <th class="table-col-name col-priority-1"><?= e(__('trip_category.category_name', 'Category Name')) ?></th>
                        <th class="table-col-code col-priority-2"><?= e(__('trip_category.slug_identifier', 'Slug Identifier')) ?></th>
                        <th class="col-priority-3 col-optional"><?= e(__('trip_category.description_purpose', 'Description & Intended Purpose')) ?></th>
                        <th class="table-col-index text-center col-priority-2"><?= e(__('trip_category.bookings_using', 'Bookings Using')) ?></th>
                        <th class="table-col-status text-center col-priority-1"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="table-col-actions text-end col-priority-1"><?= e(__('table.action', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat):
                        $isActive = ($cat['status'] === 'active');
                        $usage = (int)($cat['usage_count'] ?? 0);
                    ?>
                    <tr id="catRow-<?= (int)$cat['id'] ?>">
                        <td class="table-col-index text-center col-priority-1">
                            <span class="cust-serial-num"><?= (int)$cat['sort_order'] ?></span>
                        </td>
                        <td class="table-col-name col-priority-1">
                            <div class="table-cell-truncate" style="font-weight: 700; color: #0F172A; font-size: 0.95rem;" title="<?= e($cat['name']) ?>">
                                <?= e($cat['name']) ?>
                            </div>
                        </td>
                        <td class="table-col-code col-priority-2">
                            <code style="background: rgba(23,107,91,0.08); color: #176B5B; padding: 2px 7px; border-radius: 6px; font-size: 11.5px; font-weight: 600;">
                                <?= e($cat['slug']) ?>
                            </code>
                        </td>
                        <td class="col-priority-3 col-optional">
                            <div class="table-cell-truncate" style="font-size: 13px; color: #475569; max-width: 380px;" title="<?= e($cat['description'] ?? '') ?>">
                                <?= !empty($cat['description']) ? e($cat['description']) : '<span style="color:#94a3b8;font-style:italic;">' . e(__('trip_category.no_description', 'No description provided')) . '</span>' ?>
                            </div>
                        </td>
                        <td class="table-col-index text-center col-priority-2">
                            <span style="display: inline-flex; align-items: center; gap: 5px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 9999px; padding: 3px 10px; font-size: 12px; font-weight: 700; color: #334155;">
                                <i class="fa-solid fa-calendar-check" style="font-size: 10px; color: #64748B;"></i>
                                <?= $usage ?> <?= e(__('sidebar.bookings', 'Bookings')) ?>
                            </span>
                        </td>
                        <td class="table-col-status text-center col-priority-1">
                            <div class="cust-toggle-cell" id="catToggleWrap-<?= (int)$cat['id'] ?>" style="justify-content: center;">
                                <span class="cust-toggle-pill <?= $isActive ? 'in-progress' : 'cancelled' ?>">
                                    <span class="toggle-pulse-dot <?= $isActive ? 'in-progress' : 'cancelled' ?>"></span>
                                    <span class="toggle-label-text"><?= $isActive ? e(__('status.active', 'Active')) : e(__('status.inactive', 'Inactive')) ?></span>
                                </span>
                            </div>
                        </td>
                        <td class="table-col-actions text-end col-priority-1" style="white-space: nowrap;">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <button type="button" class="action-dropdown-item edit" 
                                             onclick='openEditCategoryModal(<?= json_encode($cat, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="fa-solid fa-pen-to-square"></i> <span><?= e(__('trip_category.edit_category', 'Edit Category')) ?></span>
                                    </button>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger" 
                                            onclick="openDeleteCategoryModal(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', <?= $usage ?>)">
                                        <i class="fa-solid fa-trash-can"></i> <span><?= e(__('trip_category.delete_category', 'Delete Category')) ?></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Touch Cards List -->
        <div class="cards-grid">
            <?php foreach ($categories as $cat):
                $isActive = ($cat['status'] === 'active');
                $usage = (int)($cat['usage_count'] ?? 0);
            ?>
            <div class="mv-category-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:1.15rem; margin-bottom:0.85rem; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                    <div>
                        <div style="font-weight:700; color:#0F172A; font-size:1.05rem;"><?= e($cat['name']) ?></div>
                        <code style="font-size:11px; color:#176B5B;"><?= e($cat['slug']) ?></code>
                    </div>
                    <div style="text-align: right;">
                        <span class="cust-toggle-pill <?= $isActive ? 'completed' : 'cancelled' ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 9999px;">
                            <span class="toggle-pulse-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                            <span><?= $isActive ? e(__('status.active', 'Active')) : e(__('status.inactive', 'Inactive')) ?></span>
                        </span>
                        <div style="font-size:11px; font-weight:600; color:#64748B; margin-top: 3px;"><?= e(__('common.order', 'Order')) ?>: <?= (int)$cat['sort_order'] ?></div>
                    </div>
                </div>
                <?php if (!empty($cat['description'])): ?>
                    <p style="font-size:12.5px; color:#475569; margin:0.4rem 0 0.75rem; line-height:1.4;"><?= e($cat['description']) ?></p>
                <?php endif; ?>
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:0.65rem; margin-top:0.45rem;">
                    <span style="font-size:12px; font-weight:600; color:#475569;">
                        <i class="fa-solid fa-calendar-check" style="color:#176B5B;"></i> <?= $usage ?> <?= e(__('sidebar.bookings', 'Bookings')) ?>
                    </span>
                    <span style="font-size: 11px; color: #64748B;"><?= e(__('trip_category.category_singular', 'Category')) ?></span>
                </div>
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                        </button>
                        <div class="action-dropdown-menu">
                            <button type="button" class="action-dropdown-item edit" 
                                    onclick='openEditCategoryModal(<?= json_encode($cat, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                <i class="fa-solid fa-pen-to-square"></i> <span><?= e(__('trip_category.edit_category', 'Edit Category')) ?></span>
                            </button>
                            <div class="action-dropdown-divider"></div>
                            <button type="button" class="action-dropdown-item danger" 
                                    onclick="openDeleteCategoryModal(<?= (int)$cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>', <?= $usage ?>)">
                                <i class="fa-solid fa-trash-can"></i> <span><?= e(__('trip_category.delete_category', 'Delete Category')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- Modal: Add Trip Category                                                  -->
<!-- ========================================================================= -->
<div id="addCategoryModal" class="custom-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1rem;">
    <div class="custom-modal-content" style="background:#fff; border-radius:20px; max-width:480px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:1.35rem 1.65rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(23,107,91,0.12); color:#176B5B; display:flex; align-items:center; justify-content:center; font-size:1.05rem;">
                    <i class="fa-solid fa-tag"></i>
                </div>
                <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:#0F172A;"><?= e(__('trip_category.add_category', 'Add Trip Category')) ?></h3>
            </div>
            <button type="button" onclick="closeModal('addCategoryModal')" style="background:none; border:none; font-size:1.35rem; color:#64748B; cursor:pointer;" aria-label="<?= e(__('btn.close', 'Close')) ?>">&times;</button>
        </div>
        <form action="<?= url('/office/trip-categories') ?>" method="POST" id="addCategoryForm" style="padding:1.5rem 1.65rem;">
            <?= csrf_field() ?>
            <div style="margin-bottom:1.15rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.category_name', 'Category Name')) ?> <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="add_cat_name" required placeholder="<?= e(__('trip_category.name_placeholder', 'e.g. Wedding, Airport Trip, Family Trip')) ?>" class="modern-input-pill" style="width:100%;">
            </div>
            <div style="margin-bottom:1.15rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.description_purpose', 'Description / Purpose')) ?></label>
                <textarea name="description" id="add_cat_desc" rows="3" placeholder="<?= e(__('trip_category.desc_placeholder', 'Briefly describe what trips use this category...')) ?>" class="modern-input-pill" style="width:100%; height:auto; border-radius:12px; padding:0.75rem 1rem;"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.35rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.display_order', 'Display Order')) ?></label>
                    <input type="number" name="sort_order" id="add_cat_sort" value="14" min="0" class="modern-input-pill" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('table.status', 'Status')) ?></label>
                    <select name="status" id="add_cat_status" class="modern-input-pill" style="width:100%;">
                        <option value="active" selected><?= e(__('status.active', 'Active')) ?></option>
                        <option value="inactive"><?= e(__('status.inactive', 'Inactive')) ?></option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid #f1f5f9; padding-top:1.15rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addCategoryModal')"><?= e(__('btn.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <?= e(__('trip_category.save_category', 'Save Category')) ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal: Edit Trip Category                                                 -->
<!-- ========================================================================= -->
<div id="editCategoryModal" class="custom-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1rem;">
    <div class="custom-modal-content" style="background:#fff; border-radius:20px; max-width:480px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:1.35rem 1.65rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(23,107,91,0.12); color:#176B5B; display:flex; align-items:center; justify-content:center; font-size:1.05rem;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:#0F172A;"><?= e(__('trip_category.edit_category', 'Edit Trip Category')) ?></h3>
            </div>
            <button type="button" onclick="closeModal('editCategoryModal')" style="background:none; border:none; font-size:1.35rem; color:#64748B; cursor:pointer;" aria-label="<?= e(__('btn.close', 'Close')) ?>">&times;</button>
        </div>
        <form method="POST" id="editCategoryForm" style="padding:1.5rem 1.65rem;">
            <?= csrf_field() ?>
            <div style="margin-bottom:1.15rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.category_name', 'Category Name')) ?> <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="edit_cat_name" required class="modern-input-pill" style="width:100%;">
            </div>
            <div style="margin-bottom:1.15rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.description_purpose', 'Description / Purpose')) ?></label>
                <textarea name="description" id="edit_cat_desc" rows="3" class="modern-input-pill" style="width:100%; height:auto; border-radius:12px; padding:0.75rem 1rem;"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.35rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('trip_category.display_order', 'Display Order')) ?></label>
                    <input type="number" name="sort_order" id="edit_cat_sort" min="0" class="modern-input-pill" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;"><?= e(__('table.status', 'Status')) ?></label>
                    <select name="status" id="edit_cat_status" class="modern-input-pill" style="width:100%;">
                        <option value="active"><?= e(__('status.active', 'Active')) ?></option>
                        <option value="inactive"><?= e(__('status.inactive', 'Inactive')) ?></option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid #f1f5f9; padding-top:1.15rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCategoryModal')"><?= e(__('btn.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <?= e(__('trip_category.update_category', 'Update Category')) ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal: Delete or Disable Confirmation                                     -->
<!-- ========================================================================= -->
<div id="deleteCategoryModal" class="custom-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1rem;">
    <div class="custom-modal-content" style="background:#fff; border-radius:20px; max-width:440px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:1.35rem 1.65rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:#dc2626;" id="delModalTitle"><?= e(__('trip_category.confirm_removal', 'Confirm Removal')) ?></h3>
            <button type="button" onclick="closeModal('deleteCategoryModal')" style="background:none; border:none; font-size:1.35rem; color:#64748B; cursor:pointer;" aria-label="<?= e(__('btn.close', 'Close')) ?>">&times;</button>
        </div>
        <form method="POST" id="deleteCategoryForm" style="padding:1.5rem 1.65rem;">
            <?= csrf_field() ?>
            <p id="delModalMessage" style="font-size:0.95rem; color:#334155; line-height:1.5; margin:0 0 1.25rem;">
                <?= e(__('trip_category.delete_confirm_question', 'Are you sure you want to remove this category?')) ?>
            </p>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('deleteCategoryModal')"><?= e(__('btn.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-danger" id="delModalSubmitBtn">
                    <i class="fa-solid fa-trash-can"></i> <?= e(__('btn.confirm', 'Confirm')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddCategoryModal() {
    var m = document.getElementById('addCategoryModal');
    if (m) {
        m.style.display = 'flex';
        document.getElementById('add_cat_name').focus();
    }
}

function openEditCategoryModal(cat) {
    var form = document.getElementById('editCategoryForm');
    if (!form || !cat) return;
    form.action = '<?= url('/office/trip-categories') ?>/' + cat.id;
    document.getElementById('edit_cat_name').value = cat.name || '';
    document.getElementById('edit_cat_desc').value = cat.description || '';
    document.getElementById('edit_cat_sort').value = cat.sort_order || 0;
    document.getElementById('edit_cat_status').value = cat.status || 'active';
    var m = document.getElementById('editCategoryModal');
    if (m) m.style.display = 'flex';
}

function openDeleteCategoryModal(id, name, usageCount) {
    var form = document.getElementById('deleteCategoryForm');
    var msg = document.getElementById('delModalMessage');
    var btn = document.getElementById('delModalSubmitBtn');
    if (!form || !msg || !btn) return;
    form.action = '<?= url('/office/trip-categories') ?>/' + id + '/delete';

    var noticeTemplate = <?= json_encode(__('modal.disable_category_notice', ['name' => '{name}', 'count' => '{count}'])) ?>;
    var confirmDeleteTemplate = <?= json_encode(__('trip_category.delete_confirm_single', ['name' => '{name}'])) ?>;
    var disableBtnText = <?= json_encode(__('modal.disable_category_btn', 'Disable Category')) ?>;
    var deleteBtnText = <?= json_encode(__('trip_category.delete_category', 'Delete Category')) ?>;

    if (usageCount > 0) {
        msg.innerHTML = noticeTemplate.replace('{name}', '<strong>' + escapeHtml(name) + '</strong>').replace('{count}', '<strong>' + usageCount + '</strong>');
        btn.innerHTML = '<i class="fa-solid fa-ban"></i> ' + disableBtnText;
        btn.className = 'btn btn-warning';
    } else {
        msg.innerHTML = confirmDeleteTemplate.replace('{name}', '<strong>' + escapeHtml(name) + '</strong>');
        btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> ' + deleteBtnText;
        btn.className = 'btn btn-danger';
    }

    var m = document.getElementById('deleteCategoryModal');
    if (m) m.style.display = 'flex';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function closeModal(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'none';
}

function toggleCategoryStatus(id, btn, event) {
    if (event) { event.preventDefault(); event.stopPropagation(); }
    if (!btn || btn.disabled) return;
    btn.disabled = true;

    var track = btn.querySelector('.toggle-slider-track');
    var spinner = btn.querySelector('.toggle-spinner');
    if (spinner) spinner.style.display = 'inline-block';

    fetch('<?= url('/office/trip-categories') ?>/' + id + '/toggle-status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_token() ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (spinner) spinner.style.display = 'none';
        if (data.success) {
            if (data.is_active) {
                btn.classList.add('is-active');
                btn.setAttribute('aria-checked', 'true');
            } else {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-checked', 'false');
            }
            var wrap = document.getElementById('catToggleWrap-' + id);
            if (wrap) {
                var pill = wrap.querySelector('.cust-toggle-pill');
                var text = wrap.querySelector('.toggle-label-text');
                var dot = wrap.querySelector('.toggle-pulse-dot');
                if (pill) {
                    pill.className = 'cust-toggle-pill ' + (data.is_active ? 'in-progress' : 'cancelled');
                }
                if (dot) {
                    dot.className = 'toggle-pulse-dot ' + (data.is_active ? 'in-progress' : 'cancelled');
                }
                if (text) {
                    text.textContent = data.is_active ? <?= json_encode(__('status.active', 'Active')) ?> : <?= json_encode(__('status.inactive', 'Inactive')) ?>;
                }
            }
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message, 'success');
            }
        } else {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || <?= json_encode(__('trip_category.status_toggle_failed', 'Status toggle failed.')) ?>, 'error');
            }
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        if (spinner) spinner.style.display = 'none';
        if (typeof window.vgNotify === 'function') {
            window.vgNotify(<?= json_encode(__('common.something_went_wrong', 'An unexpected error occurred.')) ?>, 'error');
        }
    });
}
</script>

