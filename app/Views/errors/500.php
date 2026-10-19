<div style="font-size: 3.5rem; margin-bottom: 1rem;">⚙️</div>
<h2 style="color: #EF4444; margin-bottom: 0.5rem; font-size: 1.5rem;">System Exception (500)</h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= e($message ?? 'An unexpected system error occurred. Stack traces are masked for security.') ?>
</p>
<?php if (!empty($ref)): ?>
    <div style="background: rgba(0, 0, 0, 0.3); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-family: monospace; font-size: 0.85rem; color: #CBD5E1;">
        Incident Reference: <strong><?= e($ref) ?></strong>
    </div>
<?php endif; ?>
<a href="<?= url('/') ?>" class="btn btn-outline" style="color: #FFF; border-color: rgba(255,255,255,0.3);">Reload Page</a>
