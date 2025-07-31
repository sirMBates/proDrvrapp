import { buildModal } from './appmodal.js';
import { Validation } from './validation.js';
import formValidation from './messagevalidation.js';
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

$(emailInput).on('input', () => {
    let isValid = Validation.validate($(emailInput).val(), $(emailInput).attr('type'));
    if (!isValid) {
        $(emailInput).addClass('is-invalid');
    } else {
        $(emailInput).removeClass('is-invalid');
        $(emailInput).addClass('is-valid');
    }
})

$(phoneInput).on('input', () => {
    let isValid = Validation.validate($(phoneInput).val(), $(phoneInput).attr('type'));
    if (!isValid) {
        $(phoneInput).addClass('is-invalid');
    } else {
        $(phoneInput).removeClass('is-invalid');
        $(phoneInput).addClass('is-valid');
    }
})

$(pswordInput).on('input', () => {
    let isValid = Validation.validate($(pswordInput).val(), $(pswordInput).attr('type'));
    if (!isValid) {
        $(pswordInput).addClass('is-invalid');
    } else {
        $(pswordInput).removeClass('is-invalid');
        $(pswordInput).addClass('is-valid');
    }
})

$(updatePswordBtn).on('submit', () => {
    return formValidation();
})

$(updateTelEmailBtn).on('submit', () => {
    return formValidation();
})

window.addEventListener('load', () => {
    fetch("http://prodriver.local/drvrs")
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
         })
        .then(data => {
            console.log('JSON response data:', data);
            //here's where we'll get the values of data and display them to the client
         })
        .catch(error => {
            console.error('Error fetching data: ', error);
        })
});