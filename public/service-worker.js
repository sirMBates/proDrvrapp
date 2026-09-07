// public/service-worker.js
'use strict';

/**
 * ProDriver Service Worker
 *
 * Current responsibilities:
 *  - Cache explicitly approved static application assets.
 *  - Cache Google Font resources.
 *  - Never cache authenticated HTML pages or dynamic application/API data.
 *  - Never cache Emergency, profile, assignment, or authentication responses.
 *  - Queue ONLY driver-status updates when the network is genuinely unavailable.
 *  - Replay queued driver-status updates with Background Sync when supported.
 *
 * Future systems such as Timesheet and Internal Messenger are intentionally
 * NOT handled here yet. Add their offline behavior only when those systems
 * have explicit application-level rules.
 */

const CACHE_VERSION = 'v1';
const STATIC_CACHE = `prodriver-static-${CACHE_VERSION}`;
const FONT_CACHE = `prodriver-fonts-${CACHE_VERSION}`;

const STATUS_QUEUE_DB = 'prodriver-status-sync';
const STATUS_QUEUE_STORE = 'requests';
const STATUS_SYNC_TAG = 'prodriver-status-sync';

const STATIC_ASSETS = [
    '/manifest.json',
    '/dist/js/app.js',
    '/dist/js/main.js',
    '/dist/styles/scss/main.css',
    '/dist/styles/style.css',
    '/dist/images-videos/logoandicons/prodrvr-bus-icon-192.png',
    '/dist/images-videos/logoandicons/prodrvr-bus-icon-512.png'
];

/**
 * Routes whose failed non-GET request may be queued for later replay.
 *
 * Keep this allow-list deliberately small.
 * Assignment changes and Emergency actions must remain network-authoritative.
 */
const OFFLINE_QUEUE_ROUTES = new Set([
    '/setstatus'
]);


/* -------------------------------------------------------------------------- */
/* INSTALL                                                                    */
/* -------------------------------------------------------------------------- */

self.addEventListener('install', (event) => {
    console.log('[SW] Installing:', CACHE_VERSION);

    event.waitUntil(
        (async () => {
            const cache = await caches.open(STATIC_CACHE);

            const results = await Promise.allSettled(
                STATIC_ASSETS.map(async (asset) => {
                    try {
                        const response = await fetch(asset, {
                            cache: 'no-cache'
                        });

                        if (!response.ok) {
                            console.warn(
                                '[SW] Static asset not cached:',
                                asset,
                                response.status
                            );
                            return;
                        }

                        await cache.put(asset, response);
                    } catch (error) {
                        console.warn(
                            '[SW] Static asset unavailable during install:',
                            asset,
                            error
                        );
                    }
                })
            );

            console.log('[SW] Static precache complete:', results.length);

            await self.skipWaiting();
        })()
    );
});


/* -------------------------------------------------------------------------- */
/* ACTIVATE                                                                   */
/* -------------------------------------------------------------------------- */

self.addEventListener('activate', (event) => {
    console.log('[SW] Activating:', CACHE_VERSION);

    event.waitUntil(
        (async () => {
            const allowedCaches = new Set([
                STATIC_CACHE,
                FONT_CACHE
            ]);

            const cacheNames = await caches.keys();

            await Promise.all(
                cacheNames.map((cacheName) => {
                    if (!allowedCaches.has(cacheName)) {
                        console.log('[SW] Removing old cache:', cacheName);
                        return caches.delete(cacheName);
                    }

                    return Promise.resolve(false);
                })
            );

            await self.clients.claim();

            const clients = await self.clients.matchAll({
                includeUncontrolled: true,
                type: 'window'
            });

            for (const client of clients) {
                client.postMessage({
                    type: 'SW_UPDATED',
                    version: CACHE_VERSION
                });
            }

            console.log('[SW] Activated:', CACHE_VERSION);
        })()
    );
});


/* -------------------------------------------------------------------------- */
/* MESSAGES                                                                   */
/* -------------------------------------------------------------------------- */

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        console.log('[SW] SKIP_WAITING received.');
        self.skipWaiting();
    }
});


/* -------------------------------------------------------------------------- */
/* FETCH                                                                      */
/* -------------------------------------------------------------------------- */

/**
 * One fetch listener owns all request routing.
 *
 * Default rule:
 *     If a request is not explicitly safe to cache or explicitly approved
 *     for offline queueing, the service worker leaves it alone and the
 *     browser talks directly to the server.
 */
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignore unsupported schemes such as chrome-extension://.
    if (!url.protocol.startsWith('http')) {
        return;
    }

    /* ------------------------------ NAVIGATION ----------------------------- */

    /**
     * PHP/application pages may contain:
     *  - authenticated user information
     *  - CSRF tokens
     *  - server-authoritative application state
     *
     * Never serve these from Cache Storage.
     */
    if (request.mode === 'navigate') {
        return;
    }

    /* ----------------------------- GOOGLE FONTS ---------------------------- */

    if (
        request.method === 'GET' &&
        url.origin === 'https://fonts.googleapis.com'
    ) {
        event.respondWith(cacheGoogleFontCss(request));
        return;
    }

    if (
        request.method === 'GET' &&
        url.origin === 'https://fonts.gstatic.com'
    ) {
        event.respondWith(cacheGoogleFontFile(request));
        return;
    }

    /* --------------------------- CROSS-ORIGIN DATA ------------------------- */

    // Other third-party requests are not ours to cache or queue.
    if (url.origin !== self.location.origin) {
        return;
    }

    /* ------------------------------ STATIC GET ----------------------------- */

    if (request.method === 'GET') {
        if (isApprovedStaticAsset(url)) {
            event.respondWith(cacheFirstStatic(request));
        }

        /**
         * Everything else intentionally falls through to normal browser
         * networking. Examples:
         *
         *  /emergencystatus
         *  /getprofile
         *  /getassignments
         *  /getstatus
         *  /assignments
         *  /profile
         *  /
         *
         * No Cache Storage fallback is used for these routes.
         */
        return;
    }

    /* ------------------------- NON-GET MUTATIONS --------------------------- */

    /**
     * Queue only explicitly approved requests.
     *
     * Current ProDriver policy:
     *  - /setstatus may queue when the network is actually unavailable.
     *  - Assignment PATCH/POST operations are NOT queued.
     *  - Emergency operations are NOT queued here.
     *  - Auth/profile mutations are NOT queued.
     */
    if (
        ['POST', 'PATCH', 'DELETE'].includes(request.method) &&
        OFFLINE_QUEUE_ROUTES.has(url.pathname)
    ) {
        event.respondWith(
            networkOrQueueStatusRequest(request)
        );
    }
});


/* -------------------------------------------------------------------------- */
/* STATIC CACHE                                                               */
/* -------------------------------------------------------------------------- */

function isApprovedStaticAsset(url) {
    if (url.origin !== self.location.origin) {
        return false;
    }

    return (
        url.pathname.startsWith('/dist/') ||
        url.pathname === '/manifest.json'
    );
}


async function cacheFirstStatic(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.warn(
            '[SW] Static request unavailable:',
            request.url,
            error
        );

        return new Response(
            'Static resource unavailable.',
            {
                status: 503,
                statusText: 'Service Unavailable'
            }
        );
    }
}


/* -------------------------------------------------------------------------- */
/* GOOGLE FONT CACHE                                                          */
/* -------------------------------------------------------------------------- */

async function cacheGoogleFontCss(request) {
    const cache = await caches.open(FONT_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.warn(
            '[SW] Google Font CSS unavailable:',
            request.url,
            error
        );

        return new Response('', {
            status: 503,
            statusText: 'Service Unavailable'
        });
    }
}


async function cacheGoogleFontFile(request) {
    const cache = await caches.open(FONT_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.warn(
            '[SW] Google Font file unavailable:',
            request.url,
            error
        );

        return new Response('', {
            status: 503,
            statusText: 'Service Unavailable'
        });
    }
}


/* -------------------------------------------------------------------------- */
/* STATUS OFFLINE QUEUE                                                       */
/* -------------------------------------------------------------------------- */

/**
 * IMPORTANT:
 * HTTP 4xx/5xx responses are real server responses and are returned directly.
 * They are NOT treated as offline failures.
 *
 * Only a genuine fetch/network exception causes queueing.
 */
async function networkOrQueueStatusRequest(request) {
    try {
        return await fetch(request.clone());
    } catch (error) {
        console.warn(
            '[SW] Network unavailable. Queueing status request:',
            request.url
        );

        const queuedRequest = await serializeRequest(request);

        await addQueuedStatusRequest(queuedRequest);

        if ('sync' in self.registration) {
            try {
                await self.registration.sync.register(
                    STATUS_SYNC_TAG
                );
            } catch (syncError) {
                console.warn(
                    '[SW] Background Sync registration failed:',
                    syncError
                );
            }
        }

        return new Response(
            JSON.stringify({
                status: 'queued',
                message: 'Status queued - will sync when back online.'
            }),
            {
                status: 202,
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );
    }
}


async function serializeRequest(request) {
    const headers = {};

    for (const [key, value] of request.headers.entries()) {
        headers[key] = value;
    }

    return {
        id: createQueueId(),
        url: request.url,
        method: request.method,
        headers,
        body: await request.clone().text(),
        createdAt: Date.now()
    };
}


function createQueueId() {
    if (self.crypto?.randomUUID) {
        return self.crypto.randomUUID();
    }

    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}


/* -------------------------------------------------------------------------- */
/* INDEXEDDB                                                                  */
/* -------------------------------------------------------------------------- */

function openStatusQueueDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(
            STATUS_QUEUE_DB,
            1
        );

        request.onupgradeneeded = () => {
            const db = request.result;

            if (!db.objectStoreNames.contains(STATUS_QUEUE_STORE)) {
                db.createObjectStore(
                    STATUS_QUEUE_STORE,
                    {
                        keyPath: 'id'
                    }
                );
            }
        };

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
}


async function addQueuedStatusRequest(requestData) {
    const db = await openStatusQueueDatabase();

    try {
        await new Promise((resolve, reject) => {
            const transaction = db.transaction(
                STATUS_QUEUE_STORE,
                'readwrite'
            );

            const store = transaction.objectStore(
                STATUS_QUEUE_STORE
            );

            store.put(requestData);

            transaction.oncomplete = () => resolve();
            transaction.onerror = () => reject(transaction.error);
            transaction.onabort = () => reject(transaction.error);
        });
    } finally {
        db.close();
    }
}


async function getQueuedStatusRequests() {
    const db = await openStatusQueueDatabase();

    try {
        return await new Promise((resolve, reject) => {
            const transaction = db.transaction(
                STATUS_QUEUE_STORE,
                'readonly'
            );

            const store = transaction.objectStore(
                STATUS_QUEUE_STORE
            );

            const request = store.getAll();

            request.onsuccess = () => {
                resolve(request.result || []);
            };

            request.onerror = () => {
                reject(request.error);
            };
        });
    } finally {
        db.close();
    }
}


async function deleteQueuedStatusRequest(id) {
    const db = await openStatusQueueDatabase();

    try {
        await new Promise((resolve, reject) => {
            const transaction = db.transaction(
                STATUS_QUEUE_STORE,
                'readwrite'
            );

            const store = transaction.objectStore(
                STATUS_QUEUE_STORE
            );

            store.delete(id);

            transaction.oncomplete = () => resolve();
            transaction.onerror = () => reject(transaction.error);
            transaction.onabort = () => reject(transaction.error);
        });
    } finally {
        db.close();
    }
}


/* -------------------------------------------------------------------------- */
/* BACKGROUND SYNC                                                            */
/* -------------------------------------------------------------------------- */

self.addEventListener('sync', (event) => {
    if (event.tag !== STATUS_SYNC_TAG) {
        return;
    }

    event.waitUntil(
        processStatusQueue()
    );
});


async function processStatusQueue() {
    const queuedRequests =
        await getQueuedStatusRequests();

    if (queuedRequests.length === 0) {
        return;
    }

    let successCount = 0;

    for (const queued of queuedRequests) {
        try {
            const response = await fetch(
                queued.url,
                {
                    method: queued.method,
                    headers: queued.headers,
                    body: queued.body || undefined,
                    credentials: 'include'
                }
            );

            /**
             * Remove only requests the server actually processed successfully.
             *
             * A server-side 4xx/5xx remains queued so we do not silently
             * pretend synchronization succeeded.
             *
             * NOTE:
             * A queued CSRF token may become invalid after logout/login.
             * We therefore report failed replay to the client instead of
             * deleting it as though it succeeded.
             */
            if (response.ok) {
                await deleteQueuedStatusRequest(
                    queued.id
                );

                successCount++;
            } else {
                console.warn(
                    '[SW] Queued status rejected by server:',
                    response.status,
                    queued.url
                );
            }
        } catch (error) {
            console.warn(
                '[SW] Status replay still offline:',
                queued.url,
                error
            );

            // Network is still unavailable. Leave remaining entries queued.
            break;
        }
    }

    const clients = await self.clients.matchAll({
        includeUncontrolled: true,
        type: 'window'
    });

    for (const client of clients) {
        client.postMessage({
            type: 'OFFLINE_SYNC_COMPLETE',
            successCount,
            synced: {
                statuses: successCount
            }
        });
    }
}