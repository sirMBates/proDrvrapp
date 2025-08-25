<footer class="mt-auto text-light text-center text-lg-start">
        <h5 class="<?= !urlIs('/register') ?: 'text-dark';?> text-center text-uppercase"><i>created by </i>softbigboy</h5>
        <p class="text-center"><a class="<?= urlIS('/register') ? 'text-dark' : 'text-light';?>" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
</footer>
<script src='../../dist/js/main.js'></script>
<!-- Load JQuery Color CDN(Content Delivery Network) -->
<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>
<?php
        require "includes/getscripts.php";
?>
</body>
</html>