<?php
/**
 * Chauffeur Notification Center — Dedicated Driver Portal
 * Pixel-Perfect Mobile Responsive Card UI matching Reference Image
 */

$currStatus = strtolower($status ?? '');
$totRec     = (int)($totalRecords ?? count($notifications));
$currP      = (int)($page ?? 1);
$pSize      = (int)($perPage ?? 10);
$from       = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to         = min($currP * $pSize, $totRec);

$buildFilterUrl = function(array $overrides = []) use ($search, $status, $pSize) {
    $q = [];
    $s  = array_key_exists('search', $overrides) ? $overrides['search'] : $search;
    $st = array_key_exists('status', $overrides) ? $overrides['status'] : $status;
    $pp = array_key_exists('per_page', $overrides) ? $overrides['per_page'] : $pSize;
    $pg = $overrides['page'] ?? null;

    if (!empty($s))  $q['search']   = $s;
    if (!empty($st)) $q['status']   = $st;
    if (!empty($pp) && $pp != 10)   $q['per_page'] = $pp;
    if (!empty($pg) && $pg > 1)     $q['page']     = $pg;

    return url('/driver/notifications' . (!empty($q) ? '?' . http_build_query($q) : ''));
};

// Split into Urgent (Red Section) and Upcoming/Recent (Amber Section) matching Reference Image
$urgentList = [];
$upcomingList = [];
foreach ($notifications as $n) {
    if (empty($n['is_read']) || ($n['type'] ?? '') === 'trip_assigned') {
        $urgentList[] = $n;
    } else {
        $upcomingList[] = $n;
    }
}
$unreadCount = (int)($unreadNotifCount ?? 0);
?>

<div class="driver-notif-page-wrapper">
    <!-- Main Notification Card (Matches Reference Image UI) -->
    <div class="notif-ref-card">
        <!-- 1. Card Header: Alert Icon + Title + Red Count Badge -->
        <div class="notif-ref-head">
            <div class="notif-ref-head-left">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="notif-ref-head-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h3 class="notif-ref-head-title">Duty Notifications &amp; Alerts</h3>
            </div>
            <div class="notif-ref-head-right">
                <?php if ($unreadCount > 0): ?>
                    <button type="button" class="notif-ref-markall-btn" onclick="markAllNotificationsPageRead()" title="Mark all as read">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Mark all read</span>
                    </button>
                    <span class="notif-ref-head-badge" id="driverPageNotifBadge"><?= $unreadCount ?></span>
                <?php else: ?>
                    <span class="notif-ref-head-badge notif-ref-badge-green" title="All caught up">
                        <i class="fa-solid fa-check"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Mobile-Friendly Search & Status Tabs -->
        <div class="notif-ref-toolbar">
            <form action="<?= url('/driver/notifications') ?>" method="GET" class="notif-ref-search-form" id="driverNotifFilterForm">
                <div class="notif-ref-search-input-wrap">
                    <svg class="notif-ref-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" name="search" class="notif-ref-search-input" placeholder="Search by trip code, route, passenger..." value="<?= e($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= $buildFilterUrl(['search' => '']) ?>" class="notif-ref-search-clear" title="Clear search">&times;</a>
                    <?php endif; ?>
                </div>
                <?php if (!empty($status)): ?>
                    <input type="hidden" name="status" value="<?= e($status) ?>">
                <?php endif; ?>
            </form>

            <div class="notif-ref-pills">
                <a href="<?= $buildFilterUrl(['status' => '', 'page' => 1]) ?>" class="notif-ref-pill <?= empty($currStatus) ? 'active' : '' ?>">
                    All <span class="pill-count"><?= number_format($kpis['total_all'] ?? $totRec) ?></span>
                </a>
                <a href="<?= $buildFilterUrl(['status' => 'unread', 'page' => 1]) ?>" class="notif-ref-pill <?= $currStatus === 'unread' ? 'active' : '' ?>">
                    Unread <?= !empty($kpis['total_unread']) ? '<span class="pill-count pill-count-red">' . $kpis['total_unread'] . '</span>' : '' ?>
                </a>
                <a href="<?= $buildFilterUrl(['status' => 'trip_assigned', 'page' => 1]) ?>" class="notif-ref-pill <?= $currStatus === 'trip_assigned' ? 'active' : '' ?>">
                    Trip Duties
                </a>
                <a href="<?= $buildFilterUrl(['status' => 'read', 'page' => 1]) ?>" class="notif-ref-pill <?= $currStatus === 'read' ? 'active' : '' ?>">
                    Read
                </a>
            </div>
        </div>

        <!-- 3. Notification Items Feed (Exact Sectioned Layout matching Reference Image) -->
        <div class="notif-ref-feed" id="driverNotifFeed">
            <?php if (!empty($urgentList)): ?>
                <!-- Section 1: Red Header (Matches Reference EXPIRED Section) -->
                <div class="notif-ref-section-title notif-ref-sec-red">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <span>NEW DUTY ASSIGNMENTS</span>
                </div>
                <div class="notif-ref-section-items">
                    <?php foreach ($urgentList as $n): ?>
                        <?php
                            $payload = $n['payload'] ?? [];
                            $isUnread = empty($n['is_read']);
                            $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                            $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');

                            if (!empty($payload['dispatch_code'])) {
                                $pickup = $payload['pickup_address'] ?? 'Pickup';
                                $drop   = $payload['drop_address'] ?? 'Drop';
                                $title  = $payload['dispatch_code'] . ' (' . $pickup . ' → ' . $drop . ')';
                            } else {
                                $title = $n['title'];
                            }

                            $subParts = [];
                            if (!empty($payload['reporting_time'])) {
                                $subParts[] = 'Duty: ' . $payload['reporting_time'];
                            }
                            if (!empty($payload['vehicle_name'])) {
                                $veh = $payload['vehicle_name'];
                                if (!empty($payload['vehicle_number'])) $veh .= ' (' . $payload['vehicle_number'] . ')';
                                $subParts[] = $veh;
                            }
                            if (!empty($payload['customer_name'])) {
                                $subParts[] = 'Guest: ' . $payload['customer_name'];
                            }
                            $sub = !empty($subParts) ? implode(' • ', $subParts) : time_ago($n['created_at']);
                        ?>
                        <a href="<?= $targetUrl ?>" class="notif-ref-item <?= $isUnread ? 'is-unread' : '' ?>" id="page-notif-<?= (int)$n['id'] ?>" onclick="markPageNotifRead(<?= (int)$n['id'] ?>)">
                            <div class="notif-ref-item-icon notif-ref-icon-red">
                                <i class="fa-solid fa-route"></i>
                            </div>
                            <div class="notif-ref-item-info">
                                <div class="notif-ref-item-head">
                                    <span class="notif-ref-item-title" title="<?= e($title) ?>"><?= e($title) ?></span>
                                    <?php if ($isUnread): ?>
                                        <span class="notif-ref-unread-dot" title="Unread Alert"></span>
                                    <?php endif; ?>
                                </div>
                                <span class="notif-ref-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                            </div>
                            <div class="notif-ref-item-arrow">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($upcomingList)): ?>
                <!-- Section 2: Amber Header (Matches Reference EXPIRING SOON Section) -->
                <div class="notif-ref-section-title notif-ref-sec-amber">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>UPCOMING &amp; RECENT ALERTS</span>
                </div>
                <div class="notif-ref-section-items">
                    <?php foreach ($upcomingList as $n): ?>
                        <?php
                            $payload = $n['payload'] ?? [];
                            $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                            $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');

                            if (!empty($payload['dispatch_code'])) {
                                $pickup = $payload['pickup_address'] ?? 'Trip';
                                $drop   = $payload['drop_address'] ?? '';
                                $title  = $payload['dispatch_code'] . ($drop ? ' (' . $pickup . ' → ' . $drop . ')' : ' (' . $pickup . ')');
                            } else {
                                $title = $n['title'];
                            }

                            $subParts = [];
                            if (!empty($payload['reporting_time'])) {
                                $subParts[] = 'Duty: ' . $payload['reporting_time'];
                            }
                            if (!empty($payload['vehicle_name'])) {
                                $subParts[] = $payload['vehicle_name'];
                            }
                            $sub = !empty($subParts) ? implode(' • ', $subParts) : time_ago($n['created_at']);
                        ?>
                        <a href="<?= $targetUrl ?>" class="notif-ref-item" id="page-notif-<?= (int)$n['id'] ?>" onclick="markPageNotifRead(<?= (int)$n['id'] ?>)">
                            <div class="notif-ref-item-icon notif-ref-icon-amber">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="notif-ref-item-info">
                                <div class="notif-ref-item-head">
                                    <span class="notif-ref-item-title" title="<?= e($title) ?>"><?= e($title) ?></span>
                                </div>
                                <span class="notif-ref-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                            </div>
                            <div class="notif-ref-item-arrow">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($urgentList) && empty($upcomingList)): ?>
                <!-- Empty State -->
                <div class="notif-ref-empty">
                    <div class="notif-ref-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                    <div class="notif-ref-empty-title">All caught up!</div>
                    <div class="notif-ref-empty-sub">
                        <?= !empty($search) || !empty($status) ? 'No alerts match your search or filter criteria.' : 'No active alerts. New duty dispatches and alerts will appear here.' ?>
                    </div>
                    <?php if (!empty($search) || !empty($status)): ?>
                        <a href="<?= url('/driver/notifications') ?>" class="notif-ref-reset-btn">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Reset Filters
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. Card Footer with Pagination & Action Links -->
        <div class="notif-ref-footer">
            <div class="notif-ref-footer-left">
                <a href="<?= url('/driver/trips') ?>" class="notif-ref-footer-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>My Active Trips &rarr;</span>
                </a>
            </div>
            <?php if ($totalPages > 1): ?>
                <div class="notif-ref-footer-pagination">
                    <div class="notif-page-pills">
                        <?php if ($currP > 1): ?>
                            <a href="<?= $buildFilterUrl(['page' => $currP - 1]) ?>" class="notif-page-pill" title="Previous page">&larr;</a>
                        <?php endif; ?>
                        <span class="notif-page-info"><?= $currP ?> / <?= $totalPages ?></span>
                        <?php if ($currP < $totalPages): ?>
                            <a href="<?= $buildFilterUrl(['page' => $currP + 1]) ?>" class="notif-page-pill" title="Next page">&rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function markPageNotifRead(id) {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('<?= url('/driver/notifications/') ?>' + id + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var item = document.getElementById('page-notif-' + id);
            if (item) {
                item.classList.remove('is-unread');
                var dot = item.querySelector('.notif-ref-unread-dot');
                if (dot) dot.remove();
            }
            if (typeof updateAllNotifBadges === 'function') {
                updateAllNotifBadges(data.unread_count);
            }
            var pageBadge = document.getElementById('driverPageNotifBadge');
            if (pageBadge) {
                if (data.unread_count > 0) {
                    pageBadge.textContent = data.unread_count;
                } else {
                    pageBadge.remove();
                }
            }
        }
    })
    .catch(function(err) { console.error('Mark read failed:', err); });
}

function markAllNotificationsPageRead() {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('<?= url('/driver/notifications/mark-all-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(function(err) { console.error('Mark all read failed:', err); });
}
</script>

<style>
/* ==========================================================================
   CHAUFFEUR NOTIFICATION PAGE (Matching Reference Image Card Architecture)
   Optimized for Mobile Responsive Devices & High-End Touch Screens
   ========================================================================== */

.driver-notif-page-wrapper {
    width: 100%;
    max-width: 620px;
    margin: 0.5rem auto 2.5rem;
    padding: 0 0.5rem;
    box-sizing: border-box;
}

/* Main Card Container (Matches Reference Popover Card UI) */
.notif-ref-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    box-shadow: 0 12px 36px -4px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.03);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

/* 1. Header (Icon + Title + Red Count Badge) */
.notif-ref-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.35rem 1rem;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
    gap: 0.75rem;
}

.notif-ref-head-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 0;
}

.notif-ref-head-icon {
    color: #0F172A;
    flex-shrink: 0;
}

.notif-ref-head-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    letter-spacing: -0.2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notif-ref-head-right {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-shrink: 0;
}

.notif-ref-markall-btn {
    background: rgba(23, 107, 91, 0.08);
    border: 1px solid rgba(23, 107, 91, 0.18);
    color: #176B5B;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}

.notif-ref-markall-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
}

.notif-ref-head-badge {
    background: #EF4444;
    color: #FFFFFF;
    font-size: 0.72rem;
    font-weight: 800;
    min-width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    line-height: 1;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
}

.notif-ref-badge-green {
    background: #10B981;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
    font-size: 0.7rem;
}

/* 2. Search & Filter Toolbar */
.notif-ref-toolbar {
    padding: 0.85rem 1.25rem 0.65rem;
    background: #FAFBFD;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.notif-ref-search-form {
    width: 100%;
}

.notif-ref-search-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.notif-ref-search-icon {
    position: absolute;
    left: 12px;
    width: 15px;
    height: 15px;
    color: #94A3B8;
    pointer-events: none;
}

.notif-ref-search-input {
    width: 100%;
    height: 38px;
    padding: 0 32px 0 36px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    background: #FFFFFF;
    font-size: 0.825rem;
    color: #1E293B;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.notif-ref-search-input:focus {
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.12);
}

.notif-ref-search-clear {
    position: absolute;
    right: 10px;
    color: #94A3B8;
    text-decoration: none;
    font-size: 1.15rem;
    line-height: 1;
}

.notif-ref-pills {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 2px;
}

.notif-ref-pills::-webkit-scrollbar {
    display: none;
}

.notif-ref-pill {
    padding: 4px 10px;
    border-radius: 8px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748B;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}

.notif-ref-pill:hover {
    color: #0F172A;
    border-color: #CBD5E1;
}

.notif-ref-pill.active {
    background: #0F172A;
    border-color: #0F172A;
    color: #FFFFFF;
}

.pill-count {
    background: #F1F5F9;
    color: #475569;
    font-size: 0.65rem;
    padding: 1px 5px;
    border-radius: 999px;
    font-weight: 700;
}

.notif-ref-pill.active .pill-count {
    background: rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
}

.pill-count-red {
    background: #EF4444;
    color: #FFFFFF;
}

/* 3. Items Feed (Exact Reference Styling) */
.notif-ref-feed {
    padding: 0.35rem 0 0.5rem;
    background: #FFFFFF;
}

/* Section Header (Matches Reference EXPIRED and EXPIRING SOON) */
.notif-ref-section-title {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.75px;
    text-transform: uppercase;
    padding: 0.75rem 1.35rem 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.notif-ref-section-title.notif-ref-sec-red {
    color: #EF4444;
}

.notif-ref-section-title.notif-ref-sec-amber {
    color: #D97706;
}

.notif-ref-section-items {
    display: flex;
    flex-direction: column;
}

/* Each Notification Item Row */
.notif-ref-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1.35rem;
    border-bottom: 1px solid #F8FAFC;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: background 0.15s ease;
}

.notif-ref-item:hover {
    background: #F8FAFC;
}

.notif-ref-item.is-unread {
    background: linear-gradient(135deg, rgba(254, 243, 199, 0.18) 0%, rgba(23, 107, 91, 0.04) 100%);
}

.notif-ref-item.is-unread:hover {
    background: linear-gradient(135deg, rgba(254, 243, 199, 0.28) 0%, rgba(23, 107, 91, 0.08) 100%);
}

.notif-ref-item:last-child {
    border-bottom: none;
}

/* Left Circle Icons */
.notif-ref-item-icon {
    width: 34px;
    height: 34px;
    min-width: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.notif-ref-icon-red {
    background: #FEE2E2;
    color: #EF4444;
}

.notif-ref-icon-amber {
    background: #FEF3C7;
    color: #D97706;
}

/* Item Information */
.notif-ref-item-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
    line-height: 1.3;
}

.notif-ref-item-head {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.notif-ref-item-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1E293B;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.15px;
}

.notif-ref-unread-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #EF4444;
    flex-shrink: 0;
}

.notif-ref-item-sub {
    font-size: 0.74rem;
    color: #64748B;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

/* Right Chevron Arrow */
.notif-ref-item-arrow {
    margin-left: auto;
    color: #94A3B8;
    font-size: 0.78rem;
    transition: transform 0.18s ease, color 0.18s ease;
    flex-shrink: 0;
}

.notif-ref-item:hover .notif-ref-item-arrow {
    color: #176B5B;
    transform: translateX(3px);
}

/* Empty State */
.notif-ref-empty {
    padding: 2.75rem 1.25rem;
    text-align: center;
}

.notif-ref-empty-icon {
    font-size: 2.2rem;
    color: #CBD5E1;
    margin-bottom: 0.5rem;
}

.notif-ref-empty-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #334155;
}

.notif-ref-empty-sub {
    font-size: 0.78rem;
    color: #94A3B8;
    margin-top: 4px;
    max-width: 280px;
    margin-left: auto;
    margin-right: auto;
}

.notif-ref-reset-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 1rem;
    padding: 6px 14px;
    background: #F1F5F9;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 8px;
    text-decoration: none;
}

.notif-ref-reset-btn:hover {
    background: #E2E8F0;
    color: #0F172A;
}

/* 4. Footer (Matches Reference View All Domains ->) */
.notif-ref-footer {
    padding: 0.9rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #F1F5F9;
    background: #FAFBFD;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.notif-ref-footer-link {
    font-size: 0.82rem;
    font-weight: 700;
    color: #176B5B;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.15s ease;
}

.notif-ref-footer-link:hover {
    color: #0E483D;
    text-decoration: underline;
}

.notif-ref-footer-pagination {
    display: flex;
    align-items: center;
}

.notif-page-pills {
    display: flex;
    align-items: center;
    gap: 6px;
}

.notif-page-pill {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}

.notif-page-pill:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.notif-page-info {
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748B;
    padding: 0 4px;
}

/* ==========================================================================
   MOBILE RESPONSIVENESS (<= 576px)
   ========================================================================== */
@media (max-width: 576px) {
    .driver-notif-page-wrapper {
        padding: 0;
        margin: 0.25rem 0 2rem;
    }

    .notif-ref-card {
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    }

    .notif-ref-head {
        padding: 0.95rem 1rem 0.85rem;
    }

    .notif-ref-head-title {
        font-size: 0.95rem;
    }

    .notif-ref-markall-btn span {
        display: none;
    }

    .notif-ref-markall-btn {
        padding: 5px 8px;
    }

    .notif-ref-toolbar {
        padding: 0.75rem 1rem 0.6rem;
    }

    .notif-ref-section-title {
        padding: 0.65rem 1rem 0.35rem;
        font-size: 0.68rem;
    }

    .notif-ref-item {
        padding: 0.8rem 1rem;
        gap: 0.75rem;
    }

    .notif-ref-item-icon {
        width: 32px;
        height: 32px;
        min-width: 32px;
        font-size: 0.8rem;
    }

    .notif-ref-item-title {
        font-size: 0.84rem;
    }

    .notif-ref-item-sub {
        font-size: 0.7rem;
    }

    .notif-ref-footer {
        padding: 0.8rem 1rem;
    }
}
</style>
