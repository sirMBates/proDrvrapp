<?php
    // Only required for signature widget.
    include_once "warn-modal.php";
    include_once "confirm-modal.php";
?>
<div id="signature-widget-buttons" class="container d-inline-flex my-3 d-none">
    <button id="open-sign-box" class="btn btn-primary text-capitalize" type="button"><i class="px-2 fa-solid fa-signature"></i>pre-inspection signature</button>
    <button id="get-next-signature" class="btn btn-primary text-capitalize d-none" type="button"><i class="px-2 fa-solid fa-signature"></i>post-inspection signature</button>
    <button id="close-sign-pad" class="btn btn-primary text-capitalize d-none" type="button"><i class="px-2 fa-solid fa-signature"></i>complete signatures</button>
</div>

<section id="signature-qr-container" class="container d-flex justify-content-center d-none">
    <img id="signature-qr" alt="Scan QR code to provide customer signature">
</section>

<section id="signaturecon" class="container-fluid d-flex flex-column align-items-center d-none overflow-x-auto">
    <div id="signaturePad"></div>
    <div class="container d-inline-flex justify-content-center">
        <button type="button" class="btn btn-md btn-secondary m-2">Clear</button>
        <button type="button" class="btn btn-md btn-primary m-2">Sign</button>
        <button type="button" class="btn btn-md btn-primary m-2 d-none">Sign</button>
    </div>    
</section>

<section id="signature-display" class="container my-3 d-none" aria-live="polite">
    <div id="pre-signature-display" class="signature-document d-none">
        <div class="signature-line">
            <span class="signature-x">X</span>

            <img id="pre-signature-image" class="signature-image" alt="Pre-inspection customer signature">
        </div>

        <p class="signature-label">
            Pre-Inspection Signature
        </p>
    </div>

    <div id="post-signature-display" class="signature-document d-none">
        <div class="signature-line">
            <span class="signature-x">X</span>

            <img id="post-signature-image" class="signature-image" alt="Post-inspection customer signature">
        </div>

        <p class="signature-label">
            Post-Inspection Signature
        </p>
    </div>
</section>
