<?php
// Patch app/Views/layouts/driver.php
$driverLayout = __DIR__ . '/../app/Views/layouts/driver.php';
$content = file_get_contents($driverLayout);
$content = str_replace("\r\n", "\n", $content);

// 1. Target topbar notification bell
$targetBell = <<<'HTML'
                    <!-- Notifications Button with Badge 3 (Matches both Reference Image and Owner UI) -->
                    <a href="<?= url('/driver/dashboard') ?>#notifications" class="topbar-icon-btn" title="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-label="<?= e(__('nav.notifications', 'Notifications')) ?>" style="position: relative; text-decoration: none;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <?php
                            $curUId = \App\Core\Session::getUserId();
                            $curNotifCount = isset($unreadNotifCount) ? (int)$unreadNotifCount : ($curUId ? (new \App\Services\NotificationService())->getUnreadCount($curUId) : 0);
                        ?>
                        <span class="nav-badge-pill" id="driverTopNotifBadge" style="position: absolute; top: -4px; right: -4px; background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 999px; display: <?= $curNotifCount > 0 ? 'inline-flex' : 'none' ?>; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 0 2px #fff;"><?= $curNotifCount ?></span>
                    </a>
HTML;

$newBellDropdown = <<<'HTML'
                    <!-- Notifications Button with Dropdown Box (Exact UI Matching Reference Image) -->
                    <?php
                        $curUId = \App\Core\Session::getUserId();
                        if (!isset($notifications) || !isset($unreadNotifCount)) {
                            if ($curUId) {
                                $notifService = new \App\Services\NotificationService();
                                if (!isset($notifications)) {
                                    $notifications = $notifService->getDriverNotifications($curUId, null, 10);
                                }
                                if (!isset($unreadNotifCount)) {
                                    $unreadNotifCount = $notifService->getUnreadCount($curUId, null);
                                }
                            } else {
                                $notifications = [];
                                $unreadNotifCount = 0;
                            }
                        }
                        $curNotifCount = (int)($unreadNotifCount ?? 0);

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
                    ?>
                    <div class="topbar-notif-wrap" id="topbarNotifWrap">
                        <button type="button" class="topbar-icon-btn notif-bell-btn" id="topbarNotifBtn" onclick="toggleNotifDropdown(event)" title="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-label="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-haspopup="true" aria-expanded="false" style="position: relative; text-decoration: none; cursor: pointer;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="nav-badge-pill" id="driverTopNotifBadge" style="position: absolute; top: -4px; right: -4px; background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 999px; display: <?= $curNotifCount > 0 ? 'inline-flex' : 'none' ?>; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 0 2px #fff;"><?= $curNotifCount ?></span>
                        </button>

                        <!-- Notification Dropdown Popover (Exact Pixel-Perfect Match to Reference Image) -->
                        <div class="topbar-notif-box" id="topbarNotifBox" onclick="event.stopPropagation()">
                            <!-- Header: Alert Icon + Title + Red Badge -->
                            <div class="notif-box-head">
                                <div class="notif-box-head-left">
                                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="notif-box-head-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <h4 class="notif-box-head-title">Domain Alerts</h4>
                                </div>
                                <span class="notif-box-head-badge" id="dropdownHeaderBadge" style="display: <?= $curNotifCount > 0 ? 'inline-flex' : 'none' ?>;"><?= $curNotifCount ?></span>
                            </div>

                            <!-- Scrollable Notification Feed -->
                            <div class="notif-box-body">
                                <?php if (!empty($urgentList)): ?>
                                    <!-- Section 1: Red Header (Matches Reference EXPIRED) -->
                                    <div class="notif-box-section-title notif-sec-red">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        <span>EXPIRED</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($urgentList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $isUnread = empty($n['is_read']);
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Pickup') . ' → ' . ($payload['drop_address'] ?? 'Drop') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Expired: ' . $payload['reporting_time'] : 'Expired: ' . time_ago($n['created_at']);
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
                                        <span>EXPIRING SOON</span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($upcomingList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Trip') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Expires in ' . $payload['reporting_time'] : time_ago($n['created_at']);
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
                                        <div class="notif-box-empty-sub">No active alerts. New domain and trip duty notifications will appear here.</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: View All Link with Arrow (Exact Ref Match) -->
                            <div class="notif-box-footer">
                                <a href="<?= url('/driver/trips') ?>" class="notif-box-footer-link">
                                    View All Domains &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
HTML;

$content = str_replace($targetBell, $newBellDropdown, $content);

// 2. Add JavaScript functions before closing </script>
$targetScriptEnd = "        document.addEventListener('click', function(e) {\n            var lDrop = document.getElementById('mvLangDropdown');\n            var pDrop = document.getElementById('mvProfileDropdown');\n            if (lDrop && !lDrop.contains(e.target)) lDrop.classList.remove('is-open');\n            if (pDrop && !pDrop.contains(e.target)) pDrop.classList.remove('is-open');\n        });\n    </script>";

$newScriptBlock = <<<'JS'
        document.addEventListener('click', function(e) {
            var lDrop = document.getElementById('mvLangDropdown');
            var pDrop = document.getElementById('mvProfileDropdown');
            if (lDrop && !lDrop.contains(e.target)) lDrop.classList.remove('is-open');
            if (pDrop && !pDrop.contains(e.target)) pDrop.classList.remove('is-open');
        });

        // Topbar Notification Popover (Ref Image UI)
        function toggleNotifDropdown(e) {
            if (e) {
                e.stopPropagation();
            }
            var wrap = document.getElementById('topbarNotifWrap');
            if (!wrap) return;
            var wasOpen = wrap.classList.contains('is-open');

            // Close other topbar menus
            var chip = document.getElementById('topbarUserProfile');
            if (chip) {
                chip.classList.remove('is-open');
                chip.setAttribute('aria-expanded', 'false');
            }
            var langDrop = document.getElementById('vgLanguageDropdown');
            if (langDrop) {
                langDrop.classList.remove('is-open');
                var langBtn = langDrop.querySelector('.vg-lang-btn');
                if (langBtn) langBtn.setAttribute('aria-expanded', 'false');
            }

            if (wasOpen) {
                wrap.classList.remove('is-open');
            } else {
                wrap.classList.add('is-open');
            }
        }

        function dismissDropdownNotif(id, e) {
            if (e) {
                e.stopPropagation();
            }
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
                    var item = document.getElementById('notif-box-item-' + id);
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(20px)';
                        setTimeout(function() {
                            item.remove();
                            checkDropdownEmpty();
                        }, 200);
                    }
                    var dbItem = document.getElementById('notif-item-' + id);
                    if (dbItem) {
                        dbItem.classList.remove('is-unread');
                        var newBadge = dbItem.querySelector('.notif-pill-new');
                        if (newBadge) newBadge.remove();
                        var dismissBtn = dbItem.querySelector('.notif-btn-dismiss');
                        if (dismissBtn) dismissBtn.remove();
                    }
                    updateAllNotifBadges(data.unread_count);
                }
            })
            .catch(function(err) { console.error('Dismiss notification failed:', err); });
        }

        function updateAllNotifBadges(count) {
            var topBadge = document.getElementById('driverTopNotifBadge');
            var headerBadge = document.getElementById('dropdownHeaderBadge');
            var bottomBadge = document.getElementById('driverBottomNotifBadge');
            var countPill = document.getElementById('unreadNotifCountPill');

            if (topBadge) {
                topBadge.textContent = count;
                topBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }
            if (headerBadge) {
                headerBadge.textContent = count;
                headerBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }
            if (bottomBadge) {
                bottomBadge.textContent = count;
                bottomBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }
            if (countPill) {
                if (count > 0) {
                    countPill.textContent = count + ' unread';
                    countPill.style.display = 'inline-block';
                } else {
                    countPill.remove();
                }
            }
        }

        function checkDropdownEmpty() {
            var remaining = document.querySelectorAll('.notif-box-item');
            if (!remaining || remaining.length === 0) {
                var body = document.querySelector('.notif-box-body');
                if (body) {
                    body.innerHTML = '<div class="notif-box-empty"><div class="notif-box-empty-icon"><i class="fa-regular fa-bell-slash"></i></div><div class="notif-box-empty-title">All caught up!</div><div class="notif-box-empty-sub">No active alerts. New duty dispatches will appear here.</div></div>';
                }
            }
        }

        document.addEventListener('click', function(e) {
            var wrap = document.getElementById('topbarNotifWrap');
            if (wrap && !wrap.contains(e.target)) {
                wrap.classList.remove('is-open');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var wrap = document.getElementById('topbarNotifWrap');
                if (wrap) wrap.classList.remove('is-open');
            }
        });
    </script>
JS;

$content = str_replace($targetScriptEnd, $newScriptBlock, $content);

// 3. Add CSS block right before </head>
$cssBlock = <<<'CSS'
    <style>
    /* ==========================================================================
       NOTIFICATION DROPDOWN UI (Pixel-Perfect Match to Reference Image)
       ========================================================================== */
    .topbar-notif-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .notif-bell-btn {
        position: relative;
        cursor: pointer;
        background: transparent;
        border: none;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .topbar-notif-box {
        position: absolute;
        top: calc(100% + 12px);
        right: -10px;
        width: 360px;
        max-width: calc(100vw - 24px);
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.04);
        display: none;
        flex-direction: column;
        z-index: 10005;
        animation: vgFadeDown 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
        cursor: default;
        overflow: hidden;
    }

    .topbar-notif-wrap.is-open .topbar-notif-box {
        display: flex;
    }

    /* Dropdown Header */
    .notif-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.95rem 1.15rem 0.85rem;
        border-bottom: 1px solid #F1F5F9;
        background: #FFFFFF;
    }

    .notif-box-head-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notif-box-head-icon {
        color: #0F172A;
    }

    .notif-box-head-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .notif-box-head-badge {
        background: #EF4444;
        color: #FFFFFF;
        font-size: 0.68rem;
        font-weight: 800;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        line-height: 1;
    }

    /* Dropdown Body */
    .notif-box-body {
        max-height: 380px;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0.35rem 0 0.5rem;
    }

    /* Section Headers (Matches Reference EXPIRED and EXPIRING SOON) */
    .notif-box-section-title {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.75px;
        text-transform: uppercase;
        padding: 0.65rem 1.15rem 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .notif-box-section-title.notif-sec-red {
        color: #EF4444;
    }

    .notif-box-section-title.notif-sec-amber {
        color: #D97706;
    }

    /* Notification Items */
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

    .notif-box-item:last-child {
        border-bottom: none;
    }

    /* Round Left Icons (Matches Ref Globe Icons in Red and Orange) */
    .notif-box-item-icon {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .notif-box-icon-red {
        background: #FEE2E2;
        color: #EF4444;
    }

    .notif-box-icon-amber {
        background: #FEF3C7;
        color: #D97706;
    }

    /* Info Meta */
    .notif-box-item-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
        line-height: 1.25;
    }

    .notif-box-item-name {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1E293B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 195px;
    }

    .notif-box-item-sub {
        font-size: 0.7rem;
        color: #64748B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 195px;
        margin-top: 2px;
    }

    /* Right Action Buttons (Matches Reference Green Refresh & Red Cross Buttons) */
    .notif-box-item-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: auto;
        flex-shrink: 0;
    }

    .notif-box-btn {
        width: 28px;
        height: 28px;
        min-width: 28px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .notif-box-btn:active {
        transform: scale(0.92);
    }

    .notif-btn-green {
        background: #22C55E;
        color: #FFFFFF;
    }

    .notif-btn-green:hover {
        background: #16A34A;
        color: #FFFFFF;
    }

    .notif-btn-red {
        background: #EF4444;
        color: #FFFFFF;
    }

    .notif-btn-red:hover {
        background: #DC2626;
        color: #FFFFFF;
    }

    /* Empty State */
    .notif-box-empty {
        padding: 2.25rem 1rem;
        text-align: center;
    }

    .notif-box-empty-icon {
        font-size: 2rem;
        color: #CBD5E1;
        margin-bottom: 0.4rem;
    }

    .notif-box-empty-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #334155;
    }

    .notif-box-empty-sub {
        font-size: 0.72rem;
        color: #94A3B8;
        margin-top: 2px;
    }

    /* Dropdown Footer (Matches Reference View All Domains ->) */
    .notif-box-footer {
        padding: 0.8rem 1.15rem;
        text-align: center;
        border-top: 1px solid #F1F5F9;
        background: #FAFAFA;
    }

    .notif-box-footer-link {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748B;
        text-decoration: none;
        transition: color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .notif-box-footer-link:hover {
        color: #0F172A;
        text-decoration: underline;
    }
    </style>
</head>
CSS;

$content = str_replace("</head>", $cssBlock, $content);

file_put_contents($driverLayout, $content);
echo "driver layout patched successfully!\n";
