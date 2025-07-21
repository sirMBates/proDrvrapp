<?php
    require "partials/outhead.php";
    $alert = new \core\Flash;
    include "includes/flashmessage.php";
    if (isset($_GET['cleared'])) {
        $_SESSION['reset'] = $_GET['cleared'];
    }
?>

    <main class="container-fluid d-flex justify-content-center">
        <div class="card mb-auto" style="width: 35rem; margin-top: 5% !important;">
            <form id="resetpw" action="" method="POST" class="needs-validation" novalidate>
                <div class="card-header bg-besttrailsclr text-btd-white-off">
                        <p class='h3 text-center text-capitalize'>complete password reset</p>
                </div>
                <p class="fs-4 mt-1 text-center text-besttrailsclr roboto-condensed"><u>Use this form to complete your reset.</u></p>
                <div class="card-body p-3 my-3 d-flex flex-column justify-content-center align-items-center">
                    <div class="input-group my-1">
                            <input type="password" id="resetpswd" class="form-control fs-4" name="password" placeholder="Password" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Password" data-bs-content="Must contain a minimum of 8 characters with atleast 1 (U)ppercase letter, 1 (l)owercase letter, 1 number and 1 special character. (i.e. !@#%&_)" data-bs-placement="top" data-bs-trigger="focus" required><span class="input-group-text rounded-end" id="pwd-icon-click" aria-describedby="password"><i class="fa-solid fa-eye" id="pwd-icon"></i></span>
                            <div class="invalid-feedback">Please enter a password</div>
                    </div>

                    <div class="input-group">
                            <input id="resetToken" type="hidden" class="form-control" name="resetToken" value="<?= $_SESSION['reset']; ?>" required>
                    </div>

                    <div class="input-group my-1">
                            <input type="password" id="conf-reset-pswd" class="form-control fs-4" name="conf-reset-pswd" placeholder="Confirm Password" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Confirm password" data-bs-content="Re-enter password" data-bs-placement="bottom" data-bs-trigger="focus" required><span class="input-group-text rounded-end" aria-describedby="conf-resetpswd"><i class="fa-solid fa-eye" id="con-pwd-icon"></i></span>
                            <div class="invalid-feedback">Re-type password exactly.</div>
                    </div>

                    <p id="password-not-match" class="text-danger" hidden>Your password does not match</p>

                    <div class="input-group">
                            <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                    </div>
                    
                    <div class="d-flex justify-content-center mb-3">
                            <button id="reset" type="submit" name="reset-pswd" class="btn btn-lg btn-outline-primary my-2" disabled>Reset Password</button>
                    </div>
                </div>
            </form>
        </div>
    </main>


<?php
    require "partials/outfooter.php";
?>