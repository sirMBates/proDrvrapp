import { Validation } from "./validation.js";
import { formValidation } from "./helpers.js";

const form = document.querySelector('#acct_info');
const firstNameInput = document.querySelector('#forename');
const lastNameInput = document.querySelector('#surname');
const mobileNumberInput = document.querySelector('#mobile_num');
const birthDateInput = document.querySelector('#date-of-birth');
const driverId = document.querySelector('#driverId')?.value ?? '';
const operatorIdInput = document.querySelector('#newOperatorId');

function setValidationState(input, isValid) {
    const feedback = input.closest('.form-floating')?.nextElementSibling;
    input.classList.toggle('is-invalid', !isValid);
    input.classList.toggle('is-valid', isValid);
    feedback?.classList.toggle('d-block', !isValid);
}

firstNameInput?.addEventListener('input', () => {
    const isValid = Validation.validate(firstNameInput.value, 'text');
    setValidationState(firstNameInput, isValid);
});

lastNameInput?.addEventListener('input', () => {
    const isValid = Validation.validate(lastNameInput.value, 'text');
    setValidationState(lastNameInput, isValid);
});

mobileNumberInput?.addEventListener('input', () => {
    const isValid = Validation.validate(mobileNumberInput.value, 'tel');
    setValidationState(mobileNumberInput, isValid);
});

birthDateInput?.addEventListener('input', () => {
    const isValid = Validation.validate(birthDateInput.value, 'date');
    setValidationState(birthDateInput, isValid);
});

function createOperatorId(value) {
    if (!operatorIdInput || value === '') {
        return;
    }
    
    const prefix = 'PRODRVR';
    const idNumber = value.padStart(5, '0');
    const operatorId = `${prefix}-${idNumber}`;
    operatorIdInput.value = operatorId;
};
createOperatorId(driverId);

// Initialize Bootstrap/native form validation.
formValidation();