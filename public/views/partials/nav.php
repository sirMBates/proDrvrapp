<div class="offcanvas offcanvas-start" tabindex="-1" id="useraccess" aria-labelledby="useraccessLabel">
        <div class="offcanvas-header bg-besttrailsclr">
                <div id="profilecon" style="width: 60px; height: 60px;" class="border border-2 border-primary rounded d-inline-block me-2">
                        <label for="profile-upload"><img id="profile-pic" src="../../dist/images-videos/logoandicons/photo-camera-interface-symbol-for-button.png" alt="N/A" width="50" height="50" class="mx-1 my-1"></label>
                        <input type="file" id="profile-upload" accept="image/jpg, image/jpeg, image/png, image/gif" capture="user">
                </div>
                <h5 class="offcanvas-title text-light" id="useraccessLabel"></h5>
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
                <div class="dropdown mt-3">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"><span class="px-2 fa-solid fa-user-tie"></span>Web Admin</button>
                        <ul class="dropdown-menu">
                                <li class="dropdown-item"><a href="/contact"><i class="px-2 fa-solid fa-envelope"></i>Send email</a></li>
                        </ul>
                </div>
                <div class="dropdown mt-3 d-none">
                        <a href="/printable" class="btn btn-secondary d-none" role="button"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</a>
                                
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</button>
                        <ul class="dropdown-menu">
                                <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-print"></i>Print</a></li>
                                <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-file-export"></i>Save & Download</a></li>
                        </ul>
                </div>
                <div class="dropdown mt-3">
                        <a href="#" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-circle-info"></span>Help</a>
                </div>
                <div class="dropdown mt-3 d-none">                        
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="px-2 fa-solid fa-square-poll-horizontal"></i>Switch Status</button>
                        <ul class="dropdown-menu">
                                <li><a href='#'status-enroute-location class="dropdown-item text-btd-blue-dodger set-status status-enroute-garage" role="button"><i class="px-2 fa-solid fa-road"></i>Enroute/Yard</a></li>
                                <li><a href='#' class="dropdown-item text-btd-blue-dodger set-status status-checkedin-garage" role="button"><i class="px-2 fa-solid fa-map-pin"></i>At Yard</a></li>
                                <li><a href='#' class="dropdown-item text-btd-blue-dodger set-status status-enroute-location" role="button"><i class="px-2 fa-solid fa-road"></i>Enroute/Loc</a></li>
                                <li><a href='#' class="dropdown-item text-btd-blue-dodger set-status status-onlocation" role="button"><i class="px-2 fa-solid fa-location-dot"></i>At Location</a></li>
                                <li><a href='#' class="dropdown-item text-btd-blue-dodger set-status status-working-assignment" role="button"><i class="px-2 fa-solid fa-clipboard"></i>On Assignment</a></li>
                                <li><a href='#' class="dropdown-item btn text-danger text-center set-status status-emergency" role="button">Emergency</a></li>
                        </ul>
                </div>
                <div class="dropdown mt-3">
                        <a href="/logout" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-right-from-bracket"></span>Sign Out</a>
                </div>
                <div class="d-inline-flex fixed-bottom">
                        <button type="button" id="themeBtn" class="btn btn-light" aria-label="Left Align" style="background: none; border: none; width: 50px; height: 50px;">
                        <i class="fa-fw fa-solid fa-moon fa-lg text-dark" aria-hidden="true"></i>
                        </button>
                        <p class="h5 align-self-center" style="margin-left: -5px; margin-top: 5px;">Dark theme</p>
                </div>
        </div>
</div>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-besttrailsclr container-fluid">
        <div class="container-fluid">
                <a class="navbar-brand m-0" data-bs-toggle="offcanvas" role="button" aria-controls="useraccess" href="#useraccess"><img src="../../dist/images-videos/logoandicons/bus-driver-icon-png-14404.png" alt="N/A" width="50" height="50" class="d-inline-block align-text-center"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                        <a class="nav-link" href="/"><i class="px-2 fa-solid fa-house"></i>Home</a>
                                </li>
                                <li class="nav-item">
                                        <a class="nav-link" href="/orders"><i class="px-2 fa-solid fa-file"></i>Job Order</a>
                                </li>
                                <li class="nav-item">
                                        <a class="nav-link" href="/profile"><i class="px-2 fa-solid fa-user"></i>My Profile</a>
                                </li>
                                <li class="nav-item">
                                        <a class="nav-link" href="/timesheet"><i class="px-2 fa-solid fa-file-invoice-dollar"></i>Summary</a>
                                </li>                                                                
                        </ul>
                </div>
        </div>
</nav>