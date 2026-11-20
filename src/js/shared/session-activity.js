import { proDriverRequest } from './helpers.js';

export class SessionActivityManager {
    constructor(onSessionRenewed = null) {
        this.lastInteractionAt = 0;
        this.activityThrottleMs = 60 * 1000;
        this.renewalPending = false;
        this.sessionExpired = false;
        this.onSessionRenewed = onSessionRenewed;
        this.automaticRenewalPaused = false;

        this.handleActivity = this.handleActivity.bind(this);
    }

    init() {
        const events = ['pointerdown', 'keydown'];

        events.forEach(eventName => {
            document.addEventListener(eventName, this.handleActivity, { passive: true });
        });
    }

    pauseAutomaticRenewal() {
        this.automaticRenewalPaused = true;
        console.debug('[SESSION ACTIVITY] Automatic renewal paused.');
    }

    resumeAutomaticRenewal() {
        if (this.sessionExpired) {
            return;
        }

        this.automaticRenewalPaused = false;
        console.debug('[SESSION ACTIVITY] Automatic renewal resumed.');
    }

    handleActivity(event) {
        if (this.sessionExpired || this.renewalPending || this.automaticRenewalPaused) {
            return;
        }

        if (event.type === 'keydown' && event.repeat) {
            return;
        }

        if (document.visibilityState !== 'visible') {
            return;
        }

        const now = Date.now();

        if (now - this.lastInteractionAt < this.activityThrottleMs) {
            return;
        }

        void this.renewSession();
    }

    async renewSession() {
        if (this.sessionExpired || this.renewalPending) {
            console.debug('[SESSION ACTIVITY] Renewal unavailable or already in progress.');
            return false;
        }

        this.renewalPending = true;

        try {
            const csrfToken = document.querySelector('#userToken')?.value;

            if (!csrfToken) {
                console.error('[SESSION ACTIVITY] CSRF token unavailable.');
                return false;
            }

            const result = await proDriverRequest('/session-activity', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            });

            if (result?.status !== 'success') {
                throw new Error('Session renewal was unsuccessful.');
            }

            // A late response must never revive an expired client session.
            if (this.sessionExpired) {
                console.warn('[SESSION ACTIVITY] Ignoring renewal response after session expiration.');
                return false;
            }

            // Throttle successful renewals, not failed attempts.
            this.lastInteractionAt = Date.now();

            if (typeof this.onSessionRenewed === 'function') {
                this.onSessionRenewed(result.expiresAt, result.absoluteExpiresAt);
            }

            console.debug('[SESSION ACTIVITY] Session renewed:', {
                expiresAt: result.expiresAt,
                absoluteExpiresAt: result.absoluteExpiresAt
            });

            return true;
        } catch (error) {
            console.error('[SESSION ACTIVITY] Renewal failed:', error);

            if (error.status === 401) {
                this.sessionExpired = true;
                console.warn('[SESSION ACTIVITY] Authentication expired. Sign-in required.');
            }
            return false;
        } finally {
            this.renewalPending = false;
        }
    }
};
