<div style="font-size: 3.5rem; margin-bottom: 1rem;">🗄️</div>
<h2 style="color: #F59E0B; margin-bottom: 0.5rem; font-size: 1.5rem;">Database Connectivity Alert (503)</h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= e($message ?? 'Database connection is temporarily unavailable. Automated retry in progress.') ?>
</p>
<?php if (!empty($ref)): ?>
    <div style="background: rgba(0, 0, 0, 0.3); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-family: monospace; font-size: 0.85rem; color: #CBD5E1;">
        Incident Code: <strong><?= e($ref) ?></strong>
    </div>
<?php endif; ?>
<button onclick="window.location.reload()" class="btn btn-primary" style="display: inline-block;">Check Again</button>
