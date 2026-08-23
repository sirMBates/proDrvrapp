import { Validation } from "./validation.js";
import { fetchDrvr, showFlashAlert, formValidation } from "./helpers.js";
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
        statusDisplay.textContent = localStorage.getItem('status') || sessionStorage.getItem('status');
    })
    .catch(err => console.error("Profile fetch error:", err));
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

// Inline editing for cards
document.querySelectorAll('.editable').forEach(card => {
    const btn = card.querySelector('.edit-btn');
    const field = card.dataset.field;
    const display = card.querySelector('.field-value');

    btn.addEventListener('click', () => {
        btn.disabled = true;
        const currentValue = display.textContent;

        if (field === 'password') {
            display.innerHTML = `
                <div class="input-group">
                    <input type="password" class="form-control form-control-sm edit-input" id="passwordInput" autocomplete="new-password">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show password" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                </div>
                <button type="button" class="btn btn-primary btn-sm mt-2 save-btn">
                    Save
                </button>
            `;
        } else {
            display.innerHTML = `
                <input type="text" class="form-control form-control-sm edit-input" id="${field}Input" value="${currentValue}">
                <button type="button" class="btn btn-primary btn-sm mt-2 save-btn">
                    Save
                </button>
            `;
        }

        const input = card.querySelector(`#${field}Input`);
        const saveBtn = card.querySelector('.save-btn');
        const passwordToggle = card.querySelector('.password-toggle');

        if (passwordToggle) {
            const icon = passwordToggle.querySelector('i');

            passwordToggle.addEventListener('click', () => {
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';

                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);

                passwordToggle.setAttribute(
                    'aria-label',
                    isHidden ? 'Hide password' : 'Show password'
                );

                passwordToggle.setAttribute(
                    'aria-pressed',
                    String(isHidden)
                );
            });
        }

        saveBtn.addEventListener('click', () => {
            const newValue = field === 'password' ? input.value : input.value.trim();

            let isValid = true;

            if (field === 'email') {
                isValid = Validation.validate(newValue, 'email');
            } else if (field === 'mobile') {
                isValid = Validation.validate(newValue, 'tel');
            } else if (field === 'password') {
                isValid = Validation.validate(newValue, 'password');
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                return;
            }

            input.classList.remove('is-invalid');

            display.dataset.updated = newValue;

            if (field === 'password') {
                display.textContent = '********';
            } else {
                display.textContent = newValue;
            }
            btn.disabled = false;
        });
    });
});

// Submit updated info
updateInfoBtn.addEventListener('click', () => {
    const updatedEmail = emailDisplay.dataset.updated;
    const updatedMobile = mobileDisplay.dataset.updated;

    const payload = {
        action: 'update-contact-information',
        email: updatedEmail ?? '',
        mobile: updatedMobile ?? '',
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    return formValidation(payload);
});

// Submit updated password
updatePswdBtn.addEventListener('click', () => {
    const updatedPassword = document.querySelector('#passwordDisplay')?.dataset.updated;

    const payload = {
        action: 'update-password',
        password: updatedPassword ?? '',
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    return formValidation(payload);
});
