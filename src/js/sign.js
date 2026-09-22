import { buildModal } from './appmodal.js';
import 'jSignature';
import QRCode from 'qrcode';
import { syncEmergencyState } from './emergency-state.js';
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
const signatureDisplay = document.querySelector('#signature-display');
const preSignatureDisplay = document.querySelector('#pre-signature-display');
const postSignatureDisplay = document.querySelector('#post-signature-display');
const preSignatureImage = document.querySelector('#pre-signature-image');
const postSignatureImage = document.querySelector('#post-signature-image');
const warnModalMsg = buildModal;
const warnModal = document.querySelector('#warn-modal');
const warnModalBtn = warnModal.childNodes[1].childNodes[1].childNodes[5].childNodes[1];
const confirmModal = document.querySelector('#confirm-modal');
const confirmModalOptBtn = document.querySelector('#confirm-modal-confirm');
const unconfirmModalOptBtn = document.querySelector('#confirm-modal-cancel');
const signBtnContainer = signBox.childNodes[3];
const signatureQrContainer = document.querySelector('#signature-qr-container');
const signatureQr = document.querySelector('#signature-qr');
let pendingWarningFor = null;
let signatureWarningTimer = null;
let signature;
let currentSignatureOrderId = '';
let currentSignatureAssignmentControl = '';
let currentLocalSignatureType = null;
let signatureStatusPollTimer = null;

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
};

function restoreSignatureState(signatureStatus) {
    $(openSignBoxBtn).prop('disabled', false);
    signatureDisplay?.classList.add('d-none');
    preSignatureDisplay?.classList.add('d-none');
    postSignatureDisplay?.classList.add('d-none');

    if (signatureStatus === 'pending') {
        openSignBoxBtn?.classList.remove('d-none');
        getPostSignatureBtn?.classList.add('d-none');
        closeSignPadBtn?.classList.add('d-none');

        return;
    }

    openSignBoxBtn?.classList.add('d-none');

    if (signatureStatus === 'pre-trip-complete') {
        showPersistedSignature('pre');

        openSignBoxBtn?.classList.add('d-none');
        getPostSignatureBtn?.classList.remove('d-none');
        closeSignPadBtn?.classList.add('d-none');

        return;
    }

    if (signatureStatus === 'complete') {
        showPersistedSignature('pre');
        showPersistedSignature('post');
        finalizeSignatureInterface();
    }
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

function getSignatureWarningKey(assignmentControl) {
    return `signature-warning-ack:${assignmentControl}`;
};

function getSignatureStorageKey(type, assignmentControl) {
    return `${type}-signature:${assignmentControl}`;
};

function getSignatureImageUrl(signatureType) {
    if (!currentSignatureOrderId || !currentSignatureAssignmentControl) {
        return null;
    }

    const params = new URLSearchParams({
        orderId: currentSignatureOrderId,
        assignmentControl: currentSignatureAssignmentControl,
        type: signatureType
    });

    return `/signature-image?${params.toString()}`;
};

function showPersistedSignature(signatureType) {
    const signatureImage = signatureType === 'pre' ? preSignatureImage : postSignatureImage;
    const signatureContainer = signatureType === 'pre' ? preSignatureDisplay : postSignatureDisplay;
    const imageUrl = getSignatureImageUrl(signatureType);

    if (!signatureImage || !signatureContainer || !imageUrl) {
        return;
    }

    signatureImage.src = imageUrl;

    signatureDisplay?.classList.remove('d-none');
    signatureContainer.classList.remove('d-none');
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

function addLogoToQr(qrDataUrl) {
    return new Promise((resolve, reject) => {
        const qrImage = new Image();
        const logoImage = new Image();

        qrImage.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            canvas.width = qrImage.width;
            canvas.height = qrImage.height;

            // Draw QR code.
            ctx.drawImage(qrImage, 0, 0);

            logoImage.onload = () => {
                const logoSize = canvas.width * 0.25;
                const logoX = (canvas.width - logoSize) / 2;
                const logoY = (canvas.height - logoSize) / 2;
                const padding = canvas.width * 0.025;
                const plateX = logoX - padding;
                const plateY = logoY - padding;
                const plateSize = logoSize + (padding * 2);
                const radius = canvas.width * 0.025;

                ctx.save();

                ctx.fillStyle = '#F6F4F1';

                ctx.beginPath();
                ctx.roundRect(
                    plateX,
                    plateY,
                    plateSize,
                    plateSize,
                    radius
                );

                ctx.fill();

                ctx.restore();

                ctx.drawImage(
                    logoImage,
                    logoX,
                    logoY,
                    logoSize,
                    logoSize
                );

                resolve(canvas.toDataURL('image/png'));
            };

            logoImage.onerror = reject;

            logoImage.src = '/dist/images-videos/logoandicons/prodriverlogo.png';
        };

        qrImage.onerror = reject;
        qrImage.src = qrDataUrl;
    });
};

async function showSignatureQr(signatureType) {
    const signatureData = await createSignatureRequest(signatureType);
    if (!signatureData) {
        return false;
    }

    // QR and local capture are mutually exclusive.
    signBox?.classList.add('d-none');
    signpad?.classList.add('d-none');
    signBtn?.classList.add('d-none');
    signBtnContainer?.classList.add('d-none');

    const signingUrl = new URL(signatureData.signing_path, window.location.origin).href;
    const qrDataUrl = await QRCode.toDataURL(signingUrl, {
        errorCorrectionLevel: 'H',
        margin: 2,
        width: 320,
        color: {
            dark: '#1D5283',
            light: '#F6F4F1'
        }
    });

    const brandedQrDataUrl = await addLogoToQr(qrDataUrl);

    signatureQr.src = brandedQrDataUrl;
    signatureQrContainer.classList.remove('d-none');
    startSignatureStatusPolling(signatureType);

    return true;
};

function openLocalSignaturePad(signatureType) {
    if (!['pre', 'post'].includes(signatureType)) {
        console.error('[SIGNATURE] Invalid local signature type.');
        return;
    }

    currentLocalSignatureType = signatureType;

    // QR and local capture are mutually exclusive.
    signatureQrContainer?.classList.add('d-none');
    signBox?.classList.remove('d-none');

    signpad?.classList.remove('d-none');
    signBtn?.classList.remove('d-none');
    signBtnContainer?.classList.remove('d-none');

    secondSignBtn?.classList.add('d-none');

    setTimeout(() => {
        $(signpad).jSignature('clear');
    }, 100);
};

function stopSignatureStatusPolling() {
    if (signatureStatusPollTimer !== null) {
        clearInterval(signatureStatusPollTimer);
        signatureStatusPollTimer = null;
    }
};

function startSignatureStatusPolling(signatureType) {
    stopSignatureStatusPolling();

    const orderId = currentSignatureOrderId;
    const assignmentControl = currentSignatureAssignmentControl;

    if (!orderId || !assignmentControl || !['pre', 'post'].includes(signatureType)) {
        return;
    }

    signatureStatusPollTimer = setInterval(async () => {
        // The driver switched assignments while polling.
        if (currentSignatureOrderId !== orderId || currentSignatureAssignmentControl !== assignmentControl) {
            stopSignatureStatusPolling();
            return;
        }

        const params = new URLSearchParams({
            orderId,
            assignmentControl
        });

        try {
            const data = await fetchDrvr(`/signature-status?${params.toString()}`);
            if (data.status !== 'success') {
                return;
            }

            const signatureStatus = data.signatureData?.signature_status;

            if (signatureType === 'pre' && signatureStatus === 'pre-trip-complete') {
                stopSignatureStatusPolling();
                signatureQrContainer?.classList.add('d-none');
                showPersistedSignature('pre');

                openSignBoxBtn?.classList.add('d-none');
                getPostSignatureBtn?.classList.remove('d-none');

                return;
            }

            if (signatureType === 'post' && signatureStatus === 'complete') {
                stopSignatureStatusPolling();
                signatureQrContainer?.classList.add('d-none');

                showPersistedSignature('pre');
                showPersistedSignature('post');

                finalizeSignatureInterface();
            }
        } catch (error) {
            console.error('[SIGNATURE STATUS POLL ERROR]', error);
        }
    }, 3000);
};

// Show signature-required warning modal once per assignment (integrated with MutationObserver)
function showWarnModalForAssignment(assignmentControl, requiresSignature, signatureStatus) {
    const assignmentKey = String(assignmentControl ?? '').trim();

    if (signatureStatus === 'pre-trip-complete' || signatureStatus === 'complete') {
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }

        pendingWarningFor = null;
        $(signatureBoxBtn).removeClass('d-none');

        return;
    }

    if (!requiresSignature || assignmentKey === '') {
        if (signatureWarningTimer !== null) {
            clearTimeout(signatureWarningTimer);
            signatureWarningTimer = null;
        }

        pendingWarningFor = null;

        $(signatureBoxBtn).addClass('d-none');
        signBox?.classList.add('d-none');

        signatureDisplay?.classList.add('d-none');
        preSignatureDisplay?.classList.add('d-none');
        postSignatureDisplay?.classList.add('d-none');

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

// Listen for the event from assignment.js
window.addEventListener('assignmentChanged', (e) => {
    const {
        orderId,
        assignmentControl,
        requiresSignature,
        signatureStatus
    } = e.detail ?? {};

    currentSignatureOrderId = orderId ?? '';
    currentSignatureAssignmentControl = String(assignmentControl ?? '');
    showWarnModalForAssignment(currentSignatureAssignmentControl, Boolean(requiresSignature), signatureStatus);
    if (requiresSignature) {
        restoreSignatureState(signatureStatus);
    }
});

// On unconfirm modal btn, handle signature already recorded for post trip.
async function unConfirmPostSignHandler() {
    if (await blockIfEmergencyActive()) {
        return false;
    }

    if (!currentSignatureOrderId || !currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment information is available.');
        return false;
    }

    const drvrtokenInput = document.querySelector('input[name="drvrtoken"]');
    if (!drvrtokenInput?.value) {
        console.error('[SIGNATURE] CSRF token unavailable.');
        return false;
    }

    try {
        const data = await fetchDrvr('/reuse-signature', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': drvrtokenInput.value
            },
            body: JSON.stringify({
                order_id: currentSignatureOrderId,
                assignment_control: currentSignatureAssignmentControl
            })
        });

        if (data.status !== 'success') {
            return false;
        }

        showPersistedSignature('pre');
        showPersistedSignature('post');

        finalizeSignatureInterface();
        return true;
    } catch (error) {
        console.error('[SIGNATURE REUSE ERROR]', error);
        return false;
    }
};

// Open signature widget.
// Choose how to collect the pre-inspection signature.
$(openSignBoxBtn).on('click', async () => {
    if (await blockIfEmergencyActive()) {
        return;
    }

    buildModal.confirm('How would you like to collect the client signature?', 'QR Code', 'This Device');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(confirmModal);

    // Remove handlers from previous openings.
    $(confirmModalOptBtn).off('click.signature');
    $(unconfirmModalOptBtn).off('click.signature');

    // QR Code: client signs from their own device.
    $(confirmModalOptBtn).on('click.signature', async () => {
        modalInstance.hide();
        await showSignatureQr('pre');
    });

    // This Device: client signs on the driver's device.
    $(unconfirmModalOptBtn).on('click.signature', () => {
        modalInstance.hide();
        openLocalSignaturePad('pre');
    });

    modalInstance.show();
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
        // Remove the handlers belonging to the first POST question.
        $(confirmModalOptBtn).off('click.signature');
        $(unconfirmModalOptBtn).off('click.signature');

        // Reconfigure the modal that's already open.
        buildModal.confirm('How would you like to collect the client signature?', 'QR Code', 'This Device');

        $(confirmModalOptBtn).off('click.signatureCapture');
        $(unconfirmModalOptBtn).off('click.signatureCapture');

        $(confirmModalOptBtn).on('click.signatureCapture', async () => {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(confirmModal);
            modalInstance.hide();
            await showSignatureQr('post');
        });

        $(unconfirmModalOptBtn).on('click.signatureCapture', () => {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(confirmModal);
            modalInstance.hide();
            openLocalSignaturePad('post');
        });
    });

    // No: reuse the pre-trip signature
    $(unconfirmModalOptBtn).on('click.signature', async () => {
        modalInstance.hide();

        const postSignatureSaved = await unConfirmPostSignHandler();
        if (!postSignatureSaved) {
            return;
        }
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

    if (!currentSignatureOrderId || !currentSignatureAssignmentControl) {
        console.error('[SIGNATURE] No active assignment information is available.');
        return;
    }

    const signatureData = $(signpad).jSignature('getData');
    if (!signatureData) {
        console.error('[SIGNATURE] No signature data is available.');
        return;
    }

    const drvrtokenInput = document.querySelector('input[name="drvrtoken"]');
    if (!drvrtokenInput?.value) {
        console.error('[SIGNATURE] CSRF token unavailable.');
        return;
    }

    if (!['pre', 'post'].includes(currentLocalSignatureType)) {
        console.error('[SIGNATURE] No valid local signature type is active.');
        return;
    }

    try {
        const data = await fetchDrvr('/driver-signature', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': drvrtokenInput.value
            },
            body: JSON.stringify({
                order_id: currentSignatureOrderId,
                assignment_control: currentSignatureAssignmentControl,
                signature_type: currentLocalSignatureType,
                signature: signatureData
            })
        });

        if (data.status !== 'success') {
            return;
        }

        $(signpad).jSignature('clear');

        signpad.classList.add('d-none');
        signBtn.classList.add('d-none');
        secondSignBtn.classList.add('d-none');
        signBtnContainer.classList.add('d-none');

        if (currentLocalSignatureType === 'pre') {
            showPersistedSignature('pre');

            openSignBoxBtn?.classList.add('d-none');
            getPostSignatureBtn?.classList.remove('d-none');

            currentLocalSignatureType = null;

            return;
        }

        if (currentLocalSignatureType === 'post') {
            showPersistedSignature('pre');
            showPersistedSignature('post');

            currentLocalSignatureType = null;

            finalizeSignatureInterface();

            return;
        }
    } catch (error) {
        console.error('[DRIVER PRE SIGNATURE ERROR]', error);
    }
});