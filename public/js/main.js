import { buildModal } from './appmodal.js';
import { fetchDrvr } from './drvrapi.js';
import { ChangeStatus } from './changestatus.js';
let isDarkMode;
const infoBtn = document.querySelector('#notifyinfo');
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoModBtn = document.querySelector('#info-ok');
const myCurrentView = window.location.pathname;
const profCon = document.querySelector("#profilecon");
const getMenuItems = document.querySelectorAll(".nav-item");
const textLink = document.querySelector("#useraccess");
const driverMenu = document.querySelector(".offcanvas-body");
const viewPayCard = driverMenu.childNodes[5];
const themeBtn = document.querySelector("#themeBtn");
const themeBtnText = themeBtn.nextElementSibling;
const changeStatusCon = document.querySelector('.offcanvas-body').childNodes[9]//.firstElementChild;
const logoutLink = document.querySelector('.offcanvas-body').childNodes[11].firstElementChild;
const emergencyBtn = document.querySelectorAll('.status-emergency');
let isActiveEmergency;
const emergencyBackground = document.querySelectorAll('.bg-besttrailsclr');
const DSC = document.querySelectorAll('.set-status'); // (D)river(S)tatus(C)ontrol :)
const statusEndpoint = "http://prodriver.local/setstatus";
const drvrTokenValue = document.getElementById('drvrToken').value;
const bannerMsg = document.querySelector('header').childNodes[3].childNodes[3];


$(document).ready(() => {
    const modalInstance = new bootstrap.Modal(infoModal, {
        backdrop: 'static',
        keyboard: false
    });

    infoBtn.addEventListener('click', () => {
        modalInstance.show();
    });

    infoModal.addEventListener('shown.bs.modal', () => {
        if (window.location.pathname === '/') {
                infoModalMsg.info('Welcome to your dashboard. From here, you can update your status (which will automatically notify dispatch) and get brief details about your job(s) (if any assignments assigned to you) for the day. If and when you need to update your status from another page, click the icon in the navigation bar and search for switch status. That is where you\'ll also find other options as well.', 'Ok');
        }

        if (window.location.pathname === '/orders') {
                infoModalMsg.info(`This is where your job orders will be viewed. You\'ll be able to edit certain times, details and add notes for dispatch and your personal reference.<br> You must confirm the job by clicking the button below once received.<br> When you\'re completing the job, click the edit button down below if there are any changes to be made.<br> If no changes, complete the dispatch order.<br> You can also cancel the job if dispatch allows.`, 'Ok');
        }

        if (window.location.pathname === '/profile') {
                infoModalMsg.info('Here on your account info profile, this is where you can view your personal information. You can only update your email, mobile number and password. If you would like to update any of the 3, click the button next to the field you would like to update.', 'Ok');
        }

        if (window.location.pathname === '/timesheet') {
                infoModalMsg.info('This is your timesheet (cha`ching`💰). This sheet will hold a record of each job/order you\'ve done for the week. Once the week is over, a new sheet will be made available for you to utilize. If your payroll dept requests, you may send this sheet to them as is, print it out or download a copy for yourself.', 'Ok');
        }
    });
    
    infoModBtn.addEventListener('click', () => {
        modalInstance.hide();
    });
});

window.addEventListener('DOMContentLoaded', () => {
        fetchDrvr("http://prodriver.local/getprofile", { mode: 'cors'})
        .then(data => {
            const driver = data;
            const drvrMainMenu = document.querySelector('#useraccess');
            const drvrMainHeader = drvrMainMenu.childNodes[1].childNodes[3]; 
            if (driver) {
                drvrMainHeader.textContent = `${driver[3]} ${driver[4]}`;
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

// The status controls and the connection to the DB api
const driverStatus = new ChangeStatus(DSC, statusEndpoint, drvrTokenValue, bannerMsg);
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
                themeBtnText.textContent = 'Light theme';
                btnSwitch.classList.remove("fa-moon");
                btnSwitch.classList.remove("text-dark");
                btnSwitch.classList.add("fa-sun");
                btnSwitch.classList.add("text-btd-white-floral");
                $(page).attr('data-bs-theme', 'dark');
                $(body).addClass('niteMode');
                $(header).addClass('nightMode');
                $(driverMenu).addClass('niteMode');
                if (textbox) {
                        $(textbox).removeClass('bg-btd-textarea-clr text-dark');
                }
                
                isDarkMode = true;
        },
        // Set the Light theme.
        lightTheme() {
                let page = document.querySelector('html');
                let body = document.querySelector('body');
                let header = document.querySelector('header');
                let btnSwitch = themeBtn.childNodes[1];
                const textbox = document.querySelectorAll('textarea');
                themeBtnText.textContent = 'Dark theme';
                btnSwitch.classList.remove('fa-sun');
                btnSwitch.classList.remove("text-btd-white-floral");
                btnSwitch.classList.add('fa-moon');
                btnSwitch.classList.add("text-dark");
                $(page).removeAttr('data-bs-theme');
                $(body).removeClass('niteMode');
                $(header).removeClass('nightMode');
                $(driverMenu).removeClass('niteMode');
                if (textbox) {
                        $(textbox).addClass('bg-btd-textarea-clr text-dark');
                }
                
                isDarkMode = false;
        },
        
        setThemeLocally() {
                localStorage.setItem('isDarkMode', '');
                return localStorage.isDarkMode = isDarkMode;
        }
};

// Button switcher for themes.
function themeSwitcher(e) {
        e.preventDefault();
        if (!isDarkMode) {
                themeSet.darkTheme();
        } else {
                themeSet.lightTheme();

        }
        return themeSet.setThemeLocally();
}
themeBtn.addEventListener('click', themeSwitcher, false);

// Load theme from local storage across different pages of web app.
window.addEventListener('load', function () {
        let currTheme = localStorage.getItem('isDarkMode');
        if (currTheme === 'true') {
                themeSet.darkTheme();
        } else {
                themeSet.lightTheme();
        }
        return themeSet.setThemeLocally();
});

// Allow for time range to switch themes.
function updateHr() {
        const time = new Date();
        let timeHr = time.getHours();
        return timeHr;
};

function enableDarkMode () {
        if (updateHr() >= 20 || updateHr() <= 6) {
                themeSet.darkTheme();
                localStorage.isDarkMode = true;
        }
        return themeSet.setThemeLocally();
};

window.addEventListener('load', enableDarkMode, false);

// Highlight the active link of the current page.
function activeLink () {
        getMenuItems.forEach(item => {
                let currentPage = window.location.pathname;
                let itemLink = item.firstElementChild;
                let itemLocation = itemLink.pathname;
                if (itemLocation !== currentPage) {
                        itemLink.classList.remove('active');
                        itemLink.removeAttribute('aria-current');                        
                } else {
                        itemLink.classList.add('active');
                        itemLink.setAttribute('aria-current', 'page');
                }
        })
}
activeLink();

//View pay card link in driver menu when on payroll page.
function viewablePayCard () {
        let currentView = window.location.pathname;
        if (currentView === '/timesheet') {
                viewPayCard.classList.remove('d-none');        
        } else {
                viewPayCard.classList.add('d-none');
        }

        if (currentView === '/printable') {
                viewPayCard.classList.remove('d-none', 'dropdown');
                viewPayCard.classList.add('dropdown-center');
                viewPayCard.childNodes[1].classList.add('d-none');
                viewPayCard.childNodes[3].classList.remove('d-none');        
        } else {
                viewPayCard.childNodes[3].classList.add('d-none');
                viewPayCard.childNodes[1].classList.remove('d-none');
                viewPayCard.classList.remove('dropdown-center');
                viewPayCard.classList.add('dropdown');
        }
};
$(window).on('load', viewablePayCard);

// Profile image display in navbar.
// console.log(profCon.childNodes);
// [default image is firstChild.nextElementSibling] & [file selector is 3]
const profileImage = profCon.childNodes[1].childNodes[0];
const profileInput = profCon.childNodes[3];
$(profileInput).on('change', (e) => {
        //profileImage.setAttribute('src', URL.createObjectURL(profileInput.files[0]));
        const file = e.target.files[0]; // Get the first selected file
        if (file) {
                const reader = new FileReader();

                // Define what happens when the file is successfully read
                reader.onload = (e) => {
                        profileImage.setAttribute('src', e.target.result); // Display file content
                };

                // Handle errors
                reader.onerror = () => {
                        console.error('Error reading file:', reader.error);
                };

                // Read the file as image URL
                reader.readAsDataURL(file);

                // Optionally, you can also log the file name and size
                console.log('Selected file:', file.name, 'Size:', file.size, 'bytes');
                // You can also check the file type if needed
                console.log('File type:', file.type);
                // Check if the file is an image
                if (!file.type.startsWith('image/')) {
                        alert('Please select a valid image file.');
                        profileImage.setAttribute('src', "../../images-videos/logoandicons/photo-camera-interface-symbol-for-button.png");
                        return;
                }
                // Reset the input value to allow re-uploading the same file
                profileInput.value = ''; // Clear the input value to allow re-uploading the same file
                // Reset the profile image to default if no file is selected
                if (file.name === "photo-camera-interface-symbol-for-button.png") {
                        profileImage.setAttribute('src', "../../images-videos/logoandicons/photo-camera-interface-symbol-for-button.png");
                }
                // If you want to upload the image to the server, you can do it here
                // For example, using fetch or XMLHttpRequest to send the file to your server
                
                /*if ($(profileImage).attr('src') !== "../../images-videos/logoandicons/photo-camera-interface-symbol-for-button.png") {
                        const formData = new FormData();
                        formData.append('profileImage', file);

                        fetch('/profile-image', {
                                method: 'POST',
                                body: formData
                        })
                        .then(response => response.text())
                        .then(data => alert(data))
                        .catch(error => console.error('Error uploading image:', error));
                } else {
                        alert("Please select a valid image file.");
                }*/
        }
});

window.addEventListener('load', () => {
        if (sessionStorage.getItem('status') === null && localStorage.getItem('status') === null) {
                sessionStorage.setItem('status', 'Official');
                let startUpStatus = sessionStorage.getItem('status');
                bannerMsg.textContent = startUpStatus;                
        } else if (localStorage.getItem('status') !== null) {
                sessionStorage.clear;
                let drvrStatus = localStorage.getItem('status');
                bannerMsg.textContent = drvrStatus;
        } else if (sessionStorage.getItem('status') !== null && localStorage.getItem('status') === null) {
                let drvrStatus = sessionStorage.getItem('status');
                bannerMsg.textContent = drvrStatus;
        }
}, false);

$(logoutLink).on('click', () => {
        if (localStorage.getItem('status') !== null) {
                localStorage.removeItem('status');
        }

        if (sessionStorage.getItem('status') !== null) {
                sessionStorage.removeItem('status');
        }
});

if (window.location.pathname !== '/') {
        $(changeStatusCon).removeClass('d-none');
};
        
/*const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl))*/