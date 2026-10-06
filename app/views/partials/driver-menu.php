<div id="drivermenu" class="offcanvas offcanvas-start" tabindex="-1" aria-labelledby="drivermenuLabel">
        <div class="offcanvas-header bg-prodriverclr">
                <div id="profilecon" style="width: 60px; height: 60px;" class="border border-2 border-primary rounded d-inline-block me-2">                        
                        <label for="menuProfileInput"><img id="menuProfileImage" src="../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png" alt="N/A" width="50" height="50" class="mx-1 my-1"></label>
                        <input type="file" id="menuProfileInput" accept="image/jpg, image/jpeg, image/png, image/gif">
                </div>
                <h5 class="offcanvas-title text-light" id="drivermenuLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="close"></button>
        </div>
        <div class="offcanvas-body">
                <div class="dropdown mt-3">
                        <a href="/faqs" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-circle-info"></span>Faqs</a>
                </div>
                <div class="dropdown mt-3">
                        <a href="/profile" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-user"></span>My Profile</a>
                </div>
                <div class="dropdown mt-3">
                        <a href="/counter" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-arrow-up-1-9"></span>Tick Counter</a>
                </div>
                <div class="dropdown mt-3">
                        <a href="/contact" class="btn btn-secondary" role="button"><i class="px-2 fa-solid fa-envelope"></i>Web Admin</a>
                </div>
                <div class="dropdown mt-3">
                        <a href="/preferences" class="btn btn-secondary" role="button"><i class="px-2 fa-solid fa-gear"></i>Preferences</a>
                </div>
                <div class="dropdown mt-3">
                        <button id="logout-link" type="button" class="btn btn-secondary">
                                <span class="px-2 fa-solid fa-right-from-bracket" aria-hidden="true"></span>
                                Sign Out
                        </button>
                </div>
                <div class="d-inline-flex fixed-bottom">
                        <button type="button" id="themeBtn" class="btn btn-light" aria-label="Left Align" style="background: none; border: none; width: 50px; height: 50px;">
                        <i class="fa-fw fa-solid fa-moon fa-lg text-dark" aria-hidden="true"></i>
                        </button>
                        <p class="h5 align-self-center" style="margin-left: -5px; margin-top: 5px;">Dark theme</p>
                        <span id="themeModeIndicator" class="theme-auto" style="font-size: 0.7em; margin-left:5px; color:cornflowerblue;">Auto</span>
                </div>
        </div>
</div>
<?php
require base_path('app/views/partials/logout-modal.php');
?>