const unamePattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,6}$/;
const pswordPattern = /^(?=.*[a-z])(?=.*[A-Z]*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/;
const checkInputs = document.querySelectorAll('.form-control');
const usernameInput = checkInputs[0]; 
const passwordInput = checkInputs[1];

$(function() {
    $(usernameInput).on('blur', () => {
        let isValid = unamePattern.test($(usernameInput).val());
        if (!isValid) {
            $(usernameInput).addClass('is-invalid');
        } else {
            $(usernameInput).removeClass('is-invalid');
            $(usernameInput).addClass('is-valid');
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
    // Handle disable attribute on login form. ↓
    function removeRestraint() {
        $(passwordInput).on('keyup', () => {
            let check = passwordInput.checkValidity();
            if (check) {
                $('#signin').prop('disabled', false);
                $('#signin').removeAttr('disabled');
            }
        })
    };
    removeRestraint();

    $("#psword-icon").on("click", function() {
        if ($("#psword-icon").hasClass("fa-eye")) {
            $("#psword-icon").removeClass("fa-eye");
            $("#psword-icon").addClass("fa-eye-slash");
            if ($("#password").attr("type") === "password") {
                $("#password").attr("type", "text");
            }
        } else {
            $("#psword-icon").removeClass("fa-eye-slash");
            $("#psword-icon").addClass("fa-eye");
            $("#password").attr("type, password");
            if($("#password").attr("type") === "text") {
                $("#password").attr("type", "password");
            }
        }
    })
})

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

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
//const popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});