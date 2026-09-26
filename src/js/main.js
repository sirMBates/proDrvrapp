import { ThemeManager } from './theme.js';
import { initProfilePictureHandler } from './profile.js';
import { buildModal } from './appmodal.js';
import { fetchDrvr, showFlashAlert, getCurrentView } from './helpers.js';
import { ChangeStatus } from './changestatus.js';
import { syncEmergencyState } from './emergency-state.js';
import { Validation } from './validation.js';
import { ConnectionIndicator } from './connectionindicator.js';

const curView = getCurrentView();
const menuProfileImage = document.querySelector('#menuProfileImage');
const menuProfileInput = document.querySelector('#menuProfileInput');
const defaultProfileImage = "../../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png";
const mainMenuItems = document.querySelectorAll("#navbarSupportedContent .nav-link");
const driverMenu = document.querySelector(".offcanvas-body");
const themeBtn = document.querySelector("#themeBtn");
const themeBtnText = themeBtn?.nextElementSibling ?? null;
const themeModeIndicator = document.querySelector('#themeModeIndicator');
const logoutLink = driverMenu.querySelector('#logout-link');
const emergencyBackground = document.querySelectorAll('.bg-prodriverclr');
const getDriver = fetchDrvr;
const drvrToken = document.getElementById('drvrToken').value;
const drvrAlert = showFlashAlert;
const connectionIndicatorElement = document.querySelector('#connection-indicator');
let connectionIndicator;
let themeManager;
let driverStatus;
let statusMsg;

window.addEventListener('load', async () => {
        const emergencyState = await syncEmergencyState();
        if (emergencyState === true) {
                applyEmergencyUiState(true);
        } else if (emergencyState === false) {
                applyEmergencyUiState(false);
        }
}, false);

$(document).ready(() => {
        // Skip modal setup on /help-faq
        if (curView === '/faqs' || curView === '/counter' || curView === '/int_messages' || curView === '/signature') {
                return;
        }

        const infoBtn = document.querySelector('#notifyinfo');
        const infoModal = document.querySelector('#info-modal');
        const infoModalMsg = buildModal;
        const infoModBtn = document.querySelector('#info-ok');

        // Centralized config for modal messages
        const modalMessages = {
                '/': {
                        text: `Welcome to your dashboard. From here, you can update your status (which will automatically notify dispatch) and get brief details about your job(s) (if any assignments assigned to you) for the day. If and when you need to update your status from another page, click the icon in the navigation bar and search for switch status. That is where you\'ll also find other options as well.`, 
                        button: 'Ok'
                },
                '/contact': {
                        text: `Need help with something? Not sure of what to do next? Here, you can use this page to send an email with any problems regarding the use of the app. <u>Please and only if neccessary!</u> For account issues, please refer to your company administrator.`, 
                        button: 'Understood'
                },
                '/assignments': {
                        text: `This is where your job orders will be viewed. You\'ll be able to edit certain times, details and add notes for dispatch and your personal reference.<br> You must confirm the job by clicking the button below once received.<br> When you\'re completing the job, click the edit button down below if there are any changes to be made.<br> If no changes, complete the dispatch order.<br> You can also cancel the job if dispatch allows.`, 
                        button: 'Ok'
                },
                '/profile': {
                        text: `Here on your account info profile, this is where you can view your personal information. You can only update your email, mobile number and password. If you would like to update any of the 3, click the button next to the field you would like to update.`, 
                        button: 'Ok'
                },
                '/timesheet': {
                        text: `This is your timesheet (cha\`ching\`💰). This sheet will hold a record of each job/order you\'ve done for the week. Once the week is over, a new sheet will be made available for you to utilize. If your payroll dept requests, you may send this sheet to them as is, print it out or download a copy for yourself.`, 
                        button: 'Ok'
                }
        };

        const modalInstance = new bootstrap.Modal(infoModal, {
                backdrop: 'static',
                keyboard: false
        });

        infoBtn.addEventListener('click', () => {
                modalInstance.show();
        });

        infoModal.addEventListener('shown.bs.modal', () => {
                const path = curView;
                if (modalMessages[path]) {
                        const { text, button } = modalMessages[path];
                        infoModalMsg.info(text, button);
                }
        });

        infoModBtn.addEventListener('click', () => {
                modalInstance.hide();
        });
});

window.addEventListener('DOMContentLoaded', () => {
        themeManager = new ThemeManager({
                themeButton: themeBtn,
                themeButtonText: themeBtnText,
                modeIndicator: themeModeIndicator,
                driverMenu,
                currentView: curView
        });
        themeManager.init();

        const statusButtons = document.querySelectorAll('#driver-status-controls .set-status');
        statusMsg = document.querySelector('#driver-status-dock #statusMessage');
        const tokenElement = document.getElementById('drvrToken');

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
        connectionIndicator.init();

        getDriver('/getstatus', {
                method: 'GET',
                credentials: 'include',
                headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': drvrToken
                }
        })
        .then(result => {
                if (result?.status !== 'success') {
                        throw new Error(result?.message || 'Current status could not be loaded.');
                }

                const currentStatus = result.data?.currentStatus ?? null;

                driverStatus.setConfirmedStatus(currentStatus?.driverStatus || null);
        })
        .catch(error => {
                console.error('Unable to load current driver status:', error);

                /*
                * Cache is only a temporary display fallback.
                * It does not replace the database.
                */
                const cachedStatus = localStorage.getItem('status');

                if (cachedStatus) {
                        driverStatus.setConfirmedStatus(cachedStatus);
                        showFlashAlert('info', 'Showing your last known status.');
                        return;
                }

                statusMsg.textContent = 'Current status: Unavailable';
        });

        getDriver('/getprofile', {
                method: 'GET', 
                mode: 'cors',
                credentials: 'include',
                headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': drvrToken
                }
        })
        .then(data => {
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
        })
        .catch(error => {
                if (error) {
                        const drvrMainMenu = document.querySelector('#drivermenu');
                        const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3];
                        drvrMainHeader.textContent = 'Pro Driver';
                }
                console.error('There was a problem with the fetch operation:', error);
        });
});

// Insert profile image in the driver menu bar
// Listen for file selection
if (menuProfileInput && menuProfileImage) {
    initProfilePictureHandler({
        profileInput: menuProfileInput,
        profileImage: menuProfileImage,
        drvrToken,
        getDriver: fetchDrvr,
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
    }
);

function applyEmergencyUiState(active) {
    if (active) {
        localStorage.setItem('isActiveEmergency', 'true');

        emergencyBackground.forEach(background => {
            background.classList.remove('bg-prodriverclr');
            background.classList.add('bg-danger');
        });

        if (statusMsg) {
            statusMsg.classList.add('text-danger');
            statusMsg.textContent = 'Current status: Emergency';
        }

        return;
    }

    localStorage.removeItem('isActiveEmergency');

    emergencyBackground.forEach(background => {
        background.classList.remove('bg-danger');
        background.classList.add('bg-prodriverclr');
    });

    if (statusMsg) {
        statusMsg.classList.remove('text-danger');
    }
};

// Highlight the active link of the current page.
function activeLink () {
        mainMenuItems.forEach(link => {
                let linkLocation = link.pathname;
                if (curView === linkLocation) {
                        link.setAttribute('aria-current', 'page'); //aria-current, page
                        link.classList.add('active'); //active                      
                } else {
                        link.removeAttribute('aria-current'); //aria-current
                        link.classList.remove('active'); //active 
                }
        })
};
activeLink();

$(logoutLink).on('click', () => {
        localStorage.removeItem('status');
        localStorage.removeItem('driverStatusHistoryCache');

        // Emergency State
        localStorage.removeItem('isActiveEmergency');

        // Legacy/session status state
        sessionStorage.removeItem('status');

        // Clear user override so auto theme resumes
        localStorage.removeItem('userThemeOverride');

        // Immediately apply auto theme
        autoThemeSwitcher();
        updateThemeIndicator();
        localStorage.removeItem('warnModalShownFor');
});
