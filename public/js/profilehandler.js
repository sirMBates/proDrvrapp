import { buildModal } from './appmodal.js';
import { Validation } from './validation.js';
import formValidation from './messagevalidation.js';
const inputs = document.querySelectorAll('input');
const openInfoUpdateBtn = document.querySelector('#changeinfo');
const emailChangeBtn = document.querySelector('#email-change');
const phoneChangeBtn = document.querySelector('#phone-change');
const pwdChangeBtn = document.querySelector('#pwd-change');
const fullNameInput = inputs[1];
const emailInput = inputs[2];
const birthDateInput = inputs[3];
const phoneInput = inputs[4];
const userNameInput = inputs[5];
const pswordInput = inputs[6];
const statusInput = inputs[7];
const updatePswordBtn = document.querySelector('#updatePsword');
const updateTelEmailBtn = document.querySelector('#updateTel-email');
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoBtn = document.querySelector('#info-ok');

window.addEventListener('load', () => {
    fetch("http://prodriver.local/getprofile", { mode: 'cors'})
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const driver = data;
            const profileInputs = document.querySelectorAll('input');
            const fullname = profileInputs[1];
            const email = profileInputs[2];
            const birthDate = profileInputs[3];
            const phoneNum = profileInputs[4];
            const userName = profileInputs[5];
            const status = profileInputs[7];
            if (driver) {
                fullname.value = `${driver['firstname']} ${driver['lastname']}`;
                email.value = driver['email'];
                birthDate.value = driver['birthdate'];
                phoneNum.value = driver['mobileNumber'];
                userName.value = driver['username'];
                status.value = localStorage.getItem('status') ? localStorage.getItem('status') : sessionStorage.getItem('status');
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        })
});

openInfoUpdateBtn.addEventListener('click', () => {
    $(infoModal).modal('toggle');
    infoModal.addEventListener('shown.bs.modal', () => {
        infoModalMsg.info('You can only update your email, mobile number and password. If you would like to update any of the 3, click the button next to the field you would like to update.', 'Ok');
    })
    $(infoBtn).on('click', () => {
        $(infoModal).modal('toggle');
    })
})
emailChangeBtn.addEventListener('click', () => {
    if (emailInput.hasAttribute('disabled')) {
        $(emailInput).prop('disabled', false);
    } else if (emailInput.setAttribute('disabled', 'false')) {
        $(emailInput).attr('disabled');
        if (emailInput.value === '') {
            emailInput.value = drvrData['email'];
        }
    }
});
phoneChangeBtn.addEventListener('click', () => {
    if (phoneInput.hasAttribute('disabled')) {
        $(phoneInput).prop('disabled', false);
    } else if (phoneInput.setAttribute('disabled', 'false')) {
        $(phoneInput).attr('disabled');
        if (phoneInput.value === '') {
            phoneInput.value = drvrData['mobileNumber'];
        }
    }
});
pwdChangeBtn.addEventListener('click', () => {
    if (pswordInput.hasAttribute('disabled')) {
        $(pswordInput).prop('disabled', false);
    } else if (pswordInput.setAttribute('disabled', 'false')) {
        $(pswordInput).attr('disabled');
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
});

$(pswordInput).on('input', () => {
    let isValid = Validation.validate($(pswordInput).val(), $(pswordInput).attr('type'));
    if (!isValid) {
        $(pswordInput).addClass('is-invalid');
    } else {
        $(pswordInput).removeClass('is-invalid');
        $(pswordInput).addClass('is-valid');
    }
});

$(updatePswordBtn).on('submit', () => {
    return formValidation();
});

$(updateTelEmailBtn).on('submit', () => {
    return formValidation();
});