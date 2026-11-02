<?php
/**
 * Patch app/Views/driver/trips.php to match app/Views/driver/history.php
 * for Top KPI Cards and Filter Design UI across all devices (LG and SM)
 */

$file = __DIR__ . '/../app/Views/driver/trips.php';
$content = file_get_contents($file);

$isCrlf = strpos($content, "\r\n") !== false;
if ($isCrlf) {
    $content = str_replace("\r\n", "\n", $content);
}

// 1. Update date input in desktop filter bar to use .fbc-date without inline style
$oldDate = '<input type="date" name="date" id="dateInput" class="form-control" value="<?= e($date ?? \'\') ?>" style="font-size:0.85rem; padding: 0.5rem 0.75rem; border: 1.5px solid #CBD5E1; border-radius: 10px;" title="Filter by Pickup Date">';
$newDate = '<input type="date" name="date" id="dateInput" class="fbc-date" value="<?= e($date ?? \'\') ?>" title="Filter by Pickup Date">';
if (strpos($content, $oldDate) !== false) {
    $content = str_replace($oldDate, $newDate, $content);
    echo "1. Updated desktop date input markup\n";
} else {
    echo "1. Desktop date input not found!\n";
}

// 2. Update mobile filter panel date input
$oldMvDate = '<div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvDateInput" style="font-weight: 600; font-size: 0.82rem; color: #475569;">Date:</label>
                    <input type="date" name="date" id="mvDateInput" class="form-control" value="<?= e($date ?? \'\') ?>" style="font-size: 0.85rem; padding: 0.4rem 0.65rem;">
                </div>';
$newMvDate = '<div class="mv-filter-row" style="margin-bottom: 0.65rem;">
                    <label for="mvDateInput">Date:</label>
                    <input type="date" name="date" id="mvDateInput" value="<?= e($date ?? \'\') ?>">
                </div>';
if (strpos($content, $oldMvDate) !== false) {
    $content = str_replace($oldMvDate, $newMvDate, $content);
    echo "2. Updated mobile filter date input markup\n";
} else {
    echo "2. Mobile filter date input not found!\n";
}

// 3. Replace <style> block with comprehensive history-identical stylesheet
$styleStart = strpos($content, '<style>');
if ($styleStart !== false) {
    $newStyles = <<<'CSS'
<style>
/* ========================================================================= */
/* Executive Chauffeur Trips UI & Layout Styles (Matching Driver History)   */
/* ========================================================================= */

.bookings-page-view {
    padding: 1.5rem;
    max-width: 1600px;
    margin: 0 auto;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
}

/* 1. Sleek AJAX Filtering Animations & Loading State */
.recent-bookings-section {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
    position: relative;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), filter 0.22s ease;
}

.recent-bookings-section.is-filtering {
    opacity: 0.42;
    pointer-events: none;
    filter: grayscale(0.2);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #E2E8F0;
}

.section-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.total-records-box {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #64748B;
}

.total-records-badge {
    background: #0F172A;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.8rem;
}

/* 2. Top KPI Summary Cards (Exact Match to Driver History Grid) */
.booking-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.booking-kpi-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.booking-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #CBD5E1;
}

.booking-kpi-card.is-selected {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.booking-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.booking-kpi-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.booking-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-teal    { background: #ECFDF5; color: #059669; }
.icon-emerald { background: #E0F2FE; color: #0284C7; }
.icon-amber   { background: #FEF3C7; color: #D97706; }
.icon-navy    { background: #F1F5F9; color: #475569; }

.booking-kpi-val {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.booking-kpi-sub {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #64748B;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10B981;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.kpi-filter-tag {
    color: #94A3B8;
    font-size: 0.75rem;
}

/* 3. Compact Filter Bar (Exact Match to Driver History Architecture) */
.filter-bar-compact {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    gap: 0.75rem;
    flex-wrap: wrap;
}

.fbc-search {
    position: relative;
    flex: 1;
    min-width: 260px;
}

.fbc-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: 0.9rem;
}

.fbc-search input {
    width: 100%;
    padding: 0.55rem 2.25rem 0.55rem 2.25rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    outline: none;
    color: #1E293B;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.fbc-search input:focus {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.fbc-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    font-size: 0.85rem;
}

.filter-bar-compact select,
.filter-bar-compact .fbc-date {
    padding: 0.55rem 1rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    color: #1E293B;
    background-color: #fff;
    cursor: pointer;
    outline: none;
    height: 38px;
    box-sizing: border-box;
    transition: all 0.15s ease;
}

.filter-bar-compact select:focus,
.filter-bar-compact .fbc-date:focus {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.fbc-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fbc-btn {
    padding: 0.55rem 1rem;
    border-radius: 8px;
    border: 1px solid #CBD5E1;
    background: #F8FAFC;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    height: 38px;
    box-sizing: border-box;
}

.fbc-btn:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.fbc-btn-clear {
    background: #FEE2E2;
    color: #DC2626;
    border-color: #FECACA;
}

.fbc-btn-clear:hover {
    background: #FCA5A5;
    border-color: #F87171;
}

.fbc-actions-end {
    margin-left: auto;
}

/* 4. Table Elements & Status Pills */
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.warning {
    background: #FEF3C7;
    color: #D97706;
}

.cust-toggle-pill.info {
    background: #E0F2FE;
    color: #0284C7;
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

.toggle-pulse-dot.warning {
    background: #D97706;
    box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.25);
}

.toggle-pulse-dot.info {
    background: #0284C7;
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}

.modern-table-container {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}

.modern-table thead th {
    background: #F8FAFC;
    color: #475569;
    font-weight: 700;
    padding: 0.85rem 1rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #E2E8F0;
}

.modern-table tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #F1F5F9;
    color: #334155;
    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: #F8FAFC;
}

.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    font-size: 0.88rem;
    padding: 3px 6px;
}

.cust-table-avatar-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 44px;
    height: 44px;
}

.cust-table-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.18);
    background: #E8F4F0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cust-table-monogram {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    text-transform: uppercase;
}

.cust-client-details-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cust-name-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cust-full-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0C2B52;
    text-decoration: none;
    transition: color 0.15s ease;
}

.cust-full-name:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-code-pill {
    display: inline-flex;
    align-items: center;
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 1px 7px;
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.cust-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.cust-primary-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    transition: color 0.15s ease;
}

.cust-primary-phone:hover {
    color: #176B5B;
}

.cust-phone-icon {
    font-size: 0.76rem;
    color: #176B5B;
}

.cust-bookings-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cust-bookings-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #334155;
    max-width: 280px;
}

.cust-address-line {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    font-weight: 600;
    color: #0F172A;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}

.cust-address-sub {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.3;
}

/* 5. RESPONSIVE BREAKPOINTS (Exact Match to Driver History LG vs SM) */
.mv-bookings-header,
.cards-grid {
    display: none;
}

@media (max-width: 991px) {
    .booking-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .bookings-page-view {
        padding: 0.75rem;
    }

    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container,
    .section-header {
        display: none !important;
    }

    .mv-bookings-header {
        display: block !important;
        margin-bottom: 1rem;
    }

    .mv-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .mv-top-bar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mv-back-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1E293B;
        text-decoration: none;
    }

    .mv-page-title {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
        color: #0F172A;
    }

    .mv-add-btn {
        background: #0EA5E9;
        color: #fff;
        padding: 0.45rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mv-search-form {
        display: flex;
        gap: 6px;
        margin-bottom: 0.75rem;
    }

    .mv-search-input-box {
        position: relative;
        flex: 1;
    }

    .mv-search-ico {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 0.85rem;
    }

    .mv-search-input-box input {
        width: 100%;
        padding: 0.5rem 2rem 0.5rem 2rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 0.85rem;
        box-sizing: border-box;
    }

    .mv-search-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94A3B8;
        cursor: pointer;
    }

    .mv-filter-toggle {
        padding: 0.5rem 0.75rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        cursor: pointer;
    }

    .mv-filter-toggle.active {
        background: #0EA5E9;
        color: #fff;
        border-color: #0EA5E9;
    }

    .mv-filter-panel {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .mv-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mv-filter-row label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
    }

    .mv-filter-row select,
    .mv-filter-row input {
        padding: 0.35rem 0.65rem;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        font-size: 0.82rem;
        color: #1E293B;
        background: #fff;
    }

    .mv-status-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .mv-status-scroll::-webkit-scrollbar {
        display: none;
    }

    .mv-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .mv-pill.active {
        background: #0EA5E9;
        color: #fff;
        border-color: #0EA5E9;
    }

    .mv-pill-cnt {
        background: rgba(0,0,0,0.06);
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
    }

    .mv-pill.active .mv-pill-cnt {
        background: rgba(255,255,255,0.25);
    }

    .cards-grid {
        display: flex !important;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.75rem;
    }

    .mv-booking-card-wrapper {
        position: relative;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .mv-booking-card {
        display: flex;
        align-items: flex-start;
        padding: 0.85rem;
        text-decoration: none;
        gap: 10px;
    }

    .mv-thumb-wrap {
        flex-shrink: 0;
    }

    .mv-thumb-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #E2E8F0;
        color: #334155;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .mv-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .mv-tour-title {
        font-weight: 700;
        color: #0F172A;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-date-duration,
    .mv-passenger,
    .mv-vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-right-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }

    .mv-price {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .mv-card-actions {
        position: absolute;
        bottom: 8px;
        right: 8px;
    }

    .mv-action-dots-btn {
        background: transparent;
        border: none;
        color: #94A3B8;
        font-size: 0.95rem;
        cursor: pointer;
        padding: 4px;
    }

    .card-btns-popup {
        position: absolute;
        bottom: 100%;
        right: 0;
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        gap: 4px;
        padding: 6px;
        z-index: 50;
    }

    .card-btns-popup.open,
    .card-btns-popup.show {
        display: flex;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
        align-items: center;
    }
}

@media (min-width: 769px) {
    .mv-bookings-header,
    .cards-grid {
        display: none !important;
    }
    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container,
    .section-header {
        display: block !important;
    }
}
</style>
CSS;
    $content = substr($content, 0, $styleStart) . $newStyles;
    echo "3. Replaced stylesheet in trips.php\n";
} else {
    echo "3. <style> tag not found!\n";
}

if ($isCrlf) {
    $content = str_replace("\n", "\r\n", $content);
}

file_put_contents($file, $content);
echo "Trips view updated successfully!\n";
