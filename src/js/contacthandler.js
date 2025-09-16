import { Validation } from "./validation";
import formValidation from "./messagevalidation";
import { fetchDrvr } from "./drvrapi";
const driverName = document.querySelector('#drvr-name');
const operatorId = document.querySelector('#operatorid');
const driverEmail = document.querySelector('#drvr-email');
const helpDeskEmail = document.querySelector('#help-email');
const emailSubjectTitle = document.querySelector('#mail-subject-title');
const msgBody = document.querySelector('#body-msg');
const sendBtn = document.querySelector('#send-msg');
const counter = document.querySelector("#charCounter");
const emailForm = document.querySelector("#email-form");
const maxLength = 300;
const getDriver = fetchDrvr;

window.addEventListener('DOMContentLoaded', () => {
    helpDeskEmail.value = "help-desk@prodriver.local";
    getDriver("https://prodriver.local/getprofile", {
        mode: 'cors'
    })
    .then(data => {
        const driver = data;
        if (driver) {
            driverName.value = `${driver['firstName']} ${driver['lastName']}`;
            operatorId.value = driver['operatorid'];
            driverEmail.value = driver['email'];
        }
    })
    .catch(error => {
        console.error('Sorry, we had a problem: ', error);
    });
});

$(operatorId).on('focus', () => { 
    let isValid = Validation.validateStatus($(operatorId).val(), $(operatorId).attr('type'));
    if (!isValid) {
        $(operatorId).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(operatorId).removeClass('is-invalid').addClass('is-valid');
    }
})

$(driverName).on('focus', () => {
    let getDrvrName = driverName.value;
    let unSpacedFullName = getDrvrName.replace(/\s+/g, ""); // removes space between names 
    let isValid = Validation.validate($(unSpacedFullName).val(), $(driverName).attr('type'));
    if (!isValid) {
        $(driverName).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(driverName).removeClass('is-invalid').addClass('is-valid');
    }
})

$(driverEmail).on('focus', () => {
    let isValid = Validation.validate($(driverEmail).val(), $(driverEmail).attr('type'));
    if (!isValid) {
        $(driverEmail).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(driverEmail).removeClass('is-invalid').addClass('is-valid');
    }
});

$(helpDeskEmail).on('focus', () => {
    let isValid = Validation.validate($(helpDeskEmail).val(), $(helpDeskEmail).attr('type'));
    if (!isValid) {
        $(helpDeskEmail).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(helpDeskEmail).removeClass('is-invalid').addClass('is-valid');
    }
});

$(emailSubjectTitle).on('input', () => {
    const pattern = /^[a-zA-Z0-9 .,!?]+$/;
    let isValid = pattern.test($(emailSubjectTitle).val());
    if (!isValid) {
        $(emailSubjectTitle).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(emailSubjectTitle).removeClass('is-invalid').addClass('is-valid');
    }
})

$(msgBody).on('input', () => {
    let msgValue = $(msgBody).val().trim();  
    let isValid = Validation.validateMessage(msgValue, 'textarea');
    if (!isValid) {
        $(msgBody).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(msgBody).removeClass('is-invalid').addClass('is-valid');
    }
});

// Update counter on input
msgBody.addEventListener("input", () => {
    const length = msgBody.value.length;
    counter.textContent = `${length} / ${maxLength}`;

    // Optional: add a warning style if near the limit
    if (length >= maxLength) {
        counter.style.color = "red";
    } else {
        counter.style.color = "";
    }
});

$(sendBtn).on('submit', (e) => {
    e.preventDefault();
    formValidation();
    emailForm.submit();
});