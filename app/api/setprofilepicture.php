<?php

require_once base_path('core/Helperfunc.php');

requireLoginAjax();

/*if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

include_once base_path("app/models/profilepicturemodel.php");
include_once base_path("app/classes/set_profile_pic.php");

if (isset($_FILES['profileImage'])) {
    $file = $_FILES['profileImage'];
    $drvrPicture = new SetDrvrPictureContr($file);
    $drvrPicture->setProfilePicture();
    header("Content-Type: application/json");
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Photo uploaded'
    ]);
    exit();
}

?>