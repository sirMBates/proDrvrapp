const myCurrentView = window.location.pathname;
const profCon = document.querySelector("#profilecon");
const getMenuItems = document.querySelectorAll(".nav-item");
const textLink = document.querySelector("#useraccess");
const driverMenu = document.querySelector(".offcanvas-body");
let viewPayCard = driverMenu.childNodes[5];
let themeBtn = document.querySelector("#themeBtn");
let themeBtnText = themeBtn.nextElementSibling;
const changeStatusCon = document.querySelector('.offcanvas-body').childNodes[9]//.firstElementChild;
const logoutLink = document.querySelector('.offcanvas-body').childNodes[11].firstElementChild;
//const mainPageEl = document.querySelector('html');
//console.log(changeStatusCon);
let isDarkMode;

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
        if (currentView === '/payroll') {
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

export const bannerMsg = document.querySelector('header').childNodes[3].childNodes[3];
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
}

const changeStatusBtns = changeStatusCon.childNodes[3];
$(changeStatusBtns).on('click', (e) => {
        if (e.target === changeStatusBtns.childNodes[1].firstChild) {
                localStorage.setItem('status', 'Enroute to garage');
                let changeStatus = localStorage.getItem('status');
                bannerMsg.textContent = changeStatus;
        }

        if (e.target === changeStatusBtns.childNodes[3].firstChild) {
                localStorage.setItem('status', 'Arrived at garage');
                let changeStatus = localStorage.getItem('status');
                bannerMsg.textContent = changeStatus;
        }

        if (e.target === changeStatusBtns.childNodes[5].firstChild) {
                localStorage.setItem('status', 'Enroute to location');
                let changeStatus = localStorage.getItem('status');
                bannerMsg.textContent = changeStatus;
        }

        if (e.target === changeStatusBtns.childNodes[7].firstChild) {
                localStorage.setItem('status', 'Arrived at location');
                let changeStatus = localStorage.getItem('status');
                bannerMsg.textContent = changeStatus;
        }

        if (e.target === changeStatusBtns.childNodes[9].firstChild) {
                localStorage.setItem('status', 'On assignment');
                let changeStatus = localStorage.getItem('status');
                bannerMsg.textContent = changeStatus;
        }
});
        
/*const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl))*/