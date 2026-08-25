import { Validation } from "./validation.js";
import { fetchDrvr, showFlashAlert, submitFormPayload } from "./helpers.js";
import { initProfilePictureHandler } from "./profile.js";

// Elements
const drvrAlert = showFlashAlert;
const pageInput = document.querySelector('#profilePictureInput');
const pageImage = document.querySelector('#profilePictureImage');
const defaultProfileImage = '../../dist/images-videos/logoandicons/defaultProfileImage.jpg';
const drvrToken = document.querySelector('#drvrToken').value;
const fullnameDisplay = document.querySelector('#fullnameDisplay');
const operatorIdDisplay = document.querySelector('#operatorIdDisplay');
const usernameDisplay = document.querySelector('#usernameDisplay');
const emailDisplay = document.querySelector('#emailDisplay');
const mobileDisplay = document.querySelector('#mobileDisplay');
const statusDisplay = document.querySelector('#statusDisplay');
const passwordDisplay = document.querySelector('#passwordDisplay');
const updateInfoBtn = document.querySelector('#updateInfoBtn');
const updatePswdBtn = document.querySelector('#updatePswdBtn');

// Fetch driver profile
window.addEventListener('DOMContentLoaded', () => {
    fetchDrvr("https://prodriver.local/getprofile", {
        mode: 'cors',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': drvrToken
        }
    })
    .then(driver => {
        fullnameDisplay.textContent = `${driver.firstName} ${driver.lastName}`;
        operatorIdDisplay.textContent = driver.operatorid;
        usernameDisplay.textContent = driver.username;
        emailDisplay.textContent = driver.email;
        mobileDisplay.textContent = driver.mobileNumber;
        statusDisplay.textContent = localStorage.getItem('status') || sessionStorage.getItem('status') || '';
    })
    .catch(error => {
        console.error("Profile fetch error:", error);
    });
});

if (pageInput && pageImage) {
    initProfilePictureHandler({
        profileInput: pageInput,
        profileImage: pageImage,
        drvrToken,
        getDriver: fetchDrvr,
        defaultProfileImage: defaultProfileImage,
        Validation,
        drvrAlert
    });
};

// Password visibility
function initializePasswordToggles(card) {
    card.querySelectorAll('.password-toggle').forEach(toggle => {
        const targetId = toggle.dataset.target;
        const input = card.querySelector(`#${targetId}`);
        const icon = toggle.querySelector('i');

        if (!input || !icon) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);

            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            toggle.setAttribute('aria-pressed', String(isHidden));
        });
    });
};

// Password card editor
function buildPasswordEditor(display) {
    display.innerHTML = `
        <div class="input-group mb-2">
            <input id="currentPasswordInput" type="password" class="form-control form-control-sm current-password-input" placeholder="Current password" autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="currentPasswordInput" aria-label="Show current password" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
        </div>

        <div class="input-group">
            <input id="passwordInput" type="password" class="form-control form-control-sm new-password-input" placeholder="New password" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="passwordInput" aria-label="Show new password" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
        </div>

        <div class="input-group">
            <input id="confirmPasswordInput" type="password" class="form-control form-control-sm" placeholder="Confirm new password" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="confirmPasswordInput" aria-label="Show confirmed password" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
        </div>

        <p class="text-danger mt-1 password-match-error" hidden>Passwords do not match.</p>

        <button type="button" class="btn btn-primary btn-sm mt-2 save-btn">
            Save
        </button>
    `;
};

// Standard filed editor
function buildFieldEditor(display, field, currentValue) {
    display.innerHTML = `
        <input id="${field}Input" class="form-control form-control-sm edit-input" type="text" value="${currentValue}">
        <button type="button" class="btn btn-primary btn-sm mt-2 save-btn">
            Save
        </button>
    `;
};

// Inline editing
document.querySelectorAll('.editable').forEach(card => {
    const editBtn = card.querySelector('.edit-btn');
    const field = card.dataset.field;
    const display = card.querySelector('.field-value');

    if (!editBtn || !field || !display) {
        return;
    }

    editBtn.addEventListener('click', () => {
        editBtn.disabled = true;
        const currentValue = display.textContent.trim();

        if (field === 'password') {
            buildPasswordEditor(display);
            initializePasswordToggles(card);
        } else {
            buildFieldEditor(display, field, currentValue);
        }

        const saveBtn = card.querySelector('.save-btn');
        if (!saveBtn) {
            return;
        }

        saveBtn.addEventListener('click', () => {
            if (field === 'password') {
                const currentPasswordInput = card.querySelector('#currentPasswordInput');
                const newPasswordInput = card.querySelector('#passwordInput');
                const confirmPasswordInput = card.querySelector('#confirmPasswordInput');
                const passwordMatchError = card.querySelector('.password-match-error');
                const currentPassword = currentPasswordInput?.value ?? '';
                const newPassword = newPasswordInput?.value ?? '';
                const confirmPassword = confirmPasswordInput?.value ?? '';
                let hasError = false;

                if (currentPassword === '') {
                    currentPasswordInput?.classList.add('is-invalid');
                    hasError = true;
                } else {
                    currentPasswordInput?.classList.remove('is-invalid');
                }

                const validNewPassword = Validation.validate(newPassword, 'password');

                if (!validNewPassword) {
                    newPasswordInput?.classList.add('is-invalid');
                    hasError = true;
                } else {
                    newPasswordInput?.classList.remove('is-invalid');
                }

                if (confirmPassword === '' || confirmPassword !== newPassword) {
                    confirmPasswordInput?.classList.add('is-invalid');
                    if (passwordMatchError) {
                        passwordMatchError.hidden = false;
                    }
                    hasError = true;
                } else {
                    confirmPasswordInput?.classList.remove('is-invalid');
                    if (passwordMatchError) {
                        passwordMatchError.hidden = true;
                    }
                }

                if (hasError) {
                    return;
                }

                display.dataset.currentPassword = currentPassword;
                display.dataset.newPassword = newPassword;
                display.dataset.confirmPassword = confirmPassword;
                display.textContent = '********';
                editBtn.disabled = false;
                return;
            }

            const input = card.querySelector(`#${field}Input`);

            if (!input) {
                return;
            }

            const newValue = input.value.trim();
            let isValid = true;

            if (field === 'email') {
                isValid = Validation.validate(newValue, 'email');
            }

            if (field === 'mobile') {
                isValid = Validation.validate(newValue, 'tel');
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                return;
            }

            input.classList.remove('is-invalid');
            display.dataset.updated = newValue;
            display.textContent = newValue;
            editBtn.disabled = false;
        });
    });
});

// Submit updated info
updateInfoBtn?.addEventListener('click', () => {
    const updatedEmail = emailDisplay?.dataset.updated ?? '';
    const updatedMobile = mobileDisplay?.dataset.updated ?? '';
    const payload = {
        action: 'update-contact-information',
        email: updatedEmail,
        mobile: updatedMobile,
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    submitFormPayload('#acctinfo', payload);
});

// Submit updated password
updatePswdBtn?.addEventListener('click', () => {
    const currentPassword = passwordDisplay?.dataset.currentPassword ?? '';
    const newPassword = passwordDisplay?.dataset.newPassword ?? '';
    const confirmPassword = passwordDisplay?.dataset.confirmPassword ?? '';
    const payload = {
        action: 'update-password',
        currentPassword,
        newPassword,
        confirmPassword,
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    submitFormPayload('#acctinfo', payload);
});
