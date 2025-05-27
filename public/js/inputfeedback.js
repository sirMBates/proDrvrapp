const inputChecked = document.querySelectorAll('.form-control-lg');
const saveBtn = document.querySelector('#register');
const fornameInput = inputChecked[0];
const surnameInput = inputChecked[1];
const mobileNumInput = inputChecked[2];
const birthdateInput = inputChecked[3];
const namePattern = /^[a-zA-Z]{1,}$/;
const numberPattern = /^[0-9]{10}$/;
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
    $(birthdateInput).on('blur', () => {
        let isValid = datePattern.test($(birthdateInput).val());
        if (!isValid) {
            console.log(isValid);
            $(birthdateInput).addClass('is-invalid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.add('d-block');
        } else {
            console.log(isValid);
            $(birthdateInput).removeClass('is-invalid');
            let feedBk = birthdateInput.parentElement.nextElementSibling;
            feedBk.classList.remove('d-block');
            $(birthdateInput).addClass('is-valid');
        }
    })
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

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
