import { buildModal } from "./appmodal";
import { fetchDrvr } from "./getDrvr";
const infoBtn = document.querySelector('#notifyinfo');
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoModBtn = document.querySelector('#info-ok');
const primaryA = document.querySelector('#tableA');
const groupB = document.querySelector('#tableB');
const groupC = document.querySelector('#tableC');
const groupD = document.querySelector('#tableD');
const clickCells = document.querySelectorAll('.editable-data');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const editBtn = document.querySelector('#edit');
const completeBtn = document.querySelector('#submit-order');
const fetchDriver = fetchDrvr;

window.addEventListener('load', () => {
    fetchDriver("http://prodriver.local/getprofile", { mode: 'cors'})
    .then(data => {
        const driver = data;
        const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
        const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];
        if (driver) {
            primaryDrvrId.textContent = driver['driverid'];
            primaryDrvrName.textContent = `${driver['firstname']} ${driver['lastname']}`;
        }
    })
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    })
})
$(infoBtn).on('click', () => {
    $(infoModal).modal('toggle'),
    infoModal.addEventListener('shown.bs.modal', () => {
        infoModalMsg.info(`This page is where your job orders will be viewed. You\'ll be able to edit certain times, details and add notes for company and your personal reference.<br> You must confirm the job by clicking the button below once received.<br> When you\'re completing the job, click the edit button down below if there are any changes to be made.<br> If no changes, complete the dispatch order.<br> You can also cancel the job if dispatch allows.`, 'Ok');
    })
    infoModBtn.addEventListener('click', () => {
        $(infoModal).modal('toggle');
    })
});

window.addEventListener('DOMContentLoaded', () => {
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