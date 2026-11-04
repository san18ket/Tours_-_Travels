<div class="page-container" style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-envelope-circle-check" style="color: #10B981;"></i>
                <span>SMTP Email System Diagnostics &amp; Worker Telemetry</span>
            </h2>
            <p style="margin: 0.25rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Autonomous outbox queue monitoring, worker heartbeats, and audit trail for outgoing email dispatches.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.85rem; padding: 0.4rem 0.85rem; border-radius: 8px;">
                <i class="fa-solid fa-shield-halved"></i> Admin Telemetry
            </span>
        </div>
    </div>

    <!-- Alert: Worker Stale Warning if pending items exist without active worker -->
    <?php if (($queueCounts['pending'] ?? 0) > 0 && empty($workerHealth['is_active'])): ?>
        <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.85rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #F59E0B; font-size: 1.25rem; margin-top: 0.15rem;"></i>
            <div style="flex: 1;">
                <div style="color: #FBBF24; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem;">
                    Background Email Worker Inactive (<?= (int)$queueCounts['pending'] ?> pending email<?= $queueCounts['pending'] > 1 ? 's' : '' ?> in outbox)
                </div>
                <div style="color: #CBD5E1; font-size: 0.85rem; line-height: 1.5;">
                    Emails are safely enqueued in the database outbox without blocking web pages. To dispatch pending emails, execute:
                    <div style="background: #0B192C; border: 1px solid #335384; border-radius: 6px; padding: 0.4rem 0.65rem; margin-top: 0.4rem; font-family: monospace; font-size: 0.8rem; color: #38BDF8; display: inline-block;">
                        php bin/email_worker.php --once
                    </div>
                    &nbsp;or keep it running continuously as a daemon:
                    <div style="background: #0B192C; border: 1px solid #335384; border-radius: 6px; padding: 0.4rem 0.65rem; margin-top: 0.4rem; font-family: monospace; font-size: 0.8rem; color: #34D399; display: inline-block;">
                        php bin/email_worker.php --daemon
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Diagnostic Cards Grid (4 Cards) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <!-- Card 1: SMTP Relay -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">SMTP Relay Host</span>
                <i class="fa-solid fa-server" style="color: #60A5FA;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.15rem; font-weight: 700;">
                <?= e($smtpHost) ?>
            </div>
            <div style="color: #38BDF8; font-size: 0.8rem; margin-top: 0.25rem;">
                Port <?= (int)$smtpPort ?> • <?= strtoupper(e($encryption)) ?> Encrypted
            </div>
        </div>

        <!-- Card 2: Auth Status -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Authentication</span>
                <i class="fa-solid fa-key" style="color: #F59E0B;"></i>
            </div>
            <div style="color: <?= $isConfigured ? '#10B981' : '#F59E0B' ?>; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid <?= $isConfigured ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= $isConfigured ? 'Credentials Active' : 'Setup Required' ?></span>
            </div>
            <div style="color: #94A3B8; font-size: 0.8rem; margin-top: 0.25rem;">
                <?= e($senderEmail) ?>
            </div>
        </div>

        <!-- Card 3: Worker Heartbeat Telemetry -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Queue Worker</span>
                <i class="fa-solid fa-robot" style="color: #A78BFA;"></i>
            </div>
            <div style="color: <?= !empty($workerHealth['is_active']) ? '#10B981' : '#94A3B8' ?>; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid <?= !empty($workerHealth['is_active']) ? 'fa-circle-dot fa-fade' : 'fa-circle-pause' ?>" style="color: <?= !empty($workerHealth['is_active']) ? '#10B981' : '#64748B' ?>;"></i>
                <span><?= !empty($workerHealth['is_active']) ? 'Daemon Active' : 'Idle / Scheduled' ?></span>
            </div>
            <div style="color: #94A3B8; font-size: 0.8rem; margin-top: 0.25rem;">
                <?php if (!empty($workerHealth['last_heartbeat_at'])): ?>
                    Last seen: <?= e($workerHealth['last_heartbeat_at']) ?>
                <?php else: ?>
                    Run worker via CLI or Task Scheduler
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 4: Outbox Health Counts -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Outbox Status</span>
                <i class="fa-solid fa-inbox" style="color: #38BDF8;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.6rem;">
                <span title="Pending" style="color: #FBBF24;"><i class="fa-solid fa-clock"></i> <?= (int)($queueCounts['pending'] ?? 0) ?></span>
                <span style="color: #64748B;">•</span>
                <span title="Sent" style="color: #10B981;"><i class="fa-solid fa-check"></i> <?= (int)($queueCounts['sent'] ?? 0) ?></span>
                <span style="color: #64748B;">•</span>
                <span title="Failed" style="color: #EF4444;"><i class="fa-solid fa-xmark"></i> <?= (int)($queueCounts['failed'] ?? 0) ?></span>
            </div>
            <div style="color: #94A3B8; font-size: 0.8rem; margin-top: 0.25rem;">
                Processing: <?= (int)($queueCounts['processing'] ?? 0) ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.75rem;">
        
        <!-- Left: Dispatch Test Message Form -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-paper-plane" style="color: #10B981;"></i>
                <span>Dispatch Live Test Email</span>
            </h3>
            <p style="color: #94A3B8; font-size: 0.85rem; line-height: 1.45; margin-bottom: 1.25rem;">
                Send a real-time branded diagnostic email to any verified inbox to verify Google SMTP relay connectivity and TLS handshake.
            </p>

            <form action="<?= url('/office/email/test') ?>" method="POST" data-no-ajax="true">
                <?= csrf_field() ?>
                
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; color: #CBD5E1; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">
                        Target Recipient Email Address <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="email" name="recipient_email" required placeholder="e.g. yourname@example.com" 
                        class="form-control" style="width: 100%; background: #0B192C; border: 1px solid #335384; color: #FFFFFF; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem;"
                        value="<?= e(auth_user()['email'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 8px; padding: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Verification Test Email</span>
                </button>
            </form>
        </div>

        <!-- Right: Background Worker Quick Guide -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-terminal" style="color: #38BDF8;"></i>
                <span>Queue Worker Deployment Guide</span>
            </h3>
            <p style="color: #94A3B8; font-size: 0.85rem; line-height: 1.45; margin-bottom: 0.75rem;">
                Customer/Employee creation, Booking reservations, and Driver assignments enqueue emails asynchronously in &lt;5ms. Process the outbox using:
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.82rem;">
                <div style="background: #0B192C; border: 1px solid #335384; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="color: #94A3B8; font-weight: 600; margin-bottom: 0.2rem;">Option A: Scheduled Task (Recommended for Local Dev &amp; Windows XAMPP)</div>
                    <code style="color: #38BDF8; font-family: monospace;">php bin/email_worker.php --once</code>
                    <div style="color: #64748B; font-size: 0.75rem; margin-top: 0.2rem;">Schedule every 1 minute via Windows Task Scheduler or cron.</div>
                </div>

                <div style="background: #0B192C; border: 1px solid #335384; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="color: #94A3B8; font-weight: 600; margin-bottom: 0.2rem;">Option B: Long-Running Daemon Service</div>
                    <code style="color: #34D399; font-family: monospace;">php bin/email_worker.php --daemon --batch=10 --sleep=5</code>
                    <div style="color: #64748B; font-size: 0.75rem; margin-top: 0.2rem;">Polls every 5s with auto-recovery of stale locks and heartbeat telemetry.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outgoing Email Audit Trail Table & Queue Management -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
        
        <!-- Filter Tabs & Actions -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <h3 style="margin: 0 0 0.5rem 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-list-check" style="color: #A78BFA;"></i>
                    <span>Email Outbox &amp; Transmission Audit Trail (<?= count($logs) ?>)</span>
                </h3>

                <!-- Filter Tabs -->
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="<?= url('/office/email/test') ?>" 
                        class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>" 
                        style="padding: 0.25rem 0.65rem; font-size: 0.78rem; border-radius: 6px;">
                        All Logs
                    </a>
                    <a href="<?= url('/office/email/test?status=pending') ?>" 
                        class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline' ?>" 
                        style="padding: 0.25rem 0.65rem; font-size: 0.78rem; border-radius: 6px; color: <?= $statusFilter === 'pending' ? '#FFF' : '#FBBF24' ?>;">
                        <i class="fa-solid fa-clock"></i> Pending (<?= (int)($queueCounts['pending'] ?? 0) ?>)
                    </a>
                    <a href="<?= url('/office/email/test?status=processing') ?>" 
                        class="btn btn-sm <?= $statusFilter === 'processing' ? 'btn-primary' : 'btn-outline' ?>" 
                        style="padding: 0.25rem 0.65rem; font-size: 0.78rem; border-radius: 6px; color: <?= $statusFilter === 'processing' ? '#FFF' : '#38BDF8' ?>;">
                        <i class="fa-solid fa-spinner"></i> In Progress (<?= (int)($queueCounts['processing'] ?? 0) ?>)
                    </a>
                    <a href="<?= url('/office/email/test?status=sent') ?>" 
                        class="btn btn-sm <?= $statusFilter === 'sent' ? 'btn-primary' : 'btn-outline' ?>" 
                        style="padding: 0.25rem 0.65rem; font-size: 0.78rem; border-radius: 6px; color: <?= $statusFilter === 'sent' ? '#FFF' : '#10B981' ?>;">
                        <i class="fa-solid fa-check"></i> Sent (<?= (int)($queueCounts['sent'] ?? 0) ?>)
                    </a>
                    <a href="<?= url('/office/email/test?status=failed') ?>" 
                        class="btn btn-sm <?= $statusFilter === 'failed' ? 'btn-primary' : 'btn-outline' ?>" 
                        style="padding: 0.25rem 0.65rem; font-size: 0.78rem; border-radius: 6px; color: <?= $statusFilter === 'failed' ? '#FFF' : '#EF4444' ?>;">
                        <i class="fa-solid fa-xmark"></i> Failed (<?= (int)($queueCounts['failed'] ?? 0) ?>)
                    </a>
                </div>
            </div>

            <div>
                <button type="button" class="btn btn-outline btn-sm" onclick="window.location.reload()" style="color: #FFF; border-color: rgba(255,255,255,0.2); border-radius: 6px; padding: 0.4rem 0.8rem;">
                    <i class="fa-solid fa-arrows-rotate"></i> Refresh Queue
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; color: #CBD5E1;">
                <thead>
                    <tr style="border-bottom: 1px solid #335384; color: #94A3B8; text-align: left;">
                        <th style="padding: 0.75rem 0.5rem;">ID</th>
                        <th style="padding: 0.75rem 0.5rem;">Recipient</th>
                        <th style="padding: 0.75rem 0.5rem;">Type</th>
                        <th style="padding: 0.75rem 0.5rem;">Subject</th>
                        <th style="padding: 0.75rem 0.5rem;">Status &amp; Attempts</th>
                        <th style="padding: 0.75rem 0.5rem;">Created / Dispatched</th>
                        <th style="padding: 0.75rem 0.5rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94A3B8;">
                                <i class="fa-regular fa-envelope" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.4;"></i>
                                No email logs match the current filter criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid rgba(51, 83, 132, 0.4);">
                                <td style="padding: 0.75rem 0.5rem; font-family: monospace; color: #94A3B8;">#<?= (int)$log['id'] ?></td>
                                <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: #FFFFFF;">
                                    <?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?>
                                    <?php if (!empty($log['recipient_name'])): ?>
                                        <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 400;"><?= e($log['recipient_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: rgba(56, 189, 248, 0.15); color: #38BDF8;">
                                        <?= e($log['email_type']) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($log['subject']) ?>
                                    <?php if (!empty($log['error_message'])): ?>
                                        <div style="font-size: 0.75rem; color: #EF4444; margin-top: 0.2rem; overflow: hidden; text-overflow: ellipsis;" title="<?= e($log['error_message']) ?>">
                                            <?= e(substr($log['error_message'], 0, 70)) ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #10B981; font-weight: 600;">
                                            <i class="fa-solid fa-circle-check"></i> Sent
                                        </span>
                                    <?php elseif ($log['status'] === 'pending'): ?>
                                        <div>
                                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #FBBF24; font-weight: 600;">
                                                <i class="fa-solid fa-clock"></i> Queued
                                            </span>
                                            <div style="font-size: 0.72rem; color: #94A3B8;">
                                                Attempt <?= (int)($log['attempt_count'] ?? 0) ?>/<?= (int)($log['max_attempts'] ?? 5) ?>
                                            </div>
                                        </div>
                                    <?php elseif ($log['status'] === 'processing'): ?>
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #38BDF8; font-weight: 600;">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Processing
                                        </span>
                                    <?php else: ?>
                                        <div>
                                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #EF4444; font-weight: 600;">
                                                <i class="fa-solid fa-circle-xmark"></i> Failed
                                            </span>
                                            <div style="font-size: 0.72rem; color: #94A3B8;">
                                                Attempt <?= (int)($log['attempt_count'] ?? 0) ?>/<?= (int)($log['max_attempts'] ?? 5) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; color: #94A3B8; font-size: 0.8rem;">
                                    <?= format_datetime($log['created_at']) ?>
                                    <?php if (!empty($log['sent_at'])): ?>
                                        <div style="font-size: 0.72rem; color: #10B981;">Dispatched: <?= date('H:i:s', strtotime($log['sent_at'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center; white-space: nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick="openEmailDetailsModal(<?= (int)$log['id'] ?>)" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 4px; margin-right: 0.3rem; color: #38BDF8; border-color: rgba(56, 189, 248, 0.4);" title="View Email Details">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>

                                    <?php if ($log['status'] !== 'sent'): ?>
                                        <form action="<?= url('/office/email/retry/' . $log['id']) ?>" method="POST" style="display: inline;" data-no-ajax="true">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-warning" style="padding: 0.2rem 0.55rem; font-size: 0.75rem; border-radius: 4px;" title="Retry outgoing dispatch">
                                                <i class="fa-solid fa-rotate-right"></i> Retry
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Email Log Details Modal -->
<div id="emailDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(4px);">
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 14px; max-width: 800px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
        
        <!-- Modal Header -->
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #2E5077; display: flex; align-items: center; justify-content: space-between; background: #152C47;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-envelope-open-text" style="color: #38BDF8; font-size: 1.2rem;"></i>
                <h3 id="modalTitle" style="margin: 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700;">Email Details</h3>
            </div>
            <button type="button" onclick="closeEmailDetailsModal()" style="background: transparent; border: none; color: #94A3B8; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
            <!-- Meta Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: #0B192C; padding: 1rem; border-radius: 8px; border: 1px solid #2E5077;">
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Recipient</div>
                    <div id="modalRecipient" style="color: #FFFFFF; font-weight: 600; font-size: 0.9rem;">-</div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Type</div>
                    <div id="modalType" style="color: #38BDF8; font-weight: 600; font-size: 0.9rem;">-</div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Status &amp; Attempts</div>
                    <div id="modalStatus" style="font-weight: 600; font-size: 0.9rem;">-</div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Dispatched / Created</div>
                    <div id="modalCreated" style="color: #CBD5E1; font-size: 0.85rem;">-</div>
                </div>
            </div>

            <!-- Subject -->
            <div style="margin-bottom: 1.25rem;">
                <div style="color: #94A3B8; font-size: 0.78rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">Subject</div>
                <div id="modalSubject" style="background: #0B192C; border: 1px solid #2E5077; border-radius: 6px; padding: 0.65rem 0.9rem; color: #FFFFFF; font-weight: 600; font-size: 0.95rem;">-</div>
            </div>

            <!-- Error Box (if failed) -->
            <div id="modalErrorBox" style="display: none; margin-bottom: 1.25rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 8px; padding: 0.85rem 1rem;">
                <div style="color: #EF4444; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> Error Trace
                </div>
                <div id="modalErrorText" style="color: #FCA5A5; font-family: monospace; font-size: 0.8rem; line-height: 1.45; white-space: pre-wrap; word-break: break-all;"></div>
            </div>

            <!-- HTML Body Preview -->
            <div>
                <div style="color: #94A3B8; font-size: 0.78rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">Payload Preview (Sanitized)</div>
                <div style="border: 1px solid #2E5077; border-radius: 8px; background: #FFFFFF; overflow: hidden;">
                    <iframe id="modalPreviewFrame" style="width: 100%; height: 350px; border: none; display: block;" sandbox="allow-same-origin"></iframe>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #2E5077; background: #152C47; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-outline" onclick="closeEmailDetailsModal()" style="border-radius: 6px; padding: 0.4rem 1rem; color: #FFF; border-color: rgba(255,255,255,0.2);">
                Close Details
            </button>
        </div>
    </div>
</div>

<script>
function openEmailDetailsModal(logId) {
    var modal = document.getElementById('emailDetailsModal');
    if (!modal) return;
    
    modal.style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Loading Details #' + logId + '...';
    
    fetch('<?= url('/office/email/details/') ?>' + logId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || !data.log) {
            alert('Failed to load email log details.');
            closeEmailDetailsModal();
            return;
        }
        var log = data.log;
        document.getElementById('modalTitle').textContent = 'Email Log #' + log.id + ' Details';
        document.getElementById('modalRecipient').textContent = log.recipient_email + (log.recipient_name ? ' (' + log.recipient_name + ')' : '');
        document.getElementById('modalType').textContent = log.email_type;
        document.getElementById('modalSubject').textContent = log.subject;
        document.getElementById('modalCreated').textContent = log.created_at;

        var statusEl = document.getElementById('modalStatus');
        if (log.status === 'sent') {
            statusEl.innerHTML = '<span style="color: #10B981;"><i class="fa-solid fa-circle-check"></i> Sent</span>';
        } else if (log.status === 'pending') {
            statusEl.innerHTML = '<span style="color: #FBBF24;"><i class="fa-solid fa-clock"></i> Queued (Attempt ' + log.attempt_count + '/' + log.max_attempts + ')</span>';
        } else if (log.status === 'processing') {
            statusEl.innerHTML = '<span style="color: #38BDF8;"><i class="fa-solid fa-spinner fa-spin"></i> Processing</span>';
        } else {
            statusEl.innerHTML = '<span style="color: #EF4444;"><i class="fa-solid fa-circle-xmark"></i> Failed (' + log.attempt_count + '/' + log.max_attempts + ')</span>';
        }

        var errBox = document.getElementById('modalErrorBox');
        var errText = document.getElementById('modalErrorText');
        if (log.error_message) {
            errBox.style.display = 'block';
            errText.textContent = log.error_message;
        } else {
            errBox.style.display = 'none';
        }

        var frame = document.getElementById('modalPreviewFrame');
        if (frame) {
            var doc = frame.contentDocument || frame.contentWindow.document;
            doc.open();
            doc.write(log.html_body || '<div style="padding: 2rem; color: #64748B; font-family: sans-serif; text-align: center;">No stored HTML body payload for this legacy log.</div>');
            doc.close();
        }
    })
    .catch(function(err) {
        alert('Network error fetching email details.');
        closeEmailDetailsModal();
    });
}

function closeEmailDetailsModal() {
    var modal = document.getElementById('emailDetailsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close on backdrop click
window.addEventListener('click', function(e) {
    var modal = document.getElementById('emailDetailsModal');
    if (e.target === modal) {
        closeEmailDetailsModal();
    }
});
</script>
