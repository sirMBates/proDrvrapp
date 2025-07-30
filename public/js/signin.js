import { Validation } from "./validation";
import formValidation from "./messagevalidation";

const checkInputs = document.querySelectorAll('.form-control');
const usernameInput = checkInputs[0]; 
const passwordInput = checkInputs[1];
const pwdIcon = document.querySelector('#psword-icon');
const signInBtn = document.querySelector('#signin');

$(function() {
    $(usernameInput).on('input', () => {
        let isValid = Validation.validateOnlyUsername($(usernameInput).val(), $(usernameInput).attr('type'));
        if (!isValid) {
            $(usernameInput).addClass('is-invalid');
        } else {
            $(usernameInput).removeClass('is-invalid');
            $(usernameInput).addClass('is-valid');
        }
    })

    $(passwordInput).on('input', () => {
        let isValid = Validation.validate($(passwordInput).val(), $(passwordInput).attr('type'));
        if (!isValid) {
            $(passwordInput).addClass('is-invalid');
            $(signInBtn).prop("disabled", true);
        } else {
            $(passwordInput).removeClass('is-invalid');
            $(passwordInput).addClass('is-valid');
            $(signInBtn).prop('disabled', false);
        }
    })

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
            $(passwordInput).attr("type, password");
            if ($(passwordInput).attr("type") === "text") {
                $(passwordInput).attr("type", "password");
            }
        }
    })

    $(signInBtn).on('submit', () => {
        return formValidation();
    })
});

const qString = window.location.search;
const urlParams = new URLSearchParams(qString);
const paramValue = urlParams.get('success');
if (paramValue === 'logged out') {
    setInterval(() => {
        window.location.href = '/signin';
    }, 5000);
}

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
//const popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});