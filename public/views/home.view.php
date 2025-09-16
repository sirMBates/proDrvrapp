<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        $alert = new core\Flash();
        include "partials/flashmessage.php";
        include "partials/info-modal.php";
        include "partials/custom-modal.php";
?>
<main class="container-fluid mb-1">
        <div class="card mb-auto">
                <div class="card-header bg-besttrailsclr text-btd-white-off">
                        <h3 class='text-center text-capitalize'><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>driver information</h3>
                </div>
                <div class="card-body overflow-x-auto">
                        <table id="dashboard-info" class="table m-auto" style="width: 1200px;">
                                <thead class="table-info text-capitalize">
                                        <tr>
                                                <th scope="col">operator name</th>
                                                <th scope="col">operator id</th>
                                                <th scope="col">garage report date</th>
                                                <th scope="col">garage report time</th>
                                                <th scope="col">loc. spot time</th>
                                                <th scope="col">status</th>
                                        </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                        <tr>
                                                <td scope="row"></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>  
                                        </tr>
                                </tbody>
                                <form>
                                        <div class="input-group">
                                                <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                        <div class="input-group">
                                                <input id="drvrbday" type="hidden" class="form-control" value="<?php if (isset($_SESSION['birth_date'])) {
                                                        $formatDate = date('m-d-Y', strtotime($_SESSION['birth_date']));
                                                        echo $formatDate;
                                                }; ?>" disabled>
                                        </div>
                                </form>
                        </table>
                </div>
                <div class="card-footer d-inline-flex justify-content-center">
                        <div class="my-2 overflow-x-auto">
                                <div id="update-status-con" class="mx-auto btn-group btn-group-lg" role="group" aria-label="Large button group">
                                        <button type="button" class="btn btn-outline-primary set-status status-enroute-garage">Enroute to Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary set-status status-checkedin-garage">Check-In Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary set-status status-enroute-location">Enroute to Loc</button>
                                        <button type="button" class="btn btn-outline-primary set-status status-onlocation">Arrived at Loc</button>
                                        <button type="button" class="btn btn-outline-primary set-status status-working-assignment">On Assignment</button>
                                        <button type="button" class="btn btn-danger set-status status-emergency">Emergency</button>
                                </div>
                        </div>
                </div>
        </div>
</main>
<?php
        require "partials/footer.php";
?>