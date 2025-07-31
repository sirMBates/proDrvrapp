<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
require "includes/cus-modal.php";
$alert = new \core\Flash;
include "includes/flashmessage.php";
?>

<main class="container-fluid my-3">
        <form id="acctinfo" action="" method="">
                <div class="card">
                        <div class="card-header bg-besttrailsclr">
                                <h2 class="text-capitalize text-center text-light"><button type="button" id="changeinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-user fs-3 text-light"></i></button>account information</h2>
                        </div>
                        <div class="card-body">
                                <fieldset>
                                        <legend class="h4">My Info</legend>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-id-card-clip fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="fname" class="form-control" name="firstname" placeholder="first name" disabled>
                                                        <label for="fname" class="text-capitalize"><b>first name</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-id-card-clip fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="lname" class="form-control" name="lastname" placeholder="last name" disabled>
                                                        <label for="lname" class="text-capitalize"><b>last name</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-regular fa-envelope fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="email" id="email" class="form-control" name="email" placeholder="email" readonly>
                                                        <label for="email" class="text-capitalize"><b>email</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-cake-candles fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="date" id="birthdate" class="form-control" name="birthdate" disabled>
                                                        <label for="birthdate" class="text-capitalize"><b>birth date</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="material-symbols-outlined fs-4 text-primary">smartphone</i></span>
                                                <div class="form-floating">
                                                        <input type="tel" id="mobileDev" inputmode="tel" class="form-control" name="mobile device" placeholder="Mobile number" readonly>
                                                        <label for="mobileDev" class="text-capitalize"><b>mobile number</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-regular fa-user fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="uname" class="form-control" name="username" placeholder="Username" disabled>
                                                        <label for="uname" class="text-capitalize"><b>username</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-lock fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="password" id="password" class="form-control" name="password" placeholder="Password" readonly>
                                                        <label for="password" class="text-capitalize"><b>password</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="material-symbols-outlined fs-3 text-primary">person_check</i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="status" class="form-control" name="drvrstatus" placeholder="Status" disabled>
                                                        <label for="status" class="text-capitalize"><b>status</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group">
                                                <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                        </div>
                                </fieldset>
                        </div>

                        <div class="card-footer">
                                <div class="row my-2">                        
                                        <button id="updatePswd" type="button" class="btn btn-primary text-capitalize" name="updatepswd">update<br>password</button>
                                </div>
                                <div class="row my-2">
                                        <button id="updateTel-email" type="button" class="btn btn-outline-secondary text-capitalize" name="update tel/email" formaction="">update<br>email/phone</button>
                                </div>                        
                        </div>
                </div>
        </form>
</main>

<?php
        require "partials/footer.php";
?>