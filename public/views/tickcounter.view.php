<?php
require_once "partials/head.php";
require_once "partials/nav.php";
require_once "partials/banner.php";
?>
<main class="container-fluid m-2 p-2">
    <div class="card">
        <div class="card-header bg-besttrailsclr">
            <h2 class="text-capitalize text-center text-light">tick counter</h2>
        </div>
        <div class="card-body d-flex flex-column justify-content-center align-items-center">
            <p class="fs-5 text-center">Need to take a quick count? Just click the <u><b class="text-uppercase">tick</b></u> button below!</p>
            <div class="border border-2 rounded-2 border-besttrailsclr" style="width: 75px; height: 60px;">
                <span id="counter" class="fs-1 text-center">0</span>
            </div>
            <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
        </div>
        <div class="card-footer d-inline-flex justify-content-around">
            <button id="adder" class="btn btn-lg btn-primary text-uppercase" type="button">tick</button>
            <button id="reset" class="btn btn-lg btn-secondary text-uppercase" type="button">reset</button>
        </div>
    </div>
</main>
<?php
require_once "partials/footer.php";
?>