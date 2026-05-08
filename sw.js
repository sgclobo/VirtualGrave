/**
 * Service Worker — In Loving Memory
 * Caches static assets for offline browsing
 */

const CACHE_NAME     = 'memorial-v1';
const OFFLINE_PAGE   = '/offline.html';

// Static assets to pre-cache
const PRECACHE_ASSETS = [
  '/',
  '/index.php',
  '/pages/biography.php',
  '/pages/timeline.php',
  '/pages/gallery.php',
  '/pages/memorial.php',
  '/pages/guestbook.php',
  '/assets/css/main.css',
  '/assets/css/animations.css',
  '/assets/js/main.js',
  '/assets/js/petals.js',
  '/offline.html',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
];

// ─── Install ────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_ASSETS))
  );
});

// ─── Activate ───────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
      )
    )
  );
  self.clients.claim();
});

// ─── Fetch ──────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;

  // Only handle GET requests
  if (request.method !== 'GET') return;

  // Skip admin, API, and upload paths — always fetch fresh
  const url = new URL(request.url);
  if (url.pathname.startsWith('/admin') ||
      url.pathname.startsWith('/api')   ||
      url.pathname.startsWith('/uploads')) {
    return;
  }

  event.respondWith(
    caches.match(request).then(cached => {
      if (cached) return cached;

      return fetch(request)
        .then(response => {
          // Cache successful responses for static assets
          if (response && response.status === 200) {
            const isStatic = /\.(css|js|png|jpg|jpeg|gif|webp|woff2?|svg|ico)$/i.test(url.pathname);
            if (isStatic) {
              const clone = response.clone();
              caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
            }
          }
          return response;
        })
        .catch(() => {
          // Fallback to offline page for navigation requests
          if (request.mode === 'navigate') {
            return caches.match(OFFLINE_PAGE);
          }
        });
    })
  );
});
