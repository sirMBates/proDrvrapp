import { fetchDrvr } from './helpers.js';

const LOGOUT_ACTIONS = Object.freeze({
    END_SHIFT: 'end_shift_and_logout',
    LOGOUT_ONLY: 'logout_only'
});

export class LogoutManager {
    constructor({
        logoutTrigger,
        driverOffcanvas,
        csrfToken,
        modal,
        endShiftButton,
        logoutOnlyButton,
        returnToServiceButton,
        errorDisplay
    }) {
        this.logoutTrigger = logoutTrigger;
        this.driverOffcanvas = driverOffcanvas;
        this.csrfToken = csrfToken;
        this.modal = modal;
        this.endShiftButton = endShiftButton;
        this.logoutOnlyButton = logoutOnlyButton;
        this.returnToServiceButton = returnToServiceButton;
        this.errorDisplay = errorDisplay;

        this.isSubmitting = false;
    };

    init() {
        if (!this.logoutTrigger || !this.driverOffcanvas || !this.csrfToken || !this.modal || !this.endShiftButton || !this.logoutOnlyButton || !this.returnToServiceButton || !this.errorDisplay) {
            return;
        }

        this.logoutTrigger.addEventListener('click', () => {
            this.openModal();
        });

        this.endShiftButton.addEventListener('click', () => {
            this.submitLogout(LOGOUT_ACTIONS.END_SHIFT, this.endShiftButton);
        });

        this.logoutOnlyButton.addEventListener('click', () => {
            this.submitLogout(LOGOUT_ACTIONS.LOGOUT_ONLY, this.logoutOnlyButton);
        });

        this.returnToServiceButton.addEventListener('click', () => {
            this.clearError();
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            this.clearError();
        });
    };

    async submitLogout(action, clickedButton) {
        if (this.isSubmitting) {
            return;
        }

        this.clearError();
        this.setLoading(clickedButton, true);

        try {
            const data = await fetchDrvr('/logout', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({
                    action
                })
            });

            if (data?.status !== 'success') {
                this.showError(data?.message || 'You could not be signed out.');
                return;
            }

            const redirect = data?.data?.redirect || '/signin?success=logged+out';
            this.clearDriverSessionState();
            window.location.assign(redirect);
        } catch (error) {
            console.error('[LOGOUT ERROR]', error);
            this.showError(error?.message || 'You could not be signed out. Please try again.');
        } finally {
            this.setLoading(clickedButton, false);
        }
    };

    setLoading(clickedButton, isLoading) {
        this.isSubmitting = isLoading;

        const buttons = [
            this.endShiftButton,
            this.logoutOnlyButton,
            this.returnToServiceButton
        ];

        buttons.forEach(button => {
            button.disabled = isLoading;
        });
        clickedButton.setAttribute('aria-busy', String(isLoading));

        const spinner = clickedButton.querySelector('.spinner-border');
        spinner?.classList.toggle('d-none', !isLoading);
    }

    showError(message) {
        this.errorDisplay.textContent = message;
        this.errorDisplay.classList.remove('d-none');
    }

    clearError() {
        this.errorDisplay.textContent = '';
        this.errorDisplay.classList.add('d-none');
    }

    openModal() {
        this.clearError();

        const modalInstance = bootstrap.Modal.getOrCreateInstance(this.modal);
        const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(this.driverOffcanvas);

        if (this.driverOffcanvas.classList.contains('show')) {
            this.driverOffcanvas.addEventListener('hidden.bs.offcanvas', () => {
                modalInstance.show();
            },
                { once: true }
            );

            offcanvasInstance.hide();
            return;
        }

        modalInstance.show();
    }

    clearDriverSessionState() {
        localStorage.removeItem('status');
        localStorage.removeItem('driverStatusHistoryCache');
        localStorage.removeItem('isActiveEmergency');
        localStorage.removeItem('warnModalShownFor');

        sessionStorage.removeItem('status');

        /*
         * Signed-out pages return to automatic theme behavior.
         * Server-backed preferences will replace these temporary
         * browser keys when the preferences feature is built.
         */
        localStorage.removeItem('prodriverThemeMode');
        localStorage.removeItem('userThemeOverride');
        localStorage.removeItem('isDarkMode');
    }
};
