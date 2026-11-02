<div class="page-container" style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-envelope-circle-check" style="color: #10B981;"></i>
                <span>SMTP Email System Diagnostics</span>
            </h2>
            <p style="margin: 0.25rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Secure administrative telemetry, live testing, and audit trail for outgoing email notifications.
            </p>
        </div>
        <div>
            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.85rem; padding: 0.4rem 0.85rem; border-radius: 8px;">
                <i class="fa-solid fa-shield-halved"></i> Admin Access Protected
            </span>
        </div>
    </div>

    <!-- Diagnostic Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
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
                <span><?= $isConfigured ? 'Credentials Active' : 'Setup Required (.env)' ?></span>
            </div>
            <div style="color: #94A3B8; font-size: 0.8rem; margin-top: 0.25rem;">
                Google App Password Protected
            </div>
        </div>

        <!-- Card 3: Sender Identity -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: #94A3B8; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Sender Identity</span>
                <i class="fa-solid fa-signature" style="color: #A78BFA;"></i>
            </div>
            <div style="color: #FFFFFF; font-size: 1.15rem; font-weight: 700;">
                <?= e($fromName) ?>
            </div>
            <div style="color: #94A3B8; font-size: 0.8rem; margin-top: 0.25rem;">
                <?= e($senderEmail) ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.75rem;">
        
        <!-- Left: Dispatch Test Message Form -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-paper-plane" style="color: #10B981;"></i>
                <span>Dispatch Test Email</span>
            </h3>
            <p style="color: #94A3B8; font-size: 0.85rem; line-height: 1.45; margin-bottom: 1.25rem;">
                Send a real-time branded diagnostic email to any verified inbox to test the Google SMTP connection, SSL handshake, and template rendering.
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

        <!-- Right: Gmail App Password Instructions -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-question" style="color: #38BDF8;"></i>
                <span>Gmail SMTP Configuration Guide</span>
            </h3>
            <p style="color: #94A3B8; font-size: 0.85rem; line-height: 1.45; margin-bottom: 1rem;">
                Google accounts require a 16-character <strong>App Password</strong> for automated SMTP transmission:
            </p>

            <ol style="margin: 0; padding-left: 1.25rem; color: #CBD5E1; font-size: 0.85rem; line-height: 1.6;">
                <li>Enable <strong>2-Step Verification</strong> on your Google Account.</li>
                <li>Visit <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #38BDF8; font-weight: 600; text-decoration: underline;">Google App Passwords</a>.</li>
                <li>Enter App name as <code>Tours &amp; Travels</code> and click <strong>Create</strong>.</li>
                <li>Copy the generated 16-character code (e.g. <code>xxxx xxxx xxxx xxxx</code>).</li>
                <li>Open your root <code>.env</code> file and configure:
                    <div style="background: #0B192C; border: 1px solid #335384; border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.4rem; font-family: monospace; font-size: 0.8rem; color: #34D399;">
                        MAIL_USERNAME=your-email@gmail.com<br>
                        MAIL_PASSWORD=your-16-char-app-password<br>
                        MAIL_FROM_NAME="Your Company Name"
                    </div>
                </li>
            </ol>
        </div>
    </div>

    <!-- Outgoing Email Audit Trail Table -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-list-check" style="color: #A78BFA;"></i>
                <span>Recent Outgoing Email Audit Logs (<?= count($logs) ?>)</span>
            </h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.location.reload()" style="color: #FFF; border-color: rgba(255,255,255,0.2); border-radius: 6px;">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh Logs
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; color: #CBD5E1;">
                <thead>
                    <tr style="border-bottom: 1px solid #335384; color: #94A3B8; text-align: left;">
                        <th style="padding: 0.75rem 0.5rem;">ID</th>
                        <th style="padding: 0.75rem 0.5rem;">Recipient</th>
                        <th style="padding: 0.75rem 0.5rem;">Type</th>
                        <th style="padding: 0.75rem 0.5rem;">Subject</th>
                        <th style="padding: 0.75rem 0.5rem;">Status</th>
                        <th style="padding: 0.75rem 0.5rem;">Dispatched At</th>
                        <th style="padding: 0.75rem 0.5rem; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #94A3B8;">
                                <i class="fa-regular fa-envelope" style="font-size: 1.75rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                No outgoing email logs recorded yet. Send a test email above to verify!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid rgba(51, 83, 132, 0.4);">
                                <td style="padding: 0.75rem 0.5rem; font-family: monospace; color: #94A3B8;">#<?= (int)$log['id'] ?></td>
                                <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: #FFFFFF;">
                                    <?= e($log['recipient_email']) ?>
                                    <?php if (!empty($log['recipient_name'])): ?>
                                        <div style="font-size: 0.75rem; color: #94A3B8;"><?= e($log['recipient_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: rgba(56, 189, 248, 0.15); color: #38BDF8;">
                                        <?= e($log['email_type']) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($log['subject']) ?>
                                    <?php if (!empty($log['error_message'])): ?>
                                        <div style="font-size: 0.75rem; color: #EF4444; margin-top: 0.2rem;"><?= e($log['error_message']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #10B981; font-weight: 600;">
                                            <i class="fa-solid fa-circle-check"></i> Sent
                                        </span>
                                    <?php else: ?>
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #EF4444; font-weight: 600;">
                                            <i class="fa-solid fa-circle-xmark"></i> Failed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; color: #94A3B8; font-size: 0.8rem;">
                                    <?= format_datetime($log['created_at']) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center;">
                                    <?php if ($log['status'] !== 'sent'): ?>
                                        <form action="<?= url('/office/email/retry/' . $log['id']) ?>" method="POST" style="display: inline;" data-no-ajax="true">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-warning" style="padding: 0.2rem 0.55rem; font-size: 0.75rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.3rem;" title="Retry failed email">
                                                <i class="fa-solid fa-rotate-right"></i> Retry
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #64748B; font-size: 0.75rem;">—</span>
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
