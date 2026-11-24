<div class="card-header" style="background: #152943; color: #FFF; border-bottom: 1px solid #2E5077; display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.75rem;">
    <h3 style="color: #FFF; margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 0.6rem;">
        <span style="font-size: 1.35rem;"><?= e($icon ?? '⚙️') ?></span>
        <span><?= e($title ?? 'Database Status') ?></span>
    </h3>
    <span class="badge" style="background: #F59E0B; color: #1E293B; font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.65rem; border-radius: 4px; letter-spacing: 0.03em;">Configuration Notice</span>
</div>
<div class="card-body" style="padding: 2.25rem;">
    <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h4 style="color: #92400E; margin-top: 0; margin-bottom: 0.6rem; font-size: 1.1rem; font-weight: 700;">
            <?= e($userMessage) ?>
        </h4>
        <?php if (!empty($userDescription)): ?>
            <p style="color: #B45309; font-size: 0.94rem; margin-bottom: 0.85rem; line-height: 1.55;">
                <?= e($userDescription) ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($incidentId)): ?>
            <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.82rem; color: #78350F; background: rgba(245, 158, 11, 0.15); padding: 0.4rem 0.65rem; border-radius: 6px; display: inline-block;">
                Incident Reference: <strong><?= e($incidentId) ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <p style="color: var(--text-secondary, #64748B); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.75rem;">
        Technical details have been securely recorded to the application error log. Sensitive database credentials, server paths, and raw queries are strictly masked for security.
    </p>

    <div style="display: flex; justify-content: flex-end; gap: 1rem; align-items: center;">
        <a href="<?= url('/') ?>" class="btn btn-navy" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 6px; font-weight: 600; text-decoration: none;">
            <span>🔄</span> Retry Connection
        </a>
    </div>
</div>
