import { buildModal } from './appmodal.js';
import 'jSignature';
const signatureBoxBtn = document.querySelector('#signature-widget-buttons')
const openSignBoxBtn = document.querySelector('#open-sign-box');
const getPostSignatureBtn = document.querySelector('#get-next-signature');
const closeSignPadBtn = document.querySelector('#close-sign-pad');
const signBox = document.querySelector('#signaturecon');
const signpad = document.querySelector('#signaturePad');
const clearBtn = signpad.parentNode.childNodes[3].childNodes[1];
const signBtn = signpad.parentNode.childNodes[3].childNodes[3];
const secondSignBtn = signBtn.nextElementSibling;
const signatureCheck = document.querySelector('#rendered');
const imgInspBox = document.querySelector('#insp_img_box');
const preInspSign = document.querySelector('#pre-trip');
const postInspSign = document.querySelector('#post-trip');
const warnModalMsg = buildModal;
const warnModal = document.querySelector('#warn-modal');
const warnModalBtn = warnModal.childNodes[1].childNodes[1].childNodes[5].childNodes[1];
const confirmModalMsg = buildModal;
const confirmModal = document.querySelector('#confirm-modal');
const confirmModalOptBtn = document.querySelector('#confirm-modal-confirm');
const unconfirmModalOptBtn = document.querySelector('#confirm-modal-cancel');
const signBtnContainer = signBox.childNodes[3];
let pendingWarningFor = null;
let signatureWarningTimer = null;
let signature;
let secondSignature;
let currentSignatureAssignmentControl = '';

function restoreSignatureState(assignmentControl) {
    const preSignature = localStorage.getItem(getSignatureStorageKey('pre', assignmentControl));
    const postSignature = localStorage.getItem(getSignatureStorageKey('post', assignmentControl));

    if (!preSignature) {
        openSignBoxBtn?.classList.remove('d-none');
        getPostSignatureBtn?.classList.add('d-none');
        closeSignPadBtn?.classList.add('d-none');
        return;
    }

    openSignBoxBtn?.classList.add('d-none');

    if (!postSignature) {
        getPostSignatureBtn?.classList.remove('d-none');
        closeSignPadBtn?.classList.add('d-none');
        preInspSign?.classList.remove('d-none');
        return;
    }

    preInspSign?.classList.remove('d-none');
    postInspSign?.classList.remove('d-none');

    finalizeSignatureInterface();
};

function finalizeSignatureInterface() {
    signpad?.classList.add('d-none');
    signpad?.nextElementSibling?.classList.add('d-none');

    signBtn?.classList.add('d-none');
    secondSignBtn?.classList.add('d-none');
    signBtnContainer?.classList.add('d-none');

    getPostSignatureBtn?.classList.add('d-none');
    closeSignPadBtn?.classList.add('d-none');

    signBox?.classList.add('d-none');

    openSignBoxBtn?.classList.remove('d-none');
    $(openSignBoxBtn).prop('disabled', true);
};

function getSignatureStorageKey(type, assignmentControl) {
    return `${type}-signature:${assignmentControl}`;
};

// Show warning modal once per assignment (integrated with MutationObserver)
function showWarnModalForAssignment(assignmentControl, requiresSignature) {
    const assignmentKey = String(assignmentControl ?? '');
    const lastWarned = localStorage.getItem('warnModalShownFor') ?? '';
    console.log({
        assignmentControl, lastWarned: localStorage.getItem('warnModalShownFor'), requiresSignature
    });

    // If no signature is required - hide everything
    if (!requiresSignature || assignmentKey === '') {
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }

        pendingWarningFor = null;
        $(signatureBoxBtn).addClass('d-none');
        signBox?.classList.add('d-none');

        preInspSign?.classList.add('d-none');
        postInspSign?.classList.add('d-none');

        $(openSignBoxBtn).removeClass('d-none');
        $(getPostSignatureBtn).addClass('d-none');
        $(closeSignPadBtn).addClass('d-none');

        return;
    }

    // If signature required but already warned once
    if (assignmentKey === lastWarned) {
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }

        pendingWarningFor = null;
        $(signatureBoxBtn).removeClass('d-none');
        return;
    };

    if (pendingWarningFor === assignmentKey && signatureWarningTimer !== null) {
        return;
    }

    if (signatureWarningTimer !== null) {
        clearTimeout(signatureWarningTimer);
    }
    
    pendingWarningFor = assignmentKey;

    signatureWarningTimer = setTimeout(() => {
        signatureWarningTimer = null;
        const acknowledged = localStorage.getItem('warnModalShownFor') ?? '';

        if (acknowledged === assignmentKey) {
            pendingWarningFor = null;
            $(signatureBoxBtn).removeClass('d-none');
            return;
        }

        $(warnModal).modal('show');
    }, 1500);

    $(warnModal).off('shown.bs.modal.signatureWarning').on('shown.bs.modal.signatureWarning', function () {
        warnModalMsg.warning("You're required to have a rep from the client sign both for pre & post trip inspections. Please turn device on side for better signature capture.", "Understood & agree");
    });

    $(warnModalBtn).off('click.signatureWarning').on('click.signatureWarning', function () {
        localStorage.setItem('warnModalShownFor', assignmentKey);
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }
        pendingWarningFor = null;
        $(signatureBoxBtn).removeClass('d-none');
        $(warnModal).modal('hide');
    });
};

// Listen for the event from jobhandler.js
window.addEventListener('assignmentChanged', (e) => {
    const {
        assignmentControl,
        requiresSignature
    } = e.detail ?? {};

    currentSignatureAssignmentControl = String(assignmentControl ?? '');
    showWarnModalForAssignment(currentSignatureAssignmentControl, Boolean(requiresSignature));
    if (requiresSignature) {
        restoreSignatureState(currentSignatureAssignmentControl);
    }
});

// On confirm modal btn, handle new recorded signature for post trip.
function confirmPostSignHandler () {
    imgInspBox.classList.remove('d-none');
    signBtn.classList.add('d-none');
    secondSignBtn.classList.remove('d-none');

    $(secondSignBtn).off('click.postSignature').on('click.postSignature', () => {
        if (!currentSignatureAssignmentControl) {
            console.error('[SIGNATURE] No active assignment control is available.');
            return;
        }
        secondSignature = $(signpad).jSignature("getData");
        const postSignatureKey = getSignatureStorageKey('post', currentSignatureAssignmentControl);
        localStorage.setItem(postSignatureKey, secondSignature);
        $(signatureCheck).append(`<img src='${secondSignature}' alt='Post-trip signature'>`);
        postInspSign.classList.remove('d-none');
        const holder = document.createElement('div');
        postInspSign.firstChild.after(holder);
        $(holder).append(`<img src='${secondSignature}' alt='Post-trip signature'>`);
        setTimeout(() => {
            imgInspBox.classList.add('d-none');
            $(signpad).jSignature('clear');
        }, 500);
        signpad.classList.add('d-none');
        signBtn.classList.add('d-none');
        secondSignBtn.classList.add('d-none');
        signBtnContainer.classList.add('d-none');

        getPostSignatureBtn.classList.add('d-none');
        closeSignPadBtn.classList.remove('d-none');
    });
};

// On unconfirm modal btn, handle signature already recorded for post trip.
function unConfirmPostSignHandler() {
    if (!currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment control is available.');
        return;
    }

    const preSignatureKey = getSignatureStorageKey('pre', currentSignatureAssignmentControl);
    const postSignatureKey = getSignatureStorageKey('post', currentSignatureAssignmentControl);
    const preSignature = localStorage.getItem(preSignatureKey);
    if (!preSignature) {
        console.error('[SIGNATURE] No pre-trip signature is available to reuse.');
        return;
    }

    signBtn.classList.add('d-none');
    postInspSign.classList.remove('d-none');

    const holder = document.createElement('div');
    postInspSign.firstChild.after(holder);
    $(holder).append(`<img src="${preSignature}" alt="Post-trip signature">`);
    localStorage.setItem(postSignatureKey, preSignature);

    signBtn.classList.remove('d-none');
    secondSignBtn.classList.add('d-none');
    signBtnContainer.classList.add('d-none');
};

// Open signature widget.
$(openSignBoxBtn).on('click', () => {
    signBox.classList.remove('d-none');
});   

// show confirm dialog modal for signature handlers.
getPostSignatureBtn.addEventListener('click', () => {
    buildModal.confirm('You already have a signature on file. Would you like to add a different signature?', 'Yes', 'No');

    const modalInstance = bootstrap.Modal.getOrCreateInstance(confirmModal);

    // Remove handlers from previous openings
    $(confirmModalOptBtn).off('click.signature');
    $(unconfirmModalOptBtn).off('click.signature');

    // Yes: capture a different post-trip signature
    $(confirmModalOptBtn).on('click.signature', () => {
        modalInstance.hide();
        signBox.classList.remove('d-none');
        imgInspBox.classList.remove('d-none');
        signpad.classList.remove('d-none');
        signBtnContainer.classList.remove('d-none');

        signBtn.classList.add('d-none');
        secondSignBtn.classList.remove('d-none');

        //setTimeout(() => {
            getPostSignatureBtn.classList.add('d-none');
            closeSignPadBtn.classList.add('d-none');
        //}, 1000);

        $(signpad).jSignature('clear');
        confirmPostSignHandler();
        
    });

    // No: reuse the pre-trip signature
    $(unconfirmModalOptBtn).on('click.signature', () => {
        modalInstance.hide();

        signBtnContainer.classList.remove('d-none');
        unConfirmPostSignHandler();
        setTimeout(() => {
            getPostSignatureBtn.classList.add('d-none');
            closeSignPadBtn.classList.remove('d-none');
        }, 1000);
    });

    modalInstance.show();
});

// Close sign pad and rendered preview of signature. Also disable open button so no longer can be used.
closeSignPadBtn.addEventListener('click', () => {
    finalizeSignatureInterface();
    console.log({
        assignmentControl: currentSignatureAssignmentControl, 
        preSignature: localStorage.getItem(getSignatureStorageKey('pre', currentSignatureAssignmentControl)),
        postSignature: localStorage.getItem(getSignatureStorageKey('post', currentSignatureAssignmentControl))
    });
});

// Instantiate jSignature and set up for capture.
$(document).ready(function () {
    $(signpad).jSignature({
        'background-color': '#FFFAF0',
        height: '250'
    });
});

// Clear the signature pad.
$(clearBtn).on('click', () => {
    $(signpad).jSignature('clear');
});

// When widget 1st opens, handle 1st signature capture and set rest of buttons and rendered preview.
$(signBtn).on('click', () => {
    if (!currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment control is available.');
        return;
    }

    signature = $(signpad).jSignature('getData');
    const preSignatureKey = getSignatureStorageKey('pre', currentSignatureAssignmentControl);
    localStorage.setItem(preSignatureKey, signature);
    $(signatureCheck).append(`<img src="${signature}" alt="Pre-trip signature">`);

    preInspSign.classList.remove('d-none');
    const holder = document.createElement('div');
    preInspSign.firstChild.after(holder);
    $(holder).append(`<img src="${signature}" alt="Pre-trip signature">`);

    setTimeout(() => {
        imgInspBox.classList.add('d-none');
        $(signpad).jSignature('clear');
    }, 500);

    secondSignBtn.classList.remove('d-none');
    signpad.classList.add('d-none');
    signBtn.classList.add('d-none');
    signBtnContainer.classList.add('d-none');

    setTimeout(() => {
        openSignBoxBtn.classList.add('d-none');
        getPostSignatureBtn.classList.remove('d-none');
    }, 1000);
});