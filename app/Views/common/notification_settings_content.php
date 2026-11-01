<?php
/**
 * Shared Notification & PWA Settings View Content
 */
$auth = auth_user() ?: ($user ?? []);
$userName = $auth['name'] ?? $auth['username'] ?? 'User';
?>

<div class="page-container" style="max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-bell" style="color: #176B5B;"></i>
                <span>Tours Notification Settings</span>
            </h2>
            <p style="margin: 0.25rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Manage Web Push alerts, device registrations, and PWA installation for real-time trip operations.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="button" class="btn btn-primary tours-install-btn" onclick="triggerToursInstall(event)" style="border-radius: 10px; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span class="install-btn-label">Install Tours</span>
            </button>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="notif-settings-grid">
        <!-- 1. Current Device Status & Push Toggle -->
        <div class="notif-card">
            <div class="notif-card-head">
                <div class="notif-card-title-wrap">
                    <div class="device-icon-pill">
                        <i class="fa-solid fa-tower-broadcast"></i>
                    </div>
                    <h4>This Device</h4>
                </div>
                <div id="devicePermBadge" class="notif-status-badge status-default">
                    <i class="fa-solid fa-circle-dot"></i>
                    <span id="devicePermText">Checking...</span>
                </div>
            </div>

            <p style="color: #CBD5E1; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.25rem;">
                Receive instant notifications on this device when new trips are assigned, itineraries change, or dispatch updates occur.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
                <button type="button" id="btnTogglePush" class="btn btn-primary" onclick="handlePushToggle(this)" style="border-radius: 10px; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-regular fa-bell"></i>
                    <span>Enable Notifications</span>
                </button>
                <button type="button" id="btnTestPush" class="btn btn-outline" onclick="sendTestPushNotification(this)" style="color: #FFF; border-color: rgba(255,255,255,0.25); border-radius: 10px; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Test Notification</span>
                </button>
            </div>

            <div id="permNoticeBox" style="display:none; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem; line-height: 1.45;"></div>
        </div>

        <!-- 2. PWA Application Info -->
        <div class="notif-card">
            <div class="notif-card-head">
                <div class="notif-card-title-wrap">
                    <div class="device-icon-pill" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <h4>Tours Application</h4>
                </div>
                <div id="pwaStatusBadge" class="notif-status-badge status-default">
                    <i class="fa-solid fa-circle-dot"></i>
                    <span id="pwaStatusText">Browser Mode</span>
                </div>
            </div>

            <p style="color: #CBD5E1; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.25rem;">
                Install <strong>Tours</strong> to launch it from your desktop or phone home screen without browser toolbars, with background push notification support.
            </p>

            <div style="background: rgba(0,0,0,0.2); padding: 0.85rem 1rem; border-radius: 10px; font-size: 0.85rem; color: #94A3B8; margin-bottom: 1.25rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                    <span>App Name:</span>
                    <strong style="color:#FFF;">Tours</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                    <span>PWA Version:</span>
                    <strong style="color:#FFF;">5.0 (Service Worker Ready)</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Offline Sync:</span>
                    <strong style="color:#34D399;">Active (Network-First)</strong>
                </div>
            </div>

            <button type="button" class="btn btn-outline tours-install-btn" onclick="triggerToursInstall(event)" style="width: 100%; color: #FFF; border-color: rgba(255,255,255,0.25); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span class="install-btn-label">Install Tours App</span>
            </button>
        </div>

        <!-- 3. Registered Devices Table (Full Width) -->
        <div class="notif-card notif-settings-full">
            <div class="notif-card-head">
                <div class="notif-card-title-wrap">
                    <div class="device-icon-pill" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA;">
                        <i class="fa-solid fa-devices"></i>
                    </div>
                    <h4>Registered Notification Devices (<?= count($devices) ?>)</h4>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="refreshDeviceList()" style="color:#FFF; border-color:rgba(255,255,255,0.2); border-radius:8px;">
                    <i class="fa-solid fa-arrows-rotate"></i> Refresh
                </button>
            </div>

            <div class="device-table-wrap">
                <table class="device-table" id="deviceListTable">
                    <thead>
                        <tr>
                            <th>Device / Platform</th>
                            <th>Subscribed On</th>
                            <th>Last Used</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="deviceTableBody">
                        <?php if (empty($devices)): ?>
                            <tr id="emptyDeviceRow">
                                <td colspan="4" style="text-align: center; padding: 2rem; color: #94A3B8;">
                                    <i class="fa-regular fa-bell-slash" style="font-size: 1.75rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                    No active notification devices registered yet. Click <strong>"Enable Notifications"</strong> above to register this device.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($devices as $dev): ?>
                                <tr id="deviceRow_<?= (int)$dev['id'] ?>">
                                    <td>
                                        <div class="device-name-cell">
                                            <i class="fa-solid fa-laptop" style="color: #5EEAD4;"></i>
                                            <span><?= e($dev['device_name'] ?: 'Browser Device') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('d M Y, h:i A', strtotime($dev['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?= !empty($dev['last_used_at']) ? date('d M Y, h:i A', strtotime($dev['last_used_at'])) : '<span style="color:#94A3B8;">Never</span>' ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="removePushDevice(<?= (int)$dev['id'] ?>, document.getElementById('deviceRow_<?= (int)$dev['id'] ?>'))" style="color: #EF4444; border-color: rgba(239, 68, 68, 0.35); border-radius: 6px; padding: 0.25rem 0.65rem;">
                                            <i class="fa-solid fa-trash-can"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    evaluateDeviceStatus();
});

async function evaluateDeviceStatus() {
    const badge = document.getElementById('devicePermBadge');
    const text = document.getElementById('devicePermText');
    const btnToggle = document.getElementById('btnTogglePush');
    const notice = document.getElementById('permNoticeBox');

    // Standalone check
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const pwaBadge = document.getElementById('pwaStatusBadge');
    const pwaText = document.getElementById('pwaStatusText');
    if (isStandalone) {
        pwaBadge.className = 'notif-status-badge status-granted';
        pwaText.textContent = 'Installed (Standalone)';
    }

    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
        badge.className = 'notif-status-badge status-denied';
        text.textContent = 'Unsupported';
        btnToggle.disabled = true;
        btnToggle.innerHTML = '<i class="fa-solid fa-ban"></i> Push Unsupported';
        return;
    }

    const perm = Notification.permission;
    if (perm === 'granted') {
        // Check if actually subscribed in service worker
        let isSubscribed = false;
        try {
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            isSubscribed = !!sub;
        } catch (e) {}

        badge.className = 'notif-status-badge status-granted';
        text.textContent = isSubscribed ? 'Notifications Active' : 'Permission Granted';

        if (isSubscribed) {
            btnToggle.className = 'btn btn-outline';
            btnToggle.style.color = '#EF4444';
            btnToggle.style.borderColor = 'rgba(239,68,68,0.35)';
            btnToggle.innerHTML = '<i class="fa-solid fa-bell-slash"></i> Disable on this Device';
            btnToggle.setAttribute('data-action', 'disable');
        } else {
            btnToggle.className = 'btn btn-primary';
            btnToggle.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Synchronize Push Token';
            btnToggle.setAttribute('data-action', 'enable');
        }
    } else if (perm === 'denied') {
        badge.className = 'notif-status-badge status-denied';
        text.textContent = 'Blocked by Browser';
        btnToggle.className = 'btn btn-outline';
        btnToggle.style.color = '#EF4444';
        btnToggle.innerHTML = '<i class="fa-solid fa-sliders"></i> Open Instructions';
        btnToggle.setAttribute('data-action', 'instructions');

        notice.style.display = 'block';
        notice.style.background = 'rgba(239,68,68,0.15)';
        notice.style.border = '1px solid rgba(239,68,68,0.3)';
        notice.style.color = '#FCA5A5';
        notice.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Notifications are blocked in your browser site settings. Click the lock icon in the address bar to allow notifications, then reload.';
    } else {
        badge.className = 'notif-status-badge status-default';
        text.textContent = 'Not Enabled';
        btnToggle.className = 'btn btn-primary';
        btnToggle.innerHTML = '<i class="fa-regular fa-bell"></i> Enable Notifications';
        btnToggle.setAttribute('data-action', 'enable');
    }
}

function handlePushToggle(btn) {
    const action = btn.getAttribute('data-action') || 'enable';
    if (action === 'disable') {
        window.disableToursPushNotifications(btn).then(() => evaluateDeviceStatus());
    } else if (action === 'instructions') {
        if (window.showPermissionDeniedModal) {
            window.showPermissionDeniedModal();
        } else {
            alert('Notifications are blocked. Please allow notifications in your browser address bar site settings.');
        }
    } else {
        window.enableToursPushNotifications(btn).then(() => evaluateDeviceStatus());
    }
}

async function refreshDeviceList() {
    try {
        const res = await fetch('<?= url('/api/push/devices') ?>', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success && data.devices) {
            const tbody = document.getElementById('deviceTableBody');
            if (data.devices.length === 0) {
                tbody.innerHTML = `
                    <tr id="emptyDeviceRow">
                        <td colspan="4" style="text-align: center; padding: 2rem; color: #94A3B8;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 1.75rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                            No active notification devices registered yet. Click <strong>"Enable Notifications"</strong> above to register this device.
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = data.devices.map(dev => `
                    <tr id="deviceRow_${dev.id}">
                        <td>
                            <div class="device-name-cell">
                                <i class="fa-solid fa-laptop" style="color: #5EEAD4;"></i>
                                <span>${escapeHtml(dev.device_name || 'Browser Device')}</span>
                            </div>
                        </td>
                        <td>${dev.created_at}</td>
                        <td>${dev.last_used_at || '<span style="color:#94A3B8;">Never</span>'}</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="removePushDevice(${dev.id}, document.getElementById('deviceRow_${dev.id}'))" style="color: #EF4444; border-color: rgba(239, 68, 68, 0.35); border-radius: 6px; padding: 0.25rem 0.65rem;">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
    } catch (e) {
        console.error('Error refreshing device list:', e);
    }
    evaluateDeviceStatus();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
