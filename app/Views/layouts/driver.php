<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Driver Console') ?> - <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#0B192C">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body style="background-color: #F1F5F9; padding-bottom: 75px;">
    <div id="offlineBanner" class="offline-banner">
        ⚠️ Offline: Device is currently disconnected. Trip actions will sync automatically once internet returns.
    </div>

    <!-- Mobile Header -->
    <header style="background: linear-gradient(135deg, #0B192C, #1E3E62); color:#FFF; padding: 1rem 1.25rem; border-bottom: 1px solid #2E5077; position: sticky; top:0; z-index: 40; box-shadow: var(--shadow-md);">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap: 0.75rem;">
                <div class="brand-icon" style="width:34px; height:34px; font-size:0.85rem;">VG</div>
                <div>
                    <div style="font-weight:800; font-size:1.05rem; letter-spacing:-0.02em; line-height:1.2;">VoyageGo Chauffeur</div>
                    <div style="font-size:0.75rem; color:#94A3B8;"><?= e($user['name'] ?? 'Driver') ?></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <span class="badge badge-info" style="font-size:0.7rem;">Chauffeur</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="padding: 1.25rem; max-width: 600px; margin: 0 auto; width:100%;">
        <?php foreach ($flashes as $type => $msg): ?>
            <div class="alert alert-<?= e($type) ?> alert-dismissible" style="box-shadow: var(--shadow-sm);">
                <span><?= e($msg) ?></span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;">&times;</button>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>

    <!-- Driver Bottom Navigation Bar -->
    <nav class="bottom-nav" style="display:block;">
        <div class="bottom-nav-inner" style="max-width: 600px; margin: 0 auto;">
            <a href="<?= url('/driver/duty') ?>" class="bottom-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/driver/duty') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>My Duty</span>
            </a>
            <a href="<?= url('/driver/history') ?>" class="bottom-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/driver/history') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Trip Log</span>
            </a>
            <form action="<?= url('/logout') ?>" method="POST" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="bottom-nav-item" style="background:none;border:none;cursor:pointer;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
