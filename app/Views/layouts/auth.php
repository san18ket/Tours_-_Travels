<!DOCTYPE html>
<html lang="<?= e(\App\Core\Language::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Sign In') ?> - <?= e($app['name'] ?? 'MANASWI Tours & Travels') ?></title>
    
    <?php
        $cssVer = @filemtime(dirname(__DIR__, 2) . '/public/assets/css/app.css') ?: time();
        $jsVer  = @filemtime(dirname(__DIR__, 2) . '/public/assets/js/app.js') ?: time();
        $imgLgVer = @filemtime(dirname(__DIR__, 2) . '/public/assets/images/login-scenic-desktop.jpg') ?: time();
        $imgSmVer = @filemtime(dirname(__DIR__, 2) . '/public/assets/images/login-scenic-mobile.jpg') ?: time();
        $emblemVer = @filemtime(dirname(__DIR__, 2) . '/public/assets/images/manaswi-emblem.png') ?: time();
        $iconVer = @filemtime(dirname(__DIR__, 2) . '/public/assets/images/manaswi-icon.png') ?: time();
    ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/manaswi-icon.png') ?>?v=<?= $iconVer ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/manaswi-logo.svg') ?>?v=<?= $iconVer ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Core Application CSS with Cache Busting -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= $cssVer ?>">
    
    <script src="<?= asset('js/app.js') ?>?v=<?= $jsVer ?>" defer></script>

    <style>
        :root {
            --brand-blue-deep: #0b326d;
            --brand-blue-primary: #1d72f2;
            --brand-blue-hover: #0856e2;
            --brand-blue-light: #2583ff;
            --brand-text-dark: #0b326d;
            --brand-text-muted: #536f8d;
            --glass-bg: rgba(255, 255, 255, 0.54);
            --glass-bg-focus: rgba(255, 255, 255, 0.94);
            --glass-border: rgba(255, 255, 255, 0.82);
            --glass-shadow: 0 24px 60px rgba(10, 42, 85, 0.16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body.auth-body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            min-height: 100vh !important;
            color: var(--brand-text-dark) !important;
            overflow-x: hidden !important;
            background-color: #6eb7e8 !important;
            /* Anchored to left center so the coach bus is always fully visible and never cropped */
            background-image: url('<?= asset('images/login-scenic-desktop.jpg') ?>?v=<?= $imgLgVer ?>') !important;
            background-position: left center !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            display: flex !important;
            flex-direction: column !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Ambient Lighting Overlay - transparent over scenery and bus */
        .scenic-ambient-overlay {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 15% 15%, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0) 60%);
            pointer-events: none;
            z-index: 1;
        }

        /* Master Stage Wrapper */
        .auth-stage {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.25rem 3.5rem 1.85rem;
            max-width: 1720px;
            margin: 0 auto;
            width: 100%;
        }

        /* Top Bar: Brand, Airplane Trajectory, Language */
        .auth-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
            width: 100%;
        }

        /* Desktop Brand Identity Block (Top-Left) */
        .desktop-brand-block {
            display: flex;
            align-items: center;
            gap: 1.15rem;
            user-select: none;
        }

        .desktop-brand-emblem {
            width: 68px;
            height: 68px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(10, 45, 95, 0.16));
            transition: transform 0.3s ease;
        }
        .desktop-brand-emblem:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .desktop-brand-text {
            display: flex;
            flex-direction: column;
        }

        .desktop-brand-name {
            font-size: 1.95rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #0b326d;
            line-height: 1.1;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85);
        }

        .desktop-brand-sub {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1963b5;
            letter-spacing: 0.01em;
            line-height: 1.2;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85);
        }

        /* Top-Right: Romantic Flight Trail & Airplane */
        .top-flight-deco {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            user-select: none;
            padding-top: 0.25rem;
        }

        .flight-slogan {
            font-family: 'Caveat', cursive, sans-serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: #1a5ea6;
            line-height: 1.15;
            text-align: right;
            transform: rotate(-3deg);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9);
        }

        .flight-trail-svg {
            width: 145px;
            height: 58px;
            overflow: visible;
        }

        /* Top Bar Floating Utilities (Language Switcher) */
        .auth-topbar-tools {
            position: fixed;
            top: 1.25rem;
            right: 1.5rem;
            z-index: 100;
        }

        /* Main Center Grid: Left Hero Content vs Right Glassmorphism Card */
        .auth-center-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.85fr;
            align-items: center;
            gap: 3.5rem;
            margin: auto 0;
            width: 100%;
            padding: 1rem 0;
        }

        /* Left Hero Block - Placed cleanly in the upper sky region with NO card container so the bus beneath is 100% visible */
        .hero-left-col {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-self: flex-start;
            padding: 0 !important;
            margin-top: 0.5rem;
            max-width: 500px;
            user-select: none;
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        .hero-tagline-caps {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            color: #245794;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.95), 0 0 14px rgba(255, 255, 255, 0.85);
        }

        .hero-headline {
            font-size: 3.25rem;
            font-weight: 800;
            line-height: 1.15;
            color: #0b3675;
            letter-spacing: -0.025em;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.95), 0 0 25px rgba(255, 255, 255, 0.9);
        }

        .hero-headline span {
            display: block;
        }

        .hero-description {
            font-size: 1.05rem;
            line-height: 1.6;
            color: #1e4572;
            max-width: 460px;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.95), 0 0 18px rgba(255, 255, 255, 0.85);
        }

        /* Right Column: Glassmorphism Login Card */
        .hero-right-col {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .glass-login-card {
            width: 100%;
            max-width: 440px;
            background: var(--glass-bg);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border: 1.5px solid var(--glass-border);
            border-radius: 32px;
            padding: 2.75rem 2.35rem;
            box-shadow: var(--glass-shadow), inset 0 1px 2px rgba(255, 255, 255, 0.85);
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Bottom Trust Badges (Desktop) */
        .auth-bottombar {
            display: none !important; /* Hidden on LG desktop screens to keep the bus & scenery 100% visible and clean */
            align-items: center;
            gap: 2.25rem;
            padding: 0 !important;
            user-select: none;
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: none !important;
            border-radius: 0 !important;
            width: fit-content;
            box-shadow: none !important;
        }

        .trust-badge-item {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.92rem;
            font-weight: 700;
            color: #ffffff !important;
            text-shadow: 0 1px 4px rgba(0, 18, 48, 0.85), 0 0 10px rgba(0, 18, 48, 0.7);
        }

        .trust-badge-icon {
            font-size: 1.05rem;
            color: #ffffff !important;
            filter: drop-shadow(0 2px 4px rgba(0, 18, 48, 0.7));
        }

        .trust-divider {
            width: 1px;
            height: 18px;
            background: rgba(255, 255, 255, 0.65);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        /* Mobile Centered Brand Header (Visible ONLY on phones / tablets) */
        .mobile-brand-header {
            display: none;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 1.25rem;
            padding: 0;
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: none !important;
            box-shadow: none !important;
            position: relative;
        }

        .mobile-brand-emblem {
            width: 68px;
            height: 68px;
            object-fit: contain;
            margin-bottom: 0.4rem;
            filter: drop-shadow(0 4px 12px rgba(10, 45, 95, 0.2));
        }

        .mobile-brand-name {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #0b326d;
            line-height: 1.1;
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.95), 0 0 18px rgba(255, 255, 255, 0.9);
        }

        .mobile-brand-sub {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1963b5;
            margin-bottom: 0.35rem;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9);
        }

        .mobile-brand-tagline {
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            color: #1a4f8b;
            text-transform: uppercase;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.95);
        }

        /* =====================================================================
           RESPONSIVE BREAKPOINTS (Matching Exact Mobile sm login.jpeg)
           ===================================================================== */
        @media (max-width: 992px) {
            body.auth-body {
                background-image: url('<?= asset('images/login-scenic-mobile.jpg') ?>?v=<?= $imgSmVer ?>') !important;
                background-position: left bottom !important;
                background-size: cover !important;
                background-attachment: scroll !important;
            }

            .auth-stage {
                padding: 1.5rem 1.25rem 1rem;
                min-height: 100vh;
                justify-content: space-between;
            }

            .desktop-brand-block {
                display: none;
            }

            .hero-left-col {
                display: none;
            }

            .auth-topbar {
                margin-bottom: 0.25rem;
                justify-content: flex-end;
            }

            .mobile-brand-header {
                display: flex;
            }

            .auth-center-grid {
                grid-template-columns: 1fr;
                margin: 0 auto;
                padding: 0;
                width: 100%;
                max-width: 420px;
            }

            .hero-right-col {
                justify-content: center;
                width: 100%;
            }

            .glass-login-card {
                padding: 2.25rem 1.65rem;
                border-radius: 28px;
                max-width: 100%;
                background: rgba(255, 255, 255, 0.48);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
            }

            .auth-bottombar {
                display: flex !important; /* Show ONLY on responsive devices */
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 1.25rem;
                margin-top: 1.5rem;
                padding: 0 !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }

            .trust-badge-item {
                font-size: 0.85rem;
                color: #ffffff !important;
                text-shadow: 0 1px 4px rgba(0, 18, 48, 0.85), 0 0 10px rgba(0, 18, 48, 0.7);
            }

            .trust-divider {
                height: 14px;
                background: rgba(255, 255, 255, 0.75);
            }
        }

        @media (max-width: 480px) {
            .auth-stage {
                padding: 1.25rem 1rem 1rem;
            }

            .mobile-brand-emblem {
                width: 68px;
                height: 68px;
            }

            .mobile-brand-name {
                font-size: 1.65rem;
            }

            .mobile-brand-sub {
                font-size: 1.05rem;
            }

            .glass-login-card {
                padding: 1.85rem 1.35rem;
                border-radius: 24px;
            }

            .trust-badge-item {
                font-size: 0.76rem;
            }
            .trust-badge-icon {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="auth-body">
    <!-- Ambient Filter Scrim -->
    <div class="scenic-ambient-overlay"></div>

    <!-- Master Stage -->
    <div class="auth-stage">
        
        <!-- Top Bar Area -->
        <header class="auth-topbar">
            <!-- Desktop Brand Header (Top-Left) -->
            <div class="desktop-brand-block">
                <img src="<?= asset('images/manaswi-emblem.png') ?>?v=<?= $emblemVer ?>" alt="Manaswi Emblem" class="desktop-brand-emblem">
                <div class="desktop-brand-text">
                    <span class="desktop-brand-name">MANASWI</span>
                    <span class="desktop-brand-sub">Tours &amp; Travels</span>
                </div>
            </div>

            <!-- Top-Right: Romantic Heart Flight Trajectory & Airplane -->
            <div class="top-flight-deco">
                <div class="flight-slogan">
                    More Journeys<br>More Memories
                </div>
                <svg class="flight-trail-svg" viewBox="0 0 150 60" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- Dotted Flight Trail forming heart loop -->
                    <path d="M 10 42 C 35 44, 45 40, 60 30 C 72 20, 84 10, 96 18 C 104 25, 96 40, 86 42 C 77 44, 71 35, 83 26 C 94 18, 110 20, 126 14" 
                          stroke="#1d6ec7" 
                          stroke-width="1.8" 
                          stroke-dasharray="3.5 3.5" 
                          stroke-linecap="round"/>
                    <!-- Angled Ascending Soaring Airplane -->
                    <g transform="translate(126, 14) rotate(-15) translate(-126, -14)">
                        <path d="M 124 14 L 140 7 L 134 16 L 142 22 L 137 23 L 129 18 L 126 20 L 127 24 L 124 25 L 122 20 L 117 19 L 120 17 Z" 
                              fill="#165ea9"/>
                    </g>
                </svg>
            </div>
        </header>

        <!-- Floating Language Switcher -->
        <div class="auth-topbar-tools">
            <?php require dirname(__DIR__) . '/components/language_switcher.php'; ?>
        </div>

        <!-- Mobile View Centered Brand Header (Shown on phone / tablet) -->
        <div class="mobile-brand-header">
            <img src="<?= asset('images/manaswi-emblem.png') ?>?v=<?= $emblemVer ?>" alt="Manaswi Tours & Travels" class="mobile-brand-emblem">
            <h1 class="mobile-brand-name">MANASWI</h1>
            <div class="mobile-brand-sub">Tours &amp; Travels</div>
            <div class="mobile-brand-tagline">Explore &bull; Travel &bull; Discover</div>
        </div>

        <!-- Center Master Grid: Left Hero vs Right Glass Form -->
        <main class="auth-center-grid">
            <!-- Left Hero Content (Upper sky placement with NO covering box so the scenic bus beneath is 100% visible) -->
            <section class="hero-left-col">
                <div class="hero-tagline-caps">
                    <span>EXPLORE</span>
                    <span>&bull;</span>
                    <span>TRAVEL</span>
                    <span>&bull;</span>
                    <span>DISCOVER</span>
                </div>
                <h2 class="hero-headline">
                    <span>Your Journey</span>
                    <span>Our Priority</span>
                </h2>
                <p class="hero-description">
                    Book your next adventure with Manaswi Tours &amp; Travels and explore the world with comfort and safety.
                </p>
            </section>

            <!-- Right Glassmorphism Card Container -->
            <section class="hero-right-col">
                <div class="glass-login-card">
                    <!-- Floating Toast Notifications -->
                    <?php $activeFlashes = !empty($flashes) ? $flashes : \App\Core\Session::getAllFlashes(); ?>
                    <?php if (!empty($activeFlashes)): ?>
                    <script>
                        (function() {
                            var f = <?= json_encode($activeFlashes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                            var emit = function() {
                                if (typeof window.vgNotify === 'function') {
                                    for (var k in f) {
                                        if (f.hasOwnProperty(k)) window.vgNotify(f[k], k);
                                    }
                                } else {
                                    setTimeout(emit, 40);
                                }
                            };
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', emit);
                            } else {
                                emit();
                            }
                        })();
                    </script>
                    <?php endif; ?>

                    <?= $content ?>
                </div>
            </section>
        </main>

        <!-- Bottom Trust Badges -->
        <footer class="auth-bottombar">
            <div class="trust-badge-item">
                <i class="fa-solid fa-shield-halved trust-badge-icon"></i>
                <span>Safe &amp; Secure</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-badge-item">
                <i class="fa-solid fa-headset trust-badge-icon"></i>
                <span>24/7 Support</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-badge-item">
                <i class="fa-solid fa-location-dot trust-badge-icon"></i>
                <span>Trusted Travel Partner</span>
            </div>
        </footer>

    </div>

    <!-- Auto-Sync Service Worker Cache On Load -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let reg of registrations) {
                reg.update();
            }
        });
    }
    </script>
</body>
</html>
