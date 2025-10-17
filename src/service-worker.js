// public/service-worker.js
// Generate a dynamic cache version based on build timestamp
const CACHE_VERSION = 'v' + new Date().getTime();
const CACHE_NAME = `prodriver-${CACHE_VERSION}`;

// Core files to cache
const STATIC_ASSETS = [
  '/',
  '/signin',
  '/manifest.json',
  '/dist/js/app.js',
  '/dist/js/main.js',
  '/dist/styles/scss/main.css',
  '/dist/styles/style.css',
  '/dist/images-videos/logoandicons/prodrvr-bus-icon-192.png',
  '/dist/images-videos/logoandicons/prodrvr-bus-icon-512.png'
];

// Install event – cache core assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing, cache version:', CACHE_VERSION);
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// Activate event – remove old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating...');
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', key);
            return caches.delete(key);
          }
        })
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignore non-GET requests
  if (request.method !== 'GET') return;

  // Skip browser extension requests
  if (request.url.startsWith('chrome-extension://')) return;

  // Skip caching / intercepting sensitive/auth routes
  if (
    url.pathname.startsWith('/signin') ||
    url.pathname.startsWith('/signup') ||
    url.pathname.startsWith('/logout') ||
    url.pathname.startsWith('/register') ||
    url.pathname.startsWith('/forget') ||
    url.pathname.startsWith('/completereset')
  ) {
    //event.respondWith(fetch(request)); // always let network handle
    return;
  }

  // Detect API or dynamic routes — prefer network first
  const isDynamicRequest =
    url.pathname.startsWith('/api/') ||
    url.pathname.includes('assignmenthandler') ||
    url.pathname === '/';

  if (isDynamicRequest) {
    event.respondWith(networkFirst(request));
  } else {
    event.respondWith(cacheFirst(request));
  }
});

// --- STRATEGIES ---
async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request, { redirect: 'follow' });
    if (!response.ok || response.type === 'opaqueredirect') {
      console.warn('[SW] Skipping redirected or invalid response:', request.url);
      return response;
    }

    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
    return response;
  } catch (err) {
    console.warn('[SW] CacheFirst failed:', request.url, err);
    return new Response('Offline or not cached', { status: 503 });
  }
};

async function networkFirst(request) {
  try {
    const response = await fetch(request, { redirect: 'follow' });
    if (!response.ok || response.type === 'opaqueredirect') {
      console.warn('[SW] Skipping redirected or invalid response:', request.url);
      return response;
    }

    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
    return response;
  } catch (err) {
    console.warn('[SW] NetworkFirst failed, trying cache:', request.url);
    const cached = await caches.match(request);
    return cached || new Response('Offline or fetch failed', { status: 503 });
  }
};

