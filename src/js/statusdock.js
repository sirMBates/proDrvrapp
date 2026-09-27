const DOCK_STORAGE_KEY = 'driverStatusDockState';

const DOCK_STATES = Object.freeze({
    EXPANDED: 'expanded',
    COLLAPSED: 'collapsed'
});

export class StatusDock {
    constructor(dock) {
        this.dock = dock;
        this.toggleButton = dock?.querySelector('#driver-status-dock-toggle') ?? null;

        this.preferredCollapsed = false;
        this.emergencyActive = false;
        this.statusUpdating = false;

        this.handleToggle = this.handleToggle.bind(this);
        this.handleEmergencyState = this.handleEmergencyState.bind(this);
        this.handleStatusLoading = this.handleStatusLoading.bind(this);
    }

    init() {
        if (!this.dock || !this.toggleButton) {
            return;
        }

        this.preferredCollapsed = localStorage.getItem(DOCK_STORAGE_KEY) === DOCK_STATES.COLLAPSED;
        this.emergencyActive = localStorage.getItem('isActiveEmergency') === 'true';
        this.toggleButton.addEventListener('click', this.handleToggle);

        window.addEventListener('emergency-state-changed', this.handleEmergencyState);
        window.addEventListener('driver-status-loading', this.handleStatusLoading);

        this.applyState(this.emergencyActive ? false : this.preferredCollapsed);
        this.updateToggleAvailability();
    }

    handleToggle() {
        if (this.emergencyActive || this.statusUpdating) {
            return;
        }

        this.preferredCollapsed = !this.dock.classList.contains('is-collapsed');
        localStorage.setItem(DOCK_STORAGE_KEY, this.preferredCollapsed ? DOCK_STATES.COLLAPSED : DOCK_STATES.EXPANDED);
        this.applyState(this.preferredCollapsed);
    }

    handleEmergencyState(event) {
        this.emergencyActive = event.detail?.active === true;
        if (this.emergencyActive) {
            this.applyState(false);
        } else {
            this.applyState(this.preferredCollapsed);
        }

        this.updateToggleAvailability();
    }

    handleStatusLoading(event) {
        this.statusUpdating = event.detail?.isLoading === true;
        if (this.statusUpdating) {
            this.applyState(false);
        } else if (!this.emergencyActive) {
            this.applyState(this.preferredCollapsed);
        }

        this.updateToggleAvailability();
    }

    applyState(collapsed) {
        this.dock.classList.toggle('is-collapsed', collapsed);
        this.toggleButton.setAttribute('aria-expanded', String(!collapsed));
        this.toggleButton.setAttribute('aria-label', collapsed ? 'Expand driver status dock' : 'Collapse driver status dock');
    }

    updateToggleAvailability() {
        const unavailable = this.emergencyActive || this.statusUpdating;
        this.toggleButton.disabled = unavailable;
        this.toggleButton.setAttribute('aria-disabled', String(unavailable));
    }

    destroy() {
        this.toggleButton?.removeEventListener('click', this.handleToggle);
        window.removeEventListener('emergency-state-changed', this.handleEmergencyState);
        window.removeEventListener('driver-status-loading', this.handleStatusLoading);
    }
};