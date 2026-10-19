<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Database Setup') ?> - <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: radial-gradient(circle at top right, #1E3E62, #0B192C); min-height: 100vh; padding: 2.5rem 1rem;">
    <div style="max-width: 720px; margin: 0 auto;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="brand-icon" style="width: 44px; height: 44px; font-size: 1.1rem;">VG</div>
                <div>
                    <h2 style="color: #FFF; margin: 0; font-size: 1.35rem;">VoyageGo Installation Wizard</h2>
                    <p style="color: #94A3B8; font-size: 0.85rem; margin: 0;">Automated Schema Migration & Diagnostic Initializer</p>
                </div>
            </div>
            <span class="badge badge-info">v<?= e(\App\Core\Config::get('app.version', '1.0.0')) ?></span>
        </div>

        <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); border-radius: 16px;">
            <?= $content ?>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; color: #64748B; font-size: 0.8rem;">
            Protected by Transaction Safety & Idempotent Migrations
        </div>
    </div>
</body>
</html>
