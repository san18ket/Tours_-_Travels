<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Error Notice') ?> - <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
</head>
<body style="background: #0B192C; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; color: #FFF;">
    <div style="max-width: 520px; width: 100%; background: #1E3E62; border: 1px solid #2E5077; border-radius: 16px; padding: 2.5rem; text-align: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);">
        <?= $content ?>
    </div>
</body>
</html>
