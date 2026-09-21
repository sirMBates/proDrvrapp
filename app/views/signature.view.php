<?php
require "partials/head.php";
?>

<main class="container py-4">
    <section id="signature-validating">
        <h1>Customer Signature</h1>
        <p>Validating signature request...</p>
    </section>

    <section id="signature-valid" class="d-none">
        <header class="mb-4">
            <h1 id="signature-title">Customer Signature</h1>

            <p class="text-muted">
                Please review the inspection information below before signing.
            </p>
        </header>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5">Signature Request Verified</h2>

                <p class="mb-0">
                    This signature request has been securely verified.
                    Your signature will confirm the inspection shown above.
                </p>
            </div>
        </div>

        <div id="customer-signature-area">
            <div class="mb-3">
                <label class="form-label fw-semibold">Customer Signature</label>
                <div id="signature-orientation-hint" class="alert alert-info d-none" role="status">For a larger signing area, turn your device sideways.</div>
                <div id="customer-signature-pad" class="border rounded bg-white"></div>
                <div id="customer-signature-error" class="text-danger small mt-2 d-none" role="alert">
                    Please provide your signature before submitting.
                </div>
            </div>

            <div class="d-flex gap-2">
                <button id="clear-customer-signature" type="button" class="btn btn-outline-secondary">Clear</button>
                <button id="submit-customer-signature" type="button" class="btn btn-primary">Submit Signature</button>
            </div>
        </div>
    </section>

    <section id="signature-success" class="d-none text-center py-4" role="status">
        <h1 class="h3">Signature Submitted!</h1>

        <p class="mb-0">
            Your signature was submitted successfully.
            You can now close this window.
        </p>
    </section>

    <section id="signature-invalid" class="d-none">
        <h1>Signature Unavailable</h1>
        <p id="signature-error-message">
            This signature request is invalid or no longer available.
        </p>
    </section>
</main>

<?php
require "partials/footer.php";
?>