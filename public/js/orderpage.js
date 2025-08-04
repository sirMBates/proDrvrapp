import { buildModal } from "./appmodal";
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


$(infoBtn).on('click', () => {
    $(infoModal).modal('toggle'),
    infoModal.addEventListener('shown.bs.modal', () => {
        infoModalMsg.info(`This page is where your job orders will be viewed. You\'ll be able to edit certain times, details and add notes for company and your personal reference.<br> You must confirm the job by clicking the button below once received.<br> When you\'re completing the job, click the edit button down below if there are any changes to be made.<br> If no changes, complete the dispatch order.<br> You can also cancel the job if dispatch allows.`, 'Ok');
    })
    infoModBtn.addEventListener('click', () => {
        $(infoModal).modal('toggle');
    })
});