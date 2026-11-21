import { ThemeManager } from '../shared/theme.js';
import { initProfilePictureHandler } from "./profile-picture-handler.js";
import { proDriverRequest, showFlashAlert, getCurrentView } from '../shared/helpers.js';
import { ChangeStatus } from './changestatus.js';
import { syncEmergencyState } from './emergency-state.js';
import { Validation } from '../validation/validation.js';
import { ConnectionIndicator } from '../shared/connectionindicator.js';
import { StatusDock } from './statusdock.js';
import { LogoutManager } from '../shared/logout.js';
import { initializeMessageNotifications } from './message-notifications.js';
import { PageGuidance } from './page-guidance.js';
import { getPreferences, updatePreference } from './preferences.js';
import { SessionActivityManager } from '../shared/session-activity.js';
import { SessionCountdownManager } from '../shared/session-countdown.js';
import { SessionExpirationModal } from '../shared/session-expiration-modal.js';

const curView = getCurrentView();
const menuProfileImage = document.querySelector('#menuProfileImage');
const menuProfileInput = document.querySelector('#menuProfileInput');
const defaultProfileImage = "../../../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png";
const mainMenuItems = document.querySelectorAll("#navbarSupportedContent .nav-link");
const driverMenu = document.querySelector(".offcanvas-body");
const themeBtn = document.querySelector("#themeBtn");
const themeBtnText = document.querySelector('#themeBtnText');
const themeModeSwitch = document.querySelector('#themeModeSwitch');
const themeAutoLabel = document.querySelector('#themeAutoLabel');
const themeManualLabel = document.querySelector('#themeManualLabel');
const themeModeDescription = document.querySelector('#themeModeDescription');
const pageGuidanceSwitch = document.querySelector('#pageGuidanceSwitch');
const pageGuidanceDescription = document.querySelector('#pageGuidanceDescription');
const statusDockSwitch = document.querySelector('#statusDockSwitch');
const statusDockDescription = document.querySelector('#statusDockDescription');
const emergencyBackground = document.querySelectorAll('.bg-prodriverclr');
const getDriver = proDriverRequest;
const userToken = document.querySelector('#userToken').value;
const drvrAlert = showFlashAlert;
const connectionIndicatorElement = document.querySelector('#connection-indicator');
const logoutModal = document.querySelector('#logout-modal');
const logoutCsrfToken = document.querySelector('#logout-csrf-token');
const endShiftLogoutButton = document.querySelector('#end-shift-logout-button');
const logoutOnlyButton = document.querySelector('#logout-only-button');
const returnToServiceButton = document.querySelector('#return-to-service-button');
const logoutModalError = document.querySelector('#logout-modal-error');
const logoutTrigger = document.querySelector('#logout-link');
const driverOffcanvas = document.querySelector('#drivermenu');
let pageGuidance;
let connectionIndicator;
let themeManager;
let driverStatus;
let statusMsg;
let statusDock;
let logoutManager;

function applyEmergencyUiState(active) {
        if (active) {
                localStorage.setItem('isActiveEmergency', 'true');
                emergencyBackground.forEach(background => {
                        background.classList.remove('bg-prodriverclr');
                        background.classList.add('bg-danger');
                });

                if (statusMsg) {
                statusMsg.classList.add('text-danger');
                }
        } else {
                localStorage.removeItem('isActiveEmergency');
                emergencyBackground.forEach(background => {
                        background.classList.remove('bg-danger');
                        background.classList.add('bg-prodriverclr');
                });

                if (statusMsg) {
                        statusMsg.classList.remove('text-danger');
                }
        }

        window.dispatchEvent(new CustomEvent('emergency-state-changed', { detail: { active }}));
};

// Highlight the active link of the current page.
function activeLink() {
    mainMenuItems.forEach(link => {
        const href = link.getAttribute('href');
        const activePaths = (link.dataset.activePaths || '').split(',').map(path => path.trim()).filter(Boolean);
        let isActive = false;

        if (activePaths.length) {
            isActive = activePaths.includes(curView);
        } else if (href && href !== '#') {
            const linkLocation = new URL(href, window.location.origin).pathname;
            isActive = curView === linkLocation;
        }

        link.classList.toggle('active', isActive);

        if (isActive && href && href !== '#') {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });

    document.querySelectorAll('.dropdown-item[href]').forEach(link => {
        const linkLocation = new URL(link.getAttribute('href'), window.location.origin).pathname;
        const isActive = curView === linkLocation;

        link.classList.toggle('active', isActive);

        if (isActive) {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });
};
activeLink();

function updateThemePreferenceUi() {
    if (!themeManager) {
        return;
    }

    const isManual = themeManager.getMode() !== 'auto';

    if (themeModeSwitch) {
        themeModeSwitch.checked = isManual;
    }

    if (themeAutoLabel) {
        themeAutoLabel.classList.toggle('theme-auto', !isManual);
        themeAutoLabel.classList.toggle('text-body-secondary', isManual);
        themeAutoLabel.classList.toggle('fw-semibold', !isManual);
    }

    if (themeManualLabel) {
        themeManualLabel.classList.toggle('theme-manual', isManual);
        themeManualLabel.classList.toggle('text-body-secondary', !isManual);
        themeManualLabel.classList.toggle('fw-semibold', isManual);
    }

    if (themeModeDescription) {
        themeModeDescription.textContent = isManual ? 'Choose your Light or Dark theme.' : 'Let ProDriver choose automatically.';
    }
};

function updatePageGuidanceUi(enabled) {
    if (pageGuidanceSwitch) {
        pageGuidanceSwitch.checked = enabled;
    }

    if (pageGuidanceDescription) {
        pageGuidanceDescription.textContent = enabled ? 'Information helpers are available throughout ProDriver.' : 'Information helpers are hidden. Turn Page Guidance on anytime to restore them.';
    }
};

function updateStatusDockPreferenceUi(expanded) {
        if (statusDockSwitch) {
                statusDockSwitch.checked = expanded;
        }

        if (statusDockDescription) {
                statusDockDescription.textContent = expanded ? 'The Status Dock is currently expanded.' : 'The Status Dock is currently collapsed. Your current status and connection remain visible.';
        }
};

window.addEventListener('load', async () => {
        const emergencyState = await syncEmergencyState();
        if (emergencyState === true) {
                applyEmergencyUiState(true);
        } else if (emergencyState === false) {
                applyEmergencyUiState(false);
        }
}, false);

window.addEventListener('DOMContentLoaded', () => {
        themeManager = new ThemeManager({
                themeButton: themeBtn,
                themeButtonText: themeBtnText,
                driverMenu,
                statusDock: document.querySelector('#driver-status-dock'),
                currentView: curView
        });
        themeManager.init();
        updateThemePreferenceUi();

        window.addEventListener('theme-changed', () => {
                updateThemePreferenceUi();
        })

        themeModeSwitch?.addEventListener('change', async () => {
                const previousMode = themeManager.getMode();

                const nextMode = themeModeSwitch.checked ? themeManager.getActiveTheme() : 'auto';

                if (nextMode === previousMode) {
                        updateThemePreferenceUi();
                        return;
                }

                themeManager.setMode(nextMode);

                try {
                        await updatePreference('theme', nextMode);
                } catch (error) {
                        console.error('Unable to save theme mode:', error);
                        themeManager.syncMode(previousMode);
                        showFlashAlert('danger', 'Your theme mode could not be saved.');
                }
        });

        window.addEventListener('theme-mode-changed', async event => {
                const mode = event.detail?.mode;
                if (!mode) {
                        return;
                }

                try {
                        await updatePreference('theme', mode);
                } catch (error) {
                        console.error('Unable to save theme preference:', error);
                        showFlashAlert('danger', 'Your theme changed on this device, but the preference could not be saved.');
                }
        });

        pageGuidance = new PageGuidance(curView);
        pageGuidance.init();

        pageGuidanceSwitch?.addEventListener('change', async () => {
                const previousState = pageGuidance.isEnabled();
                const nextState = pageGuidanceSwitch.checked;

                pageGuidance.setEnabled(nextState);
                updatePageGuidanceUi(nextState);

                localStorage.setItem('prodriverPageGuidance', String(nextState));
                document.documentElement.classList.toggle('page-guidance-disabled', !nextState);

                try {
                        await updatePreference('show_page_guidance', nextState);
                } catch (error) {
                        console.error('Unable to save Page Guidance preference:', error);
                        localStorage.setItem('prodriverPageGuidance', String(previousState));
                        document.documentElement.classList.toggle('page-guidance-disabled', !previousState);

                        pageGuidance.setEnabled(previousState);
                        updatePageGuidanceUi(previousState);

                        showFlashAlert('danger', 'Your Page Guidance preference could not be saved.');
                }
        });

        const statusDockElement = document.querySelector('#driver-status-dock');
        const driverId = Number(statusDockElement?.dataset.driverId);

        const statusDockPendingKey = Number.isSafeInteger(driverId) && driverId > 0 ? `prodriver:${driverId}:statusDockPendingSync` : null;

        let statusDockRevision = 0;

        function markStatusDockPending(expanded) {
                if (!statusDockPendingKey) {
                        return;
                }

                statusDockRevision++;

                localStorage.setItem(statusDockPendingKey, String(expanded));
        }

        
        function clearStatusDockPending(expanded, revision) {
                if (!statusDockPendingKey) {
                        return;
                }

                // A newer driver selection must never be cleared
                // by an older request.
                if (revision !== statusDockRevision) {
                        return;
                }

                if (localStorage.getItem(statusDockPendingKey) === String(expanded)) {
                        localStorage.removeItem(statusDockPendingKey);
                }
        }


        function getPendingStatusDockPreference() {
                if (!statusDockPendingKey) {
                        return null;
                }

                const value = localStorage.getItem(statusDockPendingKey);

                if (value === 'true') {
                        return true;
                }

                if (value === 'false') {
                        return false;
                }

                return null;
        }

        let statusDockSyncPromise = null;

        async function syncStatusDockPreference() {
                if (statusDockSyncPromise) {
                        return statusDockSyncPromise;
                }

                if (getPendingStatusDockPreference() === null) {
                        return;
                }

                statusDockSyncPromise = (async () => {
                        while (true) {
                                const expanded = getPendingStatusDockPreference();
                                const revision = statusDockRevision;

                                if (expanded === null) {
                                        return;
                                }

                                try {
                                        await updatePreference('status_dock_expanded', expanded);
                                } catch (error) {
                                        console.warn('Status Dock preference synchronization pending:', error);
                                        return;
                                }

                                clearStatusDockPending(expanded, revision);

                                // If another selection was made while the request
                                // was processing, synchronize that newer value too.
                                if (getPendingStatusDockPreference() === null) {
                                        return;
                                }
                        }
                })();

                try {
                        await statusDockSyncPromise;
                } finally {
                        statusDockSyncPromise = null;
                }
        }

        if (!Number.isSafeInteger(driverId) || driverId <= 0) {
                console.error('Unable to identify the current driver for perference synchronization.');
        }

        statusDock = new StatusDock(statusDockElement);
        statusDock.init();

        const pendingExpanded = getPendingStatusDockPreference();

        if (pendingExpanded !== null) {
                statusDock.setExpanded(pendingExpanded);
        }

        updateStatusDockPreferenceUi(statusDock.isExpanded());

        statusDockSwitch?.addEventListener('change', () => {
                const expanded = statusDockSwitch.checked;

                statusDock.setExpanded(expanded);
                updateStatusDockPreferenceUi(expanded);

                markStatusDockPending(expanded);
                void syncStatusDockPreference();
        });
        
        window.addEventListener('status-dock-preference-changed', event => {
                const expanded = event.detail?.expanded;

                if (typeof expanded !== 'boolean') {
                        return;
                }

                updateStatusDockPreferenceUi(expanded);

                markStatusDockPending(expanded);
                void syncStatusDockPreference();
        });

        const statusButtons = document.querySelectorAll('#driver-status-controls .set-status');
        statusMsg = document.querySelector('#driver-status-dock #statusMessage');
        const tokenElement = document.getElementById('userToken');

        if (statusButtons.length === 0 || !statusMsg || !tokenElement?.value) {
                console.error('Driver status dock initialization failed.', {
                        buttonCount: statusButtons.length,
                        statusDisplayFound: Boolean(statusMsg),
                        tokenFound: Boolean(tokenElement?.value)
                });

                return;
        }

        // The status controls and the connection to the DB api
        driverStatus = new ChangeStatus(statusButtons, tokenElement.value, statusMsg);
        driverStatus.init();

        connectionIndicator = new ConnectionIndicator(connectionIndicatorElement, {
                checkUrl: '/connection-check',
                checkInterval: 30000,
                timeout: 5000
        });

        window.addEventListener('prodriver-connection-changed', event => {
                if (event.detail?.state !== 'online') {
                        return;
                }

                // Synchronize any pending Status Dock preference.
                void syncStatusDockPreference();

                // Refresh the driver's current status from MySQL.
                void refreshDriverStatus();
        });

        connectionIndicator.init();
        
        async function refreshDriverStatus() {
                try {
                        const result = await getDriver('/getstatus', {
                        method: 'GET',
                        credentials: 'include',
                                headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-Token': userToken
                                }
                        });

                        if (result?.status !== 'success') {
                                throw new Error(result?.message || 'Current status could not be loaded.');
                        }

                        const currentStatus = result.data?.currentStatus ?? null;

                        driverStatus.setConfirmedStatus(currentStatus?.driverStatus || null);
                } catch (error) {
                        console.error('Unable to load current driver status:', error);

                        const cachedStatus = localStorage.getItem('status');
                        if (cachedStatus) {
                                driverStatus.setConfirmedStatus(cachedStatus);
                                showFlashAlert('info', 'Showing your last known status.');
                                return;
                        }

                        const statusValue = statusMsg.querySelector('.driver-status-display__value');

                        if (statusValue) {
                                statusValue.textContent = 'Unavailable';
                        }
                }
        }

        // Initial status load
        void refreshDriverStatus();

        getPreferences().then(preferences => {
                const accountTheme = preferences?.theme;
                const showPageGuidance = preferences?.show_page_guidance;
                const statusDockExpanded = preferences?.status_dock_expanded;

                if (accountTheme && accountTheme !== themeManager.getMode()) {
                        themeManager.syncMode(accountTheme);
                }

                if (typeof showPageGuidance === 'boolean') {
                        localStorage.setItem('prodriverPageGuidance', String(showPageGuidance));
                        document.documentElement.classList.toggle('page-guidance-disabled', !showPageGuidance);
                        pageGuidance.setEnabled(showPageGuidance);
                        updatePageGuidanceUi(showPageGuidance);
                }
                
                const pendingExpanded = getPendingStatusDockPreference();

                if (pendingExpanded !== null) {
                        // The browser has an unsaved driver selection.
                        // Do not overwrite it with an older database value.
                        statusDock.setExpanded(pendingExpanded);
                        updateStatusDockPreferenceUi(pendingExpanded);
                } else if (typeof statusDockExpanded === 'boolean') {
                        // No pending change: synchronize normally with MySQL.
                        statusDock.setExpanded(statusDockExpanded);
                        updateStatusDockPreferenceUi(statusDockExpanded);
                }

        }).catch(error => {
                console.error('Unable to synchronize driver preferences:', error);
        });

        getDriver('/getprofile', {
                method: 'GET', 
                mode: 'cors',
                credentials: 'include',
                headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': userToken
                }
        }).then(data => {
                const driver = data;
                const drvrMainMenu = document.querySelector('#drivermenu');
                const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3]; 
                if (driver) {
                        drvrMainHeader.textContent = `${driver['firstName']} ${driver['lastName']}`;
                        if (driver['profilePicture']) {
                                menuProfileImage.setAttribute('src', '/setprofilepicture');  // Assuming profilePicture contains the image URL
                        } else {
                                menuProfileImage.setAttribute('src', defaultProfileImage); // Default image if no profile picture is found
                        }
                }
        }).catch(error => {
                if (error) {
                        const drvrMainMenu = document.querySelector('#drivermenu');
                        const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3];
                        drvrMainHeader.textContent = 'Pro Driver';
                }
                console.error('There was a problem with the fetch operation:', error);
        });

        // Session Expiration Modal Management
        const sessionExpirationModal = new SessionExpirationModal(
                async () => {
                        const renewed = await sessionActivityManager.renewSession();
                        if (!renewed) {
                                return false;
                        }

                        if (sessionCountdownManager.isWarningPeriod()) {
                                console.warn('[SESSION MODAL] Renewal succeeded, but session expiration is still approaching.');
                                return false;
                        }

                        return true;
                },

                // Sign Out
                async () => {
                        if (!logoutManager) {
                                throw new Error('Logout manager is unavailable.');
                        }

                        return await logoutManager.logoutOnly();
                }
        );

        sessionExpirationModal.init();

        // Session Expiration Countdown Management
        const sessionCountdownManager = new SessionCountdownManager(
                // Five-minute warning callback
                () => {
                        sessionActivityManager.pauseAutomaticRenewal();
                        // Determine which deadline controls expiration. ⬇
                        sessionExpirationModal.setExpirationMode(sessionCountdownManager.isAbsoluteExpiration());
                        // Synchronize the display before showing the modal. ⬇
                        sessionExpirationModal.updateCountdown(sessionCountdownManager.getRemainingTime());
                        sessionExpirationModal.show();
                },

                // Countdown tick callback
                (remainingMs) => {
                        if (sessionCountdownManager.warningActive) {
                                sessionExpirationModal.updateCountdown(remainingMs);
                        }
                },

                // Session expiration callback
                () => {
                        console.warn('[SESSION] Expiration deadline reached. Returning to sign-in.');
                        sessionActivityManager.sessionExpired = true;
                        sessionActivityManager.pauseAutomaticRenewal();

                        // Clear the same browser state as a normal logout.
                        if (logoutManager) {
                                logoutManager.clearDriverSessionState();
                        }

                        // Never submit End of Shift during session expiration.
                        window.location.replace('/signin?session=expired');
                },

                // Warning-cleared callback
                () => {
                        console.debug('[SESSION] Warning cleared. Restoring normal session activity.');
                        sessionExpirationModal.modal?.hide();
                        sessionActivityManager.resumeAutomaticRenewal();
                }
        );

        // Session Activity Management
        const sessionActivityManager = new SessionActivityManager(
                (expiresAt, absoluteExpiresAt) => {
                        sessionCountdownManager.updateDeadlines(expiresAt, absoluteExpiresAt);
                }
        );

        logoutManager = new LogoutManager({
                logoutTrigger,
                driverOffcanvas,
                csrfToken: logoutCsrfToken?.value ?? '',
                modal: logoutModal,
                endShiftButton: endShiftLogoutButton,
                logoutOnlyButton: logoutOnlyButton,
                returnToServiceButton: returnToServiceButton,
                errorDisplay: logoutModalError
        });
        logoutManager.init();

        // Begin monitoring only after all dependent managers are ready
        sessionActivityManager.init();
        void sessionCountdownManager.init();

        if (logoutTrigger) {
                initializeMessageNotifications({
                        csrfToken: logoutCsrfToken?.value || userToken
                });
        }
});

// Insert profile image in the driver menu bar
// Listen for file selection
if (menuProfileInput && menuProfileImage) {
    initProfilePictureHandler({
        profileInput: menuProfileInput,
        profileImage: menuProfileImage,
        userToken,
        getDriver: proDriverRequest,
        defaultProfileImage: defaultProfileImage,
        Validation,
        drvrAlert
    });
};

window.addEventListener('driver-status-updated', (e) => {
        const statusRecord = e.detail;

        if (statusRecord?.driverStatus === 'Emergency') {
            applyEmergencyUiState(true);
        }
});
