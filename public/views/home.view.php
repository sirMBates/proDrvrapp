<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        include "includes/cus-modal.php";
        include "includes/errormsgs.php";
?>
        <main class="container-fluid mb-3 p-1">
                <div class="d-flex flex-row flex-wrap justify-content-evenly mb-3">
                        <div class="card mb-auto" style="width: 28rem;">
                                <div class="card-header bg-besttrailsclr text-btd-white-off">
                                        <p class='h3 text-center text-capitalize'>driver information</p>
                                </div>
                                <div class="card-body">
                                <form id="dashboardinfo" action="" method="">
                                        <div class="input-group my-1">
                                                <span class="input-group-text" id="identitylab">Full Name:</span>
                                                <input type="text" aria-label="full name" class="form-control" aria-describedby="#identitylab" disabled>
                                        </div>
                                        <div class="input-group my-1">
                                                <span class="input-group-text" id="idnumlab">Driver ID#:</span>
                                                <input type="number" aria-label="id num" class="form-control" aria-describedby="#idnumlab" disabled>
                                        </div>
                                        <div class="input-group my-1">
                                                <span class="input-group-text" id="reporttimelab">Report Time:</span>
                                                <input type="time" aria-label="report time" class="form-control" aria-describedby="#reporttimelab" disabled>
                                        </div>
                                        <div class="input-group my-1">
                                                <span class="input-group-text" id="spottimelab">Spot Time:</span>
                                                <input type="time" aria-label="spot time" class="form-control" aria-describedby="#spottimelab" disabled>
                                        </div>
                                        <div class="input-group my-1">
                                                <span class="input-group-text" id="drvrstatus">Status</span>
                                                <input type="text" aria-label="status" class="form-control" aria-describedby="#drvrstatus" disabled>
                                        </div>
                                        <div class="input-group">
                                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                        <div class="input-group">
                                                <input id="drvrbday" type="hidden" class="form-control" disabled>
                                        </div>
                                </div>
                                </form>
                        </div>
                </div>
                <div id="check_in_btns" class="d-grid gap-2 col-5 mx-auto">
                        <button class="btn btn-dark btn-lg">Enroute to Garage/Yard</button>
                        <button class="btn btn-primary btn-lg">Check-In at Garage/Yard</button>
                        <button class="btn btn-success btn-lg">Arrived at pickup location</button>
                </div>
        </main>
<?php
        require "partials/footer.php";
?>