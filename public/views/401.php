<?php
    $title = "Unauthorized";
    require "../../config.php";
    require "partials/outhead.php";
?>
    <div class="">
        <h1 class="text-capitalize text-danger"><img src="images-videos/404notfound.jpg" alt="Page not found" width="200">401 error</h1>
        <p class="fs-3">You're not authorize for this action.
            <a href="/" class="fs-2"><b>return to home</b></a>
        </p>
    </div>
<?php 
    require "partials/outfooter.php";
?>