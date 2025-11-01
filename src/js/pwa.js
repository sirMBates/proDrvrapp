// src/js/pwa.js
import { queueRequest, getAllQueued, clearQueued } from "./dbQueue.js";
import { fetchDrvr, showFlashAlert } from "./helpers.js";
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
  navigator.serviceWorker
    //.register(new URL('../service-worker.js', import.meta.url))
    .register(new URL('/service-worker.js', window.location.origin))
    .then((reg) => {
      console.log('[SW] Registered successfully:', reg.scope);

      // 🔹 Listen for messages from the Service Worker (updates, syncs, etc.)
      navigator.serviceWorker.addEventListener('message', (event) => {
        const data = event.data;
        if (!data || !data.type) return;

        // 🟦 New update available
        if (data.type === 'SW_UPDATED') {
          showUpdateToast(); // already in your code
        }

        // 🟩 Offline queue sync completed
        if (data.type === 'OFFLINE_SYNC_COMPLETE') {
          const { statuses = 0, assignments = 0 } = data.synced || {};
          const total = data.successCount || 0;

          console.log(`[PWA] Offline sync complete — ${total} total: ${statuses} statuses, ${assignments} assignments.`);

          if (statuses > 0)
            showSyncToast(`✅ ${statuses} status update${statuses > 1 ? 's' : ''} synced.`, '#198754'); // green

          if (assignments > 0)
            showSyncToast(`📋 ${assignments} assignment${assignments > 1 ? 's' : ''} synced.`, '#0d6efd'); // blue

          if (total > 0 && statuses === 0 && assignments === 0)
            showSyncToast(`✅ ${total} offline request${total > 1 ? 's' : ''} synced.`, '#1d5283');
        }
      });
    })
    .catch((err) => console.error('[SW] Registration failed:', err));
  });

  function showUpdateToast() {
    // Create a simple toast container
    const toast = document.createElement('div');
    toast.innerHTML = `
      <div style="
        position: fixed;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background: #1d5283;
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.25);
        z-index: 9999;
        font-family: Roboto, sans-serif;
        display: flex;
        align-items: center;
        gap: 1rem;
      ">
        <span>🚀 New update available!</span>
        <button id="refresh-app" style="
          background: #fff;
          color: #1d5283;
          border: none;
          border-radius: 0.5rem;
          padding: 0.5rem 1rem;
          font-weight: bold;
          cursor: pointer;
        ">Reload</button>
      </div>
      `;
    document.body.appendChild(toast);

    // Handle the reload button
    document.getElementById('refresh-app').addEventListener('click', async () => {
      const reg = await navigator.serviceWorker.getRegistration();
      if (reg && reg.waiting) {
        console.log('[PWA] Triggering skip waiting...');
        reg.waiting.postMessage({ type: 'SKIP_WAITING' }); // 💥 tell SW to activate
      }
      window.location.reload(); // refresh after activation
    });
  };

  // === Toast Container for Sync Notifications ===
  let syncToastContainer = document.getElementById('sync-toast-container');
  if (!syncToastContainer) {
    syncToastContainer = document.createElement('div');
    syncToastContainer.id = 'sync-toast-container';
    syncToastContainer.style.cssText = `
      position: fixed;
      top: 1rem;
      right: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      z-index: 99999;
      pointer-events: none; /* so it doesn’t block clicks */
    `;
    document.body.appendChild(syncToastContainer);
  };

  function showSyncToast(message = 'All offline actions synced!', color = '#1d5283') {
    // Create the toast element
    const toast = document.createElement('div');
    toast.className = 'sync-toast';
    toast.style.cssText = `
      background: ${color};
      color: #fff;
      padding: 0.9rem 1.4rem;
      border-radius: 0.75rem;
      box-shadow: 0 4px 16px rgba(0,0,0,0.25);
      font-family: Roboto, 'Segoe UI', sans-serif;
      font-weight: 500;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      opacity: 0;
      transform: translateX(120%);
      transition:
        opacity 0.4s ease,
        transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
      pointer-events: auto;
    `;

    toast.innerHTML = `
      <i class="fa-solid fa-cloud-arrow-up"></i>
      <span>${message}</span>
      <button style="
        margin-left: 0.75rem;
        baclground: transparent;
        border: none;
        color: #fff;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 0;"
        title="Dismiss"
      >&times;</button> 
    `;

    // Append to container
    syncToastContainer.appendChild(toast);

    // Allow manual dismiss
    toast.querySelector('button').addEventListener('click', () => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(120%)';
      setTimeout(() => toast.remove(), 400);
    });


    // Animate slide-in
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(0)';
    });

    // Auto-hide after 4 seconds
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(120%)';
      setTimeout(() => {
        toast.remove();

        // If container becomes empty, remove it after a short delay
        if (!syncToastContainer.hasChildNodes()) {
          setTimeout(() => {
            if (!syncToastContainer.hasChildNodes()) syncToastContainer.remove();
          }, 500);
        }
      }, 400);
    }, 4000);
  };
};

window.addEventListener('DOMContentLoaded', () => {
    // 🧩 Custom Install Prompt
    let deferredPrompt;
    const installBtn = document.createElement('button');
    installBtn.innerHTML = '<i class="fa-solid fa-download" style="margin-right: 8px;"></i>Install ProDriver';
    installBtn.style.cssText = `
      position: fixed;
      bottom: 1.25rem;
      right: 1.25rem;
      background: linear-gradient(135deg, #1d5283, #005fa3);
      color: #fff;
      border: none;
      padding: 0.85rem 1.5rem;
      border-radius: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      font-family: "Roboto", "Segoe UI", sans-serif;
      box-shadow: 0 4px 12px rgba(0,0,0,0.25);
      transition: 
          opacity 0.6s cubic-bezier(0.25, 0.8, 0.25, 1),
          transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1),
          background 0.3s ease;
      opacity: 0;
      transform: translateY(20px);
      cursor: pointer;
      z-index: 9999;
      display: none;
    `;

    installBtn.addEventListener('mouseenter', () => {
      installBtn.style.background = 'linear-gradient(135deg, #005fa3, #1d5283)';
      installBtn.style.transform = 'scale(1.05)';
    });
    installBtn.addEventListener('mouseleave', () => {
      installBtn.style.background = 'linear-gradient(135deg, #1d5283, #005fa3)';
      installBtn.style.transform = 'scale(1)';
    });


    document.body.appendChild(installBtn);

    // 🎬 Enhanced fade-in with gentle pop animation
    function fadeInInstallBtn() {
      installBtn.style.display = 'block';
      installBtn.style.transform = 'scale(0.9) translateY(20px)';
      installBtn.style.opacity = '0';
      requestAnimationFrame(() => {
        installBtn.style.transition = 'opacity 0.6s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1)';
        installBtn.style.opacity = '1';
        installBtn.style.transform = 'scale(1.08) translateY(0)';
      });

      // settle back to normal scale after a short delay
      setTimeout(() => {
        installBtn.style.transform = 'scale(1) translateY(0)';
      }, 550);
    }

    // 🎬 Enhanced fade-out animation with smoother exit
    function fadeOutInstallBtn() {
      installBtn.style.opacity = '0';
      installBtn.style.transform = 'scale(0.95) translateY(10px)';
      setTimeout(() => {
        installBtn.style.display = 'none';
      }, 600);
    }

    // Detect install readiness
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      fadeInInstallBtn();
    });


    // Handle click to install
    installBtn.addEventListener('click', async () => {
      fadeOutInstallBtn();
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`[PWA] Install prompt outcome: ${outcome}`);
        deferredPrompt = null;
      }
    });

    // Hide the button once installed
    window.addEventListener('appinstalled', () => {
      console.log('[PWA] App successfully installed!');
      fadeOutInstallBtn();
    });
});

// --- NETWORK STATUS TOAST (offline/online) ---
(function setupNetworkToasts() {
  // Create a single reusable toast element
  const toast = document.createElement('div');
  toast.setAttribute('id', 'network-toast');
  toast.style.cssText = `
    position: fixed;
    left: 50%;
    bottom: 1rem;
    transform: translateX(-50%) translateY(20px);
    min-width: 260px;
    max-width: 92vw;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    color: #fff;
    background: #6c757d; /* default, will be overridden per state */
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    font-size: 0.95rem;
    font-family: "Roboto", "Segoe UI", sans-serif;
    z-index: 10000;
    opacity: 0;
    pointer-events: none;
    transition:
      opacity 280ms ease,
      transform 280ms ease,
      background 180ms ease;
  `;
  toast.innerHTML = `<span id="network-toast-text"></span>`;
  document.body.appendChild(toast);

  let toastTimer = null;
  let lastState = null; // 'online' | 'offline'

  function showToast(message, bgColor = '#6c757d', autoHideMs = 2800) {
    const textEl = document.getElementById('network-toast-text');
    if (!textEl) return;
    textEl.textContent = message;
    toast.style.background = bgColor;

    // show
    toast.style.pointerEvents = 'auto';
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    // clear any existing timer
    if (toastTimer) clearTimeout(toastTimer);

    // auto-hide unless explicitly persistent
    if (autoHideMs > 0) {
      toastTimer = setTimeout(hideToast, autoHideMs);
    }
  }

  function hideToast() {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(20px)';
    setTimeout(() => {
      toast.style.pointerEvents = 'none';
    }, 300);
  }

  function handleOnline() {
    if (lastState === 'online') return; // debounce duplicate events
    lastState = 'online';
    // Blue/green success look
    showToast('Back online ✓', '#198754', 2500);
  }

  function handleOffline() {
    if (lastState === 'offline') return; // debounce
    lastState = 'offline';
    // Red warning, keep a bit longer
    showToast('You’re offline. Some features may not work.', '#dc3545', 4000);
  }

  // Initial state check on load
  if (navigator.onLine) {
    lastState = 'online';
  } else {
    lastState = 'offline';
    // Optionally show on first load if offline:
    showToast('You’re offline. Some features may not work.', '#dc3545', 4000);
  }

  // Listen for connectivity changes
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
})();

function serializeFormData(formData) {
  const obj = {};
  for (const [key, value] of formData.entries()) {
    obj[key] = value;
  }
  return obj;
};

export async function handleAssignmentFetch(options) {
  try {
    return await fetchDrvr('https://prodriver.local/assignmenthandler', options);
  } catch (err) {
    console.warn('[PWA] Network unavailable - queuing request');
    let serializedBody = null;
    if (options.body instanceof FormData) {
      serializedBody = serializeFormData(options.body);
    } else if (typeof options.body === 'object') {
      serializedBody = options.body;
    }

    await queueRequest({
      url: 'https://prodriver.local/assignmenthandler',
      options: {
        method: options.method,
        headers: options.headers,
        body: serializedBody,
      },
    });

    return { status: 'queued', message: 'Offline — will sync when back online.' };
  }
};

window.addEventListener('online', async () => {
  const queued = await getAllQueued();
  if (!queued.length) return;

  console.log(`[PWA] Syncing ${queued.length} queued request(s)...`);
  for (const req of queued) {
    try {
      const formData = new FormData();
      for (const [key, value] of Object.entries(req.options.body || {})) {
        formData.append(key, value);
      }

      const data = await fetchDrvr(req.url, {
        method: req.options.method,
        headers: req.options.headers,
        body: formData,
        credentials: 'include',
      });

      if (data && data.status === 'success') {
        await clearQueued(req.id);
        showFlashAlert('success', 'Offline request synced!');
      } else {
        console.warn('[PWA] Sync response not successful:', data);
      }
    } catch (err) {
      console.warn('[PWA] Failed to sync queued request:', err);
    }
  }
});

export async function handleStatusFetch(options) {
  try {
    return await fetchDrvr('https://prodriver.local/setstatus', options);
  } catch (err) {
    console.warn('[PWA] Offline — queueing status change');
    let serializedBody = null;
    if (options.body instanceof FormData) {
      serializedBody = serializeFormData(options.body);
    } else if (typeof options.body === 'object') {
      serializedBody = options.body;
    }

    let jsonBody = serializedBody;
    if (typeof serializedBody === 'object' && !(serializedBody instanceof FormData)) {
      jsonBody = JSON.stringify(serializedBody);
    }

    await queueRequest({
      url: 'https://prodriver.local/setstatus',
      options: {
        method: options.method,
        headers: options.headers,
        body: jsonBody,
      },
    });

    showFlashAlert('warning', 'Status saved offline — will sync when online.');
    return { status: 'queued', message: 'Offline — queued for sync.' };
  }
};