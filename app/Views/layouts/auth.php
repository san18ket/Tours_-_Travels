<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Sign In') ?> - <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: radial-gradient(circle at top right, #1E3E62, #0B192C); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="width: 100%; max-width: 440px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 56px; height: 56px; margin: 0 auto 1rem; background: linear-gradient(135deg, #008DDA, #1E3E62); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #FFF; font-weight: 800; font-size: 1.4rem; box-shadow: 0 8px 24px rgba(0, 141, 218, 0.4);">VG</div>
            <h1 style="color: #FFF; font-size: 1.75rem; letter-spacing: -0.02em; margin-bottom: 0.35rem;">VoyageGo</h1>
            <p style="color: #94A3B8; font-size: 0.95rem;">Enterprise Fleet & Tour Dispatch Operations</p>
        </div>

        <?php foreach ($flashes as $type => $msg): ?>
            <div class="alert alert-<?= e($type) ?>" style="margin-bottom: 1.25rem;">
                <?= e($msg) ?>
            </div>
        <?php endforeach; ?>

        <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); border-radius: 16px;">
            <div class="card-body" style="padding: 2rem;">
                <?= $content ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; color: #64748B; font-size: 0.8rem;">
            &copy; <?= date('Y') ?> VoyageGo Systems. Secured with AES-256 and TLS.
        </div>
    </div>
</body>
</html>
