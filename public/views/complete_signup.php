<?php
require "../../config.php";
$title = "Pro Driver - driver Info";
require "../includes/head.php";
?>
<body class="d-flex flex-column align-items-center min-vh-100 noprint">
        <!--<img id="logo" src="../images-videos/logoandicons/BestTrailsTravels_Logo.png" class="my-3 img-fluid" alt="Not Available">-->
<?php
require "../includes/errormsgs.php";
?>
        <main class="container-fluid my-auto">
                <div id="form_container" class="container">
                        <form id="acct_info" class="needs-validation" action="../../app/controllers/drvrprofile.php" method="POST" novalidate>
                                <h2 class="text-center text-capitalize text-dark">hello, <?= $_SESSION['username']?></h2>

                                <p class="h5 text-center text-capitalize text-dark">please enter information below!</p>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-regular fa-id-card fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="forename" type="text" class="form-control form-control-lg" name="forename" placeholder="First name" pattern="^[a-zA-Z]{1,}$" required>
                                                <label for="forename" class="text-capitalize"><b>first name</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter first name.</strong></div>
                                </div>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-regular fa-id-card fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="surname" type="text" class="form-control form-control-lg" name="surname" placeholder="Last name" pattern="^[a-zA-Z]{1,}$" required>
                                                <label for="surname" class="text-capitalize"><b>last name</b></label>
                                        </div>
                                        <div class="invalid-feedback fs-5"><strong>Enter last name.</strong></div>
                                </div>

                                <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fa-solid fa-mobile fs-4 text-primary"></i></span>
                                        <div class="form-floating">
                                                <input id="mobile_num" type="tel" inputmode="tel" class="form-control form-control-lg" name="mobilenum" placeholder="Mobile number" pattern="^\d{10}$" required>
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

                                <div class="input-group">
                                        <input id="secret" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                </div>

                                <div id="save_btn_box" class="col-12 row mx-auto mb-3">
                                        <button type="submit" id="save" class="btn btn-primary btn-lg text-uppercase" name="saveinfo">save & log-in</button>
                                </div>
                        </form>
                </div>
        </main>
        
        <footer class="mt-auto justify-content-center d-flex bg-transparent">
                <div class="container text-center py-2">
                        <h5 class="text-center text-uppercase text-dark"><i>created by </i>softbigboy</h5>
                        <p class="text-center"><a class="text-dark" target="_blank" href="https://www.freeiconspng.com/img/14404">Bus Driver Icon</a></p>
                </div>
        </footer>
<?php
include "../includes/getscripts.php";
?>
</body>
</html>
