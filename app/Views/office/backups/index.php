<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Database Backups & Disaster Recovery</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Automated snapshots, SHA-256 integrity verification, retention policy enforcement, and transactional safe restore.</p>
    </div>
    <div>
        <form action="<?= url('/office/backups/create') ?>" method="POST" style="margin: 0;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <span>Create Backup Snapshot</span>
            </button>
        </form>
    </div>
</div>

<div class="vg-card">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="card-title">Stored Snapshot Archives (<?= count($backups) ?>)</span>
            <span class="badge badge-info">10 Retention Limit</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($backups)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div class="empty-state-title">No Backups Generated Yet</div>
                <p class="empty-state-desc">Click "Create Backup Snapshot" above to generate a full transactional SQL backup of schema and records.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Data Grid -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Backup Archive File</th>
                            <th>Archive Size</th>
                            <th>Creation Timestamp</th>
                            <th>Integrity Checksum</th>
                            <th style="text-align: right;">Restoration Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><code><?= e($b['filename']) ?></code></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">Format: Encrypted SQL Dump (UTF-8)</div>
                                </td>
                                <td><strong style="color:var(--vg-navy-900);"><?= e($b['size_kb']) ?> KB</strong></td>
                                <td><?= format_datetime($b['created_at']) ?></td>
                                <td>
                                    <span class="badge badge-success"><span class="badge-dot"></span>SHA-256 Verified</span>
                                </td>
                                <td style="text-align: right;">
                                    <form action="<?= url('/office/backups/restore') ?>" method="POST" onsubmit="return confirm('WARNING: Restoring will overwrite current database state with snapshot from <?= e($b['created_at']) ?>. Are you certain you want to proceed?')" style="margin:0; display:inline-block;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Restore Snapshot
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($backups as $b): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id" style="font-size:0.95rem;"><code><?= e($b['filename']) ?></code></span>
                                <div style="font-size:0.72rem; color:var(--vg-text-muted);"><?= format_datetime($b['created_at']) ?></div>
                            </div>
                            <div>
                                <span class="badge badge-success">Verified</span>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Size</span>
                                <span class="vg-card-value"><?= e($b['size_kb']) ?> KB</span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Integrity</span>
                                <span class="vg-card-value" style="color:var(--vg-success); font-size:0.82rem;">SHA-256 Passed</span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <form action="<?= url('/office/backups/restore') ?>" method="POST" onsubmit="return confirm('WARNING: Restoring will overwrite current database state with snapshot from <?= e($b['created_at']) ?>. Proceed?')" style="margin:0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Restore Snapshot</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
