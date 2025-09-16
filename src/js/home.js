import { bdayCelebrationHandler } from "./celebration.js";
import { fetchDrvr } from "./drvrapi.js";
const drvrBirthDate = document.querySelector('#drvrbday');
const mainContent = document.querySelector('main');
const getDriver = fetchDrvr;
const bannerMsg = document.querySelector('header').childNodes[3].childNodes[3];
const dashBoardStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const dashboardStatusBtns = document.querySelector('#update-status-con');

window.addEventListener('DOMContentLoaded', () => {
        let dvrBirthday = $(drvrBirthDate).val();
        if ($.trim(dvrBirthday) !== '') {
                localStorage.setItem('birthdate', $(drvrBirthDate).val());
                const drvrTable = document.querySelector('table');
                let idCell = drvrTable.childNodes[3].childNodes[1].childNodes[1];
                let separateNames = idCell.textContent.split(" ");
                let firstName = separateNames[0];
                localStorage.setItem('driverName', firstName);
        };

        getDriver("https://prodriver.local/getprofile", { 
                mode: 'cors' 
        })
        .then(data => {
            const driver = data;
            const drvrMainTable = document.querySelector('#dashboard-info');
            const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
            const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
            if (driver) {
                fullname.textContent = `${driver['lastName']}, ${driver['firstName']}`;
                drvrId.textContent = driver['operatorid'];
            }
        })
        .catch(error => {
                const drvrMainTable = document.querySelector('#dashboard-info');
                const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
                const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
                if (error) {
                        fullname.textContent = 'Pro Driver';
                        drvrId.textContent = '0000';
                }
                console.error('There was a problem with the fetch operation:', error);
        })
});

function timeCelebrationHandler() {
        let currentTime = new Date();
        if ($(drvrBirthDate).val() !== '') {
                if (!sessionStorage.getItem('celebrationOccured') && !localStorage.getItem('themePlayedAlready')) {
                        let dateNow = new Date();
                        let drvrSavedBDay = localStorage.getItem('birthdate');
                        const birthdate = new Date(drvrSavedBDay);       
                        let bDayMonth = birthdate.getMonth();
                        let bDayDate = birthdate.getDate();
                        let todayMon = dateNow.getMonth();
                        let todayDate = dateNow.getDate();
                        //console.log(bDayDate);
                        if (bDayMonth === todayMon && bDayDate === todayDate) {
                                let bdaySong = document.createElement("audio");
                                mainContent.insertAdjacentElement('afterbegin', bdaySong);
                                $(bdaySong).attr('src', '../audio/happy-birthday-clip.mp3');
                                bdaySong.play();
                                bdayCelebrationHandler();
                                bdaySong.addEventListener('ended', () => {
                                        bdaySong.remove();
                                })
                        }
                }
                sessionStorage.setItem('celebrationOccured', 'true');
                localStorage.setItem('themePlayedAlready', 'true');
                const dateOptions = {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                };
                localStorage.setItem('dateOfThemePlayed', currentTime.toLocaleString('en-us', dateOptions));
        }
};

function handleCelebration () {
        let time = new Date();
        let timeHour = time.getHours();
        if (timeHour >= 6 && timeHour <= 23) {
                window.addEventListener('load', timeCelebrationHandler, false);
        }
};
handleCelebration();

function removeDrvrGov() {
        const dateOptions = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
        };
        let currentDate = new Date().toLocaleString('en-us', dateOptions);
        let birthdayStamp;
        let checkStamp;
        if (localStorage.getItem('dateOfThemePlayed') !== null) {
                birthdayStamp = localStorage.getItem('dateOfThemePlayed');
                checkStamp = birthdayStamp.toLocaleString('en-us', dateOptions);
        }
        if (currentDate !== checkStamp) {
                localStorage.removeItem('driverName');
        }

};
window.addEventListener('load', removeDrvrGov, false);

window.addEventListener('resize', () => {
        let btnGrp = document.querySelector('#update-status-con');
        const screenSize = window.innerWidth;
        if (screenSize <= 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                //console.log("Screen size is small enough for small button group.");
        } else if (screenSize >= 901) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                //console.log("Screen size is large enough for large button group.");
        }
}, false);

window.addEventListener('load', () => {
        let btnGrp = document.querySelector('#update-status-con');
        const screenSize = window.innerWidth;
        if (screenSize <= 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                //console.log("Screen size is small enough for small button group.");
        } else if (screenSize > 901) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                //console.log("Screen size is large enough for large button group.");
        }
}, false);

window.addEventListener('load', () => {
        if (sessionStorage.getItem('status') === null && localStorage.getItem('status') === null) {
                sessionStorage.setItem('status', 'Official');
                let startUpStatus = sessionStorage.getItem('status');
                dashBoardStatusValue.textContent = startUpStatus;                
        } else if (localStorage.getItem('status') !== null) {
                sessionStorage.removeItem('status');
                let drvrStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = drvrStatus;
        } else if (sessionStorage.getItem('status') !== null && localStorage.getItem('status') === null) {
                let drvrStatus = sessionStorage.getItem('status');
                dashBoardStatusValue.textContent = drvrStatus;
        }
}, false);

dashboardStatusBtns.addEventListener('click', (e) => {
        if (e.target === dashboardStatusBtns.childNodes[1]) {
                localStorage.setItem('status', 'Enroute to garage');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[3]) {
                localStorage.setItem('status', 'Arrived at garage');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[5]) {
                localStorage.setItem('status', 'Enroute to location');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[7]) {
                localStorage.setItem('status', 'Arrived at location');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[9]) {
                localStorage.setItem('status', 'On assignment');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[11]) {
                localStorage.setItem('status', 'Emergency');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
}, false);
