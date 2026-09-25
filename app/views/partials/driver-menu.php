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
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"><span class="px-2 fa-solid fa-building"></span>Office</button>
                        <ul class="dropdown-menu">
                                <li class="dropdown-item"><a href="tel:718-875-1103,2"><i class="px-2 fa-solid fa-phone"></i>Contact Office</a></li>
                                <li class="dropdown-item"><a href="tel:646-281-0778"><i class="px-2 fa-solid fa-mobile"></i>Dispatcher</a></li>
                                <li class="dropdown-item"><a href="tel:917-567-8218"><i class="px-2 fa-solid fa-mobile"></i>Dispatcher</a></li>
                                <li class="dropdown-item"><a href="tel:646-301-5715"><i class="px-2 fa-solid fa-mobile"></i>Dispatcher</a></li>
                        </ul>
                </div>
                <!--<div class="dropdown mt-3 d-none">
                        <a href="/printable" class="btn btn-secondary d-none" role="button"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</a>
                                
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</button>
                        <ul class="dropdown-menu">
                                <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-print"></i>Print</a></li>
                                <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-file-export"></i>Save & Download</a></li>
                        </ul>
                </div>-->
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
                        <a id="logout-link" href="/logout" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-right-from-bracket"></span>Log Out</a>
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