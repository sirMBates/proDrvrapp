<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        //include "includes/cus-modal.php";
        $alert = new \core\Flash;
        include "includes/flashmessage.php";
?>
<main class="container-fluid mb-1 p-1">
        <div class="card mb-auto">
                <div class="card-header bg-besttrailsclr text-btd-white-off">
                        <p class='h3 text-center text-capitalize'>driver information</p>
                </div>
                <div class="card-body overflow-auto">
                        <table id="dashboard-info" class="table">
                                <thead class="table-info text-capitalize">
                                        <tr>
                                                <th scope="col">driver name</th>
                                                <th scope="col">driver id</th>
                                                <th scope="col">garage report date</th>
                                                <th scope="col">garage report time</th>
                                                <th scope="col">loc. spot time</th>
                                                <th scope="col">status</th>
                                        </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                        <tr>
                                                <td scope="row"><?php if(!isset($_SESSION['first_name']) && !isset($_SESSION['last_name'])) {
                                                        $clientName = ucwords('pro driver');
                                                        echo $clientName;
                                                } else {
                                                        $clientName = ucwords($_SESSION['first_name'] . " " . $_SESSION['last_name']);
                                                        echo $clientName;
                                                }  ?></td>
                                                <td><?php if (!isset($_SESSION['driver_id'])) {
                                                        echo "0000";
                                                } else {
                                                        echo $_SESSION['driver_id'];
                                                } ?></td>
                                                <td>06-15-2025</td>
                                                <td>06:00</td>
                                                <td>08:00</td>
                                                <td></td>  
                                        </tr>
                                </tbody>
                                <form>
                                        <div class="input-group">
                                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                        <div class="input-group">
                                                <input id="drvrbday" type="hidden" class="form-control" disabled>
                                        </div>
                                </form>
                        </table>
                        <div class="container d-flex justify-content-lg-center">
                                <div id="update-status-con" class="mx-auto btn-group btn-group-lg" role="group" aria-label="Large button group">
                                        <button type="button" class="btn btn-outline-primary">Enroute to Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary">Check-In Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary">Enroute to Loc</button>
                                        <button type="button" class="btn btn-outline-primary">Arrived at Loc</button>
                                </div>
                        </div>
                </div>
        </div>
</main>
<?php
        require "partials/footer.php";
?>