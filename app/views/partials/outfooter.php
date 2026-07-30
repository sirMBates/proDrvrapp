<?php
use Dotenv\Dotenv;
require_once base_path("vendor/autoload.php");
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../', '.local.env');
$dotenv->load();
?>
<footer class="mt-auto text-light text-center text-lg-start">
        <h5 class="<?= !urlIs('/register') ?: 'text-dark';?> text-center text-uppercase"><i>created by </i>bigsoft</h5>
        <p class="text-center"><a class="<?= urlIS('/register') ? 'text-dark' : 'text-light';?>" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
</footer>
<script src='/dist/js/app.js'></script>
<!-- Load JQuery Color CDN(Content Delivery Network) -->
<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='<?= $_ENV['JQUERY_COLOR_INTEGRITY']?>' crossorigin='anonymous'></script>
<script src="/dist/js/pwa.js"></script>
<?php
        require base_path("app/includes/getscripts.php");
?>
</body>
</html>