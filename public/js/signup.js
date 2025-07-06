import { buildModal } from './appmodal.js';
// Used to test & validate the fields as they are filled. ↓
const unamePattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,}$/;
const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
const pswordPattern = /^(?=.*[a-z])(?=.*[A-Z]*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/;

// Set variables to the inputs from the form control class.
const checkInputs = document.querySelectorAll('.form-control');
const usernameInput = checkInputs[0]; 
const emailInput = checkInputs[1]; 
const passwordInput = checkInputs[2];
const confPasswordInput = checkInputs[3];
// Validate and secure the inputs after each one has a value before submission 
// following this! ↓ 
$(function () {
    $(usernameInput).on('blur', () => {
        let isValid = unamePattern.test($(usernameInput).val());
        if (!isValid) {
            $(usernameInput).addClass('is-invalid');
        } else {
            $(usernameInput).removeClass('is-invalid');
            $(usernameInput).addClass('is-valid');
        }
    })

    $(emailInput).on('blur', () => {
        let isValid = emailPattern.test($(emailInput).val());
        if (!isValid) {
            $(emailInput).addClass('is-invalid');
        } else {
            $(emailInput).removeClass('is-invalid');
            $(emailInput).addClass('is-valid');
        }
    })

    $(passwordInput).on('blur', () => {
        let isValid = pswordPattern.test($(passwordInput).val());
        if (!isValid) {
            $(passwordInput).addClass('is-invalid');
        } else {
            $(passwordInput).removeClass('is-invalid');
            $(passwordInput).addClass('is-valid');
        }
    })
        
    $(confPasswordInput).on('blur', () => {
        let isValid = pswordPattern.test($(confPasswordInput).val());
        let pswordVal = $(passwordInput).val();
        let confPswordVal = $(confPasswordInput).val();
        if (pswordVal.length > 0 && confPswordVal.length > 0) {
            if (!isValid && confPswordVal !== pswordVal) {
                $(confPasswordInput).addClass('is-invalid');
                $("#password-does-not-match-text").removeAttr("hidden");
                $("#signup").attr("disabled", true);
            } else if (isValid && confPswordVal !== pswordVal) {
                $(confPasswordInput).addClass('is-invalid');
                $("#password-does-not-match-text").removeAttr("hidden");
                $("#signup").attr("disabled", true);
            } else {
                $(confPasswordInput).removeClass('is-invalid');
                $(confPasswordInput).addClass('is-valid');
                $('#signup').prop("disabled", false);
                $("#signup").removeAttr("disabled");
                $("#password-does-not-match-text").attr("hidden", true);
            }
        }
    })
});

// Checkbox functionality. Enables password to be hidden or viewed.
$(function() {
    $("#pwd-icon").on("click", function() {
        if ($("#pwd-icon").hasClass("fa-eye")) {
            $("#pwd-icon").removeClass("fa-eye");
            $("#pwd-icon").addClass("fa-eye-slash");
            if ($("#password").attr("type") === "password") {
                $("#password").attr("type", "text");
            }
        } else {
            $("#pwd-icon").removeClass("fa-eye-slash");
            $("#pwd-icon").addClass("fa-eye");
            $("#password").attr("type, password");
            if($("#password").attr("type") === "text") {
                $("#password").attr("type", "password");
            }
        }
    })
    $("#con-pwd-icon").on("click", function() {
        if ($("#con-pwd-icon").hasClass("fa-eye")) {
            $("#con-pwd-icon").removeClass("fa-eye");
            $("#con-pwd-icon").addClass("fa-eye-slash");
            if ($("#confirmPassword").attr("type") === "password") {
                $("#confirmPassword").attr("type", "text");
            }
        } else {
            $("#con-pwd-icon").removeClass("fa-eye-slash");
            $("#con-pwd-icon").addClass("fa-eye");
            $("#confirmPassword").attr("type, password");
            if($("#confirmPassword").attr("type") === "text") {
                $("#confirmPassword").attr("type", "password");
            }
        }
    })
});

// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
  
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')
  
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            }
  
            form.classList.add('was-validated')
        }, false)
    })
})();

/*window.addEventListener('load', () => {
    const infoMsg = document.querySelector('#info-modal');
    const infoModBtn = document.querySelector('#info-ok');
    setTimeout(() => {
        $(infoMsg).modal('toggle'),
        infoMsg.addEventListener('shown.bs.modal', () => {
            buildModal.info(`This application uses storage cookies for functioning and performance purposes <strong><u>only</u></strong>. Continued use of this application beyond this point is your consent of agreement of said usage.`, 'I Agree');
        })
    }, 2000)
    infoModBtn.addEventListener('click', () => {
        $(infoMsg).modal('toggle');
    })
});*/

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
//const popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});

