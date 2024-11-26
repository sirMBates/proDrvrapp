        <div class="offcanvas offcanvas-start" tabindex="-1" id="useraccess" aria-labelledby="useraccessLabel">
                <div class="offcanvas-header bg-besttrailsclr">
                        <h5 class="offcanvas-title text-light" id="useraccessLabel"><?php
                        if (!isset($_SESSION['first_name']) && !isset($_SESSION['last_name'])) {
                                echo "Pro Driver";
                        }
                        else {
                                echo $_SESSION['last_name'] . " " . $_SESSION['first_name'];
                        }?></h5>
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
                                        <li class="dropdown-item"><a href="../mail/index.php"><i class="px-2 fa-solid fa-envelope"></i>Send email</a></li>
                                </ul>
                        </div>
                        <div class="dropdown mt-3 d-none">
                                <a href="../views/printable.php" class="btn btn-secondary d-none" role="button"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</a>
                                
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>View Paycard</button>
                                <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-print"></i>Print</a></li>
                                        <li><a class="dropdown-item text-btd-blue-dodger" href='#'><i class="px-2 fa-solid fa-file-export"></i>Save & Download</a></li>
                                </ul>
                        </div>
                        <div class="dropdown mt-3">
                                <a href="#" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-circle-info"></span>Help</a>
                        </div>
                        <div class="dropdown mt-3">
                                <a href="../../app/controllers/logout.php" class="btn btn-secondary" role="button"><span class="px-2 fa-solid fa-right-from-bracket"></span>Sign Out</a>
                        </div>
                        <div class="d-inline-flex fixed-bottom">
                                <button type="button" id="themeBtn" class="btn btn-light" aria-label="Left Align" style="background: none; border: none; width: 50px; height: 50px;">
                                <i class="fa-fw fa-solid fa-moon fa-lg text-dark" aria-hidden="true"></i>
                                </button>
                                <p class="h5 align-self-center" style="margin-left: -5px; margin-top: 5px;">Dark theme</p>
                        </div>
                </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-besttrailsclr">
                <div class="container-fluid">
                        <a class="navbar-brand m-0" data-bs-toggle="offcanvas" role="button" aria-controls="useraccess" href="#useraccess"><img src="../images-videos/logoandicons/bus-driver-icon-png-14404.png" alt="N/A" width="50" height="50" class="d-inline-block align-text-center"></a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                                <a class="nav-link" href="../views/home.php"><span class="px-2 fa-solid fa-house"></span>Home</a>
                                        </li>
                                        <li class="nav-item">
                                                <a class="nav-link" href="../views/joborder.php"><span class="px-2 fa-solid fa-file"></span>Job Order</a>
                                        </li>
                                        <li class="nav-item">
                                                <a class="nav-link" href="../views/dprofile.php"><span class="px-2 fa-solid fa-user"></span>My Profile</a>
                                        </li>
                                        <li class="nav-item">
                                                <a class="nav-link" href="../views/payroll.php"><span class="px-2 fa-solid fa-file-invoice-dollar"></span>Payroll</a>
                                        </li>                                                                
                                </ul>
                        </div>
                </div>
        </nav>