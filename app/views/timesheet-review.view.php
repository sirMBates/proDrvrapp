<?php
require "partials/head.php";
require "partials/banner.php";
include "partials/confirm-modal.php";
?>

<main class="container-fluid p-3">
    <section id="timesheet-review" class="card" aria-labelledby="timesheet-review-title">
        <div class="card-header bg-prodriverclr text-light">
            <h1 id="timesheet-review-title" class="h3 m-0 text-capitalize">
                Review Time Sheet
            </h1>
        </div>

        <div class="card-body">
            <header class="mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <p class="text-uppercase small mb-1">
                            Pay Period
                        </p>

                        <h2 id="timesheet-review-period" class="h5 mb-1">
                            Loading pay period…
                        </h2>

                        <span id="timesheet-review-status" class="badge text-bg-secondary">
                            Loading
                        </span>
                    </div>

                    <a href="/timesheet" class="btn btn-outline-secondary">
                        Back to Timesheet
                    </a>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    <strong>Final review:</strong>
                    Verify all assignment information before
                    submitting this Timesheet to payroll.
                </div>
            </header>

            <div id="timesheet-review-loading" class="py-5 text-center" role="status">
                <div class="spinner-border text-primary" aria-hidden="true"></div>

                <p class="mt-2 mb-0">
                    Loading Timesheet review…
                </p>
            </div>

            <div id="timesheet-review-error" class="alert alert-danger d-none" role="alert"></div>

            <div id="timesheet-review-content" class="d-none">
                <div id="timesheet-review-table-container" class="table-responsive d-none d-lg-block">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-info text-capitalize">
                            <tr>
                                <th scope="col">Order#<br><span class="fw-normal">Order ID</span></th>
                                <th scope="col">Destination<br><span class="fw-normal">From/To</span></th>
                                <th scope="col">Vehicle ID<br><span class="fw-normal">Bus #</span></th>
                                <th scope="col">Garage Report<br><span class="fw-normal">Date/Time</span></th>
                                <th scope="col">Spot Time</th>
                                <th scope="col">Drop Time</th>
                                <th scope="col">Job Details</th>
                                <th scope="col">End of Duty</th>
                                <th scope="col">Total Hours</th>
                                <th scope="col">Total Shift Hours</th>
                                <th scope="col">Tolls</th>
                                <th scope="col">Tip</th>
                                <th scope="col">Job Amount Paid</th>
                            </tr>
                        </thead>

                        <tbody id="timesheet-review-entries" class="table-group-divider"></tbody>
                    </table>
                </div>

                <div id="timesheet-review-mobile-entries" class="accordion d-lg-none"></div>

                <section id="timesheet-review-summary" class="border rounded p-3 mt-4" aria-labelledby="timesheet-review-summary-title">
                    <h2 id="timesheet-review-summary-title" class="h5">
                        Weekly Summary
                    </h2>

                    <dl class="row mb-0">
                        <dt class="col-7">
                            Completed assignments
                        </dt>

                        <dd id="timesheet-review-assignment-count" class="col-5 text-end">
                            —
                        </dd>

                        <dt class="col-7">
                            Total hours
                        </dt>

                        <dd id="timesheet-review-total-hours" class="col-5 text-end">
                            —
                        </dd>
                    </dl>
                </section>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex flex-column flex-md-row justify-content-end gap-3">
                <a href="/timesheet" class="btn btn-lg btn-outline-secondary">
                    Return Without Submitting
                </a>

                <form id="submit-timesheet-form" action="/submit-timesheet" method="POST" class="d-grid">
                    <input id="drvrToken" type="hidden" name="drvrtoken" value="<?= htmlspecialchars($_SESSION['drvr_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <input id="submit-period-start" type="hidden" name="period_start" value="">

                    <input id="submit-period-end" type="hidden" name="period_end" value="">

                    <button id="submit-timesheet" type="submit" class="btn btn-lg bg-prodriverclr text-light" disabled>
                        Submit to Payroll
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php
require "partials/footer.php";
?>