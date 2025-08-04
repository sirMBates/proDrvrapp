import { Validation } from './validation.js';
import  formValidation  from './messagevalidation.js';
import { buildModal } from './appmodal.js';
// Set variables to the inputs from the form control class.
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoModBtn = document.querySelector('#info-ok');
const checkInputs = document.querySelectorAll('.form-control');
const usernameInput = checkInputs[0]; 
const emailInput = checkInputs[1]; 
const passwordInput = checkInputs[2];
const confPasswordInput = checkInputs[3];
const pwdIcon = document.querySelector('#pwd-icon');
const confPwdIcon = document.querySelector('#con-pwd-icon');
const signUpBtn = document.querySelector('#signup');
/*
** Validate and secure the inputs after each one has a value before submission 
** following this! ↓
*/
$(function () {
    $(usernameInput).on('input', () => {
        let isValid = Validation.validateOnlyUsername($(usernameInput).val(), $(usernameInput).attr('type'));
        if (!isValid) {
            $(usernameInput).addClass('is-invalid');
        } else {
            $(usernameInput).removeClass('is-invalid');
            $(usernameInput).addClass('is-valid');
        }
    })

    $(emailInput).on('input', () => {
        let isValid = Validation.validate($(emailInput).val(), $(emailInput).attr('type'));
        if (!isValid) {
            $(emailInput).addClass('is-invalid');
        } else {
            $(emailInput).removeClass('is-invalid');
            $(emailInput).addClass('is-valid');
        }
    })

    $(passwordInput).on('input', () => {
        let isValid = Validation.validate($(passwordInput).val(), $(passwordInput).attr('type'));
        if (!isValid) {
            $(passwordInput).addClass('is-invalid');
        } else {
            $(passwordInput).removeClass('is-invalid');
            $(passwordInput).addClass('is-valid');
        }
    })
        
    $(confPasswordInput).on('input', () => {
        let isValid = Validation.validate($(confPasswordInput).val(), $(confPasswordInput).attr('type'));
        let pswordVal = $(passwordInput).val();
        let confPswordVal = $(confPasswordInput).val();
        if (pswordVal.length > 0 && confPswordVal.length > 0) {
            if (!isValid && confPswordVal !== pswordVal) {
                $(confPasswordInput).addClass('is-invalid');
                $("#password-does-not-match-text").removeAttr("hidden");
                $(signUpBtn).prop("disabled", true);
            } else if (isValid && confPswordVal !== pswordVal) {
                $(confPasswordInput).addClass('is-invalid');
                $("#password-does-not-match-text").removeAttr("hidden");
                $(signUpBtn).prop("disabled", true);
            } else {
                $(confPasswordInput).removeClass('is-invalid');
                $(confPasswordInput).addClass('is-valid');
                $("#password-does-not-match-text").attr("hidden", true);
                $(signUpBtn).prop("disabled", false);
            }
        }
    })

// Checkbox functionality. Enables password to be hidden or viewed.
    $(pwdIcon).on("click", function() {
        if ($(pwdIcon).hasClass("fa-eye")) {
            $(pwdIcon).removeClass("fa-eye");
            $(pwdIcon).addClass("fa-eye-slash");
            if ($(passwordInput).attr("type") === "password") {
                $(passwordInput).attr("type", "text");
            }
        } else {
            $(pwdIcon).removeClass("fa-eye-slash");
            $(pwdIcon).addClass("fa-eye");
            $(passwordInput).attr("type", "password");
            if($(passwordInput).attr("type") === "text") {
                $(passwordInput).attr("type", "password");
            }
        }
    })

    $(confPwdIcon).on("click", function() {
        if ($(confPwdIcon).hasClass("fa-eye")) {
            $(confPwdIcon).removeClass("fa-eye");
            $(confPwdIcon).addClass("fa-eye-slash");
            if ($(confPasswordInput).attr("type") === "password") {
                $(confPasswordInput).attr("type", "text");
            }
        } else {
            $(confPwdIcon).removeClass("fa-eye-slash");
            $(confPwdIcon).addClass("fa-eye");
            $(confPasswordInput).attr("type", "password");
            if($(confPasswordInput).attr("type") === "text") {
                $(confPasswordInput).attr("type", "password");
            }
        }
    })

    $(signUpBtn).on('submit', () => {
        return formValidation();
    });
});

/*window.addEventListener('load', () => {
    setTimeout(() => {
        $(infoModal).modal('toggle'),
        infoModal.addEventListener('shown.bs.modal', () => {
            infoModalMsg.info(`This application uses storage cookies for functioning and performance purposes <strong><u>only</u></strong>. Continued use of this application beyond this point is your consent of agreement of said usage.`, 'I Agree');
        })
    }, 2000)
    infoModBtn.addEventListener('click', () => {
        $(infoModal).modal('toggle');
    })
});*/

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
    container: 'body'
}));
//popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});

