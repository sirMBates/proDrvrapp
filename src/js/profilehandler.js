import { Validation } from "./validation.js";
import formValidation from "./helpers.js";
import { fetchDrvr, showFlashAlert } from "./helpers.js";
import { initProfilePictureHandler } from "./profile.js";

// Elements
const drvrAlert = showFlashAlert;
const pageInput = document.querySelector('#profilePictureInput');
const pageImage = document.querySelector('#profilePictureImage');
const defaultProfileImage = '../../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png'; 
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
        const currentValue = display.textContent;

        display.innerHTML = `
            <input type="${field === 'password' ? 'password' : 'text'}"
                   class="form-control form-control-sm edit-input"
                   id="${field}Input"
                   value="${field === 'password' ? '' : currentValue}">
            <button class="btn btn-primary btn-sm mt-2 save-btn">Save</button>
        `;

        const input = document.querySelector(`#${field}Input`);
        const saveBtn = card.querySelector('.save-btn');

        saveBtn.addEventListener('click', () => {
            const newValue = input.value.trim();

            // Validation
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

            // Update display
            display.textContent = newValue || "********";

            // Store updated value for submission
            display.dataset.updated = newValue;
        });
    });
});

// Submit updated info
updateInfoBtn.addEventListener('click', () => {
    const updatedEmail = emailDisplay.dataset.updated;
    const updatedMobile = mobileDisplay.dataset.updated;

    const payload = {
        email: updatedEmail,
        mobile: updatedMobile,
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    return formValidation(payload);
});

// Submit updated password
updatePswdBtn.addEventListener('click', () => {
    const updatedPassword = document.querySelector('#passwordDisplay')?.dataset.updated;

    const payload = {
        password: updatedPassword,
        drvrtoken: drvrToken,
        __method: "PATCH"
    };

    return formValidation(payload);
});
