import { Validation } from './validation.js';
import  formValidation  from './messagevalidation.js';

const resetpwd = document.querySelector('#resetpswd');
const confRespwd = document.querySelector('#conf-reset-pswd');
const pwdIcon = document.querySelector('#pwd-icon');
const confPwdIcon = document.querySelector('#con-pwd-icon');
const resetBtn = document.querySelector('#reset');

$(function () {
        $(resetpwd).on('input', () => {
            let isValid = Validation.validate($(resetpwd).val(), $(resetpwd).attr('type'));
            if (!isValid) {
                $(resetpwd).addClass('is-invalid');
            } else {
                $(resetpwd).removeClass('is-invalid');
                $(resetpwd).addClass('is-valid');
            }
        });
 
        $(confRespwd).on('input', () => {
            let isValid = Validation.validate($(confRespwd).val(), $(confRespwd).attr('type'));
            let resetpwdInput = $(resetpwd).val();
            let confRespwdInput = $(confRespwd).val();
            if (!isValid && resetpwdInput !== confRespwdInput) {
                $(confRespwd).addClass('is-invalid');
                $("#password-not-match").removeAttr("hidden");
                $(resetBtn).prop("disabled", true);
            } else if (isValid && resetpwdInput !== confRespwdInput) {
                $(confRespwd).addClass('is-invalid');
                $("#password-not-match").removeAttr("hidden");
                $(resetBtn).prop("disabled", true);
            } else {
                $(confRespwd).removeClass('is-invalid');
                $(confRespwd).addClass('is-valid');
                $("#password-not-match").prop("hidden", true);
                $(resetBtn).prop("disabled", false);
            }
        });

    $(pwdIcon).on("click", function() {
        if ($(pwdIcon).hasClass("fa-eye")) {
            $(pwdIcon).removeClass("fa-eye");
            $(pwdIcon).addClass("fa-eye-slash");
            if ($(resetpwd).attr("type") === "password") {
                $(resetpwd).attr("type", "text");
            }
        } else {
            $(pwdIcon).removeClass("fa-eye-slash");
            $(pwdIcon).addClass("fa-eye");
            $(resetpwd).attr("type", "password");
            if ($(resetpwd).attr("type") === "text") {
                $(resetpwd).attr("type", "password");
            }
        }
    });

    $(confPwdIcon).on("click", function() {
        if ($(confPwdIcon).hasClass("fa-eye")) {
            $(confPwdIcon).removeClass("fa-eye");
            $(confPwdIcon).addClass("fa-eye-slash");
            if ($(confRespwd).attr("type") === "password") {
                $(confRespwd).attr("type", "text");
            }
        } else {
            $(confPwdIcon).removeClass("fa-eye-slash");
            $(confPwdIcon).addClass("fa-eye");
            $(confRespwd).attr("type", "password");
            if ($(confRespwd).attr("type") === "text") {
                $(confRespwd).attr("type", "password");
            }
        }
    });

    $(resetBtn).on('submit', () => {
        return formValidation();
    });
});

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
    container: 'body'
}));
//popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});
