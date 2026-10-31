import { Validation } from '../validation/validation.js';
import { formValidation }  from '../shared/helpers.js';

const forgetEmail = document.querySelector('#email');
const forgetBtn = document.querySelector('#forget-pwd');

(() => {
    $(forgetEmail).on('input', () => {
            let isValid = Validation.validate($(forgetEmail).val(), $(forgetEmail).attr('type'));
            if (!isValid) {
                $(forgetEmail).addClass('is-invalid');
            } else {
                $(forgetEmail).removeClass('is-invalid');
                $(forgetEmail).addClass('is-valid');
            }
    });

    $(forgetBtn).on('submit', () => {
        return formValidation();
    })

})();