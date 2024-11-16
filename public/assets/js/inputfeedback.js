const inputChecked = document.querySelectorAll('.form-control-lg');
const saveBtn = document.querySelector('#save');
const fornameInput = inputChecked[0];
const surnameInput = inputChecked[1];
const birthdateInput = inputChecked[2];
const mobileNumInput = inputChecked[3];

const namePattern = /^[a-zA-Z]{1,}$/;
const numberPattern = /^\d{10}$/;
const datePattern = /^\d{4}[\-\/](0?[1-9]|1[012])[\-\/](0?[1-9]|[12][0-9]|3[01])$/;

$(function () {
    $(fornameInput).on('blur', () => {
        let isValid = namePattern.test($(fornameInput).val());
        if (!isValid) {
            $(fornameInput).addClass('is-invalid');
            let feedBk = fornameInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(fornameInput).removeClass('is-invalid');
            let feedBk = fornameInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
            $(fornameInput).addClass('is-valid');
        }
    })
    $(surnameInput).on('blur', () => {
        let isValid = namePattern.test($(surnameInput).val());
        if (!isValid) {
            $(surnameInput).addClass('is-invalid');
            let feedBk = surnameInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(surnameInput).removeClass('is-invalid');
            let feedBk = surnameInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
            $(surnameInput).addClass('is-valid');
        }
    })
    $(birthdateInput).on('blur', () => {
        let isValid = datePattern.test($(birthdateInput).val());
        if (!isValid) {
            $(birthdateInput).addClass('is-invalid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(birthdateInput).removeClass('is-invalid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
            $(birthdateInput).addClass('is-valid');
        }
    })
    $(mobileNumInput).on('blur', () => {
        let isValid = numberPattern.test($(mobileNumInput).val());
        if (!isValid) {
            $(mobileNumInput).addClass('is-invalid');
            let feedBk = mobileNumInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            $(mobileNumInput).removeClass('is-invalid');
            let feedBk = mobileNumInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
            $(mobileNumInput).addClass('is-valid');
        }
    })
});
