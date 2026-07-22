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
//const completedInspBox = document.querySelector('#inspect-signature-box');
let signature;
let secondSignature;

// Show warning modal once per assignment (integrated with MutationObserver)
function showWarnModalForAssignment(orderId, requiresSignature) {
    const lastWarnedId = localStorage.getItem('warnModalShownFor');

    // If no signature is required - hide it!
    if (!requiresSignature) {
        $(signatureBoxBtn).addClass('d-none');
        return;
    }

    // If signature required but already warned once
    if (orderId === lastWarnedId) {
        $(signatureBoxBtn).removeClass('d-none');
        return;
    };
    
    // Otherwise, show modal once
    setTimeout(() => {
        $(warnModal).modal('show');
    }, 1500);

    $(warnModal).off('shown.bs.modal').on('shown.bs.modal', function () {
        warnModalMsg.warning("You're required to have a rep from the client sign both for pre & post trip inspections. Please turn device on side for better signature capture.", "Understood & agree");
    });

    $(warnModalBtn).off('click').on('click', function () {
        $(signatureBoxBtn).removeClass('d-none');
        $(warnModal).modal('hide');
        localStorage.setItem('warnModalShownFor', orderId);
    });
}

// Listen for the event from jobhandler.js
window.addEventListener('assignmentChanged', (e) => {
    const { orderId, requiresSignature } = e.detail;    
    showWarnModalForAssignment(orderId, requiresSignature);
});

// On confirm modal btn, handle new recorded signature for post trip.
function confirmPostSignHandler () {
    imgInspBox.classList.remove('d-none');
    signBtn.classList.add('d-none');
    $(secondSignBtn).off('click.postSignature').on('click.postSignature', () => {
        secondSignature = $(signpad).jSignature("getData");
        localStorage.setItem('post-signature', secondSignature);
        $(signatureCheck).append(`<img src='${secondSignature}' alt='Post-trip signature'></img>`);
        postInspSign.classList.remove('d-none');
        const newPostTripSignatureHolder = document.createElement('div');
        postInspSign.firstChild.after(newPostTripSignatureHolder);
        $(newPostTripSignatureHolder).append(`<img src='${localStorage.getItem("post-signature")}' alt='Post-trip signature'></img>`);
        setTimeout(() => {
            imgInspBox.classList.add('d-none');
            $(signpad).jSignature('clear');
        }, 500);
        signBtn.classList.remove('d-none');
        secondSignBtn.classList.add('d-none');
        signBtnContainer.classList.add('d-none');
    });
};

// On unconfirm modal btn, handle signature already recorded for post trip.
function unConfirmPostSignHandler () {
    const preSignature = localStorage.getItem('pre-signature');
    if (!preSignature) {
        console.error('[SIGNATURE] No pre-trip signature is available to reuse.');
        return;
    }
    signBtn.classList.add('d-none');
    postInspSign.classList.remove('d-none');
    const newPostTripSignatureHolder = document.createElement('div');
    postInspSign.firstChild.after(newPostTripSignatureHolder);
    $(newPostTripSignatureHolder).append(`<img src='${localStorage.getItem("pre-signature")}' alt='Post-trip signature'></img>`);
    localStorage.setItem('post-signature', preSignature);
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
        signpad.classList.remove('d-none');
        signBtnContainer.classList.remove('d-none');

        confirmPostSignHandler();
        setTimeout(() => {
            getPostSignatureBtn.classList.add('d-none');
            closeSignPadBtn.classList.remove('d-none');
        }, 1000);
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
    signpad.classList.add('d-none');
    signpad.nextElementSibling.classList.add('d-none');
    setTimeout(() => {
        closeSignPadBtn.classList.add('d-none');
        openSignBoxBtn.classList.remove('d-none');
    });
    $(openSignBoxBtn).attr('disabled', true);
    console.log({preSignature: localStorage.getItem('pre-signature'), postSignature: localStorage.getItem('post-signature')});
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
    signature = $(signpad).jSignature("getData");
    localStorage.setItem('pre-signature', signature);
    $(signatureCheck).append(`<img src='${signature}'></img>`);
    preInspSign.classList.remove('d-none');
    let div = document.createElement('div');
    let newPreTripSignatureHolder = div;
    preInspSign.firstChild.after(newPreTripSignatureHolder);
    $(newPreTripSignatureHolder).append(`<img src='${localStorage.getItem("pre-signature")}'></img>`);
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