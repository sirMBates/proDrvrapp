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
    <section id="signaturecon" class="container-fluid d-flex flex-column align-items-center d-none overflow-x-auto">
        <div id="signaturePad"></div>
        <div class="container d-inline-flex justify-content-center">
            <button type="button" class="btn btn-md btn-secondary m-2">Clear</button>
            <button type="button" class="btn btn-md btn-primary m-2">Sign</button>
            <button type="button" class="btn btn-md btn-primary m-2 d-none">Sign</button>
        </div>
        <div id="insp_img_box">
            <div id="rendered" class="my-3"></div>
        </div>
        <div id="inspect-signature-box">
            <div id="pre-trip" class="w-100 bg-btd-white-floral border border-2 rounded-1 border-dark d-none"><p class='h5 text-center text-capitalize text-nowrap text-btd-blue-bright text-decoration-underline'>pre-trip signature</p></div>
            <div id="post-trip" class="w-100 bg-btd-white-floral border border-2 rounded-1 border-dark d-none"><p class='h5 text-center text-capitalize text-nowrap text-btd-blue-bright text-decoration-underline'>post-trip signature</p></div>
        </div>
    </section>
