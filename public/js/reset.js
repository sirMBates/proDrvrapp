import { Validation } from './validation.js';
import  formValidation  from './messagevalidation.js';
if (window.location.pathname === '/forget') {
    const forgetEmail = document.querySelector('#email');
}
const resetpwd = document.querySelector('#resetpswd');
const confRespwd = document.querySelector('#conf-reset-pswd');

if (window.location.pathname === '/forget') {
    $(forgetEmail).on('blur', () => {
            let isValid = Validation.validate($(resetEmail).val(), $(resetEmail).attr('type'));
            if (!isValid) {
                $(resetEmail).addClass('is-invalid');
            } else {
                $(resetEmail).removeClass('is-invalid');
                $(resetEmail).addClass('is-valid');
                return formValidation;
            }
    });
}

$(resetpwd).on('blur', () => {
        let isValid = Validation.validate($(resetpwd).val(), $(resetpwd).attr('type'));
        if (!isValid) {
            $(resetpwd).addClass('is-invalid');
        } else {
            $(resetpwd).removeClass('is-invalid');
            $(resetpwd).addClass('is-valid');
            return formValidation;
        }
});

$(confRespwd).on('blur', () => {
        let isValid = Validation.validate($(confRespwd).val(), $(confRespwd).attr('type'));
        let resetpwdInput = $(resetpwd).val();
        let confRespwdInput = $(confRespwd).val();
        if (resetpwdInput.length > 0 && confRespwdInput.length > 0) {
            if (!isValid && confRespwdInput !== resetpwdInput) {
                $(confRespwdInput).addClass('is-invalid');
                $("#password-not-match").removeAttr("hidden");
                $("#reset").attr("disabled", true);
            } else if (isValid && confRespwdInput !== resetpwdInput) {
                $(confRespwdInput).addClass('is-invalid');
                $("#password-not-match").removeAttr("hidden");
                $("#reset").attr("disabled", true);
            } else {
                $(confRespwdInput).removeClass('is-invalid');
                $(confRespwdInput).addClass('is-valid');
                $('#reset').prop("disabled", false);
                $("#reset").removeAttr("disabled");
                $("#password-not-match").attr("hidden", true);
                return formValidation;
            }
        }
});

$(function() {
    $("#pwd-icon").on("click", function() {
        if ($("#pwd-icon").hasClass("fa-eye")) {
            $("#pwd-icon").removeClass("fa-eye");
            $("#pwd-icon").addClass("fa-eye-slash");
            if ($("#resetpswd").attr("type") === "password") {
                $("#resetpswd").attr("type", "text");
            }
        } else {
            $("#pwd-icon").removeClass("fa-eye-slash");
            $("#pwd-icon").addClass("fa-eye");
            $("#resetpswd").attr("type, password");
            if($("#resetpswd").attr("type") === "text") {
                $("#resetpswd").attr("type", "password");
            }
        }
    })
    $("#con-pwd-icon").on("click", function() {
        if ($("#con-pwd-icon").hasClass("fa-eye")) {
            $("#con-pwd-icon").removeClass("fa-eye");
            $("#con-pwd-icon").addClass("fa-eye-slash");
            if ($("#conf-reset-pswd").attr("type") === "password") {
                $("#conf-reset-pswd").attr("type", "text");
            }
        } else {
            $("#con-pwd-icon").removeClass("fa-eye-slash");
            $("#con-pwd-icon").addClass("fa-eye");
            $("#conf-reset-pswd").attr("type, password");
            if($("#conf-reset-pswd").attr("type") === "text") {
                $("#conf-reset-pswd").attr("type", "password");
            }
        }
    })
});

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));