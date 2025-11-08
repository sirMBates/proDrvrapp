<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>       
<main class="w-100 d-flex flex-column justify-content-center p-1">
        <div id="assignmentContainer">
                <form class="assignment-card" data-index="" action="" method="POST" novalidate>
                        <section id="dispatch-info" class="card mb-auto">
                                <div class="card-header bg-besttrailsclr">
                                        <h3 class="text-center text-capitalize text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>dispatch work order</h3>
                                </div>
                        
                                <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>

                                <div class="card-body overflow-x-auto">                        
                                        <table id="tableA" class="table m-auto" style="width: 1300px;">
                                                <thead class="table-info text-capitalize">
                                                        <tr class="text-center">
                                                                <th scope="col">coach id</th>
                                                                <th scope="col">operator id</th>
                                                                <th scope="col">operator name</th>
                                                                <th scope="col">order#</th>
                                                                <th scope="col">#coaches</th>
                                                        </tr>
                                                </thead>
                                                <tbody class="table-group-divider">
                                                        <tr class="text-center">
                                                                <td scope="row" class="editable-data" data-type="number"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                        <table id="tableB" class="table m-auto" style="width: 1300px;">
                                                <thead class="table-info text-capitalize">
                                                        <tr class="text-center">
                                                                <th scope="col">start date, time</th>
                                                                <th scope="col">spot time</th>
                                                                <th scope="col">leave date, time</th>
                                                                <th scope="col">return date, time</th>
                                                                <th scope="col">act. drop time</th>
                                                        </tr>
                                                </thead>
                                                <tbody class="table-group-divider">
                                                        <tr class="text-center">
                                                                <td scope="row"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="editable-data" data-type="time"></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                        <table id="tableC" class="table m-auto" style="width: 1300px;">
                                                <thead class="table-info text-capitalize">
                                                        <tr class="text-center">
                                                                <th scope="col">end date, time</th>
                                                                <th scope="col">act. end time</th>
                                                                <th scope="col">total hrs</th>
                                                                <th scope="col">driving time</th>
                                                                <th scope="col">origin</th>
                                                        </tr>
                                                </thead>
                                                <tbody class="table-group-divider">
                                                        <tr class="text-center">
                                                                <td scope="row"></td>
                                                                <td class="editable-data" data-type="datetime"></td>
                                                                <td class="editable-data" data-type="decimal" data-field="total_hrs"></td>
                                                                <td class="editable-data" data-type="decimal" data-field="driving_time"></td>
                                                                <td></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                        <table id="tableD" class="table m-auto" style="width: 1300px;">
                                                <thead class="table-info text-capitalize">
                                                        <tr class="text-center">
                                                                <th scope="col">destination</th>
                                                                <th scope="col">group name, group leader</th>
                                                                <th scope="col">group leader mobile</th>
                                                                <th scope="col">customer name, phone</th>
                                                                <th scope="col">contact name, mobile</th>
                                                        </tr>
                                                </thead>
                                                <tbody class="table-group-divider">
                                                        <tr class="text-center">
                                                                <td scope="row"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                </div>
                        </section>

                        <section id="customer_location_details" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">location details</h4>
                                </div>
                                <div class="card-body conatiner">
                                        <div class="d-block w-100 mb-2 p-1">
                                                <label for="pickup_details" class="h6 form-label text-capitalize"><u>pickup details:</u></label>
                                                <div class="col">
                                                        <textarea id="pickup_details" class="form-control bg-btd-textarea-clr text-dark" name="pickupdetails" style="height: 200px;"></textarea>
                                                </div>
                                        </div>
                                        <div class="d-block w-100 mb-2 p-1">
                                                <label for="destination_details" class="h6 form-label text-capitalize"><u>destination details:</u></label>
                                                <div class="col">
                                                        <textarea id="destination_details" class="form-control bg-btd-textarea-clr text-dark" name="destinationdetails" style="height: 200px;"></textarea>
                                                </div>
                                        </div>
                                </div>
                        </section>
<?php
        if (isset($_SESSION['signature_required']) && $_SESSION['signature_required'] === 1) {
                require_once "partials/signpad.php";
        }
?>
                        <section id="driver-notes-box" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">driver trip notes</h4>
                                </div>
                                <div class="card-body">
                                        <div class="d-block mb-2 p-1">
                                                <label for="drvr_notes" class="h6 label-form text-capitalize">notes:</label>
                                                <textarea class="form-control bg-btd-textarea-clr text-dark" style="height: 200px;" id="drvr_notes" name="drvrNotes"></textarea>
                                        </div>
                                </div>
                        </section>

                        <section id="workOrder-btns" class="vstack gap-2 col-lg-12 mx-auto">
                                <input type="hidden" name="__method" value="">
                                <button id="confirm-job" class="btn btn-outline-primary" type="button" name="confirm" disabled>Confirm</button>
                                <button id="cancel-job" class="btn btn-outline-danger" type="button" name="cancel" disabled>Cancel/Unconfirm</button>
                                <button id="edit" class="btn btn-outline-info" type="button" name="modify" disabled>Edit</button>
                                <button id="submit-order" class="btn btn-outline-success" type="button" name="assignment-complete" disabled>Complete Dispatch Order</button>
                        </section>
                </form>
        </div>
</main>       
<?php
require "partials/footer.php";
?>