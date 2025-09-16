import { Validation } from "./validation";
import formValidation from "./messagevalidation";

const inputChecked = document.querySelectorAll('.form-control-lg');
const saveBtn = document.querySelector('#register');
const fornameInput = inputChecked[0];
const surnameInput = inputChecked[1];
const mobileNumInput = inputChecked[2];
const birthdateInput = inputChecked[3];
const dbDrvrId = document.querySelector('#companyId').value;
const newOpId = document.querySelector('#newOperatorId');
const registerAcct = document.querySelector('#acct_info');

$(function () {
    $(fornameInput).on('input', () => {
        let isValid = Validation.validate($(fornameInput).val(), $(fornameInput).attr('type'));
        if (!isValid) {
            $(fornameInput).addClass('is-invalid');
            let feedBk = fornameInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(fornameInput).removeClass('is-invalid');
            $(fornameInput).addClass('is-valid');
            let feedBk = fornameInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
        }
    })

    $(surnameInput).on('input', () => {
        let isValid = Validation.validate($(surnameInput).val(), $(surnameInput).attr('type'));
        if (!isValid) {
            $(surnameInput).addClass('is-invalid');
            let feedBk = surnameInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(surnameInput).removeClass('is-invalid');
            $(surnameInput).addClass('is-valid');
            let feedBk = surnameInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
        }
    })

    $(mobileNumInput).on('input', () => {
        let isValid = Validation.validate($(mobileNumInput).val(), $(mobileNumInput).attr('type'));
        if (!isValid) {
            $(mobileNumInput).addClass('is-invalid');
            let feedBk = mobileNumInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(mobileNumInput).removeClass('is-invalid');
            $(mobileNumInput).addClass('is-valid');
            let feedBk = mobileNumInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
        }
    })

    $(birthdateInput).on('input', () => {
        let isValid = Validation.validate($(birthdateInput).val(), $(birthdateInput).attr('type'));
        if (!isValid) {
            $(birthdateInput).addClass('is-invalid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(birthdateInput).removeClass('is-invalid');
            $(birthdateInput).addClass('is-valid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
        }
    })

    function createOperatorId (value) {
        let currentId = value;
        let beginIdWith = 'BTT';
        let newIDNumber = currentId.padStart(5, '0');
        let newOperatorId = `${beginIdWith}-${newIDNumber}`;
        newOpId.value = newOperatorId;
        return newOperatorId;
    };
    console.log(createOperatorId(dbDrvrId));

    $(saveBtn).on('submit', () => {
        //e.preventDefault;
        //createOpId.value = createOperatorId(createOpId);
        return formValidation();
        //registerAcct.submit();
    })
});

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
    container: 'body'
}));