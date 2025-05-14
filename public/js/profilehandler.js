import { buildModal } from './appmodal.js';
const inputs = document.querySelectorAll('.form-control');
const openUpdateInputBtn = document.querySelector('#changeinfo');
const emailInput = inputs[2];
const phoneInput = inputs[5];
const pswordInput = inputs[7];
const updatePswordBtn = document.querySelector('#updatePsword');
const updateTelEmailBtn = document.querySelector('#updateTel-email');
const infoMsg = document.querySelector('#info-modal');
const infoBtn = document.querySelector('#info-ok'); 
openUpdateInputBtn.addEventListener('click', () => {
    $(infoMsg).modal('toggle');
    infoMsg.addEventListener('shown.bs.modal', () => {
        buildModal.info('You can only update your email, mobile number and password. If you would like to update any of the 3, click/touch on the field you would like to update.', 'Ok');
    })
    $(infoBtn).on('click', () => {
        $(infoMsg).modal('toggle');
    })
})
emailInput.addEventListener('focus', (e) => {
    if (e.target.hasAttribute('readonly')) {
        $(e.target).removeAttr('readonly');
    }
});
phoneInput.addEventListener('focus', (e) => {
    if (e.target.hasAttribute('readonly')) {
        $(e.target).removeAttr('readonly');
    }
});
pswordInput.addEventListener('focus', (e) => {
    if (e.target.hasAttribute('readonly')) {
        $(e.target).removeAttr('readonly');
    }
});
