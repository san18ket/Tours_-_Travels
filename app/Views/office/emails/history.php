<div class="page-container" style="max-width: 1350px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-clock-rotate-left" style="color: #38BDF8;"></i>
                <span>Email Audit History &amp; Outbox</span>
            </h2>
            <p style="margin: 0.35rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Complete delivery logs, technical traces, and safe idempotent retry mechanisms.
            </p>
        </div>
        <div>
            <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: #38BDF8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.85rem; padding: 0.45rem 0.95rem; border-radius: 8px;">
                <i class="fa-solid fa-database"></i> <?= number_format((int)$total) ?> Total Logs
            </span>
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

    <!-- Advanced Filter Bar -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
        <form method="GET" action="<?= url('/office/emails/history') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 1rem; align-items: flex-end;">
            <!-- Status Filter -->
            <div>
                <label style="display: block; color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 0.4rem;">Delivery Status</label>
                <select name="status" style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.55rem 0.75rem; color: #FFFFFF; font-size: 0.88rem;">
                    <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent (Delivered)</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending_retry" <?= ($filters['status'] ?? '') === 'pending_retry' ? 'selected' : '' ?>>Pending Retry</option>
                    <option value="uncertain" <?= ($filters['status'] ?? '') === 'uncertain' ? 'selected' : '' ?>>Uncertain Delivery</option>
                    <option value="skipped" <?= ($filters['status'] ?? '') === 'skipped' ? 'selected' : '' ?>>Skipped (Duplicate)</option>
                </select>
            </div>

            <!-- Email Type Filter -->
            <div>
                <label style="display: block; color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 0.4rem;">Notification Type</label>
                <select name="type" style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.55rem 0.75rem; color: #FFFFFF; font-size: 0.88rem;">
                    <option value="all" <?= ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Notification Types</option>
                    <?php if (!empty($types)): ?>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= e($t) ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Keyword Search -->
            <div>
                <label style="display: block; color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 0.4rem;">Keyword / Email</label>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Recipient, subject, ref ID..." style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.55rem 0.75rem; color: #FFFFFF; font-size: 0.88rem;">
            </div>

            <!-- From Date -->
            <div>
                <label style="display: block; color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 0.4rem;">From Date</label>
                <input type="date" name="from_date" value="<?= e($filters['from_date'] ?? '') ?>" style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.55rem 0.75rem; color: #FFFFFF; font-size: 0.88rem;">
            </div>

            <!-- To Date -->
            <div>
                <label style="display: block; color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 0.4rem;">To Date</label>
                <input type="date" name="to_date" value="<?= e($filters['to_date'] ?? '') ?>" style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.55rem 0.75rem; color: #FFFFFF; font-size: 0.88rem;">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn" style="background: #10B981; color: #FFFFFF; border: none; border-radius: 8px; padding: 0.55rem 1.15rem; font-weight: 600; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="<?= url('/office/emails/history') ?>" class="btn" style="background: #335384; color: #CBD5E1; border: none; border-radius: 8px; padding: 0.55rem 0.85rem; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center;" title="Reset Filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table Card -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left;">
                <thead>
                    <tr style="background: rgba(11, 25, 44, 0.7); color: #94A3B8; border-bottom: 1px solid #2E5077; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 0.9rem 1.15rem;">ID</th>
                        <th style="padding: 0.9rem 1.15rem;">Timestamp</th>
                        <th style="padding: 0.9rem 1.15rem;">Recipient</th>
                        <th style="padding: 0.9rem 1.15rem;">Type</th>
                        <th style="padding: 0.9rem 1.15rem;">Subject</th>
                        <th style="padding: 0.9rem 1.15rem;">Entity Ref</th>
                        <th style="padding: 0.9rem 1.15rem;">Status</th>
                        <th style="padding: 0.9rem 1.15rem;">Attempts</th>
                        <th style="padding: 0.9rem 1.15rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" style="padding: 3rem 1.15rem; text-align: center; color: #94A3B8;">
                                <i class="fa-solid fa-envelope-circle-check" style="font-size: 2.2rem; color: #475569; display: block; margin-bottom: 0.5rem;"></i>
                                No email logs match the selected filter criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
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
                                    <?= date('Y-m-d H:i', strtotime($log['created_at'])) ?>
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
                                <td style="padding: 0.85rem 1.15rem; color: #E2E8F0; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= e($log['subject'] ?? 'No Subject') ?>
                                </td>
                                <td style="padding: 0.85rem 1.15rem; color: #CBD5E1; font-size: 0.82rem;">
                                    <?php if (!empty($log['reference_type']) && !empty($log['reference_id'])): ?>
                                        <span style="color: #D6B77A;"><?= e($log['reference_type']) ?> #<?= (int)$log['reference_id'] ?></span>
                                    <?php else: ?>
                                        <span style="color: #64748B;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.85rem 1.15rem;">
                                    <span style="display: inline-block; background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 6px; text-transform: uppercase;">
                                        <?= e($status) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.85rem 1.15rem; color: #CBD5E1; text-align: center;">
                                    <?= (int)($log['attempt_count'] ?? 1) ?> / <?= (int)($log['max_attempts'] ?? 5) ?>
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

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <?php
                $queryParams = array_filter($filters, fn($v) => $v !== '' && $v !== 'all');
                $buildUrl = function($p) use ($queryParams) {
                    $qp = array_merge($queryParams, ['page' => $p]);
                    return url('/office/emails/history') . '?' . http_build_query($qp);
                };
            ?>
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #2E5077; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: rgba(11, 25, 44, 0.4);">
                <div style="color: #94A3B8; font-size: 0.85rem;">
                    Showing page <strong style="color: #FFFFFF;"><?= (int)$page ?></strong> of <strong style="color: #FFFFFF;"><?= (int)$totalPages ?></strong> (<?= number_format((int)$total) ?> total records)
                </div>
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <?php if ($page > 1): ?>
                        <a href="<?= $buildUrl(1) ?>" style="padding: 0.4rem 0.75rem; background: #0B192C; border: 1px solid #335384; border-radius: 6px; color: #CBD5E1; font-size: 0.85rem; text-decoration: none;" title="First Page">&laquo;</a>
                        <a href="<?= $buildUrl($page - 1) ?>" style="padding: 0.4rem 0.75rem; background: #0B192C; border: 1px solid #335384; border-radius: 6px; color: #CBD5E1; font-size: 0.85rem; text-decoration: none;">&lsaquo; Prev</a>
                    <?php endif; ?>

                    <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($p = $startPage; $p <= $endPage; $p++):
                    ?>
                        <a href="<?= $buildUrl($p) ?>" style="padding: 0.4rem 0.75rem; background: <?= $p === $page ? '#10B981' : '#0B192C' ?>; border: 1px solid <?= $p === $page ? '#10B981' : '#335384' ?>; border-radius: 6px; color: #FFFFFF; font-size: 0.85rem; text-decoration: none; font-weight: <?= $p === $page ? '700' : '400' ?>;">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $buildUrl($page + 1) ?>" style="padding: 0.4rem 0.75rem; background: #0B192C; border: 1px solid #335384; border-radius: 6px; color: #CBD5E1; font-size: 0.85rem; text-decoration: none;">Next &rsaquo;</a>
                        <a href="<?= $buildUrl($totalPages) ?>" style="padding: 0.4rem 0.75rem; background: #0B192C; border: 1px solid #335384; border-radius: 6px; color: #CBD5E1; font-size: 0.85rem; text-decoration: none;" title="Last Page">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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

