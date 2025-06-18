<header class="container-fluid d-flex flex-row justify-content-between mb-2 bg-btd-gray-silver border-bottom border-1 border-black">
        <div class="d-flex flex-column justify-content-start">                                
                <img src="../images-videos/prodrvrbkgd.png" class="align-self-start m-2 img-fluid" width="250" id="logo" alt="companylogo">                       
        </div>
        <div class="d-flex flex-column justify-content-start align-items-end">
                <div id="clock_container" class="z-3 d-flex flex-column text-capitalize">
                        <div id="dateCon" class="container-sm d-flex flex-row" style="margin-right: -5%;">
                                <div class="text-btd-blue-bright mx-1"></div>
                                <div class="text-btd-blue-bright mx-1"></div>
                                <div class="text-btd-blue-bright mx-1"></div>
                                <div class="text-btd-blue-bright mx-1"></div>
                        </div>
                        <div id="timeCon" class="container-sm d-flex justify-content-end" style="margin-right: -4%;">
                                <div class="container-xs mx-1 text-light"></div>
                                <div class="container-xs mx-1 text-light blink">:</div>
                                <div class="container-xs mx-1 text-light"></div>
                                <div class="container-xs mx-1 text-light"></div>
                                <div class="container-xs mx-1 text-light"></div>
                        </div>
                </div>
                <p class="text-btd-white-floral fs-3">Hello, <?= !isset($_SESSION['first_name']) ? 'Driver': $_SESSION['first_name'];?></p>
        </div>
</header>
