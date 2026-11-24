<!DOCTYPE html>
<html lang="<?= e(\App\Core\Language::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= url('/') ?>">
    <title><?= e($title ?? __('error.notice', 'Error Notice')) ?> - <?= e($app['name'] ?? 'Tours & Travels') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= @filemtime(dirname(__DIR__, 3) . '/public/assets/css/app.css') ?: time() ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
</head>
<body style="background: #0B192C; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; color: #FFF; font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, sans-serif;">
    <div style="max-width: 520px; width: 100%; background: #1E3E62; border: 1px solid #2E5077; border-radius: 16px; padding: 2.5rem; text-align: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);">
        <?= $content ?>
    </div>
</body>
</html>
