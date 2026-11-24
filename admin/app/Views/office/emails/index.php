<?php
/**
 * Email Center - Simple Office-Friendly Overview
 * Clean, understandable information hierarchy matching Tours & Travels Office theme.
 */
?>
<div class="email-center-page" style="max-width: 1350px; margin: 0 auto; padding: 1.25rem 0.5rem 2rem;">

    <!-- 1. Office Page Header -->
    <?= render_page_header([
        'title'       => 'Email Center',
        'description' => 'Check your email service, send a test email, and view recent email activity.',
        'badge'       => 'Email Service',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/office/dashboard')],
            ['label' => 'Email Center', 'url' => ''],
        ],
        'actions'     => '<div style="display:flex; gap:0.65rem; align-items:center; flex-wrap:wrap;">'
            . '<a href="' . url('/office/emails/history') . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-clock-rotate-left" style="margin-right:5px;"></i> View History</a>'
            . '<a href="' . url('/office/emails/settings') . '" class="btn btn-primary btn-sm"><i class="fa-solid fa-gear" style="margin-right:5px;"></i> Email Settings</a>'
            . '</div>',
    ]) ?>

    <!-- 2. Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- 3. Flash Notifications -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #065F46; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.92rem;">
            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.15rem;"></i>
            <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('danger')): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #991B1B; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.92rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #EF4444; font-size: 1.15rem;"></i>
            <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('info')): ?>
        <div class="alert alert-info" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #075985; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.92rem;">
            <i class="fa-solid fa-circle-info" style="color: #0284C7; font-size: 1.15rem;"></i>
            <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- 4. Friendly Summary Cards Grid (Exact Office Theme) -->
    <div class="booking-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.15rem; margin-bottom: 1.75rem;">
        
        <!-- Card 1: Emails Sent -->
        <div class="booking-kpi-card" style="cursor: default;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Emails Sent</span>
                <span class="booking-kpi-icon icon-emerald" style="background: rgba(16, 185, 129, 0.12); color: #059669; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px;">
                    <i class="fa-solid fa-paper-plane" style="font-size: 1.1rem;"></i>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.85rem; font-weight: 800; color: #0F172A; margin: 0.4rem 0 0.2rem;">
                <?= number_format((int)($stats['sent'] ?? 0)) ?>
            </div>
            <div class="booking-kpi-sub" style="font-size: 0.82rem; color: #64748B;">
                <span>Emails successfully sent</span>
            </div>
        </div>

        <!-- Card 2: Emails Failed -->
        <div class="booking-kpi-card" style="cursor: default; border-color: <?= ($stats['failed'] ?? 0) > 0 ? 'rgba(239, 68, 68, 0.35)' : 'inherit' ?>;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Emails Failed</span>
                <span class="booking-kpi-icon icon-amber" style="background: rgba(239, 68, 68, 0.12); color: #DC2626; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.85rem; font-weight: 800; color: <?= ($stats['failed'] ?? 0) > 0 ? '#DC2626' : '#0F172A' ?>; margin: 0.4rem 0 0.2rem;">
                <?= number_format((int)($stats['failed'] ?? 0)) ?>
            </div>
            <div class="booking-kpi-sub" style="font-size: 0.82rem; color: #64748B;">
                <?php if (($stats['failed'] ?? 0) > 0): ?>
                    <a href="<?= url('/office/emails/history?status=failed') ?>" style="color: #DC2626; font-weight: 600; text-decoration: none;">
                        Emails that could not be sent &rarr;
                    </a>
                <?php else: ?>
                    <span>Emails that could not be sent</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 3: Emails Waiting -->
        <div class="booking-kpi-card" style="cursor: default;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Emails Waiting</span>
                <span class="booking-kpi-icon icon-teal" style="background: rgba(245, 158, 11, 0.12); color: #D97706; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px;">
                    <i class="fa-solid fa-clock" style="font-size: 1.1rem;"></i>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.85rem; font-weight: 800; color: #0F172A; margin: 0.4rem 0 0.2rem;">
                <?= number_format((int)($stats['pending_retry'] ?? 0)) ?>
            </div>
            <div class="booking-kpi-sub" style="font-size: 0.82rem; color: #64748B;">
                <span>Emails waiting to be sent</span>
            </div>
        </div>

        <!-- Card 4: Today's Emails -->
        <div class="booking-kpi-card" style="cursor: default;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Today's Emails</span>
                <span class="booking-kpi-icon icon-navy" style="background: rgba(23, 107, 91, 0.12); color: #176B5B; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px;">
                    <i class="fa-solid fa-calendar-day" style="font-size: 1.1rem;"></i>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.85rem; font-weight: 800; color: #0F172A; margin: 0.4rem 0 0.2rem;">
                <?= number_format((int)($stats['today_total'] ?? 0)) ?>
            </div>
            <div class="booking-kpi-sub" style="font-size: 0.82rem; color: #64748B;">
                <span>Emails sent or processed today</span>
            </div>
        </div>

    </div>

    <!-- 5. Two-Column Office Grid: Email Service & Send Test Email -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Left: Email Service Status -->
        <div class="vg-card" style="margin-bottom: 0;">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(23, 107, 91, 0.1); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <span class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F172A;">Email Service</span>
                </div>
                <div id="serviceStatusBadgeWrap">
                    <?php if (!empty($smtpConfig['is_configured'])): ?>
                        <span class="badge badge-success" id="serviceStatusBadge" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">
                            <span class="badge-dot"></span> Working
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" id="serviceStatusBadge" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">
                            <span class="badge-dot"></span> Not Configured
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                    Your email service is used to send booking confirmations, payment receipts, invoices, driver notifications, OTPs, and other important messages.
                </p>

                <!-- Status overview grid -->
                <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 12px; padding: 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.88rem;">
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748B; letter-spacing: 0.04em;">Sender Name</div>
                            <div style="font-weight: 700; color: #0F172A; margin-top: 0.2rem;"><?= e($smtpConfig['from_name'] ?: 'Not set') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748B; letter-spacing: 0.04em;">Sender Email</div>
                            <div style="font-weight: 600; color: #176B5B; margin-top: 0.2rem; word-break: break-all;"><?= e($smtpConfig['from_address'] ?: 'Not set') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748B; letter-spacing: 0.04em;">Service Type</div>
                            <?php
                                $hostLower = strtolower($smtpConfig['host'] ?? '');
                                $providerName = str_contains($hostLower, 'gmail') ? 'Google Workspace / Gmail' : (!empty($smtpConfig['host']) ? 'Custom Mail Server' : 'Not configured');
                            ?>
                            <div style="font-weight: 600; color: #334155; margin-top: 0.2rem;"><?= e($providerName) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748B; letter-spacing: 0.04em;">Security</div>
                            <div style="font-weight: 600; color: #334155; margin-top: 0.2rem;">
                                <?= !empty($smtpConfig['encryption']) ? 'Encrypted (' . strtoupper(e($smtpConfig['encryption'])) . ')' : 'Standard' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Test Alert Box (Hidden by default) -->
                <div id="connectionStatusAlert" style="display: none; padding: 0.85rem 1rem; border-radius: 10px; font-size: 0.88rem; margin-bottom: 1.25rem;"></div>

                <!-- Service Actions -->
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline btn-sm" id="btnCheckConnection" onclick="checkEmailConnection(this)">
                        <i class="fa-solid fa-rotate" style="margin-right: 5px;"></i>
                        <span id="btnCheckConnectionText">Check Connection</span>
                    </button>
                    <a href="<?= url('/office/emails/settings') ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-sliders" style="margin-right: 5px;"></i>
                        <span>Configure Settings</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Send Test Email -->
        <div class="vg-card" style="margin-bottom: 0;">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56, 189, 248, 0.12); color: #0284C7; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <span class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F172A;">Send Test Email</span>
                </div>
            </div>

            <div class="card-body">
                <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                    Send a test email to make sure your email service is working correctly.
                </p>

                <form action="<?= url('/office/emails/send-test') ?>" method="POST" id="testEmailForm" onsubmit="handleSendTestEmail(event, this)">
                    <?= csrf_field() ?>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="testEmailRecipient" class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; display: block;">
                            Recipient Email Address *
                        </label>
                        <div style="position: relative;">
                            <input type="email" 
                                   id="testEmailRecipient" 
                                   name="recipient_email" 
                                   class="form-control" 
                                   required 
                                   placeholder="<?= e(__('placeholder.e_g_name_example_com', 'e.g. name@example.com')) ?>" 
                                   value="<?= e($currentUserEmail ?? '') ?>"
                                   style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; font-size: 0.92rem; border-radius: 8px; border: 1px solid var(--vg-pearl-200, #CBD5E1);">
                        </div>
                        <small style="display: block; margin-top: 0.35rem; color: #94A3B8; font-size: 0.8rem;">
                            We will send a clean sample email to this address to verify outgoing delivery.
                        </small>
                    </div>

                    <!-- Inline test email alert message -->
                    <div id="testEmailResultAlert" style="display: none; padding: 0.85rem 1rem; border-radius: 10px; font-size: 0.88rem; margin-bottom: 1.15rem;"></div>

                    <button type="submit" id="btnSendTestSubmit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; font-weight: 700; border-radius: 10px;">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Send Test Email</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- 6. Recent Emails Table / Mobile Cards -->
    <div class="vg-card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span class="card-title" style="font-size: 1.05rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: #176B5B;"></i>
                    <span>Recent Emails</span>
                </span>
                <p style="margin: 0.25rem 0 0; color: #64748B; font-size: 0.82rem;">
                    See the latest emails sent by your system.
                </p>
            </div>
            <a href="<?= url('/office/emails/history') ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <span>View Full History</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="card-body" style="padding: 0;">
            <!-- Desktop Table System -->
            <div class="modern-table-container" style="border: none; box-shadow: none; border-radius: 0;">
                <table class="modern-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="table-col-index text-center col-priority-1">#</th>
                            <th class="table-col-name col-priority-1"><?= e(__('table.recipient', 'Recipient')) ?></th>
                            <th class="table-col-category col-priority-1"><?= e(__('table.email_type', 'Email Type')) ?></th>
                            <th class="col-priority-2" data-column-priority="secondary"><?= e(__('table.subject', 'Subject')) ?></th>
                            <th class="table-col-status text-center col-priority-1"><?= e(__('table.status', 'Status')) ?></th>
                            <th class="table-col-schedule text-center col-priority-1"><?= e(__('table.date', 'Date')) ?></th>
                            <th class="table-col-actions text-end col-priority-1"><?= e(__('common.actions', 'Actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="7" style="padding: 3rem 1.5rem; text-align: center; color: #64748B;">
                                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #F1F5F9; color: #94A3B8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 0.75rem;">
                                        <i class="fa-regular fa-folder-open"></i>
                                    </div>
                                    <h4 style="font-weight: 700; color: #1E293B; margin-bottom: 0.25rem;">No emails recorded yet</h4>
                                    <p style="font-size: 0.88rem; margin: 0; color: #94A3B8;">Emails sent by your system will show up here.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): 
                                $statusInfo = format_email_status_friendly($log['status'] ?? 'unknown');
                                $friendlyType = format_email_type_friendly($log['email_type'] ?? '');
                            ?>
                                <tr>
                                    <td class="table-col-index text-center col-priority-1" style="font-family: ui-monospace, monospace; color: #64748B; font-weight: 600;">
                                        #<?= (int)$log['id'] ?>
                                    </td>
                                    <td class="table-col-name col-priority-1">
                                        <div class="table-cell-truncate" style="font-weight: 700; color: #0F172A;" title="<?= e($log['recipient_name'] ?: 'Customer / Guest') ?>"><?= e($log['recipient_name'] ?: 'Customer / Guest') ?></div>
                                        <div class="table-cell-truncate" style="color: #64748B; font-size: 0.82rem; margin-top: 2px;" title="<?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?>"><?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?></div>
                                    </td>
                                    <td class="table-col-category col-priority-1">
                                        <span class="badge" style="background: rgba(23, 107, 91, 0.08); color: #16443B; border: 1px solid rgba(23, 107, 91, 0.2); font-weight: 600; font-size: 0.8rem; padding: 0.3rem 0.65rem; border-radius: 6px;">
                                            <i class="fa-solid fa-tag" style="font-size: 0.7rem; margin-right: 3px;"></i>
                                            <?= e($friendlyType) ?>
                                        </span>
                                    </td>
                                    <td class="col-priority-2" data-column-priority="secondary" style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #334155; font-size: 0.88rem;" title="<?= e($log['subject'] ?? 'No Subject') ?>">
                                        <span class="table-cell-truncate" style="max-width: 100%;"><?= e($log['subject'] ?? 'No Subject') ?></span>
                                    </td>
                                    <td class="table-col-status text-center col-priority-1">
                                        <span class="badge <?= e($statusInfo['badge']) ?>" style="font-size: 0.78rem; padding: 0.3rem 0.7rem;" title="<?= e($statusInfo['desc']) ?>">
                                            <i class="<?= e($statusInfo['icon']) ?>" style="margin-right: 4px; font-size: 0.75rem;"></i>
                                            <?= e($statusInfo['label']) ?>
                                        </span>
                                    </td>
                                    <td class="table-col-schedule text-center col-priority-1" style="color: #64748B; font-size: 0.82rem; white-space: nowrap;">
                                        <?= date('d M Y, h:i A', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td class="table-col-actions text-end col-priority-1" style="white-space: nowrap;">
                                        <div class="action-dropdown-wrap">
                                            <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" aria-label="<?= e(__('common.actions', 'Actions')) ?>">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                <span>Actions</span>
                                            </button>
                                            <div class="action-dropdown-menu">
                                                <button type="button" class="action-dropdown-item" onclick="openEmailDetailsModal(<?= (int)$log['id'] ?>)">
                                                    <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                                                </button>
                                                <?php if (($log['status'] ?? '') !== 'sent'): ?>
                                                    <div class="action-dropdown-divider"></div>
                                                    <button type="button" class="action-dropdown-item text-warning" onclick="triggerEmailRetry(<?= (int)$log['id'] ?>, this)">
                                                        <i class="fa-solid fa-rotate-right"></i> <span>Retry Sending</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Responsive Card System (Visible on small screens) -->
            <div class="mobile-email-cards cards-grid" style="padding: 0.75rem;">
                <?php if (empty($recentLogs)): ?>
                    <div style="padding: 2rem 1rem; text-align: center; color: #64748B;">
                        No emails recorded yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): 
                        $statusInfo = format_email_status_friendly($log['status'] ?? 'unknown');
                        $friendlyType = format_email_type_friendly($log['email_type'] ?? '');
                    ?>
                        <div class="mv-email-card" style="background: #FFFFFF; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div>
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.95rem;"><?= e($log['recipient_name'] ?: 'Customer / Guest') ?></div>
                                    <div style="color: #64748B; font-size: 0.82rem; word-break: break-all;"><?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?></div>
                                </div>
                                <span class="badge <?= e($statusInfo['badge']) ?>" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; white-space: nowrap;">
                                    <?= e($statusInfo['label']) ?>
                                </span>
                            </div>

                            <div style="margin: 0.4rem 0;">
                                <span class="badge" style="background: rgba(23, 107, 91, 0.08); color: #16443B; font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.55rem; border-radius: 6px;">
                                    <i class="fa-solid fa-tag" style="font-size: 0.7rem; margin-right: 3px;"></i>
                                    <?= e($friendlyType) ?>
                                </span>
                            </div>

                            <div style="font-size: 0.85rem; color: #334155; margin: 0.4rem 0; word-break: break-word;">
                                <?= e($log['subject'] ?? 'No Subject') ?>
                            </div>

                            <div style="font-size: 0.8rem; color: #94A3B8; margin-top: 0.5rem;">
                                <i class="fa-regular fa-calendar"></i> <?= date('d M, h:i A', strtotime($log['created_at'])) ?>
                            </div>
                            <div class="mv-card-actions">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" aria-label="<?= e(__('common.actions', 'Actions')) ?>">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                        <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <button type="button" class="action-dropdown-item" onclick="openEmailDetailsModal(<?= (int)$log['id'] ?>)">
                                            <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                                        </button>
                                        <?php if (($log['status'] ?? '') !== 'sent'): ?>
                                            <button type="button" class="action-dropdown-item text-warning" onclick="triggerEmailRetry(<?= (int)$log['id'] ?>, this)">
                                                <i class="fa-solid fa-rotate-right"></i> <span>Retry Sending</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Friendly Email Details -->
<div id="emailDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" onclick="handleModalBackdropClick(event)">
    <div style="background: #FFFFFF; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 16px; width: 100%; max-width: 760px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
        
        <!-- Modal Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--vg-pearl-200, #E2E8F0); display: flex; align-items: center; justify-content: space-between; background: #FAFAFA;">
            <div style="font-weight: 700; color: #0F172A; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-envelope-open-text" style="color: #176B5B;"></i>
                <span id="modalEmailTitle">Email Details</span>
            </div>
            <button type="button" onclick="closeDetailsModal()" style="background: transparent; border: none; color: #64748B; font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 0.2rem 0.4rem; border-radius: 6px;">&times;</button>
        </div>

        <!-- Modal Loading State -->
        <div id="modalLoading" style="padding: 3rem; text-align: center; color: #64748B;">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 2rem; color: #176B5B; margin-bottom: 0.75rem; display: block;"></i>
            Loading email information...
        </div>

        <!-- Modal Content -->
        <div id="modalContent" style="display: none; padding: 1.5rem; overflow-y: auto; flex: 1;">
            
            <!-- Email Information -->
            <div style="background: #F8FAFC; padding: 1.15rem; border-radius: 12px; border: 1px solid var(--vg-pearl-200, #E2E8F0); margin-bottom: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; font-size: 0.88rem;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Recipient</div>
                        <div id="modalRecipient" style="font-weight: 700; color: #0F172A; margin-top: 0.2rem;"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Email Type</div>
                        <div id="modalType" style="font-weight: 600; color: #176B5B; margin-top: 0.2rem;"></div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Subject</div>
                        <div id="modalSubject" style="font-weight: 600; color: #334155; margin-top: 0.2rem;"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Delivery Status</div>
                        <div id="modalStatusBadgeWrap" style="margin-top: 0.2rem;"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Sent Date</div>
                        <div id="modalSentAt" style="font-weight: 600; color: #334155; margin-top: 0.2rem;"></div>
                    </div>
                </div>
            </div>

            <!-- Message Preview -->
            <div style="margin-bottom: 1.25rem;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-eye" style="color: #64748B;"></i>
                    <span>Message Preview</span>
                </div>
                <iframe id="modalBodyFrame" style="width: 100%; height: 260px; background: #FFFFFF; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #CBD5E1);" sandbox="allow-same-origin"></iframe>
            </div>

            <!-- Collapsible Technical Details (For diagnostics) -->
            <details style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.85rem;">
                <summary style="font-weight: 700; color: #475569; cursor: pointer; user-select: none;">
                    <i class="fa-solid fa-wrench" style="margin-right: 5px; color: #94A3B8;"></i>
                    <span>Technical Details (Diagnostic Log)</span>
                </summary>
                <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed #CBD5E1; font-size: 0.82rem; color: #475569;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-bottom: 0.65rem;">
                        <div><strong>Log Record ID:</strong> <span id="modalLogId"></span></div>
                        <div><strong>Send Attempts:</strong> <span id="modalAttempts"></span></div>
                        <div><strong>Message ID:</strong> <span id="modalMessageId" style="font-family: monospace;"></span></div>
                        <div><strong>Related Record:</strong> <span id="modalRef"></span></div>
                    </div>
                    <div id="modalErrorContainer" style="display: none; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 0.75rem; margin-top: 0.5rem;">
                        <strong style="color: #DC2626; display: block; margin-bottom: 0.25rem;">Server Response / Error Details:</strong>
                        <div id="modalErrorMessage" style="color: #B91C1C; font-family: monospace; white-space: pre-wrap; word-break: break-all; font-size: 0.8rem;"></div>
                    </div>
                </div>
            </details>

        </div>

        <!-- Modal Footer -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--vg-pearl-200, #E2E8F0); display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; background: #FAFAFA;">
            <div id="modalRetryContainer" style="display: inline;"></div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeDetailsModal()"><?= e(__('common.close', 'Close')) ?></button>
        </div>

    </div>
</div>

<style>
@media (max-width: 768px) {
    .modern-table-container {
        display: none !important;
    }
    .mobile-email-cards {
        display: block !important;
    }
}
</style>

<script>
/**
 * Test Connection check with friendly feedback
 */
function checkEmailConnection(btn) {
    const textEl = document.getElementById('btnCheckConnectionText');
    const alertEl = document.getElementById('connectionStatusAlert');
    const badgeWrap = document.getElementById('serviceStatusBadgeWrap');
    const originalText = textEl.textContent;

    btn.disabled = true;
    textEl.textContent = 'Checking...';
    alertEl.style.display = 'none';

    fetch('<?= url('/office/emails/test-smtp') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_token() ?>'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        textEl.textContent = originalText;
        alertEl.style.display = 'block';

        if (data.connected || data.success) {
            alertEl.style.background = 'rgba(16, 185, 129, 0.1)';
            alertEl.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            alertEl.style.color = '#065F46';
            alertEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10B981; margin-right:6px;"></i> <strong>Connection successful!</strong> Your email service is working and ready to send messages.' + (data.latency_ms ? ' (' + data.latency_ms + 'ms response time)' : '');
            
            if (badgeWrap) {
                badgeWrap.innerHTML = '<span class="badge badge-success" style="font-size:0.82rem; padding:0.35rem 0.75rem;"><span class="badge-dot"></span> Working</span>';
            }
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Email service is working properly!', 'success');
            }
        } else {
            alertEl.style.background = 'rgba(239, 68, 68, 0.1)';
            alertEl.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            alertEl.style.color = '#991B1B';
            alertEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Connection problem:</strong> ' + (data.message || data.error || 'Could not connect to the email server.') + ' Please check your email settings.';
            
            if (badgeWrap) {
                badgeWrap.innerHTML = '<span class="badge badge-danger" style="font-size:0.82rem; padding:0.35rem 0.75rem;"><span class="badge-dot"></span> Connection Problem</span>';
            }
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Could not connect to email service.', 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        textEl.textContent = originalText;
        alertEl.style.display = 'block';
        alertEl.style.background = 'rgba(239, 68, 68, 0.1)';
        alertEl.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        alertEl.style.color = '#991B1B';
        alertEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Connection check failed:</strong> Please check your internet connection or try again later.';
    });
}

/**
 * Handle sending a real test email with friendly notifications (no browser alert)
 */
function handleSendTestEmail(e, form) {
    e.preventDefault();
    const btn = document.getElementById('btnSendTestSubmit');
    const resultAlert = document.getElementById('testEmailResultAlert');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Sending...</span>';
    resultAlert.style.display = 'none';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultAlert.style.display = 'block';

        if (data.success) {
            resultAlert.style.background = 'rgba(16, 185, 129, 0.1)';
            resultAlert.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            resultAlert.style.color = '#065F46';
            resultAlert.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10B981; margin-right:6px;"></i> <strong>Test email sent!</strong> ' + (data.message || 'Please check the inbox of the recipient address.');

            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Test email sent successfully! Please check your inbox.', 'success');
            }
        } else {
            resultAlert.style.background = 'rgba(239, 68, 68, 0.1)';
            resultAlert.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            resultAlert.style.color = '#991B1B';
            resultAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Email could not be sent.</strong> ' + (data.message || 'Please check the email service settings or try again later.');

            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Email could not be sent. Please check your settings.', 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultAlert.style.display = 'block';
        resultAlert.style.background = 'rgba(239, 68, 68, 0.1)';
        resultAlert.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        resultAlert.style.color = '#991B1B';
        resultAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Error:</strong> ' + err.message;
    });
}

/**
 * Open Email Details Modal
 */
function openEmailDetailsModal(id) {
    const modal = document.getElementById('emailDetailsModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const retryContainer = document.getElementById('modalRetryContainer');

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';
    retryContainer.innerHTML = '';
    document.getElementById('modalEmailTitle').textContent = 'Email Details #' + id;

    fetch('<?= url('/office/emails/details/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        loading.style.display = 'none';
        if (!data.success || !data.log) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Failed to load details.', 'error');
            }
            closeDetailsModal();
            return;
        }

        const log = data.log;
        content.style.display = 'block';
        
        document.getElementById('modalLogId').textContent = '#' + log.id;
        document.getElementById('modalRecipient').textContent = (log.recipient_name ? log.recipient_name + ' ' : '') + '<' + log.recipient_email + '>';
        document.getElementById('modalType').textContent = log.email_type;
        document.getElementById('modalSubject').textContent = log.subject || 'No Subject';
        document.getElementById('modalAttempts').textContent = log.attempt_count + ' of ' + log.max_attempts;
        document.getElementById('modalSentAt').textContent = log.sent_at || log.created_at || '—';
        document.getElementById('modalMessageId').textContent = log.message_id || 'Not assigned';
        document.getElementById('modalRef').textContent = log.reference_type ? (log.reference_type + ' #' + log.reference_id) : 'None';

        // Friendly status badge
        const badgeWrap = document.getElementById('modalStatusBadgeWrap');
        let statusBadgeClass = 'badge-neutral';
        let statusText = log.status || 'unknown';
        if (log.status === 'sent') {
            statusBadgeClass = 'badge-success';
            statusText = 'Sent Successfully';
        } else if (log.status === 'failed') {
            statusBadgeClass = 'badge-danger';
            statusText = 'Could Not Send';
        } else if (log.status === 'pending_retry' || log.status === 'pending') {
            statusBadgeClass = 'badge-warning';
            statusText = 'Waiting to Send';
        }
        badgeWrap.innerHTML = '<span class="badge ' + statusBadgeClass + '" style="font-size:0.8rem; padding:0.3rem 0.65rem;"><span class="badge-dot"></span> ' + statusText + '</span>';

        // Error message in diagnostic box
        const errBox = document.getElementById('modalErrorContainer');
        const errMsg = document.getElementById('modalErrorMessage');
        if (log.error_message) {
            errBox.style.display = 'block';
            errMsg.textContent = log.error_message;
        } else {
            errBox.style.display = 'none';
        }

        // Render preview iframe
        const frame = document.getElementById('modalBodyFrame');
        frame.srcdoc = log.html_body || '<div style="padding:20px; font-family:sans-serif; color:#64748B;">No message body captured.</div>';

        // If failed, allow retry
        if (log.status !== 'sent') {
            retryContainer.innerHTML = '<button type="button" class="btn btn-warning btn-sm" onclick="triggerEmailRetry(' + log.id + ', this)"><i class="fa-solid fa-rotate-right" style="margin-right:4px;"></i> Retry Sending</button>';
        }
    })
    .catch(err => {
        loading.style.display = 'none';
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Network error loading details: ' + err.message, 'error');
        }
        closeDetailsModal();
    });
}

function closeDetailsModal() {
    document.getElementById('emailDetailsModal').style.display = 'none';
}

function handleModalBackdropClick(e) {
    if (e.target && e.target.id === 'emailDetailsModal') {
        closeDetailsModal();
    }
}

/**
 * Trigger immediate safe retry
 */
function triggerEmailRetry(id, btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

    const formData = new FormData();
    formData.append('_csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url('/office/emails/retry/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (data.success) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Email resent successfully!', 'success');
            }
            setTimeout(() => window.location.reload(), 1200);
        } else {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Could not resend email.', 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Error: ' + err.message, 'error');
        }
    });
}
</script>
