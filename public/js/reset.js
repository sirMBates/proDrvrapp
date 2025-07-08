import { Validation } from './validation.js';
import  formValidation  from './messagevalidation.js';
const resetEmail = document.querySelector('#email');

$(resetEmail).on('blur', () => {
        let isValid = Validation.validate($(resetEmail).val(), $(resetEmail).attr('type'));
        if (!isValid) {
            $(resetEmail).addClass('is-invalid');
        } else {
            $(resetEmail).removeClass('is-invalid');
            $(resetEmail).addClass('is-valid');
            return formValidation;
        }
});