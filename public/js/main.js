const myCurrentView = window.location.pathname;
const getMenuItems = document.querySelectorAll(".nav-item");
const textLink = document.querySelector("#useraccess");
const driverMenu = document.querySelector(".offcanvas-body");
let viewPayCard = driverMenu.childNodes[5];
let themeBtn = document.querySelector("#themeBtn");
let themeBtnText = themeBtn.nextElementSibling;
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
                if (myCurrentView === "/public/views/home.php") {
                        let enrouteBtn = document.querySelector("#check_in_btns").childNodes[1];
                        $(enrouteBtn).removeClass("btn-dark");
                        $(enrouteBtn).addClass("btn-light");
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
                if (myCurrentView === "/public/views/home.php") {
                        let enrouteBtn = document.querySelector("#check_in_btns").childNodes[1];
                        $(enrouteBtn).removeClass("btn-light");
                        $(enrouteBtn).addClass("btn-dark");
                }
                isDarkMode = false;
        },
        
        setThemeLocally() {
                localStorage.setItem('isDarkMode', '');
                return localStorage.isDarkMode = isDarkMode;
        }
};

// Button switcher for themes.
function themeSwitcher (e) {
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
        if (updateHr() >= 20 || updateHr <= 6) {
                themeSet.darkTheme();
                localStorage.isDarkMode = true;
        }
        return themeSet.setThemeLocally();
};

// Highlight the active link of the current page.
function activeLink () {
        getMenuItems.forEach(item => {
                let currentPage = window.location.href;
                let itemLink = item.firstElementChild;
                let itemLocation = itemLink.href;
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
        if (currentView === '/public/views/payroll.php') {
                viewPayCard.classList.remove('d-none');        
        } else {
                viewPayCard.classList.add('d-none');
        }

        if (currentView === '/public/views/printable.php') {
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
/*const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl))*/