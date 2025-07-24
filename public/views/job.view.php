<?php
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        $alert = new \core\Flash;
        include "includes/flashmessage.php";
?>       
        <main class="w-100 d-flex flex-column justify-content-center p-1">
                <form class="" action="" method="">
                        <section id="top-header" class="card mb-auto">
                                <div class="card-header bg-besttrailsclr">
                                        <h3 class="text-center text-capitalize text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>dispatch work order</h3>
                                </div>
                                <div class="input-group">
                                        <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                </div>
                                <div class="card-body container table-responsive-md">                        
                                        <table id="sub_head_1" class="table">
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
                                                                <td scope="row">1800</td>
                                                                <td><?= $_SESSION['driver_id']; ?></td>
                                                                <td><?= $_SESSION['last_name'].', '.$_SESSION['first_name']; ?></td>
                                                                <td>99999</td>
                                                                <td>1</td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                </div>
                        </section>

                        <section id="dispatch_start_block" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">dispatch start</h4>
                                </div>
                                <div class="card-body container table-responsive-md">
                                        <table class="table">
                                                <thead class="table-info text-capitalize">
                                                        <tr>
                                                                <th scope="col">start date</th>
                                                                <th scope="col">garage time</th>
                                                                <th scope="col">leave date</th>
                                                                <th scope="col">spot time</th>
                                                                <th scope="col">leave time</th>
                                                        </tr>
                                                </thead>
                                                <tbody class="table-group-divider">
                                                        <tr>
                                                                <td scope="row"><?= date("m-d-Y", strtotime("2025-06-15")) ?></td>
                                                                <td><?= date("h:i A", strtotime('06:00')); ?></td>
                                                                <td><?= date("m-d-Y", strtotime("2025-06-15"))?></td>
                                                                <td><?= date("h:i A", strtotime('08:00')); ?></td>
                                                                <td><?= date("h:i A", strtotime('08:30')); ?></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                </div>
                        </section>

                        <section id="dispatch_end_block" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">dispatch end</h4>
                                </div>
                                <div class="card-body container table-responsive-md">
                                        <table class="table">
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
                                                                <td scope="row"><?= date("m-d-Y", strtotime("2025-06-15")); ?></td>
                                                                <td><?= date("h:i A", strtotime('21:00')); ?></td>
                                                                <td><?= date("h:i A", strtotime('21:15')); ?></td>
                                                                <td><?= date("m-d-Y", strtotime("2025-06-15")); ?></td>
                                                                <td><?= date("h:i A", strtotime('22:30')); ?></td>
                                                                <td><?= date("h:i A", strtotime('22:40')); ?></td>
                                                                <td>11:00</td>
                                                                <td>08.00</td>
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
        // Will only be avaiable if signature is needed.
        //include_once "includes/signpad.php";
?>
                        <section id="customer_details" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">customer details</h4>
                                </div>
                                <div class="card-body container table-responsive-md">
                                        <table class="table">
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
                                                                <td scope="row"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                </div>
                        </section>

                        <section id="driver_notes_box" class="card my-3">
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

                        <section id="workOrder_btns" class="vstack gap-2 col-lg-12 mx-auto">
                                <input type="hidden" name="_method" id="method" value="">
                                <button id="confirm_job" class="btn btn-outline-primary" type="button" value="" disabled>Confirm</button>
                                <button id="cancel_job" class="btn btn-outline-danger" type="button" value="" formaction="" disabled>Cancel/Unconfirm</button>
                                <button id="update_job_order" class="btn btn-outline-info" type="button" value="" formaction="" disabled>Edit</button>
                                <button id="submit_job_order" class="btn btn-outline-success" type="button" value="" formaction="" disabled>Complete Dispatch Order</button>
                        </section>
                </form>
        </main>       
<?php
        require "partials/footer.php";
?>