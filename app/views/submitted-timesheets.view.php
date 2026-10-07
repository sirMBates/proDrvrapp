<?php

declare(strict_types=1);

require "partials/head.php";
require "partials/banner.php";
?>

<main class="container py-4">
    <section class="card shadow-sm" aria-labelledby="timesheet-history-title">
        <div class="card-header bg-prodriverclr text-light">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 id="timesheet-history-title" class="h3 mb-1">
                        Timesheet History
                    </h1>

                    <p class="mb-0">
                        View, download, or print your submitted Timesheets.
                    </p>
                </div>

                <a href="/timesheet" class="btn btn-light">
                    Current Timesheet
                </a>
            </div>
        </div>

        <div class="card-body">
            <div id="timesheet-history-loading" class="text-center py-5" role="status">
                <div class="spinner-border text-primary" aria-hidden="true"></div>

                <p class="mt-3 mb-0">
                    Loading submitted Timesheets…
                </p>
            </div>

            <div id="timesheet-history-error" class="alert alert-danger d-none" role="alert"></div>

            <section id="timesheet-history-empty" class="text-center py-5 d-none">
                <i class="fa-solid fa-file-circle-xmark fa-3x text-body-secondary mb-3" aria-hidden="true"></i>

                <h2 class="h4">
                    No submitted Timesheets
                </h2>

                <p class="text-body-secondary mb-0">
                    Submitted pay periods will appear here.
                </p>
            </section>

            <div id="timesheet-history-content" class="d-none">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-12 col-md-7 col-lg-5">
                        <label for="timesheet-history-period" class="form-label fw-semibold">
                            Select a pay period
                        </label>

                        <select id="timesheet-history-period" class="form-select"></select>
                    </div>
                </div>

                <article class="card border-primary">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div>
                                <span class="text-body-secondary small">
                                    Pay Period
                                </span>

                                <h2 id="timesheet-history-selected-period" class="h4 mb-0">
                                    —
                                </h2>
                            </div>

                            <span id="timesheet-history-status" class="badge text-bg-success">
                                Submitted
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-7 col-md-4">
                                Submission reference
                            </dt>

                            <dd id="timesheet-history-submission-id" class="col-5 col-md-8 text-end text-md-start">
                                —
                            </dd>

                            <dt class="col-7 col-md-4">
                                Assignments
                            </dt>

                            <dd id="timesheet-history-assignment-count" class="col-5 col-md-8 text-end text-md-start">
                                —
                            </dd>

                            <dt class="col-7 col-md-4">
                                Total hours
                            </dt>

                            <dd id="timesheet-history-total-hours" class="col-5 col-md-8 text-end text-md-start">
                                —
                            </dd>

                            <dt class="col-7 col-md-4">
                                Submitted
                            </dt>

                            <dd id="timesheet-history-submitted-at" class="col-5 col-md-8 text-end text-md-start">
                                —
                            </dd>
                        </dl>
                    </div>

                    <div class="card-footer">
                        <input id="drvrToken" type="hidden" name="drvrtoken" value="<?= htmlspecialchars($_SESSION['drvr_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <a id="timesheet-history-view" class="btn btn-outline-primary" href="#" target="_blank" rel="noopener">
                                <i class="fa-solid fa-eye me-2" aria-hidden="true"></i>
                                View PDF
                            </a>

                            <a id="timesheet-history-download" class="btn btn-outline-success" href="#">
                                <i class="fa-solid fa-download me-2" aria-hidden="true"></i>
                                Download PDF
                            </a>

                            <button id="timesheet-history-print" type="button" class="btn bg-prodriverclr text-light">
                                <i class="fa-solid fa-print me-2" aria-hidden="true"></i>
                                Print PDF
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php
require "partials/footer.php";
?>