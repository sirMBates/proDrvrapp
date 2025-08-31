<?php

if (session_status() !== 2) {
    session_start();
}

//$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

/*if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}*/

include_once base_path("app/models/profilepicturemodel.php");
include_once base_path("app/classes/set_profile_pic.php");

if ($requestUri === '/setprofilepicture') {
    if (isset($_FILES['profileImage'])) {
        $file = $_FILES['profileImage'];
        $drvrPicture = new SetDrvrPictureContr($file);
        $drvrPicture->setProfilePicture($file);
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Photo uploaded'
        ]);
        exit();
    }
}

?>