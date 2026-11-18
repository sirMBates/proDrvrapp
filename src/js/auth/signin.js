import { Validation } from "../validation/validation.js";
import { formValidation } from '../shared/helpers.js';
import { buildModal } from '../shared/appmodal.js';

const checkInputs = document.querySelectorAll('.form-control');
const usernameInput = checkInputs[0]; 
const passwordInput = checkInputs[1];
const pwdIcon = document.querySelector('#psword-icon');
const signInForm = document.querySelector('#logInAcct');
const signInBtn = document.querySelector('#signin');
const infoModal = document.querySelector('#info-modal');
const infoModalMsg = buildModal;
const infoModBtn = document.querySelector('#info-ok');

$(document).ready(() => {
    $(usernameInput).on('input', () => {
        let isValid = Validation.validateOnlyUsername($(usernameInput).val(), $(usernameInput).attr('type'));
        if (!isValid) {
            $(usernameInput).addClass('is-invalid');
        } else {
            $(usernameInput).removeClass('is-invalid');
            $(usernameInput).addClass('is-valid');
        }
    });

    $(passwordInput).on('input', () => {
        let isValid = Validation.validate($(passwordInput).val(), 'password');
        if (!isValid) {
            $(passwordInput).addClass('is-invalid');
            $(signInBtn).prop("disabled", true);
        } else {
            $(passwordInput).removeClass('is-invalid');
            $(passwordInput).addClass('is-valid');
            $(signInBtn).prop('disabled', false);
        }
    });

    $(pwdIcon).on("click", function() {
        const isHidden = $(passwordInput).attr("type") === "password";
        $(passwordInput).attr("type", isHidden ? "text" : "password");
        $(pwdIcon).toggleClass("fa-eye fa-eye-slash");
    });

    $(signInForm).on('submit', () => {
        return formValidation();
    });
});

const qString = window.location.search;
const urlParams = new URLSearchParams(qString);
const deviceTransferRequired = urlParams.get('device_transfer') === 'required';

if (deviceTransferRequired) {
    const confirmModal = document.querySelector('#confirm-modal');
    const confirmBtn = document.querySelector('#confirm-modal-confirm');
    const cancelBtn = document.querySelector('#confirm-modal-cancel');
    const csrfToken = document.querySelector('input[name="userToken"]')?.value;

    if (confirmModal && confirmBtn && cancelBtn && csrfToken) {
        buildModal.confirm('Your account is already signed in on another device. Continuing will sign out that device and transfer your session to this one. Would you like to continue?', 'Transfer to This Device', 'Cancel');

        const modal = bootstrap.Modal.getOrCreateInstance(confirmModal);

        const submitTransfer = (action) => {
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/device-transfer';

            for (const [name, value] of Object.entries({
                userToken: csrfToken,
                transfer_action: action
            })) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        };

        confirmBtn.addEventListener('click', () => submitTransfer('confirm'));
        cancelBtn.addEventListener('click', () => submitTransfer('cancel'));

        modal.show();
    } else {
        console.error('Device-transfer confirmation could not be initialized.');
    }
};

const paramValue = urlParams.get('success');
if (paramValue === 'logged out') {
    setInterval(() => {
        window.location.href = '/signin';
    }, 2000);
};

// Check if the client has agreed already
const drvrAgreed = localStorage.getItem('operator_consented');
if (!drvrAgreed && !deviceTransferRequired) {
    window.addEventListener('load', () => {
        setTimeout(() => {
            $(infoModal).modal('toggle'),
            infoModal.addEventListener('shown.bs.modal', () => {
                infoModalMsg.info(`This application uses storage cookies for functioning and performance purposes <strong><u>only</u></strong>. Continued use of this application beyond this point is your consent of agreement of said usage.`, 'I Agree');
            })
        }, 2000)
        infoModBtn.addEventListener('click', () => {
            localStorage.setItem('operator_consented', true);
            $(infoModal).modal('toggle');
        })
    });
};

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
    container: 'body',
    trigger: 'focus'
}));
//const popover = new bootstrap.Popover('.popover-dismiss', {trigger: 'focus'});