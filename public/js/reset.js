import { Validation } from './validation.js';
const resetEmail = document.querySelector('#email');

$(resetEmail).on('blur', () => {
        let isValid = Validation.validate($(resetEmail).val(), $(resetEmail).attr('type'));
        if (!isValid) {
            $(resetEmail).addClass('is-invalid');
        } else {
            $(resetEmail).removeClass('is-invalid');
            $(resetEmail).addClass('is-valid');
        }
});

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