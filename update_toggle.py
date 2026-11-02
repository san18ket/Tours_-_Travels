import sys

# 1. Update accounts.php
with open('app/Views/office/finance/accounts.php', 'r', encoding='utf-8') as f:
    c = f.read()

old_desktop_status = """                            $status = strtolower($acc['status']);
                            $isActive = $status === 'active';
                        ?>"""

new_desktop_status = """                            $status = strtolower($acc['status'] ?? 'active');
                            $isActive = ($status === 'active');
                            $statusClass = $isActive ? 'completed' : 'cancelled';
                            $statusText = $isActive ? 'Active' : 'Inactive';
                        ?>"""

if old_desktop_status in c:
    c = c.replace(old_desktop_status, new_desktop_status, 1)
    print('Updated desktop loop status vars')

old_desktop_td = """                            <!-- 6. Status (Toggle Button like other pages) -->
                            <td class="text-center">
                                <div class="cust-toggle-cell" id="accountToggleWrap-<?= (int)$acc['id'] ?>">
                                    <button type="button" 
                                            class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                            data-id="<?= (int)$acc['id'] ?>"
                                            data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                            onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                            title="Click to toggle status (currently <?= ucfirst($status) ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <span class="cust-toggle-pill <?= $isActive ? 'completed' : 'cancelled' ?>">
                                        <span class="toggle-pulse-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                                        <span class="toggle-label-text"><?= strtoupper($acc['status']) ?></span>
                                    </span>
                                </div>
                            </td>"""

new_desktop_td = """                            <!-- 6. Status (Toggle Switch like office/customers) -->
                            <td class="text-center">
                                <div class="cust-toggle-cell" id="accountToggleWrap-<?= (int)$acc['id'] ?>">
                                    <button type="button" 
                                            class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                            data-id="<?= (int)$acc['id'] ?>"
                                            data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                            onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                            title="Click to toggle status (currently <?= $statusText ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <span class="cust-toggle-pill <?= $statusClass ?>">
                                        <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                        <span class="toggle-label-text"><?= $statusText ?></span>
                                    </span>
                                </div>
                            </td>"""

if old_desktop_td in c:
    c = c.replace(old_desktop_td, new_desktop_td, 1)
    print('Updated desktop TD markup')

old_mobile_loop = """                    $status = strtolower($acc['status']);
                    $isActive = $status === 'active';
                ?>"""

new_mobile_loop = """                    $status = strtolower($acc['status'] ?? 'active');
                    $isActive = ($status === 'active');
                    $statusClass = $isActive ? 'completed' : 'cancelled';
                    $statusText = $isActive ? 'Active' : 'Inactive';
                ?>"""

if old_mobile_loop in c:
    c = c.replace(old_mobile_loop, new_mobile_loop, 1)
    print('Updated mobile loop status vars')

old_mobile_right = """                        <!-- Right: Status Toggle Switch + Balance -->
                        <div class="mv-right-col">
                            <div class="mv-status-toggle-wrap" onclick="event.preventDefault(); event.stopPropagation();">
                                <button type="button" 
                                        class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$acc['id'] ?>"
                                        data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                        onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                        title="Click to toggle status (currently <?= ucfirst($status) ?>)">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb">
                                            <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                        </span>
                                    </span>
                                </button>
                                <span class="mv-toggle-label-text <?= $isActive ? 'completed' : 'cancelled' ?>"><?= strtoupper($acc['status']) ?></span>
                            </div>
                            <div class="mv-price" style="color: #218765; font-weight: 800; font-size: 14.5px;">
                                <?= format_currency((float)$acc['current_balance']) ?>
                            </div>
                        </div>"""

new_mobile_right = """                        <!-- Right: Status Toggle Switch + Balance -->
                        <div class="mv-right-col">
                            <div class="mv-status-toggle-wrap" onclick="event.stopPropagation();">
                                <button type="button" 
                                        class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$acc['id'] ?>"
                                        data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                        onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                        title="Click to toggle status (currently <?= $statusText ?>)">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb">
                                            <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                        </span>
                                    </span>
                                </button>
                                <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                            <div class="mv-price" style="color: #218765; font-weight: 800; font-size: 14.5px;">
                                <?= format_currency((float)$acc['current_balance']) ?>
                            </div>
                        </div>"""

if old_mobile_right in c:
    c = c.replace(old_mobile_right, new_mobile_right, 1)
    print('Updated mobile right col markup')

old_js = """            if (data.success) {
                const isNowActive = (data.status === 'active');
                const statusTxt = isNowActive ? 'ACTIVE' : 'INACTIVE';

                // Update all matching toggle buttons (desktop table & mobile cards)
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

                // Update desktop table pills
                const wrap = document.getElementById('accountToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    if (pill) {
                        pill.className = 'cust-toggle-pill ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (dot) {
                        dot.className = 'toggle-pulse-dot ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (text) {
                        text.textContent = statusTxt;
                    }
                }

                // Update mobile card toggle labels
                document.querySelectorAll(`.mv-booking-card-wrapper .cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    const cardWrap = b.closest('.mv-booking-card-wrapper');
                    if (cardWrap) {
                        const lbl = cardWrap.querySelector('.mv-toggle-label-text');
                        if (lbl) {
                            lbl.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'cancelled');
                            lbl.textContent = statusTxt;
                        }
                    }
                });
            } else {
                alert(data.message || 'Failed to update account status.');
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            console.error('Account toggle error:', err);
            alert('An error occurred while updating status.');
        });"""

new_js = """            if (data.success) {
                const isNowActive = (data.status === 'active');
                const statusTxt = isNowActive ? 'Active' : 'Inactive';

                // Update all matching toggle buttons (desktop table & mobile cards)
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

                // Update desktop table pills
                const wrap = document.getElementById('accountToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    if (pill) {
                        pill.className = 'cust-toggle-pill ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (dot) {
                        dot.className = 'toggle-pulse-dot ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (text) {
                        text.textContent = statusTxt;
                    }
                }

                // Update mobile card toggle labels
                document.querySelectorAll(`.mv-booking-card-wrapper .cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    const cardWrap = b.closest('.mv-booking-card-wrapper');
                    if (cardWrap) {
                        const lbl = cardWrap.querySelector('.mv-toggle-label-text');
                        if (lbl) {
                            lbl.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'cancelled');
                            lbl.textContent = statusTxt;
                        }
                    }
                });

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Account '${accName}' set to ${statusTxt}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to update account status.', 'error');
                } else {
                    alert(data.message || 'Failed to update account status.');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            console.error('Account toggle error:', err);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('An error occurred while updating status.', 'error');
            } else {
                alert('An error occurred while updating status.');
            }
        });"""

if old_js in c:
    c = c.replace(old_js, new_js, 1)
    print('Updated toggleAccountStatus JS')

toggle_css_local = """
/* ========================================================================= */
/* Status Toggle Switch Component (Matching office/customers)                */
/* ========================================================================= */
.cust-toggle-cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.cust-status-toggle {
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease;
}

.cust-status-toggle:hover {
    transform: scale(1.06);
}

.cust-status-toggle:active {
    transform: scale(0.94);
}

.toggle-slider-track {
    width: 44px;
    height: 24px;
    border-radius: 9999px;
    background: #CBD5E1;
    position: relative;
    display: block;
    transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}

.cust-status-toggle.is-active .toggle-slider-track {
    background: #10B981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}

.toggle-slider-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    position: absolute;
    left: 3px;
    top: 3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}

.cust-status-toggle:active:not(.is-loading) .toggle-slider-thumb {
    width: 21px;
}

/* Small toggle size (for mobile cards) */
.cust-status-toggle.sm .toggle-slider-track {
    width: 36px;
    height: 20px;
}

.cust-status-toggle.sm .toggle-slider-thumb {
    width: 14px;
    height: 14px;
    left: 3px;
    top: 3px;
}

.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}

.toggle-spinner {
    font-size: 8px;
    color: #059669;
    line-height: 1;
}

.cust-status-toggle:not(.is-active) .toggle-spinner {
    color: #64748B;
}

.toggle-shake {
    animation: toggleShakeAnim 0.35s ease;
}

@keyframes toggleShakeAnim {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}

.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.toggle-pulse-dot.completed {
    background: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}
"""

style_tag = '<style>'
if style_tag in c and '.cust-status-toggle' not in c:
    c = c.replace(style_tag, style_tag + '\n' + toggle_css_local, 1)
    print('Inserted toggle_css_local into accounts.php')

with open('app/Views/office/finance/accounts.php', 'w', encoding='utf-8') as f:
    f.write(c)
print('Successfully updated accounts.php')

# 2. Update account_show.php
with open('app/Views/office/finance/account_show.php', 'r', encoding='utf-8') as f:
    show_c = f.read()

show_style_tag = '<style>'
if show_style_tag in show_c and '.cust-status-toggle' not in show_c:
    show_c = show_c.replace(show_style_tag, show_style_tag + '\n' + toggle_css_local, 1)
    print('Inserted toggle_css_local into account_show.php')

# Also add vgNotify to toggleAccountViewStatus in account_show.php
old_show_success = """                const pill = document.getElementById('heroStatusPill');
                const text = document.getElementById('heroStatusLabelText');
                if (pill) {
                    pill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
                }
                if (text) {
                    text.textContent = statusTxt;
                }
            } else {
                alert(data.message || 'Failed to toggle status.');
            }"""

new_show_success = """                const pill = document.getElementById('heroStatusPill');
                const text = document.getElementById('heroStatusLabelText');
                if (pill) {
                    pill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
                }
                if (text) {
                    text.textContent = statusTxt;
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Account status set to ${statusTxt}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to toggle status.', 'error');
                } else {
                    alert(data.message || 'Failed to toggle status.');
                }
            }"""

if old_show_success in show_c:
    show_c = show_c.replace(old_show_success, new_show_success, 1)
    print('Updated account_show.php JS notification')

with open('app/Views/office/finance/account_show.php', 'w', encoding='utf-8') as f:
    show_c = f.write(show_c)
print('Successfully updated account_show.php')
