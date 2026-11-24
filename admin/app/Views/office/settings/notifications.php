<?php
/**
 * Office Notification Settings View — Responsive Office Theme
 * Canonical Forest Teal (#176B5B) + Warm Ivory (#F7F8F4) Design System
 */

$auth = auth_user() ?: ($user ?? []);
$userName = $auth['name'] ?? $auth['username'] ?? 'User';
$pref = $preferences ?? [];
?>

<div class="office-settings-container">
    <!-- 1. Canonical Desktop Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Notification Settings',
            'description' => 'Configure your personal in-app alerts, booking events, financial notices, and dispatch triggers.',
            'badge'       => 'Account Preferences',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Settings', 'url' => ''],
                ['label' => 'Notifications', 'url' => ''],
            ],
            'actions'     => '
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <a href="' . url('/office/notifications') . '" class="btn btn-outline" style="border-radius: 10px; gap: 0.45rem;">
                        <i class="fa-solid fa-bell"></i>
                        <span>View Notifications</span>
                    </a>
                    <button type="button" class="btn btn-primary" onclick="submitNotificationSettings(this)" style="border-radius: 10px; gap: 0.45rem;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Preferences</span>
                    </button>
                </div>
            ',
        ]) ?>
    </div>

    <!-- Notification Settings Form -->
    <form action="<?= url('/office/notification-settings') ?>" method="POST" id="notifSettingsForm" onsubmit="event.preventDefault(); submitNotificationSettings(this.querySelector('button[type=submit]'));">
        <?= csrf_field() ?>

        <div class="settings-sections-wrapper">

            <!-- Category 1: General Notifications -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-teal">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">General Notifications</h3>
                        <p class="settings-group-desc">System alerts, in-app notifications, and browser push controls.</p>
                    </div>
                </div>

                <div class="settings-list">
                    <!-- Setting 1 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_general_in_app">In-App Operations Alerts</label>
                            <span class="setting-hint">Display live notification alerts, topbar badges, and popover banners inside Office.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_general_in_app">
                                <input type="checkbox" id="pref_general_in_app" name="general_in_app" value="1" <?= !empty($pref['general_in_app']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['general_in_app']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['general_in_app']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 2 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_general_system_alerts">System &amp; Health Diagnostics</label>
                            <span class="setting-hint">Receive telemetry notices regarding database backups, worker health, and security audits.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_general_system_alerts">
                                <input type="checkbox" id="pref_general_system_alerts" name="general_system_alerts" value="1" <?= !empty($pref['general_system_alerts']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['general_system_alerts']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['general_system_alerts']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 3 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_general_browser_push">Browser Web Push Alerts</label>
                            <span class="setting-hint">Send desktop and device background notifications even when the application tab is minimized.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_general_browser_push">
                                <input type="checkbox" id="pref_general_browser_push" name="general_browser_push" value="1" <?= !empty($pref['general_browser_push']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['general_browser_push']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['general_browser_push']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 2: Booking Notifications -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-blue">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">Booking Notifications</h3>
                        <p class="settings-group-desc">Alerts for new tour itineraries, manifest adjustments, and customer cancellations.</p>
                    </div>
                </div>

                <div class="settings-list">
                    <!-- Setting 1 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_booking_new">New Reservation Bookings</label>
                            <span class="setting-hint">Notify when new customer or corporate bookings are recorded in the system.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_booking_new">
                                <input type="checkbox" id="pref_booking_new" name="booking_new" value="1" <?= !empty($pref['booking_new']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['booking_new']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['booking_new']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 2 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_booking_status_changes">Manifest Status Changes</label>
                            <span class="setting-hint">Updates when booking moves between Confirmed, In Progress, Completed, or Suspended.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_booking_status_changes">
                                <input type="checkbox" id="pref_booking_status_changes" name="booking_status_changes" value="1" <?= !empty($pref['booking_status_changes']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['booking_status_changes']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['booking_status_changes']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 3 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_booking_cancellations">Customer Cancellations</label>
                            <span class="setting-hint">High-priority alerts when a guest or agent cancels a scheduled reservation booking.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_booking_cancellations">
                                <input type="checkbox" id="pref_booking_cancellations" name="booking_cancellations" value="1" <?= !empty($pref['booking_cancellations']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['booking_cancellations']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['booking_cancellations']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 3: Payment & Finance Notifications -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-gold">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">Payment &amp; Finance Notifications</h3>
                        <p class="settings-group-desc">Alerts for recorded receipts, pending dues, overdue balances, and customer refunds.</p>
                    </div>
                </div>

                <div class="settings-list">
                    <!-- Setting 1 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_payment_received">Payment Receipts Received</label>
                            <span class="setting-hint">Notify when cash, card, or bank transfer payments are recorded against invoices.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_payment_received">
                                <input type="checkbox" id="pref_payment_received" name="payment_received" value="1" <?= !empty($pref['payment_received']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['payment_received']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['payment_received']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 2 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_payment_pending_alerts">Pending Due &amp; Overdue Balances</label>
                            <span class="setting-hint">Automated reminders when customer balances or driver advances remain pending.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_payment_pending_alerts">
                                <input type="checkbox" id="pref_payment_pending_alerts" name="payment_pending_alerts" value="1" <?= !empty($pref['payment_pending_alerts']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['payment_pending_alerts']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['payment_pending_alerts']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 3 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_payment_refund_requests">Refunds &amp; Credit Adjustments</label>
                            <span class="setting-hint">Alerts when customer refunds or credit note reversals are issued by finance officers.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_payment_refund_requests">
                                <input type="checkbox" id="pref_payment_refund_requests" name="payment_refund_requests" value="1" <?= !empty($pref['payment_refund_requests']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['payment_refund_requests']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['payment_refund_requests']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 4: Driver & Dispatch Notifications -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-emerald">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">Driver &amp; Dispatch Notifications</h3>
                        <p class="settings-group-desc">Real-time alerts for driver assignments, acceptance responses, and trip completions.</p>
                    </div>
                </div>

                <div class="settings-list">
                    <!-- Setting 1 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_driver_trip_assigned">Trip Duty Deployments</label>
                            <span class="setting-hint">Notify when fleet vehicles and drivers are allocated to passenger dispatches.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_driver_trip_assigned">
                                <input type="checkbox" id="pref_driver_trip_assigned" name="driver_trip_assigned" value="1" <?= !empty($pref['driver_trip_assigned']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['driver_trip_assigned']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['driver_trip_assigned']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 2 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_driver_acceptance">Driver Response &amp; Acceptance</label>
                            <span class="setting-hint">Instant alert when a driver accepts or indicates inability to accept assigned duty.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_driver_acceptance">
                                <input type="checkbox" id="pref_driver_acceptance" name="driver_acceptance" value="1" <?= !empty($pref['driver_acceptance']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['driver_acceptance']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['driver_acceptance']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 3 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_driver_completion">Duty Start &amp; Completion</label>
                            <span class="setting-hint">Live updates when drivers begin route travel and complete passenger drops.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_driver_completion">
                                <input type="checkbox" id="pref_driver_completion" name="driver_completion" value="1" <?= !empty($pref['driver_completion']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['driver_completion']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['driver_completion']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 5: Email & Deliverability Notifications -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-purple">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">Email Notifications</h3>
                        <p class="settings-group-desc">Telemetry notices for outbound customer emails, SMTP queues, and delivery errors.</p>
                    </div>
                </div>

                <div class="settings-list">
                    <!-- Setting 1 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_email_dispatch_alerts">Customer Email Deliveries</label>
                            <span class="setting-hint">Notify when booking confirmations, invoices, and password resets are successfully emailed.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_email_dispatch_alerts">
                                <input type="checkbox" id="pref_email_dispatch_alerts" name="email_dispatch_alerts" value="1" <?= !empty($pref['email_dispatch_alerts']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['email_dispatch_alerts']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['email_dispatch_alerts']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 2 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_email_worker_queue">SMTP Background Outbox Queue</label>
                            <span class="setting-hint">Alerts when outbound queued emails exceed normal queue delivery latency.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_email_worker_queue">
                                <input type="checkbox" id="pref_email_worker_queue" name="email_worker_queue" value="1" <?= !empty($pref['email_worker_queue']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['email_worker_queue']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['email_worker_queue']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Setting 3 -->
                    <div class="setting-item-row">
                        <div class="setting-info">
                            <label class="setting-label" for="pref_email_delivery_failures">SMTP Delivery Failures</label>
                            <span class="setting-hint">Instant alert when an outgoing message encounters authentication, bounce, or connection errors.</span>
                        </div>
                        <div class="setting-control">
                            <label class="switch-toggle" for="pref_email_delivery_failures">
                                <input type="checkbox" id="pref_email_delivery_failures" name="email_delivery_failures" value="1" <?= !empty($pref['email_delivery_failures']) ? 'checked' : '' ?> onchange="updateToggleStatusText(this)">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="toggle-status-text <?= !empty($pref['email_delivery_failures']) ? 'is-enabled' : 'is-disabled' ?>">
                                <?= !empty($pref['email_delivery_failures']) ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 6: Device & PWA Status (Office Themed) -->
            <div class="card settings-group-card">
                <div class="settings-group-header">
                    <div class="settings-icon-pill icon-teal">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <h3 class="settings-group-title">Device &amp; PWA Application Status</h3>
                        <p class="settings-group-desc">Manage standalone app installation and browser notification permissions.</p>
                    </div>
                </div>

                <div class="settings-device-grid">
                    <div class="device-status-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <span style="font-weight: 700; color: #20352F; font-size: 0.95rem;">This Device Permission</span>
                            <span id="devicePermBadge" class="badge" style="background: rgba(23,107,91,0.12); color: #176B5B; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 8px;">
                                Checking...
                            </span>
                        </div>
                        <p style="margin: 0 0 1rem; color: #53685F; font-size: 0.88rem; line-height: 1.5;">
                            Allow browser notifications on this device to receive critical dispatch and booking alerts in real time.
                        </p>
                        <button type="button" class="btn btn-outline btn-sm" id="btnRequestBrowserPerm" onclick="requestBrowserPermission()" style="border-radius: 8px; color: #176B5B; border-color: rgba(23,107,91,0.3);">
                            <i class="fa-regular fa-bell"></i>
                            <span id="btnPermText">Check Permission</span>
                        </button>
                    </div>

                    <div class="device-status-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <span style="font-weight: 700; color: #20352F; font-size: 0.95rem;"><?= e(pwa_app_name()) ?> Standalone PWA</span>
                            <span id="pwaStatusBadge" class="badge" style="background: rgba(33, 135, 101, 0.12); color: #218765; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 8px;">
                                Web Mode
                            </span>
                        </div>
                        <p style="margin: 0 0 1rem; color: #53685F; font-size: 0.88rem; line-height: 1.5;">
                            Install <?= e(pwa_app_name()) ?> onto your desktop dock or mobile home screen for an app experience with offline capability.
                        </p>
                        <button type="button" class="btn btn-primary btn-sm tours-install-btn" onclick="triggerToursInstall(event)" style="border-radius: 8px;">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                            <span class="install-btn-label">Install <?= e(pwa_app_name()) ?> App</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sticky / Mobile Bottom Save Bar -->
            <div class="settings-actions-footer">
                <a href="<?= url('/office/notifications') ?>" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Notifications</span>
                </a>
                <button type="button" id="btnSaveBottom" class="btn btn-primary" onclick="submitNotificationSettings(this)" style="border-radius: 10px; min-width: 160px; font-weight: 700;">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Changes</span>
                </button>
            </div>

        </div>
    </form>
</div>

<style>
/* --------------------------------------------------------------------------
   Office Notification Settings Component Styles (Forest Teal / Ivory Theme)
   -------------------------------------------------------------------------- */
.office-settings-container {
    max-width: 1080px;
    margin: 0 auto;
    padding-bottom: 2rem;
}

.settings-sections-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.settings-group-card {
    background: #FFFFFF;
    border: 1px solid rgba(22, 68, 59, 0.08);
    border-radius: 16px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 4px 20px rgba(22, 68, 59, 0.04);
}

.settings-group-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(22, 68, 59, 0.06);
    margin-bottom: 1rem;
}

.settings-icon-pill {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.settings-icon-pill.icon-teal {
    background: rgba(23, 107, 91, 0.12);
    color: #176B5B;
}

.settings-icon-pill.icon-blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3B82F6;
}

.settings-icon-pill.icon-gold {
    background: rgba(199, 138, 53, 0.12);
    color: #C78A35;
}

.settings-icon-pill.icon-emerald {
    background: rgba(33, 135, 101, 0.12);
    color: #218765;
}

.settings-icon-pill.icon-purple {
    background: rgba(139, 92, 246, 0.12);
    color: #8B5CF6;
}

.settings-group-title {
    margin: 0;
    font-size: 1.12rem;
    font-weight: 700;
    color: #20352F;
}

.settings-group-desc {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    color: #72827A;
}

.settings-list {
    display: flex;
    flex-direction: column;
}

.setting-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.95rem 0;
    border-bottom: 1px solid rgba(22, 68, 59, 0.05);
    gap: 1.5rem;
}

.setting-item-row:last-child {
    border-bottom: none;
    padding-bottom: 0.25rem;
}

.setting-info {
    flex: 1;
    min-width: 200px;
}

.setting-label {
    display: block;
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #20352F;
    cursor: pointer;
}

.setting-hint {
    display: block;
    margin-top: 0.2rem;
    font-size: 0.84rem;
    color: #53685F;
    line-height: 1.45;
}

.setting-control {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

/* Switch Toggle Styling */
.switch-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    margin: 0;
    cursor: pointer;
}

.switch-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #CBD5E1;
    border-radius: 34px;
    transition: background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.switch-slider::before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: #FFFFFF;
    border-radius: 50%;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.switch-toggle input:checked + .switch-slider {
    background-color: #176B5B;
}

.switch-toggle input:checked + .switch-slider::before {
    transform: translateX(22px);
}

.switch-toggle input:focus-visible + .switch-slider {
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.25);
}

.toggle-status-text {
    font-size: 0.8rem;
    font-weight: 700;
    min-width: 58px;
}

.toggle-status-text.is-enabled {
    color: #176B5B;
}

.toggle-status-text.is-disabled {
    color: #94A3B8;
}

.settings-device-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 768px) {
    .settings-device-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.device-status-box {
    background: rgba(247, 248, 244, 0.7);
    border: 1px solid rgba(22, 68, 59, 0.08);
    border-radius: 12px;
    padding: 1.15rem 1.25rem;
}

.settings-actions-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: #FFFFFF;
    border: 1px solid rgba(22, 68, 59, 0.08);
    border-radius: 16px;
    margin-top: 0.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(22, 68, 59, 0.04);
}

/* --------------------------------------------------------------------------
   Mobile Responsive Adjustments (<= 768px)
   -------------------------------------------------------------------------- */
@media (max-width: 768px) {
    .office-settings-container {
        padding: 0 0 3.5rem 0;
    }

    .settings-group-card {
        padding: 1.15rem 1rem;
        border-radius: 14px;
    }

    .setting-item-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 0;
    }

    .setting-control {
        align-self: flex-end;
    }

    .settings-actions-footer {
        flex-direction: column-reverse;
        gap: 0.75rem;
        padding: 1rem;
        margin-bottom: 4rem; /* Adequate spacing so bottom-nav does not cover buttons */
    }

    .settings-actions-footer .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function updateToggleStatusText(checkbox) {
    var statusSpan = checkbox.closest('.setting-control')?.querySelector('.toggle-status-text');
    if (statusSpan) {
        if (checkbox.checked) {
            statusSpan.textContent = 'Enabled';
            statusSpan.className = 'toggle-status-text is-enabled';
        } else {
            statusSpan.textContent = 'Disabled';
            statusSpan.className = 'toggle-status-text is-disabled';
        }
    }
}

async function submitNotificationSettings(btn) {
    var form = document.getElementById('notifSettingsForm');
    if (!form) return;

    var originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving...</span>';
    }

    try {
        var formData = new FormData(form);
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        var res = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        var data = await res.json();
        if (data.success) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Notification preferences saved successfully.', 'success');
            } else {
                alert(data.message || 'Notification preferences saved successfully.');
            }
        } else {
            var errMsg = data.error || 'Failed to save settings. Please retry.';
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(errMsg, 'danger');
            } else {
                alert(errMsg);
            }
        }
    } catch (e) {
        console.error('Error saving settings:', e);
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('An unexpected network error occurred while saving.', 'danger');
        } else {
            alert('An unexpected network error occurred while saving.');
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}

function evaluateBrowserPerm() {
    var badge = document.getElementById('devicePermBadge');
    var btnText = document.getElementById('btnPermText');
    if (!badge) return;

    if (!('Notification' in window)) {
        badge.textContent = 'Unsupported';
        badge.style.background = 'rgba(239,68,68,0.12)';
        badge.style.color = '#EF4444';
        return;
    }

    if (Notification.permission === 'granted') {
        badge.textContent = 'Granted';
        badge.style.background = 'rgba(33, 135, 101, 0.12)';
        badge.style.color = '#218765';
        if (btnText) btnText.textContent = 'Permission Granted';
    } else if (Notification.permission === 'denied') {
        badge.textContent = 'Blocked';
        badge.style.background = 'rgba(239, 68, 68, 0.12)';
        badge.style.color = '#EF4444';
        if (btnText) btnText.textContent = 'Blocked in Site Settings';
    } else {
        badge.textContent = 'Default';
        badge.style.background = 'rgba(199, 138, 53, 0.12)';
        badge.style.color = '#C78A35';
        if (btnText) btnText.textContent = 'Allow Notifications';
    }

    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    var pwaBadge = document.getElementById('pwaStatusBadge');
    if (pwaBadge && isStandalone) {
        pwaBadge.textContent = 'Installed (Standalone)';
    }
}

async function requestBrowserPermission() {
    if (!('Notification' in window)) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Browser notifications are not supported on this device.', 'warning');
        }
        return;
    }

    try {
        var perm = await Notification.requestPermission();
        evaluateBrowserPerm();
        if (perm === 'granted') {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Browser notification permission granted!', 'success');
            }
        } else if (perm === 'denied') {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Notifications are blocked in your browser settings.', 'warning');
            }
        }
    } catch (e) {
        console.error('Permission request error:', e);
    }
}

document.addEventListener('DOMContentLoaded', evaluateBrowserPerm);
</script>
