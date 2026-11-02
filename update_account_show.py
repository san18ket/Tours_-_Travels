import re

with open('app/Views/office/finance/account_show.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Tab 2: Passbook Ledger
old_tab_ledger = """                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="modern-table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 140px;">Date &amp; Time</th>
                                    <th>Description &amp; Reference</th>
                                    <th style="width: 130px;">Category</th>
                                    <th style="width: 100px;" class="text-center">Type</th>
                                    <th style="text-align: right; width: 140px;">Amount</th>
                                    <th style="text-align: right; width: 140px;">Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $idx => $tx): 
                                    $isCredit = $tx['transaction_type'] === 'credit';
                                ?>
                                <tr>
                                    <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                        <?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem;">
                                            <?= e($tx['description'] ?? 'Ledger Transaction') ?>
                                        </div>
                                        <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                            <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 1px;">
                                                Ref: <?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? '') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral" style="font-size: 0.75rem;">
                                            <?= e($tx['category'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                            <?= $isCredit ? '+ CREDIT' : '- DEBIT' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                        <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem;">
                                        <?= isset($tx['balance_after']) ? format_currency((float)$tx['balance_after']) : '—' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>"""

new_tab_ledger = """                    <!-- Desktop Table (Screen Width >= 769px) -->
                    <div class="desktop-ledger-table">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="modern-table" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="width: 140px;">Date &amp; Time</th>
                                        <th>Description &amp; Reference</th>
                                        <th style="width: 130px;">Category</th>
                                        <th style="width: 100px;" class="text-center">Type</th>
                                        <th style="text-align: right; width: 140px;">Amount</th>
                                        <th style="text-align: right; width: 140px;">Balance After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $idx => $tx): 
                                        $isCredit = $tx['transaction_type'] === 'credit';
                                    ?>
                                    <tr>
                                        <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                            <?= $idx + 1 ?>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                            <?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem;">
                                                <?= e($tx['description'] ?? 'Ledger Transaction') ?>
                                            </div>
                                            <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 1px;">
                                                    Ref: <?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-neutral" style="font-size: 0.75rem;">
                                                <?= e($tx['category'] ?? 'General') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                                <?= $isCredit ? '+ CREDIT' : '- DEBIT' ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                            <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem;">
                                            <?= isset($tx['balance_after']) ? format_currency((float)$tx['balance_after']) : '—' ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards (Screen Width <= 768px - Native App Feel matching office/customers) -->
                    <div class="mobile-ledger-cards-list">
                        <?php foreach ($recentTransactions as $idx => $tx): 
                            $isCredit = $tx['transaction_type'] === 'credit';
                        ?>
                        <div class="mb-account-card">
                            <div class="mb-card-header">
                                <div class="mb-card-date">
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?></span>
                                </div>
                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+ CREDIT' : '- DEBIT' ?>
                                </span>
                            </div>
                            <div class="mb-card-body">
                                <h4 class="mb-card-title"><?= e($tx['description'] ?? 'Ledger Transaction') ?></h4>
                                <div class="mb-card-meta">
                                    <span class="badge badge-neutral" style="font-size: 0.72rem; padding: 2px 7px;"><?= e($tx['category'] ?? 'General') ?></span>
                                    <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                        <span class="mb-card-ref">Ref: <?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? '') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-card-footer">
                                <div class="mb-card-balance">
                                    <span class="label">After:</span>
                                    <span class="val"><?= isset($tx['balance_after']) ? format_currency((float)$tx['balance_after']) : '—' ?></span>
                                </div>
                                <div class="mb-card-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>"""

if old_tab_ledger in content:
    content = content.replace(old_tab_ledger, new_tab_ledger, 1)
    print('Updated Tab 2 (Passbook Ledger) with desktop vs mobile cards')
else:
    print('Could not find old_tab_ledger exactly, trying normalized whitespace')

# 2. Update Tab 3: Fund Transfers
old_tab_transfers = """                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="modern-table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 140px;">Date &amp; Time</th>
                                    <th>Direction &amp; Account</th>
                                    <th>Notes</th>
                                    <th style="text-align: right; width: 150px;">Transfer Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransfers as $idx => $trf): 
                                    $isCredit = $trf['transaction_type'] === 'credit';
                                ?>
                                <tr>
                                    <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                        <?= format_datetime($trf['transacted_at'] ?? $trf['created_at']) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                                <?= $isCredit ? 'Received From' : 'Transferred To' ?>
                                            </span>
                                            <strong style="color: var(--vg-navy-900); font-size: 0.9rem;">
                                                <?= e($trf['related_account_name'] ?? 'Other Vault') ?>
                                            </strong>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.84rem; color: var(--text-muted);">
                                        <?= e($trf['description'] ?? 'Internal Transfer') ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                        <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$trf['amount']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>"""

new_tab_transfers = """                    <!-- Desktop Table (Screen Width >= 769px) -->
                    <div class="desktop-transfers-table">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="modern-table" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="width: 140px;">Date &amp; Time</th>
                                        <th>Direction &amp; Account</th>
                                        <th>Notes</th>
                                        <th style="text-align: right; width: 150px;">Transfer Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransfers as $idx => $trf): 
                                        $isCredit = $trf['transaction_type'] === 'credit';
                                    ?>
                                    <tr>
                                        <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                            <?= $idx + 1 ?>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                            <?= format_datetime($trf['transacted_at'] ?? $trf['created_at']) ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                                    <?= $isCredit ? 'Received From' : 'Transferred To' ?>
                                                </span>
                                                <strong style="color: var(--vg-navy-900); font-size: 0.9rem;">
                                                    <?= e($trf['related_account_name'] ?? 'Other Vault') ?>
                                                </strong>
                                            </div>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted);">
                                            <?= e($trf['description'] ?? 'Internal Transfer') ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                            <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$trf['amount']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards (Screen Width <= 768px - Native App Feel matching office/customers) -->
                    <div class="mobile-transfers-cards-list">
                        <?php foreach ($recentTransfers as $idx => $trf): 
                            $isCredit = $trf['transaction_type'] === 'credit';
                        ?>
                        <div class="mb-account-card">
                            <div class="mb-card-header">
                                <div class="mb-card-date">
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?= format_datetime($trf['transacted_at'] ?? $trf['created_at']) ?></span>
                                </div>
                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? 'Received From' : 'Transferred To' ?>
                                </span>
                            </div>
                            <div class="mb-card-body">
                                <h4 class="mb-card-title"><?= e($trf['related_account_name'] ?? 'Other Vault') ?></h4>
                                <?php if (!empty($trf['description'])): ?>
                                    <p class="mb-card-notes"><?= e($trf['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-card-footer">
                                <span class="text-muted" style="font-size: 0.75rem;">Transfer Amount</span>
                                <div class="mb-card-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$trf['amount']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>"""

if old_tab_transfers in content:
    content = content.replace(old_tab_transfers, new_tab_transfers, 1)
    print('Updated Tab 3 (Fund Transfers) with desktop vs mobile cards')

# 3. Add Mobile Floating Action Button (FAB)
fab_html = """    <!-- Mobile Floating Quick Action Button (FAB) matching modern mobile apps -->
    <div class="mobile-view-fab-wrapper">
        <button type="button" class="mobile-view-fab" onclick="toggleFabMenu(event)" title="Quick Actions" aria-label="Quick Actions">
            <i class="fa-solid fa-bolt" id="fabIcon"></i>
        </button>
        <div class="mobile-fab-menu" id="mobileFabMenu" style="display: none;">
            <button type="button" class="mobile-fab-item" onclick="openTransferModal(<?= (int)$account['id'] ?>)">
                <span class="fab-item-label">Transfer Funds</span>
                <span class="fab-item-icon green"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
            </button>
            <button type="button" class="mobile-fab-item" onclick="openEditAccountModal()">
                <span class="fab-item-label">Edit Account</span>
                <span class="fab-item-icon primary"><i class="fa-solid fa-pen-to-square"></i></span>
            </button>
            <a href="<?= url('/office/finance/transactions?account_id=' . $account['id']) ?>" class="mobile-fab-item">
                <span class="fab-item-label">Full Passbook</span>
                <span class="fab-item-icon amber"><i class="fa-solid fa-book-open"></i></span>
            </a>
            <a href="<?= url('/office/finance/expenses') ?>" class="mobile-fab-item">
                <span class="fab-item-label">Record Expense</span>
                <span class="fab-item-icon purple"><i class="fa-solid fa-receipt"></i></span>
            </a>
        </div>
    </div>
"""

modal_comment = "<!-- ========================================================================= -->\n<!-- MODAL: Edit Financial Account"
if modal_comment in content and 'mobile-view-fab-wrapper' not in content:
    content = content.replace(modal_comment, fab_html + "\n" + modal_comment, 1)
    print('Inserted Mobile Floating Action Button (FAB)')

# 4. Enhance JavaScript functions
old_js_block = """    function switchTab(tabName) {
        document.querySelectorAll('.view-nav-pill').forEach(pill => {
            const isActive = pill.getAttribute('data-tab') === tabName;
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            pane.style.display = (pane.id === 'tabContent-' + tabName) ? 'block' : 'none';
        });
    }

    /* ========================================================================= */
    /* Header Dropdown Menu                                                      */
    /* ========================================================================= */
    function toggleMoreMenu(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('accountMoreMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu && !e.target.closest('.view-more-menu-wrapper')) {
            menu.style.display = 'none';
        }
    });"""

new_js_block = """    function switchTab(tabName) {
        document.querySelectorAll('.view-nav-pill').forEach(pill => {
            const isActive = pill.getAttribute('data-tab') === tabName;
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            pane.style.display = (pane.id === 'tabContent-' + tabName) ? 'block' : 'none';
        });

        // Scroll active tab pill smoothly into view on mobile
        const activePill = document.querySelector('.view-nav-pill.active');
        if (activePill && window.innerWidth <= 768) {
            activePill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    /* ========================================================================= */
    /* Header Dropdown Menu & Mobile Floating Action Button (FAB)                */
    /* ========================================================================= */
    function toggleMoreMenu(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('accountMoreMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    function toggleFabMenu(e) {
        if (e) e.stopPropagation();
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            const isOpen = (fabMenu.style.display !== 'none');
            fabMenu.style.display = isOpen ? 'none' : 'flex';
            if (fabIcon) {
                fabIcon.className = isOpen ? 'fa-solid fa-bolt' : 'fa-solid fa-xmark';
            }
        }
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu && !e.target.closest('.view-more-menu-wrapper')) {
            menu.style.display = 'none';
        }

        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu && !e.target.closest('.mobile-view-fab-wrapper')) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditAccountModal();
            closeTransferModal();
            const menu = document.getElementById('accountMoreMenu');
            if (menu) menu.style.display = 'none';
            const fabMenu = document.getElementById('mobileFabMenu');
            const fabIcon = document.getElementById('fabIcon');
            if (fabMenu) {
                fabMenu.style.display = 'none';
                if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
            }
        }
    });"""

if old_js_block in content:
    content = content.replace(old_js_block, new_js_block, 1)
    print('Updated tab switcher and dropdown / FAB JS')

# 5. Enhance openEditAccountModal and openTransferModal
old_modal_js = """    function openEditAccountModal() {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        document.getElementById('editAccountModalBackdrop').style.display = 'flex';
    }
    function closeEditAccountModal() {
        document.getElementById('editAccountModalBackdrop').style.display = 'none';
    }
    function toggleEditBankFields(type) {
        document.getElementById('editBankFields').style.display = (type === 'bank') ? 'block' : 'none';
    }

    /* ========================================================================= */
    /* Transfer Modal                                                            */
    /* ========================================================================= */
    function openTransferModal(fromId) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        if (fromId) {
            const selectEl = document.getElementById('from_account_id');
            if (selectEl) selectEl.value = fromId;
        }
        document.getElementById('transferModalBackdrop').style.display = 'flex';
    }
    function closeTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'none';
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }"""

new_modal_js = """    function openEditAccountModal() {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
        const modal = document.getElementById('editAccountModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeEditAccountModal() {
        const modal = document.getElementById('editAccountModalBackdrop');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    function toggleEditBankFields(type) {
        const fields = document.getElementById('editBankFields');
        if (fields) fields.style.display = (type === 'bank') ? 'block' : 'none';
    }

    /* ========================================================================= */
    /* Transfer Modal                                                            */
    /* ========================================================================= */
    function openTransferModal(fromId) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
        if (fromId) {
            const selectEl = document.getElementById('from_account_id');
            if (selectEl) selectEl.value = fromId;
        }
        const modal = document.getElementById('transferModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeTransferModal() {
        const modal = document.getElementById('transferModalBackdrop');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            const modal = document.getElementById(backdropId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    }"""

if old_modal_js in content:
    content = content.replace(old_modal_js, new_modal_js, 1)
    print('Updated openEditAccountModal and openTransferModal with body overflow handling')

# 6. Add complete CSS for modals, mobile cards, FAB, and responsive breakpoints
additional_css = """
/* ========================================================================= */
/* Custom Modal System (Scoped for Accounts View)                            */
/* ========================================================================= */
.modal-backdrop-custom {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 35, 30, 0.55) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999 !important;
    padding: 1rem;
    box-sizing: border-box;
}

.modal-dialog-custom {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(15, 35, 30, 0.35);
    border: 1px solid rgba(23, 107, 91, 0.18);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalSlideInAnim 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    z-index: 100000;
}

@keyframes modalSlideInAnim {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header-custom {
    padding: 1.15rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(22, 68, 59, 0.08);
    background: #FFFFFF;
    flex-shrink: 0;
}

.modal-close-custom {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #F1F5F9;
    border: none;
    font-size: 1.4rem;
    line-height: 1;
    color: #64748B;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.modal-close-custom:hover {
    background: #E2E8F0;
    color: #0F172A;
    transform: scale(1.05);
}

.modal-body-custom {
    padding: 1.25rem 1.35rem;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    flex: 1;
}

.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: #F8FAFC;
    flex-shrink: 0;
}

/* ========================================================================= */
/* Native Mobile Cards for Passbook & Transfers (Matching office/customers)  */
/* ========================================================================= */
.desktop-ledger-table,
.desktop-transfers-table {
    display: block;
}

.mobile-ledger-cards-list,
.mobile-transfers-cards-list {
    display: none;
}

.mb-account-card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.15s ease;
}

.mb-account-card:hover {
    border-color: rgba(23, 107, 91, 0.3);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.08);
}

.mb-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.mb-card-date {
    font-size: 0.74rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.mb-card-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mb-card-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
    line-height: 1.3;
}

.mb-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 2px;
}

.mb-card-ref {
    font-size: 0.72rem;
    color: #64748B;
}

.mb-card-notes {
    font-size: 0.8rem;
    color: #475569;
    margin: 0;
}

.mb-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #F1F5F9;
    margin-top: 2px;
}

.mb-card-balance {
    font-size: 0.76rem;
    color: #64748B;
}

.mb-card-balance .label {
    margin-right: 2px;
}

.mb-card-balance .val {
    font-weight: 700;
    color: #0F172A;
}

.mb-card-amount {
    font-size: 1.05rem;
    font-weight: 800;
}

.mb-card-amount.credit {
    color: #218765;
}

.mb-card-amount.debit {
    color: #DC2626;
}

/* ========================================================================= */
/* Mobile Floating Quick Action Button (FAB)                                 */
/* ========================================================================= */
.mobile-view-fab-wrapper {
    display: none;
}

@media (max-width: 768px) {
    .mobile-view-fab-wrapper {
        display: block !important;
        position: fixed !important;
        right: 18px !important;
        bottom: 85px !important; /* Clears mobile notched bottom nav */
        z-index: 1060 !important;
    }

    .mobile-view-fab {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
        color: #FFFFFF;
        border: none;
        box-shadow: 0 6px 20px rgba(23, 107, 91, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        -webkit-tap-highlight-color: transparent;
    }

    .mobile-view-fab:active {
        transform: scale(0.92);
    }

    .mobile-fab-menu {
        position: absolute;
        right: 0;
        bottom: calc(100% + 12px);
        display: flex;
        flex-direction: column;
        gap: 9px;
        align-items: flex-end;
        animation: fabSlideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fabSlideUp {
        from { opacity: 0; transform: translateY(12px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .mobile-fab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        padding: 0;
    }

    .fab-item-label {
        background: #FFFFFF;
        color: #0F172A;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 5px 11px;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        white-space: nowrap;
        border: 1px solid #E2E8F0;
    }

    .fab-item-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 0.95rem;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        flex-shrink: 0;
    }

    .fab-item-icon.green {
        background: #218765;
    }
    .fab-item-icon.primary {
        background: #176B5B;
    }
    .fab-item-icon.amber {
        background: #C78A35;
    }
    .fab-item-icon.purple {
        background: #8B5CF6;
    }
}
"""

# Replace the responsive breakpoints at the bottom
old_media_query = """/* Responsive Breakpoints matching office/customers/show.php */
@media (max-width: 991.98px) {
    .account-view-root {
        padding: 0.85rem 0.65rem 3rem 0.65rem;
    }
    .view-layout-split {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
        margin-bottom: 0.85rem;
    }
    .view-hero-scenery {
        display: none !important;
    }
    .view-hero-card {
        padding: 1.15rem;
        margin-bottom: 0.85rem;
    }
    .view-hero-meta-list {
        gap: 0.5rem;
        flex-direction: column;
        align-items: flex-start;
    }
    .view-hero-quick-actions {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .view-hero-quick-actions::-webkit-scrollbar {
        display: none;
    }
    .hero-qa-pill {
        flex-shrink: 0;
    }
    .view-nav-tabs {
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .view-nav-tabs::-webkit-scrollbar {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .account-view-root {
        padding: 0.65rem 0.5rem 3rem 0.5rem;
    }
    .view-header-bar {
        margin-bottom: 0.65rem;
    }
    .view-header-title {
        font-size: 1.15rem;
    }
    .view-header-subtitle {
        font-size: 0.76rem;
    }
    .view-header-btn {
        width: 36px;
        height: 36px;
        font-size: 0.88rem;
    }
    .view-hero-card {
        padding: 1rem 0.85rem;
        border-radius: 16px;
    }
    .view-hero-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .view-avatar-wrap {
        width: 64px;
        height: 64px;
        border-radius: 16px;
    }
    .view-avatar-initials {
        font-size: 1.6rem;
        border-radius: 14px;
    }
    .view-hero-name {
        font-size: 1.25rem;
    }
    .view-hero-name-row {
        gap: 0.45rem;
    }
    .view-hero-code-pill {
        font-size: 0.7rem;
        padding: 2px 7px;
    }
    .view-hero-meta-item {
        font-size: 0.8rem;
    }
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    .view-kpi-card {
        padding: 0.85rem 0.7rem;
        border-radius: 14px;
    }
    .kpi-icon-wrap {
        width: 30px;
        height: 30px;
        font-size: 0.82rem;
    }
    .kpi-label {
        font-size: 0.68rem;
    }
    .kpi-value {
        font-size: 1.15rem;
    }
    .kpi-footer-note {
        font-size: 0.68rem;
    }
    .view-nav-pill {
        padding: 0.45rem 0.85rem;
        font-size: 0.78rem;
    }
    .view-section-card {
        border-radius: 15px;
    }
    .section-card-header {
        padding: 0.8rem 1rem;
    }
    .section-card-body {
        padding: 0.9rem;
    }
    .view-kv-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    .view-kv-label {
        font-size: 0.68rem;
    }
    .view-kv-value {
        font-size: 0.86rem;
    }
    .liquidity-amount {
        font-size: 1.5rem;
    }
    .modal-dialog-custom {
        max-width: 95vw;
        border-radius: 14px;
    }
    .modal-body-custom {
        padding: 1rem;
        max-height: 72vh;
        overflow-y: auto;
    }
}"""

new_media_query = additional_css + """
/* ========================================================================= */
/* FAITHFUL MOBILE RESPONSIVENESS MATCHING office/customers/show.php         */
/* ========================================================================= */
@media (max-width: 991.98px) {
    .view-hero-scenery {
        display: none !important;
    }
    .view-hero-quick-actions {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .view-hero-quick-actions::-webkit-scrollbar {
        display: none;
    }
    .hero-qa-pill {
        flex-shrink: 0;
    }
}

@media (max-width: 768px) {
    .account-view-root {
        padding: 0.65rem 0.65rem 95px 0.65rem !important;
        /* 95px clearance for mobile docked bottom navbar */
    }

    /* 1. Header Bar on Mobile */
    .view-header-bar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        position: relative !important;
        z-index: 100 !important;
        margin-bottom: 0.85rem !important;
        gap: 0.5rem !important;
    }

    .view-header-left {
        display: flex !important;
        align-items: center !important;
        gap: 0.65rem !important;
        min-width: 0 !important;
        flex: 1 !important;
    }

    /* Hide long breadcrumb path on mobile so header actions aren't pushed out */
    .view-header-breadcrumbs {
        display: none !important;
    }

    .view-header-title-wrap {
        min-width: 0 !important;
        flex: 1 !important;
    }

    .view-header-title {
        font-size: 1.15rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.25 !important;
        margin: 0 !important;
    }

    .view-header-subtitle {
        font-size: 0.74rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        margin: 0 !important;
    }

    .view-header-right {
        display: flex !important;
        align-items: center !important;
        gap: 0.45rem !important;
        flex-shrink: 0 !important;
    }

    .view-header-btn {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        font-size: 0.88rem !important;
    }

    .view-dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        top: calc(100% + 8px) !important;
        z-index: 1050 !important;
        min-width: 210px !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2) !important;
    }

    /* 2. Hero Card on Mobile */
    .view-hero-card {
        padding: 1.1rem 1rem !important;
        border-radius: 18px !important;
        margin-bottom: 0.85rem !important;
    }

    .view-hero-content {
        gap: 0.85rem !important;
    }

    .view-avatar-wrap {
        width: 74px !important;
        height: 74px !important;
        border-radius: 18px !important;
    }

    .view-avatar-initials {
        font-size: 1.75rem !important;
        border-radius: 16px !important;
    }

    .view-hero-name {
        font-size: 1.2rem !important;
    }

    .hero-status-toggle-wrapper {
        padding: 2px 7px 2px 3px !important;
        gap: 6px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-track {
        width: 38px !important;
        height: 22px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-thumb {
        width: 16px !important;
        height: 16px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle.is-active .toggle-slider-thumb {
        transform: translateX(16px) !important;
    }

    .view-hero-meta-list {
        gap: 0.45rem !important;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .view-hero-meta-item {
        font-size: 0.8rem !important;
    }

    .view-hero-quick-actions {
        margin-top: 0.65rem !important;
        gap: 0.4rem !important;
    }

    .hero-qa-pill {
        padding: 0.35rem 0.7rem !important;
        font-size: 0.74rem !important;
    }

    /* 3. KPI Grid on Mobile (Exact 2x2 grid matching customers/show.php) */
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem !important;
        margin-bottom: 0.85rem !important;
    }

    .view-kpi-card {
        padding: 0.85rem 0.75rem !important;
        border-radius: 16px !important;
    }

    .kpi-icon-wrap {
        width: 32px !important;
        height: 32px !important;
        font-size: 0.85rem !important;
        border-radius: 8px !important;
    }

    .kpi-label {
        font-size: 0.7rem !important;
    }

    .kpi-value {
        font-size: 1.2rem !important;
    }

    .kpi-footer-note {
        font-size: 0.68rem !important;
    }

    /* 4. Horizontal Pill Tabs */
    .view-nav-tabs-wrapper {
        margin-bottom: 0.85rem !important;
    }

    .view-nav-tabs {
        padding: 4px !important;
        gap: 4px !important;
        scrollbar-width: none !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .view-nav-tabs::-webkit-scrollbar {
        display: none !important;
    }

    .view-nav-pill {
        padding: 0.55rem 0.95rem !important;
        font-size: 0.82rem !important;
        gap: 0.4rem !important;
    }

    /* 5. Content Layout & Tables vs Cards */
    .view-layout-split {
        grid-template-columns: 1fr !important;
        gap: 0.85rem !important;
    }

    .view-section-card {
        border-radius: 16px !important;
    }

    .section-card-header {
        padding: 0.9rem 1rem !important;
    }

    .section-card-body {
        padding: 1rem !important;
    }

    .view-kv-grid {
        grid-template-columns: 1fr 1fr !important;
        row-gap: 0.85rem !important;
        column-gap: 0.75rem !important;
    }

    .view-kv-label {
        font-size: 0.68rem !important;
    }

    .view-kv-value {
        font-size: 0.86rem !important;
    }

    /* Hide wide desktop tables and show native mobile cards */
    .desktop-ledger-table,
    .desktop-transfers-table {
        display: none !important;
    }

    .mobile-ledger-cards-list,
    .mobile-transfers-cards-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        padding: 0.85rem !important;
    }

    /* Modals on Mobile */
    .modal-backdrop-custom {
        padding: 0.75rem !important;
    }

    .modal-dialog-custom {
        max-width: 100% !important;
        max-height: 94vh !important;
        border-radius: 16px !important;
    }

    .modal-body-custom {
        padding: 1rem !important;
        max-height: 70vh !important;
    }

    .modal-footer-custom {
        padding: 0.85rem 1rem !important;
    }
}

/* Small Screens (<= 420px) */
@media (max-width: 420px) {
    .view-kv-grid {
        grid-template-columns: 1fr !important;
        gap: 0.65rem !important;
    }

    .view-hero-content {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.75rem !important;
    }

    .view-avatar-wrap {
        width: 68px !important;
        height: 68px !important;
    }

    .view-avatar-initials {
        font-size: 1.6rem !important;
    }

    .view-hero-name {
        font-size: 1.15rem !important;
    }

    .kpi-value {
        font-size: 1.1rem !important;
    }
}
"""

if old_media_query in content:
    content = content.replace(old_media_query, new_media_query, 1)
    print('Updated responsive breakpoints with full mobile styling and modal CSS')
else:
    print('old_media_query not matched exactly, checking substring')
    idx = content.find('/* Responsive Breakpoints matching office/customers/show.php */')
    if idx != -1:
        content = content[:idx] + new_media_query + '\n</style>\n'
        print('Replaced from Responsive Breakpoints comment onwards')

with open('app/Views/office/finance/account_show.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Saved updated account_show.php')
