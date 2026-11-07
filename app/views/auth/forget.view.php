<?php
    require base_path('app/views/partials/auth/outhead.php');
    $alert = new Core\Flash();
    include base_path('app/views/partials/shared/flashmessage.php');
?>
    <main class="w-50 my-5 align-self-center">
        <h1 class="text-center text-light text-bg-dark font-monospace">
            <u>Reset your password here!</u>
        </h1>

        <p class="h5 text-center text-light text-bg-dark">
            You will receive an email with a link to reset your password.
        </p>

        <form id="forgetpswd" action="/forget" method="POST" class="needs-validation" novalidate>
            <div class="form-floating row mb-2">
                <input id="email" type="email" class="form-control" name="email" placeholder="Please enter your email" required>
                <label for="email">Email address</label>

                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= htmlspecialchars((string) ($_SESSION['drvr_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="d-flex flex-column justify-content-center mb-3">
                <button id="forget-pwd" type="submit" class="btn btn-primary my-2 p-2 fs-5" name="forget-pswd">
                    Send email
                </button>

                <a href="/signin" class="btn btn-lg btn-primary my-2" role="button">
                    Go back
                </a>
            </div>
        </form>
    </main> 
<?php
    require base_path('app/views/partials/auth/outfooter.php');
?>