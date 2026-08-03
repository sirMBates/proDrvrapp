import { bdayCelebrationHandler } from "./celebration.js";
import { fetchDrvr, viewableDateTimeHelper, showFlashAlert } from "./helpers.js";
const drvrBirthDate = document.querySelector('#drvrbday')?.value ?? '';
const mainContent = document.querySelector('main');
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;
const dtHelper = viewableDateTimeHelper;
const drvrToken = document.querySelector('#drvrToken')?.value ?? '';
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

function showNoDashboardAssignments() {
    const tableBody = document.querySelector('#dashboard-info tbody');
    if (!tableBody) return;

    tableBody.replaceChildren();
    const emptyRow = document.createElement('tr');
    const emptyCell = document.createElement('td');
    emptyCell.colSpan = 6;
    emptyCell.className = 'text-center text-muted py-4';

    const heading = document.createElement('strong');
    heading.className = 'd-block mb-1';
    heading.textContent = 'No Active Assignment(s)';

    const message = document.createElement('span');
    message.textContent = 'You’re all caught up! New assignment(s) from dispatch will appear here automatically.';
    emptyCell.append(heading, message);

    emptyRow.appendChild(emptyCell);
    tableBody.appendChild(emptyRow);

    localStorage.setItem('assignments', JSON.stringify([]));
};

// --- Centralized Table Render Helper ---
function renderHomeTable(assignments, fromSync = false) {
        const tableBody = document.querySelector('#dashboard-info tbody');
        if (!tableBody) return;

        const activeAssignments = Array.isArray(assignments) ? assignments.filter(assignment => !assignment.completed_at && !assignment.canceled_at && assignment.assignment_status !== 'completed' && assignment.assignment_status !== 'canceled') : [];

        if (activeAssignments.length === 0) {
                showNoDashboardAssignments();

                if (fromSync) {
                        showFlashAlert('info', 'No active assignment(s) available.');
                }

                lastAssignmentsUpdate = Date.now();
                return;
        }

        tableBody.replaceChildren();
        activeAssignments.forEach(assignment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                        <td>${assignment.first_name ?? ''} ${assignment.last_name ?? ''}</td>
                        <td>${assignment.operator_id ?? ''}</td>
                        <td>${dtHelper(assignment.start_date_time, 'date')}</td>
                        <td>${dtHelper(assignment.start_date_time, 'time')}</td>
                        <td>${dtHelper(assignment.spot_time, 'time')}</td>
                        <td class="text-capitalize">${assignment.assignment_status ?? 'pending'}</td>
                `;
                tableBody.appendChild(row);
        });

        if (fromSync) {
                showFlashAlert('info', 'Assignments updated!');
        }

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
                                const activeAssignments = fresh.data.filter(a => !a.completed_at && !a.canceled_at);
                                localStorage.setItem('assignments', JSON.stringify(activeAssignments));
                                renderHomeTable(activeAssignments, true);
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
};

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
                if (data?.status === 'success' && Array.isArray(data.data)) {
                        const activeAssignments = data.data.filter(a => !a.completed_at && !a.canceled_at);
                        localStorage.setItem('assignments', JSON.stringify(activeAssignments));
                        renderHomeTable(activeAssignments);
                        handleBirthdayTheme();
                        return;
                }
                showNoDashboardAssignments();
                handleBirthdayTheme();
        })
        .catch(error => {
                console.error('Fetch operation failed:', error);
                showFlashAlert('error', 'The dashboard could not be refreshed.');
        });
});

// --- Tab Focus Auto Refresh (debounced) ---
document.addEventListener('visibilitychange', async () => {
    if (document.visibilityState !== 'visible') return;

    const now = Date.now();
    if (now - lastAssignmentsUpdate < 5000) return; // prevent too frequent reloads

    try {
        const fresh = await getAssignment("https://prodriver.local/getassignments", {
            method: 'GET',
            mode: 'cors',
            credentials: 'include',
            cache: 'no-store',
            headers: { 'X-CSRF-Token': drvrToken }
        });

        if (fresh?.status === 'success' && Array.isArray(fresh.data)) {
                const activeAssignments = fresh.data.filter(a => !a.completed_at && !a.canceled_at);
                localStorage.setItem('assignments', JSON.stringify(activeAssignments));
                renderHomeTable(activeAssignments, true);
                return;            
        }
        showNoDashboardAssignments();
        showFlashAlert('error', 'Unable to retrieve assignments.');
    } catch (error) {
        console.error('[SYNC] Failed to refresh dashboard:', error);
        showFlashAlert('error', 'Failed to refresh dashboard.');
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

function startBirthdayCelebration() {
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

birthdayThemeBtn?.addEventListener('click', startBirthdayCelebration, false);

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
