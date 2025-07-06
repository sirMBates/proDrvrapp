<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        //include "includes/cus-modal.php";
        $alert = new \core\Flash;
        require "includes/flashmessage.php";
?>
        <main class="container-fluid d-flex flex-row flex-wrap justify-content-evenly mb-1 p-1">
                <div class="card mb-auto" style="width: 90rem;">
                        <div class="card-header bg-besttrailsclr text-btd-white-off">
                                <p class='h3 text-center text-capitalize'>driver information</p>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <table id="dashboard-info" class="table">
                                        <thead class="table-info">
                                                <tr>
                                                        <th scope="col">Driver Name</th>
                                                        <th scope="col">Driver ID#</th>
                                                        <th scope="col">Garage<br>Report Time</th>
                                                        <th scope="col">Spot Time<br>(PU Location)</th>
                                                        <th scope="col">Status</th>
                                                </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                                <tr>
                                                <td scope="row"><?php if(!isset($_SESSION['first_name']) && !isset($_SESSION['last_name'])) {
                                                        $clientName = ucwords('pro driver');
                                                        echo $clientName;;
                                                } else {
                                                        $clientName = ucwords($_SESSION['first_name'] . " " . $_SESSION['last_name']);
                                                        echo $clientName;
                                                }  ?></td>
                                                <td><?php if (!isset($_SESSION['driver_id'])) {
                                                        echo "0000";
                                                } else {
                                                        echo $_SESSION['driver_id'];
                                                } ?></td>
                                                <td>06:00</td>
                                                <td>08:00</td>
                                                <td><?php if (!isset($_GET['status']) && !isset($_SESSION['status'])) {
                                                        echo "N/A";
                                                } elseif (isset($_GET['status']) && !isset($_SESSION['status'])) {
                                                        $_SESSION['status'] = $_GET['status'];
                                                }
                                                
                                                if (isset($_SESSION['status'])) {
                                                        $currentStatus = ucwords($_SESSION['status']);
                                                        echo $currentStatus;
                                                } /*elseif (isset($_GET['status'])) {
                                                        $_SESSION['status'] = $_GET['status'];
                                                        $currentStatus = ucwords($_SESSION['status']);
                                                        echo $currentStatus;  
                                                } elseif (isset($_GET['status']) && $_SESSION['status'] === "enroute") {
                                                        $currentStatus = ucwords($_SESSION['status']);
                                                        echo "Enroute to Garage/Yard";
                                                } elseif (isset($_SESSION['status']) && $_SESSION['status'] === "checkedin") {
                                                        echo "Checked-In at Garage/Yard";
                                                } elseif (isset($_SESSION['status']) && $_SESSION['status'] === "arrived") {
                                                        echo "Arrived at Pickup Location";
                                                } else {
                                                        echo "N/A";
                                                }*/?></td>  
                                                </tr>
                                        </tbody>
                                        <!--<div class="input-group my-1">
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
                                        </div>-->
                                        <form>
                                        <div class="input-group">
                                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                        <div class="input-group">
                                                <input id="drvrbday" type="hidden" class="form-control" disabled>
                                        </div>
                                        </form>
                                </table>
                                <div id="check-in-btns" class="btn-group btn-group-sm" role="group" aria-label="Large button group">
                                        <button type="button" class="btn btn-outline-primary">Enroute to Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary">Check-In at Garage/Yard</button>
                                        <button type="button" class="btn btn-outline-primary">Enroute to pickup location</button>
                                        <button type="button" class="btn btn-outline-primary">Arrived at pickup location</button>
                                </div>
                        </div>
                </div>
        </main>
<?php
        require "partials/footer.php";
?>