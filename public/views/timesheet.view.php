<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>
<main class="container-fluid p-3 d-flex flex-column">
        <form id="" action="" method="POST">                        
                <div id="tsheet-data" class="card mb-auto">
                        <div class="card-header bg-besttrailsclr">
                                <h3 class="text-center text-capitalize text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>time sheet information</h3>
                        </div>
                        <div class="card-body overflow-x-auto">
                                <table class="table m-auto" style="width: 1300px;">
                                        <thead class="table-info text-capitalize">
                                                <tr>
                                                        <th scope="col">order#/conf#</th>
                                                        <th scope="col">destination</th>
                                                        <th scope="col">vehicle id</th>
                                                        <th scope="col">garage report<br>(date/time)</th>
                                                        <th scope="col">spot time</th>
                                                        <th scope="col">drop time</th>
                                                        <th scope="col">job details</th>
                                                        <th scope="col">end of duty<br>(date/time)</th>
                                                        <th scope="col">total shift hours</th>
                                                        <th scope="col">tolls</th>
                                                        <th scope="col">tip</th>
                                                        <th scope="col">job order pay</th>
                                                </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                                <tr>
                                                        <td scope="row" class="editable-data" data-inputs='[{"type":"number"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"textarea"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"number"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"datetime-local"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"time"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"time"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"textarea"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"datetime-local"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"text"}]'></td>
                                                        <td class="editable-data" data-inputs='[{"type":"checkbox"}]'>
                                                                <div class="form-check">
                                                                        <input id="tolls-yes-box" class="form-check-input" type="checkbox" value="yes" aria-label="yes-checkbox">
                                                                        <label class="form-check-label" for="tolls-yes-box">Yes</label>
                                                                </div>
                                                                <div class="form-check">
                                                                        <input id="tolls-no-box" class="form-check-input" type="checkbox" value="no" aria-label="no-checkbox">
                                                                        <label class="form-check-label" for="tolls-no-box">No</label>
                                                                </div>
                                                        </td>
                                                        <td class="editable-data" data-inputs='[{"type":"checkbox"}]'>
                                                                <div class="form-check">
                                                                        <input id="tip-yes-box" class="form-check-input" type="checkbox" value="yes" aria-label="yes-checkbox">
                                                                        <label class="form-check-label" for="tip-yes-box">Yes</label>
                                                                </div>
                                                                <div class="form-check">
                                                                        <input id="tip-no-box" class="form-check-input" type="checkbox" value="no" aria-label="no-checkbox">
                                                                        <label class="form-check-label" for="tip-no-box">No</label>
                                                                </div>
                                                        </td>
                                                        <td class="editable-data" data-inputs='[{"type":"text"}]'></td>
                                                </tr>
                                        </tbody>
                                </table>
                        </div>
                        <div class="card-footer d-flex flex-column align-items-center">
                                <div class="row my-2 col-lg-10">                        
                                        <button id="insert-info" type="button" name="insertinfo" class="text-capitalize btn btn-lg btn-outline-primary">add info</button>
                                </div>
                                <div class="row my-2 col-lg-10">
                                        <button id="update-info" type="button" name="updateinfo" class="text-capitalize btn btn-lg btn-outline-primary">update & save</button>
                                </div>
                                <div class="row my-2 col-lg-10">
                                        <button id="submit-info" type="button" name="submitinfo" class="text-capitalize btn btn-lg btn-outline-primary" disabled>submit</button>
                                </div>
                        </div>
                </div>
                <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
        </form>
</main>
<?php
        require "partials/footer.php";
?>