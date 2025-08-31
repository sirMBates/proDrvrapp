<?php

if (session_status() !== 2) {
    session_start();
}

include_once base_path("app/models/profilepicturemodel.php");
include_once base_path("app/classes/set_profile_pic.php");

$requestUri = $_SERVER['REQUEST_URI'];
if ($requestUri === '/setprofilepicture') {}

?>