/**
 * MANASWI Tours & Travels Service Worker (v4.4)
 * Network-First for dynamic navigation & assets to prevent stale caching issues on normal refresh
 */

const CACHE_NAME = 'manaswi-shell-v4.4';
const STATIC_ASSETS = [
    'assets/css/app.css',
    'assets/js/app.js',
    'manifest.json'
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

self.addEventListener('activate', (event) => {
    // Purge all legacy caches immediately
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

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

    // Network-First strategy for static assets: ensures CSS/JS updates apply immediately on normal reload!
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
                            <title>Offline - MANASWI Tours & Travels</title>
                            <link rel="stylesheet" href="/assets/css/app.css">
                        </head>
                        <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0B192C;color:#FFF;font-family:sans-serif;padding:20px;">
                            <div style="max-width:440px;background:#1E3E62;padding:32px;border-radius:16px;text-align:center;border:1px solid #2E5077;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📡</div>
                                <h2 style="color:#FFF;margin-bottom:0.5rem;">Connection Offline</h2>
                                <p style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;">You appear to be without internet. Once connectivity is restored, operations will resume syncing automatically.</p>
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
