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
const customerSignatureError = document.getElementById('customer-signature-error');
const signatureToken = new URLSearchParams(window.location.search).get('token');
const signatureSuccess = document.querySelector('#signature-success');

function initializeCustomerSignaturePad() {
    if (!customerSignaturePad) {
        return;
    }

    $(customerSignaturePad).jSignature({
        width: '100%',
        height: '160px'
    });
};

clearCustomerSignatureBtn?.addEventListener('click', () => {
    $(customerSignaturePad).jSignature('clear');
    customerSignatureError?.classList.add('d-none');
});

submitCustomerSignatureBtn?.addEventListener('click', async () => {
    const signatureData = $(customerSignaturePad).jSignature('getData', 'native');
    if (signatureData.length === 0) {
        customerSignatureError?.classList.remove('d-none');
        return;
    }

    customerSignatureError?.classList.add('d-none');

    const [signatureMime, signatureBase64] = $(customerSignaturePad).jSignature('getData', 'image');
    const signatureDataUrl = `data:${signatureMime},${signatureBase64}`;

    try {
        const response = await fetch('/signature', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                token: signatureToken,
                signature: signatureDataUrl
            })
        });

        const data = await response.json();
        console.log('[SIGNATURE SUBMIT]', data);

        if (response.ok && data.status === 'success') {
            validSection?.classList.add('d-none');
            signatureSuccess?.classList.remove('d-none');

            return;
        }
    } catch (error) {
        console.error('[SIGNATURE SUBMIT ERROR]', error);
    }
});

function updateSignatureOrientationHint() {
    if (!signatureOrientationHint) {
        return;
    }

    const narrowPortrait = window.innerWidth < 600 && window.matchMedia('(orientation: portrait)').matches;

    signatureOrientationHint.classList.toggle('d-none', !narrowPortrait);
};

async function validateSignatureAccess() {
    if (!signatureToken) {
        showInvalidRequest('Signature token is missing.');
        return;
    }

    try {
        const response = await fetch(`/signature-access?token=${encodeURIComponent(signatureToken)}`);
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