import { Validation } from "./validation.js";
import { formValidation } from "./helpers.js";

const firstNameInput = document.querySelector('#forename');
const lastNameInput = document.querySelector('#surname');
const mobileNumberInput = document.querySelector('#mobile_num');
const birthDateInput = document.querySelector('#date-of-birth');
const driverLicenseInput = document.querySelector('#cdl-driver-license');
const driverLicenseStateInput = document.querySelector('#driver-license-state');
const driverLicenseExpireInput = document.querySelector('#driver-license-expire');
const dotMedicalExpireInput = document.querySelector('#dot-medical-expire');

function setValidationState(input, isValid) {
    const feedback = input.closest('.form-floating')?.nextElementSibling;
    input.classList.toggle('is-invalid', !isValid);
    input.classList.toggle('is-valid', isValid);
    feedback?.classList.toggle('d-block', !isValid);
};

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

driverLicenseInput?.addEventListener('input', () => {
    const isValid = Validation.validate(driverLicenseInput.value, 'license');
    setValidationState(driverLicenseInput, isValid);
});

driverLicenseStateInput?.addEventListener('input', () => {
    const normalizedState = driverLicenseStateInput.value.replace(/[^a-zA-Z]/g, '').slice(0, 2).toUpperCase();
    driverLicenseStateInput.value = normalizedState;

    const isValid = Validation.validate(normalizedState, 'state');
    setValidationState(driverLicenseStateInput, isValid);
});

driverLicenseExpireInput?.addEventListener('input', () => {
    const isValid = Validation.validate(driverLicenseExpireInput.value, 'expiration-date');
    setValidationState(driverLicenseExpireInput, isValid);
});

dotMedicalExpireInput?.addEventListener('input', () => {
    const isValid = Validation.validate(dotMedicalExpireInput.value, 'expiration-date');
    setValidationState(dotMedicalExpireInput, isValid);
});

function setMinimumExpirationDate() {
    const today = new Date();

    const todayString = [
        today.getFullYear(),
        String(today.getMonth() + 1).padStart(2, '0'),
        String(today.getDate()).padStart(2, '0')
    ].join('-');

    driverLicenseExpireInput?.setAttribute('min', todayString);
    dotMedicalExpireInput?.setAttribute('min', todayString);
};

setMinimumExpirationDate();

// Initialize Bootstrap/native form validation.
formValidation();
