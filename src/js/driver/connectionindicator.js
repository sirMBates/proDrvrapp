const CONNECTION_STATES = Object.freeze({
    ONLINE: 'online',
    RECONNECTING: 'reconnecting',
    OFFLINE: 'offline'
});

export class ConnectionIndicator {
    constructor(element, options = {}) {
        this.element = element;
        this.checkUrl = options.checkUrl || '/connection-check';
        this.checkInterval = options.checkInterval || 30000;
        this.timeout = options.timeout || 5000;

        this.intervalId = null;
        this.isChecking = false;

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

    async handleOnline() {
        this.setState(CONNECTION_STATES.RECONNECTING);

        await this.verifyConnection();
        this.startChecks();
    }

    handleOffline() {
        this.stopChecks();
        this.setState(CONNECTION_STATES.OFFLINE);
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.stopChecks();
            return;
        }

        this.verifyConnection();
        this.startChecks();
    }

    startChecks() {
        this.stopChecks();

        if (document.hidden) {
            return;
        }

        this.intervalId = window.setInterval(() => this.verifyConnection(), this.checkInterval);
    }

    stopChecks() {
        if (this.intervalId === null) {
            return;
        }

        window.clearInterval(this.intervalId);
        this.intervalId = null;
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
        this.setState(CONNECTION_STATES.RECONNECTING);

        const controller = new AbortController();
        const timeoutId = window.setTimeout(() => controller.abort(), this.timeout);

        try {
            const url = new URL(this.checkUrl, window.location.origin);
            url.searchParams.set('_connection_check', Date.now().toString());

            await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.setState(CONNECTION_STATES.ONLINE);
        } catch (error) {
            this.setState(CONNECTION_STATES.OFFLINE);
        } finally {
            window.clearTimeout(timeoutId);
            this.isChecking = false;
        }
    }

    setState(state) {
        const light = this.element.querySelector('.connection-indicator__light');
        const label = this.element.querySelector('.connection-indicator__label');

        if (!light || !label) {
            return;
        }

        this.element.classList.remove(
            'is-online',
            'is-reconnecting',
            'is-offline'
        );

        const stateDetails = {
            [CONNECTION_STATES.ONLINE]: {
                className: 'is-online',
                label: 'Online'
            },

            [CONNECTION_STATES.RECONNECTING]: {
                className: 'is-reconnecting',
                label: 'Reconnecting'
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

        this.element.classList.add(details.className);
        label.textContent = details.label;
        this.element.setAttribute('aria-label', `Server connection: ${details.label.toLowerCase()}`);
    }
};