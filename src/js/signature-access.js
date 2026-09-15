import 'jSignature';

const validatingSection = document.getElementById('signature-validating');
const validSection = document.getElementById('signature-valid');
const invalidSection = document.getElementById('signature-invalid');
const signatureTitle = document.getElementById('signature-title');
const errorMessage = document.getElementById('signature-error-message');
const signatureOrientationHint = document.getElementById('signature-orientation-hint');
const customerSignaturePad = document.getElementById('customer-signature-pad');
const clearCustomerSignatureBtn = document.getElementById('clear-customer-signature');
const submitCustomerSignatureBtn = document.getElementById('submit-customer-signature');

function initializeCustomerSignaturePad() {
    if (!customerSignaturePad) {
        return;
    }

    $(customerSignaturePad).jSignature({
        width: '100%',
        height: '160px'
    });
};

function updateSignatureOrientationHint() {
    if (!signatureOrientationHint) {
        return;
    }

    const narrowPortrait = window.innerWidth < 600 && window.matchMedia('(orientation: portrait)').matches;

    signatureOrientationHint.classList.toggle('d-none', !narrowPortrait);
};

async function validateSignatureAccess() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (!token) {
        showInvalidRequest('Signature token is missing.');
        return;
    }

    try {
        const response = await fetch(`/signature-access?token=${encodeURIComponent(token)}`);
        const data = await response.json();

        console.log('[SIGNATURE ACCESS]', data);

        if (!response.ok) {
            showInvalidRequest(data.message ?? 'This signature request is no longer available.');
            return;
        }

        showValidRequest(data.signatureData);
    } catch (error) {
        console.error('[SIGNATURE ACCESS ERROR]', error);
        showInvalidRequest('The signature request could not be verified.');
    }
};

function showValidRequest(signatureData) {
    validatingSection.classList.add('d-none');
    invalidSection.classList.add('d-none');

    const signatureType = signatureData.signature_type === 'pre' ? 'Pre-Trip' : 'Post-Trip';

    signatureTitle.textContent = `${signatureType} Inspection Signature`;

    validSection.classList.remove('d-none');

    updateSignatureOrientationHint();
    initializeCustomerSignaturePad();
};


function showInvalidRequest(message) {
    validatingSection.classList.add('d-none');
    validSection.classList.add('d-none');

    errorMessage.textContent = message;

    invalidSection.classList.remove('d-none');
};

validateSignatureAccess();