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
const confirmModalMsg = buildModal;
const confirmModal = document.querySelector('#confirm-modal');
const warnModal = document.querySelector('#warn-modal');
const warnModalBtn = warnModal.childNodes[1].childNodes[1].childNodes[5].childNodes[1];
const confirmModalOptBtn = document.querySelector('#confirm');
const unconfirmModalOptBtn = document.querySelector('#unconfirm');
const signBtnContainer = signBox.childNodes[3];
//const completedInspBox = document.querySelector('#inspect-signature-box');
let signature;
let secondSignature;

// 🧩 Show warning modal once per assignment (integrated with MutationObserver)
function showWarnModalForAssignment(orderId) {
    const lastWarnedId = localStorage.getItem('warnModalShownFor');

    if (orderId && orderId !== lastWarnedId) {
        // Delay slightly so UI is ready
        setTimeout(() => {
            $(warnModal).modal('show');
        }, 2500);

        warnModal.addEventListener('shown.bs.modal', () => {
            warnModalMsg.warning('You\'re required to have a rep from the client sign both for pre & post trip inspections. Please when signing, turn device on side for better signature capture.', 'Understood & agree');
        }, { once: true });

        $(warnModalBtn).one('click', () => {
            $(signatureBoxBtn).removeClass('d-none');
            $(warnModal).modal('toggle');
            localStorage.setItem('warnModalShownFor', orderId);
        });
    } else {
        $(signatureBoxBtn).removeClass('d-none');
    }
}

// 🔔 Listen for the event from jobhandler.js
window.addEventListener('assignmentChanged', (e) => {
    const { orderId } = e.detail;
    showWarnModalForAssignment(orderId);
});

// On confirm modal btn, handle new recorded signature for post trip.
function confirmPostSignHandler () {
    localStorage.removeItem("insp-signature");
    imgInspBox.classList.remove('d-none');
    signBtn.classList.add('d-none');
    $(secondSignBtn).on('click', () => {
        secondSignature = $(signpad).jSignature("getData");
        localStorage.setItem('secondInsp-signature', secondSignature);
        $(signatureCheck).append(`<img src='${secondSignature}'></img>`);
        postInspSign.classList.remove('d-none');
        let div = document.createElement('div');
        let newPostTripSignatureHolder = div;
        postInspSign.firstChild.after(newPostTripSignatureHolder);
        $(newPostTripSignatureHolder).append(`<img src='${localStorage.getItem("secondInsp-signature")}'></img>`)
        setTimeout(() => {
            imgInspBox.classList.add('d-none');
            $(signpad).jSignature('clear');
            localStorage.removeItem('secondInsp-signature');
        }, 500);
        signBtn.classList.remove('d-none');
        secondSignBtn.classList.add('d-none');
        signBtnContainer.classList.add('d-none');
    });
};

// On unconfirm modal btn, handle signature already recorded for post trip.
function unConfirmPostSignHandler () {
    signBtn.classList.add('d-none');
    postInspSign.classList.remove('d-none');
    let div = document.createElement('div');
    let newPostTripSignatureHolder = div;
    postInspSign.firstChild.after(newPostTripSignatureHolder);
    $(newPostTripSignatureHolder).append(`<img src='${localStorage.getItem("insp-signature")}'></img>`);
    localStorage.removeItem('insp-signature');
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
    $(confirmModal).modal('toggle'); 
    confirmModal.addEventListener('shown.bs.modal', () => {
        confirmModalMsg.confirm('You already have a signature on file. Would you like to add a different signature?', 'Yes', 'No');
    });

    // confirm button on modal to record new signature.
    $(confirmModalOptBtn).on('click', () => {
        $(confirmModal).modal('hide');
        signpad.classList.remove('d-none');
        signBtnContainer.classList.remove('d-none');
        confirmPostSignHandler();
        setTimeout(() => {
            getPostSignatureBtn.classList.add('d-none');
            closeSignPadBtn.classList.remove('d-none');
        }, 1000);
    });

    // unconfirm button on modal to reuse recorded signature. 
    $(unconfirmModalOptBtn).on('click', () => {
        $(confirmModal).modal('hide');
        signBtnContainer.classList.remove('d-none');
        unConfirmPostSignHandler();
        setTimeout(() => {
            getPostSignatureBtn.classList.add('d-none');
            closeSignPadBtn.classList.remove('d-none');
        }, 1000);
    });
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
    localStorage.setItem('insp-signature', signature);
    $(signatureCheck).append(`<img src='${signature}'></img>`);
    preInspSign.classList.remove('d-none');
    let div = document.createElement('div');
    let newPreTripSignatureHolder = div;
    preInspSign.firstChild.after(newPreTripSignatureHolder);
    $(newPreTripSignatureHolder).append(`<img src='${localStorage.getItem("insp-signature")}'></img>`);
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