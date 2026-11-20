<!-- Session Expiration Modal -->
<div id="session-expiration-modal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="session-expiration-label" aria-describedby="session-expiration-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning-subtle">
                <i class="fa-solid fa-clock me-2 text-warning"></i>

                <h4 class="modal-title fs-5" id="session-expiration-label">
                    Session Expiration Warning
                </h4>
            </div>

            <div class="modal-body text-center">

                <p id="session-expiration-message" class="fs-5 mb-3">
                    Your session is about to expire due to inactivity.
                </p>

                <div id="session-expiration-countdown" class="display-4 fw-bold text-warning mb-2" role="timer" aria-label="Time remaining before session expiration">
                    05:00
                </div>

                <p class="text-body-secondary mb-0">
                    Time remaining
                </p>

            </div>

            <div class="modal-footer justify-content-center">

                <button id="session-stay-signed-in" type="button" class="btn btn-primary">
                    Stay Signed In
                </button>

                <button id="session-sign-out" type="button" class="btn btn-secondary">
                    Sign Out
                </button>

            </div>

        </div>
    </div>
</div>
