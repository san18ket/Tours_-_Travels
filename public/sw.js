/**
 * Tours & Travels Progressive Web App Service Worker (v6.0)
 * Smart Browser Caching Architecture: Versioned Static Shell & Assets,
 * Stale-While-Revalidate for Static Assets, Safe Network-First Navigation,
 * Resilient Offline Fallback, and Zero Sensitive Route Interception.
 */

const CACHE_VERSION = 'v6.0';
const SHELL_CACHE = `tours-shell-${CACHE_VERSION}`;
const STATIC_CACHE = `tours-static-${CACHE_VERSION}`;

// Pre-cached core static assets for instant application startup
const CORE_STATIC_ASSETS = [
    'assets/css/app.css',
    'assets/css/tours-pwa.css',
    'assets/css/luxury-erp-system.css',
    'assets/css/responsive.css',
    'assets/js/tours-cache.js',
    'assets/js/app.js',
    'assets/js/tours-pwa.js',
    'assets/js/table-cards.js',
    'assets/js/table-dropdowns.js',
    'assets/js/delete-confirmations.js',
    'manifest.json',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png',
    'assets/icons/icon.svg',
    'assets/images/brand-blue-bus.svg',
    'assets/images/brand-teal-bus.svg'
];

// Sensitive route patterns that MUST NOT be cached by Service Worker under security compliance.
// Patterns support both root deployment (e.g. /office) and subdirectory deployment (e.g. /tours-travels/office).
const PRIVATE_ROUTE_PATTERNS = [
    /(^|\/)(office|driver|admin|dashboard|customers|employees|invoices|finance)(\/|$|\?)/i,
    /(^|\/)(login|logout|forgot-|reset-password|setup|email-otp)/i,
    /\/api\/(mobile|gps|fleet)\//i,
    /\/(private_uploads|storage|uploads)\//i
];

function isPrivateRequest(url) {
    let scopePath = '';
    try {
        if (self.registration && self.registration.scope) {
            scopePath = new URL(self.registration.scope).pathname.replace(/\/+$/, '');
        }
    } catch (e) {}

    const pathname = url.pathname;
    const relPath = (scopePath && pathname.startsWith(scopePath))
        ? pathname.slice(scopePath.length)
        : pathname;

    const candidates = [pathname, relPath, (relPath.startsWith('/') ? relPath : '/' + relPath)];
    return candidates.some((p) => PRIVATE_ROUTE_PATTERNS.some((pattern) => pattern.test(p)));
}

// ==============================================================================
// 1. INSTALL LIFECYCLE
// ==============================================================================
self.addEventListener('install', (event) => {
    // Force new service worker to activate immediately without waiting
    self.skipWaiting();

    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return Promise.allSettled(
                CORE_STATIC_ASSETS.map((asset) => {
                    const assetUrl = new URL(asset, self.registration.scope).toString();
                    return cache.add(new Request(assetUrl, { cache: 'reload' }));
                })
            );
        })
    );
});

// ==============================================================================
// 2. ACTIVATE LIFECYCLE
// ==============================================================================
self.addEventListener('activate', (event) => {
    // Only purge legacy Tours/Manaswi caches; never delete unrelated application caches
    const activeCaches = [SHELL_CACHE, STATIC_CACHE];

    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    const isOurCache = key.startsWith('tours-shell-') || key.startsWith('tours-static-') || key.startsWith('manaswi-shell-');
                    if (isOurCache && !activeCaches.includes(key)) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// ==============================================================================
// 3. FETCH STRATEGY
// ==============================================================================
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Security compliance check: Never cache private data, APIs, or authentication endpoints
    if (isPrivateRequest(url)) {
        // Direct network fetch without service worker caching
        return;
    }

    // Strategy A: Public Static UI Assets (CSS, JS, Fonts, Images, Icons in /assets/ & manifest.json)
    // Strictly restricted to /assets/ and manifest.json — never caches /storage/, user uploads, or private files
    const isPublicStatic = (url.pathname.includes('/assets/') || url.pathname.endsWith('/manifest.json')) &&
        (url.pathname.endsWith('.css') || url.pathname.endsWith('.js') || url.pathname.endsWith('.png') ||
         url.pathname.endsWith('.svg') || url.pathname.endsWith('.ico') || url.pathname.endsWith('.woff2') ||
         url.pathname.endsWith('.woff') || url.pathname.endsWith('.ttf') || url.pathname.endsWith('/manifest.json'));

    if (isPublicStatic) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const networkFetch = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);

                // If cached response exists, return it immediately while network updates in background
                return cachedResponse || networkFetch;
            })
        );
        return;
    }

    // Strategy B: Reference Data APIs (/api/reference/) are handled by client-side IndexedDB (tours-cache.js)
    // They pass through directly to the network without entering Service Worker Cache storage
    if (url.pathname.includes('/api/reference/')) {
        return;
    }

    // Strategy C: HTML Navigation Pages
    // Safe Online-First / Network-First: ensures authenticated status and user data are strictly checked by server
    event.respondWith(
        fetch(request).catch(() => {
            // NEVER return cached private HTML pages or authentication responses
            if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
                const scopeUrl = (self.registration && self.registration.scope) ? self.registration.scope : '/';
                const cssUrl = new URL('assets/css/app.css', scopeUrl).toString();
                return new Response(
                    `<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Connection Offline - Tours &amp; Travels</title>
                        <link rel="stylesheet" href="${cssUrl}">
                    </head>
                    <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0B192C;color:#FFF;font-family:sans-serif;padding:20px;">
                        <div style="max-width:440px;background:#1E3E62;padding:32px;border-radius:16px;text-align:center;border:1px solid #2E5077;box-shadow:0 10px 30px rgba(0,0,0,0.4);">
                            <div style="font-size:3rem;margin-bottom:1rem;">📡</div>
                            <h2 style="color:#FFF;margin-bottom:0.5rem;font-size:1.35rem;">Connection Offline</h2>
                            <p style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;line-height:1.5;">You appear to be without an internet connection. Sensitive financial and operational records require fresh server confirmation.</p>
                            <button onclick="window.location.reload()" style="background:#176B5B;color:#FFF;border:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:1rem;cursor:pointer;width:100%;">Retry Connection</button>
                        </div>
                    </body>
                    </html>`,
                    { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            }
            return caches.match(request);
        })
    );
            });
        })
    );
});

// ==============================================================================
// 4. CLIENT MESSAGING & DIAGNOSTIC CONTROL
// ==============================================================================
self.addEventListener('message', (event) => {
    const data = event.data;
    if (!data) return;

    if (data.action === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (data.action === 'CLEAR_CACHES') {
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key.startsWith('tours-') || key.startsWith('manaswi-')) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => {
            if (event.ports && event.ports[0]) {
                event.ports[0].postMessage({ success: true, message: 'All application caches purged.' });
            }
        });
    } else if (data.action === 'GET_CACHE_INFO') {
        caches.keys().then((keys) => {
            const ourKeys = keys.filter((k) => k.startsWith('tours-') || k.startsWith('manaswi-'));
            if (event.ports && event.ports[0]) {
                event.ports[0].postMessage({
                    version: CACHE_VERSION,
                    shell_cache: SHELL_CACHE,
                    static_cache: STATIC_CACHE,
                    active_caches: ourKeys
                });
            }
        });
    }
});
