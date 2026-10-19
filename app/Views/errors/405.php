<div style="font-size: 3.5rem; margin-bottom: 1rem;">🚫</div>
<h2 style="color: #EF4444; margin-bottom: 0.5rem; font-size: 1.5rem;">Method Not Allowed (405)</h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= e($message ?? 'The HTTP request method is not supported for this resource.') ?>
</p>
<a href="<?= url('/') ?>" class="btn btn-primary" style="display: inline-block;">Return to Dashboard</a>
