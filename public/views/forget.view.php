<?php
    require "partials/outhead.php";
    $alert = new \core\Flash;
    include "includes/flashmessage.php";
?>
    <main class="w-50 my-5 align-self-center">
        <h1 class="text-center text-light text-bg-dark font-monospace"><u>Reset your password here!</u></h1>
        <p class="h5 text-center text-light text-bg-dark">You will receive an email with further instructions on resetting your password.</p>
        <form id="forgetpswd" action="" method="POST" class="needs-validation" novalidate>
            <div class="form-floating row mb-2">
                <input id="email" type="email" class="form-control" name="email" placeholder="Please enter your email" required>
                <label for="email">Email address</label>
            </div>
            <div class="input-group">
                    <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
            </div>
            <div class="d-flex flex-column justify-content-center mb-3">
                <button id="forget-pwd" type="submit" class="btn btn-primary my-2 p-3 fs-5" name="forget-pswd">Send email</button>
                <a href="/signin" class="btn btn-lg btn-primary my-2" role="button">Go back</a>
            </div>
        </form>
    </main> 
<?php
    require "partials/outfooter.php";
?>