// workbox-config.js
module.exports = {
  globDirectory: "public/",
  globPatterns: [
    "**/*.{html,css,js,png,jpg,svg}",
    "offline.html" // ensure offline page is precached
  ],
  swDest: "public/service-worker.js",
  clientsClaim: true,
  skipWaiting: true,

  runtimeCaching: [
    // Handle page/document requests (HTML, PHP routes, etc.)
    {
      urlPattern: ({ request }) => request.destination === "document",
      handler: "NetworkFirst",
      options: {
        cacheName: "pages",
        networkTimeoutSeconds: 5,
        plugins: [
          {
            // If both network & cache fail, fallback to offline.html
            handlerDidError: async () => caches.match("/offline.html")
          }
        ]
      }
    },

    // Handle CSS, JS, and workers
    {
      urlPattern: ({ request }) =>
        ["style", "script", "worker"].includes(request.destination),
      handler: "StaleWhileRevalidate",
      options: {
        cacheName: "assets",
      }
    },

    // Handle images and fonts
    {
      urlPattern: ({ request }) =>
        ["image", "font"].includes(request.destination),
      handler: "CacheFirst",
      options: {
        cacheName: "media",
        expiration: {
          maxEntries: 50,
          maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
        }
      }
    }
  ]
};
