// src/js/pwa.js
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register(new URL('../service-worker.js', import.meta.url))
      .then((reg) => {
        console.log('[SW] Registered successfully:', reg.scope);

        // 🔹 Optional: Listen for custom SW messages (e.g., updates)
        navigator.serviceWorker.addEventListener('message', (event) => {
          if (event.data && event.data.type === 'SW_UPDATED') {
            showUpdateToast(); // You already have this function
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
  }
}