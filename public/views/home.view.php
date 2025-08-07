<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        $alert = new core\Flash();
        include "includes/flashmessage.php";
        include "includes/custom-modal.php";
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
                                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                        <div class="input-group">
                                                <input id="drvrbday" type="hidden" class="form-control" value="<?php if (isset($_SESSION['birth_date'])) {
                                                        $formatDate = date('m-d-Y', strtotime($_SESSION['birth_date']));
                                                        echo $formatDate;
                                                }; ?>" disabled>
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