import { fetchDrvr, viewableDateTimeHelper, showFlashAlert, fadeOut, fadeIn } from "./helpers.js";
import { handleAssignmentFetch } from "./pwa.js";
const primaryA = document.querySelector('#tableA');
const groupB = document.querySelector('#tableB');
const groupC = document.querySelector('#tableC');
const groupD = document.querySelector('#tableD');
const locationPickup = document.querySelector('#pickup_details');
const locationDestination = document.querySelector('#destination_details');
const operatorNotes = document.querySelector('#drvr_notes');
const clickCells = document.querySelectorAll('.editable-data');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const editBtn = document.querySelector('#edit');
const completeBtn = document.querySelector('#submit-order');
const drvrToken = document.querySelector('#drvrToken').value;
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;
const confirmAssignment = fetchDrvr;
const cancelAssignment = fetchDrvr;
const dtHelper = viewableDateTimeHelper;
const drvrAlert = showFlashAlert;
let showAssignment;
let assignments = [];
let currentIndex = 0;
let pagination = null;

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

function showNoAssignments(driver = null) {
    // Select elements safely
    const primaryCoachId = primaryA.childNodes[3].childNodes[1].childNodes[1];
    const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
    const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];

    // Display default message for no assignments
    primaryCoachId.textContent = 'No assignment available...';

    // Show driver info if provided, else clear
    if (driver) {
        primaryDrvrId.textContent = driver['operatorid'] || 'N/A';
        primaryDrvrName.textContent = `${driver['lastName'] || ''}, ${driver['firstName'] || ''}`.trim();
    } else {
        primaryDrvrId.textContent = '';
        primaryDrvrName.textContent = '';
    }

    // Optional: visually reset the rest of the UI
    const assignmentContainer = document.querySelector('#assignmentContainer');
    if (assignmentContainer) {
        assignmentContainer.textContent = 'No active assignments found.';
    }

    // Optionally disable buttons (depending on your UX flow)
    $(confirmBtn).prop('disabled', true);
    $(cancelBtn).prop('disabled', true);
    $(editBtn).prop('disabled', true);
    $(completeBtn).prop('disabled', true);
};

async function loadNextAssignment(afterIndex) {
    // Case 1: There are still other assignments left
    if (assignments.length > 0) {
        const nextIndex = afterIndex >= assignments.length ? assignments.length - 1 : afterIndex;
        if (nextIndex >= 0) {
            showAssignment(nextIndex);
            return;
        }
    }

    // Case 2: None left → Fetch profile fallback
    console.log("Fetching profile fallback...");
    try {
        const data = await getDriver("https://prodriver.local/getprofile", {
            mode: 'cors',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': drvrToken
            }
        });

        // Pass the driver info to showNoAssignments()
        showNoAssignments(data);
    } catch (err) {
        console.error("Error fetching driver profile fallback:", err);
        // Fallback to empty state if driver fetch fails
        showNoAssignments();
    }
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
    $(editBtn).prop('disabled', true);
    $(completeBtn).prop('disabled', true);

    await fadeIn(assignmentCard); // smooth fade back in
};

window.addEventListener('DOMContentLoaded', () => {
    // Create pagination controls (Bootstrap)
    function createPaginationControls() {
        const container = document.createElement('div');
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
                pill.addEventListener('click', () => showAssignment(i));
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

    // Renders the assignment details to your existing UI tables
    showAssignment = function(index) {
        if (assignments.length === 0) return;

        if (index < 0 || index >= assignments.length) return; // guard

        currentIndex = index;
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
        const tertiaryReturnTime = groupC.childNodes[3].childNodes[1].childNodes[1];
        const tertiaryDropTime = groupC.childNodes[3].childNodes[1].childNodes[3];
        const tertiaryEndTime = groupC.childNodes[3].childNodes[1].childNodes[5];
        const tertiaryActEndTime = groupC.childNodes[3].childNodes[1].childNodes[7];
        const tertiaryShiftTime = groupC.childNodes[3].childNodes[1].childNodes[9];
        const tertiaryDriveTime = groupC.childNodes[3].childNodes[1].childNodes[11];
        const quaternaryOrigin = groupD.childNodes[3].childNodes[1].childNodes[1];
        const quaternaryDestination = groupD.childNodes[3].childNodes[1].childNodes[3];
        const quaternaryGroupNameandLeader = groupD.childNodes[3].childNodes[1].childNodes[5];
        const quaternaryGroupLeaderMobile = groupD.childNodes[3].childNodes[1].childNodes[7];
        const quaternaryCustomerNameandPhone = groupD.childNodes[3].childNodes[1].childNodes[9];
        const quaternaryContactNameandMobile = groupD.childNodes[3].childNodes[1].childNodes[11];
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
        secondarySpotTime.textContent = dtHelper(`1970-01-01 ${assignment['spot_time']}`, 'time');
        secondaryLeaveTime.textContent = dtHelper(assignment['leave_date_time']);
        tertiaryReturnTime.textContent = dtHelper(assignment['return_date_drop_time']);
        tertiaryDropTime.textContent = assignment['actual_drop_time'];
        tertiaryEndTime.textContent = dtHelper(assignment['end_date_time']);
        tertiaryActEndTime.textContent = assignment['actual_end_time'];
        tertiaryShiftTime.textContent = assignment['total_job_time'];
        tertiaryDriveTime.textContent = assignment['driving_time'];
        quaternaryOrigin.textContent = assignment['origin'];
        quaternaryDestination.textContent = assignment['destination'];
        quaternaryGroupNameandLeader.textContent = `${assignment['group_name']}, ${assignment['group_leader']}`;
        quaternaryGroupLeaderMobile.textContent = assignment['group_leader_mobile'];
        quaternaryCustomerNameandPhone.textContent = `${assignment['customer_name']}, ${assignment['customer_phone']}`;
        quaternaryContactNameandMobile.textContent = `${assignment['contact_name']}, ${assignment['contact_mobile']}`;
        pickupDetails.value = assignment['pickup_details'];
        destinationDetails.value = assignment['destination_details'];
        opNotes.value = assignment['driver_notes'];

        // Update pills and buttons if pagination exists
        if (pagination) {
            pagination.renderPills();
            pagination.updateButtons();
        }

        if (assignment['confirmed_assignment'] === 'unconfirmed') {
            $(editBtn).prop("disabled", true);
            $(completeBtn).prop("disabled", true);
        } else if (assignment['confirmed_assignment'] === 'confirmed') {
            $(editBtn).prop("disabled", false);
            $(completeBtn).prop("disabled", false);
        } else {
            $(editBtn).prop("disabled", false);
            $(completeBtn).prop("disabled", false);
        }

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

        // Expose refresh hook for refreshCurrentAssignment() ( safe, single assignment re-render)
        window._refreshAssignmentFromOutside = function() {
            showAssignment(currentIndex);
        };
    };

    getAssignment("https://prodriver.local/getassignments", { 
        mode: 'cors',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': drvrToken
        }
    })
    .then(data => {
        const operator = data;
        const confirmBtnStatus = $(confirmBtn).prop("disabled");
        const cancelBtnStatus = $(cancelBtn).prop("disabled");
        if (operator.status === 'success' && operator.data.length > 0) {
            assignments = operator.data;
            createPaginationControls();
            showAssignment(0);
            if (confirmBtnStatus) $(confirmBtn).prop("disabled", false);
            if (cancelBtnStatus) $(cancelBtn).prop("disabled", false);
        } else {
            //console.log("No assignments found, loading profile instead...");
            return getDriver("https://prodriver.local/getprofile", { 
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
            const primaryCoachId = primaryA.childNodes[3].childNodes[1].childNodes[1];
            const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
            const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];
            const primaryOrderNumber = primaryA.childNodes[3].childNodes[1].childNodes[7];
            const primaryNumOfCoaches = primaryA.childNodes[3].childNodes[1].childNodes[9];
            const secondaryStartTime = groupB.childNodes[3].childNodes[1].childNodes[1];
            const secondarySpotTime = groupB.childNodes[3].childNodes[1].childNodes[3];
            const secondaryLeaveTime = groupB.childNodes[3].childNodes[1].childNodes[5];
            const tertiaryReturnTime = groupC.childNodes[3].childNodes[1].childNodes[1];
            const tertiaryDropTime = groupC.childNodes[3].childNodes[1].childNodes[3];
            const tertiaryEndTime = groupC.childNodes[3].childNodes[1].childNodes[5];
            const tertiaryActEndTime = groupC.childNodes[3].childNodes[1].childNodes[7];
            const tertiaryShiftTime = groupC.childNodes[3].childNodes[1].childNodes[9];
            const tertiaryDriveTime = groupC.childNodes[3].childNodes[1].childNodes[11];
            const quaternaryOrigin = groupD.childNodes[3].childNodes[1].childNodes[1];
            const quaternaryDestination = groupD.childNodes[3].childNodes[1].childNodes[3];
            const quaternaryGroupNameandLeader = groupD.childNodes[3].childNodes[1].childNodes[5];
            const quaternaryGroupLeaderMobile = groupD.childNodes[3].childNodes[1].childNodes[7];
            const quaternaryCustomerNameandPhone = groupD.childNodes[3].childNodes[1].childNodes[9];
            const quaternaryContactNameandMobile = groupD.childNodes[3].childNodes[1].childNodes[11];
            
            primaryCoachId.textContent = 'No assignment available...';
            primaryDrvrId.textContent = driver['operatorid'];
            primaryDrvrName.textContent = `${driver['firstName']} ${driver['lastName']}`;
            const placeholders = [primaryOrderNumber, primaryNumOfCoaches, secondaryStartTime, secondarySpotTime, secondaryLeaveTime, tertiaryReturnTime, tertiaryDropTime, tertiaryEndTime, tertiaryActEndTime, tertiaryShiftTime, tertiaryDriveTime, quaternaryOrigin, quaternaryDestination, quaternaryGroupNameandLeader, quaternaryGroupLeaderMobile, quaternaryCustomerNameandPhone, quaternaryContactNameandMobile];
            placeholders.forEach(ph => {
                const el = eval(ph);
                if (el) el.textContent = 'No assignment available...';
            });
            locationPickup.value = 'No assignment available...';
            locationDestination.value = 'No assignment available...';
            operatorNotes.value = 'No stored notes available...';
        }
    })
    .catch(error => console.error('There was a problem with the fetch operation:', error));

    // 🔎 MutationObserver: watch for changes to the assignment order cell
    const targetNode = document.querySelector('#tableA');
    if (targetNode) {
        const observer = new MutationObserver(() => {
            const orderCell = targetNode.querySelector('td:nth-child(4)'); // 4th cell = order ID
            if (!orderCell) return;

            const previousOrderId = orderCell.dataset.previousOrderId || '';
            const currentOrderId = orderCell.textContent.trim();
            
            if (currentOrderId && currentOrderId !== 'No assignment available...' && currentOrderId !== previousOrderId) {
                const assignment = assignments.find( a => a.order_id == currentOrderId);
                const requiresSignature = assignment ? assignment.signature_required === 1 : false;
                // When order ID changes, notify other scripts
                window.dispatchEvent(new CustomEvent('assignmentChanged', {
                    detail: { 
                        orderId: currentOrderId, 
                        requiresSignature
                    } 
                }));
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
            if (!cell.querySelector('input')) {
                const currentValue = cell.textContent.trim();
                const input = document.createElement('input');
                input.type = 'text';
                input.classList.add('form-control');
                input.value = currentValue;
                cell.textContent = '';
                cell.appendChild(input);

                input.focus();
                input.addEventListener('blur', () => {
                    const newValue = input.value.trim();
                    cell.textContent = newValue || currentValue;
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') input.blur();
                });
            }
        });
    });
});

confirmBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const assignment = getCurrentAssignment();
    const btnName = confirmBtn.name;
    if (!assignment) {
        console.warn('No assignment selected yet.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append(btnName, true);
        formData.append('confirmed_assignment', 'confirmed');
        formData.append('order_id', assignment['order_id']);
        formData.append('vehicle_id', assignment['vehicle_id']);
        formData.append('driver_id', assignment['driver_id']);
        formData.append('__method', 'PATCH');
        const options = {
        //const result = await confirmAssignment("https://prodriver.local/assignmenthandler.php", {
            mode: 'cors',
            credentials: 'include',
            method: 'POST',
            headers: {
                'X-CSRF-Token': drvrToken
            },
            body: formData
        };
        const result = await handleAssignmentFetch(options);
        if (result.status === 'success') {
            drvrAlert(result.status, result.message); // toast

            // Update your local model so UI stays in sync
            assignment['confirmed_assignment'] = 'confirmed';

            // Refresh the currently displayed assignment in UI
            refreshCurrentAssignment();
            $(confirmBtn).prop('disabled', true);
            $(cancelBtn).prop('disabled', true);
            $(editBtn).prop('disabled', false);
            $(completeBtn).prop('disabled', false);
        } else {
            drvrAlert(result.status, result.message); // toast
            $(confirmBtn).prop('disabled', false);
            $(cancelBtn).prop('disabled', false);
            $(editBtn).prop('disabled', true);
            $(completeBtn).prop('disabled', true);
        }
    } catch (error) {
        console.error('Error confirmation assignment:', error);
        drvrAlert('error', 'Something went wrong. Please try again.');
    }
});

cancelBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const assignment = getCurrentAssignment();
    const btnName = cancelBtn.name;
    if (!assignment) {
        console.warn('No assignment selected yet.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append(btnName, true);
        formData.append('order_id', assignment['order_id']);
        formData.append('vehicle_id', assignment['vehicle_id']);
        formData.append('driver_id', assignment['driver_id']);
        formData.append('__method', 'DELETE');
        const options = {
        //const result = await cancelAssignment("https://prodriver.local/assignmenthandler.php", {
            mode: 'cors',
            credentials: 'include',
            method: 'POST',
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

            // Immediately clear UI for visual feedback
            clearAssignmentUI();

            // Load next assignment ( or fallback )
            await loadNextAssignment(currentIndex);
        } else {
            drvrAlert(result.status, result.message); // toast
        }
    } catch (error) {
        console.error('Error canceling assignment:', error);
        drvrAlert('error', 'Something went wrong. Please try again.');
    }
});
editBtn.addEventListener('click', () => {});
completeBtn.addEventListener('click', () => {});