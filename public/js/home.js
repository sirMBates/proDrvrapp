import { bdayCelebrationHandler } from "./celebration.js";
import { bannerMsg } from "./main.js";
import { fetchDrvr } from "./getDrvr.js";

const drvrBirthDate = document.querySelector('#drvrbday');
const mainContent = document.querySelector('main');
const fetchDriver = fetchDrvr;

window.addEventListener('load', () => {
        let dvrBirthday = $(drvrBirthDate).val();
        if ($.trim(dvrBirthday) !== '') {
                localStorage.setItem('birthdate', $(drvrBirthDate).val());
                const drvrTable = document.querySelector('table');
                let idCell = drvrTable.childNodes[3].childNodes[1].childNodes[1];
                let separateNames = idCell.textContent.split(" ");
                let firstName = separateNames[0];
                localStorage.setItem('driverName', firstName);
        };

        fetchDriver("http://prodriver.local/getprofile", { mode: 'cors'})
        .then(data => {
            const driver = data;
            const drvrMainTable = document.querySelector('#dashboard-info');
            const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
            const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
            if (driver) {
                fullname.textContent = `${driver['firstname']} ${driver['lastname']}`;
                drvrId.textContent = driver['driverid'];
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
                                const celebration = new JSConfetti();
                                bdayCelebrationHandler(celebration);
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

const changeStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const updateStatusBtns = document.querySelector('#update-status-con');
window.addEventListener('load', () => {
        if (sessionStorage.getItem('status') === null && localStorage.getItem('status') === null) {
                sessionStorage.setItem('status', 'Official');
                let startUpStatus = sessionStorage.getItem('status');
                changeStatusValue.textContent = startUpStatus;                
        } else if (localStorage.getItem('status') !== null) {
                sessionStorage.removeItem('status');
                let drvrStatus = localStorage.getItem('status');
                changeStatusValue.textContent = drvrStatus;
        } else if (sessionStorage.getItem('status') !== null && localStorage.getItem('status') === null) {
                let drvrStatus = sessionStorage.getItem('status');
                changeStatusValue.textContent = drvrStatus;
        }
}, false);
//console.log(changeStatusValue);
updateStatusBtns.addEventListener('click', (e) => {
        if (e.target === updateStatusBtns.childNodes[1]) {
                localStorage.setItem('status', 'Enroute to garage');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[3]) {
                localStorage.setItem('status', 'Arrived at garage');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[5]) {
                localStorage.setItem('status', 'Enroute to location');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[7]) {
                localStorage.setItem('status', 'Arrived at location');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
                //console.log(changeStatusValue);
        }
}, false);
