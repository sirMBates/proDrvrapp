<?php
require "partials/head.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>

<main class="container-fluid p-3">
        <input id="drvrToken" type="hidden" value="<?= htmlspecialchars($_SESSION['drvr_token'], ENT_QUOTES, 'UTF-8') ?>">
        <section id="timesheet" class="card" aria-labelledby="timesheet-title">
                <div class="card-header bg-prodriverclr text-light">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                                <h1 id="timesheet-title" class="h3 m-0 text-capitalize">
                                        Time Sheet Information
                                </h1>

                                <button type="button" id="notifyinfo" class="btn btn-link p-0 text-light" aria-label="View timesheet information"><i class="fa-solid fa-circle-info fs-3" aria-hidden="true"></i>
                                </button>
                        </div>
                </div>

                <div class="card-body">
                        <header class="mb-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                        <div>
                                                <p class="text-uppercase small mb-1">
                                                        Pay Period
                                                </p>

                                                <h2 id="timesheet-period" class="h5 mb-1">
                                                        Loading pay period…
                                                </h2>

                                                <span id="timesheet-status" class="badge text-bg-secondary">
                                                        Loading
                                                </span>
                                        </div>

                                        <nav class="d-flex gap-2" aria-label="Timesheet pay periods">
                                                <button type="button" id="previous-timesheet" class="btn btn-outline-secondary">
                                                        Previous
                                                </button>

                                                <button type="button" id="next-timesheet" class="btn btn-outline-secondary">
                                                        Next
                                                </button>
                                        </nav>
                                </div>

                                <div id="timesheet-notice" class="alert alert-warning mt-3 mb-0 d-none" role="alert"></div>
                        </header>

                        <div id="timesheet-loading" class="py-5 text-center" role="status">
                                <div class="spinner-border text-primary" aria-hidden="true"></div>

                                <p class="mt-2 mb-0">Loading timesheet…</p>
                        </div>

                        <div id="timesheet-error" class="alert alert-danger d-none" role="alert"></div>

                        <div id="timesheet-empty" class="text-center py-5 d-none">
                                <i class="fa-regular fa-calendar-xmark fs-1 mb-3" aria-hidden="true"></i>

                                <p class="mb-0">
                                        No completed assignments are recorded for this pay period.
                                </p>
                        </div>

                        <div id="timesheet-content" class="d-none">
                                <!-- Desktop and larger-screen presentation -->
                                <div id="timesheet-table-container" class="table-responsive d-none d-lg-block">
                                        <table class="table table-striped align-middle mb-0">
                                                <thead class="table-info text-capitalize">
                                                        <tr>
                                                                <th scope="col">Order#<br>
                                                                        <span class="fw-normal">Order ID</span>
                                                                </th>
                                                                <th scope="col">Destination<br>
                                                                        <span class="fw-normal">(From/To)</span>
                                                                </th>
                                                                <th scope="col">Vehicle ID<br>
                                                                        <span class="fw-normal">Bus #</span>
                                                                </th>
                                                                <th scope="col">Garage Report Date</th>
                                                                <th scope="col">Spot Time</th>
                                                                <th scope="col">Drop Time</th>
                                                                <th scope="col">Job Details</th>
                                                                <th scope="col">End of Duty<br>
                                                                        <span class="fw-normal">(Date/Time)</span>
                                                                </th>
                                                                <th scope="col">Total Job Hours</th>
                                                                <th scope="col">Total Shift Hours</th>
                                                                <th scope="col">Tolls</th>
                                                                <th scope="col">Tip</th>
                                                                <th scope="col">Job Amount Paid</th>
                                                        </tr>
                                                </thead>

                                                <tbody id="timesheet-entries" class="table-group-divider"></tbody>
                                        </table>
                                </div>

                                <!-- Phone and narrow-screen presentation -->
                                <div id="timesheet-mobile-entries" class="accordion d-lg-none"></div>
                        </div>

                        <section id="timesheet-summary" class="border rounded p-3 mt-4 d-none" aria-labelledby="timesheet-summary-title">
                                <h2 id="timesheet-summary-title" class="h5">
                                        Weekly Summary
                                </h2>

                                <dl class="row mb-0">
                                        <dt class="col-7">Completed assignments</dt>
                                        <dd id="timesheet-assignment-count" class="col-5 text-end">
                                                —
                                        </dd>

                                        <dt class="col-7">Total hours</dt>
                                        <dd id="timesheet-total-hours" class="col-5 text-end">
                                                —
                                        </dd>
                                </dl>
                        </section>
                </div>

                <div class="card-footer">
                        <div id="timesheet-edit-actions" class="d-flex flex-column flex-md-row justify-content-end gap-3">
                                <button id="save-timesheet" type="button" class="btn btn-lg btn-outline-primary" disabled>
                                        Save &amp; Lock
                                </button>

                                <button id="review-timesheet" type="button" class="btn btn-lg bg-prodriverclr text-light" disabled>
                                        Review &amp; Submit
                                </button>
                        </div>

                        <div id="timesheet-document-actions" class="d-flex flex-column flex-md-row justify-content-end gap-3 d-none">
                                <button id="view-timesheet-pdf" type="button" class="btn btn-outline-primary">
                                        View PDF
                                </button>

                                <button id="download-timesheet-pdf" type="button" class="btn btn-outline-primary">
                                        Download PDF
                                </button>

                                <button id="print-timesheet-pdf" type="button" class="btn btn-outline-primary">
                                        Print
                                </button>
                        </div>
                </div>
        </section>
</main>

<?php 
require "partials/footer.php";
?>
