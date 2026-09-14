import { buildModal } from './appmodal.js';
import 'jSignature';
import { isEmergencyActive, syncEmergencyState } from './emergency-state.js';
import { showFlashAlert, fetchDrvr } from './helpers.js';

const signatureBoxBtn = document.querySelector('#signature-widget-buttons')
const openSignBoxBtn = document.querySelector('#open-sign-box');
const getPostSignatureBtn = document.querySelector('#get-next-signature');
const closeSignPadBtn = document.querySelector('#close-sign-pad');
const signBox = document.querySelector('#signaturecon');
const signpad = document.querySelector('#signaturePad');
const clearBtn = signpad.parentNode.childNodes[3].childNodes[1];
const signBtn = signpad.parentNode.childNodes[3].childNodes[3];
const secondSignBtn = signBtn.nextElementSibling;
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
let currentSignatureOrderId = '';
let currentSignatureAssignmentControl = '';

async function blockIfEmergencyActive() {
    const emergencyState = await syncEmergencyState();
    if (emergencyState === true) {
        showFlashAlert('error', 'Signature actions are unavailable while an Emergency is active.');
        return true;
    }

    if (emergencyState === null) {
        showFlashAlert('error', 'Emergency status could not be verified. Signature actions are temporarily unavailable.');
        return true;
    }

    return false;
}

function restoreSignatureState(assignmentControl) {
    const preSignature = localStorage.getItem(getSignatureStorageKey('pre', assignmentControl));
    const postSignature = localStorage.getItem(getSignatureStorageKey('post', assignmentControl));

    if (!preSignature) {
        openSignBoxBtn?.classList.remove('d-none');
        getPostSignatureBtn?.classList.add('d-none');
        closeSignPadBtn?.classList.add('d-none');
        imgInspBox?.classList.add('d-none');

        return;
    }

    openSignBoxBtn?.classList.add('d-none');

    // Show preview area again after page reload.
    imgInspBox?.classList.remove('d-none');

    preInspSign?.classList.remove('d-none');

    renderSignaturePreview(preInspSign, preSignature, 'Pre-trip signature');

    if (!postSignature) {
        getPostSignatureBtn?.classList.remove('d-none');
        closeSignPadBtn?.classList.add('d-none');

        return;
    }

    postInspSign?.classList.remove('d-none');

    renderSignaturePreview(postInspSign, postSignature, 'Post-trip signature');

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

    imgInspBox?.classList.add('d-none');

    signBox?.classList.add('d-none');

    openSignBoxBtn?.classList.remove('d-none');
    $(openSignBoxBtn).prop('disabled', true);
};

function getSignatureWarningKey(assignmentControl) {
    return `signature-warning-ack:${assignmentControl}`;
}

function getSignatureStorageKey(type, assignmentControl) {
    return `${type}-signature:${assignmentControl}`;
};

function renderSignaturePreview(container, signatureData, altText) {
    if (!container || !signatureData) {
        return;
    }

    let previewHolder = container.querySelector('.signature-preview');

    if (!previewHolder) {
        previewHolder = document.createElement('div');
        previewHolder.classList.add('signature-preview');

        container.appendChild(previewHolder);
    }

    previewHolder.replaceChildren();

    const image = document.createElement('img');
    image.src = signatureData;
    image.alt = altText;

    previewHolder.appendChild(image);
};

async function createSignatureRequest(signatureType) {
    if (!currentSignatureOrderId) {
        console.error('[SIGNATURE REQUEST] No active order ID.');
        return null;
    }

    if (!currentSignatureAssignmentControl) {
        console.error('[SIGNATURE REQUEST] No active assignment control.');
        return null;
    }

    const drvrtokenInput = document.querySelector('input[name="drvrtoken"]');
    if (!drvrtokenInput?.value) {
        console.error('[SIGNATURE REQUEST] CSRF token unavailable.');
        return null;
    }

    try {
        const data = await fetchDrvr('/signature-request', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': drvrtokenInput.value
            },

            body: JSON.stringify({
                order_id: currentSignatureOrderId,
                assignment_control: currentSignatureAssignmentControl,
                signature_type: signatureType
            })
        });

        return data.signatureData;
    } catch (error) {
        console.error('[SIGNATURE REQUEST ERROR]', error);
        return null;
    }
};

// Show signature-required warning modal once per assignment (integrated with MutationObserver)
function showWarnModalForAssignment(assignmentControl, requiresSignature) {
    const assignmentKey = String(assignmentControl ?? '').trim();

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

    const warningKey = getSignatureWarningKey(assignmentKey);
    const acknowledged = localStorage.getItem(warningKey) === 'true';

    // Already acknowledged for THIS assignment.
    if (acknowledged) {
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }

        pendingWarningFor = null;
        $(signatureBoxBtn).removeClass('d-none');

        return;
    }

    // Already waiting to warn for this assignment.
    if (pendingWarningFor === assignmentKey && signatureWarningTimer !== null) {
        return;
    }

    if (signatureWarningTimer !== null) {
        clearTimeout(signatureWarningTimer);
    }

    pendingWarningFor = assignmentKey;

    signatureWarningTimer = setTimeout(() => {
        signatureWarningTimer = null;
        const alreadyAcknowledged = localStorage.getItem(warningKey) === 'true';

        if (alreadyAcknowledged) {
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
        localStorage.setItem(warningKey, 'true');

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
        orderId,
        assignmentControl,
        requiresSignature
    } = e.detail ?? {};

    currentSignatureOrderId = orderId ?? '';
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

    $(secondSignBtn).off('click.postSignature').on('click.postSignature', async () => {
        if (await blockIfEmergencyActive()) {
            return;
        }

        if (!currentSignatureAssignmentControl) {
            console.error('[SIGNATURE] No active assignment control is available.');
            return;
        }

        secondSignature = $(signpad).jSignature("getData");
        const postSignatureKey = getSignatureStorageKey('post', currentSignatureAssignmentControl);
        localStorage.setItem(postSignatureKey, secondSignature);

        postInspSign.classList.remove('d-none');

        renderSignaturePreview(postInspSign, secondSignature, 'Post-trip signature');
        setTimeout(() => {
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
async function unConfirmPostSignHandler() {
    if (await blockIfEmergencyActive()) {
        return false;
    }

    if (!currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment control is available.');
        return false;
    }

    const preSignatureKey = getSignatureStorageKey('pre', currentSignatureAssignmentControl);
    const postSignatureKey = getSignatureStorageKey('post', currentSignatureAssignmentControl);
    const preSignature = localStorage.getItem(preSignatureKey);
    if (!preSignature) {
        console.error('[SIGNATURE] No pre-trip signature is available to reuse.');
        return false;
    }

    // Show the complete preview area.
    signBox.classList.remove('d-none');
    imgInspBox.classList.remove('d-none');

    // We are previewing, NOT capturing another signature.
    signpad.classList.add('d-none');
    signpad.nextElementSibling?.classList.add('d-none');

    signBtn.classList.add('d-none');
    secondSignBtn.classList.add('d-none');
    signBtnContainer.classList.add('d-none');

    // Make sure both signatures previews remain visible
    preInspSign.classList.remove('d-none');
    postInspSign.classList.remove('d-none');

    renderSignaturePreview(preInspSign, preSignature, 'Pre-trip signature');
    renderSignaturePreview(postInspSign, preSignature, 'Post-trip signature');
    localStorage.setItem(postSignatureKey, preSignature);

    return true;
};

// Open signature widget.
$(openSignBoxBtn).on('click', async () => {
    if (await blockIfEmergencyActive()) {
        return;
    }

    const signatureData = await createSignatureRequest('pre');

    if (!signatureData) {
        return;
    }

    console.log('[SIGNATURE DATA]', JSON.stringify(signatureData, null, 2));
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
    $(unconfirmModalOptBtn).on('click.signature', async () => {
        modalInstance.hide();

        // Reopen the signature interface so the previews are visible.
        signBox.classList.remove('d-none');
        imgInspBox.classList.remove('d-none');

        const postSignatureSaved = await unConfirmPostSignHandler();
        if (!postSignatureSaved) {
            return;
        }

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
$(signBtn).on('click', async () => {
    if (await blockIfEmergencyActive()) {
        return;
    }

    if (!currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment control is available.');
        return;
    }

    signature = $(signpad).jSignature('getData');
    const preSignatureKey = getSignatureStorageKey('pre', currentSignatureAssignmentControl);
    localStorage.setItem(preSignatureKey, signature);

    preInspSign.classList.remove('d-none');
    renderSignaturePreview(preInspSign, signature, 'Pre-trip signature');

    setTimeout(() => {
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