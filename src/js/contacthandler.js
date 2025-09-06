import { Validation } from "./validation";
import formValidation from "./messagevalidation";
import { fetchDrvr } from "./drvrapi";
const senderEmail = document.querySelector('#drvr-email');
const helpDeskEmail = document.querySelector('#dev-email');
const msgBody = document.querySelector('#body-msg');
const sendBtn = document.querySelector('#send-msg');
const counter = document.querySelector("#charCounter");
const maxLength = 250;

window.addEventListener('DOMContentLoaded', () => {
    helpDeskEmail.value = "help-desk@prodriver.local";
    fetchDrvr("https://prodriver.local/getprofile", {
        mode: 'cors'
    })
    .then(data => {
        const driver = data;
        if (driver) {
            senderEmail.value = driver['email'];
        }
    })
    .catch(error => {
        console.error('Sorry, we had a problem: ', error);
    });
});

$(senderEmail).on('input', () => {
    let isValid = Validation.validate($(senderEmail).val(), $(senderEmail).attr('type'));
    if (!isValid) {
        $(senderEmail).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(senderEmail).removeClass('is-invalid').addClass('is-valid');
    }
});

$(helpDeskEmail).on('input', () => {
    let isValid = Validation.validate($(helpDeskEmail).val(), $(helpDeskEmail).attr('type'));
    if (!isValid) {
        $(helpDeskEmail).removeClass('is-valid').addClass('is-invalid');
    } else {
        $(helpDeskEmail).removeClass('is-invalid').addClass('is-valid');
    }
});

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

$(sendBtn).on('submit', () => {
    return formValidation();
});