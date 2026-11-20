import { proDriverRequest } from './helpers.js';

export class SessionCountdownManager {
    constructor(onWarning = null, onTick = null, onExpired = null, onWarningCleared = null) {
        this.expiresAt = null;
        this.absoluteExpiresAt = null;
        this.warningThresholdMs = 5 * 60 * 1000;
        this.deadlineVersion = 0;
        this.timerId = null;
        this.warningActive = false;
        this.sessionExpired = false;
        this.onWarning = onWarning;
        this.onTick = onTick;
        this.onExpired = onExpired;
        this.onWarningCleared = onWarningCleared;
    }

    async init() {
        const initialVersion = this.deadlineVersion;

        try {
            const result = await proDriverRequest('/session-status', {
                method: 'GET'
            });

            if (result?.status !== 'success') {
                throw new Error('Unable to retrieve session status.');
            }

            if (this.deadlineVersion !== initialVersion) {
                console.debug('[SESSION COUNTDOWN] Ignoring stale initialization response.');
                return;
            }

            this.expiresAt = this.parseUtcDate(result.expiresAt);
            this.absoluteExpiresAt = this.parseUtcDate(result.absoluteExpiresAt);

            if (!Number.isFinite(this.expiresAt) || !Number.isFinite(this.absoluteExpiresAt)) {
                throw new Error('Invalid session expiration timestamps.');
            }

            console.debug('[SESSION COUNTDOWN] Initialized:', {
                expiresAt: new Date(this.expiresAt).toISOString(),
                absoluteExpiresAt: new Date(this.absoluteExpiresAt).toISOString()
            });

            this.start();
        } catch (error) {
            console.error('[SESSION COUNTDOWN] Initialization failed:', error);
        }
    }

    parseUtcDate(value) {
        if (typeof value !== 'string') {
            return NaN;
        }

        // MySQL DATETIME values are stored in UTC.
        // Explicitly mark them as UTC before parsing.
        const normalized = value.trim().replace(' ', 'T');
        return Date.parse(`${normalized}Z`);
    }

    updateDeadlines(expiresAt, absoluteExpiresAt) {
        // Once expiration is reached, this countdown cannot be revived.
        if (this.sessionExpired) {
            console.warn('[SESSION COUNTDOWN] Ignoring deadline update after session expiration.');
            return;
        }

        const newExpiresAt = this.parseUtcDate(expiresAt);
        const newAbsoluteExpiresAt = this.parseUtcDate(absoluteExpiresAt);

        if (!Number.isFinite(newExpiresAt) || !Number.isFinite(newAbsoluteExpiresAt)) {
            console.error('[SESSION COUNTDOWN] Invalid renewal deadlines.');
            return;
        }

        this.expiresAt = newExpiresAt;
        this.absoluteExpiresAt = newAbsoluteExpiresAt;
        this.deadlineVersion++;

        // Recalculate warning state using the renewed deadlines.
        if (!this.isWarningPeriod()) {
            const wasWarningActive = this.warningActive;

            this.warningActive = false;

            if (wasWarningActive) {
                console.debug('[SESSION COUNTDOWN] Active warning cleared after deadline synchronization.');

                if (typeof this.onWarningCleared === 'function') {
                    this.onWarningCleared();
                }
            }
        }

        this.start();

        console.debug('[SESSION COUNTDOWN] Deadlines synchronized:', {
            expiresAt: new Date(this.expiresAt).toISOString(),
            absoluteExpiresAt: new Date(this.absoluteExpiresAt).toISOString()
        });
    }

    getRemainingTime() {
        if (this.expiresAt === null || this.absoluteExpiresAt === null) {
            return null;
        }

        const effectiveExpiration = Math.min(this.expiresAt, this.absoluteExpiresAt);
        return Math.max(0, effectiveExpiration - Date.now());
    }

    isWarningPeriod() {
        const remaining = this.getRemainingTime();
        return remaining !== null && remaining > 0 && remaining <= this.warningThresholdMs;
    }

    isAbsoluteExpiration() {
        if (this.expiresAt === null || this.absoluteExpiresAt === null) {
            return false;
        }

        return this.absoluteExpiresAt <= this.expiresAt;
    }

    start() {
        if (this.timerId !== null) {
            return;
        }

        if (this.expiresAt === null || this.absoluteExpiresAt === null) {
            console.warn('[SESSION COUNTDOWN] Cannot start without deadlines.');
            return;
        }

        this.checkExpiration();

        if (this.sessionExpired) {
            return;
        }

        this.timerId = window.setInterval(() => {
            this.checkExpiration();
        }, 1000);

        console.debug('[SESSION COUNTDOWN] Timer started.');
    }

    checkExpiration() {
        if (this.sessionExpired) {
            return;
        }

        const remaining = this.getRemainingTime();

        if (remaining === null) {
            return;
        }

        if (typeof this.onTick === 'function') {
            this.onTick(remaining);
        }

        if (remaining === 0) {
            this.sessionExpired = true;
            this.stop();

            console.warn('[SESSION COUNTDOWN] Session expiration reached.');

            if (typeof this.onExpired === 'function') {
                this.onExpired();
            }

            return;
        }

        if (this.isWarningPeriod() && !this.warningActive) {
            this.warningActive = true;

            // Notify the application before displaying the warning.
            if (typeof this.onWarning === 'function') {
                this.onWarning();
            }

            console.warn('[SESSION COUNTDOWN] Five-minute expiration warning triggered.');
        }
    }

    stop() {
        if (this.timerId !== null) {
            window.clearInterval(this.timerId);
            this.timerId = null;
        }
    }
};