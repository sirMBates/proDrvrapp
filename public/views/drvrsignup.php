<?php
        require "../../config.php";
        $title = "Pro Driver - Register";
        require "../includes/head.php";
?>
<body class="d-flex flex-column align-items-center min-vh-100 noprint prodrvrbkgd">
<?php
        include "../includes/cus-modal.php";
?>       
        <!--<img src="../images-videos/logoandicons/BestTrailsTravels_Logo.png" id="logo" class="mt-3 img-fluid" alt="Not Available">-->
<?php
        require "../includes/errormsgs.php";
?>
        <div id="form-container" class="d-flex flex-column my-auto">
                <form id="signUpAcct" class="needs-validation" action="../../app/router/adduser.php" method="POST" novalidate>
                        <div class="input-group input-group-lg px-3 my-3 d-flex justify-content-center flex-column">
                                <div class="input-group my-1">
                                        <input type="text" id="username" class="form-control fs-4 rounded-2" name="username" placeholder="Username" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Username" data-bs-content="Must contain 5 - 7 characters with atleast 1 (U)ppercase letter, 1  (l)owercase letter & a number." data-bs-placement="top" data-bs-trigger="focus" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,6}$" required>
                                        <div class="invalid-feedback">Please enter a username.</div>
                                </div>
                                
                                <div class="input-group my-1">
                                        <input id="userEmail" type="email" inputmode="email" class="form-control fs-4 rounded-2" name="email" placeholder="Email Address" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Email" data-bs-content="Please enter your email address" data-bs-placement="right" data-bs-trigger="focus" required>
                                        <div class="invalid-feedback">Please enter your email.</div>
                                </div>
                                
                                <div class="input-group my-1">
                                        <input type="password" id="password" class="form-control fs-4" name="password" placeholder="Password" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Password" data-bs-content="Must contain a minimum of 8 characters with atleast 1 (U)ppercase letter, 1 (l)owercase letter, 1 number and 1 special character. (i.e. !@#$%&._)" data-bs-placement="right" data-bs-trigger="focus" required pattern="^(?=.*[a-z])(?=.*[A-Z]*[A-Z])(?=.*[0-9])(?=.*[!@#\$%&\._]).\S{7,}$"><span class="input-group-text rounded-end" id="pwd-icon-click" aria-describedby="password"><i class="fa-solid fa-eye" id="pwd-icon"></i></span>
                                        <div class="invalid-feedback">Please enter a password</div>
                                </div>          
                                
                                <div class="input-group my-1">
                                        <input type="password" id="confirmPassword" class="form-control fs-4" name="confirmPassword" placeholder="Confirm Password" data-bs-container="body" data-bs-toggle="popover" data-bs-title="Confirm password" data-bs-content="Re-enter password" data-bs-placement="right" data-bs-trigger="focus" required><span class="input-group-text rounded-end" aria-describedby="confirmPassword"><i class="fa-solid fa-eye" id="con-pwd-icon"></i></span>
                                        <div class="invalid-feedback">Re-type password exactly.</div>
                                </div>

                                <p id="password-does-not-match-text" class="text-danger" hidden>Your password does not match</p>

                                <div class="input-group">
                                        <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                </div>

                                <div class="d-flex justify-content-center mb-3">
                                        <button id="signup" type="submit" name="createAccount" class="btn btn-lg btn-primary" disabled>Create Account</button>
                                </div>

                                <p class="text-center"><a href="./drvrsignin.php" id="linkSignIn" class="link-btd-white-floral link-opacity-50-hover link-offset-2 link-underline-opacity-50-hover">Already have an account? Log in here!</a></p>
                        </div>
                </form>
        </div>

        <footer class="mt-auto text-light text-center text-lg-start" >
                <h5 class="text-center text-uppercase"><i>created by </i>softbigboy</h5>
                <p class="text-center"><a class="text-light" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
        </footer>

<?php
        include "../includes/scripts.php";
?>
</body>
</html>