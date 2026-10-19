<div class="card-header" style="background: #7F1D1D; color: #FFF;">
    <h3 style="color: #FFF; margin: 0; font-size: 1.15rem;">Setup Interrupted</h3>
</div>
<div class="card-body" style="padding: 2rem;">
    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
        <h4 style="color: #991B1B; margin-top: 0;">Setup Failure Diagnostics</h4>
        <p style="color: #7F1D1D; font-size: 0.95rem; margin-bottom: 0.75rem;">
            <?= e($message) ?>
        </p>
        <div style="font-family: monospace; font-size: 0.85rem; color: #6B7280;">
            Incident Reference Code: <strong><?= e($incidentId) ?></strong>
        </div>
    </div>

    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
        Diagnostic details have been safely recorded to <code>storage/logs/error.log</code>. No sensitive database credentials or raw SQL traces were exposed. Existing business data was preserved through transaction rollback.
    </p>

    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="<?= url('/setup') ?>" class="btn btn-navy">Retry Setup Wizard</a>
    </div>
</div>
