import { Validation } from '../validation/validation.js';
import { formValidation } from '../shared/helpers.js';

const forgetEmail = document.querySelector('#email');
const forgetForm = document.querySelector('#forgetpswd');

(() => {
    if (!forgetEmail || !forgetForm) return;

    // Initialize Bootstrap form validation once.
    formValidation();

    // Provide immediate email validation feedback.
    $(forgetEmail).on('input', () => {
        const isValid = Validation.validate($(forgetEmail).val(), $(forgetEmail).attr('type'));
        $(forgetEmail).toggleClass('is-invalid', !isValid).toggleClass('is-valid', isValid);
    });
})();
