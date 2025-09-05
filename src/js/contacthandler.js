import { Validation } from "./validation";
import formValidation from "./messagevalidation";
import { fetchDrvr } from "./drvrapi";
const senderEmail = document.querySelector('#drvr-email');
const helpDeskEmail = document.querySelector('#dev-email');
const msgBody = document.querySelector('#body-msg');
const sendBtn = document.querySelector('#send-msg');

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
        $(senderEmail).addClass('is-invalid');
    } else {
        $(senderEmail).removeClass('is-invalid');
        $(senderEmail).addClass('is-valid');
    }
});

$(helpDeskEmail).on('input', () => {
    let isValid = Validation.validate($(helpDeskEmail).val(), $(helpDeskEmail).attr('type'));
    if (!isValid) {
        $(helpDeskEmail).addClass('is-invalid');
    } else {
        $(helpDeskEmail).removeClass('is-invalid');
        $(helpDeskEmail).addClass('is-valid');
    }
});

$(msgBody).on('input', () => {
    let msgValue = msgBody.value.trim();  
    let isValid = Validation.validateMessage(msgValue.val(), msgValue.attr('textarea'));
    if (!isValid) {
        $(msgBody).addClass('is-invalid');
    } else {
        $(msgBody).removeClass('is-invalid');
        $(msgBody).addClass('is-valid');
    }
});

$(sendBtn).on('submit', () => {
    return formValidation();
});