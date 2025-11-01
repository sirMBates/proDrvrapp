// public/service-worker.js
importScripts('https://cdn.jsdelivr.net/npm/idb@7/build/umd.js');
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

// Precache Google Font CSS and WOFF2 files
const PRECACHE_FONTS = [
  // CSS stylesheets
  'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap',
  'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
  'https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap',

  // Actual WOFF2 files (add the key ones used by your site)
  'https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxP.woff2',
  'https://fonts.gstatic.com/s/roboto/v30/KFOlCnqEu92Fr1MmWUlfBBc9.woff2'
];

// 🔹 Listen for messages from the client (e.g., SKIP_WAITING)
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    console.log('[SW] Received SKIP_WAITING message from client');
    self.skipWaiting(); // immediately activate the new worker
  }
});

self.addEventListener('install', (event) => {
  console.log('[SW] Installing, cache version:', CACHE_VERSION);
  event.waitUntil(
    (async () => {
      const appCache = await caches.open(CACHE_NAME);

      // --- Safer static asset caching ---
      const results = await Promise.allSettled(
        STATIC_ASSETS.map(asset =>
          fetch(asset)
            .then(response => {
              if (response.ok) return appCache.put(asset, response);
              console.warn('[SW] Skipping failed asset:', asset, response.status);
            })
            .catch(err => console.warn('[SW] Failed to fetch asset:', asset, err))
        )
      );
      console.log('[SW] Cached app assets:', results);

      // --- Pre-cache Google Fonts or other static font assets ---
      if (typeof FONT_CACHE !== 'undefined' && typeof PRECACHE_FONTS !== 'undefined') {
        const fontCache = await caches.open(FONT_CACHE);
        const fontResults = await Promise.allSettled(
          PRECACHE_FONTS.map(font =>
            fetch(font)
              .then(response => {
                if (response.ok) return fontCache.put(font, response);
                console.warn('[SW] Skipping failed font:', font, response.status);
              })
              .catch(err => console.warn('[SW] Failed to fetch font:', font, err))
          )
        );
        console.log('[SW] Pre-cached fonts:', fontResults);
      }

      await self.skipWaiting();
      console.log('[SW] Installed new version');
    })()
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

          if (key.startsWith('prodriver-fonts-') && key !== FONT_CACHE) {
              console.log('[SW] Deleting old font cache:', key);
              return caches.delete(key);
          }

          if (key !== CACHE_NAME && key !== FONT_CACHE) {
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
    url.pathname.startsWith('/getassignments') ||
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

// --- GOOGLE FONT OPTIMIZATION ---
// When you update your font list or add a new font, just bump the cache version:
const FONT_CACHE = 'prodriver-fonts-v2';

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Handle Google Fonts CSS (stylesheets)
  if (url.origin === 'https://fonts.googleapis.com') {
    event.respondWith(cacheGoogleFontCSS(request));
    return;
  }

  // Handle Google Fonts font files (woff2)
  if (url.origin === 'https://fonts.gstatic.com') {
    event.respondWith(cacheGoogleFontFiles(request));
    return;
  }
});

// Cache and serve Google Fonts CSS
async function cacheGoogleFontCSS(request) {
  const cache = await caches.open(FONT_CACHE);
  const cachedResponse = await cache.match(request);
  if (cachedResponse) return cachedResponse;

  try {
    const response = await fetch(request);
    if (response.ok) {
      // Clone and cache CSS
      cache.put(request, response.clone());
    }
    return response;
  } catch (err) {
    console.warn('[SW] Font CSS fetch failed:', request.url, err);
    return cachedResponse || new Response('', { status: 503 });
  }
};

// Cache and serve Google Fonts font files
async function cacheGoogleFontFiles(request) {
  const cache = await caches.open(FONT_CACHE);
  const cachedResponse = await cache.match(request);
  if (cachedResponse) return cachedResponse;

  try {
    const response = await fetch(request);
    if (response.ok) {
      cache.put(request, response.clone());
    }
    return response;
  } catch (err) {
    console.warn('[SW] Font file fetch failed:', request.url, err);
    return cachedResponse || new Response('', { status: 503 });
  }
};

// --- AUTO UPDATE DETECTION ---
// Notify clients when a new service worker takes control
self.addEventListener('install', () => {
  console.log('[SW] Installed new version');
});

// --- OFFLINE SYNC QUEUE ---
const OFFLINE_QUEUE = 'prodriver-sync-queue';

// Intercept POST / PATCH / DELETE requests
self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (
    request.method === 'POST' ||
    request.method === 'PATCH' ||
    request.method === 'DELETE'
  ) {
    event.respondWith(
      (async () => {
        try {
          // Try network first
          const response = await fetch(request.clone());
          return response;
        } catch (err) {
          // Save request for background sync
          const queue = await openQueue();
          const body = await request.clone().text();
          await queue.add({
            url: request.url,
            method: request.method,
            body,
            headers: [...request.headers],
            timestamp: Date.now(),
          });

          // Register background sync event
          if ('sync' in self.registration) {
            await self.registration.sync.register('prodriver-sync');
          }

          console.warn('[SW] Queued offline request for sync later:', request.url);
          return new Response(
            JSON.stringify({ status: 'queued', message: 'Offline — will sync when online.' }),
            { headers: { 'Content-Type': 'application/json' } }
          );
        }
      })()
    );
  }
});

// Helper: open IndexedDB queue
async function openQueue() {
  const db = await idb.openDB(OFFLINE_QUEUE, 1, {
    upgrade(db) {
      db.createObjectStore('requests', { keyPath: 'timestamp' });
    },
  });
  return {
    async add(requestData) {
      const tx = db.transaction('requests', 'readwrite');
      await tx.store.add(requestData);
      await tx.done;
    },
    async getAll() {
      return (await db.getAll('requests')) || [];
    },
    async clear() {
      const tx = db.transaction('requests', 'readwrite');
      await tx.store.clear();
      await tx.done;
    },
  };
}

// --- Background sync handler ---
self.addEventListener('sync', (event) => {
  if (event.tag === 'prodriver-sync') {
    event.waitUntil(processOfflineQueue());
  }
});

async function processOfflineQueue() {
  const queue = await openQueue();
  const requests = await queue.getAll();
  console.log(`[SW] Processing ${requests.length} queued requests...`);

  let syncedStatusCount = 0;
  let syncedAssignmentCount = 0;
  let successCount = 0;

  for (const req of requests) {
    try {
      const res = await fetch(req.url, {
        method: req.method,
        headers: new Headers(req.headers),
        body: req.body,
      });
      if (res.ok) console.log('[SW] Synced request:', req.url);
      successCount++;
      // Categorize the synced request by its endpoint
      if ( req.url.includes('/setstatus')) {
        syncedStatusCount++;
      } else if ( req.url.includes('/assignmenthandler')) {
        syncedAssignmentCount++;
      }
    } catch (err) {
      console.warn('[SW] Failed to re-sync request:', req.url);
    }
  }

  await queue.clear();
  // Notify all connected clients ( tabs/windows )
  const allClients = await self.clients.matchAll({ includeUncontrolled: true });
  for ( const client of allClients ) {
    client.postMessage({
      type: 'OFFLINE_SYNC_COMPLETE',
      successCount,
      synced: {
        statuses: syncedStatusCount,
        assignments: syncedAssignmentCount,
      },
    });
  }
  console.log(`[SW] Sync complete — ${successCount} successful (${syncedStatusCount} status, ${syncedAssignmentCount} assignments)`);
};

