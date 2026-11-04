<div class="page-container" style="max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <div style="margin-bottom: 0.35rem;">
                <a href="<?= url('/office/emails/history') ?>" style="color: #38BDF8; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Email History</span>
                </a>
            </div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-envelope-open-text" style="color: #38BDF8;"></i>
                <span>Email Audit Record #<?= (int)$log['id'] ?></span>
            </h2>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <?php if (($log['status'] ?? '') !== 'sent'): ?>
                <form action="<?= url('/office/emails/retry/' . (int)$log['id']) ?>" method="POST" onsubmit="return confirm('Trigger immediate retry for Email #<?= (int)$log['id'] ?>?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn" style="background: #F59E0B; color: #000000; border: none; border-radius: 8px; padding: 0.55rem 1.15rem; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>Retry Dispatched Message</span>
                    </button>
                </form>
            <?php endif; ?>
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

    <!-- Summary Details Grid -->
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
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
            <div style="font-weight: 700; color: #FFFFFF; font-size: 1rem;">
                Dispatch Metadata
            </div>
            <span style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 6px; text-transform: uppercase;">
                <?= e($status) ?>
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; font-size: 0.88rem;">
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Recipient</div>
                <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.2rem;"><?= e($log['recipient_name'] ?: 'None') ?></div>
                <div style="color: #38BDF8; font-size: 0.82rem;"><?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?></div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Notification Type</div>
                <div style="margin-top: 0.2rem;">
                    <span style="background: #0B192C; border: 1px solid #335384; color: #38BDF8; font-size: 0.78rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-family: monospace;">
                        <?= e($log['email_type']) ?>
                    </span>
                </div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Related Business Record</div>
                <div style="color: #D6B77A; font-weight: 600; margin-top: 0.2rem;">
                    <?= !empty($log['reference_type']) ? e($log['reference_type']) . ' #' . (int)$log['reference_id'] : '—' ?>
                </div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Dispatch Attempts</div>
                <div style="color: #CBD5E1; font-weight: 600; margin-top: 0.2rem;">
                    <?= (int)($log['attempt_count'] ?? 1) ?> / <?= (int)($log['max_attempts'] ?? 5) ?> attempts
                </div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Created At</div>
                <div style="color: #CBD5E1; margin-top: 0.2rem;"><?= e($log['created_at']) ?></div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Sent / Last Attempt</div>
                <div style="color: #CBD5E1; margin-top: 0.2rem;"><?= e($log['sent_at'] ?: ($log['last_attempt_at'] ?: '—')) ?></div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">SMTP Message ID</div>
                <div style="color: #94A3B8; font-family: monospace; font-size: 0.8rem; margin-top: 0.2rem; word-break: break-all;">
                    <?= e($log['message_id'] ?: 'Not issued') ?>
                </div>
            </div>
            <div>
                <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Subject Line</div>
                <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.2rem; word-break: break-word;">
                    <?= e($log['subject'] ?? 'No Subject') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Diagnostic Box (if any) -->
    <?php if (!empty($log['error_message'])): ?>
        <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
            <div style="color: #EF4444; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Technical Diagnostic Trace</span>
            </div>
            <div style="background: #0B192C; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 0.85rem 1rem; color: #FCA5A5; font-family: monospace; font-size: 0.85rem; line-height: 1.5; white-space: pre-wrap; word-break: break-all;">
                <?= e($log['error_message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rendered HTML Message Body Preview -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
        <div style="font-weight: 700; color: #FFFFFF; font-size: 1rem; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-file-code" style="color: #38BDF8;"></i>
            <span>Dispatched HTML Body Preview (Sanitized)</span>
        </div>
        <div style="background: #FFFFFF; border-radius: 8px; overflow: hidden; border: 1px solid #335384;">
            <iframe srcdoc="<?= htmlspecialchars($htmlBody ?? '<div style="padding:20px; font-family:sans-serif; color:#666;">No HTML payload captured.</div>') ?>" style="width: 100%; height: 500px; border: none; display: block;" sandbox="allow-same-origin"></iframe>
        </div>
    </div>
</div>

