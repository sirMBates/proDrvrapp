import { Validation } from "./validation.js";
import formValidation from "./messagevalidation.js";
import { fetchDrvr } from "./drvrapi.js";

const openInfoUpdateBtn = document.querySelector('#changeinfo');
const emailChangeBtn = document.querySelector('#email-change');
const phoneChangeBtn = document.querySelector('#phone-change');
const pwdChangeBtn = document.querySelector('#pwd-change');
const profileInputs = document.querySelectorAll('input');
const drvrFullName = profileInputs[2];
const operatorID = profileInputs[3];
const drvrEmail = profileInputs[4];
const drvrBirthDate = profileInputs[5];
const drvrPhoneNumber = profileInputs[6];
const drvrUserName = profileInputs[7];
const drvrPsword = profileInputs[8];
const drvrStatus = profileInputs[9];
const drvrToken = document.querySelector('#drvrToken').value;
const updatePswordBtn = document.querySelector('#updatePsword');
const updateTelEmailBtn = document.querySelector('#updateTel-email');
const getDriver = fetchDrvr;

window.addEventListener('DOMContentLoaded', () => {
    getDriver("https://prodriver.local/getprofile", {
        mode: 'cors',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': drvrToken
        }
    })
        .then(data => {
            const driver = data;
            if (driver) {
                drvrFullName.value = `${driver['firstName']} ${driver['lastName']}`;
                operatorID.value = driver['operatorid'];
                drvrEmail.value = driver['email'];
                drvrBirthDate.value = driver['birthdate'];
                drvrPhoneNumber.value = driver['mobileNumber'];
                drvrUserName.value = driver['username'];
                drvrStatus.value = localStorage.getItem('status') ? localStorage.getItem('status') : sessionStorage.getItem('status');
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        })
});

emailChangeBtn.addEventListener('click', () => {
    if (drvrEmail.hasAttribute('disabled')) {
        $(drvrEmail).prop('disabled', false);
    } else if (drvrEmail.setAttribute('disabled', 'false')) {
        $(drvrEmail).attr('disabled');
    }
});

phoneChangeBtn.addEventListener('click', () => {
    if (drvrPhoneNumber.hasAttribute('disabled')) {
        $(drvrPhoneNumber).prop('disabled', false);
    } else if (drvrPhoneNumber.setAttribute('disabled', 'false')) {
        $(drvrPhoneNumber).attr('disabled');
    }
});

pwdChangeBtn.addEventListener('click', () => {
    if (drvrPsword.hasAttribute('disabled')) {
        $(drvrPsword).prop('disabled', false);
    } else if (drvrPsword.setAttribute('disabled', 'false')) {
        $(drvrPsword).attr('disabled');
    }
});

$(drvrFullName).on('input', () => {
    let isValid = Validation.validate($(drvrFullName).val(), $(drvrFullName).attr('type'));
    if (!isValid) {
        $(drvrFullName).addClass('is-invalid');
    } else {
        $(drvrFullName).removeClass('is-invalid');
        $(drvrFullName).addClass('is-valid');
    }
})

$(operatorID).on('input', () => {
    let isValid = Validation.validate($(operatorID).val(), $(operatorID).attr('type'));
    if (!isValid) {
        $(operatorID).addClass('is-invalid');
    } else {
        $(operatorID).removeClass('is-invalid');
        $(operatorID).addClass('is-valid');
    }
})

$(drvrEmail).on('input', () => {
    let isValid = Validation.validate($(drvrEmail).val(), $(drvrEmail).attr('type'));
    if (!isValid) {
        $(drvrEmail).addClass('is-invalid');
    } else {
        $(drvrEmail).removeClass('is-invalid');
        $(drvrEmail).addClass('is-valid');
    }
})

$(drvrBirthDate).on('input', () => {
    let isValid = Validation.validate($(drvrBirthDate).val(), $(drvrBirthDate).attr('type'));
    if (!isValid) {
        $(drvrBirthDate).addClass('is-invalid');
    } else {
        $(drvrBirthDate).removeClass('is-invalid');
        $(drvrBirthDate).addClass('is-valid');
    }
});

$(drvrPhoneNumber).on('input', () => {
    let isValid = Validation.validate($(drvrPhoneNumber).val(), $(drvrPhoneNumber).attr('type'));
    if (!isValid) {
        $(drvrPhoneNumber).addClass('is-invalid');
    } else {
        $(drvrPhoneNumber).removeClass('is-invalid');
        $(drvrPhoneNumber).addClass('is-valid');
    }
});

$(drvrUserName).on('input', () => {
    let isValid = Validation.validateOnlyUsername($(drvrUserName).val(), $(drvrUserName).attr('type'));
    if (!isValid) {
        $(drvrUserName).addClass('is-invalid');
    } else {
        $(drvrUserName).removeClass('is-invalid');
        $(drvrUserName).addClass('is-valid');
    }
});

$(drvrPsword).on('input', () => {
    let isValid = Validation.validate($(drvrPsword).val(), $(drvrPsword).attr('type'));
    if (!isValid) {
        $(drvrPsword).addClass('is-invalid');
    } else {
        $(drvrPsword).removeClass('is-invalid');
        $(drvrPsword).addClass('is-valid');
    }
});

$(updatePswordBtn).on('submit', () => {
    return formValidation();
});

$(updateTelEmailBtn).on('submit', () => {
    return formValidation();
});