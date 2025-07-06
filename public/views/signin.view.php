<?php
require "partials/outhead.php";
$alert = new \core\Flash;
require "includes/flashmessage.php";
?>
        <!--<img src="../images-videos/logoandicons/BestTrailsTravels_Logo.png" id="logo" class="mt-3 img-fluid" alt="Not Available">-->
        <div id="form-container" class="d-flex flex-column my-auto">
                <form id="logInAcct" class="needs-validation" action="" method="POST" novalidate>
                        <div class="input-group input-group-lg d-flex flex-column justify-content-center px-2">
                                <div class="input-group my-2">
                                        <input type="text" id="username" class="form-control fs-4 rounded-2" name="username" placeholder="Username" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Username" data-bs-content="Please enter your username" required>
                                        <div class="invalid-feedback">Please enter username</div>
                                </div>

                                <div class="input-group my-2">
                                        <input type="password" id="password" class="form-control fs-4" name="password" placeholder="Password" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Password" data-bs-content="Please enter your password" required><span class="input-group-text rounded-end" aria-describedby="password"><i class="fa-solid fa-eye" id="psword-icon"></i></span>
                                        <div class="invalid-feedback">Please enter your password</div>
                                </div>

                                <div class="input-group">
                                        <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                </div>
                                                
                                <div class="d-flex justify-content-center my-3 px-2">
                                        <button type="submit" id="signin" name="loginAcct" class="btn btn-lg btn-primary text-center text-capitalize" disabled>sign in</button>
                                </div>

                                <p class="text-center"><a href="/" class="link-btd-white-floral link-opacity-50-hover link-offset-2 link-underline-opacity-50-hover" id="linkCreateAcct">Don't have an account? Create account</a></p>

                                <p class="text-center"><a href="#" class="link-btd-white-floral link-opacity-50-hover link-offset-2 link-underline-opacity-50-hover" id="linkResetPass">Forgot password?</a></p>
                        </div>
                </form>
        </div>
<?php
require "partials/outfooter.php";
?>