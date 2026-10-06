<?php

declare(strict_types=1);

$logoutCsrfToken = htmlspecialchars((string) ($_SESSION['drvr_token'] ?? ''), ENT_QUOTES, 'UTF-8');

?>

<div id="logout-modal" class="modal fade" tabindex="-1" aria-labelledby="logout-modal-title" aria-describedby="logout-modal-description" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h2 id="logout-modal-title" class="modal-title fs-5">
                    Before You Sign Out
                </h2>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Return to service"></button>
            </div>

            <div class="modal-body">
                <input id="logout-csrf-token" type="hidden" value="<?= $logoutCsrfToken ?>">

                <p id="logout-modal-description" class="mb-2">
                    Would you like to end your shift before
                    signing out?
                </p>

                <p class="small text-body-secondary mb-0">
                    Ending your shift records your end-of-duty
                    time for the current operational day.
                    Signing out only will leave your shift open.
                </p>

                <div id="logout-modal-error" class="alert alert-danger d-none mt-3 mb-0" role="alert" aria-live="assertive"></div>
            </div>

            <div class="modal-footer flex-column gap-2">
                <button id="end-shift-logout-button" type="button" class="btn btn-primary w-100">
                    <span class="spinner-border spinner-border-sm d-none me-2" aria-hidden="true"></span>

                    <span class="logout-button-label">
                        End Shift &amp; Sign Out
                    </span>
                </button>

                <button id="logout-only-button" type="button" class="btn btn-outline-danger w-100">
                    <span class="spinner-border spinner-border-sm d-none me-2" aria-hidden="true"></span>

                    <span class="logout-button-label">
                        Sign Out Only
                    </span>
                </button>

                <button id="return-to-service-button" type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    Return to Service
                </button>
            </div>
        </div>
    </div>
</div>