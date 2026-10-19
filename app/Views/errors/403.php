<div style="font-size: 3.5rem; margin-bottom: 1rem;">🛡️</div>
<h2 style="color: #EF4444; margin-bottom: 0.5rem; font-size: 1.5rem;">Access Forbidden (403)</h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= e($message ?? 'You do not have the required administrative role or record ownership to access this resource.') ?>
</p>
<a href="<?= url('/') ?>" class="btn btn-primary" style="display: inline-block;">Return to Safe Area</a>
