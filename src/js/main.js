import { buildModal } from './appmodal.js';
import { fetchDrvr } from './drvrapi.js';
import { ChangeStatus } from './changestatus.js';
import { Validation } from './validation.js';

const myCurrentView = window.location.pathname;
//const profCon = document.querySelector("#profilecon");
// Profile image display in navbar.
// [default image is firstChild.nextElementSibling] & [file selector is 3]
const profileImage = document.querySelector('#profile-pic');
const profileInput = document.querySelector('#profile-upload');
const defaultProfileImage = "../../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png";
const getMenuItems = document.querySelectorAll(".nav-link");
//const textLink = document.querySelector("#useraccess");
const driverMenu = document.querySelector(".offcanvas-body");
const viewPayCard = driverMenu.childNodes[5];
let isDarkMode;
const themeBtn = document.querySelector("#themeBtn");
const themeBtnText = themeBtn.nextElementSibling;
const themeModeIndicator = document.querySelector('#themeModeIndicator');
const changeStatusCon = document.querySelector('.offcanvas-body').childNodes[3]//.firstElementChild;
const logoutLink = document.querySelector('.offcanvas-body').childNodes[11].firstElementChild;
const emergencyBtn = document.querySelectorAll('.status-emergency');
let isActiveEmergency;
const emergencyBackground = document.querySelectorAll('.bg-besttrailsclr');
const DSC = document.querySelectorAll('.set-status'); // (D)river(S)tatus(C)ontrol :)
const getDriver = fetchDrvr;
const statusEndpoint = "https://prodriver.local/setstatus";
const drvrToken = document.getElementById('drvrToken').value;
const statusMsg = document.querySelector('#statusMessage');


$(document).ready(() => {
        // Skip modal setup on /help-faq
        if (myCurrentView === '/help') {
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
                '/orders': {
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
                const path = window.location.pathname;
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
        getDriver("https://prodriver.local/getprofile", {
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
            const drvrMainMenu = document.querySelector('#useraccess');
            const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3]; 
            if (driver) {
                drvrMainHeader.textContent = `${driver['firstName']} ${driver['lastName']}`;
                if (driver['profilePicture']) {
                    profileImage.setAttribute('src', driver['profilePicture']);  // Assuming profilePicture contains the image URL
                } else {
                    profileImage.setAttribute('src', defaultProfileImage); // Default image if no profile picture is found
                }
            }
        })
        .catch(error => {
                if (error) {
                        const drvrMainMenu = document.querySelector('#useraccess');
                        const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3];
                        drvrMainHeader.textContent = 'Pro Driver';
                }
                console.error('There was a problem with the fetch operation:', error);
        });
});

// Insert profile image in the driver menu bar
// Listen for file selection
profileInput.addEventListener('change', (e) => {
        profileImage.src = defaultProfileImage;
        const file = e.target.files[0];
        if (!file) return;

        // Validate the file
        const isValid = Validation.validate(file, 'file'); 
        if (!isValid) {
                alert('Please select a valid image file (JPG, JPEG, PNG, GIF) and ensure it is within the size limit.');
                profileImage.src = defaultProfileImage;
                return;
        }

        // Read file for preview
        const reader = new FileReader();
        reader.onload = (ev) => {
                // Show the preview
                profileImage.src = ev.target.result;

                // Prepare form data for upload
                const formData = new FormData();
                formData.append('profileImage', file);
                formData.append('drvrtoken', drvrToken);
                formData.append('__method', 'PATCH');

                // Upload to server
                getDriver('https://prodriver.local/setprofilepicture', {
                        mode: 'cors',
                        credentials: 'include',
                        method: 'POST',
                        headers: { 
                                'X-CSRF-Token': drvrToken
                        }, // if needed
                        body: formData,
                })
                //.then(response => response.text())
                .then(data => {
                        if (data.status === 'success') {
                                alert(data.message);
                                // Reset input after successful upload
                                profileInput.value = '';
                        } else {
                                alert(data.message || 'Upload failed');
                        }
                })
                .catch(error => console.error('Error uploading image:', error));
        };

        reader.onerror = () => {
                console.error('Error reading file:', reader.error);
        };
        // Read as base64 to preview in <img>
        reader.readAsDataURL(file);
});

// The status controls and the connection to the DB api
const driverStatus = new ChangeStatus(DSC, statusEndpoint, drvrToken, statusMsg);
driverStatus.init();

emergencyBtn.forEach(btn => {
        btn.addEventListener('click', () => {
                localStorage.setItem('isActiveEmergency', true);
                emergencyBackground.forEach(background => {
                        background.classList.remove('bg-besttrailsclr');
                        background.classList.add('bg-danger');
                        return isActiveEmergency = localStorage.getItem('isActiveEmergency');
                })
        })
});

window.addEventListener('load', () => {
        isActiveEmergency = localStorage.getItem('isActiveEmergency');
        if (isActiveEmergency === 'true') {
                emergencyBackground.forEach(background => {
                        background.classList.remove('bg-besttrailsclr');
                        background.classList.add('bg-danger');
                })
        }
})

// Set the theme.
const themeSet = {
        // Set dark theme.
        darkTheme() {
                const page = document.querySelector('html');
                const body = document.querySelector('body');
                const header = document.querySelector('header');
                const btnSwitch = themeBtn.childNodes[1];
                const textbox = document.querySelectorAll('textarea');
                if (myCurrentView === '/help') {
                        const cardImage = document.querySelector('#card-img');
                        cardImage.src = "../../dist/images-videos/busnitepics/drvr-area-nite.jpg";
                };
                themeBtnText.textContent = 'Light theme';
                btnSwitch.classList.remove("fa-moon", "text-dark");
                btnSwitch.classList.add("fa-sun", "text-btd-white-floral");
                page.setAttribute('data-bs-theme', 'dark');
                body.classList.add('niteMode');
                header.classList.add('nightMode');
                $(driverMenu).addClass('niteMode');
                if (textbox) {
                        $(textbox).removeClass('bg-btd-textarea-clr text-dark');
                }
                isDarkMode = true;
        },
        // Set the Light theme.
        lightTheme() {
                const page = document.querySelector('html');
                const body = document.querySelector('body');
                const header = document.querySelector('header');
                const btnSwitch = themeBtn.childNodes[1];
                const textbox = document.querySelectorAll('textarea');
                if (myCurrentView === '/help') {
                        const cardImage = document.querySelector('#card-img');
                        cardImage.src = "../../dist/images-videos/drvrarea1.jpg";
                };
                themeBtnText.textContent = 'Dark theme';
                btnSwitch.classList.remove('fa-sun', 'text-btd-white-floral');
                btnSwitch.classList.add('fa-moon', 'text-dark');
                page.removeAttribute('data-bs-theme');
                body.classList.remove('niteMode');
                header.classList.remove('nightMode');
                $(driverMenu).removeClass('niteMode');
                if (textbox) {
                        $(textbox).addClass('bg-btd-textarea-clr text-dark');
                }
                isDarkMode = false;
        },

        savePreference(userOverride = false) {
        // Save user's manual choice
                if (userOverride) {
                        localStorage.setItem('userThemeOverride', isDarkMode ? 'dark' : 'light');
                }
                // Save current theme for auto mode
                localStorage.setItem('isDarkMode', isDarkMode);
        }
};

// Manual button switch
function themeSwitcher(e) {
    e.preventDefault();
    if (!isDarkMode) {
        themeSet.darkTheme();
    } else {
        themeSet.lightTheme();
    }
    themeSet.savePreference(true); // user override
    updateThemeIndicator();
}

themeBtn.addEventListener('click', themeSwitcher, false);

// Auto theme based on time
function autoThemeSwitcher() {
    const hour = new Date().getHours();
    const userOverride = localStorage.getItem('userThemeOverride');

    // Only auto switch if no user override
    if (!userOverride) {
        if (hour >= 20 || hour <= 6) {
            if (!isDarkMode) themeSet.darkTheme();
        } else {
            if (isDarkMode) themeSet.lightTheme();
        }
        themeSet.savePreference(false);
    }
    updateThemeIndicator();
}

// Run auto theme every minute
setInterval(autoThemeSwitcher, 60 * 1000); // 60 seconds

// Load theme on page load
window.addEventListener('load', () => {
    const userOverride = localStorage.getItem('userThemeOverride');
    const lastTheme = localStorage.getItem('isDarkMode');

    if (userOverride === 'dark') {
        themeSet.darkTheme();
    } else if (userOverride === 'light') {
        themeSet.lightTheme();
    } else if (lastTheme === 'true') {
        themeSet.darkTheme();
    } else {
        themeSet.lightTheme();
        autoThemeSwitcher(); // initial check
    }
    updateThemeIndicator();
});

function updateThemeIndicator() {
    const userOverride = localStorage.getItem('userThemeOverride');
    if (userOverride) {
        themeModeIndicator.textContent = 'Manual';
        themeModeIndicator.classList.remove('theme-auto');
        themeModeIndicator.classList.add('theme-manual');
    } else {
        themeModeIndicator.textContent = 'Auto';
        themeModeIndicator.classList.remove('theme-manual');
        themeModeIndicator.classList.add('theme-auto');
    }
}

// Highlight the active link of the current page.
function activeLink () {
        getMenuItems.forEach(link => {
                let linkLocation = link.pathname;
                if (myCurrentView === linkLocation) {
                        link.setAttribute('aria-current', 'page'); //aria-current, page
                        link.classList.add('active'); //active                       
                } else {
                        link.removeAttribute('aria-current'); //aria-current
                        link.classList.remove('active'); //active 
                }
        })
}
activeLink();

window.addEventListener('load', () => {
        if (sessionStorage.getItem('status') === null && localStorage.getItem('status') === null) {
                sessionStorage.setItem('status', 'Official');
                let startUpStatus = sessionStorage.getItem('status');
                statusMsg.textContent = startUpStatus;                
        } else if (localStorage.getItem('status') !== null) {
                sessionStorage.clear;
                let drvrStatus = localStorage.getItem('status');
                statusMsg.textContent = drvrStatus;
        } else if (sessionStorage.getItem('status') !== null && localStorage.getItem('status') === null) {
                let drvrStatus = sessionStorage.getItem('status');
                statusMsg.textContent = drvrStatus;
        }
}, false);

$(logoutLink).on('click', () => {
        if (localStorage.getItem('status') === 'End of Shift') {
                localStorage.removeItem('status');
        }

        if (localStorage.getItem('isActiveEmergency') !== null) {
                localStorage.removeItem('isActiveEmergency');
        }

        if (sessionStorage.getItem('status') !== null) {
                sessionStorage.removeItem('status');
        }

        // Clear user override so auto theme resumes
        localStorage.removeItem('userThemeOverride');
        // Immediately apply auto theme
        autoThemeSwitcher();
        updateThemeIndicator();
});

if (myCurrentView !== '/') {
        $(changeStatusCon).removeClass('d-none');
};
        
/*const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl))*/