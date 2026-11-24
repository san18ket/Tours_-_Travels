<?php
/**
 * Standalone Email Details View
 * Clean office-friendly layout with message preview and collapsible technical details.
 */
$statusInfo = format_email_status_friendly($log['status'] ?? 'unknown');
$friendlyType = format_email_type_friendly($log['email_type'] ?? '');
?>
<div class="email-details-page" style="max-width: 1200px; margin: 0 auto; padding: 1.25rem 0.5rem 2rem;">

    <!-- 1. Page Header -->
    <?= render_page_header([
        'title'       => 'Email Details #' . (int)$log['id'],
        'description' => 'Sent to ' . e($log['recipient_email'] ?? ($log['to_email'] ?? '')) . ' on ' . date('d M Y, h:i A', strtotime($log['created_at'])),
        'badge'       => $statusInfo['label'],
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/office/dashboard')],
            ['label' => 'Email Center', 'url' => url('/office/emails')],
            ['label' => 'Email History', 'url' => url('/office/emails/history')],
            ['label' => '#' . (int)$log['id'], 'url' => ''],
        ],
        'actions'     => '<div style="display:flex; gap:0.65rem; align-items:center; flex-wrap:wrap;">'
            . '<a href="' . url('/office/emails/history') . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left" style="margin-right:5px;"></i> Back to History</a>'
            . (($log['status'] ?? '') !== 'sent' ? '<form action="' . url('/office/emails/retry/' . (int)$log['id']) . '" method="POST" style="margin:0;">' . csrf_field() . '<button type="submit" class="btn btn-warning btn-sm" style="background:#F59E0B; color:#FFF; border:none;"><i class="fa-solid fa-rotate-right" style="margin-right:4px;"></i> Retry Sending</button></form>' : '')
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

    <!-- 4. Email Overview Card -->
    <div class="vg-card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <span class="card-title" style="font-size: 1.05rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-info" style="color: #176B5B;"></i>
                <span>Email Information</span>
            </span>
            <span class="badge <?= e($statusInfo['badge']) ?>" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">
                <i class="<?= e($statusInfo['icon']) ?>" style="margin-right: 4px;"></i>
                <?= e($statusInfo['label']) ?>
            </span>
        </div>

        <div class="card-body">
            <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.15rem; font-size: 0.88rem;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Recipient Name</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-top: 0.2rem;">
                            <?= e($log['recipient_name'] ?: 'Customer / Guest') ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Recipient Email</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #176B5B; margin-top: 0.2rem; word-break: break-all;">
                            <?= e($log['recipient_email'] ?? ($log['to_email'] ?? '')) ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Email Purpose</div>
                        <div style="margin-top: 0.2rem;">
                            <span class="badge" style="background: rgba(23, 107, 91, 0.08); color: #16443B; border: 1px solid rgba(23, 107, 91, 0.2); font-weight: 600; font-size: 0.82rem; padding: 0.3rem 0.65rem; border-radius: 6px;">
                                <i class="fa-solid fa-tag" style="font-size: 0.7rem; margin-right: 3px;"></i>
                                <?= e($friendlyType) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Related Business Record</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #334155; margin-top: 0.2rem;">
                            <?= !empty($log['reference_type']) ? ucwords(str_replace('_', ' ', e($log['reference_type']))) . ' #' . (int)$log['reference_id'] : '—' ?>
                        </div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Subject Line</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #0F172A; margin-top: 0.2rem;">
                            <?= e($log['subject'] ?? 'No Subject') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Summary Message -->
            <div style="margin-bottom: 1.5rem; padding: 0.9rem 1.15rem; border-radius: 10px; font-size: 0.88rem; <?php if (($log['status'] ?? '') === 'sent'): ?>background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); color: #065F46;<?php else: ?>background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #991B1B;<?php endif; ?>">
                <?php if (($log['status'] ?? '') === 'sent'): ?>
                    <i class="fa-solid fa-circle-check" style="color: #10B981; margin-right: 6px;"></i>
                    <strong>Sent successfully:</strong> Delivered to the recipient on <?= date('d M Y, h:i A', strtotime($log['sent_at'] ?: $log['created_at'])) ?>.
                <?php else: ?>
                    <i class="fa-solid fa-triangle-exclamation" style="color: #EF4444; margin-right: 6px;"></i>
                    <strong>Could not send this email:</strong> <?= e($log['error_message'] ?: 'An issue occurred with the mail delivery server.') ?>
                <?php endif; ?>
            </div>

            <!-- Rendered Message Content Preview -->
            <div style="margin-bottom: 1.5rem;">
                <div style="font-size: 0.92rem; font-weight: 700; color: #0F172A; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-eye" style="color: #64748B;"></i>
                    <span>Message Preview (Sanitized)</span>
                </div>
                <iframe srcdoc="<?= htmlspecialchars($htmlBody ?: '<div style="padding:20px; font-family:sans-serif; color:#64748B;">No message body captured.</div>', ENT_QUOTES, 'UTF-8') ?>" 
                        style="width: 100%; height: 500px; background: #FFFFFF; border-radius: 12px; border: 1px solid var(--vg-pearl-200, #CBD5E1);" 
                        sandbox="allow-same-origin"></iframe>
            </div>

            <!-- Collapsible Technical Diagnostic Section -->
            <details style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 12px; padding: 0.9rem 1.15rem; font-size: 0.88rem;">
                <summary style="font-weight: 700; color: #475569; cursor: pointer; user-select: none;">
                    <i class="fa-solid fa-wrench" style="margin-right: 6px; color: #94A3B8;"></i>
                    <span>Technical Details (Diagnostic Log)</span>
                </summary>
                <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px dashed var(--vg-pearl-200, #CBD5E1); font-size: 0.85rem; color: #475569;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-bottom: 0.85rem;">
                        <div><strong>Log Record ID:</strong> #<?= (int)$log['id'] ?></div>
                        <div><strong>Send Attempts:</strong> <?= (int)($log['attempt_count'] ?? 0) ?> of <?= (int)($log['max_attempts'] ?? 5) ?></div>
                        <div><strong>First Created:</strong> <?= e($log['created_at']) ?></div>
                        <div><strong>Last Attempt:</strong> <?= e($log['last_attempt_at'] ?: '—') ?></div>
                        <div><strong>Message ID:</strong> <span style="font-family: monospace;"><?= e($log['message_id'] ?: 'None') ?></span></div>
                        <div><strong>Underlying Type:</strong> <code><?= e($log['email_type']) ?></code></div>
                    </div>

                    <?php if (!empty($log['error_message'])): ?>
                        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 0.85rem; margin-top: 0.75rem;">
                            <strong style="color: #DC2626; display: block; margin-bottom: 0.35rem;">Server Error Message:</strong>
                            <div style="color: #991B1B; font-family: monospace; white-space: pre-wrap; word-break: break-all; font-size: 0.82rem;">
                                <?= e($log['error_message']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </details>

        </div>
    </div>

</div>
