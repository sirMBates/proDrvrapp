import { showFlashAlert } from "./helpers.js";
import { handleStatusFetch } from "./pwa.js";

const STATUS_MAP = Object.freeze({
    enroute_garage: 'Enroute to garage',
    checked_in_garage: 'Arrived at garage',
    on_location: 'Arrived at location',
    working_assignment: 'On assignment',
    on_break: 'On Break',
    end_shift: 'End of Shift',
    emergency: 'Emergency'
});
export class ChangeStatus {
    constructor(buttons, drvrToken, statusDisplay) {
        this.buttons = [...buttons];
        this.drvrToken = drvrToken;
        this.statusDisplay = statusDisplay;
        this.drvrStatus = '';
        this.isUpdating = false;
    };

    init() {
        if (this.buttons.length === 0 || !this.drvrToken || !this.statusDisplay) {
            return;
        }

        this.buttons.forEach(button => {
            button.addEventListener('click', (e) => this.updateDrvrStatusControl(e));
        });

        window.addEventListener('driver-status-updated', (e) => {
            const statusRecord = e.detail;
            if (!statusRecord?.driverStatus) {
                return;
            }

            this.setConfirmedStatus(statusRecord.driverStatus);
            console.log(`Attaching status controls: ${this.buttons.length}`);
        });
    };

    async updateDrvrStatusControl(e) {
        console.log('Status button clicked:', e.currentTarget.dataset.statusCode);
        if (this.isUpdating) {
            return;
        }

        const button = e.currentTarget;
        const statusCode = button.dataset.statusCode;
        const newStatus = STATUS_MAP[statusCode];

        if (!newStatus) {
            showFlashAlert('error', 'Invalid driver status.');
            return;
        }

        if (newStatus === this.drvrStatus) {
            return;
        }

        await this.updateDBStatus(this.drvrToken, newStatus, button);
    };

    async updateDBStatus(token, driverStatus, button) {
        this.setLoading(button, true);
        try {
            const result = await handleStatusFetch({
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({
                    drvrStatus: driverStatus
                })
            });

            if (result?.status === 'success') {
                window.dispatchEvent(new CustomEvent('driver-status-updated', { detail: result.data }));
                showFlashAlert(result.status, result.message);
                return;
            } 
            
            if (result?.status === 'queued') {
                showFlashAlert('info', result.message || 'Status queued - it will sync when you\'re back online.');
                return;                
            }
            
            showFlashAlert('error', result?.message || 'Your status could not be updated.');
        } catch (error) {
            console.error('Error updating driver status:', error);
            showFlashAlert('error', 'Error saving status.');
        } finally {
            this.setLoading(button, false);
        }
    };

    setLoading(clickedButton, isLoading) {
        this.isUpdating = isLoading;

        this.buttons.forEach(button => {
            button.disabled = isLoading;
        });

        clickedButton.setAttribute('aria-busy', String(isLoading));
        clickedButton.querySelector('.status-icon')?.classList.toggle('d-none', isLoading);
        clickedButton.querySelector('.status-spinner')?.classList.toggle('d-none', !isLoading);
    };

    setConfirmedStatus(driverStatus) {
        const validStatuses = Object.values(STATUS_MAP);
        if (!driverStatus || !validStatuses.includes(driverStatus)) {
            this.drvrStatus = '';

            this.buttons.forEach(button => {
                button.classList.remove('active');
                button.setAttribute('aria-pressed', 'false');
            });

            this.statusDisplay.textContent = 'Current status: Not set';

            localStorage.removeItem('status');
            sessionStorage.removeItem('status');
            return;
        }

        this.drvrStatus = driverStatus;

        this.buttons.forEach(button => {
            const buttonStatus = STATUS_MAP[button.dataset.statusCode];
            const isActive = buttonStatus === driverStatus;

            button.classList.toggle('active', isActive);

            button.setAttribute('aria-pressed', String(isActive));
        });

        this.statusDisplay.textContent = `Current status: ${driverStatus}`;
        localStorage.setItem('status', driverStatus);
    };
};
