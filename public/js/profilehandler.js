import { Validation } from "./validation.js";
import formValidation from "./messagevalidation.js";
import { fetchDrvr } from "./drvrapi.js";

const openInfoUpdateBtn = document.querySelector('#changeinfo');
const emailChangeBtn = document.querySelector('#email-change');
const phoneChangeBtn = document.querySelector('#phone-change');
const pwdChangeBtn = document.querySelector('#pwd-change');
const profileInputs = document.querySelectorAll('input');
const drvrFullName = profileInputs[2];
const drvrEmail = profileInputs[3];
const drvrBirthDate = profileInputs[4];
const drvrPhoneNumber = profileInputs[5];
const drvrUserName = profileInputs[6];
const drvrPsword = profileInputs[7];
const drvrStatus = profileInputs[8];
const updatePswordBtn = document.querySelector('#updatePsword');
const updateTelEmailBtn = document.querySelector('#updateTel-email');
const fetchDriver = fetchDrvr;

window.addEventListener('load', () => {
    fetchDriver("http://prodriver.local/getprofile", { mode: 'cors'})
        .then(data => {
            const driver = data;
            if (driver) {
                drvrFullName.value = `${driver[3]} ${driver[4]}`;
                drvrEmail.value = driver[2];
                drvrBirthDate.value = driver[6];
                drvrPhoneNumber.value = driver[5];
                drvrUserName.value = driver[1];
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