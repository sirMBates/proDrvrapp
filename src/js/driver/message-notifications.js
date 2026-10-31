import { proDriverRequest, showFlashAlert } from '../shared/helpers.js';

const controls = document.querySelector('#messenger-push-controls');
const enableButton = document.querySelector('#messenger-enable-notifications');
const statusMessage = document.querySelector('#messenger-push-status');

let isEnablingNotifications = false;
let notificationsInitialized = false;

function publicKeyToBytes(value) {
    const padding = '='.repeat((4 - value.length % 4) % 4);
    const decoded = atob((value + padding).replace(/-/g, '+').replace(/_/g, '/'));

    return Uint8Array.from(decoded, character => character.charCodeAt(0));
};

async function saveBrowserSubscription(subscription, token) {
    const response = await proDriverRequest('/push-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        },
        body: JSON.stringify(subscription.toJSON())
    });

    if (response?.status !== 'success') {
        throw new Error(response?.message || 'The subscription could not be saved.');
    }
};

function showNotificationsEnabled() {
    if (statusMessage) {
        statusMessage.textContent = 'This browser is registered for message notifications.';
    }

    if (enableButton) {
        enableButton.textContent = 'Notifications enabled';
        enableButton.disabled = true;
    }
};

async function enableMessageNotifications() {
    if (isEnablingNotifications) {
        return;
    }

    const publicKey = controls?.dataset.publicKey;
    const token = document.querySelector('#drvrToken')?.value;

    if (!publicKey || !token) {
        showFlashAlert('error', 'Notification configuration is unavailable. Please reload the page.');
        return;
    }

    if (Notification.permission === 'denied') {
        showFlashAlert('error', 'Notifications are blocked. Allow them in this browser’s site settings, then try again.');
        return;
    }

    isEnablingNotifications = true;
    enableButton.disabled = true;
    enableButton.textContent = 'Enabling...';

    let readyTimeout = null;

    try {
        /*
         * Request permission directly from the click, before waiting
         * for registration or making network requests.
         */
        const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission();

        if (permission !== 'granted') {
            showFlashAlert('info', 'Notifications were not enabled. You can try again when ready.');
            return;
        }

        const registration = await Promise.race([
            navigator.serviceWorker.ready,
            new Promise((resolve, reject) => {
                readyTimeout = setTimeout(() => {
                    reject(new Error(
                        'The service worker is not ready. Reload the page and try again.'
                    ));
                }, 10000);
            })
        ]);

        clearTimeout(readyTimeout);

        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: publicKeyToBytes(publicKey)
            });
        }

        await saveBrowserSubscription(subscription, token);

        showFlashAlert('success', 'This browser is registered for message notifications.');

        if (statusMessage) {
            statusMessage.textContent = 'This browser is registered for message notifications.';
        }

        showNotificationsEnabled();
    } catch (error) {
        console.error('[MESSAGE PUSH] Enable failed:', error);
        showFlashAlert('error', error?.message || 'Notifications could not be enabled.');
    } finally {
        clearTimeout(readyTimeout);
        isEnablingNotifications = false;

        if (enableButton.textContent !== 'Notifications enabled') {
            enableButton.textContent = 'Enable notifications';
            enableButton.disabled = false;
        }
    }
};

async function restoreExistingMessageNotifications(token) {
    if (Notification.permission !== 'granted') {
        return;
    }

    let readyTimeout = null;

    isEnablingNotifications = true;

    if (enableButton) {
        enableButton.disabled = true;
        enableButton.textContent = 'Checking notifications...';
    }

    try {
        const registration = await Promise.race([
            navigator.serviceWorker.ready,
            new Promise((resolve, reject) => {
                readyTimeout = setTimeout(() => {
                    reject(new Error(
                        'The service worker is not ready.'
                    ));
                }, 10000);
            })
        ]);

        const subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            return;
        }

        /*
         * Reuse the browser subscription and associate it with
         * the currently authenticated account.
         * No permission prompt or new subscription is created.
         */
        await saveBrowserSubscription(subscription, token);

        showNotificationsEnabled();
    } catch (error) {
        console.error('[MESSAGE PUSH] Subscription recognition failed:', error);

        if (statusMessage) {
            statusMessage.textContent = 'Notification registration could not be confirmed. Use Enable notifications to retry.';
        }
    } finally {
        clearTimeout(readyTimeout);
        isEnablingNotifications = false;

        if (enableButton && enableButton.textContent !== 'Notifications enabled') {
            enableButton.textContent = 'Enable notifications';
            enableButton.disabled = false;
        }
    }
};

export function initializeMessageNotifications({ csrfToken } = {}) {
    if (notificationsInitialized || !csrfToken) {
        return;
    }

    notificationsInitialized = true;

    if (window.location.port === '3000') {
        if (enableButton) {
            enableButton.disabled = true;
        }

        if (statusMessage) {
            statusMessage.textContent = 'Open ProDriver without BrowserSync port 3000 to enable notifications.';
        }

        return;
    }

    if (!window.isSecureContext || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        if (enableButton) {
            enableButton.disabled = true;
        }

        if (statusMessage) {
            statusMessage.textContent = 'Notifications are unavailable in this browser. On iPhone or iPad, try opening ProDriver from its Home Screen installation.';
        }

        return;
    }

    enableButton?.addEventListener('click', enableMessageNotifications);

    const restore = () => {
        restoreExistingMessageNotifications(csrfToken);
    };

    // Your PWA registration starts on window.load.
    if (document.readyState === 'complete') {
        restore();
    } else {
        window.addEventListener('load', restore, { once: true });
    }
};
