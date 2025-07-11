import { Validation } from './validation.js';
import  formValidation  from './messagevalidation.js';

const forgetEmail = document.querySelector('#email');

if (window.location.pathname === '/forget') {
    $(forgetEmail).on('blur', () => {
            let isValid = Validation.validate($(forgetEmail).val(), $(forgetEmail).attr('type'));
            if (!isValid) {
                $(forgetEmail).addClass('is-invalid');
            } else {
                $(forgetEmail).removeClass('is-invalid');
                $(forgetEmail).addClass('is-valid');
                return formValidation;
            }
    });
}