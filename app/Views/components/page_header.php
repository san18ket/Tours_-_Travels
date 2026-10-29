<?php
/**
 * Shared Page Header Component — Exact Dashboard Hero Card Design
 *
 * @var string $title Main page title
 * @var string|null $subtitle Optional subtitle / description
 * @var string|null $description Optional alias for subtitle
 * @var array|null $breadcrumbs Optional breadcrumbs [['label' => '...', 'url' => '...']]
 * @var string|null $image Image filename in assets/images/page-headers/ or full path
 * @var string|null $actions Optional raw HTML for action buttons
 * @var string|array|null $badge Optional badge
 */

$title = $title ?? 'Page Title';
$desc = $description ?? $subtitle ?? null;
$breadcrumbs = $breadcrumbs ?? [
    ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
    ['label' => $title, 'url' => ''],
];

$imageName = $image ?? 'header-bookings.jpg';
// Support both simple filename like 'header-bookings.jpg' and full paths
if (!str_contains($imageName, '/')) {
    $imageUrl = asset('images/page-headers/' . $imageName);
} else {
    $imageUrl = asset($imageName);
}

$badgeText = is_array($badge ?? null) ? ($badge['text'] ?? '') : ($badge ?? null);
?>

<div class="travel-page-header db-hero-card">
    <!-- Travel Photograph: Desktop pinned right; Mobile compact thumbnail on LEFT -->
    <div class="db-hero-image-wrap">
        <img src="<?= e($imageUrl) ?>" alt="<?= e($title) ?>" class="db-hero-bus-img">
    </div>

    <!-- Text details & Actions -->
    <div class="db-hero-left">
        <?php if (!empty($breadcrumbs)): 
            $homeUrl = !empty($breadcrumbs[0]['url']) ? $breadcrumbs[0]['url'] : url('/office/dashboard');
            $homeLabel = !empty($breadcrumbs[0]['label']) ? $breadcrumbs[0]['label'] : __('sidebar.dashboard', 'Dashboard');
        ?>
        <nav class="travel-header-breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= e($homeUrl) ?>" class="travel-header-breadcrumb-link" title="<?= e($homeLabel) ?>">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: -2px; margin-right: 2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span><?= e($homeLabel) ?></span>
            </a>
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <?php if ($i === 0) continue; ?>
                <span class="travel-header-breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= e($crumb['url']) ?>" class="travel-header-breadcrumb-link"><?= e($crumb['label']) ?></a>
                <?php else: ?>
                    <span class="travel-header-breadcrumb-current"><?= e($crumb['label']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="db-hero-greeting">
            <span><?= e($title) ?></span>
            <?php if (!empty($badgeText)): ?>
                <span class="badge badge-success" style="font-size: 0.72rem; vertical-align: middle; margin-left: 0.5rem; padding: 0.25rem 0.65rem; border-radius: 999px;">
                    <span class="pulse-indicator"></span> <?= e($badgeText) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($desc)): ?>
        <p class="db-hero-subtitle">
            <?= e($desc) ?>
        </p>
        <?php endif; ?>

        <?php if (!empty($actions)): ?>
        <div class="travel-header-actions">
            <?= $actions ?>
        </div>
        <?php endif; ?>
    </div>
</div>
