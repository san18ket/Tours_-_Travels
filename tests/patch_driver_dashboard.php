<?php
$file = __DIR__ . '/../app/Views/driver/dashboard.php';
$content = file_get_contents($file);

// Normalize to LF
$content = str_replace("\r\n", "\n", $content);

// 1. Target mock notifications card
$mockCard = <<<'HTML'
            <!-- 1. Recent Notifications Card -->
            <div class="driver-card driver-notif-card">
                <div class="notif-card-header">
                    <h3 class="notif-card-title">Recent Notifications</h3>
                    <a href="<?= url('/driver/trips') ?>" class="notif-view-all">View All &rarr;</a>
                </div>

                <div class="notif-list">
                    <!-- Item 1: Trip Started -->
                    <div class="notif-item">
                        <div class="notif-icon-circle notif-green">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="notif-content">
                            <strong class="notif-title">Trip started successfully</strong>
                            <span class="notif-sub">Mumbai &rarr; Pune</span>
                        </div>
                        <span class="notif-time">08:02 AM</span>
                    </div>

                    <!-- Item 2: Passenger Count -->
                    <div class="notif-item">
                        <div class="notif-icon-circle notif-blue">
                            <i class="fa-solid fa-info"></i>
                        </div>
                        <div class="notif-content">
                            <strong class="notif-title">Passenger count updated</strong>
                            <span class="notif-sub">12 passengers on board</span>
                        </div>
                        <span class="notif-time">09:10 AM</span>
                    </div>

                    <!-- Item 3: Next Stop -->
                    <div class="notif-item">
                        <div class="notif-icon-circle notif-amber">
                            <i class="fa-solid fa-exclamation"></i>
                        </div>
                        <div class="notif-content">
                            <strong class="notif-title">Next stop approaching</strong>
                            <span class="notif-sub">Lonavala</span>
                        </div>
                        <span class="notif-time">10:15 AM</span>
                    </div>

                    <!-- Item 4: Message from Manager -->
                    <div class="notif-item">
                        <div class="notif-icon-circle notif-blue">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                        <div class="notif-content">
                            <strong class="notif-title">New message from manager</strong>
                            <span class="notif-sub">&ldquo;Keep up the good work!&rdquo;</span>
                        </div>
                        <span class="notif-time">10:32 AM</span>
                    </div>
                </div>
            </div>
HTML;

$realCard = <<<'HTML'
            <!-- 1. Recent Notifications Card -->
            <div class="driver-card driver-notif-card" id="notifications">
                <div class="notif-card-header">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <h3 class="notif-card-title">Recent Notifications</h3>
                        <?php if (!empty($unreadNotifCount) && $unreadNotifCount > 0): ?>
                            <span class="notif-unread-count-pill" id="unreadNotifCountPill"><?= (int)$unreadNotifCount ?> unread</span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.6rem;">
                        <?php if (!empty($unreadNotifCount) && $unreadNotifCount > 0): ?>
                            <button type="button" class="btn-mark-all-read" onclick="markAllNotificationsRead()" title="Mark all as read">
                                <i class="fa-solid fa-check-double"></i> Mark all read
                            </button>
                        <?php endif; ?>
                        <a href="<?= url('/driver/trips') ?>" class="notif-view-all">All Trips &rarr;</a>
                    </div>
                </div>

                <div class="notif-list" id="driverNotifList">
                    <?php if (empty($notifications)): ?>
                        <div class="notif-empty-state">
                            <div class="notif-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                            <div class="notif-empty-title">All caught up!</div>
                            <div class="notif-empty-sub">No recent notifications. New trip duty dispatches will appear here.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <?php
                                $payload = $n['payload'] ?? [];
                                $isUnread = empty($n['is_read']);
                                $isTrip = ($n['type'] ?? '') === 'trip_assigned';
                                $timeAgo = time_ago($n['created_at']);
                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                            ?>
                            <div class="notif-item <?= $isUnread ? 'is-unread' : '' ?>" id="notif-item-<?= (int)$n['id'] ?>">
                                <div class="notif-icon-circle <?= $isTrip ? 'notif-gold' : 'notif-blue' ?>">
                                    <i class="fa-solid <?= $isTrip ? 'fa-route' : 'fa-bell' ?>"></i>
                                </div>
                                <div class="notif-content">
                                    <div class="notif-title-row">
                                        <strong class="notif-title"><?= e($n['title']) ?></strong>
                                        <?php if ($isUnread): ?>
                                            <span class="notif-pill-new">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-sub-details">
                                        <?php if (!empty($payload['pickup_address']) && !empty($payload['drop_address'])): ?>
                                            <div class="notif-route-line">
                                                <i class="fa-solid fa-location-dot" style="color:#D4AF37; margin-right:4px;"></i>
                                                <span><?= e($payload['pickup_address']) ?> &rarr; <?= e($payload['drop_address']) ?></span>
                                            </div>
                                            <div class="notif-meta-line">
                                                <span><i class="fa-solid fa-calendar-day" style="margin-right:4px;"></i> <?= e($payload['reporting_time'] ?? '') ?></span>
                                                <?php if (!empty($payload['vehicle_number'])): ?>
                                                    <span class="notif-meta-dot">•</span>
                                                    <span><i class="fa-solid fa-car" style="margin-right:4px;"></i> <?= e($payload['vehicle_name']) ?> (<?= e($payload['vehicle_number']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <p style="margin:0; font-size:0.75rem; color:#475569;"><?= nl2br(e($n['message'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-item-actions">
                                        <?php if ($dispatchId): ?>
                                            <a href="<?= url('/driver/trips/' . $dispatchId) ?>" class="notif-btn-action">
                                                <i class="fa-solid fa-circle-arrow-right"></i> Review Trip Duty
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($isUnread): ?>
                                            <button type="button" class="notif-btn-dismiss" onclick="markNotificationRead(<?= (int)$n['id'] ?>)">
                                                <i class="fa-solid fa-check"></i> Mark read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="notif-time"><?= e($timeAgo) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
HTML;

if (str_contains($content, '<!-- 1. Recent Notifications Card -->')) {
    $content = str_replace($mockCard, $realCard, $content);
}

// 2. Add JavaScript functions for notifications
$jsTarget = "}, 1000);\n</script>";
$jsNew = <<<'JS'
}, 1000);

// Notification Mark Read Helpers
function markNotificationRead(id) {
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
            var item = document.getElementById('notif-item-' + id);
            if (item) {
                item.classList.remove('is-unread');
                var newBadge = item.querySelector('.notif-pill-new');
                if (newBadge) newBadge.remove();
                var dismissBtn = item.querySelector('.notif-btn-dismiss');
                if (dismissBtn) dismissBtn.remove();
            }
            updateNotifBadges(data.unread_count);
        }
    })
    .catch(function(err) { console.error('Mark read failed:', err); });
}

function markAllNotificationsRead() {
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
            document.querySelectorAll('.notif-item.is-unread').forEach(function(item) {
                item.classList.remove('is-unread');
                var newBadge = item.querySelector('.notif-pill-new');
                if (newBadge) newBadge.remove();
                var dismissBtn = item.querySelector('.notif-btn-dismiss');
                if (dismissBtn) dismissBtn.remove();
            });
            var markAllBtn = document.querySelector('.btn-mark-all-read');
            if (markAllBtn) markAllBtn.remove();
            updateNotifBadges(0);
        }
    })
    .catch(function(err) { console.error('Mark all read failed:', err); });
}

function updateNotifBadges(count) {
    var topBadge = document.getElementById('driverTopNotifBadge');
    var bottomBadge = document.getElementById('driverBottomNotifBadge');
    var countPill = document.getElementById('unreadNotifCountPill');

    if (topBadge) {
        topBadge.textContent = count;
        topBadge.style.display = count > 0 ? 'inline-flex' : 'none';
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
</script>
JS;

if (!str_contains($content, 'function markNotificationRead')) {
    $content = str_replace($jsTarget, $jsNew, $content);
}

// 3. Add CSS for notification components
$cssTarget = "/* Recent Notifications Card */";
$cssNew = <<<'CSS'
/* Recent Notifications Card */
.notif-unread-count-pill {
    background: #FEE2E2;
    color: #DC2626;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
}
.btn-mark-all-read {
    font-size: 0.72rem;
    font-weight: 600;
    color: #176B5B;
    background: rgba(23, 107, 91, 0.08);
    border: 1px solid rgba(23, 107, 91, 0.2);
    border-radius: 6px;
    padding: 3px 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-mark-all-read:hover {
    background: #176B5B;
    color: #FFF;
}
.notif-gold {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    color: #B45309;
}
.notif-item.is-unread {
    background: linear-gradient(135deg, rgba(23, 107, 91, 0.05), rgba(212, 175, 55, 0.05));
    border: 1px solid rgba(23, 107, 91, 0.15);
    border-radius: 12px;
    padding: 0.65rem 0.75rem;
}
.notif-title-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}
.notif-pill-new {
    background: #EF4444;
    color: #FFF;
    font-size: 0.58rem;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 999px;
    letter-spacing: 0.5px;
}
.notif-sub-details {
    margin-top: 3px;
}
.notif-route-line {
    font-size: 0.75rem;
    font-weight: 600;
    color: #1E293B;
}
.notif-meta-line {
    font-size: 0.7rem;
    color: #64748B;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.notif-meta-dot {
    color: #CBD5E1;
}
.notif-item-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-top: 6px;
}
.notif-btn-action {
    font-size: 0.72rem;
    font-weight: 700;
    color: #176B5B;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(23, 107, 91, 0.1);
    padding: 3px 9px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.notif-btn-action:hover {
    background: #176B5B;
    color: #FFF;
}
.notif-btn-dismiss {
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748B;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 3px 6px;
    border-radius: 4px;
}
.notif-btn-dismiss:hover {
    color: #0F172A;
    text-decoration: underline;
}
.notif-empty-state {
    text-align: center;
    padding: 1.75rem 0.75rem;
}
.notif-empty-icon {
    font-size: 2rem;
    color: #CBD5E1;
    margin-bottom: 0.4rem;
}
.notif-empty-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #334155;
}
.notif-empty-sub {
    font-size: 0.72rem;
    color: #94A3B8;
    margin-top: 2px;
}
CSS;

if (!str_contains($content, 'notif-unread-count-pill')) {
    $content = str_replace($cssTarget, $cssNew, $content);
}

file_put_contents($file, $content);
echo "Driver dashboard patched successfully!\n";
