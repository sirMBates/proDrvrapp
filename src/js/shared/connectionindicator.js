const CONNECTION_STATES = Object.freeze({
    ONLINE: 'online',
    RECONNECTING: 'reconnecting',
    DEGRADED: 'degraded',
    OFFLINE: 'offline'
});

export class ConnectionIndicator {
    constructor(element, options = {}) {
        this.element = element;
        this.checkUrl = options.checkUrl ?? '/connection-check';
        this.checkInterval = options.checkInterval ?? 30000;
        this.timeout = options.timeout ?? 5000;

        this.intervalId = null;
        this.isChecking = false;
        this.state = null;
        this.controller = null;

        this.handleOnline = this.handleOnline.bind(this);
        this.handleOffline = this.handleOffline.bind(this);
        this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
    }

    init() {
        if (!this.element) {
            return;
        }

        window.addEventListener('online', this.handleOnline);
        window.addEventListener('offline', this.handleOffline);
        document.addEventListener('visibilitychange', this.handleVisibilityChange);

        if (!navigator.onLine) {
            this.setState(CONNECTION_STATES.OFFLINE);
            return;
        }

        this.verifyConnection();
        this.startChecks();
    }

    handleOnline() {
        this.setState(CONNECTION_STATES.RECONNECTING);
        this.verifyConnection();
        this.startChecks();
    }

    handleOffline() {
        this.stopChecks();
        this.controller?.abort();
        this.setState(CONNECTION_STATES.OFFLINE);
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.stopChecks();
            return;
        }

        if (!navigator.onLine) {
            this.setState(CONNECTION_STATES.OFFLINE);
            return;
        }

        this.verifyConnection();
        this.startChecks();
    }

    startChecks() {
        this.stopChecks();

        if (document.hidden || !navigator.onLine) {
            return;
        }

        this.intervalId = window.setInterval(() => {
            this.verifyConnection();
        }, this.checkInterval);
    }

    stopChecks() {
        if (this.intervalId !== null) {
            window.clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    async verifyConnection() {
        if (this.isChecking) {
            return;
        }

        if (!navigator.onLine) {
            this.setState(CONNECTION_STATES.OFFLINE);
            return;
        }

        this.isChecking = true;

        // Only show reconnecting when initially checking or recovering
        // from a confirmed offline state.
        if (this.state === null || this.state === CONNECTION_STATES.OFFLINE) {
            this.setState(CONNECTION_STATES.RECONNECTING);
        }

        const controller = new AbortController();
        this.controller = controller;

        const timeoutId = window.setTimeout(() => {
            controller.abort();
        }, this.timeout);

        try {
            const url = new URL(this.checkUrl, window.location.origin);
            url.searchParams.set('_connection_check', Date.now().toString());

            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            // A valid server response means PHP is reachable.
            // A 503 degraded response means MySQL is unavailable.
            if (response.status === 200 && result?.status === CONNECTION_STATES.ONLINE) {
                this.setState(CONNECTION_STATES.ONLINE);
            } else if (response.status === 503 && result?.status === CONNECTION_STATES.DEGRADED) {
                this.setState(CONNECTION_STATES.DEGRADED);
            } else {
                this.setState(CONNECTION_STATES.OFFLINE);
            }

        } catch (error) {
            if (navigator.onLine) {
                this.setState(CONNECTION_STATES.OFFLINE);
            }
        } finally {
            window.clearTimeout(timeoutId);
            this.controller = null;
            this.isChecking = false;
        }
    }

    setState(state) {
        if (this.state === state) {
            return;
        }

        const light = this.element.querySelector('.connection-indicator__light');
        const label = this.element.querySelector('.connection-indicator__label');

        if (!light || !label) {
            return;
        }

        const stateDetails = {
            [CONNECTION_STATES.ONLINE]: {
                className: 'is-online',
                label: 'Online'
            },
            [CONNECTION_STATES.RECONNECTING]: {
                className: 'is-reconnecting',
                label: 'Reconnecting'
            },
            [CONNECTION_STATES.DEGRADED]: {
                className: 'is-degraded',
                label: 'Partial Service'
            },
            [CONNECTION_STATES.OFFLINE]: {
                className: 'is-offline',
                label: 'Offline'
            }
        };

        const details = stateDetails[state];

        if (!details) {
            return;
        }

        this.element.classList.remove('is-online', 'is-reconnecting', 'is-degraded', 'is-offline');
        this.element.classList.add(details.className);
        label.textContent = details.label;
        this.element.setAttribute('aria-label', `Server connection: ${details.label.toLowerCase()}`);
        
        const previousState = this.state;

        this.state = state;

        window.dispatchEvent(new CustomEvent('prodriver-connection-changed', {
            detail: {
                state,
                previousState
            }
        }));

    }
};
