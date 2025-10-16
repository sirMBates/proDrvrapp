<footer class="mt-auto text-light text-center text-lg-start">
        <h5 class="<?= !urlIs('/register') ?: 'text-dark';?> text-center text-uppercase"><i>created by </i>softbigboy</h5>
        <p class="text-center"><a class="<?= urlIS('/register') ? 'text-dark' : 'text-light';?>" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
</footer>
<script src='../../dist/js/app.js'></script>
<!-- Load JQuery Color CDN(Content Delivery Network) -->
<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>
<!--<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('[SW] Registered:', reg.scope))
      .catch(err => console.error('[SW] Registration failed:', err));

    navigator.serviceWorker.addEventListener('message', (event) => {
      if (event.data && event.data.type === 'SW_UPDATED') {
        showUpdateToast();
      }
    });
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
    document.getElementById('refresh-app').addEventListener('click', () => {
      window.location.reload();
    });
  }
}
</script>-->
<?php
        require base_path("app/includes/getscripts.php");
?>
</body>
</html>