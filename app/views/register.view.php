<?php
require "partials/outhead.php";
$alert = new Core\Flash();
include "partials/flashmessage.php";
?>
        <!--<img id="logo" src="../images-videos/logoandicons/BestTrailsTravels_Logo.png" class="my-3 img-fluid" alt="Not Available">-->
        <main class="container-fluid my-auto">
                <div id="form_container" class="container">
                        <form id="acct_info" action="" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="__method" value="patch">
                                <input type="hidden" name="action" value="complete-registration">
                                <h2 class="text-center text-capitalize text-dark">hello, <?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="h5 text-center text-capitalize text-dark">please enter information below!</p>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-regular fa-id-card fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="forename" type="text" class="form-control form-control-lg" name="forename" placeholder="First name" pattern="^[a-zA-Z'\-]+$" required>
                                                <label for="forename" class="text-capitalize"><b>first name</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter first name.</strong></div>
                                </div>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-regular fa-id-card fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="surname" type="text" class="form-control form-control-lg" name="surname" placeholder="Last name" pattern="^[a-zA-Z'\-]+$" required>
                                                <label for="surname" class="text-capitalize"><b>last name</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter last name.</strong></div>
                                </div>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-solid fa-mobile fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="mobile_num" type="tel" inputmode="tel" class="form-control form-control-lg" name="mobilenum" placeholder="Mobile number" pattern="[0-9]{10}" required>
                                                <label for="mobile_num" class="text-capitalize"><b>mobile number</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter mobile number.</strong></div>
                                </div>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-solid fa-cake-candles fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="date-of-birth" type="date" class="form-control form-control-lg" name="dateofbirth" required>
                                                <label for="date-of-birth" class="text-capitalize"><b>birth date</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter your birthdate.</strong></div>
                                </div>

                                <input id="drvrToken" type="hidden" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>

                                <input id="driverId" type="hidden" value="<?= $_SESSION['driver_id']?>">

                                <input id="newOperatorId" type="hidden" name="operatorid" value="" required>

                                <div id="save_btn_box" class="col-12 row mx-auto mb-3">
                                        <button id="register" type="submit" class="btn btn-primary btn-lg text-uppercase">save & log-in</button>
                                </div>
                        </form>
                </div>
        </main>
<?php
        require "partials/outfooter.php";
?>