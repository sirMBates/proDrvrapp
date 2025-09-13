<?php

requireLoginAjax();

include_once base_path("app/models/getdrvrmodel.php");
include_once base_path("app/classes/get_drvr.php");
header('Content-Type: application/json');

$getDriversProfile = new GetDrvrContr();
$getDriversProfile->driverInfo();

?>