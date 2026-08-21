<?php
require "partials/head.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>

<main class="container my-4">
        <form id="acctinfo" action="" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="__method" value="patch">
                <!-- Profile Picture -->
                <div class="text-center mb-4">
                        <img id="profilePictureImage" src="../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png" class="profile-pic shadow-sm" alt="Profile Picture">
                        <input type="file" id="profilePictureInput" class="d-none" accept="image/*">
                </div>

                <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-12">
                                <div class="card field-card">
                                        <div class="card-header bg-besttrailsclr d-flex justify-content-between align-items-center">
                                                <h2 class="text-capitalize text-light mb-0">Profile Information</h2>

                                                <button type="button" id="notifyinfo" class="btn btn-light btn-sm">
                                                        <i class="fa-solid fa-circle-info fs-5 text-besttrailsclr"></i>
                                                </button>
                                        </div>

                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                        <h6 class="text-muted mb-1">Full Name</h6>
                                                        <p id="fullnameDisplay" class="field-value"></p>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <!-- Operator ID -->
                        <div class="col-12">
                                <div class="card field-card">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                        <h6 class="text-muted mb-1">Operator ID</h6>
                                                        <p id="operatorIdDisplay" class="field-value"></p>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <!-- Username -->
                        <div class="col-12">
                                <div class="card field-card">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                        <h6 class="text-muted mb-1">Username</h6>
                                                        <p id="usernameDisplay" class="field-value"></p>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12">
                                <div class="card field-card editable" data-field="email">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="field-content">
                                                        <h6 class="text-muted mb-1">Email</h6>
                                                        <p id="emailDisplay" class="field-value"></p>
                                                </div>
                                                <button class="btn btn-outline-primary btn-sm edit-btn">Edit</button>
                                        </div>
                                </div>
                        </div>

                        <!-- Mobile -->
                        <div class="col-12">
                                <div class="card field-card editable" data-field="mobile">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="field-content">
                                                        <h6 class="text-muted mb-1">Mobile</h6>
                                                        <p id="mobileDisplay" class="field-value"></p>
                                                </div>
                                                <button class="btn btn-outline-primary btn-sm edit-btn">Edit</button>
                                        </div>
                                </div>
                        </div>

                        <!-- Password -->
                        <div class="col-12">
                                <div class="card field-card editable" data-field="password">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="field-content">
                                                        <h6 class="text-muted mb-1">Password</h6>
                                                        <p class="field-value">********</p>
                                                </div>
                                                <button class="btn btn-outline-primary btn-sm edit-btn">Change</button>
                                        </div>
                                </div>
                        </div>

                        <!-- Status -->
                        <div class="col-12">
                                <div class="card field-card">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                        <h6 class="text-muted mb-1">Status</h6>
                                                        <p id="statusDisplay" class="field-value"></p>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                </div>

                <div class="text-center mt-4">
                        <button id="updateInfoBtn" class="btn btn-secondary mx-2">Update Information</button>
                        <button id="updatePswdBtn" class="btn btn-primary mx-2">Update Password</button>
                </div>
        </form>
</main>

<?php
        require "partials/footer.php";
?>

<!--<main class="container-fluid my-3">
        <form id="acctinfo" action="" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="__method" value="patch">
                <div class="card">
                        <div class="card-header bg-besttrailsclr">
                                <h2 class="text-capitalize text-center text-light"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-user fs-3 text-light"></i></button>profile information</h2>
                        </div>
                        <div class="card-body">
                                <fieldset>
                                        <legend class="h4">My Info</legend>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-id-card-clip fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="fullname" class="form-control" name="fullname" placeholder="full name" disabled>
                                                        <label for="fullname" class="text-capitalize"><b>full name</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-regular fa-id-card fs-4 text-primary"></i></span>
                                                <div class="form-floating">
                                                        <input type="text" id="operatorId" class="form-control" name="operatorid" placeholder="Operator Id" disabled>
                                                        <label for="operatorId" class="text-capitalize"><b>Operator Id</b></label>
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
                                                <span class="input-group-text"><button id="email-change" type="button" class="btn"><i class="fa-regular fa-envelope fs-4 text-primary"></i></button></span>
                                                <div class="form-floating">
                                                        <input type="email" id="email" class="form-control" name="email" placeholder="email" disabled>
                                                        <label for="email" class="text-capitalize"><b>email</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fa-solid fa-cake-candles fs-4 text-primary"></i><!--<button id="birth-date-change" type="button" class="btn"></button>--><!--</span>
                                                <div class="form-floating">
                                                        <input type="date" id="birthdate" class="form-control" name="birthdate" placeholder="birth date" disabled>
                                                        <label for="birthdate" class="text-capitalize"><b>birth date</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><button id="phone-change" type="button" class="btn"><i class="fa-solid fa-mobile fs-4 text-primary"></i></button></span>
                                                <div class="form-floating">
                                                        <input type="tel" id="mobileDev" inputmode="tel" class="form-control" name="mobile" placeholder="mobile number" disabled>
                                                        <label for="mobileDev" class="text-capitalize"><b>mobile number</b></label>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2 position-relative">
                                                <span class="input-group-text"><button id="pwd-change" type="button" class="btn p-0 border-0 bg-transparent"><i class="fa-solid fa-lock fs-4 text-primary"></i></button></span>
                                                <div class="form-floating flex-grow-1">
                                                        <input type="password" id="password" class="form-control" name="password" placeholder="Password" disabled>
                                                        <label for="password" class="text-capitalize"><b>password</b></label>
                                                        <i id="psword-icon" class="fa-solid fa-eye position-absolute end-0 top-50 translate-middle-y me-3 text-muted" role="button" style="cursor: pointer; z-index: 5;"></i>
                                                </div>
                                        </div>
                                        <div class="input-group mb-2">
                                                <span class="input-group-text"><span class="material-symbols-outlined fs-3 text-primary">person_check</span></span>
                                                <div class="form-floating">
                                                        <input type="text" id="status" class="form-control" name="drvrstatus" placeholder="Status" disabled>
                                                        <label for="status" class="text-capitalize"><b>status</b></label>
                                                </div>
                                        </div>
                                </fieldset>
                        </div>

                        <div class="card-footer">
                                <div class="row my-2">                        
                                        <button id="updatePswd" type="submit" class="btn btn-outline-primary text-capitalize" name="updatepswd">update<br>password only</button>
                                </div>
                                <div class="row my-2">
                                        <button id="update-info" type="submit" class="btn btn-outline-secondary text-capitalize" name="updateinfo">update<br>information</button>
                                </div>                        
                        </div>
                </div>
        </form>
</main>-->