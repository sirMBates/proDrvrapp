<?php
        $title = "Pro Driver - Dispatch Order";
        require_once "../includes/head.php";
?>

<body class="d-flex flex-column vh-100 overflow-x-hidden noprint">
<?php
        include_once "../includes/navbar.php";
        include_once "../includes/header.php";
        include_once "../includes/cus-modal.php";
?>       
        <main class="container-fluid my-3">
                <form class="" action="" method="">
                        <section id="top-header" class="card">
                                <div class="card-header bg-besttrailsclr">
                                        <h3 class="text-center text-capitalize text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>dispatch work order</h3>
                                </div>
                                <div class="card-body d-sm-inline-flex justify-content-between">                        
                                        <div id="sub_head_1" class="p-2 overflow-x-auto">
                                                <div>
                                                        <div class="input-group mb-2">
                                                                <label for="coach_id" class="h5 col-form-label">Coach ID:</label>
                                                                <input type="number" id="coach_id" class="form-control bg-transparent border-0" name="coachid" placeholder="1800" disabled>
                                                        </div>
                                                </div>
                                                <div>
                                                        <div class="input-group mb-2">
                                                                <label for="driver_id" class="h5 col-form-label">Driver ID:</label>
                                                                <input type="number" id="driver_id" class="form-control bg-transparent border-0" name="driverid" placeholder="1000" disabled>
                                                        </div>
                                                </div>
                                                <div>
                                                        <div class="input-group mb-2">
                                                                <label for="driver_name" class="h5 col-form-label">Name:</label>
                                                                <input type="text" id="driver_name" class="form-control bg-transparent border-0" name="drivername" placeholder="John Doe" disabled>
                                                        </div>
                                                </div>
                                                <div>
                                                        <div class="input-group mb-2">
                                                                <label for="order_id" class="h5 col-form-label">Order #:</label>
                                                                <input type="number" id="order_id" class="form-control bg-transparent border-0" name="orderid" placeholder="99999" disabled>
                                                        </div>
                                                </div>
                                                <div>
                                                        <div class="input-group mb-2">
                                                                <label for="number_of_coaches" class="h5 col-form-label"># Coaches:</label>
                                                                <input type="text" id="number_of_coaches" class="form-control bg-transparent border-0" name="numOfCoaches" placeholder="1" disabled>
                                                        </div>
                                                </div>
                                        </div>
                        </section>

                        <section id="dispatch_start_block" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">dispatch start</h4>
                                </div>
                                <div class="card-body d-sm-inline-flex justify-content-evenly flex-sm-wrap">
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="start_date" class="form-label text-capitalize pe-2">start date</label>
                                                        <div class="col-lg-6">                                          
                                                                <input type="date" id="start_date" class="form-control" name="startdate" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="garage_time" class="form-label text-capitalize pe-2">garage time</label>
                                                        <div class="col-lg-5">                                          
                                                                <input type="time" id="garage_time" class="form-control" name="garagetime" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="leave_date" class="form-label text-capitalize pe-2">leave date</label>
                                                        <div class="">                                          
                                                                <input type="date" id="leave_date" class="form-control" name="leavedate" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="spot_time" class="form-label text-capitalize pe-2">spot time</label>
                                                        <div class="col-lg-5">                                          
                                                                <input type="time" id="spot_time" class="form-control" name="spottime" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="leave_time" class="form-label text-capitalize pe-2">leave time</label>
                                                        <div class="col-lg-5">                                          
                                                                <input type="time" id="leave_time" class="form-control" name="leavetime" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </section>

                        <section id="dispatch_end_block" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">dispatch end</h4>
                                </div>
                                <div class="card-body d-sm-inline-flex justify-content-evenly flex-sm-wrap">
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="return_date" class="form-label text-capitalize pe-2">return date</label>
                                                        <div class="col">                                          
                                                                <input type="date" id="return_date" class="form-control" name="returndate" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="drop_time" class="form-label text-capitalize pe-2">drop time</label>
                                                        <div class="col">                                          
                                                                <input type="time" id="drop_time" class="form-control" name="droptime" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="actual_drop_time" class="form-label text-capitalize pe-1">act. drop time</label>
                                                        <div class="col">                                          
                                                                <input type="time" id="actual_drop_time" class="form-control" name="actualdroptime">
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="end_date" class="form-label text-capitalize pe-2">end date</label>
                                                        <div class="col">                                          
                                                                <input type="date" id="end_date" class="form-control" name="enddate" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="mb-1 p-1">
                                                <div class="input-group">
                                                        <label for="end_time" class="form-label text-capitalize pe-2">end time</label>
                                                        <div class="col">                                          
                                                                <input type="time" id="end_time" class="form-control" name="endtime" disabled>
                                                        </div>
                                                </div>
                                        </div>
                                </div>

                                <div id="end-shift-times" class="d-flex justify-content-sm-center">
                                        <div class="d-sm-inline-flex">
                                                <div class="mb-1 p-2">
                                                        <div class="input-group">
                                                                <label for="actual_end_time" class="form-label text-capitalize pe-2">actual end time</label>
                                                                <div class="col">                                          
                                                                        <input type="time" id="actual_end_time" class="form-control" name="actualendtime">
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="mb-1 p-2">
                                                        <div class="input-group">
                                                                <label for="total_hours" class="form-label text-capitalize pe-2">total hrs.</label>
                                                                <div class="col">                                          
                                                                        <input type="text" id="total_hours" class="form-control" name="totalhours" placeholder="0.00 hrs" readonly>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="mb-1 p-2">
                                                        <div class="input-group">
                                                                <label for="drive_time" class="form-label text-capitalize pe-2">driving time</label>
                                                                <div class="col">                                          
                                                                        <input type="text" id="drive_time" class="form-control" name="drivetime" placeholder="0.00 hrs" disabled>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </section>

                        <section id="customer_location_details" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">location details</h4>
                                </div>
                                <div class="card-body d-sm-inline-flex">
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
        //include_once "../includes/signpad.php";
?>
                        <section id="customer_details" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">customer details</h4>
                                </div>
                                <div class="card-body d-flex flex-row flex-wrap justify-content-center">
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">origin:</span>
                                                <input type="text" class="form-control" aria-label="Origin" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">destination:</span>
                                                <input type="text" class="form-control" aria-label="Destination" disabled>
                                        </div>                                
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">group name:</span>
                                                <input type="text" class="form-control" aria-label="Group leader" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">group leader(gL):</span>
                                                <input type="text" class="form-control" aria-label="Group leader name" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">gL mobile number:</span>
                                                <input type="text" class="form-control" aria-label="Group leader mobile" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">customer name:</span>
                                                <input type="text" class="form-control" aria-label="Customer name" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">customer phone:</span>
                                                <input type="text" class="form-control" aria-label="Customer phone" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">contact name:</span>
                                                <input type="text" class="form-control" aria-label="Contact name" disabled>
                                        </div>
                                        <div class="input-group my-2">
                                                <span class="input-group-text text-capitalize">contact mobile:</span>
                                                <input type="text" class="form-control" aria-label="Contact mobile" disabled>
                                        </div>
                                </div>
                        </section>

                        <section id="driver_notes_box" class="card my-3">
                                <div class="card-header bg-besttrailsclr">
                                        <h4 class="text-center text-capitalize text-light">driver trip notes</h4>
                                </div>
                                <div class="card-body">
                                        <div class="d-block w-100 mb-2 p-1">
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
        include_once "../includes/footer.php";
        require_once "../includes/getscripts.php";
?>       
</body>
</html>