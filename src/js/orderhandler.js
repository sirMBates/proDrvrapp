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
const getDriver = fetchDrvr;

window.addEventListener('DOMContentLoaded', () => {
    getDriver("https://prodriver.local/getprofile", {
        mode: 'cors'
    })
    .then(data => {
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
        if (driver) {
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