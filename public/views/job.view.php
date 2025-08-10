<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
$alert = new \core\Flash;
include "includes/flashmessage.php";
include "includes/info-modal.php"
?>       
<main class="w-100 d-flex flex-column justify-content-center p-1">
        <form class="" action="" method="POST" novalidate>
                <section id="dispatch-info" class="card mb-auto">
                        <div class="card-header bg-besttrailsclr">
                                <h3 class="text-center text-capitalize text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>dispatch work order</h3>
                        </div>
                        <div class="input-group">
                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                        </div>
                        <div class="card-body overflow-x-auto">                        
                                <table id="tableA" class="table m-auto" style="width: 1200px;">
                                        <thead class="table-info text-capitalize">
                                                <tr>
                                                        <th scope="col">coach id</th>
                                                        <th scope="col">driver id</th>
                                                        <th scope="col">name</th>
                                                        <th scope="col">order#</th>
                                                        <th scope="col">num of coaches</th>
                                                </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                                <tr>
                                                        <td scope="row" class="editable-data">1800</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>99999</td>
                                                        <td>1</td>
                                                </tr>
                                                <tr>
                                                        <table id="tableB" class="table m-auto" style="width: 1200px;">
                                                                <thead class="table-info text-capitalize">
                                                                        <tr>
                                                                                <th>start date</th>
                                                                                <th>garage time</th>
                                                                                <th>leave date</th>
                                                                                <th>spot time</th>
                                                                                <th>leave time</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody class="table-group-divider">
                                                <tr>
                                                        <td scope="row">08-15-2025</td>
                                                        <td>06:00</td>
                                                        <td>08-15-2025</td>
                                                        <td>08:00</td>
                                                        <td>08:30</td>
                                                </tr>
                                        </tbody>
                                                        </table>
                                                </tr>
                                                <tr>
                                                        <table id="tableC" class="table m-auto" style="width: 1200px;">
                                                                <thead class="table-info text-capitalize">
                                                <tr>
                                                        <th class="col">return date</th>
                                                        <th class="col">drop time</th>
                                                        <th class="col">act. drop time</th>
                                                        <th class="col">end date</th>
                                                        <th class="col">end time</th>
                                                        <th class="col">actual end time</th>
                                                        <th class="col">total hrs</th>
                                                        <th class="col">driving time</th>
                                                </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                                <tr>
                                                        <td scope="row">08-15-2025</td>
                                                        <td>19:30</td>
                                                        <td class="editable-data"></td>
                                                        <td>08-15-2025</td>
                                                        <td>20:30</td>
                                                        <td class="editable-data"></td>
                                                        <td class="editable-data">12.00</td>
                                                        <td class="editable-data"></td>
                                                </tr>
                                        </tbody>
                                                        </table>
                                                </tr>
                                                <tr>
                                                        <table id="tableD" class="table m-auto" style="width: 1200px;">
                                        <thead class="table-info text-capitalize">
                                                <tr>
                                                        <th scope="col">origin</th>
                                                        <th scope="col">destination</th>
                                                        <th scope="col">group name</th>
                                                        <th scope="col">group leader(gL)</th>
                                                        <th scope="col">gL mobile number</th>
                                                        <th scope="col">customer name</th>
                                                        <th scope="col">customer phone</th>
                                                        <th scope="col">contact name</th>
                                                        <th scope="col">contact mobile</th>
                                                </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                                <tr>
                                                        <td scope="row">New York, NY</td>
                                                        <td>Philadelphia, PA</td>
                                                        <td>New Golden Era</td>
                                                        <td>Jane Doe</td>
                                                        <td>917-654-9783</td>
                                                        <td>Lazzy Tours Inc.</td>
                                                        <td>718-231-7498</td>
                                                        <td>John Doe</td>
                                                        <td>347-743-5891</td>
                                                </tr>
                                        </tbody>
                                </table>
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
        if (isset($_SESSION['signatureRequired']) && $_SESSION['signatureRequired'] === true) {
                require_once "partials/signpad.php";
        }
?>
                <section id="driver-notes-box" class="card my-3">
                        <div class="card-header bg-besttrailsclr">
                                <h4 class="text-center text-capitalize text-light">driver trip notes</h4>
                        </div>
                        <div class="card-body">
                                <div class="d-block mb-2 p-1">
                                        <label for="drvrNotes" class="h6 label-form text-capitalize">notes:</label>
                                        <textarea class="form-control bg-btd-textarea-clr text-dark" style="height: 200px;" id="drvrNotes" name="drvrNotes"></textarea>
                                </div>
                        </div>
                </section>

                <section id="workOrder-btns" class="vstack gap-2 col-lg-12 mx-auto">
                        <input type="hidden" name="__method" value="">
                        <button id="confirm-job" class="btn btn-outline-primary" type="button" disabled>Confirm</button>
                        <button id="cancel-job" class="btn btn-outline-danger" type="button" disabled>Cancel/Unconfirm</button>
                        <button id="edit" class="btn btn-outline-info" type="button" disabled>Edit</button>
                        <button id="submit-order" class="btn btn-outline-success" type="submit" disabled>Complete Dispatch Order</button>
                </section>
        </form>
</main>       
<?php
        require "partials/footer.php";
?>