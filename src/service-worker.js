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

// 🔹 Listen for messages from the client (e.g., SKIP_WAITING)
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    console.log('[SW] Received SKIP_WAITING message from client');
    self.skipWaiting(); // immediately activate the new worker
  }
});

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
    (async () => {
      // 1️⃣ Remove outdated caches
      const keys = await caches.keys();
      await Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', key);
            return caches.delete(key);
          }
        })
      );

      // 2️⃣ Immediately take control of all open clients
      self.skipWaiting();
      await self.clients.claim();

      // 3️⃣ Notify clients that a new version is active
      const allClients = await self.clients.matchAll({ includeUncontrolled: true });
      for (const client of allClients) {
        client.postMessage({ type: 'SW_UPDATED' });
      }

      console.log('[SW] Activated new version:', CACHE_NAME);
    })()
  );
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

// --- AUTO UPDATE DETECTION ---
// Notify clients when a new service worker takes control
self.addEventListener('install', () => {
  console.log('[SW] Installed new version');
});

self.addEventListener('activate', async () => {
  console.log('[SW] Activated new version:', CACHE_NAME);

  // Once activated, send a message to all controlled clients
  const allClients = await self.clients.matchAll({ includeUncontrolled: true });
  for (const client of allClients) {
    client.postMessage({ type: 'SW_UPDATED' });
  }
});
