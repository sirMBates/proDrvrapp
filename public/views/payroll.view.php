<?php
        require "../config.php";
        require "partials/head.php";
        require "partials/nav.php";
        require "partials/banner.php";
        include "includes/cus-modal.php";
        include "includes/errormsgs.php";
?>
        <main class="container-fluid p-3 d-flex flex-column">
                <form id="" action="" method="">
                        <div id="drvrEntryData" class="w-100 carousel slide carousel-fade mb-3">
                                <div class="carousel-inner">
                                        <div class="carousel-item active">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Order#/Conf#</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="orderConfId" class="form-control form-control-lg" type="number" inputmode="numeric" placeholder="Enter order/conf# here">
                                                                        <label for="orderConfId">Enter order/conf# here</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Destination</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <textarea id="destination" class="form-control" style="height: 100px;" placeholder="Brief location details"></textarea>
                                                                        <label for="destination">Brief location details</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Vehicle Id</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="vehicleId" class="form-control form-control-lg" list="vehicleid" placeholder="Enter vehicle number">
                                                                                <datalist id="vehicleid"></datalist>
                                                                        <label for="vehicleId">Enter vehicle number</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Garage report (date/time)</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="reportDate" class="form-control form-control-lg" type="datetime-local" placeholder="Enter report date">
                                                                        <label for="reportDate">Enter report date</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Spot Time</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="spotTime" class="form-control form-control-lg" type="time" placeholder="Pickup location time" style="width: 225px;">
                                                                        <label for="spotTime">Pickup location time</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Drop Time</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="dropTime" class="form-control form-control-lg" type="time" placeholder="Drop-off/return location time" style="width: 225px;">
                                                                        <label for="dropTime">Drop-off/return location time</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Job details</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <textarea id="jobDetails" class="form-control form-control-lg" style="height: 100px;" placeholder="Brief desc of job/work order"></textarea>
                                                                        <label for="jobDetails">Brief desc of job/work order</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">End of duty (date/time)</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="endTime" class="form-control form-control-lg" type="datetime-local" placeholder="Your finish time">
                                                                        <label for="endTime">Your finish time</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Total shift hours</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="totalHrs" class="form-control form-control-lg" type="text" placeholder="Total time on duty" disabled>
                                                                        <label for="totalHrs">Total time on duty</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div id="tollCard" class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Tolls</h5>
                                                        <div class="card-body d-flex flex-column align-items-start">
                                                                <div class="input-group mb-3">
                                                                        <div class="input-group-text">
                                                                                <input class="form-check-input" type="checkbox" value="yes" aria-label="yes-checkbox">
                                                                        </div>
                                                                                <input type="text" class="form-control" value="Yes" aria-label="yes-value" disabled>
                                                                </div>
                                                                <div class="input-group mb-3">
                                                                        <div class="input-group-text">
                                                                                <input class="form-check-input" type="checkbox" value="no" aria-label="no-checkbox">
                                                                        </div>
                                                                                <input type="text" class="form-control" value="No" aria-label="no-value" disabled>
                                                                </div>
                                                                <button class="btn btn-primary mt-2 align-self-center">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div id="tipCard" class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Tip</h5>
                                                        <div class="card-body d-flex flex-column align-items-start">
                                                                <div class="input-group mb-3">
                                                                        <div class="input-group-text">
                                                                                <input class="form-check-input" type="checkbox" value="yes" aria-label="yes-checkbox">
                                                                        </div>
                                                                                <input type="text" class="form-control" value="Yes" aria-label="yes-value" disabled>
                                                                </div>
                                                                <div class="input-group mb-3">
                                                                        <div class="input-group-text">
                                                                                <input class="form-check-input" type="checkbox" value="no" aria-label="no-checkbox">
                                                                        </div>
                                                                                <input type="text" class="form-control" value="No" aria-label="no-value" disabled>
                                                                </div>
                                                                <button class="btn btn-primary mt-2 align-self-center">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="carousel-item">
                                                <div class="card">
                                                        <h5 class="card-header bg-besttrailsclr text-light">Job\Work order Pay</h5>
                                                        <div class="card-body d-flex flex-column align-items-center">
                                                                <div class="form-floating">
                                                                        <input id="jobPay" class="form-control form-control-lg" type="text" placeholder="$0.00">
                                                                        <label for="jobPay">Amount\pay rate</label>
                                                                </div>
                                                                <button class="btn btn-primary mt-2 align-self-center">Add</button>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#drvrEntryData" data-bs-slide="prev">
                                        <span class="fa-solid fa-circle-chevron-left text-btd-blue-bright" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#drvrEntryData" data-bs-slide="next">
                                        <span class="fa-solid fa-circle-chevron-right text-btd-blue-bright" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                </button>
                        </div>

                        <div class="w-100 text-center text-capitalize border rounded-top mb-3 d-flex nowrap overflow-x-auto" id="entriesDataCon">
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="orderNum" type="text" readonly>
                                                <label for="orderNum"><b>Order#/Conf#</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <textarea class="form-control" id="locDetails" readonly></textarea>
                                                <label for="locDetails"><b>destination</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="busId" type="text" readonly>
                                                <label for="busId"><b>Vehicle id</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="garageReport" type="text" readonly>
                                                <label for="garageReport"><b>Garage report</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="locSpotTime" type="text" readonly>
                                                <label for="locSpotTime"><b>spot time</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="locDropTime" type="text" readonly>
                                                <label for="locDropTime"><b>drop time</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <textarea class="form-control" id="jobDescDetails" readonly></textarea>
                                                <label for="jobDescDetails"><b>Job details</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="endShift" type="text" readonly>
                                                <label for="endShift"><b>End of duty</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="shiftTotHrs" type="text" readonly>
                                                <label for="shiftTotHrs"><b>Total shift hours</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="tollUsed" type="text" readonly>
                                                <label for="tollsUsed"><b>tolls</b></label>
                                        </div>        
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="tipRecd" type="text" readonly>
                                                <label for="tipRecd"><b>tip</b></label>
                                        </div>
                                </div>
                                <div class="grid col-6 rounded border p-2 m-2 col-2">
                                        <div class="form-floating">
                                                <input class="form-control" id="jobPayAmount" type="text" readonly>
                                                <label for="jobPayAmount"><b>Job\Work order Pay</b></label>
                                        </div>
                                </div>
                        </div>

                        <div class="vstack gap-2 col-md-12 mx-auto">
                                <button class="btn btn-primary text-capitalize" type="button" name="btn1">add<br>info</button>
                                <button class="btn btn-sm btn-primary text-capitalize" type="button" name="btn2" formaction="">update<br>& save</button>
                                <button class="btn btn-sm btn-success text-capitalize" type="button" name="btn3" formaction="" disabled>submit<br>payroll</button>
                        </div>
                </form>
        </main>

<?php
        require "partials/footer.php";
?>