import { proDriverRequest, viewableDateTimeHelper } from './helpers.js';

export class MessagePreview {
    constructor(container) {
        this.container = container;

        this.list = container?.querySelector('[data-message-preview-list]');
        this.loading = container?.querySelector('[data-message-preview-loading]');
        this.error = container?.querySelector('[data-message-preview-error]');
        this.empty = container?.querySelector('[data-message-preview-empty]');
        this.total = container?.querySelector('[data-message-preview-total]');

        this.timer = null;
        this.inFlight = false;
        this.failures = 0;
        this.initialized = false;
        this.lastRenderedSignature = null;
    }

    init() {
        if (!this.container || this.initialized) {
            return;
        }

        this.initialized = true;

        document.addEventListener('visibilitychange', () => {
            this.stop();

            if (!document.hidden) {
                this.failures = 0;
                this.schedule(0);
            }
        });

        window.addEventListener('offline', () => {
            this.stop();
        });

        window.addEventListener('online', () => {
            this.failures = 0;
            this.schedule(0);
        });

        this.schedule(0);
    }

    stop() {
        if (this.timer !== null) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }

    schedule(delay = 3000) {
        this.stop();

        if (document.hidden || !navigator.onLine) {
            return;
        }

        this.timer = setTimeout(() => {
            this.timer = null;
            this.refresh();
        }, delay);
    }

    async refresh() {
        if (document.hidden || !navigator.onLine) {
            return;
        }

        if (this.inFlight) {
            this.schedule();
            return;
        }

        this.inFlight = true;

        try {
            const token = document.querySelector('#drvrToken')?.value;

            if (!token) {
                throw new Error('Your session token is missing. Reload the page.');
            }

            const options = {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-CSRF-Token': token
                }
            };

            const response = await proDriverRequest('/messenger-unread', options);

            if (response?.status !== 'success' || !Array.isArray(response.unreadData?.conversations)) {
                throw new Error(response?.message || 'Message previews could not be loaded.');
            }

            this.render(response.unreadData);

            this.failures = 0;
            this.loading?.classList.add('d-none');
            this.error?.classList.add('d-none');
        } catch (error) {
            this.failures++;

            console.error('[MESSAGE PREVIEW] Refresh failed:', error);

            this.loading?.classList.add('d-none');

            if (this.error) {
                this.error.textContent = 'Messages could not be refreshed. Retrying...';
                this.error.classList.remove('d-none');
            }
        } finally {
            this.inFlight = false;
            this.schedule(Math.min(3000 * (2 ** Math.min(this.failures, 4)), 30000));
        }
    }

    render(unreadData) {
        const conversations = unreadData.conversations.filter(summary => {
            const recipient = summary.recipient;
            return (summary.conversation_type === 'direct' && summary.latest_message_id != null && recipient && Number.isSafeInteger(recipient.userId) && recipient.userId > 0 && typeof recipient.displayName === 'string');
        });

        conversations.sort((a, b) => String(b.latest_message_created_at).localeCompare(String(a.latest_message_created_at)) || Number(b.latest_message_id) - Number(a.latest_message_id));

        const recent = conversations.slice(0, 5);
        const totalUnread = Number(unreadData.total_unread) || 0;

        if (this.total) {
            this.total.textContent = `${totalUnread} unread`;
            this.total.classList.toggle('d-none', totalUnread === 0);
        }

        const signature = JSON.stringify(recent);

        // Avoid replacing focused links when nothing changed.
        if (signature === this.lastRenderedSignature) {
            return;
        }

        const fragment = document.createDocumentFragment();

        recent.forEach(summary => {
            const recipient = summary.recipient;

            const link = document.createElement('a');
            link.className = 'list-group-item list-group-item-action dashboard-message-row';
            link.href = `/message-center?recipient=${encodeURIComponent(recipient.userId)}`;

            const heading = document.createElement('div');
            heading.className = 'd-flex justify-content-between align-items-center gap-2';

            const name = document.createElement('strong');
            name.className = 'text-truncate';
            name.textContent = recipient.displayName;

            heading.appendChild(name);

            if (summary.unread_count > 0) {
                const badge = document.createElement('span');
                badge.className = 'badge rounded-pill text-bg-danger flex-shrink-0';
                badge.textContent = String(summary.unread_count);
                badge.setAttribute('aria-label', `${summary.unread_count} unread messages`);

                heading.appendChild(badge);
            }

            const preview = document.createElement('div');
            preview.className = 'small text-body-secondary text-truncate mt-1';
            preview.textContent = `${summary.latest_is_own ? 'You: ' : ''}${String(summary.latest_message_body ?? '').replace(/\s+/g, ' ').trim()}`;

            const timestamp = document.createElement('small');
            timestamp.className = 'text-body-secondary d-block mt-1';
            timestamp.textContent = viewableDateTimeHelper(summary.latest_message_created_at, 'datetime');

            link.append(heading, preview, timestamp);
            fragment.appendChild(link);
        });

        this.list?.replaceChildren(fragment);
        this.empty?.classList.toggle('d-none', recent.length !== 0);

        this.lastRenderedSignature = signature;
    }
}
