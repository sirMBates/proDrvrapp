import { fetchDrvr } from "./drvrapi.js";
const primaryA = document.querySelector('#tableA');
const groupB = document.querySelector('#tableB');
const groupC = document.querySelector('#tableC');
const groupD = document.querySelector('#tableD');
const locationPickup = document.querySelector('#pickup_details');
const locationDestination = document.querySelector('#destination_details');
const driverNotes = document.querySelector('#drvr_notes');
const clickCells = document.querySelectorAll('.editable-data');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const editBtn = document.querySelector('#edit');
const completeBtn = document.querySelector('#submit-order');
const drvrToken = document.querySelector('#drvrToken');
const getDriver = fetchDrvr;
const getAssignment = fetchDrvr;

window.addEventListener('DOMContentLoaded', () => {
    getAssignment("https://prodriver.local/getassignments", { 
        mode: 'cors',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': drvrToken.value
        }
    })
    .then(data => {
        const operator = data;
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
        const operatorNotes = driverNotes;
        if (operator.status === 'success' && operator.data.length > 0) {
            const assignment = operator.data[0];
            primaryCoachId.textContent = assignment['vehicle_id'];
            primaryDrvrId.textContent = assignment['operator_id'];
            primaryDrvrName.textContent = `${assignment['last_name']} ${assignment['first_name']}`;
            primaryOrderNumber.textContent = assignment['order_id'];
            primaryNumOfCoaches.textContent = assignment['num_of_coaches'];
            secondaryStartTime.textContent = assignment['start_date_time'];
            secondarySpotTime.textContent = assignment['spot_time'];
            secondaryLeaveTime.textContent = assignment['leave_date_time'];
            tertiaryReturnTime.textContent = assignment['return_date_drop_time'];
            tertiaryDropTime.textContent = assignment['actual_drop_time'];
            tertiaryEndTime.textContent = assignment['end_time'];
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
            operatorNotes.value = assignment['driver_notes'];
        } else {
            console.log("No assignments found, loading profile instead...");
            return getDriver("https://prodriver.local/getprofile", {
                method: 'GET', 
                mode: 'cors',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': drvrToken.value
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
            const pickupDetails = locationPickup;
            const destinationDetails = locationDestination;
            const operatorNotes = driverNotes;

            primaryDrvrId.textContent = driver['operatorid'];
            primaryDrvrName.textContent = `${driver['lastName']}, ${driver['firstName']}`;
            primaryCoachId.textContent = 'No assignment available...';
            primaryOrderNumber.textContent = 'No assignment available...';
            primaryNumOfCoaches.textContent = 'No assignment available...';
            secondaryStartTime.textContent = 'No assignment available...';
            secondarySpotTime.textContent = 'No assignment available...';
            secondaryLeaveTime.textContent = 'No assignment available...';
            tertiaryReturnTime.textContent = 'No assignment available...';
            tertiaryDropTime.textContent = 'No assignment available...';
            tertiaryEndTime.textContent = 'No assignment available...';
            tertiaryActEndTime.textContent = 'No assignment available...';
            tertiaryShiftTime.textContent = 'No assignment available...';
            tertiaryDriveTime.textContent = 'No assignment available...';
            quaternaryOrigin.textContent = 'No assignment available...';
            quaternaryDestination.textContent = 'No assignment available...';
            quaternaryGroupNameandLeader.textContent = 'No assignment available...';
            quaternaryGroupLeaderMobile.textContent = 'No assignment available...';
            quaternaryCustomerNameandPhone.textContent = 'No assignment available...';
            quaternaryContactNameandMobile.textContent = 'No assignment available...';
            pickupDetails.value = 'No assignment available...';
            destinationDetails.value = 'No assignment available...';
            operatorNotes.value = 'No stored notes available...';
        }
    })
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });

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
                    if (e.key === 'Enter') {
                        input.blur();
                    }
                });
            }
        });
    });
});