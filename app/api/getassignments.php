<?php

requireLoginAjax();

include_once base_path("app/models/workassignmentsmodel.php");
include_once base_path("app/classes/get_work.php");
header('Content-Type: application/json');

$getOperatorJob = new GetWorkContr();
$getOperatorJob->workInformation();

?>