<?php

$alert = new \core\Flash();

if (session_status() !== 2) {
    session_start();
}

if (isset($_GET['token'])) {
    $token = $_GET['token_hash'];
    $token_hash = hash("sha256", $token);
} elseif (isset($_POST['reset-pswd'])) {
            // Get the token from the queryString
            $token = $_GET['token'];
            $sanitizedToken = filter_var($token, FILTER_SANITIZE_STRING);
            $token_hash = hash("sha256", $sanitizedToken);
            // Getting the info from the form using POST method from the name attribute.
            $password = htmlspecialchars($_POST['password']);
            // Instantiate the add user controller class. ↓
            include_once base_path("app/models/database.php");
            include_once base_path("app/models/resetpwdmeth.php");
            include_once base_path("app/classes/reset_pwd.php");
}