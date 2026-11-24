<div style="font-size: 3.5rem; margin-bottom: 1rem;">⏳</div>
<h2 style="color: #F59E0B; margin-bottom: 0.5rem; font-size: 1.5rem;"><?= e(__('error.rate_limit_title', 'Rate Limit Exceeded (429)')) ?></h2>
<p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 1.5rem;">
    <?= __('error.rate_limit_desc', ['seconds' => '<strong>' . (int)($seconds ?? 60) . '</strong>']) ?>
</p>
<button onclick="window.location.reload()" class="btn btn-outline" style="color: #FFF; border-color: rgba(255,255,255,0.3);"><?= e(__('error.retry', 'Retry')) ?></button>
