<?php
        require "../config.php";
        require "partials/outhead.php";
        include "includes/errormsgs.php";
?>
    <main class="w-50 my-5 align-self-center">
        <h1 class="text-center text-light font-monospace"><u>Reset your password here!</u></h1>
        <p class="h5 text-center text-light">You will receive an email with further instructions on resetting your password.</p>
        <form id="resetpswd" action="../../app/controllers/reset-password.php" method="POST" class="needs-validation" novalidate>
            <div class="form-floating row mb-2">
                <input id="email-for-pswdreset" type="email" inputmode="email" class="form-control" placeholder="Please enter your email" required>
                <label for="email-for-pswdreset">Email address</label>
            </div>
            <div class="row mb-2">
                <button type="button" class="btn btn-lg btn-primary my-2 p-3" name="reset-pswd">Send email</button>
                <button type="button" class="btn btn-lg btn-primary my-2 p-3" name="cancel">Go back</button>
            </div>
        </form>
    </main> 
<?php
    require "partials/outfooter.php";
?>