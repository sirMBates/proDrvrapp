import { bdayCelebrationHandler } from "./celebration.js";
import { fetchDrvr, viewableDateTimeHelper, showFlashAlert } from "./helpers.js";
const drvrBirthDate = document.querySelector('#drvrbday').value;
const mainContent = document.querySelector('main');
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;
const dtHelper = viewableDateTimeHelper;
const drvrToken = document.querySelector('#drvrToken').value;
const bannerMsg = document.querySelector('#statusMessage');
const dashBoardStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const dashboardStatusBtns = document.querySelector('#update-status-con');
const birthdayThemeBtn = document.querySelector('#birthday-theme-btn');
const todayDate = dtHelper(new Date(), 'date');
let lastAssignmentsUpdate = 0;

function resetDailyFlags () {
        const lastPlayed = localStorage.getItem('dateOfThemePlayed');
        if ( lastPlayed && lastPlayed !== todayDate) {
                sessionStorage.removeItem('celebrationOccured');
                localStorage.removeItem('themePlayedAlready');
                localStorage.removeItem('dateOfThemePlayed');
        }
};
resetDailyFlags();

// --- Centralized Table Render Helper ---
function renderHomeTable(assignments, fromSync = false) {
  const tableBody = document.querySelector('#dashboard-info tbody');
  tableBody.innerHTML = '';
  assignments.forEach(a => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${a.first_name} ${a.last_name}</td>
      <td>${a.operator_id}</td>
      <td>${dtHelper(a.start_date_time, 'date')}</td>
      <td>${dtHelper(a.start_date_time, 'time')}</td>
      <td>${dtHelper('1970-01-01 ' + a.spot_time, 'time')}</td>
      <td class="text-capitalize">${a.confirmed_assignment}</td>`;
    tableBody.appendChild(row);
  });

  if (fromSync) showFlashAlert('info', 'Assignments updated!');
  lastAssignmentsUpdate = Date.now();
};

// --- BroadcastChannel + Fallback Setup ---
let bcSupported = false;

try {
        const bc = new BroadcastChannel('assignments');
        bcSupported = true;
        bc.onmessage = async (evt) => {
                if (evt?.data?.type === 'assignments-updated') {
                        const fresh = await getAssignment("https://prodriver.local/getassignments", {
                                method: 'GET',
                                mode: 'cors',
                                credentials: 'include',
                                cache: 'no-store',
                                headers: {
                                        'X-CSRF-Token': drvrToken
                                }
                        });
                        if (fresh?.status === 'success') {
                                localStorage.setItem('assignments', JSON.stringify(fresh.data));
                                renderHomeTable(fresh.data, true);
                        }
                }
        };
} catch {
  bcSupported = false;
};

// --- Fallback if BroadcastChannel unavailable ---
if (!bcSupported) {
        window.addEventListener('storage', (event) => {
                if (event.key === 'assignments' && event.newValue) {
                        try {
                                const updated = JSON.parse(event.newValue);
                                renderHomeTable(updated, true);
                                console.log('[SYNC] Home updated from storage fallback.');
                        } catch (err) {
                                console.error('Failed to parse updated assignments:', err);
                        }
                }
        });
}

window.addEventListener('DOMContentLoaded', () => {
        const storedAssignments = localStorage.getItem("assignments");
        let cached = [];
        if (storedAssignments && storedAssignments !== "undefined" && storedAssignments !== "null") {
                try {
                        cached = JSON.parse(storedAssignments);
                        if ( Array.isArray(cached)) {
                                renderHomeTable(cached);
                        } else {
                                console.warn("Assignments cache not an array - resetting.");
                                localStorage.removeItem("assignments");
                        }
                } catch (err) {
                        console.warn("Invalid JSON in localStorage:", storedAssignments);
                        localStorage.removeItem("assignments");
                }
        };

        getAssignment("https://prodriver.local/getassignments", {
                method: 'GET',
                mode: 'cors',
                credentials: 'include',
                cache: 'no-store',
                headers: {
                        'X-CSRF-Token': drvrToken
                }
        })
        .then(data => {
                if (data.status === 'success' && data.data.length > 0) {
                        localStorage.setItem('assignments', JSON.stringify(data.data));
                        renderHomeTable(data.data);
                        handleBirthdayTheme();
                } else {
                         // Fallback: load profile if no assignments
                        return getDriver("https://prodriver.local/getprofile", {
                                method: 'GET',
                                mode: 'cors',
                                credentials: 'include',
                                cache: 'no-store',
                                headers: {
                                        'X-CSRF-Token': drvrToken
                                }
                        });
                }
        })
        .then(profile => {
                if (profile) {
                        const drvrMainTable = document.querySelector('#dashboard-info');
                        const fullname = drvrMainTable.childNodes[3].childNodes[1].childNodes[1];
                        const drvrId = drvrMainTable.childNodes[3].childNodes[1].childNodes[3];
                        const reportDate = drvrMainTable.childNodes[3].childNodes[1].childNodes[5];
                        const reportTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[7];
                        const spotTime = drvrMainTable.childNodes[3].childNodes[1].childNodes[9];

                        fullname.textContent = `${profile['firstName']} ${profile['lastName']}`;
                        drvrId.textContent = profile['operatorid'];
                        reportDate.textContent = 'No assignment available...';
                        reportTime.textContent = 'No assignment available...';
                        spotTime.textContent = 'No assignment available...';
                        handleBirthdayTheme();
                }
        })
        .catch(error => {
                console.error('Fetch operation failed:', error);
        });
});

// --- Tab Focus Auto Refresh (debounced) ---
document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'visible') {
                const now = Date.now();
                if (now - lastAssignmentsUpdate < 5000) return; // prevent too frequent reloads
                        const fresh = await getAssignment("https://prodriver.local/getassignments", {
                                method: 'GET',
                                mode: 'cors',
                                credentials: 'include',
                                cache: 'no-store',
                                headers: {
                                        'X-CSRF-Token': drvrToken
                                }
                        });
                if (fresh?.status === 'success') {
                        localStorage.setItem('assignments', JSON.stringify(fresh.data));
                        renderHomeTable(fresh.data, true);
                        console.log('[SYNC] Dashboard refreshed on tab focus.');
                }
        }
});

function handleBirthdayTheme() {
        const drvrBirthday = $(drvrBirthDate).val();
        const hasPlayed = localStorage.getItem('themePlayedAlready') === 'true';
        const now = new Date();
        const hour = now.getHours();
        if (!drvrBirthday || hasPlayed) {
                $(birthdayThemeBtn).addClass('d-none');
        } else {
                localStorage.setItem('birthdate', drvrBirthday);
                const birthDate = new Date(drvrBirthday);
                const bdayMonth = birthDate.getMonth();
                const bdayDay = birthDate.getDate();
                const todayMonth = now.getMonth();
                const todayDay = now.getDate();

                if (bdayMonth === todayMonth && bdayDay === todayDay && hour >= 6 && hour <= 23) {
                        $(birthdayThemeBtn).removeClass('d-none');
                } else {
                        $(birthdayThemeBtn).addClass('d-none');
                }
        };
};

function birthdayCelebrationHandler() {
        const drvrBDay = localStorage.getItem('birthdate');
        if (!drvrBDay) return; 
        const birthDate = new Date(drvrBDay);
        const now = new Date();
        const bdayMonth = birthDate.getMonth();
        const bdayDay = birthDate.getDate();
        const todayMonth = now.getMonth();       
        const todayDay = now.getDate();
        if (bdayMonth === todayMonth && bdayDay === todayDay) {
                const song = document.createElement("audio");
                song.src = '../../dist/audio/happy-birthday-clip.mp3';
                mainContent.insertAdjacentElement('afterbegin', song);
                song.play().then(() => {
                        bdayCelebrationHandler();
                        song.addEventListener('ended', () => {
                                song.remove();
                                $(birthdayThemeBtn).addClass('d-none');
                        });
                        sessionStorage.setItem('celebrationOccured', 'true');
                        localStorage.setItem('themePlayedAlready', 'true');
                        localStorage.setItem('dateOfThemePlayed', todayDate);
                }).catch(() => {
                        console.warn('Audio playback failed ( likely due to browser restrictions).');
                })
        }
};

birthdayThemeBtn.addEventListener('click', birthdayCelebrationHandler, false);

function removeDrvrGov() {
        const currentDate = dtHelper(new Date(), 'date');
        const dateOfThemePlayed = localStorage.getItem('dateOfThemePlayed');
        
        if (dateOfThemePlayed && dtHelper(dateOfThemePlayed, 'date') !== currentDate) {
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
