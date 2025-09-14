<?php
require "partials/head.php";
require "partials/nav.php";
// offline.php - PWA Offline Page
?>

<!-- Small offline indicator -->
<span id="offline-icon" title="You're offline">
    <i class="fas fa-wifi-slash"></i>
</span>

<div class="offline-container" style="text-align:center; padding:2rem;">
    <img src="../dist/images-videos/logoandicons/bus-driver-icon-512.png" 
         alt="Prodriver Logo" 
         style="max-width:150px; margin-bottom:2rem;">
    <h1>You are offline</h1>
    <p>Prodriver cannot load this page while offline. Please check your internet connection and try again.</p>
    <button onclick="location.reload()" 
            style="margin-top:1.5rem; padding:0.5rem 1rem; font-size:1rem; cursor:pointer;">
        Retry
    </button>
</div>

<script>
const offlineIcon = document.getElementById('offline-icon');

function updateOfflineStatus() {
    if (navigator.onLine) {
        offlineIcon.style.display = 'none';
    } else {
        offlineIcon.style.display = 'inline-block';
    }
}

// Listen for online/offline events
window.addEventListener('online', updateOfflineStatus);
window.addEventListener('offline', updateOfflineStatus);

// Check status on page load
updateOfflineStatus();
</script>
<?php
require 'partials/footer.php';
?>
