import { fetchDrvr, viewableDateTimeHelper, showFlashAlert, fadeOut, fadeIn, ServiceTimeCalculator, highlightErrorElement, clearValidationState, focusFirstInvalid, setSubmittingState } from "./helpers.js";
import { buildModal } from "./appmodal.js";
import { normalizeDecimalValue, validateEditableElement, validateAssignmentTextarea, validateCrossFieldRules, validateCurrentAssignmentFields, appendHiddenFields, toInputDateTime, toDisplayDateTime, appendEditableFields } from "./assignment-form.js";
import { handleAssignmentFetch } from "./pwa.js";

const primaryA = document.querySelector('#tableA');
const groupB = document.querySelector('#tableB');
const groupC = document.querySelector('#tableC');
const groupD = document.querySelector('#tableD');
const locationPickup = document.querySelector('#pickup-details');
const locationDestination = document.querySelector('#destination-details');
const operatorNotes = document.querySelector('#shared-job-note');
const clickCells = document.querySelectorAll('.editable-data');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const saveBtn = document.querySelector('#save-assignment');
const completeBtn = document.querySelector('#submit-assignment');
const drvrToken = document.querySelector('#drvrToken').value;
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;
const confirmAssignment = fetchDrvr;
const cancelAssignment = fetchDrvr;
const dtHelper = viewableDateTimeHelper;
const drvrAlert = showFlashAlert;
const COMPLETED_ASSIGNMENTS_KEY = 'completedAssignmentData';
let showAssignment;
let assignments = [];
let currentIndex = 0;
let pagination = null;
// Auto-refresh Assignments on Tab Focus (debounced, full sync) ===
let lastAssignmentsUpdate = 0;

function updateButtonStates(assignment) {
    if (!assignment) return;

    const status = String(assignment.assignment_status ?? 'pending').toLowerCase();

    if (status === 'confirmed') {
        $(confirmBtn).prop('disabled', true);
        $(cancelBtn).prop('disabled', true);
        $(saveBtn).prop('disabled', false);
        $(completeBtn).prop('disabled', false);
        return;
    } 
    
    if (status === 'pending') {
        $(confirmBtn).prop('disabled', false);
        $(cancelBtn).prop('disabled', false);
        $(saveBtn).prop('disabled', true);
        $(completeBtn).prop('disabled', true);
        return;
    }

    // fallback — unknown state
    $(confirmBtn).prop('disabled', true);
    $(cancelBtn).prop('disabled', true);
    $(saveBtn).prop('disabled', true);
    $(completeBtn).prop('disabled', true);
};

// --- Broadcast Helper (BC + Storage Fallback) ---
function broadcastAssignmentsUpdate(assignments) {
    // Always update localStorage first (used by Home view & offline)
    localStorage.setItem('assignments', JSON.stringify(assignments));

    try {
        // Use BroadcastChannel if available
        const bc = new BroadcastChannel('assignments');
        bc.postMessage({ type: 'assignments-updated' });
        bc.close();
        console.log('[SYNC] BroadcastChannel update sent.');
    } catch (err) {
        // Fallback to StorageEvent simulation (older browsers)
        console.warn('[SYNC] BC unavailable, using StorageEvent fallback.');
        window.dispatchEvent(new StorageEvent('storage', {
            key: 'assignments',
            newValue: JSON.stringify(assignments)
        }));
    }
};

function getCurrentAssignment() {
    // safe guard
    return (Array.isArray(assignments) && assignments.length > 0 && typeof currentIndex === 'number') ? assignments[currentIndex] : null;
};

function refreshCurrentAssignment() {
    // re-render same index (uses showAssignment defined inside the DOMContentLoaded; we'll expose a small helper for that)
    // we'll call window._refreshAssignmentFromOutside() (see below) which showAssignment will set up
    if ( typeof window._refreshAssignmentFromOutside === 'function') {
        window._refreshAssignmentFromOutside();
    }
};

function clearStoredSignatures(assignmentControl) {
    const control = String(assignmentControl ?? '');
    if (!control) return;

    localStorage.removeItem(`pre-signature:${control}`);
    localStorage.removeItem(`post-signature:${control}`);

    if (localStorage.getItem('warnModalShownFor') === control) {
        localStorage.removeItem('warnModalShownFor');
    }

    console.log(`[SIGNATURE] Cleared stored signatures for ${control}.`);
};

async function refreshAssignmentsFromServer(preferredOrderId = null) {
    try {
        const response = await getAssignment('https://prodriver.local/getassignments', {            
            method: 'GET',
            mode: 'cors',
            credentials: 'include',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': drvrToken
            }
        });

        if (!response || response.status !== 'success' || !Array.isArray(response.data)) {
            console.warn('[ASSIGNMENT REFRESH] Invalid assignment response.', response);
            return false;
        }

        /*
         * Preserve the previous list before replacing it.
         * This lets us identify assignments that were completed
         * or canceled and therefore disappeared from the server list.
         */
        const previousAssignments = Array.isArray(assignments) ? [...assignments] : [];

        /*
         * The backend should already return active assignments only,
         * but this remains as defensive frontend filtering.
         */
        const refreshedAssignments = response.data.filter(assignment => {
            const status = String(assignment.assignment_status ?? '').toLowerCase();
            return (!assignment.completed_at && !assignment.canceled_at && status !== 'completed' && status !== 'canceled');
        });

        /*
         * Build a set containing every assignment control that
         * still exists in the refreshed active list.
         */
        const refreshedAssignmentControls = new Set(refreshedAssignments.map(assignment => String(assignment.assignment_control ?? '')).filter(Boolean));

        /*
         * An assignment that existed previously but no longer exists
         * in the server response was completed, canceled, or otherwise
         * removed from the driver's active assignments.
         */
        const removedAssignments = previousAssignments.filter(assignment => {
            const assignmentControl = String(assignment.assignment_control ?? '');
            return (assignmentControl !== '' && !refreshedAssignmentControls.has(assignmentControl));
        });

        /*
         * Remove locally stored signatures belonging only to
         * assignments that are no longer active.
         */
        removedAssignments.forEach(assignment => {
            clearStoredSignatures(assignment.assignment_control);
        });

        assignments = refreshedAssignments;
        localStorage.setItem('assignments', JSON.stringify(assignments));

        /*
         * No active assignments remain.
         */
        if (assignments.length === 0) {
            currentIndex = 0;

            sessionStorage.setItem('lastAssignmentIndex', '0');
            document.querySelector('#assignment-pager')?.remove();
            pagination = null;
            showNoAssignments();

            console.log('[ASSIGNMENT REFRESH] No active assignments remain.', {
                removed: removedAssignments.map(assignment => assignment.assignment_control)
            });

            return true;
        }

        /*
         * Preserve the preferred assignment when it still exists.
         * This is useful after confirmation or Save Changes.
         */
        const preferredIndex = preferredOrderId !== null ? assignments.findIndex(assignment => String(assignment.order_id) === String(preferredOrderId)) : -1;

        if (preferredIndex >= 0) {
            currentIndex = preferredIndex;
        } else {
            /*
             * The preferred assignment may have been completed or
             * canceled. Keep the index inside the new array bounds
             * so the next available assignment is displayed.
             */
            const safeCurrentIndex = Number.isInteger(currentIndex) ? currentIndex : 0;
            currentIndex = Math.min(Math.max(safeCurrentIndex, 0), assignments.length - 1);
        }

        sessionStorage.setItem('lastAssignmentIndex', String(currentIndex));

        /*
         * Recreate the pager so the number of pills agrees
         * with the refreshed assignment list.
         */
        if (typeof window._rebuildAssignmentPagination === 'function') {
            window._rebuildAssignmentPagination();
        }

        showAssignment(currentIndex);
        const currentAssignment = getCurrentAssignment();

        if (currentAssignment) {
            updateButtonStates(currentAssignment);
        }

        console.log('[ASSIGNMENT REFRESH] Active assignments refreshed.', {
            count: assignments.length, 
            currentIndex,
            assignmentControl: currentAssignment?.assignment_control ?? null,
            orderId: currentAssignment?.order_id ?? null,
            removedAssignments: removedAssignments.map(assignment => ({
                assignmentControl: assignment.assignment_control,
                orderId: assignment.order_id
            }))
        });

        return true;
    } catch (error) {
        console.error('[ASSIGNMENT REFRESH] Failed to refresh assignments from server:', error);
        return false;
    }
};

function showNoAssignments() {
    assignments = [];
    currentIndex = 0;

    localStorage.setItem('assignments', JSON.stringify([]));
    sessionStorage.setItem('lastAssignmentIndex', '0');

    const assignmentCard = document.querySelector('.assignment-card');
    assignmentCard?.classList.add('hidden');
    document.querySelector('#assignment-pager')?.remove();

    pagination = null;
    let emptyState = document.querySelector('#no-assignments');

    if (!emptyState) {
        emptyState = document.createElement('section');
        emptyState.id = 'no-assignments';
        emptyState.className = 'card text-center p-4 py-5';

        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-circle-check ' + 'fs-1 text-success mb-3';
        icon.setAttribute('aria-hidden', 'true');

        const heading = document.createElement('h2');
        heading.className = 'h4 mb-2';
        heading.textContent = 'No Active Assignment(s)';

        const message = document.createElement('p');
        message.className = 'text-muted mb-0';
        message.textContent = 'You’re all caught up! New assignments ' + 'assigned by dispatch will appear here automatically.';
        emptyState.append(icon, heading, message);

        /*
         * Insert the empty state next to the assignment card.
         * Adjust the parent if your layout requires a different
         * placement.
         */
        assignmentCard?.parentElement?.appendChild(emptyState);
    }

    emptyState.classList.remove('hidden');

    $(confirmBtn).prop('disabled', true);
    $(cancelBtn).prop('disabled', true);
    $(saveBtn).prop('disabled', true);
    $(completeBtn).prop('disabled', true);
};

function showAssignmentInterface() {
    document.querySelector('.assignment-card')?.classList.remove('hidden');
    document.querySelector('#no-assignments')?.classList.add('hidden');
};

function loadNextAssignment(afterIndex) {
    // Case 1: There are still other assignments left
    if (assignments.length > 0) {
        const nextIndex = afterIndex >= assignments.length ? assignments.length - 1 : afterIndex;
        if (nextIndex >= 0) {
            showAssignment(nextIndex);
            return;
        }
    }

    showNoAssignments();
};

async function clearAssignmentUI() {
    const assignmentCard = document.querySelector('.assignment-card');
    if (!assignmentCard) return;

    await fadeOut(assignmentCard); // smooth fade out

    // Grab your primary assignment container (or however your current assignment is shown)
    const primaryCoachId = primaryA.childNodes[3].childNodes[1].childNodes[1];
    const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
    const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];

    // Clear out the content
    primaryCoachId.textContent = 'Loading next assignment...';
    primaryDrvrId.textContent = '';
    primaryDrvrName.textContent = '';

    // Optionally disable action buttons to prevent interactions mid-transition
    $(confirmBtn).prop('disabled', false);
    $(cancelBtn).prop('disabled', false);
    $(saveBtn).prop('disabled', true);
    $(completeBtn).prop('disabled', true);

    await fadeIn(assignmentCard); // smooth fade back in
};

function getCurrentOrderId() {
    return document.querySelector("#tableA tbody tr td:nth-child(4)")?.textContent.trim() || '';
};

function getAssignmentStorageId(assignment) {
    if (!assignment) return '';

    const orderId = String(assignment.order_id ?? '').trim();
    const orderRef = String(assignment.order_ref ?? '').trim();

    if (!orderId || !orderRef) return '';

    return `${orderId}_${orderRef}`;
};

function getAssignmentDraftKey(assignment) {
    const storageId = getAssignmentStorageId(assignment);
    return storageId ? `assignment_draft_${storageId}` : '';
};

function normalizeDraftComparisonValue(value) {
    return String(value ?? '').trim();
};

function updateAssignmentDraftField(assignment, field, value) {
    if (!assignment || !field) return;

    const key = getAssignmentDraftKey(assignment);
    if (!key) return;

    try {
        const draft = JSON.parse(localStorage.getItem(key) || '{}');

        const originalValues = {
            vehicle_id: assignment.vehicle_id ?? '',
            actual_drop_time: assignment.actual_drop_time ?? '',
            actual_end_time: assignment.actual_end_time
                ? assignment.actual_end_time.replace(' ', 'T').slice(0, 16)
                : '',
            total_hrs: assignment.total_job_time ?? '',
            driving_time: assignment.driving_time ?? '',
            pickup_details: assignment.pickup_details ?? '',
            destination_details: assignment.destination_details ?? '',
            shared_job_note: assignment.current_driver_shared_note ?? ''
        };

        const normalizedValue = normalizeDraftComparisonValue(value);
        const normalizedOriginal = normalizeDraftComparisonValue(
            originalValues[field]
        );

        if (normalizedValue === normalizedOriginal) {
            delete draft[field];
        } else {
            draft[field] = value;
        }

        if (Object.keys(draft).length === 0) {
            localStorage.removeItem(key);
        } else {
            localStorage.setItem(key, JSON.stringify(draft));
        }
    } catch (err) {
        console.warn('Failed to update assignment draft:', err);
    }
};

function saveCurrentVisibleAssignmentDraft() {
    const assignment = getCurrentAssignment();
    if (!assignment) return;

    document.querySelectorAll('.editable-data').forEach(cell => {
        const field = cell.dataset.field;
        if (!field) return;

        const input = cell.querySelector('input');
        let value = '';
        if (input) {
            value = input.value.trim();

            if (cell.dataset.type === 'datetime' || cell.dataset.type === 'datetime-local') {
                value = value.slice(0, 16);
            }
        } else if (cell.dataset.type === 'datetime' || cell.dataset.type === 'datetime-local') {
            value = cell.dataset.raw || '';
        } else {
            value = cell.textContent.trim();
        }
        updateAssignmentDraftField(assignment, field, value);
    });

    const textareaDraftFields = [
        {
            id: 'pickup-details',
            field: 'pickup_details'
        },
        {
            id: 'destination-details',
            field: 'destination_details'
        },
        {
            id: 'shared-job-note',
            field: 'shared_job_note'
        }
    ];

    textareaDraftFields.forEach(({ id, field }) => {
        const el = document.getElementById(id);
        if (!el) return;

        updateAssignmentDraftField(assignment, field, el.value.trim());
    });
};

function getAssignmentDraft(assignment) {
    if (!assignment) return {};

    const key = getAssignmentDraftKey(assignment);
    if (!key) return {};

    try {
        return JSON.parse(localStorage.getItem(key) || '{}');
    } catch(err) {
        console.warn('Failed to read assignment draft: ', err);
        return {};
    }
};

function clearAssignmentDraftByIdentity(orderId, orderRef) {
    const normalizedOrderId = String(orderId ?? '').trim();
    const normalizedOrderRef = String(orderRef ?? '').trim();

    if (!normalizedOrderId || !normalizedOrderRef) return false;

    const key =
        `assignment_draft_${normalizedOrderId}_${normalizedOrderRef}`;

    try {
        const existed = localStorage.getItem(key) !== null;
        localStorage.removeItem(key);
        return existed;
    } catch (err) {
        console.warn(
            'Failed to clear assignment draft:',
            err
        );

        return false;
    }
};

function clearSubmittedAssignmentDraftFromUrl() {
    const params = new URLSearchParams(
        window.location.search
    );

    const status = params.get('status');
    const orderId = params.get('order_id');
    const orderRef = params.get('order_ref');

    const successfulStatuses = [
        'saved',
        'completed'
    ];

    if (
        !successfulStatuses.includes(status) ||
        !orderId ||
        !orderRef
    ) {
        return;
    }

    const cleared = clearAssignmentDraftByIdentity(
        orderId,
        orderRef
    );

    if (cleared) {
        console.log(
            `[DRAFT] Cleared ${status} assignment draft:`,
            { orderId, orderRef }
        );
    }

    params.delete('status');
    params.delete('order_id');
    params.delete('order_ref');

    const remainingQuery = params.toString();

    const cleanUrl =
        window.location.pathname +
        (remainingQuery ? `?${remainingQuery}` : '') +
        window.location.hash;

    window.history.replaceState({}, document.title, cleanUrl);
};

function hasCurrentAssignmentDraft () {
    const assignment = getCurrentAssignment();
    if (!assignment) return false;

    const draft = getAssignmentDraft(assignment);
    return Object.keys(draft).length > 0;
};

function reconcileCompletedAssignmentFromRedirect() {
    const url = new URL(window.location.href);
    const status = url.searchParams.get('status');
    const completedOrderId = url.searchParams.get('completed') ?? (status === 'completed' ? url.searchParams.get('order_id') : null);

    if (!completedOrderId) {
        return null;
    }

    let storedAssignments = [];

    try {
        const storedData = localStorage.getItem('assignments');
        const parsedData = storedData ? JSON.parse(storedData) : [];

        if (Array.isArray(parsedData)) {
            storedAssignments = parsedData;
        }
    } catch (error) {
        console.error('[ASSIGNMENT STATE] Could not read cached assignments:', error);
    }

    const completedAssignment = storedAssignments.find(assignment => String(assignment.order_id) === String(completedOrderId));

    if (completedAssignment?.assignment_control) {
        clearStoredSignatures(completedAssignment.assignment_control);
    }

    const remainingAssignments = storedAssignments.filter((assignment) => {
            return String(assignment.order_id) !== String(completedOrderId);
        });
    try {
        localStorage.setItem('assignments', JSON.stringify(remainingAssignments));
    } catch (error) {
        console.error('[ASSIGNMENT STATE] Could not update cached assignments:', error);
    }

    const storedIndex = Number.parseInt(sessionStorage.getItem('lastAssignmentIndex') ?? '0', 10);
    const safeStoredIndex = Number.isInteger(storedIndex) && storedIndex >= 0 ? storedIndex : 0;
    const correctedIndex = remainingAssignments.length === 0 ? 0 : Math.min(safeStoredIndex, remainingAssignments.length - 1);
    sessionStorage.setItem('lastAssignmentIndex', String(correctedIndex));

    /*
     * Remove the completed parameter so refreshing
     * does not attempt to process it again.
     */
    url.searchParams.delete('completed');
    if (status === 'completed') {
        url.searchParams.delete('status');
        url.searchParams.delete('order_id');
        url.searchParams.delete('order_ref');
    }

    window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
    console.log(`[ASSIGNMENT STATE] Removed completed order ${completedOrderId}.`, {
            before: storedAssignments.length,
            remaining: remainingAssignments.length,
            currentIndex: correctedIndex
        }
    );

    return {
        assignments: remainingAssignments,
        currentIndex: correctedIndex
    };
};

window.addEventListener('DOMContentLoaded', () => {
    const reconciledState = reconcileCompletedAssignmentFromRedirect();
    clearSubmittedAssignmentDraftFromUrl();
    // Create pagination controls (Bootstrap)
    function createPaginationControls() {
        const existing = document.querySelector('#assignment-pager');
        if ( existing ) existing.remove();

        if (assignments.length <= 1) {
            return null;
        }

        const container = document.createElement('div');
        container.id = 'assignment-pager';
        container.classList.add('card-footer', 'd-flex', 'justify-content-between', 'align-items-center', 'mt-3');

        const prevBtn = document.createElement('button');
        prevBtn.classList.add('btn', 'btn-outline-secondary');
        prevBtn.setAttribute('type', 'button');
        prevBtn.textContent = 'Previous';

        const stepIndicator = document.createElement('div');
        stepIndicator.classList.add('d-flex', 'align-items-center', 'mx-3', 'gap-1');
        stepIndicator.id = 'step-indicator';

        const nextBtn = document.createElement('button');
        nextBtn.classList.add('btn', 'btn-primary');
        nextBtn.setAttribute('type', 'button');
        nextBtn.textContent = 'Next';

        container.append(prevBtn, stepIndicator, nextBtn);
        primaryA.parentElement.parentElement.append(container); // place under primary section

        // Build pill buttons
        function renderPills() {
            const stepIndicator = document.querySelector('#step-indicator');
            stepIndicator.innerHTML = ''; // Clear previous pills

            assignments.forEach((_, i) => {
                const pill = document.createElement('button');
                pill.type = 'button';
                pill.classList.add('btn', i === currentIndex ? 'btn-primary' : 'btn-outline-secondary');
                pill.textContent = i + 1;
                pill.addEventListener('click', () => {
                    showAssignment(i);
                    renderPills();
                    updateButtons();
                });
                stepIndicator.appendChild(pill);
            });
        };

        // Update Previous / Next buttons state
        function updateButtons() {
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === assignments.length - 1;
        };

        prevBtn.addEventListener('click', () => {
            showAssignment(currentIndex - 1);
            renderPills();
            updateButtons();
        });

        nextBtn.addEventListener('click', () => {
            showAssignment(currentIndex + 1);
            renderPills();
            updateButtons();
        });

        // Initial render
        renderPills();
        updateButtons();

        // Expose a function to refresh pills and buttons from showAssignment
        return { renderPills, updateButtons };
    };

    window._rebuildAssignmentPagination = function() {
        pagination = createPaginationControls();
        return pagination;
    }

    function renderSharedNotes(assignment) {
        const container = document.querySelector('#existing-shared-notes');
        const list = document.querySelector('#shared-notes-list');

        if ( !container || !list ) return;

        list.innerHTML = '';

        const notes = assignment.shared_notes || [];

        if ( !notes.length ) {
            container.classList.add('hidden');
            return;
        }

        notes.forEach(note => {
            const item = document.createElement('div');
            item.className = 'border rounded p-2 mb-2 bg-light';
            
            item.innerHTML = `<small class="text-muted d-block mb-1">
                                    Updated: ${note.updated_at || ''}
                                </small>
                                <p class="mb-0">${note.note_body || ''}</p>`;
            list.appendChild(item);
        });
        container.classList.remove('hidden');
    }

    // Renders the assignment details to your existing UI tables
    showAssignment = function(index) {
        if (assignments.length === 0) {
            showNoAssignments();
            return;
        }

        if (index < 0 || index >= assignments.length) return; // guard

        showAssignmentInterface();
        currentIndex = index;
        sessionStorage.setItem('lastAssignmentIndex', index);
        const assignment = assignments[index];

        // Existing DOM refs
        const primaryCoachId = primaryA.childNodes[3].childNodes[1].childNodes[1];
        const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
        const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];
        const primaryOrderNumber = primaryA.childNodes[3].childNodes[1].childNodes[7];
        const primaryNumOfCoaches = primaryA.childNodes[3].childNodes[1].childNodes[9];
        const secondaryStartTime = groupB.childNodes[3].childNodes[1].childNodes[1];
        const secondarySpotTime = groupB.childNodes[3].childNodes[1].childNodes[3];
        const secondaryLeaveTime = groupB.childNodes[3].childNodes[1].childNodes[5];
        const secondaryReturnTime = groupB.childNodes[3].childNodes[1].childNodes[7];
        const secondaryDropTime = groupB.childNodes[3].childNodes[1].childNodes[9];
        const tertiaryEndTime = groupC.childNodes[3].childNodes[1].childNodes[1];
        const tertiaryActEndTime = groupC.childNodes[3].childNodes[1].childNodes[3];
        const tertiaryShiftTime = groupC.childNodes[3].childNodes[1].childNodes[5];
        const tertiaryDriveTime = groupC.childNodes[3].childNodes[1].childNodes[7];
        const tertiaryOrigin = groupC.childNodes[3].childNodes[1].childNodes[9];
        const quaternaryDestination = groupD.childNodes[3].childNodes[1].childNodes[1];
        const quaternaryGroupNameandLeader = groupD.childNodes[3].childNodes[1].childNodes[3];
        const quaternaryGroupLeaderMobile = groupD.childNodes[3].childNodes[1].childNodes[5];
        const quaternaryCustomerNameandPhone = groupD.childNodes[3].childNodes[1].childNodes[7];
        const quaternaryContactNameandMobile = groupD.childNodes[3].childNodes[1].childNodes[9];
        const pickupDetails = locationPickup;
        const destinationDetails = locationDestination;
        const opNotes = operatorNotes;

        // Populate fields
        primaryCoachId.textContent = assignment['vehicle_id'];
        primaryDrvrId.textContent = assignment['operator_id'];
        primaryDrvrName.textContent = `${assignment['last_name']}, ${assignment['first_name']}`;
        primaryOrderNumber.textContent = assignment['order_id'];
        primaryNumOfCoaches.textContent = assignment['num_of_coaches'];
        secondaryStartTime.textContent = dtHelper(assignment['start_date_time']);
        secondaryStartTime.dataset.raw = assignment['start_date_time'];
        secondarySpotTime.textContent = dtHelper(`1970-01-01 ${assignment['spot_time']}`, 'time');
        secondaryLeaveTime.textContent = dtHelper(assignment['leave_date_time']);
        secondaryReturnTime.textContent = dtHelper(assignment['return_date_drop_time']);
        const rawActualDropTime = String(assignment['actual_drop_time'] ?? '').trim();
        secondaryDropTime.dataset.raw = rawActualDropTime ? rawActualDropTime.slice(0, 5) : '';
        secondaryDropTime.textContent = rawActualDropTime ? dtHelper(rawActualDropTime, 'time') : '';
        tertiaryEndTime.textContent = dtHelper(assignment['end_date_time']);
        tertiaryEndTime.dataset.raw = assignment['end_date_time'];
        tertiaryActEndTime.textContent = assignment['actual_end_time'] ? dtHelper(assignment['actual_end_time'], 'datetime') :  '';
        tertiaryActEndTime.dataset.raw = assignment['actual_end_time'] ? assignment['actual_end_time'].replace(' ', 'T').slice(0, 16) : '';
        tertiaryShiftTime.textContent = assignment['total_job_time'];
        tertiaryDriveTime.textContent = assignment['driving_time'] || '0.00';
        tertiaryOrigin.textContent = assignment['origin'];
        quaternaryDestination.textContent = assignment['destination'];
        quaternaryGroupNameandLeader.textContent = `${assignment['group_name']}, ${assignment['group_leader']}`;
        quaternaryGroupLeaderMobile.textContent = assignment['group_leader_mobile'];
        quaternaryCustomerNameandPhone.textContent = `${assignment['customer_name']}, ${assignment['customer_phone']}`;
        quaternaryContactNameandMobile.textContent = `${assignment['contact_name']}, ${assignment['contact_mobile']}`;
        pickupDetails.value = assignment['pickup_details'];
        destinationDetails.value = assignment['destination_details'];
        opNotes.value = assignment['current_driver_shared_note'] || '';
        renderSharedNotes(assignment);

        // Apply local draft values on top of server values
        const draft = getAssignmentDraft(assignment);

        if (draft.vehicle_id !== undefined) {
            primaryCoachId.textContent = draft.vehicle_id;
        };

        if (draft.actual_drop_time !== undefined) {
            secondaryDropTime.textContent = draft.actual_drop_time;
        };

        if (draft.actual_end_time !== undefined) {
            tertiaryActEndTime.textContent = draft.actual_end_time ? dtHelper(draft.actual_end_time, 'datetime') : '';

            tertiaryActEndTime.dataset.raw = draft.actual_end_time || '';
        };

        if (draft.total_hrs !== undefined) {
            tertiaryShiftTime.textContent = draft.total_hrs;
        };

        if (draft.driving_time !== undefined) {
            tertiaryDriveTime.textContent = draft.driving_time;
        };

        if (draft['pickup_details'] !== undefined) {
            pickupDetails.value = draft['pickup_details'];
        };

        if (draft['destination_details'] !== undefined) {
            destinationDetails.value = draft['destination_details'];
        };

        if (draft['shared_job_note'] !== undefined) {
            opNotes.value = draft['shared_job_note'];
        };

        document.querySelector('.assignment-card')?.setAttribute(
            'data-original-assignment',
            JSON.stringify({
                vehicle_id: assignment['vehicle_id'] ?? '',
                actual_drop_time: assignment['actual_drop_time'] ?? '',
                actual_end_time: assignment['actual_end_time'] ?? '',
                total_hrs: assignment['total_job_time'] ?? '',
                driving_time: assignment['driving_time'] ?? '',
                pickup_details: assignment['pickup_details'] ?? '',
                destination_details: assignment['destination_details'] ?? '',
                shared_job_note: assignment['current_driver_shared_note'] ?? ''
            })
        );

        // Update pills and buttons if pagination exists
        if (pagination) {
            pagination.renderPills();
            pagination.updateButtons();
        }
        updateButtonStates(assignment);

        const assignmentForm = document.querySelector('.assignment-card');
        if (assignmentForm) {
            assignmentForm.dataset.currentIndex = currentIndex.toString();
            // also store order_id for convenience
            if (assignment && assignment['order_id']) {
                assignmentForm.dataset.orderId = assignment['order_id'];
            } else {
                delete assignmentForm.dataset.orderId;
            }
        }

        window.dispatchEvent(new CustomEvent('assignmentChanged', {
            detail: {
                assignmentControl: assignment.assignment_control,
                requiresSignature: Number(assignment.signature_required) === 1
            }
        }));

        if (typeof window.updateSignatureState === 'function') {
            window.updateSignatureState({
                assignmentControl: assignment.assignment_control,
                requiresSignature: Number(assignment.signature_required) === 1,
                signatureStatus: assignment.signature_status ?? ''
            });
        }

        // Expose refresh hook for refreshCurrentAssignment() ( safe, single assignment re-render)
        window._refreshAssignmentFromOutside = function() {
            showAssignment(currentIndex);
        };
    };

    const storedAssignments = localStorage.getItem("assignments");
    if (reconciledState) {
        assignments = reconciledState.assignments;
        currentIndex = reconciledState.currentIndex;
    } else if (storedAssignments) {
        try {
            const parsedAssignments = JSON.parse(storedAssignments);
            assignments = Array.isArray(parsedAssignments) ? parsedAssignments : [];
        } catch (error) {
            console.error('[INIT] Failed to restore assignments:', error);
            assignments = [];
        }

        const savedIndex = Number.parseInt(sessionStorage.getItem('lastAssignmentIndex') ?? '0', 10);
        currentIndex = Number.isInteger(savedIndex) ? savedIndex : 0;
    }

    /* Filter before creating pagination.
    * Otherwise, the pills are created from the old
    * assignment count.
    */
    assignments = assignments.filter(assignment => !assignment.completed_at && !assignment.canceled_at);
    currentIndex = assignments.length === 0 ? 0 : Math.min(currentIndex, assignments.length - 1);
    sessionStorage.setItem('lastAssignmentIndex', String(currentIndex));
    if (assignments.length > 0) {
        pagination = createPaginationControls();

        setTimeout(() => {
            showAssignment(currentIndex);
            const current = getCurrentAssignment();

            if (current) {
                updateButtonStates(current);
            }

            console.log(`[INIT] Restored assignment index ${currentIndex}.`);
        }, 100)
    } else {
        showNoAssignments();
    }

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
        const operator = data;
        
        if (operator.status === 'success' && Array.isArray(operator.data) && operator.data.length > 0) {
            assignments = operator.data.filter(assignment => !assignment.completed_at && !assignment.canceled_at);
            localStorage.setItem('assignments', JSON.stringify(assignments));
            currentIndex = assignments.length === 0 ? 0 : Math.min(currentIndex, assignments.length - 1);
            sessionStorage.setItem('lastAssignmentIndex', String(currentIndex));

            if (assignments.length > 0) {
                pagination = createPaginationControls();
                showAssignment(currentIndex);
                return;
            }
        }

        showNoAssignments();
    })
    .catch(error => { 
        console.error('There was a problem with the fetch operation:', error);
        showNoAssignments();
    });

    // 🔎 MutationObserver: watch for changes to the assignment order cell
    const targetNode = document.querySelector('#tableA');
    if (targetNode) {
        const observer = new MutationObserver(() => {
            const orderCell = targetNode.querySelector('td:nth-child(4)'); // 4th cell = order ID
            if (!orderCell) return;

            const previousOrderId = orderCell.dataset.previousOrderId || '';
            const currentOrderId = orderCell.textContent.trim();
            
            if (currentOrderId && currentOrderId !== 'No assignment available...' && currentOrderId !== previousOrderId) {
                const assignment = assignments?.find( item => String(item.order_id) == String(currentOrderId));
                if (!assignment) return;
                updateButtonStates(assignment);               
                orderCell.dataset.previousOrderId = currentOrderId;
            }
        });

        observer.observe(targetNode, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    clickCells.forEach(cell => {
        cell.addEventListener('click', () => {
            const type = cell.dataset.type;
            const field = cell.dataset.field;

            if ( type === "decimal" && field === "driving_time") {
                if ( cell.querySelector("input") ) return;

                const currentValue = cell.textContent.trim() || '0.00';
                const input = document.createElement('input');
                input.type = 'number';
                input.step = '0.01';
                input.min = '0';          
                input.classList.add('form-control');
                input.value = currentValue || '';
                cell.textContent = '';
                cell.appendChild(input);

                input.focus();
                input.addEventListener('input', () => {
                    validateEditableElement(input, type, field);

                    const assignment = getCurrentAssignment();
                    updateAssignmentDraftField(assignment, field, input.value.trim());
                });
                
                input.addEventListener('blur', () => {
                    const isValid = validateEditableElement(input, type, field);
                    const newValue = input.value.trim();

                    if (!isValid) {
                        cell.textContent = newValue || currentValue; 
                        return;
                    }

                    const normalizedValue = normalizeDecimalValue(newValue || currentValue);
                    cell.textContent = normalizedValue;

                    const assignment = getCurrentAssignment();
                    updateAssignmentDraftField(assignment, field, normalizedValue);
                    // Persist to LocalStorage for this assignment
                });
            
                // Save on Enter
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') input.blur();
                });
                return;
            }

            if ( type === "decimal" && field === "total_hrs" ) {
                try {
                    const assignment = getCurrentAssignment();

                    if (!assignment) {
                        showFlashAlert('warning', 'Missing assignment data.');
                        return;
                    }

                    const actualEndCell = document.querySelector('[data-field="actual_end_time"]');
                    const actualEndInput = actualEndCell?.querySelector('input');

                    const actualEndValue = actualEndInput ? actualEndInput.value.trim() : (actualEndCell?.dataset.raw || actualEndCell?.textContent.trim());

                    const spotTime = assignment.spot_time;

                    if (!assignment?.start_date_time || !spotTime || !actualEndValue) {
                        showFlashAlert('warning', 'Please enter spot time and/or actual end time.');
                        return;
                    }

                    const serviceDate = assignment.start_date_time.split(' ')[0];
                    const spotStart = `${serviceDate}T${spotTime.slice(0, 5)}`;
                    const actualEnd = actualEndValue.replace(' ', 'T').slice(0, 16);

                    const startObj = new Date(spotStart);
                    const endObj = new Date(actualEnd);

                    if (Number.isNaN(startObj.getTime()) || Number.isNaN(endObj.getTime())) {
                        showFlashAlert('warning', 'Invalid spot time or actual end time.');
                        return;
                    }

                    if (endObj <= startObj) {
                        showFlashAlert('warning', 'Actual end date and time must be after the spot date and time.');
                        return;
                    }

                    const total = ServiceTimeCalculator.getTotalHours(startObj, endObj);

                    if (Number.isNaN(total.decimal)) {
                        showFlashAlert('warning', 'Unable to calculate total hours.');
                        return;
                    }

                    const totalValue = total.decimal.toFixed(2);

                    cell.textContent = totalValue;

                    //const assignment = getCurrentAssignment();
                    updateAssignmentDraftField(assignment, field, totalValue);
                    showFlashAlert('info', `Total hours updated: ${total.formatted} (${totalValue} hrs)`);
                } catch (err) {
                    console.error("[CALC ERROR]", err);
                    showFlashAlert('error', 'Error calculating total hours.');
                }
                return;
            }

            if ( !cell.querySelector('input') ) {
                const currentValue = cell.textContent.trim();
                const input = document.createElement('input');

                if (type === 'time') {
                    input.type = 'time';
                } else if (type === 'datetime' || type === 'datetime-local') {
                    input.type = 'datetime-local';
                } else if (type === 'decimal') {
                    input.type = 'number';
                    input.step = '0.01';
                    input.min = '0';
                } else if (type === 'vehicle' || type === 'number') {
                    input.type = 'number';
                    input.step = '1';
                    input.min = '0';
                } else {
                    input.type = 'text';
                }

                const rawValue = cell.dataset.raw || currentValue;
                input.classList.add("form-control");
                // convert displayed datetime to datetime-local input format if needed
                if ((type === 'datetime' || type === 'datetime-local') && rawValue) {
                    input.value = toInputDateTime(rawValue);
                } else {
                    input.value = currentValue;
                }

                cell.textContent = '';
                cell.appendChild(input);

                input.focus();

                input.addEventListener('input', () => {
                    validateEditableElement(input, type, field);

                    const assignment = getCurrentAssignment();
                    let liveValue = input.value.trim();

                    if ( (type === 'datetime' || type === 'datetime-local') && liveValue) {
                        liveValue = liveValue.slice(0, 16);
                    }
                    updateAssignmentDraftField(assignment, field, liveValue);
                });
                //
                input.addEventListener('blur', () => {
                    const isValid = validateEditableElement(input, type, field);
                    const newValue = input.value.trim();

                    let displayValue = newValue || currentValue;

                    if ((type === 'datetime' || type === 'datetime-local') && newValue) {
                        const normalized = newValue.replace(' ', 'T').slice(0, 16);
                        cell.dataset.raw = normalized;
                        displayValue = toDisplayDateTime(normalized, dtHelper);

                        const assignment = getCurrentAssignment();
                        updateAssignmentDraftField(assignment, field, normalized);

                        cell.textContent = displayValue;
                        return;
                    }

                    if (type === 'time' && newValue) {
                        const normalizedTime = newValue.slice(0, 5);

                        cell.dataset.raw = normalizedTime;
                        displayValue = dtHelper(normalizedTime, 'time');

                        const assignment = getCurrentAssignment();
                        updateAssignmentDraftField(assignment, field, normalizedTime);

                        cell.textContent = displayValue;
                        return;
                    }

                    cell.textContent = displayValue;

                    if (!isValid) return;
                    
                    const assignment = getCurrentAssignment();
                    updateAssignmentDraftField(assignment, field, displayValue);
                });

                input.addEventListener('keydown', e => {
                    if (e.key === 'Enter') input.blur();
                });
            }
        });
    });
    
    window.addEventListener('load', () => {
        setTimeout(() => restoreButtonStateFromStorage(), 300);
    });

    function saveActiveEditableInputDraft () {
        const active = document.activeElement;
        if (!active || active.tagName !== 'INPUT') return;

        const cell = active.closest('.editable-data');
        if (!cell) return;

        const field = cell.dataset.field;
        const type = cell.dataset.type;

        let value = active.value.trim();

        if ( (type === 'datetime' || type === 'datetime-local') && value ) {
            value = value.replace('T', ' ');
        }

        const assignment = getCurrentAssignment();
        updateAssignmentDraftField(assignment, field, value);
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            console.log('[PWA] Page restored from cache. Re-syncing button states...');
            restoreButtonStateFromStorage();
        }
    });

    window.addEventListener('pagehide', () => {
        saveCurrentVisibleAssignmentDraft();
    });

    const textareaDraftFields = [
        {
            id: 'pickup-details',
            field: 'pickup_details'
        },
        {
            id: 'destination-details',
            field: 'destination_details'
        },
        {
            id: 'shared-job-note',
            field: 'shared_job_note'
        }
    ];

    textareaDraftFields.forEach(({ id, field }) => {
        const el = document.getElementById(id);
        if (!el) return;

        const saveTextareaDraft = () => {
            validateAssignmentTextarea(el);
            const assignment = getCurrentAssignment();
            updateAssignmentDraftField(assignment, field, el.value.trim());
        };

        el.addEventListener('input', saveTextareaDraft);
        el.addEventListener('blur', saveTextareaDraft);
    });
});

function getCompletePayrollData(assignment) {
    return {
        order_id: assignment.order_id ?? '',
        origin: assignment.origin ?? '',
        destination: assignment.destination ?? '',
        vehicle_id: document.querySelector('[data-field="vehicle_id"]')?.textContent.trim() ?? '',
        assignment_date: assignment.start_date_time?.split(' ')[0] ?? '',
        spot_time: assignment.spot_time ?? '',
        actual_drop_time: document.querySelector('[data-field="actual_drop_time"]')?.textContent.trim() ?? '',
        total_job_time: document.querySelector('[data-field="total_hrs"]')?.textContent.trim() ?? ''
    };
};

function getStoredCompletedAssignments() {
    try {
        const storedData = localStorage.getItem(COMPLETED_ASSIGNMENTS_KEY);

        if (!storedData) {
            return {};
        }

        const parsedData = JSON.parse(storedData);

        if (parsedData === null || typeof parsedData !== 'object' || Array.isArray(parsedData)) {
            console.warn('[PAYROLL SNAPSHOT] Invalid stored structure. Using an empty collection.');

            return {};
        }

        return parsedData;
    } catch (error) {
        console.error('[PAYROLL SNAPSHOT] Failed to read completed assignments:', error);

        return {};
    }
};

function saveCompletedAssignmentData(assignment) {
    const payrollData = getCompletePayrollData(assignment);
    const orderId = String(payrollData.order_id ?? '').trim();

    if (!orderId) {
        console.error('[PAYROLL SNAPSHOT] Cannot save an assignment without an order_id.');

        return false;
    }

    const storedAssignments = getStoredCompletedAssignments();

    // Add a new order or replace only the matching order.
    storedAssignments[orderId] = payrollData;

    try {
        localStorage.setItem(COMPLETED_ASSIGNMENTS_KEY, JSON.stringify(storedAssignments));
        console.log(`[PAYROLL SNAPSHOT] Saved completed order ${orderId}.`, payrollData);

        return true;
    } catch (error) {
        console.error(`[PAYROLL SNAPSHOT] Failed to save completed order ${orderId}:`, error);

        return false;
    }
};

function updateStoredPayrollDataIfPresent(assignment) {
    const orderId = String(assignment?.order_id ?? '').trim();

    if (!orderId) {
        console.error('[PAYROLL SNAPSHOT] Cannot update an assignment without an order_id.');

        return false;
    }

    const storedAssignments = getStoredCompletedAssignments();

    if (!Object.hasOwn(storedAssignments, orderId)) {
        console.log(`[PAYROLL SNAPSHOT] Order ${orderId} is not stored. No payroll update needed.`);

        return false;
    }

    storedAssignments[orderId] = getCompletePayrollData(assignment);

    try {
        localStorage.setItem(COMPLETED_ASSIGNMENTS_KEY, JSON.stringify(storedAssignments));
        console.log(`[PAYROLL SNAPSHOT] Updated stored order ${orderId}.`, storedAssignments[orderId]);

        return true;
    } catch (error) {
        console.error(`[PAYROLL SNAPSHOT] Failed to update stored order ${orderId}:`, error);

        return false;
    }
};

// Reusable Restoration Function with Visual Debug ===
function restoreButtonStateFromStorage() {
    try {
        const storedAssignments = JSON.parse(localStorage.getItem('assignments') || '[]');
        const savedIndex = Number.parseInt(sessionStorage.getItem('lastAssignmentIndex') || '0', 10);
        const current = storedAssignments[savedIndex] || storedAssignments[0];

        if (!current) {
            console.warn('[STATE] No current assignment found to restore.');
            return;
        }

        // Apply correct button logic
        updateButtonStates(current);

        // Debug log: detailed visual breakdown
        const status = String(current.assignment_status ?? 'pending').toLowerCase();
        const isPending = status === 'pending';
        const isConfirmed = status === 'confirmed';
        const isCanceled = status === 'canceled';
        const isCompleted = status === 'completed';

        console.groupCollapsed('%c[STATE RESTORE]', 'color: #0aa; font-weight: bold;');
        console.log('Assignment control:', current.assignment_control || '(none)');
        console.log('Current assignment ID:', current.order_id || '(none)');
        console.log('Assignment status:', status);
        console.log('Driver ID:', current.driver_id);
        console.log('Vehicle ID:', current.vehicle_id);
        console.log('Status flags:', { isPending, isConfirmed, isCanceled, isCompleted });
        console.log('🔘 Button states applied:');
        console.log('Confirm button:', $(confirmBtn).prop('disabled') ? '❌ disabled' : '✅ enabled');
        console.log('Cancel button:', $(cancelBtn).prop('disabled') ? '❌ disabled' : '✅ enabled');
        console.log('Save button:', $(saveBtn).prop('disabled') ? '❌ disabled' : '✅ enabled');
        console.log('Complete button:', $(completeBtn).prop('disabled') ? '❌ disabled' : '✅ enabled');
        console.groupEnd();
    } catch (error) {
        console.error('[STATE] Failed to restore button logic:', error);
    }
};

function submitAssignment(options) {
    const { buttonEl, flagName, flagValue, confirmMessage } = options;
    const form = document.querySelector('.assignment-card');
    const assignment = getCurrentAssignment();
    if (!form || !assignment) return;

    // Blur active input
    if (document.activeElement?.matches('.editable-data input')) {
        document.activeElement.blur();
    }

    // Save latest UI state
    saveCurrentVisibleAssignmentDraft();

    // Validate fields
    const validationMode = flagName === 'assignment-complete' ? 'complete' : 'save';
    if (!validateCurrentAssignmentFields({ showFlashAlert, focusFirstInvalid, mode: validationMode })) {
        return;
    }

    // Actual submission logic
    const doSubmit = () => {
        // Disable all buttons
        const buttons = document.querySelectorAll('#workOrder-btns button');
        buttons.forEach(btn => btn.disabled = true);
        setSubmittingState(buttonEl, true);

        // Overlay/fade assignment card
        const assignmentCard = document.querySelector('.assignment-card');
        assignmentCard.style.opacity = '0.5';
        assignmentCard.style.pointerEvents = 'none';

        // Clean old temp fields
        form.querySelectorAll('.temp-hidden').forEach(el => el.remove());

        // Build payload from editable cells
        appendEditableFields(form);

        const actualEndCell = document.querySelector('[data-field="actual_end_time"]');

        const actualEndInput = actualEndCell?.querySelector('input');
        let actualEndValue = (actualEndInput?.value ?? actualEndCell?.dataset.raw ?? '').trim();

        if (actualEndValue) {
            actualEndValue = actualEndValue.replace(' ', 'T').slice(0, 16);
        }

        form.querySelectorAll('input[name="actual_end_time"]').forEach(input => input.remove());
        appendHiddenFields(form, {
            actual_end_time: actualEndValue
        });

        // Append textareas
        ['pickup-details', 'destination-details', 'shared-job-note'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            form.querySelectorAll(`input[name="${el.name}"]`).forEach(h => h.remove());
            appendHiddenFields(form, { [el.name]: el.value.trim() });
        });

        // Ensure driving time to 0.00 if empty
        const drivingTimeCell = document.querySelector("[data-field='driving_time']");
        const drivingTimeInput = drivingTimeCell?.querySelector('input');
        const rawDrivingTime = (drivingTimeInput?.value ?? drivingTimeCell?.textContent ?? '').trim();
        const drivingTimeValue = rawDrivingTime === '' ? '0.00' : normalizeDecimalValue(rawDrivingTime);
        form.querySelectorAll("input[name='driving_time']").forEach(h => h.remove()); // h = hidden
        appendHiddenFields(form, { driving_time: drivingTimeValue }); 

        // Add action-specific flag and identifiers
        if (flagName && flagValue) appendHiddenFields(form, { [flagName]: flagValue });
        const identifiers = {
            assignment_control: assignment.assignment_control, 
            order_id: assignment.order_id,
            order_ref: assignment.order_ref, 
            driver_id: assignment.driver_id,
            signature_required: Number(assignment.signature_required) === 1 ? '1' : '0', 
            __method: 'PATCH' 
        };
        Object.keys(identifiers).forEach(name => form.querySelectorAll(`input[name="${name}"]`).forEach(h => h.remove()));
        appendHiddenFields(form, identifiers);

        // Add CSRF token from drvrtoken input only once
        const drvrtokenInput = document.querySelector('input[name="drvrtoken"]');
        if (drvrtokenInput) {
            form.querySelectorAll(`input[name='X-CSRF-Token']`).forEach(h => h.remove());
            appendHiddenFields(form, { 'X-CSRF-Token': drvrtokenInput.value });
        }

        // Payroll snapshot
        if (flagName === 'assignment-complete') {
            saveCompletedAssignmentData(assignment);
        } else if (flagName === 'modify') {
            updateStoredPayrollDataIfPresent(assignment);
        }
        
        form.querySelectorAll("input[name='pre_signature_base64'], " + "input[name='post_signature_base64']").forEach(input => input.remove());

        const requiresSignature = Number(assignment.signature_required) === 1;
        const signatureControl = assignment.assignment_control;
        const signaturePayload = requiresSignature ? {
            pre_signature_base64: localStorage.getItem(`pre-signature:${signatureControl}`) ?? '',
            post_signature_base64: localStorage.getItem(`post-signature:${signatureControl}`) ?? ''
        } : {
            pre_signature_base64: '',
            post_signature_base64: ''
        };

        appendHiddenFields(form, signaturePayload);

        console.log('[ACTUAL END SUBMISSION]', form.querySelector('input[name="actual_end_time"]')?.value);

        // Submit form
        form.requestSubmit(buttonEl);
    };

    if (confirmMessage) {
        // Show confirmation modal
        const confirmModalEl = document.querySelector('#confirm-modal');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const confirmModalBtn = document.querySelector('#confirm-modal-confirm');
        const unconfirmModalBtn = document.querySelector('#confirm-modal-cancel');

        // Show modal
        buildModal.confirm(confirmMessage, 'Yes', 'Cancel');
        confirmModal.show();

        // Clone buttons to remove old event listeners
        confirmModalBtn.replaceWith(confirmModalBtn.cloneNode(true));
        unconfirmModalBtn.replaceWith(unconfirmModalBtn.cloneNode(true));
        const newConfirmBtn = document.querySelector('#confirm-modal-confirm');
        const newUnconfirmBtn = document.querySelector('#confirm-modal-cancel');

        newConfirmBtn.addEventListener('click', () => {
            doSubmit();
            bootstrap.Modal.getInstance(confirmModalEl)?.hide();
        });
    
        newUnconfirmBtn.addEventListener('click', () => {
            bootstrap.Modal.getInstance(confirmModalEl)?.hide();
        });
    } else {
        doSubmit();
    }
};

// Confirm assignment button 
confirmBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const assignment = getCurrentAssignment();
    if (!assignment) return;

    try {
        const formData = new FormData();
        formData.append('assignment_control', assignment['assignment_control']);
        formData.append('order_id', assignment['order_id']);
        formData.append('confirm', '1');
        formData.append('__method', 'PATCH');
        const result = await handleAssignmentFetch({
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            headers: {
                'X-CSRF-Token': drvrToken
            },
            body: formData
        });
        drvrAlert(result.status, result.message); // toast
        // Only update Local model on confirmed success and refresh the currently displayed assignment in UI
        if (result.status === 'success') {
            const confirmedOrderId =
            assignment.order_id;

            await refreshAssignmentsFromServer(confirmedOrderId);
            broadcastAssignmentsUpdate(assignments);

            const current = getCurrentAssignment();

            if (current) {
                updateButtonStates(current);
            }
        }
    } catch (error) {
        console.error('Error confirmation assignment:', error);
        drvrAlert('error', 'Something went wrong. Please try again.');
    }
});

// Cancel/Remove assignment button
cancelBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const assignment = getCurrentAssignment();
    if (!assignment) {
        console.warn('No assignment selected yet.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('assignment_control', assignment['assignment_control']);
        formData.append('order_id', assignment['order_id']);
        formData.append('cancel', '1');
        formData.append('__method', 'PATCH');
        const options = {
        //const result = await cancelAssignment("https://prodriver.local/assignmenthandler.php", {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            headers: {
                'X-CSRF-Token': drvrToken
            },
            body: formData
        };
        const result = await handleAssignmentFetch(options);
        if (result.status === 'success') {
            //console.log('Assignment confirmed:', result);
            drvrAlert(result.status, result.message); // toast
            // Remove canceled assignment from array
            assignments.splice(currentIndex, 1);
            broadcastAssignmentsUpdate(assignments);
            // Immediately clear UI for visual feedback
            clearAssignmentUI();
            // Load next assignment ( or fallback )
            loadNextAssignment(currentIndex);
        } else {
            drvrAlert(result.status, result.message); // toast
        }
    } catch (error) {
        console.error('Error canceling assignment:', error);
        drvrAlert('error', 'Something went wrong. Please try again.');
    }
});

// Update/Modify assignment button
saveBtn.addEventListener('click', (e) => {
    e.preventDefault();
    submitAssignment({
        buttonEl: saveBtn,
        flagName: 'modify',
        flagValue: '1'
    });
});

// Complete assignment button
completeBtn.addEventListener('click', (e) => {
    e.preventDefault();
    submitAssignment({
        buttonEl: completeBtn,
        flagName: 'assignment-complete',
        flagValue: '1',
        confirmMessage: 'Are you sure you want to complete this assignment? Once completed, the assignment is no longer available and you will not be able to make any changes.'
    });
});

document.addEventListener('visibilitychange', async () => {
    if (document.visibilityState === 'hidden') {
        saveCurrentVisibleAssignmentDraft();
        return;
    }

    if (document.visibilityState !== 'visible') return;
    saveCurrentVisibleAssignmentDraft();

    if ( hasCurrentAssignmentDraft() ) {
        console.log('[SYNC] Skipped refresh because unsaved assignment edits exist.');
        showFlashAlert('info', 'Unsaved edits preserved.');
        return;
    }

    const now = Date.now();
    if (now - lastAssignmentsUpdate < 5000) return; // prevent too frequent reloads
    lastAssignmentsUpdate = now;

    try {
        const fresh = await getAssignment("https://prodriver.local/getassignments", {
            method: 'GET',
            mode: 'cors',
            credentials: 'include',
            cache: 'no-store',
            headers: { 
                'X-CSRF-Token': drvrToken 
            }
        });

        if (fresh?.status === 'success' && Array.isArray(fresh.data)) {
            assignments = fresh.data.filter(assignment => !assignment.completed_at && !assignment.canceled_at);
            localStorage.setItem('assignments', JSON.stringify(assignments));
            if (assignments.length === 0) {
                showNoAssignments();
                showFlashAlert('info', 'No active assignment(s) available.');
                return;
            }

            const savedIndex = parseInt(sessionStorage.getItem('lastAssignmentIndex') || '0', 10);
            const validIndex = Number.isInteger(savedIndex) && savedIndex >= 0 && savedIndex < assignments.length ? savedIndex : 0;

            currentIndex = validIndex;
            sessionStorage.setItem('lastAssignmentIndex', String(currentIndex));

            if (typeof window._rebuildAssignmentPagination === 'function') {
                window._rebuildAssignmentPagination();
            }

            // Re-render and restore state
            showAssignment(currentIndex);
            showFlashAlert('info', 'Assignments refreshed.');

            return;
        }

        showNoAssignments();
        showFlashAlert('error', 'Unable to retrieve assignments.');
    } catch (error) {
        console.error('[SYNC] Assignment page refresh failed:', error);
        showFlashAlert('error', 'Failed to refresh assignments.');
    }
});