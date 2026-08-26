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
                        <label for="profilePictureInput"><img id="profilePictureImage" src="../dist/images-videos/logoandicons/defaultProfileImage.jpg" class="profile-pic shadow-sm" alt="Profile Picture"></label>
                        <input type="file" id="profilePictureInput" class="d-none" accept="image/jpg, image/jpeg, image/png, image/gif">
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
                                                <button type="button" class="btn btn-outline-primary btn-sm edit-btn">Edit</button>
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
                                                <button type="button" class="btn btn-outline-primary btn-sm edit-btn">Edit</button>
                                        </div>
                                </div>
                        </div>

                        <!-- Password -->
                        <div class="col-12">
                                <div class="card field-card editable" data-field="password">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="field-content">
                                                        <h6 class="text-muted mb-1">Password</h6>
                                                        <p id="passwordDisplay" class="field-value">********</p>
                                                </div>
                                                <button type="button" class="btn btn-outline-primary btn-sm edit-btn">Change</button>
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
                        <button type="button" id="updateInfoBtn" class="btn btn-secondary mx-2">Update Information</button>
                        <button type="button" id="updatePswdBtn" class="btn btn-primary mx-2">Update Password</button>
                </div>
        </form>
</main>

<?php
        require "partials/footer.php";
?>
