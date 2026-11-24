<div style="font-size: 3.5rem; margin-bottom: 1rem;">🧭</div>
<h2 style="color: #FFF; margin-bottom: 0.5rem; font-size: 1.5rem;"><?= e(__('error.page_not_found_title', 'Page Not Found (404)')) ?></h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= e(__('error.page_not_found_desc', 'The requested tour route or resource does not exist or may have been moved.')) ?>
</p>
<a href="<?= url('/') ?>" class="btn btn-primary" style="display: inline-block;"><?= e(__('error.return_to_dashboard', 'Return to Dashboard')) ?></a>
