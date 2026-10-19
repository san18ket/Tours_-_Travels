/**
 * VoyageGo Service Worker
 * Ensures offline shell availability while strictly protecting private customer and invoice data from cache
 */

const CACHE_NAME = 'voyagego-shell-v1';
const STATIC_ASSETS = [
    '/',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/manifest.json'
];

// Sensitive route patterns that MUST NOT be cached by Service Worker under security compliance
const PRIVATE_ROUTE_PATTERNS = [
    /\/office\/invoices/,
    /\/office\/bookings/,
    /\/private_uploads/,
    /\/api\//,
    /\/login/,
    /\/setup/
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Security compliance check: Never cache private data or APIs
    const isPrivate = PRIVATE_ROUTE_PATTERNS.some((pattern) => pattern.test(url.pathname));
    if (isPrivate) {
        // Direct network fetch without caching
        return;
    }

    // Static Asset Cache-First Strategy
    if (url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    if (response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
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
                            <title>Offline - VoyageGo</title>
                            <link rel="stylesheet" href="/assets/css/app.css">
                        </head>
                        <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0B192C;color:#FFF;font-family:sans-serif;padding:20px;">
                            <div style="max-width:440px;background:#1E3E62;padding:32px;border-radius:16px;text-align:center;border:1px solid #2E5077;">
                                <div style="font-size:3rem;margin-bottom:1rem;">📡</div>
                                <h2 style="color:#FFF;margin-bottom:0.5rem;">Connection Offline</h2>
                                <p style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;">You appear to be without internet. Once connectivity is restored, telemetry and bookings will resume syncing automatically.</p>
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
