/**
 * Tours Progressive Web App Service Worker (v5.0)
 * Network-First for dynamic navigation & assets, offline fallback shell, and Web Push Notifications
 */

const CACHE_NAME = 'tours-shell-v5.0';
const STATIC_ASSETS = [
    'assets/css/app.css',
    'assets/js/app.js',
    'manifest.json',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png'
];

// Sensitive route patterns that MUST NOT be cached by Service Worker under security compliance
const PRIVATE_ROUTE_PATTERNS = [
    /\/driver\//,
    /\/office\//,
    /\/api\//,
    /\/login/,
    /\/logout/,
    /\/setup/,
    /\/private_uploads/
];

// ==============================================================================
// 1. INSTALL LIFECYCLE
// ==============================================================================
self.addEventListener('install', (event) => {
    // Force new service worker to activate immediately without waiting
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                STATIC_ASSETS.map((asset) => {
                    const assetUrl = new URL(asset, self.registration.scope).toString();
                    return cache.add(assetUrl);
                })
            );
        })
    );
});

// ==============================================================================
// 2. ACTIVATE LIFECYCLE
// ==============================================================================
self.addEventListener('activate', (event) => {
    // Only purge legacy Tours/Manaswi shells; never delete unrelated application caches
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if ((key.startsWith('tours-shell-') || key.startsWith('manaswi-shell-')) && key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// ==============================================================================
// 3. FETCH STRATEGY (Network-First with Safe Offline Fallback)
// ==============================================================================
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Security compliance check: Never cache private data, APIs, or authentication endpoints
    const isPrivate = PRIVATE_ROUTE_PATTERNS.some((pattern) => pattern.test(url.pathname));
    if (isPrivate) {
        // Direct network fetch without service worker caching
        return;
    }

    // Network-First strategy for static assets: ensures CSS/JS updates apply immediately on normal reload
    if (url.pathname.includes('/assets/')) {
        event.respondWith(
            fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            }).catch(() => {
                // Offline fallback from cache
                return caches.match(request);
            })
        );
        return;
    }

    // Network-First for Navigation Pages
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request).then((cached) => {
                if (cached) return cached;
                // Return offline fallback shell if navigating HTML
                if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Offline - Tours</title>
                            <link rel="stylesheet" href="/assets/css/app.css">
                        </head>
                        <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0B192C;color:#FFF;font-family:sans-serif;padding:20px;">
                            <div style="max-width:440px;background:#1E3E62;padding:32px;border-radius:16px;text-align:center;border:1px solid #2E5077;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📡</div>
                                <h2 style="color:#FFF;margin-bottom:0.5rem;">Connection Offline</h2>
                                <p style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;">You appear to be without internet. Once connectivity is restored, Tours will resume operations automatically.</p>
                                <button onclick="window.location.reload()" class="btn btn-primary" style="width:100%;">Retry Connection</button>
                            </div>
                        </body>
                        </html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                }
            });
        })
    );
});
