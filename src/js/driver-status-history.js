import { handleStatusHistoryFetch } from "./pwa.js";

const STATUS_HISTORY_CACHE_KEY = 'driverStatusHistoryCache';

export class DriverStatusHistory {
    constructor(container) {
        this.container = container;
        this.history = [];
    }

    async init() {
        if (!this.container) {
            return;
        }

        window.addEventListener('driver-status-updated', (e) => {
            this.handleStatusUpdate(e.detail);
        });

        const cacheRendered = this.loadCachedHistory();

        await this.loadHistory(cacheRendered);
    }

    async loadHistory(cacheRendered = false) {
        try {
            const result = await handleStatusHistoryFetch({
                method: 'GET',
                credentials: 'include',
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (result.status !== 'success') {
                if (!cacheRendered) {
                    this.renderEmptyState();
                }
                return;
            }

            const recentHistory = result.data?.recentHistory ?? [];            
            this.history = recentHistory.filter(record => this.isToday(record.statusTimestamp));            

            if (!this.history.length) {
                this.clearHistoryCache();
                this.renderEmptyState();
                return;
            }

            this.saveHistoryCache();
            this.renderHistory();
        } catch (err) {
            console.error('Error loading driver status history:', err);

            if (!cacheRendered) {
                this.renderEmptyState();
            }
        }
    }

    loadCachedHistory() {
        const cachedValue = localStorage.getItem(STATUS_HISTORY_CACHE_KEY);
        if (!cachedValue) {
            return false;
        }

        try {
            const cache = JSON.parse(cachedValue);

            if (cache.date !== this.getTodayDateKey()) {
                this.clearHistoryCache();
                return false;
            }

            if (!Array.isArray(cache.history) || !cache.history.length) {
                return false;
            }

            this.history = cache.history.filter(record => this.isToday(record.statusTimestamp));
            if (!this.history.length) {
                this.clearHistoryCache();
                return false;
            }

            this.renderHistory();
        } catch (err) {
            console.error('Invalid driver status history cache:', err);
            this.clearHistoryCache();

            return false;
        }
    }

    saveHistoryCache() {
        const cache = {
            date: this.getTodayDateKey(),
            history: this.history.slice(0, 20)
        };

        localStorage.setItem(STATUS_HISTORY_CACHE_KEY, JSON.stringify(cache));
    }

    clearHistoryCache() {
        localStorage.removeItem(STATUS_HISTORY_CACHE_KEY);
    }

    getTodayDateKey() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    isToday(statusTimestamp) {
        const statusDate = statusTimestamp.slice(0, 10);
        return statusDate === this.getTodayDateKey();
    }

    formatTime(statusTimestamp) {
        const date = new Date(
            statusTimestamp.replace(' ', 'T')
        );

        return date.toLocaleTimeString([], {
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    formatDate(statusTimestamp) {
        const datePart = statusTimestamp.slice(0, 10);

        const [year, month, day] = datePart.split('-');
        return `${month}/${day}/${year}`;
    }

    renderHistory() {
        const displayDate = this.formatDate(this.history[0].statusTimestamp);
         this.container.innerHTML = `
            <div class="driver-status-history__date">
                <span>Today</span>
                <time datetime="${this.history[0].statusTimestamp.slice(0, 10)}">
                    ${displayDate}
                </time>
            </div>
            <div class="driver-status-history__labels">
                <span>Status</span>
                <span>Time</span>
            </div>

            <div class="driver-status-history__list">
                ${this.history.map(record => `
                    <div class="driver-status-history__item">
                        <span class="driver-status-history__status">
                            ${record.driverStatus}
                        </span>

                        <time class="driver-status-history__time" datetime="${record.statusTimestamp}">
                            ${this.formatTime(record.statusTimestamp)}
                        </time>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderEmptyState() {
        this.container.innerHTML = `
            <div class="driver-status-history__empty">
                <div class="driver-status-history__empty-icon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <h2>No status activity yet</h2>

                <p>
                    Your status updates for today
                    will appear here.
                </p>
            </div>
        `;
    }

    handleStatusUpdate(statusRecord) {
        if (!statusRecord || !this.isToday(statusRecord.statusTimestamp)) {
            return;
        }

        const alreadyExists = this.history.some(record => record.statusId === statusRecord.statusId);
        if (alreadyExists) {
            return;
        }

        this.history.unshift(statusRecord);
        this.history = this.history.slice(0, 20);
        this.saveHistoryCache();
        this.renderHistory();
    }
};