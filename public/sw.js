const CACHE_NAME = 'erleravm-cache-v2';
const IMAGE_CACHE = 'erleravm-image-cache-v2';

const PRECACHE_ASSETS = [
    '/',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== IMAGE_CACHE) {
                        return caches.delete(cacheName);
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
    if (request.method !== 'GET') return;

    // Handle Image Requests
    if (request.destination === 'image' || url.pathname.match(/\.(png|jpg|jpeg|gif|svg|webp)$/i)) {
        event.respondWith(
            caches.open(IMAGE_CACHE).then((cache) => {
                return cache.match(request).then((response) => {
                    if (response) {
                        // Return from cache, but fetch and update cache in background (Stale-While-Revalidate)
                        fetch(request).then((networkResponse) => {
                            if (networkResponse.ok) {
                                cache.put(request, networkResponse.clone());
                            }
                        }).catch(() => {});
                        return response;
                    }
                    // If not in cache, fetch and cache
                    return fetch(request).then((networkResponse) => {
                        if (networkResponse.ok) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // Default Cache-First strategy for static assets like fonts/JS/CSS (if not handled by Vite/Browser caching)
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(request).then((response) => {
                return response || fetch(request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, networkResponse.clone());
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // Network-first for other requests (e.g. Inertia pages/API)
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
