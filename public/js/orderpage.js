import { buildModal } from "./appmodal";
import { fetchDrvr } from "./getDrvr";
const infoBtn = document.querySelector('#notifyinfo');
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoModBtn = document.querySelector('#info-ok');
const headingA = document.querySelector('#tableA');
const dispatchB = document.querySelector('#tableB');
const dispatchC = document.querySelector('#tableC');
const customerDetailsD = document.querySelector('#tableD');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const editBtn = document.querySelector('#edit');
const completeBtn = document.querySelector('#submit-order');
const fetchDriver = fetchDrvr;

window.addEventListener('load', () => {
    fetchDriver("http://prodriver.local/getprofile", { mode: 'cors'})
    .then(data => {
        const driver = data;
        const primaryTable = document.querySelector('#tableA');
        const primaryDrvrId = primaryTable.childNodes[3].childNodes[1].childNodes[3];
        const primaryDrvrName = primaryTable.childNodes[3].childNodes[1].childNodes[5];
        if (driver) {
            primaryDrvrId.textContent = driver['driverid'];
            primaryDrvrName.textContent = `${driver['lastname']}, ${driver['firstname']}`;
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