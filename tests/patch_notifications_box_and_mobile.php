<?php
$layoutFile = __DIR__ . '/../app/Views/layouts/driver.php';
$content = file_get_contents($layoutFile);
$content = str_replace("\r\n", "\n", $content);

// 1. Update URI detection variables
$oldUriBlock = <<<'HTML'
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    $isDashboardActive = str_contains($uri, '/driver/dashboard') || $uri === '/driver' || $uri === '/driver/';
                    $isTripsActive     = str_contains($uri, '/driver/trips');
                    $isHistoryActive   = str_contains($uri, '/driver/history');
                    $isVehicleActive   = str_contains($uri, '/driver/vehicle');
                    $isLocationActive  = str_contains($uri, '/driver/location');
                    $isProfileActive   = str_contains($uri, '/driver/profile');
HTML;

$newUriBlock = <<<'HTML'
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    $isDashboardActive = str_contains($uri, '/driver/dashboard') || $uri === '/driver' || $uri === '/driver/';
                    $isTripsActive     = str_contains($uri, '/driver/trips');
                    $isNotifActive     = str_contains($uri, '/driver/notifications');
                    $isHistoryActive   = str_contains($uri, '/driver/history');
                    $isVehicleActive   = str_contains($uri, '/driver/vehicle');
                    $isLocationActive  = str_contains($uri, '/driver/location');
                    $isProfileActive   = str_contains($uri, '/driver/profile');
HTML;

$content = str_replace($oldUriBlock, $newUriBlock, $content);

// 2. Add Notifications Category to Sidebar Nav if not present
$oldSidebarTrips = <<<'HTML'
                <!-- 2. My Assigned Trips -->
                <div class="sidebar-category" id="cat-driver-trips">
                    <a href="<?= url('/driver/trips') ?>" class="category-header direct-link <?= $isTripsActive ? 'has-active' : '' ?>" id="cat-driver-trips-header" data-title="Assigned Trips" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.assigned_trips')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTripsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.assigned_trips')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>
HTML;

$newSidebarTripsWithNotif = <<<'HTML'
                <!-- 2. My Assigned Trips -->
                <div class="sidebar-category" id="cat-driver-trips">
                    <a href="<?= url('/driver/trips') ?>" class="category-header direct-link <?= $isTripsActive ? 'has-active' : '' ?>" id="cat-driver-trips-header" data-title="Assigned Trips" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.assigned_trips')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTripsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.assigned_trips')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 3. Notifications & Alerts -->
                <div class="sidebar-category" id="cat-driver-notifications">
                    <a href="<?= url('/driver/notifications') ?>" class="category-header direct-link <?= $isNotifActive ? 'has-active' : '' ?>" id="cat-driver-notifications-header" data-title="Notifications" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('nav.notifications', 'Notifications')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if (($curNotifCount ?? 0) > 0): ?>
                                <span class="badge-dot" style="background:#EF4444;" title="Unread alerts"></span>
                            <?php endif; ?>
                            <?php if ($isNotifActive): ?>
                                <span class="active-dot" title="<?= e(__('nav.notifications', 'Notifications')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>
HTML;

if (!str_contains($content, 'id="cat-driver-notifications"')) {
    $content = str_replace($oldSidebarTrips, $newSidebarTripsWithNotif, $content);
}

// 3. Update Dropdown Box to remove both icon buttons and make the item directly clickable
$oldDropdownBody = <<<'HTML'
                            <!-- Scrollable Notification Feed -->
                            <div class="notif-box-body">
                                <?php if (!empty($urgentList)): ?>
                                    <!-- Section 1: Red Header (Matches Reference EXPIRED) -->
                                    <div class="notif-box-section-title notif-sec-red">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        <span>NEW DUTY ASSIGNMENTS</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($urgentList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $isUnread = empty($n['is_read']);
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Pickup') . ' → ' . ($payload['drop_address'] ?? 'Drop') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                            ?>
                                            <div class="notif-box-item <?= $isUnread ? 'is-unread' : '' ?>" id="notif-box-item-<?= (int)$n['id'] ?>">
                                                <div class="notif-box-item-icon notif-box-icon-red">
                                                    <i class="fa-solid fa-globe"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-actions">
                                                    <?php if ($dispatchId): ?>
                                                        <a href="<?= url('/driver/trips/' . $dispatchId) ?>" class="notif-box-btn notif-btn-green" title="View Duty Itinerary">
                                                            <i class="fa-solid fa-arrow-rotate-right"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= url('/driver/dashboard#notifications') ?>" class="notif-box-btn notif-btn-green" title="View Alert">
                                                            <i class="fa-solid fa-arrow-rotate-right"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="notif-box-btn notif-btn-red" onclick="dismissDropdownNotif(<?= (int)$n['id'] ?>, event)" title="Dismiss Alert">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($upcomingList)): ?>
                                    <!-- Section 2: Amber Header (Matches Reference EXPIRING SOON) -->
                                    <div class="notif-box-section-title notif-sec-amber">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>UPCOMING &amp; RECENT ALERTS</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($upcomingList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Trip') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                            ?>
                                            <div class="notif-box-item" id="notif-box-item-<?= (int)$n['id'] ?>">
                                                <div class="notif-box-item-icon notif-box-icon-amber">
                                                    <i class="fa-solid fa-globe"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-actions">
                                                    <?php if ($dispatchId): ?>
                                                        <a href="<?= url('/driver/trips/' . $dispatchId) ?>" class="notif-box-btn notif-btn-green" title="View Trip Details">
                                                            <i class="fa-solid fa-arrow-rotate-right"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="notif-box-btn notif-btn-red" onclick="dismissDropdownNotif(<?= (int)$n['id'] ?>, event)" title="Dismiss Alert">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($urgentList) && empty($upcomingList)): ?>
                                    <div class="notif-box-empty">
                                        <div class="notif-box-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                                        <div class="notif-box-empty-title">All caught up!</div>
                                        <div class="notif-box-empty-sub">No active alerts. New duty dispatches will appear here.</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: View All Link with Arrow (Exact Ref Match) -->
                            <div class="notif-box-footer">
                                <a href="<?= url('/driver/trips') ?>" class="notif-box-footer-link">
                                    View All Trips &rarr;
                                </a>
                            </div>
HTML;

$newDropdownBody = <<<'HTML'
                            <!-- Scrollable Notification Feed -->
                            <div class="notif-box-body">
                                <?php if (!empty($urgentList)): ?>
                                    <!-- Section 1: Red Header -->
                                    <div class="notif-box-section-title notif-sec-red">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        <span>NEW DUTY ASSIGNMENTS</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($urgentList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $isUnread = empty($n['is_read']);
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Pickup') . ' → ' . ($payload['drop_address'] ?? 'Drop') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                                $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');
                                            ?>
                                            <a href="<?= $targetUrl ?>" class="notif-box-item <?= $isUnread ? 'is-unread' : '' ?>" id="notif-box-item-<?= (int)$n['id'] ?>" onclick="markNotifReadAsync(<?= (int)$n['id'] ?>)">
                                                <div class="notif-box-item-icon notif-box-icon-red">
                                                    <i class="fa-solid fa-route"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <div class="notif-box-item-head">
                                                        <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                        <?php if ($isUnread): ?>
                                                            <span class="notif-item-unread-dot" title="Unread Alert"></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-arrow">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($upcomingList)): ?>
                                    <!-- Section 2: Amber Header -->
                                    <div class="notif-box-section-title notif-sec-amber">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>UPCOMING &amp; RECENT ALERTS</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($upcomingList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Trip') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                                $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');
                                            ?>
                                            <a href="<?= $targetUrl ?>" class="notif-box-item" id="notif-box-item-<?= (int)$n['id'] ?>" onclick="markNotifReadAsync(<?= (int)$n['id'] ?>)">
                                                <div class="notif-box-item-icon notif-box-icon-amber">
                                                    <i class="fa-solid fa-clock"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-arrow">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($urgentList) && empty($upcomingList)): ?>
                                    <div class="notif-box-empty">
                                        <div class="notif-box-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                                        <div class="notif-box-empty-title">All caught up!</div>
                                        <div class="notif-box-empty-sub">No active alerts. New duty dispatches will appear here.</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: View All Link -->
                            <div class="notif-box-footer">
                                <a href="<?= url('/driver/notifications') ?>" class="notif-box-footer-link">
                                    View All Notifications &rarr;
                                </a>
                            </div>
HTML;

$content = str_replace($oldDropdownBody, $newDropdownBody, $content);

// 4. Update Mobile Bottom Navbar link
$oldBottomNav = <<<'HTML'
            <a href="<?= url('/driver/dashboard') ?>#notifications" class="bottom-nav-item nav-notif-item">
                <div style="position: relative; display: inline-flex;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="nav-badge-pill" id="driverBottomNotifBadge" style="display: <?= $curNotifCount > 0 ? 'inline-flex' : 'none' ?>;"><?= $curNotifCount ?></span>
                </div>
                <span><?= e(__('nav.notifications', 'Notifications')) ?></span>
            </a>
HTML;

$newBottomNav = <<<'HTML'
            <a href="<?= url('/driver/notifications') ?>" class="bottom-nav-item nav-notif-item <?= $isNotifActive ? 'active' : '' ?>">
                <div style="position: relative; display: inline-flex;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="nav-badge-pill" id="driverBottomNotifBadge" style="display: <?= $curNotifCount > 0 ? 'inline-flex' : 'none' ?>;"><?= $curNotifCount ?></span>
                </div>
                <span><?= e(__('nav.notifications', 'Notifications')) ?></span>
            </a>
HTML;

$content = str_replace($oldBottomNav, $newBottomNav, $content);

// 5. Add markNotifReadAsync helper to JavaScript
$jsTarget = "function toggleNotifDropdown(e) {";
$jsNew = <<<'JS'
function markNotifReadAsync(id) {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('<?= url('/driver/notifications/') ?>' + id + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(function() {});
}

function toggleNotifDropdown(e) {
JS;

if (!str_contains($content, 'function markNotifReadAsync')) {
    $content = str_replace($jsTarget, $jsNew, $content);
}

// 6. Update CSS in head for .notif-box-item clickable styles
$oldItemCss = <<<'CSS'
    .notif-box-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 1.15rem;
        border-bottom: 1px solid #F8FAFC;
        transition: background 0.15s ease, opacity 0.2s ease, transform 0.2s ease;
    }

    .notif-box-item:hover {
        background: #F8FAFC;
    }
CSS;

$newItemCss = <<<'CSS'
    .notif-box-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.15rem;
        border-bottom: 1px solid #F8FAFC;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .notif-box-item:hover {
        background: #F1F5F9;
    }

    .notif-box-item.is-unread {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.15) 0%, rgba(23, 107, 91, 0.04) 100%);
    }

    .notif-box-item.is-unread:hover {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.25) 0%, rgba(23, 107, 91, 0.08) 100%);
    }

    .notif-box-item-head {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .notif-item-unread-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #EF4444;
        flex-shrink: 0;
    }

    .notif-box-item-arrow {
        margin-left: auto;
        color: #94A3B8;
        font-size: 0.75rem;
        transition: transform 0.18s ease, color 0.18s ease;
        flex-shrink: 0;
    }

    .notif-box-item:hover .notif-box-item-arrow {
        color: #176B5B;
        transform: translateX(3px);
    }
CSS;

$content = str_replace($oldItemCss, $newItemCss, $content);

file_put_contents($layoutFile, $content);
echo "Layout driver patched successfully!\n";
