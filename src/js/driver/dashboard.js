import { bdayCelebrationHandler } from "./celebration.js";
import { proDriverRequest, viewableDateTimeHelper, showFlashAlert } from '../shared/helpers.js';
import { DriverStatusHistory } from './driver-status-history.js';
import { MessagePreview } from './message-preview.js';
import { buildModal } from '../shared/appmodal.js';

const drvrBirthDate = document.querySelector('#drvrbday')?.value ?? '';
const mainContent = document.querySelector('main');
const statusHistoryContainer = document.getElementById('driver-status-history');
const driverStatusHistory = new DriverStatusHistory(statusHistoryContainer);
const getAssignment = proDriverRequest;
const dtHelper = viewableDateTimeHelper;
const drvrToken = document.querySelector('#drvrToken')?.value ?? '';
const dashBoardStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const birthdayThemeBtn = document.querySelector('#birthday-theme-btn');
const todayDate = dtHelper(new Date(), 'date');
const dashboardTableBody = document.querySelector('#dashboard-info tbody');
const messagePreviewContainer = document.getElementById('dashboard-message-preview');
const messagePreview = new MessagePreview(messagePreviewContainer);
let lastAssignmentsUpdate = 0;
let bcSupported = false;
let assignmentsChannel = null;
let currentHomeAssignments = [];

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

        currentHomeAssignments = [];
        tableBody.replaceChildren();

        const emptyRow = document.createElement('tr');
        const emptyCell = document.createElement('td');
        emptyCell.colSpan = 7;
        emptyCell.className = 'text-center text-muted py-4';

        const icon = document.createElement('i');
        icon.className = 'bi bi-bus-front fs-2 d-block mb-2';
        icon.setAttribute('aria-hidden', 'true');

        const heading = document.createElement('strong');
        heading.className = 'd-block mb-1';
        heading.textContent = 'No Active Assignment(s)';

        const message = document.createElement('span');
        message.textContent = 'You’re all caught up! New assignment(s) from dispatch will appear here automatically.';
        emptyCell.append(icon, heading, message);

        emptyRow.appendChild(emptyCell);
        tableBody.appendChild(emptyRow);

        localStorage.setItem('assignments', JSON.stringify([]));
};

function getActiveAssignments(assignments) {
    if (!Array.isArray(assignments)) {
        return [];
    }

    return assignments.filter(assignment => {
        const status = String(assignment.assignment_status ?? '').toLowerCase();
        return (!assignment.completed_at && !assignment.canceled_at && status !== 'completed' && status !== 'canceled');
    });
};

function showHomeAssignmentDetails(assignment) {
        if (!assignment) {
                return;
        }

        const garageReportTime = dtHelper(assignment.start_date_time, 'time');
        const spotTime = dtHelper(assignment.spot_time, 'time');
        const origin = escapeHTML(assignment.origin?.trim() || 'Not provided');
        const destination = escapeHTML(assignment.destination?.trim() || 'Not provided');
        const pickupDetails = escapeHTML(assignment.pickup_details?.trim() || 'No pickup details provided.');
        const destinationDetails = escapeHTML(assignment.destination_details?.trim() || 'No destination details provided.');

        const message = `
                <div class="text-start">
                        <div class="row g-3">
                                <div class="col-6">
                                        <span class="fw-semibold d-block">
                                                Garage Report Time
                                        </span>
                                        <span>${escapeHTML(garageReportTime)}</span>
                                </div>

                                <div class="col-6">
                                        <span class="fw-semibold d-block">
                                                Spot Time
                                        </span>
                                        <span>${escapeHTML(spotTime)}</span>
                                </div>

                                <div class="col-12">
                                        <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                        <span class="fw-semibold d-block">
                                                Origin
                                        </span>
                                        <span>${origin}</span>
                                </div>

                                <div class="col-md-6">
                                        <span class="fw-semibold d-block">
                                                Destination
                                        </span>
                                        <span>${destination}</span>
                                </div>

                                <div class="col-12">
                                        <hr class="my-1">
                                </div>

                                <div class="col-12">
                                        <span class="fw-semibold d-block">
                                                Pickup Details
                                        </span>
                                        <span>${pickupDetails}</span>
                                </div>

                                <div class="col-12">
                                        <span class="fw-semibold d-block">
                                                Destination Details
                                        </span>
                                        <span>${destinationDetails}</span>
                                </div>
                        </div>
                </div>
        `;

        buildModal.info(message, 'Got It!');

        const infoModal = document.querySelector('#info-modal');
        if (!infoModal) {
                return;
        }

        bootstrap.Modal.getOrCreateInstance(infoModal).show();
};

// --- Centralized Table Render Helper ---
function renderHomeTable(assignments) {
        const tableBody = document.querySelector('#dashboard-info tbody');
        if (!tableBody) return;

        const activeAssignments = getActiveAssignments(assignments);
        currentHomeAssignments = activeAssignments;

        if (activeAssignments.length === 0) {
                showNoDashboardAssignments();
                lastAssignmentsUpdate = Date.now();
                return;
        }

        tableBody.replaceChildren();
        activeAssignments.forEach(assignment => {
                const row = document.createElement('tr');
                row.classList.add('dashboard-assignment-row');
                const status = String(assignment.assignment_status ?? 'pending').toLowerCase();
                const isPending = status === 'pending';
                const isConfirmed = status === 'confirmed';

                row.dataset.orderId = assignment.order_id ?? '';
                row.dataset.assignmentControl = assignment.assignment_control ?? '';

                row.innerHTML = `
                        <td>${assignment.order_id ?? ''}</td>
                        <td>${assignment.operator_id ?? ''}</td>
                        <td>${dtHelper(assignment.start_date_time, 'date')}</td>
                        <td>${dtHelper(assignment.start_date_time, 'time')}</td>
                        <td>${assignment.vehicle_id ?? ''}</td>
                        <td>${dtHelper(assignment.spot_time, 'time')}</td>
                        <td class="assignment-home-action"></td>
                `;

                const actionCell = row.querySelector('.assignment-home-action');

                if (isPending) {
                        const buttonGroup = document.createElement('div');
                        buttonGroup.className = 'd-flex flex-wrap gap-2';

                        const confirmBtn = document.createElement('button');
                        confirmBtn.type = 'button';
                        confirmBtn.className = 'btn btn-sm btn-success';
                        confirmBtn.textContent = 'Confirm';
                        confirmBtn.dataset.assignmentAction = 'confirm';

                        const cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'btn btn-sm btn-outline-danger';
                        cancelBtn.textContent = 'Cancel';
                        cancelBtn.dataset.assignmentAction = 'cancel';

                        buttonGroup.append(confirmBtn, cancelBtn);
                        actionCell.appendChild(buttonGroup);
                } else if (isConfirmed) {
                        const confirmed = document.createElement('span');
                        confirmed.className = 'badge text-bg-success';
                        confirmed.textContent = 'Confirmed';
                        actionCell.appendChild(confirmed);
                } else {
                        actionCell.textContent = status || 'Pending';
                        actionCell.classList.add('text-capitalize');
                }

                tableBody.appendChild(row);
        });

        lastAssignmentsUpdate = Date.now();
};

function findHomeAssignment(row) {
        const orderId = row?.dataset.orderId ?? '';
        const assignmentControl = row?.dataset.assignmentControl ?? '';

        return currentHomeAssignments.find(assignment => {
                return (String(assignment.order_id ?? '') === String(orderId) && String(assignment.assignment_control ?? '') === String(assignmentControl));
        }) ?? null;
};

function escapeHTML(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
};

async function refreshHomeAssignments() {
        const data = await getAssignment('https://prodriver.local/getassignments', {
                method: 'GET',
                mode: 'cors',
                credentials: 'include',
                cache: 'no-store',
                headers: {
                        'X-CSRF-Token': drvrToken
                }
        });

        if (data?.status !== 'success' || !Array.isArray(data.data)) {
                throw new Error(data?.message ?? 'Unable to retrieve assignments.');
        }

        const activeAssignments = getActiveAssignments(data.data);
        localStorage.setItem('assignments', JSON.stringify(activeAssignments));

        renderHomeTable(activeAssignments);
        return activeAssignments;
};

function broadcastAssignmentsUpdate(assignments) {
        if (assignmentsChannel) {
                assignmentsChannel.postMessage({
                        type: 'assignments-updated'
                });
                return;
        }

        // StorageEvent fallback for browsers without BroadcastChannel.
        localStorage.setItem('assignments', JSON.stringify(assignments));
};

dashboardTableBody?.addEventListener('click', async (event) => {
        const actionButton = event.target.closest('[data-assignment-action]');

        // Confirm / Cancel owns this click.
        if (actionButton) {
                event.preventDefault();
                event.stopPropagation();

                const row = actionButton.closest('tr');
                const orderId = row?.dataset.orderId ?? '';
                const assignmentControl = row?.dataset.assignmentControl ?? '';
                const action = actionButton.dataset.assignmentAction ?? '';

                if (!orderId || !assignmentControl || !['confirm', 'cancel'].includes(action)) {
                        showFlashAlert('error', 'Invalid assignment request.');
                        return;
                }

                await submitAssignmentDecision(orderId, assignmentControl, action, row);
                return;
        }

        // Otherwise, treat the assignment row as a details request.
        const row = event.target.closest('tr[data-assignment-control]');

        if (!row) {
                return;
        }

        event.preventDefault();
        event.stopPropagation();

        const assignment = findHomeAssignment(row);
        if (!assignment) {
                showFlashAlert('error', 'Unable to load assignment details.');
                return;
        }

        showHomeAssignmentDetails(assignment);
});

async function submitAssignmentDecision(orderId, assignmentControl, action, row) {
        const buttons = row.querySelectorAll('[data-assignment-action]');

        buttons.forEach(button => {
                button.disabled = true;
        });

        const formData = new FormData();

        formData.append('__method', 'PATCH');
        formData.append('order_id', orderId);
        formData.append('assignment_control', assignmentControl);
        formData.append(action, '1');

        try {
                const data = await proDriverRequest('/assignmentstatushandler', {
                        method: 'POST',
                        headers: {
                                'X-CSRF-Token': drvrToken
                        },
                        body: formData
                });

                if (data?.status !== 'success') {
                        throw new Error(data?.message ?? 'Assignment decision failed.');
                }

                showFlashAlert('success', data.message ?? (action === 'confirm' ? 'Assignment confirmed.' : 'Assignment canceled.'));

                const refreshedAssignments = await refreshHomeAssignments();
                broadcastAssignmentsUpdate(refreshedAssignments);
        } catch (error) {
                console.error('[HOME ASSIGNMENT DECISION ERROR]', error);
                showFlashAlert('error', error?.message ?? 'Unable to update the assignment.');

                buttons.forEach(button => {
                        button.disabled = false;
                });
        }
};

driverStatusHistory.init();
messagePreview.init();

// --- BroadcastChannel + Fallback Setup ---
try {
        assignmentsChannel = new BroadcastChannel('assignments');
        bcSupported = true;

        assignmentsChannel.onmessage = async (evt) => {
                if (evt?.data?.type !== 'assignments-updated') {
                        return;
                }

                try {
                        await refreshHomeAssignments();
                } catch (error) {
                        console.error('[SYNC] Failed to refresh assignments:', error);
                        showFlashAlert('error', 'Unable to refresh assignments.');
                }
        };
} catch {
        bcSupported = false;
        assignmentsChannel = null;
};

// --- Fallback if BroadcastChannel unavailable ---
if (!bcSupported) {
        window.addEventListener('storage', (event) => {
                if (event.key === 'assignments' && event.newValue) {
                        try {
                                const updated = JSON.parse(event.newValue);
                                renderHomeTable(updated);
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

        refreshHomeAssignments().then(() => {
                handleBirthdayTheme();
        })
        .catch(error => {
                console.error('Fetch operation failed:', error);
                showNoDashboardAssignments();
                handleBirthdayTheme();
                showFlashAlert('error', 'The dashboard could not be refreshed.');
        });
});

// --- Tab Focus Auto Refresh (debounced) ---
document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState !== 'visible') {
                return;
        }

        const now = Date.now();
        if (now - lastAssignmentsUpdate < 5000) {
                return;
        }

        try {
                await refreshHomeAssignments();
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
