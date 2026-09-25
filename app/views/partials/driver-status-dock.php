<aside id="driver-status-dock" class="driver-status-dock navbar fixed-bottom bg-body-tertiary border-top shadow-lg d-print-none" aria-label="Driver status dock">
    <div class="container-xl flex-column gap-2 py-2">

        <!-- Current driver status and server connectivity -->
        <div class="d-flex align-items-stretch gap-2 w-100">
            <div id="statusMessage" class="driver-status-display form-control d-flex align-items-center justify-content-center text-center fw-semibold" role="status" aria-live="polite" aria-atomic="true">
                Current status: Loading…
            </div>

            <div id="connection-indicator" class="connection-indicator is-reconnecting" role="status" aria-live="polite" aria-atomic="true" aria-label="Server connection: checking">
                <span class="connection-indicator__light" aria-hidden="true"></span>

                <span class="connection-indicator__label">
                    Checking
                </span>
            </div>

        </div>

        <!-- Driver status controls -->
        <div id="driver-status-controls" class="driver-status-controls nav nav-pills nav-fill flex-nowrap gap-1 w-100" role="group" aria-label="Choose your current driver status">
            <button type="button" class="nav-link set-status" data-status-code="enroute_garage" aria-pressed="false">
                <i class="fa-solid fa-road status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">Enroute</span>
            </button>

            <button type="button" class="nav-link set-status" data-status-code="checked_in_garage" aria-pressed="false">
                <i class="fa-solid fa-map-pin status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">At Yard</span>
            </button>

            <button type="button" class="nav-link set-status" data-status-code="on_location" aria-pressed="false">
                <i class="fa-solid fa-location-dot status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">At Location</span>
            </button>

            <button type="button" class="nav-link set-status" data-status-code="working_assignment" aria-pressed="false">
                <i class="fa-solid fa-clipboard status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">On Assignment</span>
            </button>

            <button type="button" class="nav-link set-status" data-status-code="on_break" aria-pressed="false">
                <i class="fa-solid fa-mug-hot status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">On Break</span>
            </button>

            <button type="button" class="nav-link set-status" data-status-code="end_shift" aria-pressed="false">
                <i class="fa-solid fa-person-running status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">End Shift</span>
            </button>

            <button type="button" class="nav-link set-status status-emergency" data-status-code="emergency" aria-pressed="false">
                <i class="fa-solid fa-triangle-exclamation status-icon" aria-hidden="true"></i>
                <span class="spinner-border spinner-border-sm status-spinner d-none" aria-hidden="true"></span>
                <span class="status-label">Emergency</span>
            </button>
        </div>

    </div>
</aside>