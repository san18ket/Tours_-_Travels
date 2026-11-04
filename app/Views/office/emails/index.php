<div class="page-container" style="max-width: 1300px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-envelope-circle-check" style="color: #10B981;"></i>
                <span>Email Management &amp; Delivery Center</span>
            </h2>
            <p style="margin: 0.35rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Centralized transactional SMTP engine, delivery audit trail, and automated branding.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="testSmtpConnection(this)" class="btn" style="background: #1E3E62; color: #38BDF8; border: 1px solid #335384; padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
                <i class="fa-solid fa-bolt"></i>
                <span id="smtpTestBtnText">Test SMTP Socket</span>
            </button>
            <a href="<?= url('/office/emails/preview') ?>" class="btn" style="background: rgba(214, 183, 122, 0.15); color: #D6B77A; border: 1px solid rgba(214, 183, 122, 0.35); padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-eye"></i>
                <span>Preview Templates</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- Flash Notifications -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #10B981; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-circle-check"></i>
            <span style="font-size: 0.9rem;"><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('danger')): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #EF4444; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span style="font-size: 0.9rem;"><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('info')): ?>
        <div style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #38BDF8; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-circle-info"></i>
            <span style="font-size: 0.9rem;"><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Diagnostic Cards Grid (Executive Metrics) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <!-- Card 1: Total Sent -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Delivered Successfully</span>
                <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.1rem;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.75rem; font-weight: 800; line-height: 1.2;">
                <?= number_format((int)($stats['sent'] ?? 0)) ?>
            </div>
            <div style="color: #10B981; font-size: 0.82rem; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span><?= number_format((float)($stats['success_rate'] ?? 0), 1) ?>% delivery rate</span>
            </div>
        </div>

        <!-- Card 2: Failed Messages -->
        <div style="background: #1E3E62; border: 1px solid <?= ($stats['failed'] ?? 0) > 0 ? 'rgba(239, 68, 68, 0.4)' : '#2E5077' ?>; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Delivery Failures</span>
                <i class="fa-solid fa-circle-xmark" style="color: #EF4444; font-size: 1.1rem;"></i>
            </div>
            <div style="color: <?= ($stats['failed'] ?? 0) > 0 ? '#EF4444' : '#FFFFFF' ?>; font-size: 1.75rem; font-weight: 800; line-height: 1.2;">
                <?= number_format((int)($stats['failed'] ?? 0)) ?>
            </div>
            <div style="color: #94A3B8; font-size: 0.82rem; margin-top: 0.35rem;">
                <?php if (($stats['failed'] ?? 0) > 0): ?>
                    <a href="<?= url('/office/emails/history?status=failed') ?>" style="color: #F87171; text-decoration: none; font-weight: 600;">View failed outbox &rarr;</a>
                <?php else: ?>
                    <span style="color: #10B981;">No active SMTP failures</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 3: Pending / Retry -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Awaiting Retry</span>
                <i class="fa-solid fa-rotate" style="color: #F59E0B; font-size: 1.1rem;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.75rem; font-weight: 800; line-height: 1.2;">
                <?= number_format((int)($stats['pending_retry'] ?? 0)) ?>
            </div>
            <div style="color: #FBBF24; font-size: 0.82rem; margin-top: 0.35rem;">
                Direct transactional fallback
            </div>
        </div>

        <!-- Card 4: Today's Volume -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Today's Activity</span>
                <i class="fa-solid fa-calendar-day" style="color: #38BDF8; font-size: 1.1rem;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.75rem; font-weight: 800; line-height: 1.2;">
                <?= number_format((int)($stats['today_total'] ?? 0)) ?>
            </div>
            <div style="color: #94A3B8; font-size: 0.82rem; margin-top: 0.35rem;">
                <span style="color: #10B981; font-weight: 600;"><?= (int)($stats['today_sent'] ?? 0) ?> sent</span>
                <?php if (($stats['today_failed'] ?? 0) > 0): ?>
                    • <span style="color: #EF4444; font-weight: 600;"><?= (int)($stats['today_failed'] ?? 0) ?> failed</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SMTP Configuration & Fast Diagnostics Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.75rem;">
        <!-- Left: Active SMTP Engine Status -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
                <div style="font-weight: 700; color: #FFFFFF; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-server" style="color: #38BDF8;"></i>
                    <span>Active SMTP Transport</span>
                </div>
                <span class="badge" style="background: <?= !empty($smtpConfig['has_credentials']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= !empty($smtpConfig['has_credentials']) ? '#10B981' : '#EF4444' ?>; border: 1px solid <?= !empty($smtpConfig['has_credentials']) ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' ?>; font-size: 0.8rem; padding: 0.25rem 0.65rem; border-radius: 6px;">
                    <i class="fa-solid <?= !empty($smtpConfig['has_credentials']) ? 'fa-check' : 'fa-xmark' ?>"></i>
                    <?= !empty($smtpConfig['has_credentials']) ? 'Configured' : 'Missing Credentials' ?>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; font-size: 0.88rem;">
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Relay Host</div>
                    <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.2rem;"><?= e($smtpConfig['host'] ?? 'Not set') ?></div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Port &amp; Security</div>
                    <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.2rem;"><?= (int)($smtpConfig['port'] ?? 587) ?> (<?= strtoupper(e($smtpConfig['encryption'] ?? 'tls')) ?>)</div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">From Identity</div>
                    <div style="color: #CBD5E1; margin-top: 0.2rem; word-break: break-all;"><?= e($smtpConfig['from_name'] ?? '') ?> &lt;<?= e($smtpConfig['from_address'] ?? '') ?>&gt;</div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">SMTP User</div>
                    <div style="color: #CBD5E1; margin-top: 0.2rem; word-break: break-all;"><?= e($smtpConfig['username'] ?? 'None') ?></div>
                </div>
            </div>

            <div id="smtpSocketResult" style="display: none; margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem;"></div>
        </div>

        <!-- Right: Fast Diagnostic Dispatch -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="font-weight: 700; color: #FFFFFF; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-paper-plane" style="color: #10B981;"></i>
                <span>Send Real Diagnostic Email</span>
            </div>

            <form action="<?= url('/office/emails/send-test') ?>" method="POST" id="quickTestForm" onsubmit="handleSendTest(event, this)">
                <?= csrf_field() ?>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; color: #CBD5E1; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.35rem;">Destination Recipient Email</label>
                    <input type="email" name="recipient_email" required placeholder="e.g. your-work-email@gmail.com" style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.65rem 0.85rem; color: #FFFFFF; font-size: 0.9rem;" value="<?= e($user['email'] ?? '') ?>">
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <span style="font-size: 0.78rem; color: #94A3B8;">
                        Dispatches immediate branded test email.
                    </span>
                    <button type="submit" id="sendTestSubmitBtn" class="btn" style="background: #10B981; color: #FFFFFF; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Dispatch Test</span>
                    </button>
                </div>
            </form>
            <div id="quickTestResult" style="display: none; margin-top: 0.85rem; padding: 0.65rem 0.9rem; border-radius: 8px; font-size: 0.85rem;"></div>
        </div>
    </div>

    <!-- Recent Email Dispatches Table -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; overflow: hidden; margin-bottom: 2rem;">
        <div style="padding: 1.15rem 1.25rem; border-bottom: 1px solid #2E5077; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-clock-rotate-left" style="color: #38BDF8;"></i>
                <h3 style="margin: 0; color: #FFFFFF; font-size: 1.1rem; font-weight: 700;">Recent Transactional Dispatches</h3>
            </div>
            <a href="<?= url('/office/emails/history') ?>" style="color: #38BDF8; font-size: 0.85rem; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;">
                <span>View Full Audit History</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left;">
                <thead>
                    <tr style="background: rgba(11, 25, 44, 0.6); color: #94A3B8; border-bottom: 1px solid #2E5077; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 0.85rem 1.15rem;">ID</th>
                        <th style="padding: 0.85rem 1.15rem;">Timestamp</th>
                        <th style="padding: 0.85rem 1.15rem;">Recipient</th>
                        <th style="padding: 0.85rem 1.15rem;">Type</th>
                        <th style="padding: 0.85rem 1.15rem;">Subject</th>
                        <th style="padding: 0.85rem 1.15rem;">Status</th>
                        <th style="padding: 0.85rem 1.15rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentLogs)): ?>
                        <tr>
                            <td colspan="7" style="padding: 2.5rem 1.15rem; text-align: center; color: #94A3B8;">
                                <i class="fa-solid fa-inbox" style="font-size: 2rem; color: #475569; display: block; margin-bottom: 0.5rem;"></i>
                                No recent outgoing emails recorded in the audit log.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <?php
                                $status = $log['status'] ?? 'unknown';
                                $statusBg = '#475569';
                                $statusColor = '#FFFFFF';
                                if ($status === 'sent') {
                                    $statusBg = 'rgba(16, 185, 129, 0.15)';
                                    $statusColor = '#10B981';
                                } elseif ($status === 'failed') {
                                    $statusBg = 'rgba(239, 68, 68, 0.15)';
                                    $statusColor = '#EF4444';
                                } elseif ($status === 'pending_retry' || $status === 'pending') {
                                    $statusBg = 'rgba(245, 158, 11, 0.15)';
                                    $statusColor = '#F59E0B';
                                } elseif ($status === 'uncertain') {
                                    $statusBg = 'rgba(168, 85, 247, 0.15)';
                                    $statusColor = '#C084FC';
                                } elseif ($status === 'skipped') {
                                    $statusBg = 'rgba(148, 163, 184, 0.15)';
                                    $statusColor = '#94A3B8';
                                }
                            ?>
                            <tr style="border-bottom: 1px solid rgba(46, 80, 119, 0.5); transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.85rem 1.15rem; color: #64748B; font-weight: 600;">
                                    #<?= (int)$log['id'] ?>
                                </td>
                                <td style="padding: 0.85rem 1.15rem; color: #CBD5E1; white-space: nowrap;">
                                    <?= date('M d, H:i', strtotime($log['created_at'])) ?>
                                </td>
                                <td style="padding: 0.85rem 1.15rem;">
                                    <div style="font-weight: 600; color: #FFFFFF;"><?= e($log['recipient_name'] ?: 'Recipient') ?></div>
                                    <div style="color: #94A3B8; font-size: 0.8rem;"><?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?></div>
                                </td>
                                <td style="padding: 0.85rem 1.15rem;">
                                    <span style="display: inline-block; background: #0B192C; border: 1px solid #335384; color: #38BDF8; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-family: monospace;">
                                        <?= e($log['email_type']) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.85rem 1.15rem; color: #E2E8F0; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= e($log['subject'] ?? 'No Subject') ?>
                                </td>
                                <td style="padding: 0.85rem 1.15rem;">
                                    <span style="display: inline-block; background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 6px; text-transform: uppercase;">
                                        <?= e($status) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.85rem 1.15rem; text-align: right; white-space: nowrap;">
                                    <button type="button" onclick="viewEmailDetails(<?= (int)$log['id'] ?>)" style="background: transparent; border: 1px solid #335384; color: #38BDF8; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer; margin-right: 0.35rem;" title="Inspect Payload">
                                        <i class="fa-solid fa-eye"></i> Details
                                    </button>
                                    <?php if ($status !== 'sent'): ?>
                                        <form action="<?= url('/office/emails/retry/' . (int)$log['id']) ?>" method="POST" style="display: inline;" onsubmit="return confirm('Attempt manual safe SMTP retry for Email #<?= (int)$log['id'] ?>?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #F59E0B; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer;" title="Retry Delivery">
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

<!-- Modal: Email Record Details Inspection -->
<div id="emailDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; width: 100%; max-width: 780px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #2E5077; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; color: #FFFFFF; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-envelope-open-text" style="color: #38BDF8;"></i>
                <span id="modalEmailTitle">Email Record Details</span>
            </div>
            <button type="button" onclick="closeDetailsModal()" style="background: transparent; border: none; color: #94A3B8; font-size: 1.4rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div id="modalLoading" style="padding: 2.5rem; text-align: center; color: #94A3B8;">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 1.75rem; color: #38BDF8; margin-bottom: 0.5rem; display: block;"></i>
            Loading audit record...
        </div>

        <div id="modalContent" style="display: none; padding: 1.25rem; overflow-y: auto; flex: 1;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #0B192C; padding: 1rem; border-radius: 8px; border: 1px solid #2E5077; margin-bottom: 1rem; font-size: 0.85rem;">
                <div><strong style="color: #94A3B8;">Recipient:</strong> <span id="modalRecipient" style="color: #FFFFFF;"></span></div>
                <div><strong style="color: #94A3B8;">Type:</strong> <span id="modalType" style="color: #38BDF8; font-family: monospace;"></span></div>
                <div><strong style="color: #94A3B8;">Subject:</strong> <span id="modalSubject" style="color: #FFFFFF;"></span></div>
                <div><strong style="color: #94A3B8;">Status:</strong> <span id="modalStatusBadge"></span></div>
                <div><strong style="color: #94A3B8;">Attempts:</strong> <span id="modalAttempts" style="color: #FFFFFF;"></span></div>
                <div><strong style="color: #94A3B8;">Created:</strong> <span id="modalCreated" style="color: #CBD5E1;"></span></div>
                <div><strong style="color: #94A3B8;">Sent At:</strong> <span id="modalSentAt" style="color: #CBD5E1;"></span></div>
                <div><strong style="color: #94A3B8;">Reference:</strong> <span id="modalRef" style="color: #CBD5E1;"></span></div>
            </div>

            <div id="modalErrorContainer" style="display: none; margin-bottom: 1rem; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); padding: 0.85rem 1rem; border-radius: 8px;">
                <div style="color: #EF4444; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.35rem;">Technical Diagnostic Message:</div>
                <div id="modalErrorMessage" style="color: #FCA5A5; font-size: 0.82rem; font-family: monospace; white-space: pre-wrap; word-break: break-all;"></div>
            </div>

            <div>
                <strong style="color: #94A3B8; font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Rendered Message Body Preview (Sanitized):</strong>
                <iframe id="modalBodyFrame" style="width: 100%; height: 260px; background: #FFFFFF; border-radius: 8px; border: 1px solid #335384;" sandbox="allow-same-origin"></iframe>
            </div>
        </div>

        <div style="padding: 0.85rem 1.25rem; border-top: 1px solid #2E5077; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; background: #0B192C;">
            <div id="modalRetryContainer" style="display: inline;"></div>
            <button type="button" onclick="closeDetailsModal()" style="background: #335384; color: #FFFFFF; border: none; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.85rem; cursor: pointer;">Close</button>
        </div>
    </div>
</div>

<script>
function testSmtpConnection(btn) {
    const textEl = document.getElementById('smtpTestBtnText');
    const resultEl = document.getElementById('smtpSocketResult');
    const originalText = textEl.textContent;

    btn.disabled = true;
    textEl.textContent = 'Testing Socket...';
    resultEl.style.display = 'none';

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
        resultEl.style.display = 'block';

        if (data.connected) {
            resultEl.style.background = 'rgba(16, 185, 129, 0.15)';
            resultEl.style.border = '1px solid rgba(16, 185, 129, 0.4)';
            resultEl.style.color = '#10B981';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (data.message || 'SMTP Socket connection succeeded!') + (data.latency_ms ? ' (' + data.latency_ms + 'ms)' : '');
        } else {
            resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
            resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
            resultEl.style.color = '#EF4444';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Connection Failed: ' + (data.error || 'Could not establish connection to SMTP host.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        textEl.textContent = originalText;
        resultEl.style.display = 'block';
        resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
        resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
        resultEl.style.color = '#EF4444';
        resultEl.textContent = 'Network error testing SMTP connection: ' + err.message;
    });
}

function handleSendTest(e, form) {
    e.preventDefault();
    const btn = document.getElementById('sendTestSubmitBtn');
    const resultEl = document.getElementById('quickTestResult');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Dispatched...';
    resultEl.style.display = 'none';

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
        resultEl.style.display = 'block';

        if (data.success) {
            resultEl.style.background = 'rgba(16, 185, 129, 0.15)';
            resultEl.style.border = '1px solid rgba(16, 185, 129, 0.4)';
            resultEl.style.color = '#10B981';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (data.message || 'Test email dispatched successfully!');
        } else {
            resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
            resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
            resultEl.style.color = '#EF4444';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + (data.message || 'Dispatch failed.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultEl.style.display = 'block';
        resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
        resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
        resultEl.style.color = '#EF4444';
        resultEl.textContent = 'Error: ' + err.message;
    });
}

function viewEmailDetails(id) {
    const modal = document.getElementById('emailDetailsModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const retryContainer = document.getElementById('modalRetryContainer');

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';
    retryContainer.innerHTML = '';
    document.getElementById('modalEmailTitle').textContent = 'Email Record #' + id;

    fetch('<?= url('/office/emails/details/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        loading.style.display = 'none';
        if (!data.success || !data.log) {
            alert(data.message || 'Failed to load details.');
            closeDetailsModal();
            return;
        }

        const log = data.log;
        content.style.display = 'block';
        document.getElementById('modalRecipient').textContent = (log.recipient_name ? log.recipient_name + ' ' : '') + '<' + log.recipient_email + '>';
        document.getElementById('modalType').textContent = log.email_type;
        document.getElementById('modalSubject').textContent = log.subject;
        document.getElementById('modalAttempts').textContent = log.attempt_count + ' / ' + log.max_attempts;
        document.getElementById('modalCreated').textContent = log.created_at || '—';
        document.getElementById('modalSentAt').textContent = log.sent_at || '—';
        document.getElementById('modalRef').textContent = (log.reference_type ? log.reference_type + ' #' + log.reference_id : 'None');

        const badge = document.getElementById('modalStatusBadge');
        badge.textContent = (log.status || 'unknown').toUpperCase();
        badge.style.padding = '0.2rem 0.5rem';
        badge.style.borderRadius = '4px';
        badge.style.fontSize = '0.75rem';
        badge.style.fontWeight = '700';

        if (log.status === 'sent') {
            badge.style.background = 'rgba(16, 185, 129, 0.2)';
            badge.style.color = '#10B981';
        } else if (log.status === 'failed') {
            badge.style.background = 'rgba(239, 68, 68, 0.2)';
            badge.style.color = '#EF4444';
        } else {
            badge.style.background = 'rgba(245, 158, 11, 0.2)';
            badge.style.color = '#F59E0B';
        }

        const errBox = document.getElementById('modalErrorContainer');
        const errMsg = document.getElementById('modalErrorMessage');
        if (log.error_message) {
            errBox.style.display = 'block';
            errMsg.textContent = log.error_message;
        } else {
            errBox.style.display = 'none';
        }

        const frame = document.getElementById('modalBodyFrame');
        frame.srcdoc = log.html_body || '<div style="padding:20px; font-family:sans-serif; color:#666;">No HTML payload captured.</div>';

        if (log.status !== 'sent') {
            retryContainer.innerHTML = '<form action="<?= url('/office/emails/retry/') ?>' + log.id + '" method="POST" style="display:inline;" onsubmit="return confirm(\'Trigger immediate retry for Email #' + log.id + '?\')"><?= csrf_field() ?><button type="submit" style="background:#F59E0B; color:#000000; border:none; border-radius:6px; padding:0.5rem 1rem; font-weight:700; font-size:0.85rem; cursor:pointer;"><i class="fa-solid fa-rotate-right"></i> Retry Dispatched Message</button></form>';
        }
    })
    .catch(err => {
        loading.style.display = 'none';
        alert('Network error loading details: ' + err.message);
        closeDetailsModal();
    });
}

function closeDetailsModal() {
    document.getElementById('emailDetailsModal').style.display = 'none';
}
</script>

