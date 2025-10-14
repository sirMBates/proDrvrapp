<?php
$alert = new core\Flash();
?>
<header class="container-fluid d-inline-flex justify-content-between align-items-center mb-2 bg-btd-gray-silver border-bottom border-1 border-black">
        <div class="flex-shrink-0 d-flex align-items-center" style="width: 20%;">                                
                <img src="../../dist/images-videos/prodrvrbkgd.png" class="img-fluid m-3" width="250" id="logo" alt="companylogo">                     
        </div>
        <div id="alert-container" class="flex-shrink-1 d-flex justify-content-center align-items-center"><?php include_once base_path("public/views/partials/flashmessage.php");?></div>
        <div id="statusMessage" class="z-10 text-btd-white-floral fs-3 align-self-start mt-2"></div>
</header>