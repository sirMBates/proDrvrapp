<footer class="mt-auto text-light text-center text-lg-start">
        <h5 class="<?= !urlIs('/register') ?: 'text-dark';?> text-center text-uppercase"><i>created by </i>softbigboy</h5>
        <p class="text-center"><a class="<?= urlIS('/register') ? 'text-dark' : 'text-light';?>" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
</footer>
<script src='../../dist/js/app.js'></script>
<!-- Load JQuery Color CDN(Content Delivery Network) -->
<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>
<?php
        require base_path("app/includes/getscripts.php");
?>
<script>
        // Toggle offline indicator visibility dynamically
        window.addEventListener('online', () => {
                document.getElementById('offline-indicator').style.display = 'none';
        });
        window.addEventListener('offline', () => {
                document.getElementById('offline-indicator').style.display = 'block';
        });
        if (!navigator.onLine) document.getElementById('offline-indicator').style.display = 'block';
</script>
</body>
</html>