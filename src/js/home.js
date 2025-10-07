import { bdayCelebrationHandler } from "./celebration.js";
import { fetchDrvr, viewableDateTimeHelper } from "./helpers.js";
const drvrBirthDate = document.querySelector('#drvrbday');
const mainContent = document.querySelector('main');
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;
const dtHelper = viewableDateTimeHelper;
const drvrToken = document.querySelector('#drvrToken').value;
const bannerMsg = document.querySelector('#statusMessage');
const dashBoardStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const dashboardStatusBtns = document.querySelector('#update-status-con');
const birthdayThemeBtn = document.querySelector('#birthday-theme-btn');

window.addEventListener('DOMContentLoaded', () => {
        const dvrBirthday = $(drvrBirthDate).val();
        if ($.trim(dvrBirthday) !== '') {
                localStorage.setItem('birthdate', $(drvrBirthDate).val());
                localStorage.getItem('driverName');
                let time = new Date();
                let timeHour = time.getHours();
                if (timeHour >= 6 && timeHour <= 23) {
                        $(birthdayThemeBtn).removeClass('d-none');
                }
        } else if (sessionStorage.getItem('celebrationOccured') === 'true' && localStorage.getItem('themePlayedAlready') === 'true') {
                $(birthdayThemeBtn).addClass('d-none');
        }

        getAssignment("https://prodriver.local/getassignments", {
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
            const drvrMainTable = document.querySelector('#dashboard-info');
            const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
            const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
            const reportDate = drvrMainTable.childNodes[3].childNodes[1].childNodes[5];
            const reportTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[7];
            const spotTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[9];
            // Check if assignments exist
            if (driver.status === 'success' && driver.data.length > 0) {
                const assignment = driver.data[0]; // For now, just take the first
                fullname.textContent = `${assignment['first_name']} ${assignment['last_name']}`;
                localStorage.setItem('driverName', assignment['first_name']);
                drvrId.textContent = assignment['operator_id'];
                reportDate.textContent = dtHelper(assignment['start_date_time'], 'date');
                reportTime.textContent = dtHelper(assignment['start_date_time'], 'time');
                spotTime.textContent = dtHelper(`1970-01-01 ${assignment['spot_time']}`, 'time');
            } else {
                // No assignments → fallback to getProfile
                console.log("No assignments found, loading profile instead...");
                return getDriver("https://prodriver.local/getprofile", {
                        method: 'GET', 
                        mode: 'cors',
                        credentials: 'include',
                        headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': drvrToken
                        } 
                });
            }
        })
        .then(data => {
            if (data) {
                const driver = data;
                const drvrMainTable = document.querySelector('#dashboard-info');
                const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
                const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
                const reportDate = drvrMainTable.childNodes[3].childNodes[1].childNodes[5];
                const reportTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[7];
                const spotTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[9];
                fullname.textContent = `${driver['lastName']}, ${driver['firstName']}`;
                localStorage.setItem('driverName', driver['first_name']);
                drvrId.textContent = driver['operatorid'];
                reportDate.textContent = 'No assignment available...';
                reportTime.textContent = 'No assignment available...';
                spotTime.textContent = 'No assignment available...';
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

function birthdayCelebrationHandler() {
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
                                $(bdaySong).attr('src', '../../dist/audio/happy-birthday-clip.mp3');
                                bdaySong.play();
                                bdayCelebrationHandler();
                                bdaySong.addEventListener('ended', () => {
                                        bdaySong.remove();
                                })
                        }
                }
                sessionStorage.setItem('celebrationOccured', 'true');
                localStorage.setItem('themePlayedAlready', 'true');
                localStorage.setItem('dateOfThemePlayed', dtHelper(currentTime, 'date'));
        }
};

function endCelebration () {
        if (sessionStorage.getItem('celebrationOccured') === true && localStorage.getItem('themePlayedAlready') === true) {
                $(birthdayThemeBtn).addClass('d-none');
        }
};

birthdayThemeBtn.addEventListener('click', birthdayCelebrationHandler, false);

function removeDrvrGov() {
        const currentDate = dtHelper(new Date(), 'date');
        let checkStamp = null;
        if (localStorage.getItem('dateOfThemePlayed') !== null) {
                const birthdayStamp = localStorage.getItem('dateOfThemePlayed');
                checkStamp = dtHelper(birthdayStamp, 'date');
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
                localStorage.setItem('status', 'End of Shift');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
        if (e.target === dashboardStatusBtns.childNodes[13]) {
                localStorage.setItem('status', 'Emergency');
                let changeStatus = localStorage.getItem('status');
                dashBoardStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                if (sessionStorage.getItem('status') !== null) {
                        sessionStorage.removeItem('status');
                }
        }
}, false);
