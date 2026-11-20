export class SessionExpirationModal {
    constructor(onStaySignedIn = null, onSignOut = null) {
        this.modalElement = document.querySelector('#session-expiration-modal');
        this.messageElement = document.querySelector('#session-expiration-message');
        this.countdownElement = document.querySelector('#session-expiration-countdown');
        this.staySignedInButton = document.querySelector('#session-stay-signed-in');
        this.signOutButton = document.querySelector('#session-sign-out');

        this.modal = null;
        this.onStaySignedIn = onStaySignedIn;
        this.onSignOut = onSignOut;
    }

    init() {
        if (!this.modalElement) {
            console.error('[SESSION MODAL] Modal element not found.');
            return;
        }

        this.modal = bootstrap.Modal.getOrCreateInstance(this.modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        this.staySignedInButton.addEventListener('click', async () => {
            if (typeof this.onStaySignedIn !== 'function') {
                console.error('[SESSION MODAL] Renewal callback unavailable.');
                return;
            }

            this.staySignedInButton.disabled = true;

            try {
                const renewed = await this.onStaySignedIn();
                if (renewed) {
                    console.debug('[SESSION MODAL] Session renewed. Warning dismissal handled by application controller.');
                } else {
                    console.warn('[SESSION MODAL] Session renewal was unsuccessful.');
                }
            } catch (error) {
                console.error('[SESSION MODAL] Renewal request failed:', error);
            } finally {
                this.staySignedInButton.disabled = false;
            }
        });

        this.signOutButton.addEventListener('click', async () => {
            if (typeof this.onSignOut !== 'function') {
                console.error('[SESSION MODAL] Sign-out callback unavailable.');
                return;
            }

            this.signOutButton.disabled = true;

            try {
                await this.onSignOut();
            } catch (error) {
                console.error('[SESSION MODAL] Sign-out failed:', error);
                this.signOutButton.disabled = false;
            }
        });

        console.debug('[SESSION MODAL] Initialized.');
    }

    show() {
        if (!this.modal) {
            console.error('[SESSION MODAL] Cannot display an uninitialized modal.');
            return;
        }

        this.modal.show();
        console.debug('[SESSION MODAL] Expiration warning displayed.');
    }

    updateCountdown(remainingMs) {
        if (!this.countdownElement) {
            return;
        }

        const totalSeconds = Math.max(0, Math.ceil(remainingMs / 1000));

        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        this.countdownElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    setExpirationMode(isAbsoluteExpiration) {
        if (!this.messageElement || !this.staySignedInButton) {
            console.error('[SESSION MODAL] Required elements unavailable.');
            return;
        }

        if (isAbsoluteExpiration) {
            this.messageElement.textContent = 'Your maximum sign-in period is ending. Please sign in again to continue using ProDriver.';

            this.staySignedInButton.classList.add('d-none');
        } else {
            this.messageElement.textContent = 'Your session is about to expire due to inactivity. Select Stay Signed In to continue.';

            this.staySignedInButton.classList.remove('d-none');
        }

        console.debug('[SESSION MODAL] Expiration mode:', isAbsoluteExpiration ? 'absolute' : 'inactivity');
    }
};
